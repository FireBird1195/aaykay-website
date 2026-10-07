<?php
/**
 * The enquiry form.
 *
 * The form posts to wp-admin/admin-post.php (action "aaykay_enquiry"). site.js sends it in
 * the background and shows the result in place; without JavaScript the browser posts it
 * normally and comes back to the contact section with a message.
 *
 * Every valid enquiry is saved under Dashboard > Enquiries first and then emailed to the
 * "Send enquiries to" address in Site settings, so a lead is never lost if email fails.
 *
 * Spam protection without third-party services: a hidden field people never fill in
 * (honeypot) and a minimum time on the page (measured by site.js; required whenever the
 * form is sent by script). A connection that sends many enquiries in an hour still has
 * them saved, but they are not emailed; only a flood is turned away. There is no nonce on
 * purpose: the page is cached for visitors, and a cached nonce would expire and block
 * real enquiries.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

const AAYKAY_ENQUIRY_EMAIL_LIMIT = 20; // Per connection per hour; beyond this, saved but not emailed.
const AAYKAY_ENQUIRY_HARD_LIMIT  = 60; // Per connection per hour; beyond this, refused.

/** Options in the "Project type" select (also the only values the server accepts). */
function aaykay_project_types() {
	$types = array_diff( aaykay_lines( 'contact', 'project_types' ), array( 'Other' ) );
	$types = array_slice( array_values( $types ), 0, 20 );
	$types[] = 'Other';
	return $types;
}

/** Field name => array( label, required, max length, words for "Please enter your ..." ). */
function aaykay_enquiry_fields() {
	return array(
		'name'    => array( 'Name', true, 100, 'name' ),
		'company' => array( 'Company', true, 150, 'company name' ),
		'email'   => array( 'Work email', true, 254, 'email address' ),
		'phone'   => array( 'Phone', false, 40, '' ),
		'type'    => array( 'Project type', false, 60, '' ),
		'city'    => array( 'City', false, 100, '' ),
		'message' => array( 'Scope and timeline', true, 5000, 'project scope' ),
	);
}

/**
 * Format rules for each field, so what arrives is usable rather than garbage. site.js
 * applies the same rules as the visitor types (the patterns are kept identical there);
 * these server checks are the ones that count, because a browser can be bypassed.
 *
 *   name     letters (any language), spaces, full stops, apostrophes and hyphens; no digits
 *   company  must contain letters; letters, digits, spaces and & . , ( ) ' / + -
 *   email    a real-looking address (WordPress is_email(), plus a dot in the domain)
 *   phone    optional; digits, spaces, + ( ) -, with 7 to 15 digits in total
 *   city     optional; letters, spaces, full stops, apostrophes and hyphens
 *   message  at least 10 characters, including some letters
 *
 * Returns field => message for every field that breaks a rule.
 */
function aaykay_enquiry_check_formats( $d ) {
	$e      = array();
	$letter = '/\p{L}/u';
	if ( '' !== $d['name'] && ( ! preg_match( "/^\\p{L}[\\p{L}\\p{M}\\s.'’-]*$/u", $d['name'] ) || mb_strlen( $d['name'] ) < 2 ) ) {
		$e['name'] = 'Please use letters only for your name (no numbers or symbols).';
	}
	if ( '' !== $d['company'] && ( ! preg_match( "/^[\\p{L}\\p{N}][\\p{L}\\p{M}\\p{N}\\s&.,()'’\\/+-]*$/u", $d['company'] ) || preg_match_all( $letter, $d['company'] ) < 2 ) ) {
		$e['company'] = 'Please enter your company’s name.';
	}
	if ( '' !== $d['email'] && ( ! is_email( $d['email'] ) || ! preg_match( '/@[^@\s]+\.[a-z]{2,}$/i', $d['email'] ) ) ) {
		$e['email'] = 'Please enter an email address like name@company.com.';
	}
	if ( '' !== $d['phone'] ) {
		$digits = preg_replace( '/\D/', '', $d['phone'] );
		if ( ! preg_match( '/^\+?[\d\s()-]+$/', $d['phone'] ) || strlen( $digits ) < 7 || strlen( $digits ) > 15 ) {
			$e['phone'] = 'Please enter a phone number using digits only, e.g. +91 98480 12345.';
		}
	}
	if ( '' !== $d['city'] && ! preg_match( "/^\\p{L}[\\p{L}\\p{M}\\s.'’-]*$/u", $d['city'] ) ) {
		$e['city'] = 'Please use letters only for the city.';
	}
	if ( '' !== $d['message'] && ( mb_strlen( $d['message'] ) < 10 || preg_match_all( $letter, $d['message'] ) < 5 ) ) {
		$e['message'] = 'Please describe the scope and timeline in a few words.';
	}
	return $e;
}

