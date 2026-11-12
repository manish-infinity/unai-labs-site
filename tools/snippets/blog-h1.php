<?php
/**
 * Code Snippet #23 "SEO: /blog/ H1 (Claude, 2026-11-12)"
 * /blog/ (posts page, ID 27) prints no H1 in Astra. Adds one visible H1 "Blog"
 * (the page's existing title, no new copy) at the top of the content, posts page only.
 * Reversible: switch this snippet off. Writes nothing, reads no user input. Front end only.
 */
if ( is_admin() ) { return; }
$unai_blog_h1 = function () {
	static $done = false;
	if ( $done || ! is_home() ) { return; }
	$done = true;
	echo '<h1 class="unai-blog-title" style="margin:0 0 1.25rem;font-size:2rem;line-height:1.2;">'
		. esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ) . '</h1>';
};
add_action( 'astra_primary_content_top', $unai_blog_h1 );
