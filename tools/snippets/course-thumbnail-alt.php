<?php
/**
 * Code Snippet #19 (INACTIVE): UnAI: alt text on Tutor course thumbnail (Claude, 2026-11-05).
 * Tried 2026-11-05. Tutor 4.2.0 prints the single-course thumbnail as a bare
 * <img src="..."> and does not run it through the tutor_course_thumbnail filter,
 * so this had no effect and was switched off. Kept for reference.
 * Media 329-332 already carry alt text; the gap is in the Tutor template.
 */
add_filter( 'tutor_course_thumbnail', function ( $html, $post_id = 0 ) {
	if ( ! is_string( $html ) || false === strpos( $html, '<img' ) || preg_match( '/\salt=/i', $html ) ) {
		return $html;
	}
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$alt     = (string) get_post_meta( (int) get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true );
	if ( '' === trim( $alt ) ) {
		$alt = get_the_title( $post_id );
	}
	return preg_replace( '/<img\s/i', '<img alt="' . esc_attr( wp_strip_all_tags( $alt ) ) . '" ', $html, 1 );
}, 10, 2 );
