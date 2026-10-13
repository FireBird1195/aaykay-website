<?php
/**
 * The website's pages (client brief, 10 Oct 2026): Home, About Us, Our Capabilities,
 * Projects (with Clients, Architects, Consultants & PMCs and Appreciations under it),
 * Quality & Safety, Careers and Contact Us.
 *
 * The pages reuse the existing sections (template-parts/home/*.php) unchanged in design:
 * each page is a list of those sections. The one-page site's sections (version 1) are spread
 * across pages.
 *
 *   - Each page is an ordinary WordPress page (Dashboard > Pages), created by the theme and
 *     tagged with the meta field _aaykay_page = its key. The key, not the title or address,
 *     decides which sections are shown, so AAYKAY can rename a page or change its address
 *     in Pages; the menu follows the page titles.
 *   - What the sections say is still edited in Dashboard > Homepage content.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/**
 * Page key => array( default title, default slug, parent key, sections, default search
 * description ). Order = menu order. Sections are template-parts/home/{name}.php. The
 * description becomes the page's Excerpt, editable on the page (the home page's is in
 * Site settings).
 */
function aaykay_page_registry() {
	return array(
		'home'          => array( 'Home', 'home', '', array( 'hero', 'clients' ), '' ),
		'about'         => array( 'About Us', 'about-us', '', array( 'company', 'prequal' ), 'AAYKAY Electricals Private Limited, an ISO 9001:2015 and ISO 45001:2018 certified electrical and MEP contractor in Hyderabad since 2008: company, directors and pre-qualification facts.' ),
		'capabilities'  => array( 'Our Capabilities', 'capabilities', '', array( 'services', 'safety-solutions', 'deliver' ), 'What AAYKAY Electricals installs, the sectors it serves, AAYKAY Safety Solutions, and how projects are delivered from design to handover.' ),
		'projects'      => array( 'Projects', 'projects', '', array( 'work', 'record', 'photos' ), 'The AAYKAY Electricals project record: hospitals, offices, banks, residential towers and infrastructure, with location, built-up area and status.' ),
		'clients'       => array( 'Clients', 'clients', 'projects', array( 'clients' ), 'Clients AAYKAY Electricals has worked for.' ),
		'consultants'   => array( 'Architects, Consultants & PMCs', 'architects-consultants-pmcs', 'projects', array( 'firms' ), 'Architects, consultants and project management consultants AAYKAY Electricals has worked with.' ),
		'appreciations' => array( 'Appreciations', 'appreciations', 'projects', array( 'appreciations', 'letter' ), 'Certificates and letters of appreciation received by AAYKAY Electricals and its site teams.' ),
		'quality'       => array( 'Quality & Safety', 'quality-safety', '', array( 'quality' ), 'AAYKAY Electricals is certified to ISO 9001:2015 and ISO 45001:2018: its quality and safety practice, site training and testing kit.' ),
		'careers'       => array( 'Careers', 'careers', '', array( 'careers' ), 'Careers at AAYKAY Electricals: what we provide our people on site, current openings and how to apply.' ),
		'contact'       => array( 'Contact Us', 'contact-us', '', array( 'contact' ), 'Contact AAYKAY Electricals, Hyderabad: phone, email and the project enquiry form.' ),
	);
}

// A page's Excerpt is its description in search results (inc/seo.php).
add_action(
	'init',
	function () {
		add_post_type_support( 'page', 'excerpt' );
	}
);

/** The published page ID for a key (0 if missing or not published). */
function aaykay_page_id( $key ) {
	static $ids = null;
	if ( null === $ids ) {
		$ids = array();
		foreach ( get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'meta_key'       => '_aaykay_page', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- a handful of pages.
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		) as $p ) {
			$k = (string) get_post_meta( $p->ID, '_aaykay_page', true );
			if ( '' !== $k && ! isset( $ids[ $k ] ) ) {
				$ids[ $k ] = (int) $p->ID;
			}
		}
	}
	return isset( $ids[ $key ] ) ? $ids[ $key ] : 0;
}

/** Address of a page by key (the home page is the site address). */
function aaykay_page_url( $key ) {
	if ( 'home' === $key ) {
		return home_url( '/' );
	}
	$id = aaykay_page_id( $key );
	if ( $id ) {
		return get_permalink( $id );
	}
	$reg = aaykay_page_registry();
	return isset( $reg[ $key ] ) ? home_url( '/' . $reg[ $key ][1] . '/' ) : home_url( '/' );
}

