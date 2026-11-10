<?php
/**
 * Snippet #21 (prepared 2026-11-08, ACTIVE since 2026-11-10): "HTML browser cache: 5 min, not 31 days"
 *
 * Problem (found 2026-11-08, logged out): public HTML pages (/, /courses/, course
 * pages, /pricing/, /about/, /student-registration/) are served with
 *   Cache-Control: public, max-age=2678400   (31 days)
 * so a returning visitor's browser can show a month-old page without asking the
 * server. Seen live: the browser-cached /courses/ had no H1 and /about/ and
 * /courses/brain-health-101/ had no meta description, although the server copies do.
 * Sales risk: after the launch sale ends on 30 Nov, cached course/pricing pages can
 * still show Rs 1,999 while the cart charges Rs 2,999.
 * Cart, checkout and my-account are already private/no-cache (not touched here).
 *
 * Fix: for logged-out front-end HTML only, ask browsers to keep a page for 5 minutes
 * and let the CDN edge keep it for 1 day (s-maxage). Logged-in users, wp-admin, REST,
 * feeds and any response WooCommerce already marked no-cache are left alone.
 *
 * NOTE: if the CDN (GoDaddy/Cloudflare "Browser Cache TTL") forces 1 month, it
 * overrides this header; then the CDN setting must be changed to "Respect existing
 * headers". Check the response header after activating.
 *
 * 2026-11-10: activated as Code Snippet #21. Live header still "public, max-age=2678400"
 * on a cache MISS: Cloudflare Browser Cache TTL (1 month) overrides shorter origin
 * values. Needs the CDN setting changed to "Respect existing headers" (Manish).
 *
 * Revert: switch this snippet off in Code Snippets.
 * No secrets, no user data, no output changes.
 */
add_action( 'template_redirect', function () { // query is ready here, headers not yet sent
	if ( headers_sent() || is_admin() || is_user_logged_in() || wp_doing_ajax() || is_feed() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}
	foreach ( headers_list() as $h ) {
		if ( stripos( $h, 'Cache-Control:' ) === 0 && ( stripos( $h, 'no-cache' ) !== false || stripos( $h, 'private' ) !== false ) ) {
			return;
		}
	}
	header( 'Cache-Control: public, max-age=300, s-maxage=86400', true );
	header_remove( 'Expires' );
}, 99 );
