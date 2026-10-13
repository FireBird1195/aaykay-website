<?php
/**
 * Quality & Safety page: site safety photographs (toolbox meetings, barricading, medical
 * check-ups…), in the site photographs' mosaic. Content: Homepage content > Site safety
 * photographs.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
get_template_part(
	'template-parts/home/photos',
	null,
	array(
		'section' => 'safety_photos',
		'id'      => 'safety-photos',
	)
);
