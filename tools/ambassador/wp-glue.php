
/* ===================== WordPress glue (Snippet: UnAI ambassador ledger) =====================
 * Built by tools/ambassador/build-snippet.sh = ledger.php engine + this file. Do not edit the
 * generated snippet by hand. Design: claude/AMBASSADOR_ONBOARDING_SPEC.md v3.
 * Security: every admin action needs manage_woocommerce + a nonce; every query is prepared;
 * every output escaped; ambassadors only ever see their own rows (no IDs taken from requests).
 */
namespace UnaiAmbWP;
use function UnaiAmb\{close_period, payout_due, record_payout, statement, balance};

if ( ! defined( 'ABSPATH' ) ) { return; }
const DB_VERSION = '1';

function table() { global $wpdb; return $wpdb->prefix . 'unai_amb_ledger'; }

/* ---------- schema ---------- */
\add_action( 'admin_init', function () {
	if ( \get_option( 'unai_amb_ledger_db' ) === DB_VERSION ) { return; }
	global $wpdb; require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t = table(); $c = $wpdb->get_charset_collate();
	\dbDelta( "CREATE TABLE $t (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		amb_user BIGINT UNSIGNED NOT NULL,
		type VARCHAR(10) NOT NULL,
		period CHAR(7) NOT NULL,
		order_id BIGINT UNSIGNED NULL,
		amount BIGINT NOT NULL,
		rate_bp INT NULL,
		ref VARCHAR(64) NULL,
		created_at DATETIME NOT NULL,
		created_by BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY one_credit (amb_user, type, order_id),
		KEY amb_period (amb_user, period)
	) $c;" );
	\update_option( 'unai_amb_ledger_db', DB_VERSION, false );
} );

function rows_for( int $uid ): array {
	global $wpdb;
	$r = $wpdb->get_results( $wpdb->prepare( 'SELECT type, period, order_id, amount, rate_bp, ref FROM ' . table() . ' WHERE amb_user = %d ORDER BY id', $uid ), ARRAY_A );
	return array_map( fn( $x ) => [ 'type' => $x['type'], 'period' => $x['period'], 'order_id' => $x['order_id'] === null ? null : (int) $x['order_id'], 'amount' => (int) $x['amount'], 'rate_bp' => $x['rate_bp'] === null ? null : (int) $x['rate_bp'], 'ref' => $x['ref'] ], $r ?: [] );
}

/* ---------- orders -> engine input ---------- */
function orders_for( int $uid ): array {
	$cid = (int) \get_user_meta( $uid, '_unai_amb_coupon_id', true ); if ( ! $cid ) { return []; }
	$code = \wc_get_coupon_code_by_id( $cid ); if ( ! $code ) { return []; }
	$out = [];
	foreach ( \wc_get_orders( [ 'limit' => -1, 'status' => [ 'processing', 'completed', 'refunded' ], 'meta_key' => '_unai_amb_code', 'meta_value' => $code ] ) as $o ) {
		$paid = $o->get_date_paid(); if ( ! $paid ) { continue; }
		$items = [];
		foreach ( $o->get_items() as $it ) { $items[] = [ 'product' => (int) $it->get_product_id(), 'qty' => (int) $it->get_quantity(), 'paid_paise' => (int) round( (float) $it->get_total() * 100 ) ]; }
		// Order-level discounts stored as negative fees (e.g. All-Access bundle saving) reduce the
		// eligible items' base in proportion, so commission is always on what the buyer really paid.
		$neg = 0; foreach ( $o->get_fees() as $fee ) { $f = (int) round( (float) $fee->get_total() * 100 ); if ( $f < 0 ) { $neg += -$f; } }
		if ( $neg > 0 ) {
			$all = array_sum( array_column( $items, 'paid_paise' ) );
			foreach ( $items as &$it2 ) { if ( $all > 0 ) { $it2['paid_paise'] = max( 0, $it2['paid_paise'] - intdiv( $it2['paid_paise'] * $neg + intdiv( $all, 2 ), $all ) ); } }
			unset( $it2 );
		}
		$refunds = [];
		foreach ( $o->get_refunds() as $rf ) { $d = $rf->get_date_created(); $refunds[] = [ 'at' => $d ? $d->getTimestamp() : time(), 'paise' => (int) round( abs( (float) $rf->get_amount() ) * 100 ) ]; }
		$out[] = [ 'order_id' => $o->get_id(), 'paid_at' => $paid->getTimestamp(), 'self' => (bool) $o->get_meta( '_unai_amb_self' ), 'items' => $items, 'refunds' => $refunds ];
	}
	return $out;
}

