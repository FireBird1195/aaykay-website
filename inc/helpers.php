<?php
/**
 * Small shared helpers. No WordPress hooks are registered here.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/** URL of a file inside the theme, e.g. aaykay_asset( 'assets/img/founder-413.webp' ). */
function aaykay_asset( $path ) {
	return get_theme_file_uri( $path );
}

/** Cache-busting version for a theme file: changes whenever a deploy changes the file. */
function aaykay_asset_version( $path ) {
	$file = get_theme_file_path( $path );
	return file_exists( $file ) ? (string) filemtime( $file ) : AAYKAY_VERSION;
}

/**
 * An icon from the sprite (assets/icons/icons.svg). Icons always sit next to words that say
 * the same thing, so they are hidden from assistive technology.
 */
function aaykay_icon( $name, $extra = '' ) {
	$class = 'ic' . ( $extra ? ' ' . $extra : '' );
	return '<svg class="' . esc_attr( $class ) . '" aria-hidden="true"><use href="' . esc_url( aaykay_asset( 'assets/icons/icons.svg' ) ) . '#' . esc_attr( $name ) . '"/></svg>';
}

/** Symbol ids available in the sprite, for the sector icon picker. */
function aaykay_icon_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$svg = (string) file_get_contents( get_theme_file_path( 'assets/icons/icons.svg' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		preg_match_all( '/<symbol id="([^"]+)"/', $svg, $m );
		$ids = $m[1];
	}
	return $ids;
}

/** Arrow glyphs used by links and buttons (decorative). */
function aaykay_arrow( $direction = 'right' ) {
	$path = 'down' === $direction ? 'M8 1.5v12M3.5 9 8 13.5 12.5 9' : 'M1.5 8h12M9 3.5 13.5 8 9 12.5';
	return '<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="' . $path . '" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>';
}

/**
 * Link to a section of the home page: "#work" on the home page itself, the full URL
 * (https://akepl.in/#work) anywhere else, so the header works on every page.
 */
function aaykay_section_url( $id ) {
	$hash = '#' . ltrim( $id, '#' );
	return is_front_page() ? $hash : home_url( '/' ) . $hash;
}

/** "+91 40 6636 2094" -> "+914066362094" for tel: links. */
function aaykay_tel( $phone ) {
	return preg_replace( '/[^\d+]/', '', (string) $phone );
}

/** Join the non-empty parts: aaykay_join( ', ', array( 'Somajiguda', '', 'Hyderabad' ) ) -> "Somajiguda, Hyderabad". */
function aaykay_join( $separator, $parts ) {
	return implode( $separator, array_filter( array_map( 'trim', (array) $parts ), 'strlen' ) );
}

/**
 * "A · B · C" as escaped HTML. A line never ends on "·" and a short part such as
 * "ISO 9001:2015" is never split; the first short part stays with the word before it.
 *
 * @param string|array $parts Parts, or a string already joined with " · ".
 * @return string Escaped HTML.
 */
function aaykay_dot_line( $parts ) {
	if ( ! is_array( $parts ) ) {
		$parts = explode( ' · ', (string) $parts );
	}
	$parts = array_values( array_filter( array_map( 'trim', $parts ), 'strlen' ) );
	$html  = '';
	foreach ( $parts as $i => $part ) {
		if ( 0 === $i ) {
			$html .= esc_html( $part );
		} elseif ( strlen( $part ) <= 24 ) {
			// The first short part sticks to the word before it; later ones may wrap as "· part".
			$html .= ( 1 === $i ? '&nbsp;' : ' ' ) . '<span class="nowrap">· ' . esc_html( $part ) . '</span>';
		} else {
			$html .= ' · ' . esc_html( $part );
		}
	}
	return $html;
}

/** True for a non-empty string of digits only ("24", not "G+30"). */
function aaykay_is_digits( $value ) {
	return 1 === preg_match( '/^\d+$/', (string) $value );
}

/** 7 -> "seven", 30 -> "thirty"; other numbers are returned as digits. */
function aaykay_number_word( $n ) {
	$words = array( 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve' );
	$tens  = array( 20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety' );
	$n     = (int) $n;
	if ( isset( $words[ $n ] ) ) {
		return $words[ $n ];
	}
	return isset( $tens[ $n ] ) ? $tens[ $n ] : (string) $n;
}

/**
 * Width and height of an image file, read from its header: SVG (viewBox or width/height),
 * PNG, WebP, JPEG, GIF. Returns array( w, h ) or null.
 */
function aaykay_image_size( $file ) {
	if ( ! is_readable( $file ) ) {
		return null;
	}
	if ( preg_match( '/\.svg$/i', $file ) ) {
		$head = (string) file_get_contents( $file, false, null, 0, 65536 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.
		if ( ! preg_match( '/<svg\b[^>]*>/s', $head, $tag ) ) {
			return null;
		}
		$size = null;
		if ( preg_match( '/viewBox="\s*[-\d.]+[\s,]+[-\d.]+[\s,]+([\d.]+)[\s,]+([\d.]+)/', $tag[0], $vb ) ) {
			$size = array( (float) $vb[1], (float) $vb[2] );
		} elseif ( preg_match( '/\bwidth="([\d.]+)/', $tag[0], $w ) && preg_match( '/\bheight="([\d.]+)/', $tag[0], $h ) ) {
			$size = array( (float) $w[1], (float) $h[1] );
		}
		return ( $size && $size[0] > 0 && $size[1] > 0 ) ? $size : null;
	}
	$size = @getimagesize( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- unreadable files return null below.
	return ( $size && $size[0] > 0 && $size[1] > 0 ) ? array( (float) $size[0], (float) $size[1] ) : null;
}

/**
 * An <img> sized for optical balance. Logos come in every shape (IBM is wide, Shell is
 * square), so giving them all the same height makes wide ones shout and square ones vanish.
 * Each logo gets roughly the same area instead, within a max width and height (rem),
 * written as --logo-h for the CSS.
 */
function aaykay_logo_img( $src, $w, $h, $alt, $area, $max_w, $max_h ) {
	$ratio  = $w / $h;
	$height = min( sqrt( $area / $ratio ), $max_h, $max_w / $ratio );
	return sprintf(
		'<img class="logo" src="%s" width="%d" height="%d" alt="%s" style="--logo-h:%.2frem" loading="lazy" decoding="async">',
		esc_url( $src ),
		round( $w ),
		round( $h ),
		esc_attr( $alt ),
		$height
	);
}

/**
 * Letters for a firm without a logo: an acronym as written (JLL, CBRE, ARKK), otherwise the
 * initials of the first two words (M Moser Associates -> MM).
 */
function aaykay_monogram( $name ) {
	$words = array_values(
		array_filter(
			preg_split( '/[\s()]+/u', (string) $name ),
			function ( $w ) {
				return '' !== $w && preg_match( '/^[A-Za-z0-9]/', $w );
			}
		)
	);
	if ( $words ) {
		$first = $words[0];
		$len   = strlen( $first );
		if ( $len >= 2 && $len <= 4 && preg_match( '/^[A-Z]+$/', $first ) ) {
			return $first;
		}
	}
	$letters = '';
	foreach ( array_slice( $words, 0, 2 ) as $w ) {
		$letters .= strtoupper( substr( $w, 0, 1 ) );
	}
	return $letters;
}

/** Echo the escaped URL of a theme file (used throughout the templates). */
function aaykay_a( $path ) {
	echo esc_url( aaykay_asset( $path ) );
}
