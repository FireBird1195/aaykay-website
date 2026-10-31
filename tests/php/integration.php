<?php
/**
 * Integration tests for the AAYKAY theme, run inside a real WordPress with the theme active:
 *
 *   wp eval-file tests/php/integration.php            (from the WordPress folder: --path=…)
 *
 * They use the real database, so run them only on a local or CI copy of the site, never on
 * the live one: they create and delete enquiries and change settings (restored at the end).
 * Exit code 0 = all passed. No Composer or PHPUnit needed (packagist is not always
 * reachable, and WordPress itself is the thing under test).
 *
 * @package aaykay
 */

// phpcs:disable WordPress.PHP.DevelopmentFunctions, WordPress.Security.EscapeOutput

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run with: wp eval-file tests/php/integration.php\n" );
	exit( 2 );
}
if ( 'production' === wp_get_environment_type() ) {
	fwrite( STDERR, "Refusing to run on a production site (WP_ENVIRONMENT_TYPE=production).\n" );
	exit( 2 );
}

$GLOBALS['aaykay_t'] = array(
	'pass' => 0,
	'fail' => array(),
);

function t_ok( $cond, $name ) {
	if ( $cond ) {
		++$GLOBALS['aaykay_t']['pass'];
		echo "  ok   $name\n";
	} else {
		$GLOBALS['aaykay_t']['fail'][] = $name;
		echo "  FAIL $name\n";
	}
}
function t_eq( $expected, $actual, $name ) {
	t_ok( $expected === $actual, $name . ( $expected === $actual ? '' : ' (expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . ')' ) );
}
function t_section( $title ) {
	echo "\n$title\n";
}

$saved_settings = get_option( AAYKAY_SETTINGS_OPTION );
$saved_content  = get_option( AAYKAY_CONTENT_OPTION );
$created        = array();

/* ------------------------------------------------------------------ theme is set up */
t_section( 'Theme setup' );
t_eq( 'aaykay', get_stylesheet(), 'the AAYKAY theme is active' );
t_ok( (bool) get_option( 'aaykay_starter_imported' ), 'starter content was imported' );
t_eq( AAYKAY_DATA_VERSION, (int) get_option( 'aaykay_data_version' ), 'data version is current' );
foreach ( array_keys( aaykay_page_registry() ) as $key ) {
	t_ok( aaykay_page_id( $key ) > 0, "page '$key' exists and is published" );
}
t_eq( 'page', get_option( 'show_on_front' ), 'a static front page is set' );
t_eq( aaykay_page_id( 'home' ), (int) get_option( 'page_on_front' ), 'Home is the front page' );
$seed = aaykay_seed_data();
t_eq( count( $seed['projects'] ), (int) wp_count_posts( 'aaykay_project' )->publish, 'every starter project is published' );
t_ok( post_type_exists( 'aaykay_enquiry' ) && ! get_post_type_object( 'aaykay_enquiry' )->public, 'enquiries are a private post type' );
t_ok( ! get_post_type_object( 'aaykay_enquiry' )->show_in_rest, 'enquiries are not in the REST API' );

/* ------------------------------------------------------------------ enquiry validation */
t_section( 'Enquiry format rules (server side)' );
$good = array(
	'name'    => 'Asha Rao',
	'company' => 'Test Infra Pvt. Ltd.',
	'email'   => 'asha@example.com',
	'phone'   => '+91 98480 12345',
	'type'    => '',
	'city'    => 'Hyderabad',
	'message' => 'New hospital block, LT panels, start Q1 2027.',
);
t_eq( array(), aaykay_enquiry_check_formats( $good ), 'a normal enquiry passes' );
t_eq( array(), aaykay_enquiry_check_formats( array_merge( $good, array( 'name' => 'Zoë D’Souza-Nair' ) ) ), 'accents, apostrophes and hyphens are fine in names' );
t_eq( array(), aaykay_enquiry_check_formats( array_merge( $good, array( 'name' => 'राजेश कुमार' ) ) ), 'Devanagari names are accepted' );
$cases = array(
	'name'    => array( 'R2D2', 'A', '<script>' ),
	'company' => array( '!!!', '1' ),
	'email'   => array( 'not-an-email', 'a@b', 'a@b.c' ),
	'phone'   => array( '12345', 'call me', '+91 98480 12345 12345 123' ),
	'city'    => array( 'Hyd3rabad' ),
	'message' => array( 'hi', '1234567890123' ),
);
foreach ( $cases as $field => $values ) {
	foreach ( $values as $v ) {
		$e = aaykay_enquiry_check_formats( array_merge( $good, array( $field => $v ) ) );
		t_ok( isset( $e[ $field ] ), "$field '$v' is rejected" );
	}
}
t_ok( in_array( 'Other', aaykay_project_types(), true ), 'project types always include Other' );
t_ok( count( aaykay_project_types() ) <= 21, 'project types are capped' );

/* ------------------------------------------------------------------ privacy tools */
t_section( 'Privacy: export, erase, retention' );
$mk = function ( $email, $date_gmt = null ) use ( &$created ) {
	$args = array(
		'post_type'   => 'aaykay_enquiry',
		'post_status' => 'private',
		'post_title'  => 'Test — ' . $email,
	);
	if ( $date_gmt ) {
		$args['post_date_gmt'] = $date_gmt;
		$args['post_date']     = get_date_from_gmt( $date_gmt );
	}
	$id = wp_insert_post( $args );
	update_post_meta( $id, '_aaykay_email', $email );
	update_post_meta( $id, '_aaykay_name', 'Privacy Test' );
	update_post_meta( $id, '_aaykay_message', 'Message for the privacy test.' );
	$created[] = $id;
	return $id;
};
$a1 = $mk( 'privacy-a@example.test' );
$a2 = $mk( 'privacy-a@example.test' );
$b1 = $mk( 'privacy-b@example.test' );
$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );
t_ok( isset( $exporters['aaykay-enquiries'] ), 'an exporter is registered' );
$export = call_user_func( $exporters['aaykay-enquiries']['callback'], 'privacy-a@example.test', 1 );
t_eq( 2, count( $export['data'] ), 'export finds both enquiries from that address, not others' );
t_ok( $export['done'], 'export reports done' );
$names = wp_list_pluck( $export['data'][0]['data'], 'name' );
t_ok( in_array( 'Work email', $names, true ) && in_array( 'Scope and timeline', $names, true ), 'export includes the email and the message' );
$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
$erase   = call_user_func( $erasers['aaykay-enquiries']['callback'], 'privacy-a@example.test', 1 );
t_eq( 2, $erase['items_removed'], 'erase removes both enquiries' );
t_ok( null === get_post( $a1 ) && null === get_post( $a2 ), 'erased enquiries are gone for good (not in the bin)' );
t_ok( null !== get_post( $b1 ), 'another person’s enquiry is untouched' );