function ambassadors(): array {
	return \get_users( [ 'meta_key' => '_unai_amb_coupon_id', 'meta_compare' => 'EXISTS', 'fields' => 'ID' ] );
}

function insert( int $uid, array $row ): void {
	global $wpdb;
	$ok = $wpdb->insert( table(), [ 'amb_user' => $uid, 'type' => $row['type'], 'period' => $row['period'], 'order_id' => $row['order_id'], 'amount' => $row['amount'], 'rate_bp' => $row['rate_bp'], 'ref' => $row['ref'], 'created_at' => \current_time( 'mysql', true ), 'created_by' => \get_current_user_id() ],
		[ '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d' ] );
	if ( ! $ok ) { throw new \RuntimeException( 'ledger insert failed: ' . $wpdb->last_error ); }
}

/* Close one month for every ambassador, inside a transaction. Returns rows written. */
function close_month( string $period ): int {
	if ( ! preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $period ) ) { throw new \InvalidArgumentException( 'bad period' ); }
	$tz = new \DateTimeZone( 'Asia/Kolkata' );
	$end = ( new \DateTimeImmutable( $period . '-01 00:00:00', $tz ) )->modify( 'first day of next month' )->getTimestamp() - 1;
	if ( $end >= time() ) { throw new \RuntimeException( 'month not over yet' ); }
	global $wpdb; $n = 0;
	$wpdb->query( 'START TRANSACTION' );
	try {
		foreach ( ambassadors() as $uid ) {
			foreach ( close_period( rows_for( (int) $uid ), orders_for( (int) $uid ), $period, $end ) as $row ) { insert( (int) $uid, $row ); $n++; }
		}
		$wpdb->query( 'COMMIT' );
	} catch ( \Throwable $e ) { $wpdb->query( 'ROLLBACK' ); throw $e; }
	return $n;
}

function mask_upi( string $upi ): string { $p = explode( '@', $upi ); return strlen( $p[0] ) > 3 ? substr( $p[0], 0, 2 ) . str_repeat( '*', strlen( $p[0] ) - 3 ) . substr( $p[0], -1 ) . '@' . ( $p[1] ?? '' ) : '***@' . ( $p[1] ?? '' ); }
function rs( int $paise ): string { return 'Rs ' . number_format( $paise / 100, 2 ); }

/* ---------- admin: WooCommerce > Ambassador payouts ---------- */
\add_action( 'admin_menu', function () { \add_submenu_page( 'woocommerce', 'Ambassador payouts', 'Ambassador payouts', 'manage_woocommerce', 'unai-amb-payouts', __NAMESPACE__ . '\\admin_page' ); } );

