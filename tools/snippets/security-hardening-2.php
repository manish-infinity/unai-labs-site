// UnAI: security hardening 2 (Claude, 2026-11-03)
// Findings from the 3 Nov security scan (claude/QA_SECURITY_REPORT_2026-11-03.md):
// 1) Tutor LMS REST /tutor/v1/author-information/<id> leaked the admin login (user_nicename) to anyone.
// 2) Login had no limit on failed attempts: lock an address for 15 min after 10 failures, generic error text.
// 3) No Content-Security-Policy: add a minimal, safe policy that cannot break scripts, Razorpay or YouTube.
// 4) WordPress / WooCommerce / Tutor version numbers shown in <meta name="generator">: removed.
// Nothing here touches registration, cart, checkout or payment. Logged-in users keep full REST access.

if ( ! defined( 'ABSPATH' ) ) { return; }

/* 1) Block the Tutor author endpoint for logged-out visitors. */
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	$route = $request->get_route();
	if ( 0 === strpos( $route, '/tutor/v1/author-information' ) && ! is_user_logged_in() ) {
		return new WP_Error( 'rest_forbidden', 'Not available.', array( 'status' => 401 ) );
	}
	return $result;
}, 10, 3 );

/* 2) Failed-login limiter (wp-login, My Account, Tutor login all go through wp_authenticate). */
function unai_login_client_ip() {
	$ip = isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' );
	$ip = filter_var( trim( (string) $ip ), FILTER_VALIDATE_IP );
	return $ip ? $ip : 'unknown';
}
function unai_login_key() {
	return 'unai_lf_' . md5( unai_login_client_ip() );
}
add_filter( 'authenticate', function ( $user, $username, $password ) {
	if ( empty( $username ) && empty( $password ) ) {
		return $user; // page load, not an attempt
	}
	$fails = (int) get_transient( unai_login_key() );
	if ( $fails >= 10 ) {
		return new WP_Error( 'unai_locked', 'Too many failed sign-in attempts. Please wait 15 minutes, or reset your password. Need help? hello@unai-labs.com' );
	}
	return $user;
}, 30, 3 );
add_action( 'wp_login_failed', function () {
	$key   = unai_login_key();
	$fails = (int) get_transient( $key );
	set_transient( $key, $fails + 1, 15 * MINUTE_IN_SECONDS );
} );
add_action( 'wp_login', function () {
	delete_transient( unai_login_key() );
} );
// Generic error: do not reveal whether the username exists.
add_filter( 'login_errors', function ( $error ) {
	if ( false !== strpos( (string) $error, 'Too many failed' ) ) {
		return $error;
	}
	return 'Sign-in failed: the username/email or password is not correct.';
} );

/* 3) Safe security headers on front-end pages. */
add_action( 'send_headers', function () {
	if ( is_admin() ) { return; }
	header( "Content-Security-Policy: frame-ancestors 'self'; object-src 'none'; base-uri 'self'; upgrade-insecure-requests" );
	header( 'Cross-Origin-Opener-Policy: same-origin-allow-popups' );
}, 20 );

/* 4) Remove generator meta tags (version disclosure). */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
add_action( 'wp_head', function () { ob_start(); }, -9999 );
add_action( 'wp_head', function () {
	$html = ob_get_clean();
	echo preg_replace( '/<meta[^>]+name=["\']generator["\'][^>]*>\s*/i', '', (string) $html );
}, 99999 );
