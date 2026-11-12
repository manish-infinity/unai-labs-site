<?php
/**
 * Snippet #24 UnAI: course page buy bar on phones (Claude, 2026-11-12, roadmap R-01).
 * Why: on a phone the price and Add to cart sit below the whole curriculum, so a
 * first-time visitor scrolls 20+ lessons before seeing a price. This shows a slim bar
 * at the bottom with the live price (copied from the page's own price box) and a
 * button that clicks the page's own Add to cart button. No new prices, no new
 * purchase logic, nothing sent to the server. Hidden when the real button is on screen,
 * on wide screens, and for anyone who cannot buy (no Add to cart form on the page).
 * Revert: deactivate this snippet.
 */
add_action( 'wp_footer', function () {
	if ( ! is_singular( 'courses' ) ) { return; }
	?>
<style id="unai-buybar-css">
#unai-buybar{position:fixed;left:0;right:0;bottom:0;z-index:9990;display:none;align-items:center;justify-content:space-between;gap:12px;padding:10px 16px calc(10px + env(safe-area-inset-bottom));background:#fff;border-top:1px solid #dfe5f0;box-shadow:0 -4px 16px rgba(15,30,61,.08);font-family:inherit}
#unai-buybar .p{font-size:18px;font-weight:700;color:#0f1e3d;line-height:1.2}
#unai-buybar .p del{font-size:13px;font-weight:400;color:#6b778c;margin-left:6px}
#unai-buybar button{flex:0 0 auto;background:#0f1e3d;color:#fff;border:0;border-radius:8px;padding:12px 20px;font-size:16px;font-weight:600;cursor:pointer}
@media (min-width:1200px){#unai-buybar{display:none!important}}
</style>
<script id="unai-buybar-js">
(function(){
  if (window.innerWidth >= 1200) return;
  var btn = document.querySelector('.tutor-sidebar-card button[name="add-to-cart"]');
  var priceBox = document.querySelector('.tutor-course-sidebar-card-pricing');
  if (!btn || !priceBox) return;
  var ins = priceBox.querySelector('.tutor-fs-4 .woocommerce-Price-amount');
  var del = priceBox.querySelector('del .woocommerce-Price-amount');
  if (!ins) return;
  var bar = document.createElement('div'); bar.id = 'unai-buybar';
  var p = document.createElement('div'); p.className = 'p';
  p.textContent = ins.textContent.replace(/\.00$/, '').trim();
  if (del) { var d = document.createElement('del'); d.textContent = del.textContent.replace(/\.00$/, '').trim(); p.appendChild(d); }
  var b = document.createElement('button'); b.type = 'button'; b.textContent = 'Add to cart';
  b.addEventListener('click', function(){ btn.click(); });
  bar.appendChild(p); bar.appendChild(b); document.body.appendChild(bar);
  var show = function(v){ bar.style.display = v ? 'flex' : 'none'; };
  show(true);
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function(e){ show(!e[0].isIntersecting); }).observe(btn);
  }
})();
</script>
	<?php
}, 99 );
