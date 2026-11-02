<?php
/**
 * UnAI Campus Ambassadors v2: SELF-SERVE portal (Snippet #17)
 * Approved terms (Manish 2026-11-02): buyer 10% off courses + eBook; ambassador share
 * 10% (1-24 units), 15% (25-99), 20% (100-249), 25% cap (250+); we keep >= 60% of paid.
 *
 * Self-serve flow (shortcode [unai_ambassador_portal] on page 363):
 *  1. Student logs in (normal account; registration has the bot shield).
 *  2. Applies with a COLLEGE email. Free-mail domains are refused.
 *  3. A one-time link (48h, hashed token, bound to the logged-in user) goes to that college email.
 *  4. On verification: institutional domains (.ac.in .edu.in .edu .ac.xx .edu.xx) are auto-approved,
 *     max 5 per domain; anything else waits for admin review (WooCommerce > Ambassadors).
 *  5. Approved: a unique code is created automatically; the portal shows code, share link,
 *     units, tier, earned, pending, paid out, UPI ID for payouts, and tips.
 * Tight controls: code rejected for the ambassador's own account/emails; once per buyer;
 * individual use; never on licences; suspend = code disabled; no buyer data ever shown.
 */
if ( ! defined( 'ABSPATH' ) ) { return; }

const UNAI_AMB_ELIGIBLE   = array( 120, 157, 226, 249, 286 );
const UNAI_AMB_EXCLUDED   = array( 311, 356, 357 );
const UNAI_AMB_DISCOUNT   = 10;
const UNAI_AMB_FEE_RATE   = 0.0236;
const UNAI_AMB_MIN_KEEP   = 0.60;
const UNAI_AMB_NOTIFY_TO  = 'manish.singh@unai-labs.com';
const UNAI_AMB_PER_DOMAIN = 5;
const UNAI_AMB_FREE_MAIL  = array( 'gmail.com','googlemail.com','yahoo.com','yahoo.co.in','ymail.com','outlook.com','hotmail.com','live.com','msn.com','icloud.com','me.com','rediffmail.com','rediff.com','protonmail.com','proton.me','aol.com','zoho.com','zohomail.in','gmx.com','mail.com','yandex.com','tutanota.com' );

function unai_amb_rate_for_units( $u ) { return $u >= 250 ? 0.25 : ( $u >= 100 ? 0.20 : ( $u >= 25 ? 0.15 : 0.10 ) ); }
function unai_amb_status( $uid ) { return (string) get_user_meta( $uid, '_unai_amb_status', true ); }
function unai_amb_domain( $email ) { $p = explode( '@', strtolower( $email ) ); return isset( $p[1] ) ? $p[1] : ''; }
function unai_amb_is_institutional( $domain ) { return (bool) preg_match( '/\.(ac\.in|edu\.in|edu|ac\.[a-z]{2}|edu\.[a-z]{2})$/', $domain ); }

/* ---------- Coupon creation, suspension ---------- */
function unai_amb_make_code( $name ) {
	$base = strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', remove_accents( $name ) ), 0, 8 ) );
	if ( strlen( $base ) < 3 ) { $base = 'UNAI'; }
	for ( $i = 0; $i < 20; $i++ ) {
		$code = $base . wp_rand( 100, 999 );
		if ( ! wc_get_coupon_id_by_code( $code ) ) { return $code; }
	}
	return 'UNAI' . strtoupper( wp_generate_password( 6, false, false ) );
}

