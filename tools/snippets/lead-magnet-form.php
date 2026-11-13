<?php
/**
 * Snippet: "Lead magnet form: free 5-day course" (Claude roadmap runner, 2026-11-13, R-08 / WEB-004)
 *
 * Why: about 3,400 visits a month and almost no orders. Visitors who are not ready to buy leave
 * with nothing. This adds one small sign-up box for the free 5-day email course "Your Brain vs AI"
 * (copy in GitHub content/lead-magnet/) under the main content of the home page and of each
 * course page, so they can stay in touch.
 *
 * What it does: prints a MailPoet form by shortcode. It does nothing until UNAI_LM_FORM_ID below
 * is set to the real MailPoet form ID, and nothing if MailPoet is switched off. It never touches
 * cart, checkout, account or registration, and adds no external scripts.
 *
 * Deploy (live session or site build):
 *   1. MailPoet > Lists: create list "5-day course: Your Brain vs AI".
 *   2. MailPoet > Forms: new form, fields First name (optional) + Email, list above,
 *      button "Send me Day 1", success text "Check your inbox for Day 1." Note its ID.
 *   3. MailPoet > Automations: welcome sequence on that list, Day 1 at once, Days 2-5 one day apart.
 *   4. Set UNAI_LM_FORM_ID, activate this snippet, smoke test the buy path before and after.
 * Revert: switch this snippet off. No data stored by the snippet itself.
 */
if ( ! defined( 'UNAI_LM_FORM_ID' ) ) {
	define( 'UNAI_LM_FORM_ID', 0 ); // set to the MailPoet form ID before activating
}

add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() || is_feed() ) {
		return $content;
	}
	$form_id = (int) UNAI_LM_FORM_ID;
	if ( $form_id <= 0 || ! shortcode_exists( 'mailpoet_form' ) ) {
		return $content;
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return $content;
	}
	$on_home   = is_front_page();
	$on_course = is_singular( array( 'courses', 'product' ) );
	if ( ! $on_home && ! $on_course ) {
		return $content;
	}
	$box  = '<section class="unai-lm" aria-labelledby="unai-lm-h" style="max-width:640px;margin:32px auto;padding:20px;border:1px solid #d0d7de;border-radius:10px;background:#f7f9fb">';
	$box .= '<h3 id="unai-lm-h" style="margin:0 0 8px">Not ready to buy? Try the free 5-day course</h3>';
	$box .= '<p style="margin:0 0 12px">"Your Brain vs AI": one short email and one 5-minute experiment a day, with the science named. Unsubscribe any time.</p>';
	$box .= do_shortcode( '[mailpoet_form id="' . $form_id . '"]' );
	$box .= '</section>';
	return $content . $box;
}, 20 );
