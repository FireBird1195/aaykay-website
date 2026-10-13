<?php
/**
 * The website's pages (client brief, 13 Oct 2026): Home (with About Us), Our Capabilities,
 * Projects, Quality & Safety, Careers and Contact Us.
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
 * description, menu dropdown ). Order = menu order. Sections are template-parts/home/{name}.php,
 * shown in this order. The description becomes the page's Excerpt, editable on the page (the
 * home page's is in Site settings). The menu dropdown lists sections of the page as
 * section => anchor id; their names in the menu are edited in Homepage content > Menu
 * dropdowns, and a section that is switched off or empty is left out.
 *
 * Client brief (13 Oct 2026): the home page is Home and About Us in one: the slideshow,
 * then About Us (the company, its timeline, its team, its project methodology). The menu
 * reads Home, About Us (links to those sections), Our Capabilities, Projects (Sectors,
 * Project list, Clients, Architects/Consultants/PMCs, Appreciation certificates), Quality &
 * Safety, Careers, Contact Us. Every page except Contact Us ends with the enquiry form.
 */
function aaykay_page_registry() {
	return array(
		'home'         => array(
			'Home',
			'home',
			'',
			array( 'hero', 'company', 'timeline', 'team', 'deliver' ),
			'',
			array(
				'company'  => 'about',
				'timeline' => 'timeline',
				'team'     => 'team',
				'deliver'  => 'deliver',
			),
		),
		'capabilities' => array(
			'Our Capabilities',
			'capabilities',
			'',
			array( 'services', 'photos' ),
			'What AAYKAY Electricals installs: HT/LT panels and distribution, containment, wiring and cabling, busducts, lighting, critical power, earthing, testing and fire safety.',
			array(),
		),
		'projects'     => array(
			'Projects',
			'projects',
			'',
			array( 'sectors', 'record', 'clients', 'firms', 'appreciations', 'letter' ),
			'AAYKAY Electricals projects: sectors served, the project list with location, built-up area and status, clients, architects and consultants, and appreciation certificates.',
			array(
				'sectors'       => 'sectors',
				'record'        => 'record',
				'clients'       => 'clients',
				'firms'         => 'firms',
				'appreciations' => 'appreciations',
			),
		),
		'quality'      => array( 'Quality & Safety', 'quality-safety', '', array( 'quality', 'safety-photos', 'prequal' ), 'AAYKAY Electricals is certified to ISO 9001:2015 and ISO 45001:2018: quality and safety practice, site safety procedures, testing equipment, compliance and pre-qualification.', array() ),
		'careers'      => array( 'Careers', 'careers', '', array( 'careers' ), 'Careers at AAYKAY Electricals: open positions in project management, site engineering, supervision and safety, and how to apply.', array() ),
		'contact'      => array( 'Contact Us', 'contact-us', '', array( 'contact' ), 'Contact AAYKAY Electricals, Hyderabad: phone numbers, email, branches and the enquiry form.', array() ),
	);
}

/**
 * The menu entry "About Us" is not a page of its own: it links to the About Us sections of
 * the home page, and its dropdown to each of them (the home page's dropdown in the registry).
 */
function aaykay_about_label() {
	$v = trim( (string) aaykay_t( 'menu', 'about' ) );
	return '' !== $v ? $v : 'About Us';
}

/**
 * Pages of earlier versions whose content now lives elsewhere: page key => array( page key,
 * anchor ). Their addresses redirect there (301), so old links and bookmarks keep working.
 * AAYKAY may delete these pages in Dashboard > Pages.
 */
function aaykay_legacy_pages() {
	return array(
		'about'         => array( 'home', 'about' ),
		'clients'       => array( 'projects', 'clients' ),
		'consultants'   => array( 'projects', 'firms' ),
		'appreciations' => array( 'projects', 'appreciations' ),
	);
}

