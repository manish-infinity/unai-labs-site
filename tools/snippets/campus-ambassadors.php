<?php
/**
 * UnAI Campus Ambassadors (Snippet #17, approved by Manish 2026-11-02)
 *
 * - Each ambassador = one WooCommerce coupon (10% off for the buyer, individual use,
 *   courses + eBook only, never team/campus licences) tagged with meta _unai_ambassador.
 * - Commission tiers on lifetime eligible units sold with the code:
 *     1-24: 10%   25-99: 15%   100-249: 20%   250+: 25% (cap)
 *   Commission base = what the buyer actually paid for eligible items.
 * - Guardrail: we always keep >= 60% of what was paid after commission and ~2.36%
 *   payment fees. With a 25% cap this always holds; the check stays in as a safety net.
 * - Earned only on Completed orders older than 7 days (refund window). Refunded or
 *   cancelled orders never count. Self-purchase by the ambassador's own email never counts.
 * - Admin screen: WooCommerce > Ambassadors (create ambassador, see units, tier, earned).
 * - Public shortcode [unai_ambassador_apply]: application form (nonce, honeypot, rate
 *   limit), stored privately and emailed to the business mailbox.
 * No customer names, emails or order data are ever printed on public pages.
 */

if ( ! defined( 'ABSPATH' ) ) { return; }

const UNAI_AMB_ELIGIBLE  = array( 120, 157, 226, 249, 286 ); // 4 courses + eBook
const UNAI_AMB_EXCLUDED  = array( 311, 356, 357 );           // test product, team + campus licences
const UNAI_AMB_DISCOUNT  = 10;                                 // buyer discount %
const UNAI_AMB_FEE_RATE  = 0.0236;                             // Razorpay ~2% + GST
const UNAI_AMB_MIN_KEEP  = 0.60;                               // we keep at least 60% of paid
const UNAI_AMB_NOTIFY_TO = 'manish.singh@unai-labs.com';

function unai_amb_rate_for_units( $units ) {
	if ( $units >= 250 ) { return 0.25; }
	if ( $units >= 100 ) { return 0.20; }
	if ( $units >= 25 )  { return 0.15; }
	return 0.10;
}

/** Record ambassador data on the order when it is paid. */
add_action( 'woocommerce_order_status_changed', function ( $order_id, $from, $to, $order ) {
	if ( ! in_array( $to, array( 'processing', 'completed' ), true ) ) { return; }
	if ( $order->get_meta( '_unai_amb_code' ) ) { return; }
	foreach ( $order->get_coupon_codes() as $code ) {
		$coupon = new WC_Coupon( $code );
		if ( ! $coupon->get_id() || ! $coupon->get_meta( '_unai_ambassador' ) ) { continue; }
		// No commission on the ambassador's own purchase.
		$amb_email = strtolower( (string) $coupon->get_meta( '_unai_amb_email' ) );
		if ( $amb_email && strtolower( $order->get_billing_email() ) === $amb_email ) {
			$order->update_meta_data( '_unai_amb_code', $code );
			$order->update_meta_data( '_unai_amb_self', 1 );
			$order->save();
			return;
		}
		$paid = 0.0; $units = 0;
		foreach ( $order->get_items() as $item ) {
			$pid = (int) $item->get_product_id();
			if ( in_array( $pid, UNAI_AMB_ELIGIBLE, true ) ) {
				$paid  += (float) $item->get_total();
				$units += (int) $item->get_quantity();
			}
		}
		// Bundle saving fee (Snippet #14) reduces what was paid; include negative fees.
		foreach ( $order->get_fees() as $fee ) {
			if ( (float) $fee->get_total() < 0 ) { $paid += (float) $fee->get_total(); }
		}
		$order->update_meta_data( '_unai_amb_code', $code );
		$order->update_meta_data( '_unai_amb_paid', max( 0, round( $paid, 2 ) ) );
		$order->update_meta_data( '_unai_amb_units', $units );
		$order->save();
		return;
	}
}, 10, 4 );