function admin_page() {
	if ( ! \current_user_can( 'manage_woocommerce' ) ) { \wp_die( 'Not allowed' ); }
	$prev = ( new \DateTimeImmutable( 'first day of last month', new \DateTimeZone( 'Asia/Kolkata' ) ) )->format( 'Y-m' );
	$msg = '';
	if ( isset( $_POST['unai_amb_do'] ) && \check_admin_referer( 'unai_amb_payouts' ) ) {
		try {
			$do = \sanitize_key( $_POST['unai_amb_do'] );
			if ( $do === 'close' ) { $msg = 'Closed ' . $prev . ': ' . close_month( $prev ) . ' rows written.'; }
			if ( $do === 'pay' ) {
				$uid = \absint( $_POST['uid'] ?? 0 ); $utr = \sanitize_text_field( \wp_unslash( $_POST['utr'] ?? '' ) );
				if ( ! $uid || ! \get_user_meta( $uid, '_unai_amb_coupon_id', true ) ) { throw new \RuntimeException( 'not an ambassador' ); }
				$rows = rows_for( $uid ); $due = payout_due( $rows, $prev );
				$row = record_payout( $rows, $prev, $due, $utr );   // amount comes from the ledger, never from the form
				insert( $uid, $row );
				$u = \get_userdata( $uid );
				\wp_mail( $u->user_email, 'UnAI Labs ambassador payout: ' . rs( $due ), "Hi,\n\nWe have sent " . rs( $due ) . " to your UPI ID for $prev.\nUPI reference (UTR): $utr\n\nYour full statement is on your dashboard: " . \home_url( '/campus-ambassadors/' ) . "\n\nQuestions: manish.singh@unai-labs.com\nUnAI Labs" );
				$msg = 'Payout recorded for user ' . $uid . '.';
			}
		} catch ( \Throwable $e ) { $msg = 'Refused: ' . $e->getMessage(); }
	}
	echo '<div class="wrap"><h1>Ambassador payouts</h1>';
	if ( $msg ) { echo '<div class="notice notice-info"><p>' . \esc_html( $msg ) . '</p></div>'; }
	echo '<p>Month to close and pay: <strong>' . \esc_html( $prev ) . '</strong>. Closing twice adds nothing. Amounts come from the ledger; you only enter the UPI reference after paying.</p>';
	echo '<form method="post">' . \wp_nonce_field( 'unai_amb_payouts', '_wpnonce', true, false ) . '<button class="button button-primary" name="unai_amb_do" value="close">Close ' . \esc_html( $prev ) . '</button></form><br>';
	echo '<table class="widefat striped"><thead><tr><th>Ambassador</th><th>UPI (masked)</th><th>Opening</th><th>Credits</th><th>Top-ups</th><th>Paid</th><th>Due now</th><th>Record payout</th></tr></thead><tbody>';
	foreach ( ambassadors() as $uid ) {
		$rows = rows_for( (int) $uid ); $s = statement( $rows, $prev ); $due = payout_due( $rows, $prev );
		$u = \get_userdata( $uid ); $upi = (string) \get_user_meta( $uid, '_unai_amb_upi', true );
		$form = $due > 0 && $upi ? '<form method="post">' . \wp_nonce_field( 'unai_amb_payouts', '_wpnonce', true, false ) . '<input type="hidden" name="uid" value="' . (int) $uid . '"><input name="utr" required pattern="[A-Za-z0-9]{8,30}" placeholder="UTR" style="width:130px"> <button class="button" name="unai_amb_do" value="pay">Paid</button></form>' : ( $due > 0 ? 'No UPI ID yet' : '-' );
		printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><strong>%s</strong></td><td>%s</td></tr>', \esc_html( $u ? $u->display_name : '#' . $uid ), \esc_html( $upi ? mask_upi( $upi ) : '' ), \esc_html( rs( $s['opening'] ) ), \esc_html( rs( $s['credits'] ) ), \esc_html( rs( $s['topups'] ) ), \esc_html( rs( $s['paid'] ) ), \esc_html( rs( $due ) ), $form );
	}
	echo '</tbody></table><p>Pay each line from your UPI app to the full UPI ID shown on the user\'s profile in WooCommerce &gt; Ambassadors, then paste the UTR here.</p></div>';
}

/* ---------- ambassador's own statement: [unai_ambassador_statement] ---------- */
\add_shortcode( 'unai_ambassador_statement', function () {
	if ( ! \is_user_logged_in() ) { return ''; }
	$uid = \get_current_user_id();                        // never from the request
	if ( ! \get_user_meta( $uid, '_unai_amb_coupon_id', true ) ) { return ''; }
	$rows = rows_for( $uid ); if ( ! $rows ) { return '<p>Your statement appears here after your first confirmed sale and month close.</p>'; }
	$labels = [ 'CREDIT' => 'Sale confirmed', 'TOPUP' => 'Tier top-up', 'PAYOUT' => 'Paid by UPI', 'ADJUST' => 'Adjustment' ];
	$h = '<h3>Your statement</h3><table><tr><th>Month</th><th>Entry</th><th>Amount</th><th>Reference</th></tr>';
	foreach ( $rows as $r ) {
		$h .= '<tr><td>' . \esc_html( $r['period'] ) . '</td><td>' . \esc_html( $labels[ $r['type'] ] ?? $r['type'] ) . ( $r['rate_bp'] ? ' (' . (int) ( $r['rate_bp'] / 100 ) . '%)' : '' ) . '</td><td>' . \esc_html( ( $r['type'] === 'PAYOUT' ? '-' : '' ) . rs( $r['amount'] ) ) . '</td><td>' . \esc_html( $r['type'] === 'PAYOUT' ? (string) $r['ref'] : '' ) . '</td></tr>';
	}
	return $h . '<tr><td colspan="2"><strong>Balance due</strong></td><td><strong>' . \esc_html( rs( balance( $rows ) ) ) . '</strong></td><td></td></tr></table>';
} );
