<?php
/**
 * Snippet: "Stale page self-refresh" (Claude, 2026-11-10)
 *
 * Why: GoDaddy's managed CDN forces `Cache-Control: public, max-age=2678400` (31 days) on every
 * public page. Tested 10 Nov: WordPress headers (max-age=300, no-cache, even private) are all
 * overwritten downstream; the hosting panel has no browser-cache setting. So a returning visitor
 * can see a month-old copy (e.g. the Rs 1,999 sale price after the sale ends on 30 Nov).
 *
 * Fix: each logged-out public HTML page carries the time it was built. If the browser shows a
 * copy older than 12 hours, the page reloads itself once from the network with a cache-busting
 * query (?fresh=<time>), which is a new URL for the CDN and the browser. A sessionStorage flag
 * stops loops; if storage is blocked, the query itself (fresh= present) stops a second reload.
 * Cart, checkout, account, admin, REST and logged-in users are untouched. Canonical tags (Yoast)
 * keep search engines on the clean URL.
 *
 * Revert: switch this snippet off. No data, no secrets, no external calls.
 */
add_action( 'wp_head', function () {
	if ( is_admin() || is_user_logged_in() || is_feed() ) {
		return;
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}
	$built = time();
	?>
<script>
(function(){try{
var built=<?php echo (int) $built; ?>*1000, maxAge=12*3600*1000, u=new URL(location.href);
if(Date.now()-built<maxAge||u.searchParams.has('fresh'))return;
var k='unai_fresh_'+u.pathname;
try{if(sessionStorage.getItem(k))return;sessionStorage.setItem(k,'1');}catch(e){}
u.searchParams.set('fresh',Date.now());location.replace(u.toString());
}catch(e){}})();
</script>
	<?php
}, 1 );