$old = $mk( 'privacy-old@example.test', gmdate( 'Y-m-d H:i:s', strtotime( '-30 months' ) ) );
$new = $mk( 'privacy-new@example.test', gmdate( 'Y-m-d H:i:s', strtotime( '-2 months' ) ) );
$s   = get_option( AAYKAY_SETTINGS_OPTION, array() );
$s['enquiry_retention_months'] = '';
update_option( AAYKAY_SETTINGS_OPTION, $s );
t_eq( 0, aaykay_apply_retention(), 'retention does nothing while the setting is empty' );
$s['enquiry_retention_months'] = '24';
update_option( AAYKAY_SETTINGS_OPTION, $s );
aaykay_apply_retention();
t_ok( null === get_post( $old ), 'retention deletes an enquiry older than 24 months' );
t_ok( null !== get_post( $new ), 'retention keeps a recent enquiry' );
t_ok( (bool) wp_next_scheduled( AAYKAY_RETENTION_HOOK ), 'the daily retention job is scheduled' );

/* ------------------------------------------------------------------ CSV export */
t_section( 'CSV download' );
$csv_id = $mk( 'csv@example.test' );
update_post_meta( $csv_id, '_aaykay_company', '=HYPERLINK("http://evil.example","x")' );
update_post_meta( $csv_id, '_aaykay_name', '+cmd|calc' );
update_post_meta( $csv_id, '_aaykay_mailed', '1' );
t_eq( "'=HYPERLINK(\"http://evil.example\",\"x\")", aaykay_csv_cell( get_post_meta( $csv_id, '_aaykay_company', true ) ), 'a cell starting with = is neutralised' );
t_eq( "'+cmd|calc", aaykay_csv_cell( '+cmd|calc' ), 'a cell starting with + is neutralised' );
t_eq( "'-2+3", aaykay_csv_cell( '-2+3' ), 'a cell starting with - is neutralised' );
t_eq( "'@SUM(A1)", aaykay_csv_cell( '@SUM(A1)' ), 'a cell starting with @ is neutralised' );
t_eq( 'Asha Rao', aaykay_csv_cell( 'Asha Rao' ), 'ordinary text is unchanged' );