add_action(
	'template_redirect',
	function () {
		if ( ! is_page() || is_front_page() ) {
			return;
		}
		$key    = (string) get_post_meta( get_queried_object_id(), '_aaykay_page', true );
		$legacy = aaykay_legacy_pages();
		if ( isset( $legacy[ $key ] ) ) {
			wp_safe_redirect( aaykay_page_url( $legacy[ $key ][0] ) . '#' . $legacy[ $key ][1], 301 );
			exit;
		}
	}
);

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
		if ( in_array( $section, $p[3], true ) || in_array( $section, $p[5], true ) ) {
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

/**
 * The Homepage content section whose "Show this section" switch governs a template part.
 * The timeline and the team (which opens with the directors) follow the company section;
 * the team cards themselves have their own switch (team.php).
 */
function aaykay_part_schema( $part ) {
	$map = array(
		'timeline' => 'company',
		'team'     => 'company',
	);
	return isset( $map[ $part ] ) ? $map[ $part ] : str_replace( '-', '_', $part );
}

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
				return aaykay_section_on( aaykay_part_schema( $part ) );
			}
		)
	);
}

/**
 * The end of every page except Contact Us: the contact details and the enquiry form
 * (client brief, 13 Oct 2026: "the footer has the contact information and the form").
 */
function aaykay_page_end() {
	if ( 'contact' !== aaykay_current_page_key() ) {
		get_template_part( 'template-parts/home/contact', null, array( 'compact' => true ) );
		echo "\n";
	}
}

/* ------------------------------------------------------------------ menu */

/**
 * Whether a section has something to show (a section that is switched off, or a list with
 * nothing in it, is left out of the menu dropdowns).
 */
function aaykay_section_has_content( $part ) {
	if ( ! aaykay_section_on( aaykay_part_schema( $part ) ) ) {
		return false;
	}
	switch ( $part ) {
		case 'firms':
			return '' !== aaykay_render_firms( '' );
		case 'clients':
			return '' !== aaykay_render_clients( '' );
		case 'work':
			return (bool) aaykay_featured_projects();
		case 'photos':
			return (bool) aaykay_rows( 'photos', 'figures' );
	}
	return true;
}

/**
 * Menu items: array( key, label, url, children ). Labels are the page titles (Dashboard >
 * Pages). Children are the page's sections listed in its dropdown: array( part, label, url ).
 * The header and phone menu show every page in the order of aaykay_page_registry(); Contact
 * Us is the button at the end of the header and the last item of the phone menu.
 */
function aaykay_menu() {
	$items = array();
	foreach ( aaykay_page_registry() as $key => $p ) {
		if ( 'home' !== $key && ! aaykay_page_id( $key ) ) {
			continue; // A page AAYKAY has unpublished stays out of the menu.
		}
		$children = array();
		foreach ( $p[5] as $part => $anchor ) {
			$label = trim( (string) aaykay_t( 'menu', str_replace( '-', '_', $part ) ) );
			if ( '' === $label || ! aaykay_section_has_content( $part ) ) {
				continue;
			}
			$children[] = array( $part, $label, aaykay_page_url( $key ) . '#' . $anchor );
		}
		if ( 'home' === $key ) {
			// Home has no dropdown; its About Us sections are the next item's.
			$items[] = array( 'home', aaykay_page_title( 'home' ), aaykay_page_url( 'home' ), array() );
			$items[] = array( 'about', aaykay_about_label(), aaykay_page_url( 'home' ) . '#about', $children );
			continue;
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
		$key = (string) get_post_meta( absint( $_GET['post'] ), '_aaykay_page', true ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
		if ( '' === $key ) {
			return;
		}
		$legacy = aaykay_legacy_pages();
		if ( isset( $legacy[ $key ] ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html( 'This page is no longer used: its content is now part of the ' . ( 'home' === $legacy[ $key ][0] ? 'home page' : 'Projects page' ) . ', and visitors who open this page’s address are taken there. You can move this page to the Bin.' ) . '</p></div>';
			return;
		}
		echo '<div class="notice notice-info"><p>' . wp_kses( 'This is one of the website’s fixed pages. Here you can change its <strong>title</strong> (also its name in the menu), its <strong>address</strong> and its <strong>Excerpt</strong> (its description in search results). Its words and photos are edited in <a href="' . esc_url( admin_url( 'admin.php?page=aaykay-content' ) ) . '">Homepage content</a>; anything typed in the box below is not shown.', array( 'strong' => array(), 'a' => array( 'href' => array() ) ) ) . '</p></div>';
	}
);
