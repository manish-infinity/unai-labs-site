<?php
/**
 * Snippet #26 UnAI: homepage shows live course prices + two stale lines corrected (Claude, 2026-11-12, roadmap R-01).
 * Why: (1) the homepage course cards say Rs 2,999 while the course pages and cart charge
 * the launch price Rs 1,999 (to 30 Nov), so the sale is invisible on the page most visitors
 * land on. The badge now reads the live WooCommerce price and sale end date, so it switches
 * back to Rs 2,999 by itself when the sale ends (no hand-typed price to go stale).
 * (2) The homepage says Kindle/paperback are "coming soon"; the Kindle edition is live on
 * Amazon.in (B0HP7SZ846), so the line now links to it. (3) The AI Literacy course page says
 * "video versions coming soon"; all 15 lessons already have video. Text only, display time only,
 * nothing in the database changes. Revert: deactivate.
 */
add_filter( 'the_content', function ( $html ) {
	if ( is_admin() || ! function_exists( 'wc_get_product' ) ) { return $html; }

	if ( is_front_page() ) {
		$map = array(
			'AI Literacy for Everyone'   => 120,
			'Neuroplasticity in Practice' => 157,
			'Deep Focus'                 => 226,
			'Brain Health 101'           => 249,
		);
		foreach ( $map as $title => $pid ) {
			$p = wc_get_product( $pid );
			if ( ! $p ) { continue; }
			$reg  = (float) $p->get_regular_price();
			$now  = (float) $p->get_price();
			$fmt  = function ( $n ) { return '&#8377;' . number_format( $n ); };
			$badge = $fmt( $now );
			if ( $p->is_on_sale() && $reg > $now ) {
				$to = $p->get_date_on_sale_to();
				$badge = '<s style="opacity:.7;font-weight:400">' . $fmt( $reg ) . '</s> ' . $fmt( $now );
				if ( $to ) { $badge .= ' &middot; until ' . esc_html( $to->date_i18n( 'j M' ) ); }
			}
			$pattern = '~(<span[^>]*border-radius:20px">)(?:&#8377;|₹)\s*2,999(</span></div><div[^>]*><h3[^>]*>)(' . preg_quote( $title, '~' ) . ')~u';
			$html = preg_replace( $pattern, '${1}' . $badge . '${2}${3}', $html, 1 );
		}
		$html = preg_replace(
			'~All-formats edition \(Kindle, paperback\) (?:&#8377;|₹)1,099\s*(?:&#8212;|—|&mdash;)\s*coming soon\.~u',
			'Also on Kindle: <a href="https://www.amazon.in/dp/B0HP7SZ846" rel="noopener" target="_blank">get it on Amazon.in</a>.',
			$html, 1
		);
	}

	if ( is_singular( 'courses' ) ) {
		$html = str_replace( 'reading + exercises (video versions coming soon)', 'video + reading + exercises in every lesson', $html );
	}
	return $html;
}, 20 );