/* ------------------------------------------------------------------ Google Sheets */
t_section( 'Google Sheets connection (HTTP mocked)' );
$s               = get_option( AAYKAY_SETTINGS_OPTION, array() );
$s['sheets_url'] = '';
update_option( AAYKAY_SETTINGS_OPTION, $s );
t_eq( null, aaykay_send_to_sheet( $good, '2026-10-31 10:00:00' ), 'no sheet connected: nothing is sent' );
$s['sheets_url'] = 'https://script.google.com/macros/s/TEST/exec';
update_option( AAYKAY_SETTINGS_OPTION, $s );
$sent_body = null;
$mock      = function ( $pre, $args, $url ) use ( &$sent_body ) {
	$sent_body = json_decode( $args['body'], true );
	return array(
		'headers'  => array(),
		'body'     => '{"ok":true}',
		'response' => array( 'code' => 200, 'message' => 'OK' ),
		'cookies'  => array(),
		'filename' => null,
	);
};
add_filter( 'pre_http_request', $mock, 10, 3 );
t_eq( true, aaykay_send_to_sheet( $good, '2026-10-31 10:00:00' ), 'a confirmed row returns true' );
t_ok( is_array( $sent_body ) && strlen( $sent_body['secret'] ) >= 12 && 'asha@example.com' === $sent_body['email'], 'the row carries the secret and the enquiry' );
remove_filter( 'pre_http_request', $mock, 10 );
add_filter( 'pre_http_request', function () { return new WP_Error( 'http_request_failed', 'timeout' ); } );
t_eq( false, aaykay_send_to_sheet( $good, '2026-10-31 10:00:00' ), 'a network failure returns false (the enquiry is still saved)' );
remove_all_filters( 'pre_http_request' );
$clean = aaykay_sanitize_settings( array_merge( (array) $s, array( 'sheets_url' => 'https://evil.example/collect' ) ) );
t_eq( '', $clean['sheets_url'], 'only script.google.com addresses are accepted for the sheet (no SSRF)' );

/* ------------------------------------------------------------------ settings sanitising */
t_section( 'Site settings sanitising' );
$clean = aaykay_sanitize_settings(
	array(
		'phone'          => '<b>+91 98667 00400</b>',
		'email'          => 'bad email',
		'enquiry_to'     => 'a@example.com, nonsense, b@example.com',
		'brochure_url'   => 'javascript:alert(1)',
		'res_pm'         => '12 people',
		'seo_title'      => '<script>x</script>Title',
		'show_pending'   => '1',
		'cf_analytics'   => 'abc',
	)
);
t_eq( '+91 98667 00400', $clean['phone'], 'tags are stripped from text settings' );
t_ok( is_email( $clean['email'] ), 'an invalid email keeps the previous one' );
t_eq( 'a@example.com, b@example.com', $clean['enquiry_to'], 'invalid recipients are dropped' );
t_eq( '', $clean['brochure_url'], 'a javascript: brochure link is refused' );
t_eq( '12', $clean['res_pm'], 'counts keep digits only' );
t_ok( false === strpos( $clean['seo_title'], '<' ), 'no markup in the search title' );
t_eq( '1', $clean['show_pending'], 'checkbox saved as 1' );
t_eq( '0', aaykay_sanitize_settings( array() )['show_pending'], 'unticked checkbox saved as 0' );

