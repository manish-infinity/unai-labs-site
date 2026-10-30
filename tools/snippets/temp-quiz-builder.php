<?php
/**
 * UnAI temp: Tutor quiz builder (Claude, 2026-10-31). DEACTIVATE AFTER USE.
 * Admin-only REST routes (manage_options + REST nonce). No public surface.
 *  GET  /wp-json/unai-tmp/v1/quiz-inspect?course=ID
 *  POST /wp-json/unai-tmp/v1/quiz-build  {topic_id, course_id, title, order, questions:[{title,type,explanation,answers:[{t,c}]}]}
 * Idempotent: skips if a quiz with the same title already exists under the topic.
 */
add_action( 'rest_api_init', function () {
	$perm = function () { return current_user_can( 'manage_options' ); };

	register_rest_route( 'unai-tmp/v1', '/quiz-inspect', array(
		'methods'             => 'GET',
		'permission_callback' => $perm,
		'callback'            => function ( WP_REST_Request $r ) {
			global $wpdb;
			$course = absint( $r->get_param( 'course' ) );
			$out    = array( 'tables' => array() );
			foreach ( array( 'tutor_quiz_questions', 'tutor_quiz_question_answers' ) as $t ) {
				$out['tables'][ $t ] = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->prefix}{$t}" );
			}
			$topics = get_posts( array( 'post_type' => 'topics', 'post_parent' => $course, 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC', 'post_status' => 'any' ) );
			foreach ( $topics as $tp ) {
				$kids = get_posts( array( 'post_type' => 'any', 'post_parent' => $tp->ID, 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC', 'post_status' => 'any' ) );
				$out['topics'][] = array(
					'id'    => $tp->ID,
					'title' => $tp->post_title,
					'order' => $tp->menu_order,
					'items' => array_map( function ( $k ) { return array( $k->ID, $k->post_type, $k->post_status, $k->menu_order, $k->post_title ); }, $kids ),
				);
			}
			$any = get_posts( array( 'post_type' => 'tutor_quiz', 'posts_per_page' => 3, 'post_status' => 'any' ) );
			if ( $any ) {
				$out['sample_quiz_meta'] = get_post_meta( $any[0]->ID );
			}
			return $out;
		},
	) );

	register_rest_route( 'unai-tmp/v1', '/quiz-build', array(
		'methods'             => 'POST',
		'permission_callback' => $perm,
		'callback'            => function ( WP_REST_Request $r ) {
			global $wpdb;
			$p        = $r->get_json_params();
			$topic_id = absint( $p['topic_id'] ?? 0 );
			$course   = absint( $p['course_id'] ?? 0 );
			$title    = sanitize_text_field( $p['title'] ?? '' );
			$topic    = get_post( $topic_id );
			if ( ! $topic || 'topics' !== $topic->post_type || (int) $topic->post_parent !== $course || '' === $title ) {
				return new WP_Error( 'bad', 'topic/course mismatch', array( 'status' => 400 ) );
			}
			$existing = get_posts( array( 'post_type' => 'tutor_quiz', 'post_parent' => $topic_id, 'title' => $title, 'post_status' => 'any', 'posts_per_page' => 1 ) );
			if ( $existing ) {
				return array( 'skipped' => true, 'quiz_id' => $existing[0]->ID );
			}
			$quiz_id = wp_insert_post( array(
				'post_type'    => 'tutor_quiz',
				'post_title'   => $title,
				'post_content' => wp_kses_post( $p['description'] ?? '' ),
				'post_status'  => 'publish',
				'post_parent'  => $topic_id,
				'menu_order'   => absint( $p['order'] ?? 99 ),
				'post_author'  => get_current_user_id(),
			), true );
			if ( is_wp_error( $quiz_id ) ) {
				return $quiz_id;
			}
			update_post_meta( $quiz_id, '_tutor_course_id_for_lesson', $course );
			update_post_meta( $quiz_id, 'tutor_quiz_option', array(
				'time_limit'                 => array( 'time_value' => 0, 'time_type' => 'minutes' ),
				'hide_quiz_time_display'     => 0,
				'feedback_mode'              => 'retry',
				'attempts_allowed'           => 0,
				'pass_is_required'           => 0,
				'passing_grade'              => 80,
				'max_questions_for_answer'   => 0,
				'quiz_auto_start'            => 0,
				'question_layout_view'       => 'single_question',
				'questions_order'            => 'sorting',
				'hide_question_number_overview' => 0,
				'short_answer_characters_limit' => 200,
				'open_ended_answer_characters_limit' => 500,
			) );
			$qt = $wpdb->prefix . 'tutor_quiz_questions';
			$at = $wpdb->prefix . 'tutor_quiz_question_answers';
			$n  = 0;
			foreach ( (array) ( $p['questions'] ?? array() ) as $i => $q ) {
				$type = in_array( $q['type'] ?? '', array( 'true_false', 'single_choice' ), true ) ? $q['type'] : 'single_choice';
				$wpdb->insert( $qt, array(
					'quiz_id'            => $quiz_id,
					'question_title'     => sanitize_text_field( $q['title'] ),
					'question_description' => '',
					'answer_explanation' => wp_kses_post( $q['explanation'] ?? '' ),
					'question_type'      => $type,
					'question_mark'      => 1,
					'question_settings'  => maybe_serialize( array( 'question_type' => $type, 'answer_required' => '1', 'question_mark' => '1', 'randomize_question' => '0', 'show_question_mark' => '0' ) ),
					'question_order'     => $i + 1,
				) );
				$qid = (int) $wpdb->insert_id;
				foreach ( (array) $q['answers'] as $j => $a ) {
					$wpdb->insert( $at, array(
						'belongs_question_id'   => $qid,
						'belongs_question_type' => $type,
						'answer_title'          => sanitize_text_field( $a['t'] ),
						'is_correct'            => empty( $a['c'] ) ? 0 : 1,
						'image_id'              => 0,
						'answer_two_gap_match'  => '',
						'answer_view_format'    => 'text',
						'answer_settings'       => '',
						'answer_order'          => $j + 1,
					) );
				}
				$n++;
			}
			return array( 'quiz_id' => $quiz_id, 'questions' => $n );
		},
	) );
} );
