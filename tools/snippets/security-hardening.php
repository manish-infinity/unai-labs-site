// UnAI: security hardening (Claude, 2026-10-30)
// 1) Hide the user list from the public REST API (it leaked the admin username).
// 2) Stop ?author=N username discovery.
// 3) Standard browser security headers on every front-end page.
// Logged-in admins and all shop/course/checkout flows are unaffected.

if ( ! defined( 'ABSPATH' ) ) { return; }

add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	$route = $request->get_route();
	if ( 0 === strpos( $route, '/wp/v2/users' ) && ! is_user_logged_in() ) {
		return new WP_Error( 'rest_forbidden', 'Not available.', array( 'status' => 401 ) );
	}
	return $result;
}, 10, 3 );

add_action( 'template_redirect', function () {
	if ( isset( $_GET['author'] ) && ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );

add_action( 'send_headers', function () {
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Strict-Transport-Security: max-age=31536000' );
} );