/* ------------------------------------------------------------------ content sanitising */
t_section( 'Homepage content sanitising' );
$img = aaykay_sanitize_field( array( 'image', 'Photo' ), array( 'default' => wp_json_encode( array( 'src' => 'assets/img/../../../../wp-config.php' ) ) ) );
t_ok( empty( $img['src'] ), 'a launch photo path with .. is refused' );
$img = aaykay_sanitize_field( array( 'image', 'Photo' ), array( 'default' => wp_json_encode( array( 'src' => '/etc/passwd' ) ) ) );
t_ok( empty( $img['src'] ), 'a launch photo outside assets/img is refused' );
$img = aaykay_sanitize_field( array( 'image', 'Photo' ), array( 'default' => wp_json_encode( array( 'src' => 'assets/img/hero-1280.webp', 'w' => 1280, 'h' => 853 ) ), 'alt' => '<b>Alt</b>' ) );
t_eq( 'assets/img/hero-1280.webp', $img['src'], 'a launch photo inside assets/img is kept' );
t_eq( 'Alt', $img['alt'], 'alt text is plain text' );
t_eq( 'gauge', aaykay_sanitize_field( array( 'icon', 'Icon' ), 'not-an-icon"><script>' ), 'unknown icons fall back to a known one' );
t_eq( '', aaykay_link_url( 'javascript:alert(1)' ), 'javascript: links are dropped' );
t_ok( 0 === strpos( aaykay_link_url( 'https://example.com/a' ), 'https://example.com/a' ), 'https links are kept' );

/* ------------------------------------------------------------------ output escaping */
t_section( 'Output escaping' );
$s                 = get_option( AAYKAY_SETTINGS_OPTION, array() );
$s['phone']        = '+91 98667 00400';
$s['clients_served'] = '100+';
update_option( AAYKAY_SETTINGS_OPTION, $s );
t_eq( 'more than 100', aaykay_clients_phrase(), '"100+" reads "more than 100" in running text' );
t_eq( '+919866700400', aaykay_tel( '+91 98667 00400' ), 'tel: links keep digits and +' );
t_ok( false === strpos( aaykay_dot_line( array( '<i>x</i>', 'ISO 9001:2015' ) ), '<i>' ), 'dot lines escape their parts' );

/* ------------------------------------------------------------------ upgrade lock */
t_section( 'Upgrade routine' );
t_ok( false === get_option( 'aaykay_upgrade_lock' ), 'no upgrade lock is left behind' );

/* ------------------------------------------------------------------ health */
t_section( 'Health endpoint' );
$res = rest_do_request( new WP_REST_Request( 'GET', '/aaykay/v1/health' ) );
t_eq( 200, $res->get_status(), 'health answers 200' );
t_eq( array( 'status' => 'ok' ), $res->get_data(), 'health says only "ok"' );
$tests = apply_filters( 'site_status_tests', array( 'direct' => array(), 'async' => array() ) );
foreach ( array( 'aaykay_email', 'aaykay_launch', 'aaykay_privacy' ) as $t ) {
	$r = call_user_func( $tests['direct'][ $t ]['test'] );
	t_ok( isset( $r['status'] ) && in_array( $r['status'], array( 'good', 'recommended', 'critical' ), true ), "Site Health check $t runs" );
}

/* ------------------------------------------------------------------ clean up */
foreach ( $created as $id ) {
	if ( get_post( $id ) ) {
		wp_delete_post( $id, true );
	}
}
update_option( AAYKAY_SETTINGS_OPTION, $saved_settings );
if ( false === $saved_content ) {
	delete_option( AAYKAY_CONTENT_OPTION );
} else {
	update_option( AAYKAY_CONTENT_OPTION, $saved_content );
}

$r = $GLOBALS['aaykay_t'];
echo "\n" . $r['pass'] . ' passed, ' . count( $r['fail'] ) . " failed\n";
if ( $r['fail'] ) {
	echo 'Failed: ' . implode( '; ', $r['fail'] ) . "\n";
	exit( 1 );
}