function unai_amb_activate( $uid ) {
	$u = get_userdata( $uid );
	if ( ! $u ) { return false; }
	$cid = (int) get_user_meta( $uid, '_unai_amb_coupon_id', true );
	if ( $cid && get_post( $cid ) ) {
		wp_update_post( array( 'ID' => $cid, 'post_status' => 'publish' ) );
	} else {
		$name = trim( $u->first_name . ' ' . $u->last_name ) ?: $u->display_name;
		$c = new WC_Coupon();
		$c->set_code( unai_amb_make_code( $u->first_name ?: $name ) );
		$c->set_discount_type( 'percent' );
		$c->set_amount( UNAI_AMB_DISCOUNT );
		$c->set_individual_use( true );
		$c->set_usage_limit_per_user( 1 );
		$c->set_exclude_sale_items( false );
		$c->set_product_ids( UNAI_AMB_ELIGIBLE );
		$c->set_excluded_product_ids( UNAI_AMB_EXCLUDED );
		$c->set_description( 'Campus ambassador #' . $uid . ' (' . get_user_meta( $uid, '_unai_amb_college', true ) . ')' );
		$c->update_meta_data( '_unai_ambassador', 1 );
		$c->update_meta_data( '_unai_amb_user', $uid );
		$c->update_meta_data( '_unai_amb_email', strtolower( (string) get_user_meta( $uid, '_unai_amb_college_email', true ) ) );
		$c->update_meta_data( '_unai_amb_name', $name );
		$c->update_meta_data( '_unai_amb_college', (string) get_user_meta( $uid, '_unai_amb_college', true ) );
		$cid = $c->save();
		update_user_meta( $uid, '_unai_amb_coupon_id', $cid );
	}
	update_user_meta( $uid, '_unai_amb_status', 'active' );
	$code = wc_get_coupon_code_by_id( $cid );
	wp_mail( $u->user_email, 'You are now a UnAI Labs Campus Ambassador', "Welcome aboard!\n\nYour code: {$code}\nShare link: " . home_url( '/?amb=' . rawurlencode( $code ) ) . "\n\nAnyone who buys with it gets 10% off, and you earn a share of every sale. See your live dashboard any time: " . home_url( '/campus-ambassadors/' ) . "\n\nUnAI Labs" );
	return $cid;
}

function unai_amb_suspend( $uid ) {
	update_user_meta( $uid, '_unai_amb_status', 'suspended' );
	$cid = (int) get_user_meta( $uid, '_unai_amb_coupon_id', true );
	if ( $cid ) { wp_update_post( array( 'ID' => $cid, 'post_status' => 'draft' ) ); }
}

/* ---------- Tight coupon rules: no self-use ---------- */
add_filter( 'woocommerce_coupon_is_valid', function ( $valid, $coupon ) {
	if ( ! $valid || ! $coupon->get_meta( '_unai_ambassador' ) ) { return $valid; }
	$owner = (int) $coupon->get_meta( '_unai_amb_user' );
	if ( $owner && unai_amb_status( $owner ) !== 'active' ) {
		throw new Exception( __( 'This ambassador code is not active.', 'unai' ) );
	}
	$uid = get_current_user_id();
	$emails = array_filter( array( strtolower( (string) $coupon->get_meta( '_unai_amb_email' ) ) ) );
	if ( $owner ) { $ou = get_userdata( $owner ); if ( $ou ) { $emails[] = strtolower( $ou->user_email ); } }
	$buyer = array();
	if ( $uid ) { $cu = wp_get_current_user(); $buyer[] = strtolower( $cu->user_email ); }
	if ( function_exists( 'WC' ) && WC()->customer ) { $buyer[] = strtolower( (string) WC()->customer->get_billing_email() ); }
	if ( ( $owner && $uid === $owner ) || array_intersect( array_filter( $buyer ), $emails ) ) {
		throw new Exception( __( 'Ambassador codes are for your friends and classmates, not for your own purchases.', 'unai' ) );
	}
	return $valid;
}, 10, 2 );

