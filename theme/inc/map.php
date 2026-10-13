<?php
/**
 * The map of India on the Contact Us page: AAYKAY's branch states, after the "Our
 * branches" page of the company profile (Aug 2026, p.10): the states shaded, a pin in each,
 * a larger pin for the head office state.
 *
 * The states come from Site settings > States with branches (head office first), so the
 * map changes with that list. The outline data (seed/india-map.json) is built from Natural
 * Earth, public domain, by tools/build-india-map.py; it uses the official outline of India.
 * A pin marks a state (at its centre), never an office address. A state name the map does
 * not know is still listed beside the map, just without a pin.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/** Map data (viewBox, outline, states), or null. */
function aaykay_map_data() {
	static $data = false;
	if ( false === $data ) {
		$file = get_theme_file_path( 'seed/india-map.json' );
		$json = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		$data = is_array( $json ) && ! empty( $json['states'] ) ? $json : null;
	}
	return $data;
}

/** "Tamil Nadu", "TAMILNADU", "Telangana (HYD)" -> the map's state name, or ''. */
function aaykay_map_state( $name ) {
	$data = aaykay_map_data();
	if ( ! $data ) {
		return '';
	}
	$key     = function ( $s ) {
		return preg_replace( '/[^a-z]/', '', strtolower( preg_replace( '/\(.*?\)/', '', $s ) ) );
	};
	$aliases = array(
		'maharastra'      => 'maharashtra',
		'orissa'          => 'odisha',
		'pondicherry'     => 'puducherry',
		'newdelhi'        => 'delhi',
		'jk'              => 'jammuandkashmir',
		'jammukashmir'    => 'jammuandkashmir',
		'ladakh'          => 'jammuandkashmir',
		'chattisgarh'     => 'chhattisgarh',
		'uttaranchal'     => 'uttarakhand',
		'andhra'          => 'andhrapradesh',
		'bengal'          => 'westbengal',
		'karnatak'        => 'karnataka',
		'tamilnadustate'  => 'tamilnadu',
		'telanganastate'  => 'telangana',
	);
	$want    = $key( $name );
	$want    = isset( $aliases[ $want ] ) ? $aliases[ $want ] : $want;
	foreach ( array_keys( $data['states'] ) as $state ) {
		if ( $key( $state ) === $want ) {
			return $state;
		}
	}
	return '';
}

/**
 * The branches map as inline SVG (decorative: the list beside it says the same thing), or
 * '' without map data.
 */
function aaykay_branch_map() {
	$data = aaykay_map_data();
	if ( ! $data ) {
		return '';
	}
	$branches = array();
	foreach ( aaykay_branches() as $i => $name ) {
		$state = aaykay_map_state( $name );
		if ( '' !== $state && ! isset( $branches[ $state ] ) ) {
			$branches[ $state ] = 0 === $i;
		}
	}
	$svg = '<svg class="branch-map-svg" viewBox="' . esc_attr( $data['viewBox'] ) . '" aria-hidden="true" focusable="false">';
	foreach ( $data['states'] as $state => $s ) {
		$svg .= '<path class="' . ( isset( $branches[ $state ] ) ? 'map-state is-branch' : 'map-state' ) . '" d="' . esc_attr( $s['d'] ) . '"/>';
	}
	$svg .= '<path class="map-outline" d="' . esc_attr( $data['outline'] ) . '"/>';
	// Pins: a teardrop whose point sits on the state's centre; the head office's is larger.
	foreach ( $branches as $state => $hq ) {
		$s    = $data['states'][ $state ];
		$k    = $hq ? 1.5 : 1;
		$svg .= sprintf(
			'<g class="%s" transform="translate(%s %s) scale(%s)"><path d="M0 0C-5-9-15-17-15-28a15 15 0 1 1 30 0C15-17 5-9 0 0Z"/><circle cx="0" cy="-28" r="5.5"/></g>',
			$hq ? 'map-pin map-pin--hq' : 'map-pin',
			esc_attr( $s['x'] ),
			esc_attr( $s['y'] ),
			esc_attr( $k * 1.6 )
		);
	}
	return $svg . '</svg>';
}
