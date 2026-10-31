<?php
// Router for PHP's built-in web server (CI): serve existing files, send everything else
// to WordPress, as the .htaccess rules do on Apache/LiteSpeed.
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = $_SERVER['DOCUMENT_ROOT'] . $path;
if ( '/' !== $path && is_file( $file ) ) {
	if ( preg_match( '/\.(md|json|ya?ml|sh)$/', $path ) && false !== strpos( $path, '/themes/aaykay/' ) ) {
		http_response_code( 403 ); // Mirrors theme/.htaccess.
		exit;
	}
	return false;
}
if ( is_dir( $file ) && is_file( rtrim( $file, '/' ) . '/index.php' ) ) {
	$_SERVER['SCRIPT_NAME'] = rtrim( $path, '/' ) . '/index.php';
	require rtrim( $file, '/' ) . '/index.php';
	return;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
