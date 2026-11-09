<?php
/**
 * UnAI Labs ambassador commission engine (pure PHP, no WordPress, no I/O).
 * Design: claude/AMBASSADOR_ONBOARDING_SPEC.md (v3). All money is integer paise.
 *
 * Rules implemented:
 *  - Base = what the buyer paid for eligible products (after discounts); optional GST strip.
 *  - Rate by lifetime CONFIRMED units: 10% (1-24), 15% (25-99), 20% (100-249), 25% (250+).
 *    When a tier is reached, all earlier confirmed sales move up (top-up at month close).
 *  - A sale confirms 7 days after payment if not refunded. Refunds inside 7 days void (or
 *    reduce) it. After confirmation a sale is final (later refunds are absorbed by UnAI Labs).
 *  - Self-purchases never count.
 *  - Each sale's commission is rounded once, half up, in paise.
 *  - Ledger is append-only. Month close is idempotent. Payouts are system-set and once per period.
 */
declare(strict_types=1);

namespace UnaiAmb;

const CONFIRM_DAYS   = 7;
const PAYOUT_MIN     = 50000;          // Rs 500 in paise
const ELIGIBLE       = [120, 157, 226, 249, 286];

function rate_bp(int $units): int {   // basis points
    if ($units >= 250) return 2500;
    if ($units >= 100) return 2000;
    if ($units >= 25)  return 1500;
    return 1000;
}

/** round(amount * bp / 10000), half up, integers only. */
function apply_bp(int $paise, int $bp): int {
    if ($paise < 0 || $bp < 0) throw new \InvalidArgumentException('negative');
    return intdiv($paise * $bp + 5000, 10000);
}

/** Strip GST from a GST-inclusive amount (gst_bp e.g. 1800 for 18%). 0 = no GST. */
function base_excl_gst(int $paise, int $gst_bp): int {
    if ($gst_bp === 0) return $paise;
    return intdiv($paise * 10000 + intdiv(10000 + $gst_bp, 2), 10000 + $gst_bp);
}

/**
 * Normalise one order into a sale, or null if it does not count.
 * $o = ['order_id'=>int,'paid_at'=>int ts,'self'=>bool,'items'=>[['product'=>int,'qty'=>int,'paid_paise'=>int]],
 *       'refunds'=>[['at'=>int ts,'paise'=>int]]]
 * Returns ['order_id','paid_at','confirm_at','units','base'] where base and units reflect
 * refunds made BEFORE confirm_at; null if self-purchase, nothing eligible, or fully refunded in time.
 */
function sale_from_order(array $o, int $gst_bp = 0): ?array {
    if (!empty($o['self'])) return null;
    $units = 0; $paid = 0;
    foreach ($o['items'] ?? [] as $it) {
        if (!in_array((int)$it['product'], ELIGIBLE, true)) continue;
        $q = (int)$it['qty']; $p = (int)$it['paid_paise'];
        if ($q < 0 || $p < 0) throw new \InvalidArgumentException('negative item');
        $units += $q; $paid += $p;
    }
    if ($units === 0 || $paid === 0) return null;
    $confirm_at = (int)$o['paid_at'] + CONFIRM_DAYS * 86400;
    $refunded = 0;
    foreach ($o['refunds'] ?? [] as $r) {
        if ((int)$r['at'] < $confirm_at) $refunded += (int)$r['paise'];   // only refunds inside the window
    }
    if ($refunded >= $paid) return null;                                    // fully refunded in time
    if ($refunded > 0) {                                                    // partial: keep units, reduce base
        $paid -= $refunded;
    }
    return ['order_id' => (int)$o['order_id'], 'paid_at' => (int)$o['paid_at'],
            'confirm_at' => $confirm_at, 'units' => $units, 'base' => base_excl_gst($paid, $gst_bp)];
}

/**
 * Month close for one ambassador. Pure: takes the existing ledger rows and all orders, returns NEW rows only.
 * $period_end = last second of the month being closed (ts). $period = 'YYYY-MM'.
 * Ledger row: ['type'=>'CREDIT'|'TOPUP'|'PAYOUT'|'ADJUST','period'=>..,'order_id'=>?int,'amount'=>int paise,'rate_bp'=>?int,'ref'=>?string]
 */
