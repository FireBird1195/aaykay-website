<?php
/**
 * One-time upgrades of a site set up with an earlier version of the theme. They run once,
 * on the first request after the theme files are updated, in order:
 *   - aaykay_upgrade_2(): version 1 (one-page website) to version 2 (client brief of
 *     10 Oct 2026: separate pages, new sections, the client's project list);
 *   - aaykay_upgrade_3(): version 2 to version 3 (final client brief of 13 Oct 2026);
 *   - aaykay_upgrade_4(): version 3 to version 4 (client review of 13 Oct 2026);
 *   - aaykay_upgrade_5(): version 4 to version 5 (second round of the 13 Oct review);
 *   - aaykay_upgrade_cleanup(): drops saved fields and sections that no longer exist.
 * The rule throughout: a launch value is replaced only while it is still exactly as
 * launched (seed/upgrade-N.json), so nothing AAYKAY has edited is overwritten.
 *
 * Version 2 in detail:
 * A new install needs none of this: the starter import (inc/starter-content.php) brings
 * version 2 directly. On a site that already has version 1 content, it runs once, on the
 * first request after the theme files are updated, and:
 *   - adds what is missing: the pages, the projects and the Industrial sector from the
 *     client's list, and the fields the list confirms on existing projects (status, area,
 *     "Executed via") where those are still empty; updates a sector description the new
 *     projects outgrew, if it is still as launched;
 *   - moves the appreciation documents and the firms heading to their own sections;
 *   - gives a field the new launch content only where it still shows the version 1 launch
 *     value (seed/upgrade-2.json), so nothing AAYKAY has edited is overwritten;
 *   - drops settings and fields that no longer exist (turnover, menu labels).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

const AAYKAY_DATA_VERSION = 5;

add_action(
	'init',
	function () {
		$version = (int) get_option( 'aaykay_data_version', 1 );
		if ( $version >= AAYKAY_DATA_VERSION ) {
			return;
		}
		// Only a site that imported earlier content has anything to upgrade.
		if ( get_option( 'aaykay_starter_imported' ) ) {
			if ( $version < 2 ) {
				aaykay_upgrade_2();
			}
			if ( $version < 3 ) {
				aaykay_upgrade_3();
			}
			if ( $version < 4 ) {
				aaykay_upgrade_4();
			}
			aaykay_upgrade_5();
			aaykay_upgrade_cleanup();
		}
		update_option( 'aaykay_data_version', AAYKAY_DATA_VERSION, true );
	},
	20 // After the content types are registered (init, 10).
);

/** seed/upgrade-N.json as an array, or null. */
function aaykay_upgrade_seed( $n ) {
	$data = json_decode( (string) file_get_contents( get_theme_file_path( 'seed/upgrade-' . (int) $n . '.json' ) ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	return is_array( $data ) ? $data : null;
}

/** Same value, ignoring key order and number/string/boolean spelling (saved values are sanitised). */
function aaykay_same_value( $a, $b ) {
	$norm = function ( $v ) use ( &$norm ) {
		if ( is_array( $v ) ) {
			ksort( $v );
			return array_map( $norm, $v );
		}
		if ( is_bool( $v ) ) {
			return $v ? '1' : '';
		}
		return null === $v ? '' : (string) $v;
	};
	return wp_json_encode( $norm( $a ) ) === wp_json_encode( $norm( $b ) );
}

function aaykay_upgrade_2() {
	$seed = aaykay_seed_data();
	$old  = aaykay_upgrade_seed( 2 );
	if ( ! $seed || ! $old ) {
		return;
	}

	// Pages, new projects and sectors (adds only what is missing).
	aaykay_import_starter_content();

	// Existing projects: fill what the client's list confirms, only where empty.
	foreach ( $seed['projects'] as $p ) {
		$post = get_page_by_path( $p['slug'], OBJECT, 'aaykay_project' );
		if ( ! $post ) {
			continue;
		}
		foreach ( array( 'area_sqft', 'status', 'note' ) as $key ) {
			if ( isset( $p[ $key ] ) && null !== $p[ $key ] && '' === aaykay_project_meta( $post->ID, $key ) ) {
				update_post_meta( $post->ID, '_aaykay_' . $key, (string) $p[ $key ] );
			}
		}
	}

	// Sector descriptions the new projects outgrew, where still as launched.
	foreach ( isset( $old['sectors'] ) ? $old['sectors'] : array() as $key => $fields ) {
		$term = get_term_by( 'slug', $key, 'aaykay_sector' );
		$new  = wp_list_filter( $seed['sectors'], array( 'key' => $key ) );
		if ( ! $term || ! $new ) {
			continue;
		}
		$new = reset( $new );
		foreach ( $fields as $field => $v1 ) {
			if ( (string) get_term_meta( $term->term_id, 'aaykay_' . $field, true ) === $v1 ) {
				update_term_meta( $term->term_id, 'aaykay_' . $field, $new[ $field ] );
			}
		}
	}

	// The options are written as they are: their dashboard sanitisers expect a full form.
	remove_all_filters( 'sanitize_option_' . AAYKAY_SETTINGS_OPTION );
	remove_all_filters( 'sanitize_option_' . AAYKAY_CONTENT_OPTION );

	$settings = get_option( AAYKAY_SETTINGS_OPTION );
	if ( is_array( $settings ) ) {
		$defaults = aaykay_settings_defaults();
		unset( $settings['turnover'] );
		foreach ( $old['settings'] as $key => $v1 ) {
			if ( isset( $settings[ $key ] ) && aaykay_same_value( $settings[ $key ], $v1 ) ) {
				$settings[ $key ] = $defaults[ $key ];
			}
		}
		update_option( AAYKAY_SETTINGS_OPTION, $settings );
	}

	$content = get_option( AAYKAY_CONTENT_OPTION );
	if ( is_array( $content ) && $content ) {
		$defaults = aaykay_content_defaults();
		// Moved to their own pages.
		if ( isset( $content['quality'] ) && ! isset( $content['appreciations'] ) ) {
			$q                        = $content['quality'];
			$content['appreciations'] = array(
				'enabled' => ! isset( $q['docs_enabled'] ) || ! empty( $q['docs_enabled'] ),
				'heading' => isset( $q['docs_heading'] ) ? $q['docs_heading'] : '',
				'sub'     => isset( $q['docs_sub'] ) ? $q['docs_sub'] : '',
				'docs'    => isset( $q['docs'] ) ? $q['docs'] : array(),
			);
		}
		if ( isset( $content['record']['firms_heading'] ) && ! isset( $content['firms'] ) ) {
			$content['firms'] = array(
				'enabled' => true,
				'heading' => $content['record']['firms_heading'],
			);
		}
		// Fields still showing the version 1 launch value get the current launch value (a field
		// that no longer exists, e.g. the single hero photo, is left for aaykay_upgrade_3()).
		foreach ( $old['content'] as $sid => $fields ) {
			foreach ( $fields as $key => $v1 ) {
				if ( isset( $content[ $sid ] ) && is_array( $content[ $sid ] ) && array_key_exists( $key, $content[ $sid ] ) && isset( $defaults[ $sid ] ) && array_key_exists( $key, $defaults[ $sid ] ) && aaykay_same_value( $content[ $sid ][ $key ], $v1 ) ) {
					$content[ $sid ][ $key ] = $defaults[ $sid ][ $key ];
				}
			}
		}
		update_option( AAYKAY_CONTENT_OPTION, $content );
	}
}

/**
 * Version 2 to 3 (final client brief, 13 Oct 2026).
 *   - Pages: search descriptions still as launched get the new ones. About Us is now part of
 *     the home page, and Clients, Architects… and Appreciations are sections of Projects:
 *     their addresses redirect there (inc/pages.php) and the pages can be deleted.
 *   - Opening screen: the photo and its caption become the first photo of the slideshow; if
 *     it is still the launch photo, the launch's other photos follow it.
 *   - Our Capabilities: the AAYKAY Safety Solutions section becomes the ninth card, Fire
 *     Safety, with its text and website address carried over. An unedited card list becomes
 *     the new launch list (Wiring, HT/LT, Containment first); an edited one keeps its order
 *     and gets Fire Safety added at the end.
 *   - The sectors heading moves from Our Capabilities to its own section, if it was edited.
 *   - Careers benefits get icons; openings typed one per line become positions. Texts still
 *     as launched get the new launch text ("Start a project" becomes "Contact Us", "How we
 *     deliver" becomes "Project Methodology", and so on).
 */
function aaykay_upgrade_3() {
	$old = aaykay_upgrade_seed( 3 );
	if ( ! $old ) {
		return;
	}
	aaykay_ensure_pages();
	$reg = aaykay_page_registry();
	foreach ( isset( $old['pages']['titles'] ) ? $old['pages']['titles'] : array() as $key => $v2 ) {
		$id = aaykay_page_id_any( $key );
		if ( $id && get_post_field( 'post_title', $id ) === $v2 ) {
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => $reg[ $key ][0],
				)
			);
		}
	}
	foreach ( $old['pages']['excerpts'] as $key => $v2 ) {
		$id = aaykay_page_id_any( $key );
		if ( $id && get_post_field( 'post_excerpt', $id ) === $v2 ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_excerpt' => $reg[ $key ][4],
				)
			);
		}
	}

	$content = get_option( AAYKAY_CONTENT_OPTION );
	if ( ! is_array( $content ) || ! $content ) {
		return; // Never saved: the website already shows the version 3 launch content.
	}
	remove_all_filters( 'sanitize_option_' . AAYKAY_CONTENT_OPTION );
	$defaults = aaykay_content_defaults();

	// Opening screen: one photo -> the slideshow.
	if ( isset( $content['hero'] ) && is_array( $content['hero'] ) && ! isset( $content['hero']['slides'] ) ) {
		$image = isset( $content['hero']['image'] ) ? $content['hero']['image'] : null;
		if ( aaykay_image_data( $image ) ) {
			$slides = array(
				array(
					'image'   => $image,
					'caption' => isset( $content['hero']['caption'] ) ? (string) $content['hero']['caption'] : '',
				),
			);
			if ( ! empty( $image['src'] ) && $image['src'] === $old['moved']['hero_image_src'] ) {
				$slides = array_merge( $slides, array_slice( $defaults['hero']['slides'], 1 ) );
			}
			$content['hero']['slides'] = $slides;
		}
	}

	// Our Capabilities: AAYKAY Safety Solutions -> the Fire Safety card.
	if ( isset( $content['services'] ) && is_array( $content['services'] ) ) {
		$fire = null;
		foreach ( $defaults['services']['scope'] as $card ) {
			if ( 'flame' === $card['icon'] ) {
				$fire = $card;
			}
		}
		$ss = isset( $content['safety_solutions'] ) && is_array( $content['safety_solutions'] ) ? $content['safety_solutions'] : array();
		if ( $fire ) {
			if ( ! empty( $ss['text'] ) ) {
				$fire['text'] = (string) $ss['text'];
			}
			if ( ! empty( $ss['link_url'] ) ) {
				$fire['link_url'] = (string) $ss['link_url'];
			}
			if ( ! empty( $ss['link_label'] ) && $ss['link_label'] !== $old['moved']['safety_solutions_link_label'] ) {
				$fire['link_label'] = (string) $ss['link_label'];
			}
		}
		$scope = isset( $content['services']['scope'] ) && is_array( $content['services']['scope'] ) ? $content['services']['scope'] : null;
		if ( null !== $scope && aaykay_same_value( $scope, $old['content']['services']['scope'] ) ) {
			$scope = $defaults['services']['scope'];
			foreach ( $scope as $i => $card ) {
				if ( $fire && 'flame' === $card['icon'] ) {
					$scope[ $i ] = $fire;
				}
			}
		} elseif ( null !== $scope ) {
			$has = false;
			foreach ( $scope as $i => $card ) {
				$scope[ $i ] = array_merge(
					array(
						'link_url'   => '',
						'link_label' => '',
					),
					$card
				);
				$has         = $has || false !== stripos( (string) $card['title'], 'fire' );
			}
			if ( ! $has && $fire ) {
				$scope[] = $fire;
			}
		}
		if ( null !== $scope ) {
			$content['services']['scope'] = array_values( $scope );
		}
		// The sectors heading, if it was edited, moves to the new Sectors served section.
		$sectors = array();
		foreach ( array( 'heading' => 'sectors_heading', 'hint' => 'sectors_hint' ) as $new => $was ) {
			if ( isset( $content['services'][ $was ] ) && $content['services'][ $was ] !== $old['moved']['sectors'][ $new ] ) {
				$sectors[ $new ] = (string) $content['services'][ $was ];
			}
		}
		if ( $sectors && ! isset( $content['sectors'] ) ) {
			$content['sectors'] = array_merge( array( 'enabled' => true ), $defaults['sectors'], $sectors );
		}
	}

	// Careers benefits: icons.
	if ( isset( $content['careers']['benefits'] ) && is_array( $content['careers']['benefits'] ) ) {
		if ( aaykay_same_value( $content['careers']['benefits'], $old['content']['careers']['benefits'] ) ) {
			$content['careers']['benefits'] = $defaults['careers']['benefits'];
		} else {
			$icons = array();
			foreach ( $defaults['careers']['benefits'] as $b ) {
				$icons[ $b['key'] ] = $b['icon'];
			}
			foreach ( $content['careers']['benefits'] as $i => $b ) {
				if ( empty( $b['icon'] ) ) {
					$content['careers']['benefits'][ $i ]['icon'] = isset( $b['key'], $icons[ $b['key'] ] ) ? $icons[ $b['key'] ] : 'circle-check';
				}
			}
		}
	}

	// Careers: openings typed one per line become positions (job title only, to complete).
	if ( isset( $content['careers']['roles'] ) && is_string( $content['careers']['roles'] ) && '' !== trim( $content['careers']['roles'] ) ) {
		$rows = array();
		foreach ( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $content['careers']['roles'] ) ), 'strlen' ) as $title ) {
			$rows[] = array_fill_keys( array( 'domain', 'location', 'experience', 'type', 'summary', 'responsibilities', 'requirements' ), '' ) + array( 'title' => $title );
		}
		$content['careers']['roles'] = $rows;
	}

	// Texts still as launched in version 2 get the version 3 launch text.
	foreach ( $old['content'] as $sid => $fields ) {
		foreach ( $fields as $key => $v2 ) {
			if ( in_array( $sid . '.' . $key, array( 'services.scope', 'careers.benefits' ), true ) ) {
				continue; // Done above.
			}
			if ( isset( $content[ $sid ] ) && is_array( $content[ $sid ] ) && array_key_exists( $key, $content[ $sid ] ) && isset( $defaults[ $sid ] ) && array_key_exists( $key, $defaults[ $sid ] ) && aaykay_same_value( $content[ $sid ][ $key ], $v2 ) ) {
				$content[ $sid ][ $key ] = $defaults[ $sid ][ $key ];
			}
		}
	}
	update_option( AAYKAY_CONTENT_OPTION, $content );
}

