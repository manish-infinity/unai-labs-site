<?php
/**
 * UnAI Labs: course completion certificate.
 * When a learner reaches 100% on a Tutor LMS course, generate a one-page PDF
 * certificate (name, course, date, certificate ID) and email it to them.
 * No plugins, no external services. Issued once per learner per course.
 */

if ( ! function_exists( 'unai_cert_widths' ) ) {

	function unai_cert_widths() {
		static $w = null;
		if ( null === $w ) {
			$raw = array(
			'Helvetica' => '278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584,761,556,761,222,556,333,1000,556,556,333,1000,667,333,1000,761,611,761,761,222,222,333,333,350,556,1000,333,1000,500,333,944,761,500,667,278,333,556,556,556,556,260,556,333,737,370,556,584,333,737,333,400,584,333,333,333,556,537,278,333,333,365,556,834,834,834,611,667,667,667,667,667,667,1000,722,667,667,667,667,278,278,278,278,722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,556,556,556,556,556,556,889,500,556,556,556,556,278,278,278,278,556,556,556,556,556,556,556,584,611,556,556,556,556,500,556,500',
			'Helvetica-Bold' => '278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,611,611,389,556,333,611,556,778,556,556,500,389,280,389,584,761,556,761,278,556,500,1000,556,556,333,1000,667,333,1000,761,611,761,761,278,278,500,500,350,556,1000,333,1000,556,333,944,761,500,667,278,333,556,556,556,556,280,556,333,737,370,556,584,333,737,333,400,584,333,333,333,611,556,278,333,333,365,556,834,834,834,611,722,722,722,722,722,722,1000,722,667,667,667,667,278,278,278,278,722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,556,556,556,556,556,556,889,556,556,556,556,556,278,278,278,278,611,611,611,611,611,611,611,584,611,611,611,611,611,556,611,556',
			'Times-Roman' => '250,333,408,500,500,833,778,180,333,333,500,564,250,333,250,278,500,500,500,500,500,500,500,500,500,500,278,278,564,564,564,444,921,722,667,667,722,611,556,722,722,333,389,722,611,889,722,722,556,722,667,556,611,722,722,944,722,722,611,333,278,333,469,500,333,444,500,444,500,444,333,500,500,278,278,500,278,778,500,500,500,500,333,389,278,500,500,722,500,500,444,480,200,480,541,761,500,761,333,500,444,1000,500,500,333,1000,556,333,889,761,611,761,761,333,333,444,444,350,500,1000,333,980,389,333,722,761,444,722,250,333,500,500,500,500,200,500,333,760,276,500,564,333,760,333,400,564,300,300,333,500,453,250,333,300,310,500,750,750,750,444,722,722,722,722,722,722,889,667,611,611,611,611,333,333,333,333,722,722,722,722,722,722,722,564,722,722,722,722,722,722,556,500,444,444,444,444,444,444,667,444,444,444,444,444,278,278,278,278,500,500,500,500,500,500,500,564,500,500,500,500,500,500,500,500',
			'Times-Bold' => '250,333,555,500,500,1000,833,278,333,333,500,570,250,333,250,278,500,500,500,500,500,500,500,500,500,500,333,333,570,570,570,500,930,722,667,722,722,667,611,778,778,389,500,778,667,944,722,778,611,778,722,556,667,722,722,1000,722,722,667,333,278,333,581,500,333,500,556,444,556,444,333,500,556,278,333,556,278,833,556,500,556,556,444,389,333,556,500,722,500,500,444,394,220,394,520,761,500,761,333,500,500,1000,500,500,333,1000,556,333,1000,761,667,761,761,333,333,500,500,350,500,1000,333,1000,389,333,722,761,444,722,250,333,500,500,500,500,220,500,333,747,300,500,570,333,747,333,400,570,300,300,333,556,540,250,333,300,330,500,750,750,750,500,722,722,722,722,722,722,1000,722,667,667,667,667,389,389,389,389,722,722,778,778,778,778,778,570,778,722,722,722,722,722,611,556,500,500,500,500,500,500,722,444,444,444,444,444,278,278,278,278,500,556,500,500,500,500,500,570,500,556,556,556,556,500,556,500',
			'Times-Italic' => '250,333,420,500,500,833,778,214,333,333,500,675,250,333,250,278,500,500,500,500,500,500,500,500,500,500,333,333,675,675,675,500,920,611,611,667,722,611,611,722,722,333,444,667,556,833,667,722,611,722,611,500,556,722,611,833,611,556,556,389,278,389,422,500,333,500,500,444,500,444,278,500,500,278,278,444,278,722,500,500,500,500,389,389,278,500,444,667,444,444,389,400,275,400,541,761,500,761,333,500,556,889,500,500,333,1000,500,333,944,761,556,761,761,333,333,556,556,350,500,889,333,980,389,333,667,761,389,556,250,389,500,500,500,500,275,500,333,760,276,500,675,333,760,333,400,675,300,300,333,500,523,250,333,300,310,500,750,750,750,500,611,611,611,611,611,611,889,667,611,611,611,611,333,333,333,333,722,667,722,722,722,722,722,675,722,722,722,722,722,556,611,500,500,500,500,500,500,500,667,444,444,444,444,444,278,278,278,278,500,500,500,500,500,500,500,675,500,500,500,500,500,444,500,444',
			);
			$w = array();
			foreach ( $raw as $k => $v ) {
				$w[ $k ] = array_map( 'intval', explode( ',', $v ) );
			}
		}
		return $w;
	}

	function unai_cert_enc( $s ) {
		$s = trim( html_entity_decode( wp_strip_all_tags( (string) $s ), ENT_QUOTES, 'UTF-8' ) );
		$s = str_replace( array( "\u{2014}", "\u{2013}" ), '-', $s );
		$t = function_exists( 'iconv' ) ? @iconv( 'UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s ) : false;
		return ( false === $t ) ? preg_replace( '/[^\x20-\x7E]/', '', $s ) : $t;
	}

	function unai_cert_tw( $s, $font, $size, $cs = 0 ) {
		$w = unai_cert_widths()[ $font ];
		$t = 0;
		$n = strlen( $s );
		for ( $i = 0; $i < $n; $i++ ) {
			$c = ord( $s[ $i ] );
			$t += ( $c >= 32 ) ? $w[ $c - 32 ] : 0;
		}
		return $t * $size / 1000 + $cs * max( 0, $n - 1 );
	}

	function unai_cert_pdf( $name, $course, $date, $cid ) {
		$W     = 842;
		$H     = 595;
		$fk    = array( 'Helvetica' => 'F1', 'Helvetica-Bold' => 'F2', 'Times-Roman' => 'F3', 'Times-Bold' => 'F4', 'Times-Italic' => 'F5' );
		$navy  = array( 0.059, 0.118, 0.239 );
		$gold  = array( 0.69, 0.55, 0.24 );
		$grey  = array( 0.25, 0.29, 0.38 );
		$c     = '';
		// $cx = centre x of the text; size shrinks to fit $max width.
		$text = function ( $s, $font, $size, $y, $rgb, $cx = null, $cs = 0, $max = 700 ) use ( &$c, $W, $fk ) {
			$s = unai_cert_enc( $s );
			while ( $size > 9 && unai_cert_tw( $s, $font, $size, $cs ) > $max ) {
				$size -= 0.5;
			}
			$cx = ( null === $cx ) ? $W / 2 : $cx;
			$x  = $cx - unai_cert_tw( $s, $font, $size, $cs ) / 2;
			$s  = str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $s );
			$c .= sprintf( "BT /%s %.2F Tf %.3F %.3F %.3F rg %.2F Tc %.2F %.2F Td (%s) Tj ET\n", $fk[ $font ], $size, $rgb[0], $rgb[1], $rgb[2], $cs, $x, $y, $s );
		};
		$line = function ( $x1, $y1, $x2, $y2, $rgb, $wd ) use ( &$c ) {
			$c .= sprintf( "%.3F %.3F %.3F RG %.2F w %.2F %.2F m %.2F %.2F l S\n", $rgb[0], $rgb[1], $rgb[2], $wd, $x1, $y1, $x2, $y2 );
		};

		// Background and frames.
		$c .= "0.969 0.980 1 rg 0 0 842 595 re f\n";
		$c .= sprintf( "%.3F %.3F %.3F RG 3 w 22 22 798 551 re S\n", $navy[0], $navy[1], $navy[2] );
		$c .= sprintf( "%.3F %.3F %.3F RG 0.8 w 32 32 778 531 re S\n", $gold[0], $gold[1], $gold[2] );

		$text( 'UnAI Labs', 'Helvetica-Bold', 17, 515, $navy, null, 0.5 );
		$text( 'Your Brain Is More Powerful Than Any AI', 'Helvetica', 9, 500, $grey, null, 0.4 );

		$text( 'CERTIFICATE OF COMPLETION', 'Helvetica-Bold', 26, 435, $navy, null, 3 );
		$line( 381, 418, 461, 418, $gold, 1.5 );

		$text( 'This certifies that', 'Times-Italic', 16, 382, $grey );
		$text( $name, 'Times-Bold', 40, 330, $navy, null, 0, 620 );
		$line( 211, 316, 631, 316, $gold, 0.8 );
		$text( 'has successfully completed the online course', 'Times-Italic', 16, 284, $grey );
		$text( $course, 'Helvetica-Bold', 22, 248, $navy, null, 0, 660 );

		// Bottom row: date / signature / certificate ID.
		$cols = array(
			array( 211, $date, 'Helvetica', 12, 'DATE' ),
			array( 421, 'Manish Kumar Singh', 'Times-Italic', 17, 'FOUNDER, UNAI LABS' ),
			array( 631, $cid, 'Helvetica', 12, 'CERTIFICATE ID' ),
		);
		foreach ( $cols as $col ) {
			$text( $col[1], $col[2], $col[3], 140, $navy, $col[0], 0, 170 );
			$line( $col[0] - 85, 132, $col[0] + 85, 132, $grey, 0.6 );
			$text( $col[4], 'Helvetica', 8, 118, $grey, $col[0], 1 );
		}
		$text( 'unai-labs.com', 'Helvetica', 9, 58, $grey, null, 0.5 );

		// Assemble the PDF.
		$objs   = array();
		$objs[] = '<< /Type /Catalog /Pages 2 0 R >>';
		$objs[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
		$fonts  = '';
		$i      = 5;
		foreach ( $fk as $base => $key ) {
			$fonts .= "/$key $i 0 R ";
			$i++;
		}
		$objs[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $W $H] /Resources << /Font << $fonts>> >> /Contents 4 0 R >>";
		$objs[] = '<< /Length ' . strlen( $c ) . " >>\nstream\n" . $c . "endstream";
		foreach ( $fk as $base => $key ) {
			$objs[] = "<< /Type /Font /Subtype /Type1 /BaseFont /$base /Encoding /WinAnsiEncoding >>";
		}
		$pdf = "%PDF-1.4\n";
		$off = array();
		foreach ( $objs as $n => $o ) {
			$off[] = strlen( $pdf );
			$pdf  .= ( $n + 1 ) . " 0 obj\n$o\nendobj\n";
		}
		$x    = strlen( $pdf );
		$pdf .= 'xref' . "\n0 " . ( count( $objs ) + 1 ) . "\n0000000000 65535 f \n";
		foreach ( $off as $o ) {
			$pdf .= sprintf( "%010d 00000 n \n", $o );
		}
		$pdf .= 'trailer << /Size ' . ( count( $objs ) + 1 ) . " /Root 1 0 R >>\nstartxref\n$x\n%%EOF";
		return $pdf;
	}

	function unai_cert_name( $user ) {
		$first = get_user_meta( $user->ID, 'billing_first_name', true ) ?: $user->first_name;
		$last  = get_user_meta( $user->ID, 'billing_last_name', true ) ?: $user->last_name;
		$name  = trim( $first . ' ' . $last );
		return array( $name ? $name : $user->display_name, $first ? $first : $user->display_name );
	}

	function unai_cert_issue( $course_id, $user_id, $force = false ) {
		$course_id = (int) $course_id;
		$user_id   = (int) $user_id;
		if ( ! $course_id || ! $user_id || ! function_exists( 'tutor_utils' ) ) {
			return;
		}
		$key = '_unai_cert_' . $course_id;
		if ( get_user_meta( $user_id, $key, true ) ) {
			return; // already issued
		}
		if ( ! $force && (float) tutor_utils()->get_course_completed_percent( $course_id, $user_id ) < 100 ) {
			return;
		}
		$user = get_userdata( $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		list( $name, $first ) = unai_cert_name( $user );
		$course = get_the_title( $course_id );
		$date   = wp_date( 'j F Y' );
		$cid    = 'UNAI-' . $course_id . '-' . strtoupper( substr( md5( $user_id . '|' . $course_id . '|' . wp_salt( 'auth' ) ), 0, 8 ) );
		update_user_meta( $user_id, $key, array( 'id' => $cid, 'date' => $date, 'name' => $name ) );

		$file = trailingslashit( get_temp_dir() ) . 'UnAI-Labs-Certificate-' . $course_id . '-' . $user_id . '.pdf';
		file_put_contents( $file, unai_cert_pdf( $name, $course, $date, $cid ) );

		$body = '<p>Hi ' . esc_html( $first ) . ',</p>'
			. '<p>Congratulations on completing <strong>' . esc_html( $course ) . '</strong>. Your certificate of completion is attached as a PDF.</p>'
			. '<p>Certificate ID: ' . esc_html( $cid ) . '</p>'
			. '<p>Keep going: <a href="' . esc_url( home_url( '/courses/' ) ) . '">see the other UnAI Labs courses</a>.</p>'
			. '<p>Manish Kumar Singh<br>UnAI Labs</p>';
		$headers = array( 'Content-Type: text/html; charset=UTF-8', 'Bcc: ' . get_option( 'admin_email' ) );
		$sent = wp_mail( $user->user_email, 'Your certificate: ' . wp_strip_all_tags( $course ), $body, $headers, array( $file ) );
		@unlink( $file );
		if ( ! $sent ) {
			delete_user_meta( $user_id, $key ); // allow a retry on the next completion event
		}
	}

	// Triggers: the explicit "Complete course" action, plus any lesson or quiz that takes progress to 100%.
	add_action( 'tutor_course_complete_after', function ( $course_id, $user_id ) {
		unai_cert_issue( $course_id, $user_id, true );
	}, 20, 2 );
	add_action( 'tutor_mark_lesson_complete_after', function ( $lesson_id, $user_id ) {
		unai_cert_issue( tutor_utils()->get_course_id_by( 'lesson', $lesson_id ), $user_id );
	}, 20, 2 );
	add_action( 'tutor_quiz/attempt_ended', function ( $attempt_id, $course_id, $user_id ) {
		unai_cert_issue( $course_id, $user_id );
	}, 20, 3 );

	// Admin-only preview: /wp-admin/admin-ajax.php?action=unai_cert_preview&course_id=30
	add_action( 'wp_ajax_unai_cert_preview', function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'no' );
		}
		$course_id = isset( $_GET['course_id'] ) ? (int) $_GET['course_id'] : 30;
		$name      = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : 'Sample Learner';
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: inline; filename="certificate-preview.pdf"' );
		echo unai_cert_pdf( $name, get_the_title( $course_id ), wp_date( 'j F Y' ), 'UNAI-' . $course_id . '-PREVIEW' ); // phpcs:ignore
		exit;
	} );
}
