<?php
/** Smoke + security test of the generated snippet against stubbed WordPress/WooCommerce.
 *  Run: php tools/ambassador/test_glue.php   (exit 0 = pass). Real integration runs on staging. */
define( 'ABSPATH', '/tmp/' ); define( 'ARRAY_A', 'ARRAY_A' );
$H = [ 'actions' => [], 'short' => [], 'menu' => [], 'mail' => [], 'opt' => [] ];
$CUR = 1; $CAN = true; $NONCE_OK = true;
$META = [ 5 => [ '_unai_amb_coupon_id' => 900, '_unai_amb_upi' => 'rahulsharma@okhdfcbank' ], 6 => [] ];
$USERS = [ 5 => (object) [ 'ID' => 5, 'display_name' => '<script>x</script>Rahul', 'user_email' => 'r@x.ac.in' ], 6 => (object) [ 'ID' => 6, 'display_name' => 'Other', 'user_email' => 'o@x.in' ] ];
function add_action( $h, $cb ) { global $H; $H['actions'][ $h ][] = $cb; }
function add_shortcode( $t, $cb ) { global $H; $H['short'][ $t ] = $cb; }
function add_submenu_page( ...$a ) { global $H; $H['menu'][] = $a[5]; }
function get_option( $k ) { global $H; return $H['opt'][ $k ] ?? false; }
function update_option( $k, $v ) { global $H; $H['opt'][ $k ] = $v; }
function get_user_meta( $u, $k, $s ) { global $META; return $META[ $u ][ $k ] ?? ''; }
function get_users( $a ) { global $META; return array_keys( array_filter( $META, fn( $m ) => isset( $m['_unai_amb_coupon_id'] ) ) ); }
function get_userdata( $u ) { global $USERS; return $USERS[ $u ] ?? false; }
function wc_get_coupon_code_by_id( $id ) { return $id === 900 ? 'RAHULAB12CD' : ''; }
function current_time( $t, $g ) { return gmdate( 'Y-m-d H:i:s' ); }
function get_current_user_id() { global $CUR; return $CUR; }
function is_user_logged_in() { global $CUR; return $CUR > 0; }
function current_user_can( $c ) { global $CAN; return $CAN; }
function check_admin_referer( $a ) { global $NONCE_OK; if ( ! $NONCE_OK ) { throw new Exception( 'nonce' ); } return true; }
function sanitize_key( $k ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $k ) ); }
function sanitize_text_field( $s ) { return trim( strip_tags( $s ) ); }
function wp_unslash( $s ) { return $s; }
function absint( $n ) { return abs( (int) $n ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function wp_nonce_field( ...$a ) { return '<input type="hidden" name="_wpnonce" value="n">'; }
function wp_mail( $to, $s, $b ) { global $H; $H['mail'][] = [ $to, $s, $b ]; return true; }
function home_url( $p = '' ) { return 'https://example.test' . $p; }
function wp_die( $m ) { throw new Exception( 'die: ' . $m ); }
class D { private $t; function __construct( $t ) { $this->t = $t; } function getTimestamp() { return $this->t; } }
class It { function __construct( public $p, public $q, public $tot ) {} function get_product_id() { return $this->p; } function get_quantity() { return $this->q; } function get_total() { return $this->tot; } }
class Fee { function __construct( public $t ) {} function get_total() { return $this->t; } }
class Rf { function __construct( public $a, public $t ) {} function get_amount() { return $this->a; } function get_date_created() { return new D( $this->t ); } }
class Ord { function __construct( public $id, public $paid, public $items, public $fees = [], public $refunds = [], public $self = false ) {}
	function get_id() { return $this->id; } function get_date_paid() { return new D( $this->paid ); } function get_items() { return $this->items; }
	function get_fees() { return $this->fees; } function get_refunds() { return $this->refunds; } function get_meta( $k ) { return $k === '_unai_amb_self' ? $this->self : ''; } }
$OCT = strtotime( '2026-10-05 10:00:00 Asia/Kolkata' );
$ORDERS = [
	new Ord( 1, $OCT, [ new It( 120, 1, 1799.10 ) ] ),                                             // course, counts
	new Ord( 2, $OCT, [ new It( 157, 1, 1799.10 ) ], [], [ new Rf( 1799.10, $OCT + 2 * 86400 ) ] ),   // refunded in window
	new Ord( 3, $OCT, [ new It( 226, 1, 1799.10 ) ], [], [], true ),                                // self purchase
	new Ord( 4, $OCT, [ new It( 120, 1, 1799.10 ), new It( 286, 1, 359.10 ) ], [ new Fee( -500.00 ) ] ), // bundle-style saving
];
function wc_get_orders( $a ) { global $ORDERS; return $a['meta_value'] === 'RAHULAB12CD' ? $ORDERS : []; }
class FakeDB { public $prefix = 'wp_'; public $last_error = ''; public $rows = []; public $q = [];
	function get_charset_collate() { return ''; }
	function prepare( $sql, ...$a ) { return [ $sql, $a ]; }
	function get_results( $p, $f ) { [ $sql, $a ] = $p; return array_values( array_filter( $this->rows, fn( $r ) => $r['amb_user'] == $a[0] ) ); }
	function insert( $t, $row, $fmt ) { foreach ( $this->rows as $r ) { if ( $r['type'] === 'CREDIT' && $row['type'] === 'CREDIT' && $r['amb_user'] == $row['amb_user'] && $r['order_id'] == $row['order_id'] ) { $this->last_error = 'Duplicate'; return false; } } $this->rows[] = $row; return 1; }
	function query( $s ) { $this->q[] = $s; if ( $s === 'START TRANSACTION' ) { $this->snap = $this->rows; } if ( $s === 'ROLLBACK' ) { $this->rows = $this->snap; } return true; } }
$wpdb = new FakeDB();

eval( '?>' . file_get_contents( __DIR__ . '/../snippets/ambassador-ledger.php' ) );

$pass = 0; $fail = 0;
function ok( $c, $n ) { global $pass, $fail; if ( $c ) { $pass++; } else { $fail++; echo "FAIL: $n\n"; } }
function page( array $post = [] ) { $_POST = $post; ob_start(); try { ( 'UnaiAmbWP\\admin_page' )(); } catch ( Throwable $e ) { ob_end_clean(); return 'EXC:' . $e->getMessage(); } return ob_get_clean(); }

ok( isset( $H['short']['unai_ambassador_statement'] ) && count( $H['actions']['admin_menu'] ?? [] ) === 1, 'shortcode and admin page registered' );
// close Oct 2026 directly
$n = UnaiAmbWP\close_month( '2026-10' );
$credits = array_values( array_filter( $wpdb->rows, fn( $r ) => $r['type'] === 'CREDIT' ) );
ok( $n === 2 && count( $credits ) === 2, 'only orders 1 and 4 credited (refund and self excluded)' );
// order 4: items 179910 + 35910 = 215820, saving 50000 -> base 165820 -> 10% = 16582
ok( $credits[0]['amount'] === 17991 && $credits[1]['amount'] === 16582, 'amounts: 179.91 and bundle-reduced 165.82' );
ok( UnaiAmbWP\close_month( '2026-10' ) === 0, 'second close adds nothing' );
ok( str_starts_with( (string) ( function () { try { UnaiAmbWP\close_month( gmdate( 'Y-m' ) ); return 'no'; } catch ( Throwable $e ) { return 'refused'; } } )(), 'refused' ), 'current month cannot be closed' );
ok( ( function () { try { UnaiAmbWP\close_month( "2026-10' OR 1=1" ); return false; } catch ( Throwable $e ) { return true; } } )(), 'malformed period refused' );
// total 345.73 is below Rs 500: nothing due
ok( UnaiAmb\payout_due( UnaiAmbWP\rows_for( 5 ), '2026-10' ) === 0, 'Rs 345.73 not paid (below Rs 500)' );
// add a manual adjustment to cross Rs 500 for the payout test
$wpdb->rows[] = [ 'amb_user' => 5, 'type' => 'ADJUST', 'period' => '2026-10', 'order_id' => null, 'amount' => 20000, 'rate_bp' => null, 'ref' => 'test', 'created_at' => '', 'created_by' => 1 ];
// security: no capability
$CAN = false; ok( str_starts_with( page(), 'EXC:die' ), 'non-admin cannot open payouts page' ); $CAN = true;
// security: bad nonce
$NONCE_OK = false; ok( str_starts_with( page( [ 'unai_amb_do' => 'pay', 'uid' => 5, 'utr' => 'ABCD12345678' ] ), 'EXC:nonce' ), 'bad nonce refused' ); $NONCE_OK = true;
// pay for a non-ambassador
ok( str_contains( page( [ 'unai_amb_do' => 'pay', 'uid' => 6, 'utr' => 'ABCD12345678' ] ), 'Refused: not an ambassador' ), 'payout to non-ambassador refused' );
// the form cannot set the amount: post an amount field, it is ignored
$_prevmonth = ( new DateTimeImmutable( 'first day of last month', new DateTimeZone( 'Asia/Kolkata' ) ) )->format( 'Y-m' );
// move rows into "last month" so the page acts on them
foreach ( $wpdb->rows as &$r ) { $r['period'] = $_prevmonth; } unset( $r );
$out = page( [ 'unai_amb_do' => 'pay', 'uid' => 5, 'utr' => 'ABCD12345678', 'amount' => '999999' ] );
$p = array_values( array_filter( $wpdb->rows, fn( $r ) => $r['type'] === 'PAYOUT' ) );
ok( count( $p ) === 1 && $p[0]['amount'] === 54573, 'payout = ledger balance Rs 545.73, form amount ignored' );
ok( count( $H['mail'] ) === 1 && str_contains( $H['mail'][0][1], 'Rs 545.73' ) && $H['mail'][0][0] === 'r@x.ac.in', 'payout email to the ambassador' );
ok( str_contains( page( [ 'unai_amb_do' => 'pay', 'uid' => 5, 'utr' => 'ZZZZ12345678' ] ), 'Refused' ), 'second payout in same month refused' );
ok( str_contains( page( [ 'unai_amb_do' => 'pay', 'uid' => 5, 'utr' => "x'><script>" ] ), 'Refused' ), 'bad UTR refused' );
$html = page();
ok( ! str_contains( $html, '<script>x</script>' ) && str_contains( $html, '&lt;script&gt;' ), 'names escaped on admin page' );
ok( ! str_contains( $html, 'rahulsharma@' ) && str_contains( $html, 'ra********a@okhdfcbank' ), 'UPI masked on admin page' );
// shortcode: own data only
$CUR = 5; $s = $H['short']['unai_ambassador_statement'](); ok( str_contains( $s, 'Balance due' ) && str_contains( $s, 'ABCD12345678' ), 'ambassador sees own statement' );
$_GET['uid'] = 6; $CUR = 6; ok( $H['short']['unai_ambassador_statement']() === '', 'non-ambassador sees nothing (request uid ignored)' );
$CUR = 0; ok( $H['short']['unai_ambassador_statement']() === '', 'logged out sees nothing' );
echo "passed $pass, failed $fail\n"; exit( $fail ? 1 : 0 );
