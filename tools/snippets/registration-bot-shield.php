// UnAI: registration bot shield (Claude, 2026-10-30)
// Keeps student sign-up OPEN for humans and blocks scripted sign-ups.
// 1) The old WordPress-core sign-up form (wp-login.php?action=register) is the classic bot path.
//    Anyone hitting it is sent to the real student sign-up page instead.
// 2) The Tutor student sign-up form gets a signed form token (added by JavaScript), a hidden
//    honeypot field, and a 3-second minimum fill time. A POST without a valid token is refused.
// Checkout account creation (WooCommerce) is NOT touched, so buying always works.

if ( ! defined( 'ABSPATH' ) ) { return; }

add_action( 'login_form_register', function () {
	wp_safe_redirect( home_url( '/student-registration/' ), 302 );
	exit;
} );

add_action( 'wp_footer', function () {
	$ts  = time();
	$sig = wp_hash( 'unai_reg_' . $ts );
	?>
<script>
(function(){
  var forms = [];
  document.querySelectorAll('form').forEach(function(f){
    var a = f.querySelector('input[name="tutor_action"]');
    if ((a && a.value === 'tutor_register_student') || f.id === 'tutor-registration-form' || f.classList.contains('tutor-registration-form')) { forms.push(f); }
  });
  forms.forEach(function(form){
    if (form.querySelector('input[name="unai_sig"]')) { return; }
    var w = document.createElement('div');
    w.setAttribute('aria-hidden', 'true');
    w.style.cssText = 'position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;';
    w.innerHTML = '<label>Leave this field empty <input type="text" name="unai_website" tabindex="-1" autocomplete="off" value=""></label>';
    form.appendChild(w);
    var t = document.createElement('input'); t.type = 'hidden'; t.name = 'unai_ts';  t.value = '<?php echo (int) $ts; ?>'; form.appendChild(t);
    var s = document.createElement('input'); s.type = 'hidden'; s.name = 'unai_sig'; s.value = '<?php echo esc_js( $sig ); ?>'; form.appendChild(s);
  });
})();
</script>
	<?php
}, 99 );

add_action( 'init', function () {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { return; }
	if ( ( $_POST['tutor_action'] ?? '' ) !== 'tutor_register_student' ) { return; }

	$ts   = isset( $_POST['unai_ts'] ) ? (int) $_POST['unai_ts'] : 0;
	$sig  = isset( $_POST['unai_sig'] ) ? sanitize_text_field( wp_unslash( $_POST['unai_sig'] ) ) : '';
	$trap = isset( $_POST['unai_website'] ) ? trim( (string) wp_unslash( $_POST['unai_website'] ) ) : '';

	$ok = $ts > 0
		&& hash_equals( wp_hash( 'unai_reg_' . $ts ), $sig )
		&& '' === $trap
		&& ( time() - $ts ) >= 3;

	if ( $ok ) { return; }

	wp_die(
		'<p>Sorry, we could not verify this sign-up.</p><p>Please go back, wait a few seconds and submit the form again. If it keeps failing, write to hello@unai-labs.com and we will set up your account.</p><p><a href="' . esc_url( home_url( '/student-registration/' ) ) . '">Back to sign-up</a></p>',
		'Sign-up check',
		array( 'response' => 403 )
	);
}, 1 );