/* ---------- Share link ?amb=CODE (applied on cart/checkout) ---------- */
add_action( 'template_redirect', function () {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) { return; }
	if ( isset( $_GET['amb'] ) ) {
		$code = wc_format_coupon_code( sanitize_text_field( wp_unslash( $_GET['amb'] ) ) );
		$cid  = $code ? wc_get_coupon_id_by_code( $code ) : 0;
		if ( $cid && get_post_meta( $cid, '_unai_ambassador', true ) && get_post_status( $cid ) === 'publish' ) {
			WC()->session->set( 'unai_amb_code', $code );
		}
	}
	$code = WC()->session->get( 'unai_amb_code' );
	if ( $code && ( is_cart() || is_checkout() ) && WC()->cart && ! WC()->cart->is_empty() && ! WC()->cart->has_discount( $code ) && ! WC()->cart->get_applied_coupons() ) {
		WC()->session->set( 'unai_amb_code', null ); // try once; never re-add a code the buyer removed
		WC()->cart->apply_coupon( $code );
	}
} );

/* ---------- Record ambassador data on paid orders ---------- */
add_action( 'woocommerce_order_status_changed', function ( $order_id, $from, $to, $order ) {
	if ( ! in_array( $to, array( 'processing', 'completed' ), true ) || $order->get_meta( '_unai_amb_code' ) ) { return; }
	foreach ( $order->get_coupon_codes() as $code ) {
		$coupon = new WC_Coupon( $code );
		if ( ! $coupon->get_id() || ! $coupon->get_meta( '_unai_ambassador' ) ) { continue; }
		$owner = (int) $coupon->get_meta( '_unai_amb_user' );
		$self  = ( $owner && (int) $order->get_customer_id() === $owner ) || strtolower( $order->get_billing_email() ) === strtolower( (string) $coupon->get_meta( '_unai_amb_email' ) );
		$paid = 0.0; $units = 0;
		foreach ( $order->get_items() as $item ) {
			if ( in_array( (int) $item->get_product_id(), UNAI_AMB_ELIGIBLE, true ) ) { $paid += (float) $item->get_total(); $units += (int) $item->get_quantity(); }
		}
		foreach ( $order->get_fees() as $fee ) { if ( (float) $fee->get_total() < 0 ) { $paid += (float) $fee->get_total(); } }
		$order->update_meta_data( '_unai_amb_code', $code );
		$order->update_meta_data( '_unai_amb_paid', max( 0, round( $paid, 2 ) ) );
		$order->update_meta_data( '_unai_amb_units', $units );
		if ( $self ) { $order->update_meta_data( '_unai_amb_self', 1 ); }
		$order->save();
		return;
	}
}, 10, 4 );

function unai_amb_stats( $code ) {
	$orders = wc_get_orders( array( 'limit' => -1, 'status' => array( 'processing', 'completed' ), 'meta_key' => '_unai_amb_code', 'meta_value' => $code ) );
	$units = 0; $paid = 0.0; $earned = 0.0; $pending = 0.0; $cutoff = time() - 7 * DAY_IN_SECONDS;
	foreach ( $orders as $o ) { if ( ! $o->get_meta( '_unai_amb_self' ) ) { $units += (int) $o->get_meta( '_unai_amb_units' ); } }
	$rate = unai_amb_rate_for_units( $units );
	foreach ( $orders as $o ) {
		if ( $o->get_meta( '_unai_amb_self' ) ) { continue; }
		$p = (float) $o->get_meta( '_unai_amb_paid' ); $paid += $p;
		$c = max( 0, min( $p * $rate, $p - $p * UNAI_AMB_FEE_RATE - $p * UNAI_AMB_MIN_KEEP ) );
		$done = $o->get_status() === 'completed' && $o->get_date_paid() && $o->get_date_paid()->getTimestamp() < $cutoff;
		if ( $done ) { $earned += $c; } else { $pending += $c; }
	}
	return compact( 'units', 'paid', 'rate', 'earned', 'pending' );
}

/* ---------- Email verification ---------- */
function unai_amb_send_verify( $uid ) {
	$token = wp_generate_password( 32, false, false );
	update_user_meta( $uid, '_unai_amb_token_hash', wp_hash_password( $token ) );
	update_user_meta( $uid, '_unai_amb_token_exp', time() + 2 * DAY_IN_SECONDS );
	$url = add_query_arg( array( 'unai_amb_verify' => $token, 'u' => $uid ), home_url( '/campus-ambassadors/' ) );
	wp_mail( get_user_meta( $uid, '_unai_amb_college_email', true ), 'Confirm your UnAI Labs Campus Ambassador application', "Hi,\n\nPlease confirm this is your college email by opening this link while logged in to your UnAI Labs account (valid for 48 hours):\n\n{$url}\n\nIf you did not apply, ignore this email.\n\nUnAI Labs" );
}