/**
 * Version 3 to 4 (client review of 13 Oct 2026).
 *   - Texts and lists still as launched in version 3 get the version 4 launch value: the
 *     capabilities heading ("Scope of Works" becomes "Works We Undertake"), the card list
 *     (HT/LT first), the methodology stages (without tags) and the appreciations line.
 *   - An edited card list keeps its order; only a card still titled "Panels & distribution"
 *     is renamed "HT/LT Panels & Distribution".
 *   - The Quality & Safety page description, if still as launched, gets the new one.
 *   - Removed fields (opening-screen buttons and key figures, Why AAYKAY, the methodology
 *     sheets, the testing equipment list, the pre-qualification button, the safety photos'
 *     heading) are dropped afterwards by aaykay_upgrade_cleanup().
 * The new Certifications & licences section needs nothing: unsaved fields show the launch
 * content.
 */
function aaykay_upgrade_4() {
	$old = aaykay_upgrade_seed( 4 );
	if ( ! $old ) {
		return;
	}
	$reg = aaykay_page_registry();
	foreach ( $old['pages']['excerpts'] as $key => $v3 ) {
		$id = aaykay_page_id_any( $key );
		if ( $id && get_post_field( 'post_excerpt', $id ) === $v3 ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_excerpt' => $reg[ $key ][4],
				)
			);
		}
	}

	$content = get_option( AAYKAY_CONTENT_OPTION );
	if ( ! is_array( $content ) || ! $content ) {
		return; // Never saved: the website already shows the version 4 launch content.
	}
	remove_all_filters( 'sanitize_option_' . AAYKAY_CONTENT_OPTION );
	$defaults = aaykay_content_defaults();
	foreach ( $old['content'] as $sid => $fields ) {
		foreach ( $fields as $key => $v3 ) {
			if ( isset( $content[ $sid ] ) && is_array( $content[ $sid ] ) && array_key_exists( $key, $content[ $sid ] ) && isset( $defaults[ $sid ] ) && array_key_exists( $key, $defaults[ $sid ] ) && aaykay_same_value( $content[ $sid ][ $key ], $v3 ) ) {
				$content[ $sid ][ $key ] = $defaults[ $sid ][ $key ];
			}
		}
	}
	if ( isset( $content['services']['scope'] ) && is_array( $content['services']['scope'] ) ) {
		foreach ( $content['services']['scope'] as $i => $card ) {
			if ( isset( $card['title'] ) && $card['title'] === $old['moved']['scope_card_title'] ) {
				$content['services']['scope'][ $i ]['title'] = $defaults['services']['scope'][0]['title'];
			}
		}
	}
	update_option( AAYKAY_CONTENT_OPTION, $content );
}

