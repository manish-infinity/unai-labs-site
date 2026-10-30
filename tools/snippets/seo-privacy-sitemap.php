<?php
/**
 * UnAI: hide admin username + keep login-walled pages out of Google (Claude, 2026-10-30)
 * Code Snippet #13. Switch off in Snippets to revert. No effect on cart, checkout, registration or course access.
 *
 * 1. Author archives (/author/<login>/) redirect home, and author links point home,
 *    so the admin login name no longer appears in page source, oEmbed or the sitemap.
 * 2. Yoast: author archives disabled (removes author-sitemap.xml).
 * 3. Lessons, quizzes and assignments are login-walled for visitors, so they are removed
 *    from the Yoast sitemap and marked noindex,follow. Tutor "preview" lessons stay indexable.
 */

add_action( 'template_redirect', function () {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );

add_filter( 'author_link', function () {
	return home_url( '/' );
}, 99 );

add_filter( 'option_wpseo_titles', function ( $o ) {
	if ( is_array( $o ) ) {
		$o['disable-author']       = true;
		$o['noindex-author-wpseo'] = true;
		foreach ( array( 'lesson', 'tutor_quiz', 'tutor_assignments' ) as $t ) {
			$o[ 'noindex-' . $t ] = true;
		}
	}
	return $o;
} );

function unai_walled_types() {
	return array( 'lesson', 'tutor_quiz', 'tutor_assignments' );
}

add_filter( 'wpseo_sitemap_exclude_post_type', function ( $excluded, $post_type ) {
	return in_array( $post_type, unai_walled_types(), true ) ? true : $excluded;
}, 999, 2 );

add_filter( 'wpseo_robots', function ( $robots ) {
	if ( is_singular( unai_walled_types() ) ) {
		$id = get_queried_object_id();
		if ( 'lesson' === get_post_type( $id ) && get_post_meta( $id, '_is_preview', true ) ) {
			return $robots;
		}
		return 'noindex, follow';
	}
	return $robots;
}, 99 );
