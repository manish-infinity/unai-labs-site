<?php
/**
 * Snippet: "SEO: courses archive H1 + course thumbnail alt" (prepared 2026-11-07, NOT yet live)
 *
 * 1) /courses/ archive has no H1 (Astra + Tutor template). Adds one visible H1 at the top
 *    of the primary content area, archive only.
 * 2) Tutor 4.2 prints the course thumbnail as a bare <img> with no alt on single course
 *    pages. Adds alt = course title in the browser (no output buffering, so the buy
 *    button and checkout markup are never touched).
 *
 * Reversible: switch this snippet off in Snippets. Writes nothing, reads no user input.
 * Run: front end only.
 */
if ( is_admin() ) { return; }

add_action( 'astra_primary_content_top', function () {
	if ( ! is_post_type_archive( 'courses' ) ) { return; }
	echo '<h1 class="unai-archive-title" style="margin:0 0 1.25rem;font-size:2rem;line-height:1.2;">'
		. esc_html__( 'Online Courses on AI and the Brain', 'unai' ) . '</h1>';
} );

add_action( 'wp_footer', function () {
	if ( ! is_singular( 'courses' ) ) { return; }
	$alt = wp_json_encode( get_the_title( get_queried_object_id() ) . ' course' );
	echo '<script>(function(){var a=' . $alt . ';document.querySelectorAll(".tutor-course-thumbnail img:not([alt])").forEach(function(i){i.setAttribute("alt",a);});})();</script>';
}, 99 );