/** Aggregate stats for one ambassador code. */
function unai_amb_stats( $code ) {
	$orders = wc_get_orders( array(
		'limit'      => -1,
		'status'     => array( 'processing', 'completed' ),
		'meta_key'   => '_unai_amb_code',
		'meta_value' => $code,
	) );
	$units = 0; $paid = 0.0; $earned = 0.0; $pending = 0.0; $cutoff = time() - 7 * DAY_IN_SECONDS;
	// First pass: lifetime units decide the tier.
	foreach ( $orders as $o ) {
		if ( $o->get_meta( '_unai_amb_self' ) ) { continue; }
		$units += (int) $o->get_meta( '_unai_amb_units' );
	}
	$rate = unai_amb_rate_for_units( $units );
	foreach ( $orders as $o ) {
		if ( $o->get_meta( '_unai_amb_self' ) ) { continue; }
		$p = (float) $o->get_meta( '_unai_amb_paid' );
		$paid += $p;
		$commission = $p * $rate;
		// Guardrail: never let what we keep fall under 60% of what was paid.
		$max_commission = $p - ( $p * UNAI_AMB_FEE_RATE ) - ( $p * UNAI_AMB_MIN_KEEP );
		$commission = max( 0, min( $commission, $max_commission ) );
		$done = $o->get_status() === 'completed' && $o->get_date_paid() && $o->get_date_paid()->getTimestamp() < $cutoff;
		if ( $done ) { $earned += $commission; } else { $pending += $commission; }
	}
	return compact( 'units', 'paid', 'rate', 'earned', 'pending' );
}

/** Create an ambassador coupon. */
function unai_amb_create( $name, $email, $college, $code ) {
	$code = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $code ) );
	if ( strlen( $code ) < 4 || wc_get_coupon_id_by_code( $code ) ) { return new WP_Error( 'code', 'Code is too short or already exists.' ); }
	$c = new WC_Coupon();
	$c->set_code( $code );
	$c->set_discount_type( 'percent' );
	$c->set_amount( UNAI_AMB_DISCOUNT );
	$c->set_individual_use( true );
	$c->set_usage_limit_per_user( 1 );
	$c->set_exclude_sale_items( false );
	$c->set_product_ids( UNAI_AMB_ELIGIBLE );
	$c->set_excluded_product_ids( UNAI_AMB_EXCLUDED );
	$c->set_description( 'Campus ambassador: ' . $name . ' (' . $college . ')' );
	$c->update_meta_data( '_unai_ambassador', 1 );
	$c->update_meta_data( '_unai_amb_name', $name );
	$c->update_meta_data( '_unai_amb_email', $email );
	$c->update_meta_data( '_unai_amb_college', $college );
	$c->save();
	return $c->get_id();
}

/** Admin screen: WooCommerce > Ambassadors. */
add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', 'Campus Ambassadors', 'Ambassadors', 'manage_woocommerce', 'unai-ambassadors', 'unai_amb_admin_page' );
} );

function unai_amb_admin_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
	$msg = '';
	if ( isset( $_POST['unai_amb_new'] ) && check_admin_referer( 'unai_amb_new' ) ) {
		$r = unai_amb_create(
			sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['college'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) )
		);
		$msg = is_wp_error( $r ) ? $r->get_error_message() : 'Ambassador created.';
	}
	$coupons = get_posts( array( 'post_type' => 'shop_coupon', 'numberposts' => -1, 'meta_key' => '_unai_ambassador', 'meta_value' => 1 ) );
	echo '<div class="wrap"><h1>Campus Ambassadors</h1>';
	if ( $msg ) { echo '<div class="notice notice-info"><p>' . esc_html( $msg ) . '</p></div>'; }
	echo '<p>Tiers on lifetime units: 1-24 = 10%, 25-99 = 15%, 100-249 = 20%, 250+ = 25% (cap). Buyer gets ' . esc_html( UNAI_AMB_DISCOUNT ) . '% off courses and eBook. Earned = completed orders older than 7 days. We always keep at least 60% of what was paid.</p>';
	echo '<table class="widefat striped"><thead><tr><th>Code</th><th>Name</th><th>College</th><th>Units</th><th>Paid (Rs)</th><th>Tier</th><th>Earned (Rs)</th><th>Pending (Rs)</th></tr></thead><tbody>';
	foreach ( $coupons as $p ) {
		$c = new WC_Coupon( $p->ID ); $s = unai_amb_stats( $c->get_code() );
		printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%d</td><td>%s</td><td>%d%%</td><td>%s</td><td>%s</td></tr>',
			esc_html( strtoupper( $c->get_code() ) ), esc_html( $c->get_meta( '_unai_amb_name' ) ), esc_html( $c->get_meta( '_unai_amb_college' ) ),
			(int) $s['units'], esc_html( number_format( $s['paid'], 0 ) ), (int) round( $s['rate'] * 100 ), esc_html( number_format( $s['earned'], 0 ) ), esc_html( number_format( $s['pending'], 0 ) ) );
	}
	if ( ! $coupons ) { echo '<tr><td colspan="8">No ambassadors yet.</td></tr>'; }
	echo '</tbody></table><h2>Add ambassador</h2><form method="post">';
	wp_nonce_field( 'unai_amb_new' );
	echo '<p><input name="name" placeholder="Full name" required> <input name="email" type="email" placeholder="Email" required> <input name="college" placeholder="College" required> <input name="code" placeholder="CODE e.g. PRIYA10" required> <button class="button button-primary" name="unai_amb_new" value="1">Create</button></p></form>';
	$apps = get_option( 'unai_amb_applications', array() );
	echo '<h2>Applications (' . count( $apps ) . ')</h2><table class="widefat striped"><thead><tr><th>Date</th><th>Name</th><th>Email</th><th>College</th><th>City</th><th>Course/year</th><th>Why</th></tr></thead><tbody>';
	foreach ( array_reverse( $apps ) as $a ) {
		printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( $a['date'] ), esc_html( $a['name'] ), esc_html( $a['email'] ), esc_html( $a['college'] ), esc_html( $a['city'] ), esc_html( $a['year'] ), esc_html( $a['why'] ) );
	}
	echo '</tbody></table></div>';
}

