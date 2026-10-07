<?php
/**
 * Search and sharing: title, description, canonical URL, social preview tags and
 * structured data (schema.org Organization) for the home page. Values come from
 * Site settings, so contact details are never out of step with what the page shows.
 *
 * Whether search engines may index the site is the WordPress setting
 * Settings > Reading > "Discourage search engines" (keep it ticked until launch).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( is_front_page() ) {
			$custom = trim( (string) aaykay_setting( 'seo_title' ) );
			return '' !== $custom ? esc_html( $custom ) : $title;
		}
		return $title;
	}
);

// WordPress only prints a canonical link on single posts and pages; the home page gets ours.
add_action(
	'wp',
	function () {
		if ( is_front_page() ) {
			remove_action( 'wp_head', 'rel_canonical' );
		}
	}
);

// User accounts are not content: keep them out of wp-sitemap.xml.
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

add_action(
	'wp_head',
	function () {
		if ( ! is_front_page() ) {
			return;
		}
		$name        = 'AAYKAY Electricals';
		$description = trim( (string) aaykay_setting( 'seo_description' ) );
		$home        = home_url( '/' );
		$image       = aaykay_asset( 'assets/og-image.jpg' );
		$c           = aaykay_contact();

		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		echo '<link rel="canonical" href="' . esc_url( $home ) . '">' . "\n";
		$og = array(
			'og:type'        => 'website',
			'og:site_name'   => $name,
			'og:url'         => $home,
			'og:title'       => 'AAYKAY Electricals: electrical systems for buildings that can’t go dark',
			'og:description' => $description,
			'og:image'       => $image,
			'og:image:width' => '1200',
			'og:image:height' => '630',
		);
		foreach ( $og as $property => $content ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

		$data = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Organization',
			'name'          => $name,
			'legalName'     => 'AAYKAY Electricals Private Limited',
			'alternateName' => 'AAYKAY',
			'url'           => $home,
			'foundingDate'  => '2008',
			'founder'       => array(
				'@type' => 'Person',
				'name'  => 'Abdul Kareem P',
			),
			'address'       => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => trim( $c['street'] . ', ' . $c['area'], ', ' ),
				'addressLocality' => $c['city'],
				'addressRegion'   => $c['state'],
				'postalCode'      => preg_replace( '/\s+/', '', $c['postcode'] ),
				'addressCountry'  => 'IN',
			),
			'telephone'     => $c['tel'],
			'email'         => $c['email'],
		);
		if ( aaykay_branches() ) {
			$data['areaServed'] = aaykay_branches();
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
	},
	5
);