add_action(
	'init',
	function () {
		register_post_type(
			'aaykay_enquiry',
			array(
				'labels'              => array(
					'name'               => 'Enquiries',
					'singular_name'      => 'Enquiry',
					'edit_item'          => 'Enquiry',
					'search_items'       => 'Search enquiries',
					'not_found'          => 'No enquiries yet',
					'not_found_in_trash' => 'No enquiries in the bin',
					'all_items'          => 'All enquiries',
					'menu_name'          => 'Enquiries',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_icon'           => 'dashicons-email-alt',
				'menu_position'       => 24,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);
	}
);

add_action( 'admin_post_nopriv_aaykay_enquiry', 'aaykay_handle_enquiry' );
add_action( 'admin_post_aaykay_enquiry', 'aaykay_handle_enquiry' );

function aaykay_handle_enquiry() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form, see the file header.
	$wants_json = isset( $_SERVER['HTTP_ACCEPT'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ), 'application/json' );

	// Bots: the honeypot is filled in, or the form was sent within 3 seconds of the page
	// opening. site.js always reports the time; a plain HTML post (no JavaScript) can't, so
	// only those are let through without it. Answer as if it worked so bots learn nothing,
	// and store nothing.
	$honeypot = isset( $_POST['website'] ) ? ( is_string( $_POST['website'] ) ? trim( wp_unslash( $_POST['website'] ) ) : 'not a string' ) : '';
	$elapsed  = isset( $_POST['elapsed'] ) && is_string( $_POST['elapsed'] ) && '' !== $_POST['elapsed'] ? (int) $_POST['elapsed'] : -1;
	$too_fast = $wants_json ? $elapsed < 3000 : ( $elapsed >= 0 && $elapsed < 3000 );
	if ( '' !== $honeypot || $too_fast ) {
		aaykay_enquiry_respond( $wants_json, true );
	}

	$data             = array();
	$errors           = array();
	$required_missing = array();
	foreach ( aaykay_enquiry_fields() as $key => $f ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$raw = is_string( $raw ) ? $raw : '';
		$val = 'message' === $key ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		$val = mb_substr( trim( $val ), 0, $f[2] ); // WordPress provides mb_substr() if mbstring is missing.
		if ( $f[1] && '' === $val ) {
			$errors[ $key ]     = 'Please enter your ' . $f[3] . '.';
			$required_missing[] = $key;
		}
		$data[ $key ] = $val;
	}
	// phpcs:enable
	$errors = array_merge( aaykay_enquiry_check_formats( $data ), $errors );
	if ( '' !== $data['type'] && ! in_array( $data['type'], aaykay_project_types(), true ) ) {
		$data['type'] = 'Other';
	}
	if ( $errors ) {
		$missing = (bool) $required_missing;
		aaykay_enquiry_respond( $wants_json, false, $errors, $missing ? 'Some required details are missing. They are marked above.' : 'Some details need a correction. They are marked above.' );
	}

	// Count enquiries per connection per hour (the address is hashed, never stored). Many
	// people can share one mobile-network address, so a busy hour is saved without email
	// rather than refused; only a flood is turned away.
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key   = 'aaykay_enq_' . md5( $ip . wp_salt( 'nonce' ) );
	$count = (int) get_transient( $key );
	if ( $count >= AAYKAY_ENQUIRY_HARD_LIMIT ) {
		$contact = aaykay_contact();
		aaykay_enquiry_respond( $wants_json, false, array(), 'Too many enquiries have been sent from this connection in the last hour. Please email ' . $contact['email'] . ' or call ' . $contact['phone'] . '.' );
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	$send_email = $count < AAYKAY_ENQUIRY_EMAIL_LIMIT;

	// 1. Save it.
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'aaykay_enquiry',
			'post_status' => 'private',
			'post_title'  => $data['company'] . ' — ' . $data['name'],
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		aaykay_enquiry_respond( $wants_json, false, array(), 'Sorry, your enquiry could not be sent. Please email or call us instead.' );
	}
	foreach ( $data as $k => $v ) {
		update_post_meta( $post_id, '_aaykay_' . $k, $v );
	}

	// 2. Add it to the Google Sheet, if one is connected (inc/leads.php). Not for a busy hour
	//    from one connection, which is held for a person to check first.
	if ( $send_email ) {
		$sheet = aaykay_send_to_sheet( $data, get_post_time( 'Y-m-d H:i:s', false, $post_id ) );
		if ( null !== $sheet ) {
			update_post_meta( $post_id, '_aaykay_sheet', $sheet ? '1' : '0' );
		}
	}

	// 3. Email it.
	if ( ! $send_email ) {
		update_post_meta( $post_id, '_aaykay_mailed', 'held' );
		aaykay_enquiry_respond( $wants_json, true );
	}
	$to = array_filter( array_map( 'trim', explode( ',', (string) aaykay_setting( 'enquiry_to' ) ) ), 'is_email' );
	if ( ! $to ) {
		$to = array( get_option( 'admin_email' ) );
	}
	$subject = 'Website enquiry: ' . $data['company'] . ( '' !== $data['type'] ? ' (' . $data['type'] . ')' : '' );
	$lines   = array();
	foreach ( aaykay_enquiry_fields() as $k => $f ) {
		if ( 'message' !== $k && '' !== $data[ $k ] ) {
			$lines[] = $f[0] . ': ' . $data[ $k ];
		}
	}
	$lines[] = '';
	$lines[] = $data['message'];
	$lines[] = '';
	$lines[] = '—';
	$lines[] = 'Sent from the enquiry form on ' . home_url( '/' ) . '. Reply to this email to answer ' . $data['name'] . ' directly.';
	$lines[] = 'All enquiries: ' . admin_url( 'edit.php?post_type=aaykay_enquiry' );
	// Commas and quotes would break the Reply-To header (wp_mail splits addresses on commas).
	$headers = array( 'Reply-To: ' . str_replace( array( "\r", "\n", '"', ',', '<', '>' ), '', $data['name'] ) . ' <' . $data['email'] . '>' );
	$sent    = wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
	update_post_meta( $post_id, '_aaykay_mailed', $sent ? '1' : '0' );

	aaykay_enquiry_respond( $wants_json, true );
}

/** Reply with JSON (site.js) or a redirect back to the contact section (no JavaScript). */
function aaykay_enquiry_respond( $json, $ok, $errors = array(), $message = '' ) {
	if ( '' === $message ) {
		$message = $ok
			? 'Thank you. Your enquiry has been sent and we will reply by email.'
			: 'Some required details are missing. They are marked above.';
	}
	if ( $json ) {
		wp_send_json(
			array(
				'ok'      => $ok,
				'message' => $message,
				'errors'  => (object) $errors,
			),
			$ok ? 200 : 422
		);
	}
	$state = $ok ? 'sent' : ( $errors ? 'invalid' : 'failed' );
	wp_safe_redirect( add_query_arg( 'enquiry', $state, home_url( '/' ) ) . '#contact', 303 );
	exit;
}

/** Message for the no-JavaScript round trip (?enquiry=sent etc.), or ''. */
function aaykay_enquiry_status_text() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display of a fixed message.
	$state    = isset( $_GET['enquiry'] ) ? sanitize_key( wp_unslash( $_GET['enquiry'] ) ) : '';
	$contact  = aaykay_contact();
	$messages = array(
		'sent'    => 'Thank you. Your enquiry has been sent and we will reply by email.',
		'invalid' => 'Some required details were missing, so the enquiry was not sent. Please fill in every field marked * and send it again.',
		'failed'  => 'Sorry, your enquiry could not be sent. Please email ' . $contact['email'] . ' or call ' . $contact['phone'] . '.',
	);
	return isset( $messages[ $state ] ) ? $messages[ $state ] : '';
}

