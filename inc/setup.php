<?php
/**
 * Theme supports, scripts and styles, a lean <head>, and small hardening steps.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	}
);

/**
 * Save resized copies of uploaded JPEG and PNG photos as WebP when the server can, so
 * the photos AAYKAY uploads are light without anyone converting them first. The original
 * upload is kept as it is.
 */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$formats['image/jpeg'] = 'image/webp';
			$formats['image/png']  = 'image/webp';
		}
		return $formats;
	}
);

/* ------------------------------------------------------------------ scripts and styles */

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'aaykay', aaykay_asset( 'assets/css/site.css' ), array(), aaykay_asset_version( 'assets/css/site.css' ) );
		wp_enqueue_script(
			'aaykay',
			aaykay_asset( 'assets/js/site.js' ),
			array(),
			aaykay_asset_version( 'assets/js/site.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => false,
			)
		);

		// The page uses no blocks, so the block editor's front-end styles are dead weight.
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports' ) as $handle ) {
			wp_dequeue_style( $handle );
		}

		// Logged in: keep the fixed header below the WordPress toolbar.
		if ( is_admin_bar_showing() ) {
			wp_add_inline_style( 'aaykay', '.admin-bar .site-header{top:var(--wp-admin--admin-bar--height,32px)}@media screen and (max-width:600px){.admin-bar .site-header{top:0}}' );
		}
	},
	100
);

/**
 * Early <head> output: theme colour, favicon, and preloads for what the first screen needs
 * (two fonts and the hero photo), before the stylesheet.
 */
add_action(
	'wp_head',
	function () {
		echo '<meta name="theme-color" content="#070B15">' . "\n";
		// Until a Site Icon is set (Appearance > Customize > Site Identity), the theme's own
		// set: SVG for modern browsers, ICO for old ones, PNG for phones' home screens.
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( aaykay_asset( 'assets/favicon.ico' ) ) . '" sizes="32x32">' . "\n";
			echo '<link rel="icon" href="' . esc_url( aaykay_asset( 'assets/favicon.svg' ) ) . '" type="image/svg+xml">' . "\n";
			echo '<link rel="apple-touch-icon" href="' . esc_url( aaykay_asset( 'assets/icon-180.png' ) ) . '">' . "\n";
		}
		if ( is_front_page() ) {
			foreach ( array( 'big-shoulders-display-latin-800-normal', 'ibm-plex-sans-latin-400-normal' ) as $font ) {
				echo '<link rel="preload" href="' . esc_url( aaykay_asset( "assets/fonts/{$font}.woff2" ) ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
			}
			// The hero photo (Homepage content > Opening screen), so the browser fetches it first.
			$hero = aaykay_image_data( aaykay_c( 'hero', 'image' ) );
			if ( $hero && $hero['url'] ) {
				printf(
					'<link rel="preload" as="image" href="%s"%s imagesizes="100vw" fetchpriority="high">' . "\n",
					esc_url( $hero['url'] ),
					'' !== $hero['srcset'] ? ' imagesrcset="' . esc_attr( $hero['srcset'] ) . '"' : ''
				);
			}
		}
	},
	1
);

/**
 * Right after the stylesheet (wp_print_styles runs at priority 8): swap the no-js class and
 * stop the browser restoring scroll position on its own (site.js decides: top for a fresh
 * visit, the same place after a reload, the saved place for Back/Forward).
 *
 * Its position matters for speed: an inline script after a stylesheet makes the browser
 * finish the stylesheet before it reads on, so site.js and the icon sprite don't compete
 * with the stylesheet for bandwidth on slow connections. Measured: about 0.6 s earlier
 * first paint on a throttled mobile connection.
 */
add_action(
	'wp_head',
	function () {
		echo "<script>\ndocument.documentElement.className = \"js\";\nhistory.scrollRestoration = \"manual\";\n</script>\n";
	},
	8
);

/* ------------------------------------------------------------------ a lean <head> */

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
add_filter( 'emoji_svg_url', '__return_false' );

/* ------------------------------------------------------------------ comments: not used */

add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_action(
	'init',
	function () {
		remove_post_type_support( 'post', 'comments' );
		remove_post_type_support( 'page', 'comments' );
		remove_post_type_support( 'post', 'trackbacks' );
	},
	100
);
add_action(
	'wp_before_admin_bar_render',
	function () {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'comments' );
	}
);

/* ------------------------------------------------------------------ hardening */

// XML-RPC is an old remote-publishing API and a common attack target. Nothing here uses it.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Don't list user accounts (login names) to visitors through the REST API.
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
);

/**
 * The site is one page. Author pages (which reveal login names), attachment pages, blog
 * archives and search results have nothing to show, so they go to the home page or 404.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_author() || is_attachment() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
		if ( ( is_home() && ! is_front_page() ) || is_archive() || is_search() || is_feed() ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}
);

/* ------------------------------------------------------------------ page cache */

/**
 * Hostinger serves the page from LiteSpeed Cache. The cache plugin clears itself when a
 * public post changes, but not when Site settings, a sector, or one of this site's own
 * (non-public) content types change, so ask it to clear everything then. Does nothing if
 * LiteSpeed Cache is not installed. Enquiries are excluded: they don't change the page.
 */
function aaykay_purge_page_cache() {
	do_action( 'litespeed_purge_all' );
}
add_action( 'update_option_' . AAYKAY_SETTINGS_OPTION, 'aaykay_purge_page_cache' );
add_action( 'add_option_' . AAYKAY_SETTINGS_OPTION, 'aaykay_purge_page_cache' );
foreach ( array( 'created_aaykay_sector', 'edited_aaykay_sector', 'delete_aaykay_sector' ) as $aaykay_hook ) {
	add_action( $aaykay_hook, 'aaykay_purge_page_cache' );
}
add_action(
	'transition_post_status',
	function ( $new_status, $old_status, $post ) {
		if ( in_array( $post->post_type, array( 'aaykay_project', 'aaykay_client', 'aaykay_firm' ), true ) && ( 'publish' === $new_status || 'publish' === $old_status ) ) {
			aaykay_purge_page_cache();
		}
	},
	10,
	3
);
add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( in_array( $post->post_type, array( 'aaykay_project', 'aaykay_client', 'aaykay_firm' ), true ) && 'publish' === $post->post_status && ! wp_is_post_revision( $post_id ) ) {
			aaykay_purge_page_cache();
		}
	},
	20,
	2
);

/* ------------------------------------------------------------------ security headers */

/**
 * Basic browser protections on every page WordPress serves (audit dossier §18):
 * no MIME sniffing, no framing by other sites, a referrer that doesn't leak full URLs,
 * and no access to camera, microphone or location. HSTS (force HTTPS) is left to the
 * host, to be switched on only once HTTPS works on both akepl.in and www.akepl.in.
 */
add_action(
	'send_headers',
	function () {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()' );
	}
);

/**
 * Nobody edits theme or plugin code from the dashboard (Appearance > Theme File Editor):
 * a typo there takes the site down, and the next deploy would overwrite the change anyway.
 * Code changes go through Git. (Equivalent to DISALLOW_FILE_EDIT in wp-config.php.)
 */
add_filter(
	'map_meta_cap',
	function ( $caps, $cap ) {
		return in_array( $cap, array( 'edit_themes', 'edit_plugins', 'edit_files' ), true ) ? array( 'do_not_allow' ) : $caps;
	},
	10,
	2
);
