<?php
/**
 * Snippet #25 UnAI: hide empty proof signals on course pages (Claude, 2026-11-12, roadmap R-01).
 * Why: a first-time visitor sees five empty stars, "No Review Yet", "0 Total Enrolled"
 * and "00:00" next to every lesson. Each reads as "nobody buys this" or "broken".
 * This hides only those empty signals: stars/reviews come back automatically once a
 * course has a real rating, the enrolled count returns at 25 or more, and lesson times
 * show again if a real duration is set. Nothing is invented. Revert: deactivate.
 */
add_action( 'wp_footer', function () {
	if ( ! is_singular( 'courses' ) && ! is_post_type_archive( 'courses' ) ) { return; }
	?>
<style id="unai-proof-css">
.tutor-course-content-list-item-duration.unai-zero{display:none!important}
.unai-hide{display:none!important}
</style>
<script id="unai-proof-js">
(function(){
  document.querySelectorAll('.tutor-course-content-list-item-duration').forEach(function(e){
    if (/^\s*0?0:00(:00)?\s*$/.test(e.textContent)) e.classList.add('unai-zero');
  });
  document.querySelectorAll('.tutor-course-details-ratings, .tutor-course-ratings, .tutor-ratings').forEach(function(r){
    if (!r.querySelector('.tutor-icon-star-bold, .tutor-icon-star-half-bold, .tutor-icon-star-fill')) r.classList.add('unai-hide');
  });
  var empty = document.querySelector('#tutor-course-details-tab-reviews .tutor-empty-state');
  if (empty) {
    var tab = document.getElementById('tutor-course-details-tab-reviews'); if (tab) tab.classList.add('unai-hide');
    document.querySelectorAll('[data-tutor-nav-target="tutor-course-details-tab-reviews"], a[href="#tutor-course-details-tab-reviews"]').forEach(function(n){ var li = n.closest('li') || n; li.classList.add('unai-hide'); });
  }
  document.querySelectorAll('[aria-labelledby="Total Enrolled"]').forEach(function(i){
    var li = i.closest('li'); if (!li) return;
    var n = parseInt((li.textContent.match(/(\d+)/) || [0, 0])[1], 10);
    if (n < 25) li.classList.add('unai-hide');
  });
})();
</script>
	<?php
}, 99 );