/** Title of a page by key, as AAYKAY named it in Pages. */
function aaykay_page_title( $key ) {
	$id = aaykay_page_id( $key );
	if ( $id && 'home' !== $key ) {
		return get_post_field( 'post_title', $id );
	}
	$reg = aaykay_page_registry();
	return isset( $reg[ $key ] ) ? $reg[ $key ][0] : '';
}

/** The key of the page being shown ('' on any other page). */
function aaykay_current_page_key() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_page() ) {
		return (string) get_post_meta( get_queried_object_id(), '_aaykay_page', true );
	}
	return '';
}

/** Which page a section is on (for links such as #contact or #record from another page). */
function aaykay_section_page( $section ) {
	foreach ( aaykay_page_registry() as $key => $p ) {
		if ( in_array( $section, $p[3], true ) ) {
			return $key;
		}
	}
	return 'home';
}

/** Page ID for a key whatever its status (used while creating pages). */
function aaykay_page_id_any( $key ) {
	$found = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => '_aaykay_page', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off.
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- one-off.
			'fields'         => 'ids',
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * Create any missing page and make Home the front page. Never changes a page that exists
 * (its title, address and status are AAYKAY's). Returns the number of pages created.
 */
function aaykay_ensure_pages() {
	$created = 0;
	$order   = 0;
	foreach ( aaykay_page_registry() as $key => $p ) {
		++$order;
		if ( aaykay_page_id_any( $key ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p[0],
				'post_name'    => $p[1],
				'post_parent'  => $p[2] ? aaykay_page_id_any( $p[2] ) : 0,
				'menu_order'   => $order,
				'post_excerpt' => $p[4],
			),
			true
		);
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_aaykay_page', $key );
			++$created;
		}
	}
	$home = aaykay_page_id_any( 'home' );
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
	}
	return $created;
}

// Every tagged page except Home is drawn by aaykay-page.php (Home: front-page.php).
add_filter(
	'template_include',
	function ( $template ) {
		if ( is_page() && ! is_front_page() && '' !== aaykay_current_page_key() ) {
			return get_theme_file_path( 'aaykay-page.php' );
		}
		return $template;
	}
);

/** The sections of a page that are switched on in Homepage content. */
function aaykay_page_sections( $key ) {
	$reg = aaykay_page_registry();
	if ( ! isset( $reg[ $key ] ) ) {
		return array();
	}
	return array_values(
		array_filter(
			$reg[ $key ][3],
			function ( $part ) {
				return aaykay_section_on( str_replace( '-', '_', $part ) );
			}
		)
	);
}

/* ------------------------------------------------------------------ menu */

/**
 * Menu items: array( key, label, url, children ). Labels are the page titles.
 * Children (the pages under Projects) are listed in the footer and on the Projects pages.
 */
function aaykay_menu() {
	$items = array();
	$reg   = aaykay_page_registry();
	foreach ( $reg as $key => $p ) {
		if ( 'home' === $key || $p[2] || ! aaykay_page_id( $key ) ) {
			continue;
		}
		$children = array();
		foreach ( $reg as $ck => $cp ) {
			if ( $cp[2] === $key && aaykay_page_id( $ck ) ) {
				$children[] = array( $ck, aaykay_page_title( $ck ), aaykay_page_url( $ck ) );
			}
		}
		$items[] = array( $key, aaykay_page_title( $key ), aaykay_page_url( $key ), $children );
	}
	return $items;
}

/* ------------------------------------------------------------------ dashboard */

// On the edit screen of one of these pages, say where its words are edited.
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'page' !== $screen->id || empty( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
			return;
		}
		if ( '' === (string) get_post_meta( absint( $_GET['post'] ), '_aaykay_page', true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
			return;
		}
		echo '<div class="notice notice-info"><p>' . wp_kses( 'This is one of the website’s fixed pages. Here you can change its <strong>title</strong> (also its name in the menu), its <strong>address</strong> and its <strong>Excerpt</strong> (its description in search results). Its words and photos are edited in <a href="' . esc_url( admin_url( 'admin.php?page=aaykay-content' ) ) . '">Homepage content</a>; anything typed in the box below is not shown.', array( 'strong' => array(), 'a' => array( 'href' => array() ) ) ) . '</p></div>';
	}
);
