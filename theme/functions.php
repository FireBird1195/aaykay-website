<?php
/**
 * AAYKAY Electricals theme.
 *
 * One page (front-page.php) built from template parts in template-parts/home/.
 * Content that changes lives in WordPress; everything else is in the templates.
 *
 *   inc/helpers.php          small shared functions (asset URLs, icons, numbers, logo sizing)
 *   inc/settings.php         Dashboard > Site settings (contact, figures, turnover, resources, SEO)
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
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

define( 'AAYKAY_VERSION', '1.0.0' );
define( 'AAYKAY_DIR', get_template_directory() );

require AAYKAY_DIR . '/inc/helpers.php';
require AAYKAY_DIR . '/inc/settings.php';
require AAYKAY_DIR . '/inc/content.php';
require AAYKAY_DIR . '/inc/content-types.php';
require AAYKAY_DIR . '/inc/template-data.php';
require AAYKAY_DIR . '/inc/enquiry.php';
require AAYKAY_DIR . '/inc/leads.php';
require AAYKAY_DIR . '/inc/setup.php';
require AAYKAY_DIR . '/inc/seo.php';
require AAYKAY_DIR . '/inc/starter-content.php';

if ( is_admin() ) {
	require AAYKAY_DIR . '/inc/admin-fields.php';
	require AAYKAY_DIR . '/inc/admin-content.php';
}
