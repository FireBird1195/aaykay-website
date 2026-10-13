<?php
/**
 * One-time upgrade of a site set up with version 1 (the one-page website) to version 2
 * (client brief of 10 Oct 2026: separate pages, new sections, the client's project list).
 *
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

const AAYKAY_DATA_VERSION = 2;

add_action(
	'init',
	function () {
		if ( (int) get_option( 'aaykay_data_version', 1 ) >= AAYKAY_DATA_VERSION ) {
			return;
		}
		// Only a site that imported version 1 content has anything to upgrade.
		if ( get_option( 'aaykay_starter_imported' ) ) {
			aaykay_upgrade_2();
		}
		update_option( 'aaykay_data_version', AAYKAY_DATA_VERSION, true );
	},
	20 // After the content types are registered (init, 10).
);

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
	$old  = json_decode( (string) file_get_contents( get_theme_file_path( 'seed/upgrade-2.json' ) ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	if ( ! $seed || ! is_array( $old ) ) {
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
		$schema   = aaykay_content_schema();
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
		// Fields still showing the version 1 launch value get the version 2 launch value.
		foreach ( $old['content'] as $sid => $fields ) {
			foreach ( $fields as $key => $v1 ) {
				if ( isset( $content[ $sid ] ) && is_array( $content[ $sid ] ) && array_key_exists( $key, $content[ $sid ] ) && aaykay_same_value( $content[ $sid ][ $key ], $v1 ) ) {
					$content[ $sid ][ $key ] = $defaults[ $sid ][ $key ];
				}
			}
		}
		// Fields and sections that no longer exist.
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
}