function close_period(array $ledger, array $orders, string $period, int $period_end, int $gst_bp = 0): array {
    foreach ($ledger as $row) {
        if (in_array($row['type'], ['CREDIT', 'TOPUP'], true) && $row['period'] === $period) return []; // idempotent
    }
    $credited_orders = [];
    $credited_total = 0;
    foreach ($ledger as $row) {
        if ($row['type'] === 'CREDIT') $credited_orders[$row['order_id']] = true;
        if (in_array($row['type'], ['CREDIT', 'TOPUP'], true)) $credited_total += (int)$row['amount'];
    }
    $confirmed = [];
    foreach ($orders as $o) {
        $s = sale_from_order($o, $gst_bp);
        if ($s && $s['confirm_at'] <= $period_end) $confirmed[] = $s;
    }
    $units = array_sum(array_column($confirmed, 'units'));
    $bp = rate_bp($units);
    $target = 0; $new = [];
    foreach ($confirmed as $s) {
        $amt = apply_bp($s['base'], $bp);
        $target += $amt;
        if (!isset($credited_orders[$s['order_id']])) {
            $new[] = ['type' => 'CREDIT', 'period' => $period, 'order_id' => $s['order_id'], 'amount' => $amt, 'rate_bp' => $bp, 'ref' => null];
        }
    }
    $new_sum = array_sum(array_column($new, 'amount'));
    $topup = $target - $credited_total - $new_sum;
    if ($topup < 0) {
        // Cannot happen under the rules (confirmed sales are final, rate never drops). Refuse loudly.
        throw new \RuntimeException("negative top-up $topup in $period: ledger and orders disagree");
    }
    if ($topup > 0) $new[] = ['type' => 'TOPUP', 'period' => $period, 'order_id' => null, 'amount' => $topup, 'rate_bp' => $bp, 'ref' => null];
    return $new;
}

function balance(array $ledger): int {
    $b = 0;
    foreach ($ledger as $r) {
        $b += in_array($r['type'], ['PAYOUT'], true) ? -(int)$r['amount'] : (int)$r['amount'];
    }
    return $b;
}

/** Amount to pay for a period, or 0 (below minimum, or already paid this period). */
function payout_due(array $ledger, string $period): int {
    foreach ($ledger as $r) if ($r['type'] === 'PAYOUT' && $r['period'] === $period) return 0;
    $b = balance($ledger);
    return $b >= PAYOUT_MIN ? $b : 0;
}

/** Record a payout. Amount is fixed by the system; a mismatch or second payout is refused. */
function record_payout(array $ledger, string $period, int $amount, string $utr): array {
    $due = payout_due($ledger, $period);
    if ($due === 0) throw new \RuntimeException('nothing due or already paid for ' . $period);
    if ($amount !== $due) throw new \RuntimeException("amount $amount does not match due $due");
    if (!preg_match('/^[A-Za-z0-9]{8,30}$/', $utr)) throw new \InvalidArgumentException('bad UTR');
    return ['type' => 'PAYOUT', 'period' => $period, 'order_id' => null, 'amount' => $amount, 'rate_bp' => null, 'ref' => $utr];
}

/** Statement for one period: opening, credits, top-ups, payouts, closing. */
function statement(array $ledger, string $period): array {
    $open = 0; $credits = 0; $topups = 0; $paid = 0;
    foreach ($ledger as $r) {
        $v = (int)$r['amount']; $sign = $r['type'] === 'PAYOUT' ? -1 : 1;
        if (strcmp($r['period'], $period) < 0) { $open += $sign * $v; continue; }
        if ($r['period'] !== $period) continue;
        if ($r['type'] === 'CREDIT') $credits += $v;
        elseif ($r['type'] === 'TOPUP') $topups += $v;
        elseif ($r['type'] === 'PAYOUT') $paid += $v;
        elseif ($r['type'] === 'ADJUST') $credits += $v;
    }
    return ['period' => $period, 'opening' => $open, 'credits' => $credits, 'topups' => $topups,
            'paid' => $paid, 'closing' => $open + $credits + $topups - $paid];
}
