// UnAI: All-Access bundle (Claude, 2026-10-30, prices approved by Manish 2026-10-30)
// Link /?unai_bundle=all-access puts all 4 courses + the eBook in the cart.
// When all 5 are in the cart, a negative fee brings the total to Rs 5,999.
// Each course still enrols through its own normal product, so Tutor and checkout work unchanged.

if ( ! defined( 'ABSPATH' ) ) { return; }

function unai_bundle_ids() { return array( 120, 157, 226, 249, 286 ); }

add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['unai_bundle'] ) || 'all-access' !== sanitize_key( wp_unslash( $_GET['unai_bundle'] ) ) ) { return; }
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) { return; }
	$in_cart = array();
	foreach ( WC()->cart->get_cart() as $item ) { $in_cart[] = (int) $item['product_id']; }
	foreach ( unai_bundle_ids() as $pid ) {
		if ( ! in_array( $pid, $in_cart, true ) ) {
			$p = wc_get_product( $pid );
			if ( $p && $p->is_purchasable() ) { WC()->cart->add_to_cart( $pid, 1 ); }
		}
	}
	wp_safe_redirect( wc_get_cart_url() );
	exit;
} );

add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) { return; }
	$ids   = unai_bundle_ids();
	$found = array();
	$sum   = 0.0;
	foreach ( $cart->get_cart() as $item ) {
		$pid = (int) $item['product_id'];
		if ( in_array( $pid, $ids, true ) && ! isset( $found[ $pid ] ) ) {
			$found[ $pid ] = true;
			$qty  = max( 1, (int) $item['quantity'] );
			$sum += ( (float) $item['line_total'] + (float) $item['line_tax'] ) / $qty;
		}
	}
	if ( count( $found ) !== count( $ids ) ) { return; }
	$saving = round( $sum - 5999, 2 );
	if ( $saving > 0 ) {
		$cart->add_fee( 'All-Access bundle saving', -$saving, false );
	}
}, 20 );
