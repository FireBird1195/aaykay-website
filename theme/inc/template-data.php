<?php
/**
 * Data for the home page templates, and the markup for the repeated, data-driven parts
 * (navigation, sector rows, filter chips, the project record, logos, featured cards).
 * Each renderer returns HTML with every value escaped.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ navigation */

/**
 * Navigation, in the same order as the sections on the page. 'in' says where a link
 * appears (header, mobile, footer); 'cta' styles it as the call to action.
 */
function aaykay_nav_items() {
	return array(
		array( 'services', 'What we do', array( 'header', 'mobile', 'footer' ), false ),
		array( 'sectors', 'Sectors', array( 'footer' ), false ),
		array( 'work', 'Projects', array( 'header', 'mobile', 'footer' ), false ),
		array( 'record', 'Project record', array( 'footer' ), false ),
		array( 'deliver', 'How we deliver', array( 'header', 'mobile', 'footer' ), false ),
		array( 'quality', 'Quality & safety', array( 'header', 'mobile', 'footer' ), false ),
		array( 'company', 'Company', array( 'header', 'mobile', 'footer' ), false ),
		array( 'prequal', 'Pre-qualification', array( 'header', 'mobile', 'footer' ), false ),
		array( 'contact', 'Start a project', array( 'mobile' ), true ),
		array( 'contact', 'Contact', array( 'footer' ), false ),
	);
}

/** The Homepage content section each navigation target belongs to. */
function aaykay_nav_section( $id ) {
	$map = array(
		'services' => 'services',
		'sectors'  => 'services',
		'work'     => 'work',
		'record'   => 'record',
		'deliver'  => 'deliver',
		'quality'  => 'quality',
		'company'  => 'company',
		'prequal'  => 'prequal',
		'contact'  => 'contact',
	);
	return isset( $map[ $id ] ) ? $map[ $id ] : $id;
}

function aaykay_render_nav( $place, $indent ) {
	$lines = array();
	foreach ( aaykay_nav_items() as $item ) {
		list( $id, $label, $in, $cta ) = $item;
		// Skip places the link doesn't belong, and sections switched off in Homepage content.
		if ( ! in_array( $place, $in, true ) || ! aaykay_section_on( aaykay_nav_section( $id ) ) ) {
			continue;
		}
		if ( 'contact' === $id && $cta ) {
			$label = aaykay_t( 'brand', 'header_cta' );
		} elseif ( '' !== aaykay_t( 'brand', 'menu_' . $id ) ) {
			$label = aaykay_t( 'brand', 'menu_' . $id );
		}
		$link    = '<a href="' . esc_attr( aaykay_section_url( $id ) ) . '"' . ( $cta ? ' class="nav-cta"' : '' ) . '>' . esc_html( $label ) . '</a>';
		$lines[] = 'footer' === $place ? $link : '<li>' . $link . '</li>';
	}
	return implode( "\n" . $indent, $lines );
}

/* ------------------------------------------------------------------ sectors */

