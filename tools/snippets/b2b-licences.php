// UnAI: B2B team + campus licences (Claude, 2026-10-30, prices approved by Manish 2026-10-30)
// Products: 356 Team licence (Rs 1,499/seat, min 10, Rs 999/seat from 100), 357 Campus licence (Rs 499/student, min 50).
// After the order is paid (processing or completed), one access code is created: 100% off the All-Access
// bundle (4 courses + eBook), usable once per person, as many times as seats bought. The buyer gets it by email.
// Staff/students redeem at /?unai_bundle=all-access, apply the code, total Rs 0.

if ( ! defined( 'ABSPATH' ) ) { return; }

function unai_lic_rules() {
	return array(
		356 => array( 'min' => 10, 'prefix' => 'TEAM' ),
		357 => array( 'min' => 50, 'prefix' => 'CAMPUS' ),
	);
}

add_action( 'woocommerce_check_cart_items', function () {
	if ( ! WC()->cart ) { return; }
	$rules = unai_lic_rules();
	foreach ( WC()->cart->get_cart() as $item ) {
		$pid = (int) $item['product_id'];
		if ( isset( $rules[ $pid ] ) && (int) $item['quantity'] < $rules[ $pid ]['min'] ) {
			wc_add_notice( sprintf( 'The %s needs at least %d seats. Please update the quantity in your cart.', esc_html( $item['data']->get_name() ), $rules[ $pid ]['min'] ), 'error' );
		}
	}
} );

add_filter( 'woocommerce_quantity_input_args', function ( $args, $product ) {
	$rules = unai_lic_rules();
	if ( $product && isset( $rules[ $product->get_id() ] ) ) {
		$args['min_value'] = $rules[ $product->get_id() ]['min'];
		if ( empty( $args['input_value'] ) || $args['input_value'] < $args['min_value'] ) { $args['input_value'] = $args['min_value']; }
	}
	return $args;
}, 10, 2 );

add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) { return; }
	foreach ( $cart->get_cart() as $item ) {
		if ( 356 === (int) $item['product_id'] ) {
			$item['data']->set_price( (int) $item['quantity'] >= 100 ? 999 : 1499 );
		}
	}
}, 20 );

function unai_lic_issue_code( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_unai_licence_code' ) ) { return; }
	$rules  = unai_lic_rules();
	$seats  = 0;
	$prefix = 'TEAM';
	foreach ( $order->get_items() as $item ) {
		$pid = (int) $item->get_product_id();
		if ( isset( $rules[ $pid ] ) ) { $seats += (int) $item->get_quantity(); $prefix = $rules[ $pid ]['prefix']; }
	}
	if ( $seats < 1 ) { return; }
	$code   = strtoupper( $prefix . '-' . wp_generate_password( 8, false, false ) );
	$coupon = new WC_Coupon();
	$coupon->set_code( $code );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 100 );
	$coupon->set_product_ids( array( 120, 157, 226, 249, 286 ) );
	$coupon->set_usage_limit( $seats );
	$coupon->set_usage_limit_per_user( 1 );
	$coupon->set_individual_use( true );
	$coupon->set_description( sprintf( 'B2B access code for order #%d (%d seats). Auto-created.', $order_id, $seats ) );
	$coupon->save();
	$order->update_meta_data( '_unai_licence_code', $code );
	$order->add_order_note( sprintf( 'Access code %s created for %d seats.', $code, $seats ) );
	$order->save();

	$link    = home_url( '/?unai_bundle=all-access' );
	$message = sprintf(
		'<p>Thank you for choosing UnAI Labs.</p><p>Your access code for %1$d people is: <strong>%2$s</strong></p><p>Share these steps with your team or students:</p><ol><li>Open %3$s (this puts all four courses and the eBook in the cart).</li><li>Create an account or log in at checkout.</li><li>Enter the code %2$s in the coupon box. The total becomes Rs 0.</li><li>Place the order. The courses appear under My Courses.</li></ol><p>Each person can use the code once, up to %1$d people in total. Questions: reply to this email.</p>',
		$seats, esc_html( $code ), esc_url( $link )
	);
	$mailer = WC()->mailer();
	$mailer->send( $order->get_billing_email(), 'Your UnAI Labs access code', $mailer->wrap_message( 'Your access code', $message ) );
}
add_action( 'woocommerce_order_status_processing', 'unai_lic_issue_code' );
add_action( 'woocommerce_order_status_completed', 'unai_lic_issue_code' );
