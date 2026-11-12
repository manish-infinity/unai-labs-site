<?php
/**
 * Code Snippet #22 "UnAI: Add to cart goes to cart, not a sign-in pop-up (Claude, 2026-11-12)"
 * Scope: front-end. Revert: deactivate the snippet in Snippets (one click).
 *
 * Problem: Tutor LMS adds the class "tutor-open-login-modal" to every Add to cart
 * button for logged-out visitors (course pages and the /courses/ cards). A new
 * buyer clicks Add to cart and gets a Sign In pop-up with a captcha. They have no
 * account, so they must leave for /student-registration/ and the course is lost.
 *
 * Fix: for paid (WooCommerce) courses only, drop that class so the button simply
 * adds the course to the cart. WooCommerce already creates the buyer's account at
 * checkout from their email (guest checkout stays OFF), so every buyer still gets
 * an account and Tutor still enrols them on payment.
 *
 * Security: no input, no output, no data stored. Only removes a CSS class.
 */
add_filter( 'tutor_enroll_required_login_class', function ( $class ) {
	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'tutor_utils' ) ) {
		return $class;
	}
	if ( 'wc' !== tutor_utils()->get_option( 'monetize_by' ) ) {
		return $class;
	}
	return '';
}, 20 );
