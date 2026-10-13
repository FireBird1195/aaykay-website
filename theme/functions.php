<?php
/**
 * AAYKAY Electricals theme.
 *
 * Ten pages (inc/pages.php) built from the sections in template-parts/home/: the home page
 * is front-page.php, the others aaykay-page.php.
 * Content that changes lives in WordPress; everything else is in the templates.
 *
 *   inc/helpers.php          small shared functions (asset URLs, icons, numbers, logo sizing)
 *   inc/settings.php         Dashboard > Site settings (contact, figures, resources, SEO)
 *   inc/pages.php            the pages, which sections each shows, the menu
 *   inc/content.php          Dashboard > Homepage content: schema, defaults, getters, photos
 *   inc/admin-content.php    the Homepage content editor screen (dashboard only)
 *   inc/content-types.php    Projects, Sectors, Client logos, Architects & consultants
 *   inc/admin-fields.php     the edit screens for those types (fields, columns, saving)
 *   inc/template-data.php    queries the templates use (record, featured cards, logos, nav)
 *   inc/enquiry.php          the enquiry form: validation, storage, email
 *   inc/leads.php            enquiries to Google Sheets (optional) and CSV download
 *   inc/setup.php            theme supports, scripts and styles, <head> clean-up, hardening
 *   inc/seo.php              title, description, social tags, canonical URL, structured data
 *   inc/starter-content.php  one-time import of seed/content.json
 *   inc/upgrade.php          one-time upgrade of a version 1 site (client brief, 10 Oct 2026)
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

define( 'AAYKAY_VERSION', '2.0.0' );
define( 'AAYKAY_DIR', get_template_directory() );

require AAYKAY_DIR . '/inc/helpers.php';
require AAYKAY_DIR . '/inc/settings.php';
require AAYKAY_DIR . '/inc/pages.php';
require AAYKAY_DIR . '/inc/content.php';
require AAYKAY_DIR . '/inc/content-types.php';
require AAYKAY_DIR . '/inc/template-data.php';
require AAYKAY_DIR . '/inc/enquiry.php';
require AAYKAY_DIR . '/inc/leads.php';
require AAYKAY_DIR . '/inc/setup.php';
require AAYKAY_DIR . '/inc/seo.php';
require AAYKAY_DIR . '/inc/starter-content.php';
require AAYKAY_DIR . '/inc/upgrade.php';

if ( is_admin() ) {
	require AAYKAY_DIR . '/inc/admin-fields.php';
	require AAYKAY_DIR . '/inc/admin-content.php';
}
