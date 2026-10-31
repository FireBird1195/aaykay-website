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
			// The website's other pages: WordPress prints their title and canonical link; the
			// description is the page's own Excerpt (inc/pages.php), never a copy of the home
			// page's (Google advises against identical descriptions).
			if ( ! is_page() || is_404() ) {
				return;
			}
			$page_id = get_queried_object_id();
			$excerpt = has_excerpt( $page_id ) ? trim( wp_strip_all_tags( get_the_excerpt( $page_id ) ) ) : '';
			if ( '' !== $excerpt ) {
				echo '<meta name="description" content="' . esc_attr( $excerpt ) . '">' . "\n";
			}
			// Social previews (WhatsApp, LinkedIn…) for a shared page link: its own title,
			// address and description, with the site's preview image.
			aaykay_print_social_tags(
				array(
					'og:type'         => 'website',
					'og:site_name'    => get_bloginfo( 'name' ),
					'og:url'          => get_permalink( $page_id ),
					'og:title'        => html_entity_decode( wp_get_document_title(), ENT_QUOTES, 'UTF-8' ),
					'og:description'  => $excerpt,
					'og:image'        => aaykay_asset( 'assets/og-image.jpg' ),
					'og:image:width'  => '1200',
					'og:image:height' => '630',
				)
			);
			return;
		}
		$name        = get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'AAYKAY Electricals';
		$legal       = aaykay_legal_name();
		$founded     = preg_replace( '/\D/', '', aaykay_t( 'company', 'glance_established' ) );
		$founder     = aaykay_t( 'company', 'founder_name' );
		$og_title    = trim( (string) aaykay_setting( 'seo_title' ) );
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
			'og:title'       => '' !== $og_title ? $og_title : $name,
			'og:description' => $description,
			'og:image'       => $image,
			'og:image:width' => '1200',
			'og:image:height' => '630',
		);
		aaykay_print_social_tags( $og );
		$gsc = trim( (string) aaykay_setting( 'gsc_verification' ) );
		if ( '' !== $gsc ) {
			echo '<meta name="google-site-verification" content="' . esc_attr( $gsc ) . '">' . "\n";
		}

		$data = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Organization',
			'name'          => $name,
			'legalName'     => $legal,
			'alternateName' => 'AAYKAY',
			'url'           => $home,
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
		$data['address'] = array_filter( $data['address'], 'strlen' ); // No empty street or PIN when the address is not published.
		// From Homepage content > Company, so the structured data follows what the page says.
		if ( 4 === strlen( $founded ) ) {
			$data['foundingDate'] = $founded;
		}
		if ( '' !== $founder ) {
			$data['founder'] = array(
				'@type' => 'Person',
				'name'  => $founder,
			);
		}
		if ( aaykay_branches() ) {
			$data['areaServed'] = aaykay_branches();
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
	},
	5
);

/** Open Graph tags (empty values are left out) and the large-image card for X/Twitter. */
function aaykay_print_social_tags( $og ) {
	foreach ( $og as $property => $content ) {
		if ( '' !== (string) $content ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '">' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}

/**
 * Cloudflare Web Analytics (optional, Site settings): cookie-free visitor counts, so no
 * cookie banner is needed. Only printed when a token is set, and never for logged-in
 * users (so editing the site doesn't count as visits).
 */
add_action(
	'wp_footer',
	function () {
		$token = trim( (string) aaykay_setting( 'cf_analytics' ) );
		if ( '' === $token || is_user_logged_in() || ! preg_match( '/^[A-Za-z0-9]{16,64}$/', $token ) ) {
			return;
		}
		echo '<script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon="' . esc_attr( wp_json_encode( array( 'token' => $token ) ) ) . '"></script>' . "\n";
	},
	50
);