add_action( 'template_redirect', function () {
	if ( empty( $_GET['unai_amb_verify'] ) || empty( $_GET['u'] ) ) { return; }
	$uid = absint( $_GET['u'] ); $token = sanitize_text_field( wp_unslash( $_GET['unai_amb_verify'] ) );
	if ( ! is_user_logged_in() ) { wp_safe_redirect( wp_login_url( add_query_arg( array( 'unai_amb_verify' => $token, 'u' => $uid ), home_url( '/campus-ambassadors/' ) ) ) ); exit; }
	$msg = 'invalid';
	if ( get_current_user_id() === $uid && unai_amb_status( $uid ) === 'pending_verify' ) {
		$hash = get_user_meta( $uid, '_unai_amb_token_hash', true ); $exp = (int) get_user_meta( $uid, '_unai_amb_token_exp', true );
		if ( $hash && $exp > time() && wp_check_password( $token, $hash ) ) {
			delete_user_meta( $uid, '_unai_amb_token_hash' ); delete_user_meta( $uid, '_unai_amb_token_exp' );
			$domain = unai_amb_domain( get_user_meta( $uid, '_unai_amb_college_email', true ) );
			$count  = count( get_users( array( 'meta_query' => array( array( 'key' => '_unai_amb_domain', 'value' => $domain ), array( 'key' => '_unai_amb_status', 'value' => 'active' ) ), 'fields' => 'ID' ) ) );
			if ( unai_amb_is_institutional( $domain ) && $count < UNAI_AMB_PER_DOMAIN ) { unai_amb_activate( $uid ); $msg = 'active'; }
			else { update_user_meta( $uid, '_unai_amb_status', 'pending_review' ); $msg = 'review';
				wp_mail( UNAI_AMB_NOTIFY_TO, 'Ambassador to review: ' . get_user_meta( $uid, '_unai_amb_college', true ), 'A verified applicant needs review (domain ' . $domain . '). wp-admin: WooCommerce > Ambassadors.' ); }
		}
	}
	wp_safe_redirect( add_query_arg( 'amb_msg', $msg, home_url( '/campus-ambassadors/' ) ) ); exit;
} );