/**
 * Version 4 to 5 (second round of the client review of 13 Oct 2026).
 *   - The opening headline becomes "Complete electrical installations, from design to
 *     commissioning." and the GST registration certificate joins the Certifications &
 *     Licences list. Each is replaced only while the saved value is still exactly the version
 *     4 launch value (seed/upgrade-5.json), so nothing AAYKAY has edited is overwritten.
 *   - The empty "site team member in full PPE" photo slot on Quality & Safety is removed.
 */
function aaykay_upgrade_5() {
	$old     = aaykay_upgrade_seed( 5 );
	$content = get_option( AAYKAY_CONTENT_OPTION );
	if ( ! $old || ! is_array( $content ) || ! $content ) {
		return; // Never saved: the website already shows the version 5 launch content.
	}
	remove_all_filters( 'sanitize_option_' . AAYKAY_CONTENT_OPTION );
	$defaults = aaykay_content_defaults();
	foreach ( $old['content'] as $sid => $fields ) {
		foreach ( $fields as $key => $v4 ) {
			if ( isset( $content[ $sid ] ) && is_array( $content[ $sid ] ) && array_key_exists( $key, $content[ $sid ] ) && isset( $defaults[ $sid ] ) && array_key_exists( $key, $defaults[ $sid ] ) && aaykay_same_value( $content[ $sid ][ $key ], $v4 ) ) {
				$content[ $sid ][ $key ] = $defaults[ $sid ][ $key ];
			}
		}
	}
	// The "site team member in full PPE" photo slot (no photo was ever supplied) is removed.
	if ( isset( $content['quality']['photos'] ) && is_array( $content['quality']['photos'] ) ) {
		$content['quality']['photos'] = array_values(
			array_filter(
				$content['quality']['photos'],
				function ( $row ) {
					$img = is_array( $row ) && isset( $row['image'] ) && is_array( $row['image'] ) ? $row['image'] : array();
					return ! ( is_array( $row ) && empty( $img['id'] ) && empty( $img['src'] ) && isset( $row['caption'] ) && 'A site team member in full PPE with the AAYKAY logo' === $row['caption'] );
				}
			)
		);
	}
	update_option( AAYKAY_CONTENT_OPTION, $content );
}

/** Drop saved sections and fields that the current schema no longer has (after the upgrades). */
function aaykay_upgrade_cleanup() {
	$content = get_option( AAYKAY_CONTENT_OPTION );
	if ( ! is_array( $content ) || ! $content ) {
		return;
	}
	remove_all_filters( 'sanitize_option_' . AAYKAY_CONTENT_OPTION );
	$schema = aaykay_content_schema();
	foreach ( $content as $sid => $fields ) {
		if ( ! isset( $schema[ $sid ] ) || ! is_array( $fields ) ) {
			unset( $content[ $sid ] );
			continue;
		}
		foreach ( array_keys( $fields ) as $key ) {
			if ( 'enabled' !== $key && ! isset( $schema[ $sid ]['fields'][ $key ] ) ) {
				unset( $content[ $sid ][ $key ] );
			}
		}
	}
	update_option( AAYKAY_CONTENT_OPTION, $content );
}
