<?php
/**
 * Where enquiries go besides the dashboard:
 *
 *   1. Google Sheets (optional). The office creates a Google Sheet with a small Apps Script
 *      (docs/google-sheets-apps-script.gs in the repository, also printed in the client
 *      guide) and pastes its web app URL into Site settings. Each new enquiry is then sent
 *      there as one row. A shared secret code stops anyone else adding rows.
 *   2. CSV download. Dashboard > Enquiries > "Download all as CSV" opens in Excel or
 *      Google Sheets, with or without the connection above.
 *
 * The enquiry is always saved in WordPress first (inc/enquiry.php), so a Sheets or email
 * failure never loses a lead.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/** The shared secret for the Sheets connection, created the first time it is needed. */
function aaykay_sheets_secret() {
	$secret = (string) aaykay_setting( 'sheets_secret' );
	if ( strlen( $secret ) < 12 ) {
		$secret                   = wp_generate_password( 24, false );
		$options                  = get_option( AAYKAY_SETTINGS_OPTION, array() );
		$options                  = is_array( $options ) ? $options : array();
		$options['sheets_secret'] = $secret;
		update_option( AAYKAY_SETTINGS_OPTION, $options );
	}
	return $secret;
}

/**
 * Send one enquiry to the Google Sheet. Returns true when the sheet confirms it, false on
 * any failure, null when no sheet is connected.
 */
function aaykay_send_to_sheet( $data, $received ) {
	$url = trim( (string) aaykay_setting( 'sheets_url' ) );
	if ( '' === $url || 0 !== strpos( $url, 'https://script.google.com/' ) ) {
		return null;
	}
	$payload  = array_merge(
		array(
			'secret'   => aaykay_sheets_secret(),
			'received' => $received,
			'source'   => home_url( '/' ),
		),
		$data
	);
	$response = wp_remote_post(
		$url,
		array(
			'timeout'     => 8,
			'redirection' => 5,
			'headers'     => array( 'Content-Type' => 'application/json' ),
			'body'        => wp_json_encode( $payload ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return false;
	}
	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	return is_array( $body ) && ! empty( $body['ok'] );
}

/* ------------------------------------------------------------------ test button */

add_action(
	'admin_post_aaykay_sheets_test',
	function () {
		if ( ! current_user_can( AAYKAY_SETTINGS_CAP ) || ! check_admin_referer( 'aaykay_sheets_test' ) ) {
			wp_die( 'Not allowed.' );
		}
		$result = aaykay_send_to_sheet(
			array(
				'name'    => 'Test row from the website',
				'company' => 'AAYKAY website',
				'email'   => 'test@example.com',
				'phone'   => '',
				'type'    => '',
				'city'    => '',
				'message' => 'If you can read this in the sheet, the connection works. You can delete this row.',
			),
			wp_date( 'Y-m-d H:i:s' )
		);
		$state = null === $result ? 'none' : ( $result ? 'ok' : 'fail' );
		wp_safe_redirect( add_query_arg( 'aaykay_sheets', $state, admin_url( 'admin.php?page=aaykay-settings' ) ) . '#aaykay-sheets_url' );
		exit;
	}
);

/** The test button and its result, printed on the Site settings page. */
function aaykay_render_sheets_test() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display of a fixed message.
	$state    = isset( $_GET['aaykay_sheets'] ) ? sanitize_key( wp_unslash( $_GET['aaykay_sheets'] ) ) : '';
	$messages = array(
		'ok'   => array( 'success', 'The test row reached the Google Sheet. The connection works.' ),
		'fail' => array( 'error', 'The test row did not reach the sheet. Check the web app URL and that the secret code in the Apps Script matches the one here, and that the script is deployed with access for "Anyone".' ),
		'none' => array( 'warning', 'No Google Sheet is connected yet. Paste the web app URL above and save first.' ),
	);
	if ( isset( $messages[ $state ] ) ) {
		printf( '<div class="notice notice-%s inline"><p>%s</p></div>', esc_attr( $messages[ $state ][0] ), esc_html( $messages[ $state ][1] ) );
	}
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="aaykay_sheets_test">
		<?php wp_nonce_field( 'aaykay_sheets_test' ); ?>
		<p><button type="submit" class="button">Send a test row to the Google Sheet</button> <span class="description">Save the settings first.</span></p>
	</form>
	<?php
}

/* ------------------------------------------------------------------ CSV download */

add_action(
	'admin_post_aaykay_enquiries_csv',
	function () {
		if ( ! current_user_can( 'edit_others_posts' ) || ! check_admin_referer( 'aaykay_enquiries_csv' ) ) {
			wp_die( 'Not allowed.' );
		}
		$fields = aaykay_enquiry_fields();
		$posts  = get_posts(
			array(
				'post_type'   => 'aaykay_enquiry',
				'post_status' => array( 'private', 'publish' ),
				'numberposts' => -1,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="aaykay-enquiries-' . wp_date( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 mark, so Excel shows ₹ and other characters correctly.
		$head = array( 'Received' );
		foreach ( $fields as $f ) {
			$head[] = $f[0];
		}
		$head[] = 'Emailed';
		fputcsv( $out, $head );
		// A cell starting with = + - @ would run as a formula in Excel; prefix it with '.
		$safe = function ( $v ) {
			$v = (string) $v;
			return preg_match( '/^[=+\-@\t\r]/', $v ) ? "'" . $v : $v;
		};
		foreach ( $posts as $post ) {
			$row = array( get_the_date( 'Y-m-d H:i', $post ) );
			foreach ( array_keys( $fields ) as $key ) {
				$row[] = $safe( get_post_meta( $post->ID, '_aaykay_' . $key, true ) );
			}
			$mailed = get_post_meta( $post->ID, '_aaykay_mailed', true );
			$row[]  = '1' === $mailed ? 'Yes' : ( 'held' === $mailed ? 'Held' : 'No' );
			fputcsv( $out, $row );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- php://output stream.
		exit;
	}
);

// The download button above the Enquiries list.
add_action(
	'manage_posts_extra_tablenav',
	function ( $which ) {
		$screen = get_current_screen();
		if ( 'top' !== $which || ! $screen || 'edit-aaykay_enquiry' !== $screen->id ) {
			return;
		}
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=aaykay_enquiries_csv' ), 'aaykay_enquiries_csv' );
		echo '<div class="alignleft actions"><a class="button" href="' . esc_url( $url ) . '">Download all as CSV</a></div>';
	}
);