/* ---------- Portal shortcode ---------- */
add_shortcode( 'unai_ambassador_portal', function () {
	$notice = ''; $m = isset( $_GET['amb_msg'] ) ? sanitize_key( $_GET['amb_msg'] ) : '';
	$msgs = array( 'active' => 'Your college email is verified and your code is ready below.', 'review' => 'Your college email is verified. We review applications from this college within 3 working days.', 'invalid' => 'That link is invalid or expired. Log in and request a new one below.' );
	if ( isset( $msgs[ $m ] ) ) { $notice = '<p class="unai-amb-notice"><strong>' . esc_html( $msgs[ $m ] ) . '</strong></p>'; }

	if ( ! is_user_logged_in() ) {
		return $notice . '<p><strong>Ready to start?</strong> Create a free UnAI Labs account or log in, then come back to this page to apply. You will need your college email.</p><p><a class="button" href="' . esc_url( home_url( '/student-registration/' ) ) . '">Create account</a> <a class="button" href="' . esc_url( wp_login_url( home_url( '/campus-ambassadors/' ) ) ) . '">Log in</a></p>';
	}
	$uid = get_current_user_id(); $status = unai_amb_status( $uid ); $out = $notice;

	// Handle POSTs
	if ( isset( $_POST['unai_amb_action'] ) && isset( $_POST['_unai_amb_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_unai_amb_nonce'] ) ), 'unai_amb_portal_' . $uid ) ) {
		$act = sanitize_key( $_POST['unai_amb_action'] );
		if ( $act === 'apply' && in_array( $status, array( '', 'pending_verify' ), true ) && ! get_transient( 'unai_amb_apply_' . $uid ) && empty( $_POST['website'] ) ) {
			$cemail = strtolower( sanitize_email( wp_unslash( $_POST['college_email'] ?? '' ) ) ); $domain = unai_amb_domain( $cemail );
			$college = sanitize_text_field( wp_unslash( $_POST['college'] ?? '' ) );
			$taken = $cemail ? get_users( array( 'meta_key' => '_unai_amb_college_email', 'meta_value' => $cemail, 'exclude' => array( $uid ), 'fields' => 'ID' ) ) : array();
			if ( ! is_email( $cemail ) || ! $domain || in_array( $domain, UNAI_AMB_FREE_MAIL, true ) ) { $out .= '<p><strong>Please use your official college email, not a personal one like Gmail.</strong></p>'; }
			elseif ( $taken ) { $out .= '<p><strong>That college email is already registered.</strong></p>'; }
			elseif ( ! $college || empty( $_POST['terms'] ) ) { $out .= '<p><strong>Please add your college and accept the terms.</strong></p>'; }
			else {
				update_user_meta( $uid, '_unai_amb_college', $college );
				update_user_meta( $uid, '_unai_amb_college_email', $cemail );
				update_user_meta( $uid, '_unai_amb_domain', $domain );
				update_user_meta( $uid, '_unai_amb_city', sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ) );
				update_user_meta( $uid, '_unai_amb_year', sanitize_text_field( wp_unslash( $_POST['year'] ?? '' ) ) );
				update_user_meta( $uid, '_unai_amb_terms', time() );
				update_user_meta( $uid, '_unai_amb_status', 'pending_verify' );
				set_transient( 'unai_amb_apply_' . $uid, 1, 10 * MINUTE_IN_SECONDS );
				unai_amb_send_verify( $uid ); $status = 'pending_verify';
				$out .= '<p><strong>Check your college inbox for a confirmation link (valid 48 hours).</strong></p>';
			}
		} elseif ( $act === 'upi' && $status === 'active' ) {
			$upi = sanitize_text_field( wp_unslash( $_POST['upi'] ?? '' ) );
			if ( preg_match( '/^[A-Za-z0-9.\-_]{2,64}@[A-Za-z]{2,32}$/', $upi ) ) { update_user_meta( $uid, '_unai_amb_upi', $upi ); $out .= '<p><strong>UPI ID saved.</strong></p>'; }
			else { $out .= '<p><strong>That does not look like a UPI ID (example: name@okhdfcbank).</strong></p>'; }
		}
	}
	$nonce = wp_nonce_field( 'unai_amb_portal_' . $uid, '_unai_amb_nonce', true, false );

	if ( $status === 'suspended' ) { return $out . '<p>Your ambassador account is paused. Write to hello@unai-labs.com if you think this is a mistake.</p>'; }
	if ( $status === 'pending_review' ) { return $out . '<p>Your application is verified and waiting for review. We reply within 3 working days.</p>'; }
	if ( $status === 'active' ) {
		$cid = (int) get_user_meta( $uid, '_unai_amb_coupon_id', true ); $code = wc_get_coupon_code_by_id( $cid ); $s = unai_amb_stats( $code );
		$paid_out = (float) get_user_meta( $uid, '_unai_amb_paid_out', true ); $upi = (string) get_user_meta( $uid, '_unai_amb_upi', true );
		$link = home_url( '/?amb=' . rawurlencode( $code ) );
		$next = $s['units'] < 25 ? 25 : ( $s['units'] < 100 ? 100 : ( $s['units'] < 250 ? 250 : 0 ) );
		$out .= '<h2>Your ambassador dashboard</h2><p>Your code: <strong style="font-size:1.4em">' . esc_html( $code ) . '</strong><br>Share link (applies the code automatically): <code>' . esc_html( $link ) . '</code></p>';
		$out .= '<table><tr><td>Courses sold with your code</td><td><strong>' . (int) $s['units'] . '</strong></td></tr><tr><td>Your current share</td><td><strong>' . (int) round( $s['rate'] * 100 ) . '%</strong>' . ( $next ? ' (next tier at ' . $next . ')' : ' (top tier)' ) . '</td></tr><tr><td>Confirmed earnings</td><td>Rs ' . esc_html( number_format( $s['earned'], 0 ) ) . '</td></tr><tr><td>Pending (inside 7-day refund window)</td><td>Rs ' . esc_html( number_format( $s['pending'], 0 ) ) . '</td></tr><tr><td>Paid out so far</td><td>Rs ' . esc_html( number_format( $paid_out, 0 ) ) . '</td></tr><tr><td>Balance due</td><td><strong>Rs ' . esc_html( number_format( max( 0, $s['earned'] - $paid_out ), 0 ) ) . '</strong> (paid monthly from Rs 500)</td></tr></table>';
		$out .= '<form method="post">' . $nonce . '<input type="hidden" name="unai_amb_action" value="upi"><p><label>UPI ID for payouts<br><input name="upi" value="' . esc_attr( $upi ) . '" placeholder="name@okhdfcbank" maxlength="100"></label> <button type="submit">Save</button></p></form>';
		$out .= '<h3>How to share well</h3><ul><li>Post your link on your Instagram story with one line on why focus matters to you.</li><li>Share it in class and club WhatsApp groups once, not every day.</li><li>Ask your wellness centre, placement cell or tech club about a free 45-minute "Your Brain vs AI" session: introduce them to hello@unai-labs.com.</li><li>Be honest: no fake reviews, no spam, no promises the courses do not make. Your own purchases do not count and your code will not work for you.</li></ul>';
		return $out;
	}
	if ( $status === 'pending_verify' ) {
		$out .= '<p>We sent a confirmation link to <strong>' . esc_html( get_user_meta( $uid, '_unai_amb_college_email', true ) ) . '</strong>. Not there? Check spam, or re-send below (once every 10 minutes).</p>';
	}
	$out .= '<h2>Apply now</h2><form method="post">' . $nonce . '<input type="hidden" name="unai_amb_action" value="apply">'
		. '<p><label>College or university<br><input name="college" required maxlength="120" value="' . esc_attr( get_user_meta( $uid, '_unai_amb_college', true ) ) . '"></label></p>'
		. '<p><label>Your official college email<br><input name="college_email" type="email" required maxlength="120" value="' . esc_attr( get_user_meta( $uid, '_unai_amb_college_email', true ) ) . '"></label></p>'
		. '<p><label>City<br><input name="city" maxlength="60"></label></p><p><label>Course and year<br><input name="year" maxlength="60" placeholder="e.g. B.Tech, 2nd year"></label></p>'
		. '<p style="position:absolute;left:-9999px" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></p>'
		. '<p><label><input type="checkbox" name="terms" value="1" required> I agree to the programme rules on this page.</label></p>'
		. '<p><button type="submit">' . ( $status === 'pending_verify' ? 'Re-send confirmation' : 'Get my code' ) . '</button></p></form>';
	return $out;
} );

