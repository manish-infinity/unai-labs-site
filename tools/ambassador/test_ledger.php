<?php
/** Tests for ledger.php. Run: php tools/ambassador/test_ledger.php  (exit code 0 = all pass) */
declare(strict_types=1);
require __DIR__ . '/ledger.php';
use function UnaiAmb\{rate_bp, apply_bp, base_excl_gst, sale_from_order, close_period, balance, payout_due, record_payout, statement};

$pass = 0; $fail = 0;
function check(bool $ok, string $name) { global $pass, $fail; if ($ok) { $pass++; } else { $fail++; echo "FAIL: $name\n"; } }
function throws(callable $f): bool { try { $f(); return false; } catch (\Throwable $e) { return true; } }

const DAY = 86400;
$T0 = strtotime('2026-12-01 10:00:00 UTC');
function order(int $id, int $paid_at, int $paise, int $product = 120, int $qty = 1, array $refunds = [], bool $self = false): array {
    return ['order_id' => $id, 'paid_at' => $paid_at, 'self' => $self, 'items' => [['product' => $product, 'qty' => $qty, 'paid_paise' => $paise]], 'refunds' => $refunds];
}
$C = 269910; // Rs 2,999 course after 10% off = Rs 2,699.10

// ---- 1. tiers and rounding ----
check(rate_bp(0) === 1000 && rate_bp(24) === 1000 && rate_bp(25) === 1500, 'tier 24/25');
check(rate_bp(99) === 1500 && rate_bp(100) === 2000, 'tier 99/100');
check(rate_bp(249) === 2000 && rate_bp(250) === 2500 && rate_bp(10000) === 2500, 'tier 249/250 and cap');
check(apply_bp($C, 1000) === 26991, '10% of 2699.10 = 269.91');
check(apply_bp($C, 1500) === 40487, '15% of 2699.10 = 404.865 rounds half up to 404.87');
check(apply_bp(179910, 1000) === 17991, '10% of 1799.10');
check(apply_bp(35910, 2500) === 8978, '25% of 359.10 = 89.775 -> 89.78');
check(apply_bp(1, 5000) === 1 && apply_bp(1, 4999) === 0, 'half-up edge on one paisa');
check(throws(fn() => apply_bp(-1, 1000)), 'negative amount refused');
check(base_excl_gst(118000, 1800) === 100000, 'GST strip 1180 -> 1000');
check(base_excl_gst(269910, 0) === 269910, 'no GST = unchanged');

// ---- 2. which orders count ----
check(sale_from_order(order(1, $T0, $C, 120, 1, [], true)) === null, 'self purchase ignored');
check(sale_from_order(order(2, $T0, 1499000, 356, 10)) === null, 'team licence ignored');
check(sale_from_order(order(3, $T0, $C, 120, 1, [['at' => $T0 + 3 * DAY, 'paise' => $C]])) === null, 'full refund inside 7 days voids');
$s = sale_from_order(order(4, $T0, $C, 120, 1, [['at' => $T0 + 9 * DAY, 'paise' => $C]]));
check($s !== null && $s['base'] === $C, 'refund after 7 days does not change a confirmed sale');
$s = sale_from_order(order(5, $T0, $C, 120, 1, [['at' => $T0 + DAY, 'paise' => 100000]]));
check($s !== null && $s['base'] === $C - 100000, 'partial refund inside 7 days reduces base');
check(sale_from_order(order(6, $T0, $C))['confirm_at'] === $T0 + 7 * DAY, 'confirm time = paid + 7 days');
$mixed = ['order_id' => 7, 'paid_at' => $T0, 'self' => false, 'items' => [['product' => 120, 'qty' => 1, 'paid_paise' => $C], ['product' => 357, 'qty' => 50, 'paid_paise' => 2495000]], 'refunds' => []];
check(sale_from_order($mixed)['base'] === $C && sale_from_order($mixed)['units'] === 1, 'mixed cart counts only eligible items');

// ---- 3. month close: confirmation timing ----
$dec_end = strtotime('2026-12-31 23:59:59 UTC'); $jan_end = strtotime('2027-01-31 23:59:59 UTC');
$late = order(10, strtotime('2026-12-28 10:00 UTC'), $C);          // confirms 4 Jan
$rows = close_period([], [$late], '2026-12', $dec_end);
check($rows === [], 'sale on 28 Dec is not confirmed at Dec close');
$rows = close_period([], [$late], '2027-01', $jan_end);
check(count($rows) === 1 && $rows[0]['amount'] === 26991, 'it is credited at Jan close');

// ---- 4. idempotent close ----
$L = $rows;
check(close_period($L, [$late], '2027-01', $jan_end) === [], 'closing the same month twice adds nothing');

// ---- 5. tier jump with retroactive top-up ----
$orders = []; for ($i = 1; $i <= 24; $i++) $orders[] = order(100 + $i, $T0, $C);
$L = close_period([], $orders, '2026-12', $dec_end);
check(balance($L) === 24 * 26991, '24 sales at 10% = Rs 6,477.84');
$orders[] = order(125, strtotime('2027-01-05 UTC'), $C);
$new = close_period($L, $orders, '2027-01', $jan_end); $L = array_merge($L, $new);
check(balance($L) === 25 * 40487, '25th sale lifts all 25 to 15% = Rs 10,121.75');
$top = array_values(array_filter($new, fn($r) => $r['type'] === 'TOPUP'));
check(count($top) === 1 && $top[0]['amount'] === 24 * (40487 - 26991), 'top-up = difference on the first 24');