/** Public application form: [unai_ambassador_apply] */
add_shortcode( 'unai_ambassador_apply', function () {
	$out = '';
	if ( isset( $_POST['unai_amb_apply'] ) ) {
		$ok = isset( $_POST['_unai_amb_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_unai_amb_nonce'] ) ), 'unai_amb_apply' );
		$hp = ! empty( $_POST['website'] ); // honeypot
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? md5( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) ) : 'x';
		$rl = get_transient( 'unai_amb_rl_' . $ip );
		if ( ! $ok || $hp || $rl ) {
			$out .= '<p><strong>Sorry, we could not take that application. Please try again later or write to hello@unai-labs.com.</strong></p>';
		} else {
			$a = array(
				'date'    => current_time( 'Y-m-d H:i' ),
				'name'    => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
				'email'   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
				'college' => sanitize_text_field( wp_unslash( $_POST['college'] ?? '' ) ),
				'city'    => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
				'year'    => sanitize_text_field( wp_unslash( $_POST['year'] ?? '' ) ),
				'why'     => sanitize_textarea_field( wp_unslash( $_POST['why'] ?? '' ) ),
			);
			if ( $a['name'] && is_email( $a['email'] ) && $a['college'] ) {
				$apps = get_option( 'unai_amb_applications', array() );
				$apps[] = $a;
				update_option( 'unai_amb_applications', array_slice( $apps, -1000 ), false );
				set_transient( 'unai_amb_rl_' . $ip, 1, 10 * MINUTE_IN_SECONDS );
				wp_mail( UNAI_AMB_NOTIFY_TO, 'New campus ambassador application: ' . $a['college'], "Name: {$a['name']}\nEmail: {$a['email']}\nCollege: {$a['college']}\nCity: {$a['city']}\nCourse/year: {$a['year']}\n\nWhy:\n{$a['why']}\n\nReview in wp-admin: WooCommerce > Ambassadors" );
				return '<p><strong>Thank you! We read every application and reply within 3 working days.</strong></p>';
			}
			$out .= '<p><strong>Please fill in your name, a valid email and your college.</strong></p>';
		}
	}
	$out .= '<form method="post" class="unai-amb-form">' . wp_nonce_field( 'unai_amb_apply', '_unai_amb_nonce', true, false )
		. '<p><label>Full name<br><input name="name" required maxlength="80"></label></p>'
		. '<p><label>Email<br><input name="email" type="email" required maxlength="120"></label></p>'
		. '<p><label>College or university<br><input name="college" required maxlength="120"></label></p>'
		. '<p><label>City<br><input name="city" maxlength="60"></label></p>'
		. '<p><label>Course and year<br><input name="year" maxlength="60" placeholder="e.g. B.Tech, 2nd year"></label></p>'
		. '<p><label>Why do you want to be an ambassador? (2-3 lines)<br><textarea name="why" rows="4" maxlength="800"></textarea></label></p>'
		. '<p style="position:absolute;left:-9999px" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></p>'
		. '<p><button type="submit" name="unai_amb_apply" value="1">Apply to be an ambassador</button></p></form>';
	return $out;
} );