/* ---------- Admin: WooCommerce > Ambassadors ---------- */
add_action( 'admin_menu', function () { add_submenu_page( 'woocommerce', 'Campus Ambassadors', 'Ambassadors', 'manage_woocommerce', 'unai-ambassadors', 'unai_amb_admin_page' ); } );

function unai_amb_admin_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
	if ( isset( $_POST['unai_amb_admin'] ) && check_admin_referer( 'unai_amb_admin' ) ) {
		$uid = absint( $_POST['uid'] ?? 0 ); $act = sanitize_key( $_POST['unai_amb_admin'] );
		if ( $uid && $act === 'approve' ) { unai_amb_activate( $uid ); }
		if ( $uid && $act === 'suspend' ) { unai_amb_suspend( $uid ); }
		if ( $uid && $act === 'remove' ) {
			$cid = (int) get_user_meta( $uid, '_unai_amb_coupon_id', true );
			if ( $cid ) { wp_trash_post( $cid ); }
			foreach ( array( 'status', 'college', 'college_email', 'domain', 'city', 'year', 'terms', 'coupon_id', 'upi', 'paid_out', 'token_hash', 'token_exp' ) as $k ) { delete_user_meta( $uid, '_unai_amb_' . $k ); }
		}
		if ( $uid && $act === 'paid' ) { $amt = (float) ( $_POST['amount'] ?? 0 ); if ( $amt > 0 ) { update_user_meta( $uid, '_unai_amb_paid_out', (float) get_user_meta( $uid, '_unai_amb_paid_out', true ) + $amt ); } }
		echo '<div class="notice notice-success"><p>Done.</p></div>';
	}
	$users = get_users( array( 'meta_key' => '_unai_amb_status', 'meta_compare' => 'EXISTS' ) );
	echo '<div class="wrap"><h1>Campus Ambassadors</h1><p>Self-serve: students apply on /campus-ambassadors/ with a college email. Institutional domains auto-approve (max ' . (int) UNAI_AMB_PER_DOMAIN . ' per domain); others wait here. Tiers 10/15/20/25% on lifetime units; we keep at least 60% of paid.</p>';
	echo '<table class="widefat striped"><thead><tr><th>Name</th><th>College / email</th><th>Status</th><th>Code</th><th>Units</th><th>Tier</th><th>Earned</th><th>Paid out</th><th>UPI</th><th>Actions</th></tr></thead><tbody>';
	foreach ( $users as $u ) {
		$st = unai_amb_status( $u->ID ); $cid = (int) get_user_meta( $u->ID, '_unai_amb_coupon_id', true ); $code = $cid ? wc_get_coupon_code_by_id( $cid ) : '';
		$s = $code ? unai_amb_stats( $code ) : array( 'units' => 0, 'rate' => 0.10, 'earned' => 0 );
		$f = function ( $act, $label, $extra = '' ) use ( $u ) { return '<form method="post" style="display:inline">' . wp_nonce_field( 'unai_amb_admin', '_wpnonce', true, false ) . '<input type="hidden" name="uid" value="' . (int) $u->ID . '">' . $extra . '<button class="button" name="unai_amb_admin" value="' . esc_attr( $act ) . '">' . esc_html( $label ) . '</button></form> '; };
		$actions = ( $st === 'pending_review' || $st === 'suspended' ? $f( 'approve', 'Approve' ) : '' ) . ( $st === 'active' ? $f( 'suspend', 'Suspend' ) . $f( 'paid', 'Record payout', '<input name="amount" type="number" min="1" step="1" style="width:80px" placeholder="Rs">' ) : '' ) . ( $st !== 'active' ? $f( 'remove', 'Remove' ) : '' );
		printf( '<tr><td>%s</td><td>%s<br><small>%s</small></td><td>%s</td><td>%s</td><td>%d</td><td>%d%%</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
			esc_html( $u->display_name ), esc_html( get_user_meta( $u->ID, '_unai_amb_college', true ) ), esc_html( get_user_meta( $u->ID, '_unai_amb_college_email', true ) ), esc_html( $st ), esc_html( strtoupper( $code ) ),
			(int) $s['units'], (int) round( $s['rate'] * 100 ), esc_html( number_format( $s['earned'], 0 ) ), esc_html( number_format( (float) get_user_meta( $u->ID, '_unai_amb_paid_out', true ), 0 ) ), esc_html( get_user_meta( $u->ID, '_unai_amb_upi', true ) ), $actions );
	}
	if ( ! $users ) { echo '<tr><td colspan="10">No ambassadors yet.</td></tr>'; }
	echo '</tbody></table></div>';
}