// ---- 6. payouts ----
$L1 = close_period([], [order(200, $T0, $C)], '2026-12', $dec_end);       // Rs 269.91
check(payout_due($L1, '2026-12') === 0, 'below Rs 500 is not paid');
$L1 = array_merge($L1, close_period($L1, [order(200, $T0, $C), order(201, strtotime('2027-01-02 UTC'), $C)], '2027-01', $jan_end));
check(payout_due($L1, '2027-01') === 53982, 'carried balance paid once it reaches Rs 500');
check(throws(fn() => record_payout($L1, '2027-01', 50000, 'UTR12345678')), 'wrong amount refused');
check(throws(fn() => record_payout($L1, '2027-01', 53982, "x';drop")), 'bad UTR refused');
$L1[] = record_payout($L1, '2027-01', 53982, 'UTR12345678');
check(balance($L1) === 0, 'balance zero after payout');
check(payout_due($L1, '2027-01') === 0 && throws(fn() => record_payout($L1, '2027-01', 53982, 'UTR99999999')), 'second payout in same period refused');
$exact = [['type' => 'ADJUST', 'period' => '2026-12', 'order_id' => null, 'amount' => 50000, 'rate_bp' => null, 'ref' => 'test']];
check(payout_due($exact, '2026-12') === 50000, 'exactly Rs 500 is paid');
check(payout_due([['type' => 'ADJUST', 'period' => '2026-12', 'order_id' => null, 'amount' => 49999, 'rate_bp' => null, 'ref' => 'test']], '2026-12') === 0, 'Rs 499.99 is not paid');
$st = statement($L1, '2027-01');
check($st['opening'] === 26991 && $st['credits'] === 26991 && $st['paid'] === 53982 && $st['closing'] === 0, 'statement adds up');

// ---- 7. property test: random histories against an independent reference ----
mt_srand(20261109);
$prods = [[120, 179910], [157, 269910], [226, 269910], [249, 179910], [286, 35910], [356, 1349100], [357, 449100]];
$months = []; for ($m = 0; $m < 8; $m++) { $months[] = [date('Y-m', strtotime("2026-11-01 +$m month")), strtotime(date('Y-m-t 23:59:59', strtotime("2026-11-01 +$m month")) . ' UTC')]; }
$runs = 3000; $bad = 0;
for ($run = 0; $run < $runs; $run++) {
    $n = mt_rand(0, 320); $orders = [];
    for ($i = 0; $i < $n; $i++) {
        [$pid, $price] = $prods[mt_rand(0, count($prods) - 1)];
        $paid_at = strtotime('2026-11-01 UTC') + mt_rand(0, 200) * DAY + mt_rand(0, 86399);
        $ref = []; $roll = mt_rand(1, 100);
        if ($roll <= 8) $ref[] = ['at' => $paid_at + mt_rand(0, 6) * DAY, 'paise' => $price];          // refund in window
        elseif ($roll <= 11) $ref[] = ['at' => $paid_at + mt_rand(8, 40) * DAY, 'paise' => $price];    // refund after
        elseif ($roll <= 13) $ref[] = ['at' => $paid_at + mt_rand(0, 6) * DAY, 'paise' => intdiv($price, 3)]; // partial
        $orders[] = order($i + 1, $paid_at, $price, $pid, 1, $ref, mt_rand(1, 100) <= 3);
    }
    $L = []; $ok = true; $prev_credit = 0;
    foreach ($months as [$p, $end]) {
        try { $new = close_period($L, $orders, $p, $end); } catch (\Throwable $e) { $ok = false; break; }
        $L = array_merge($L, $new);
        if (close_period($L, $orders, $p, $end) !== []) $ok = false;                 // idempotent
        $credit = 0; foreach ($L as $r) if ($r['type'] !== 'PAYOUT') $credit += $r['amount'];
        if ($credit < $prev_credit) $ok = false; $prev_credit = $credit;            // never decreases
        // independent reference: recompute from scratch, a different way
        $u = 0; $bases = [];
        foreach ($orders as $o) {
            if ($o['self']) continue; $it = $o['items'][0];
            if (!in_array($it['product'], [120, 157, 226, 249, 286], true)) continue;
            if ($o['paid_at'] + 7 * DAY > $end) continue;
            $inwin = 0; foreach ($o['refunds'] as $r) if ($r['at'] < $o['paid_at'] + 7 * DAY) $inwin += $r['paise'];
            if ($inwin >= $it['paid_paise']) continue;
            $u += $it['qty']; $bases[] = $it['paid_paise'] - $inwin;
        }
        $bp = $u >= 250 ? 2500 : ($u >= 100 ? 2000 : ($u >= 25 ? 1500 : 1000));
        $expect = 0; foreach ($bases as $b) $expect += (int) floor(($b * $bp) / 10000 + 0.5);
        if ($credit !== $expect) $ok = false;
        $due = payout_due($L, $p);
        if ($due > 0) { $L[] = record_payout($L, $p, $due, 'UTR' . $run . 'X' . str_replace('-', '', $p)); }
        if (balance($L) < 0) $ok = false;
        if ($due === 0 && balance($L) >= 50000 && !array_filter($L, fn($r) => $r['type'] === 'PAYOUT' && $r['period'] === $p)) $ok = false;
    }
    $paid = 0; foreach ($L as $r) if ($r['type'] === 'PAYOUT') $paid += $r['amount'];
    if ($paid + balance($L) !== $prev_credit) $ok = false;                            // money in = paid + owed
    if (!$ok) $bad++;
}
check($bad === 0, "property test: $runs random histories, $bad failed");

echo "passed $pass, failed $fail\n";
exit($fail === 0 ? 0 : 1);
