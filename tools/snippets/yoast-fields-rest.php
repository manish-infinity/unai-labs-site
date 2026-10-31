<?php
// Code Snippet #6 on unai-labs.com (active). Exposes Yoast focus keyphrase + meta description to REST
// for admins (edit_posts). 2026-10-31: extended from post/page to product + courses.
add_action('init', function () {
  foreach (array('post','page','product','courses') as $t) {
    foreach (array('_yoast_wpseo_focuskw','_yoast_wpseo_metadesc') as $k) {
      register_post_meta($t, $k, array('type'=>'string','single'=>true,'show_in_rest'=>true,
        'auth_callback'=>function(){ return current_user_can('edit_posts'); }));
    }
  }
});