/**
 * Optional SMTP sending. PHP's built-in mail is often filtered as spam, so on the live site
 * enquiries should go out through a real mailbox. Add these lines to wp-config.php (above
 * "That's all, stop editing!"), with the mailbox created in Hostinger's Email section:
 *
 *   define( 'AAYKAY_SMTP_HOST', 'smtp.hostinger.com' );
 *   define( 'AAYKAY_SMTP_PORT', 465 );
 *   define( 'AAYKAY_SMTP_USER', 'website@akepl.in' );
 *   define( 'AAYKAY_SMTP_PASS', 'the mailbox password' );
 *
 * The password stays in wp-config.php (not in the database or in Git). Without these lines
 * WordPress sends mail the usual way.
 */
add_action(
	'phpmailer_init',
	function ( $mailer ) {
		if ( ! defined( 'AAYKAY_SMTP_HOST' ) || ! defined( 'AAYKAY_SMTP_USER' ) || ! defined( 'AAYKAY_SMTP_PASS' ) ) {
			return;
		}
		$port               = defined( 'AAYKAY_SMTP_PORT' ) ? (int) AAYKAY_SMTP_PORT : 465;
		$mailer->isSMTP();
		$mailer->Host       = AAYKAY_SMTP_HOST; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- PHPMailer property.
		$mailer->Port       = $port; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- PHPMailer property.
		$mailer->SMTPAuth   = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- PHPMailer property.
		$mailer->SMTPSecure = 465 === $port ? 'ssl' : 'tls'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- PHPMailer property.
		$mailer->Username   = AAYKAY_SMTP_USER; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- PHPMailer property.
		$mailer->Password   = AAYKAY_SMTP_PASS; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- PHPMailer property.
	}
);

// With SMTP set up, mail must come from that mailbox or it is rejected.
add_filter(
	'wp_mail_from',
	function ( $from ) {
		return defined( 'AAYKAY_SMTP_USER' ) && is_email( AAYKAY_SMTP_USER ) ? AAYKAY_SMTP_USER : $from;
	}
);
add_filter(
	'wp_mail_from_name',
	function ( $name ) {
		return defined( 'AAYKAY_SMTP_USER' ) ? 'AAYKAY website' : $name;
	}
);