/** Sectors in display order, each with its published project count. */
function aaykay_sectors() {
	static $sectors = null;
	if ( null !== $sectors ) {
		return $sectors;
	}
	$sectors = array();
	$terms   = get_terms(
		array(
			'taxonomy'   => 'aaykay_sector',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $sectors;
	}
	$counts = array();
	foreach ( aaykay_projects() as $p ) {
		$counts[ $p['sector'] ] = ( isset( $counts[ $p['sector'] ] ) ? $counts[ $p['sector'] ] : 0 ) + 1;
	}
	foreach ( $terms as $t ) {
		$icon      = aaykay_sector_meta( $t->term_id, 'icon' );
		$short     = aaykay_sector_meta( $t->term_id, 'short_label' );
		$sectors[] = array(
			'key'         => $t->slug,
			'label'       => $t->name,
			'short'       => '' !== $short ? $short : $t->name,
			'description' => aaykay_sector_meta( $t->term_id, 'description' ),
			'clients'     => aaykay_sector_meta( $t->term_id, 'clients' ),
			'icon'        => in_array( $icon, aaykay_icon_ids(), true ) ? $icon : 'building-2',
			'order'       => (int) aaykay_sector_meta( $t->term_id, 'order' ),
			'count'       => isset( $counts[ $t->slug ] ) ? $counts[ $t->slug ] : 0,
		);
	}
	usort(
		$sectors,
		function ( $a, $b ) {
			return array( $a['order'], strtolower( $a['label'] ) ) <=> array( $b['order'], strtolower( $b['label'] ) );
		}
	);
	return $sectors;
}

function aaykay_sector_by_key( $key ) {
	foreach ( aaykay_sectors() as $s ) {
		if ( $s['key'] === $key ) {
			return $s;
		}
	}
	return null;
}

function aaykay_render_sector_rows( $indent ) {
	$out = array();
	foreach ( aaykay_sectors() as $s ) {
		$c = $s['count'];
		if ( ! $c ) {
			continue; // A sector with no published projects yet stays off the page.
		}
		$out[] = sprintf(
			'<li><a class="sector-row" href="?sector=%1$s#record" data-filter="%1$s"><span class="sector-name">%2$s%3$s</span><span class="sector-info"><span class="sector-desc">%4$s</span><span class="sector-clients">%5$s</span></span><span class="sector-count">%6$d project%7$s</span><span class="sector-arrow" aria-hidden="true">%8$s</span></a></li>',
			esc_attr( $s['key'] ),
			aaykay_icon( $s['icon'], 'sector-ic' ),
			esc_html( $s['label'] ),
			esc_html( $s['description'] ),
			esc_html( $s['clients'] ),
			$c,
			1 === $c ? '' : 's',
			aaykay_arrow( 'down' )
		);
	}
	return implode( "\n" . $indent, $out );
}

function aaykay_render_chips( $indent ) {
	$items = array( array( 'all', 'All sectors', count( aaykay_projects() ), '' ) );
	foreach ( aaykay_sectors() as $s ) {
		if ( $s['count'] ) {
			$items[] = array( $s['key'], $s['label'], $s['count'], aaykay_icon( $s['icon'] ) );
		}
	}
	$out = array();
	foreach ( $items as $item ) {
		list( $key, $label, $count, $icon ) = $item;
		$out[] = sprintf(
			'<button class="chip" type="button" data-filter="%s" aria-pressed="%s">%s<span class="chip-label">%s</span> <span class="chip-count">%d</span></button>',
			esc_attr( $key ),
			'all' === $key ? 'true' : 'false',
			$icon,
			esc_html( $label ),
			$count
		);
	}
	return implode( "\n" . $indent, $out );
}

/* ------------------------------------------------------------------ projects */

/** Site settings > "Show order values on the website". */
function aaykay_show_values() {
	return '0' !== (string) aaykay_setting( 'show_order_values' );
}

/**
 * Published projects that have a sector, in record order: rows are grouped by how much
 * is stated (order value first, then area, then name only) and alphabetical within each
 * group. Never sorted by order value, so clients are not ranked by contract size.
 */
function aaykay_projects() {
	static $projects = null;
	if ( null !== $projects ) {
		return $projects;
	}
	$projects = array();
	$posts    = get_posts(
		array(
			'post_type'        => 'aaykay_project',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);
	foreach ( $posts as $post ) {
		$terms = get_the_terms( $post, 'aaykay_sector' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			continue; // A project without a sector is not shown (the edit screen warns about this).
		}
		$p = array(
			'id'     => $post->ID,
			'slug'   => $post->post_name,
			'name'   => $post->post_title, // Raw title, escaped on output (get_the_title() would texturize it).
			'sector' => $terms[0]->slug,
			'menu'   => (int) $post->menu_order,
			'date'   => $post->post_date,
		);
		foreach ( array_keys( aaykay_project_fields() ) as $key ) {
			$p[ $key ] = aaykay_project_meta( $post->ID, $key );
		}
		$p['area_sqft'] = '' !== $p['area_sqft'] ? (int) $p['area_sqft'] : 0;
		$projects[]     = $p;
	}
	usort(
		$projects,
		function ( $a, $b ) {
			$tier = function ( $p ) {
				return ( '' !== $p['order_value'] && aaykay_show_values() ) ? 0 : ( $p['area_sqft'] ? 1 : 2 );
			};
			$lower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
			return array( $tier( $a ), $lower( $a['name'] ), $a['slug'] ) <=> array( $tier( $b ), $lower( $b['name'] ), $b['slug'] );
		}
	);
	return $projects;
}

/** "24" -> "24 floors"; descriptions (G+30, 2 towers) are shown as written. */
function aaykay_floors_text( $floors ) {
	return aaykay_is_digits( $floors ) ? $floors . ' floors' : $floors;
}

function aaykay_render_record_rows( $indent ) {
	$unit = function ( $value, $u = '' ) {
		if ( '' === (string) $value ) {
			return '';
		}
		return esc_html( $value ) . ( $u ? '<span class="u">' . esc_html( $u ) . '</span>' : '' );
	};
	$rows = array();
	foreach ( aaykay_projects() as $p ) {
		$s = aaykay_sector_by_key( $p['sector'] );
		if ( ! $s ) {
			continue;
		}
		// Under the name: the sector (icon + words), then optional markers. Status gets its
		// own marker because it changes what the row means (finished vs in progress).
		$meta = '<span class="p-meta">' . aaykay_icon( $s['icon'] ) . esc_html( $s['label'] ) . '</span>';
		if ( '' !== $p['status'] ) {
			$done  = 0 === strpos( $p['status'], 'Completed' );
			$meta .= '<span class="p-status' . ( $done ? ' p-status--done' : '' ) . '">' . aaykay_icon( $done ? 'circle-check' : 'clock' ) . esc_html( $p['status'] ) . '</span>';
		}
		if ( '' !== $p['note'] ) {
			$meta .= '<span class="p-note">' . esc_html( $p['note'] ) . '</span>';
		}
		$area        = $p['area_sqft'] ? number_format( $p['area_sqft'] ) : '';
		$floors_unit = aaykay_is_digits( $p['floors'] ) ? ' floors' : '';
		// A developer (housing rows) is named with its own "Developer:" prefix; "dev" stops the
		// phone layout adding its "Design team" label in front of it.
		$team_class = 0 === strpos( $p['design_team'], 'Developer:' ) ? 'team dev' : 'team';
		$value_cell = aaykay_show_values() ? '<td class="num">' . $unit( $p['order_value'], ' order' ) . '</td>' : '';
		$rows[]     = sprintf(
			'<tr data-sector="%s"><th scope="row"><span class="p-name">%s</span>%s</th><td>%s</td><td class="num">%s</td><td class="floors">%s</td>%s<td class="%s">%s</td></tr>',
			esc_attr( $p['sector'] ),
			esc_html( $p['name'] ),
			$meta,
			$unit( $p['location'] ),
			$unit( $area, ' sq ft' ),
			$unit( $p['floors'], $floors_unit ),
			$value_cell,
			$team_class,
			$unit( $p['design_team'] )
		);
	}
	return implode( "\n" . $indent, $rows );
}

/** Published projects marked for the "Selected work" cards that have a photo, in card order. */
function aaykay_featured_projects() {
	$cards = array();
	foreach ( aaykay_projects() as $p ) {
		if ( '1' === $p['featured'] && has_post_thumbnail( $p['id'] ) ) {
			$cards[] = $p;
		}
	}
	usort(
		$cards,
		function ( $a, $b ) {
			return array( $a['menu'], $a['date'] ) <=> array( $b['menu'], $b['date'] );
		}
	);
	return $cards;
}

/** Spec line on a card: area · floors · order value (or status when there is no order value). */
function aaykay_card_specs( $p ) {
	$specs = array();
	if ( $p['area_sqft'] ) {
		$specs[] = number_format( $p['area_sqft'] ) . ' sq ft';
	}
	if ( '' !== $p['floors'] ) {
		$specs[] = aaykay_floors_text( $p['floors'] );
	}
	if ( '' !== $p['order_value'] && aaykay_show_values() ) {
		$specs[] = $p['order_value'] . ' order';
	} elseif ( '' !== $p['status'] ) {
		$specs[] = $p['status'];
	}
	return implode(
		'',
		array_map(
			function ( $s ) {
				return '<li>' . esc_html( $s ) . '</li>';
			},
			$specs
		)
	);
}

/** "Scope: Internal electrification" lines -> array of array( label, value ). */
function aaykay_card_details( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$parts = explode( ':', $line, 2 );
		if ( 2 === count( $parts ) && '' !== trim( $parts[0] ) && '' !== trim( $parts[1] ) ) {
			$out[] = array( trim( $parts[0] ), trim( $parts[1] ) );
		}
	}
	return $out;
}

/**
 * One "Selected work" card. The first card is wide, the second medium, the rest regular,
 * matching the grid in site.css. The sizes attribute follows the card width; very wide
 * photos are cropped to fill the frame, so they are asked for at a larger width.
 */
function aaykay_render_card( $p, $position ) {
	$variant = 0 === $position ? ' work-card--wide' : ( 1 === $position ? ' work-card--mid' : '' );
	$thumb   = (int) get_post_thumbnail_id( $p['id'] );
	$full    = wp_get_attachment_image_src( $thumb, 'full' );
	$ratio   = ( $full && $full[2] ) ? $full[1] / $full[2] : 1.5;
	if ( 0 === $position ) {
		$desktop = 56;
		$frame   = 1.75;
	} elseif ( 1 === $position ) {
		$desktop = 40;
		$frame   = 1.25;
	} else {
		$desktop = 32;
		$frame   = 1.25;
	}
	$scale = max( 1, $ratio / $frame );
	$vw    = function ( $n ) use ( $scale ) {
		return min( 200, (int) round( $n * $scale ) ) . 'vw';
	};
	$sizes = 0 === $position || 1 === $position
		? '(min-width: 900px) ' . $vw( $desktop ) . ', ' . $vw( 100 )
		: '(min-width: 900px) ' . $vw( $desktop ) . ', (min-width: 620px) ' . $vw( 50 ) . ', ' . $vw( 100 );

	$s     = aaykay_sector_by_key( $p['sector'] );
	$city  = trim( explode( ',', $p['location'] )[0] );
	$label = '' !== $p['card_label'] ? $p['card_label'] : implode( ' · ', array_filter( array( $s ? $s['short'] : '', $city ), 'strlen' ) );

	$img = wp_get_attachment_image(
		$thumb,
		'large',
		false,
		array(
			'sizes'    => $sizes,
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	$meta = '';
	foreach ( aaykay_card_details( $p['card_details'] ) as $d ) {
		$meta .= '<div><dt>' . esc_html( $d[0] ) . '</dt><dd>' . esc_html( $d[1] ) . '</dd></div>';
	}

	$html  = '<article class="work-card' . $variant . '" data-project="' . esc_attr( $p['slug'] ) . '">' . "\n";
	$html .= '  <div class="work-media" data-reveal-media>' . "\n    " . $img . "\n  </div>\n";
	$html .= '  <p class="label work-tag">' . esc_html( $label ) . "</p>\n";
	$html .= '  <h3>' . esc_html( $p['name'] ) . "</h3>\n";
	$html .= '  <ul class="specs">' . aaykay_card_specs( $p ) . "</ul>\n";
	if ( $meta ) {
		$html .= '  <dl class="work-meta">' . $meta . "</dl>\n";
	}
	if ( '' !== $p['card_note'] ) {
		$html .= '  <p class="work-note">' . esc_html( $p['card_note'] ) . "</p>\n";
	}
	return $html . '</article>';
}

/* ------------------------------------------------------------------ logos */

/**
 * The logo for a client or firm: the uploaded logo (Featured image) if there is one,
 * otherwise a logo file bundled with the theme (meta _aaykay_bundled_logo, used for the
 * SVG logos imported at launch). Returns array( url, width, height ) or null.
 */
function aaykay_logo( $post_id ) {
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		$src = wp_get_attachment_image_src( $thumb, 'full' );
		if ( $src && $src[1] && $src[2] ) {
			return array( $src[0], (float) $src[1], (float) $src[2] );
		}
	}
	$bundled = (string) get_post_meta( $post_id, '_aaykay_bundled_logo', true );
	if ( '' !== $bundled && 0 === strpos( $bundled, 'assets/logos/' ) && false === strpos( $bundled, '..' ) ) {
		$size = aaykay_image_size( get_theme_file_path( $bundled ) );
		if ( $size ) {
			return array( aaykay_asset( $bundled ), $size[0], $size[1] );
		}
	}
	return null;
}

function aaykay_logo_posts( $type, $orderby ) {
	return get_posts(
		array(
			'post_type'        => $type,
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => $orderby,
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);
}

/**
 * Client strip: the logo, or the name set as a wordmark in the same cell when there is no
 * logo yet, so the row reads as one set either way.
 */
function aaykay_render_clients( $indent ) {
	$out = array();
	foreach ( aaykay_logo_posts( 'aaykay_client', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) as $post ) {
		$name = $post->post_title;
		$logo = aaykay_logo( $post->ID );
		if ( $logo ) {
			$out[] = '<li class="client">' . aaykay_logo_img( $logo[0], $logo[1], $logo[2], $name, 11, 8, 2.4 ) . '</li>';
		} else {
			$out[] = '<li class="client client--word"><span>' . esc_html( $name ) . '</span></li>';
		}
	}
	return implode( "\n" . $indent, $out );
}

/**
 * Architects and consultants: a mark (logo, or letters on a neutral plate) beside the name.
 * The name is always written out, so the mark is decorative for screen readers.
 */
function aaykay_render_firms( $indent ) {
	$out = array();
	foreach ( aaykay_logo_posts( 'aaykay_firm', 'title' ) as $post ) {
		$name = $post->post_title;
		$logo = aaykay_logo( $post->ID );
		if ( $logo ) {
			$mark = '<span class="firm-mark">' . aaykay_logo_img( $logo[0], $logo[1], $logo[2], '', 6, 5, 2 ) . '</span>';
		} else {
			$letters = (string) get_post_meta( $post->ID, '_aaykay_mark', true );
			$mark    = '<span class="firm-mark firm-mark--letters" aria-hidden="true">' . esc_html( '' !== $letters ? $letters : aaykay_monogram( $name ) ) . '</span>';
		}
		$out[] = '<li class="firm">' . $mark . '<span class="firm-name">' . esc_html( $name ) . '</span></li>';
	}
	return implode( "\n" . $indent, $out );
}
