<?php
/**
 * Content types. None of them has its own public page: they only feed the home page,
 * so they are not public, not searchable and not in the sitemap.
 *
 *   aaykay_project  Projects          (title, sector, record fields, optional homepage card)
 *   aaykay_sector   Sectors           (taxonomy on projects; label, description, icon, order)
 *   aaykay_client   Client logos      (title = client name, featured image = logo)
 *   aaykay_firm     Architects & consultants (title = firm name, optional logo)
 *
 * Enquiries (aaykay_enquiry) are registered in inc/enquiry.php.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/**
 * Project fields, stored as post meta "_aaykay_<key>" (the underscore keeps them out of
 * the generic Custom Fields box). key => array( label, type, help ).
 */
function aaykay_project_fields() {
	return array(
		'location'     => array( 'Location', 'text', 'e.g. Kondapur, Hyderabad' ),
		'area_sqft'    => array( 'Built-up area (sq ft)', 'int', 'Numbers only, e.g. 130000. Leave empty if not known.' ),
		'floors'       => array( 'Floors', 'text', 'A number (10) or a description (G+30, 2 towers).' ),
		'order_value'  => array( 'AAYKAY order value', 'text', 'As it should be shown, e.g. ₹1.75 Cr. Leave empty to keep it private.' ),
		'design_team'  => array( 'Architect / electrical consultant', 'text', 'Separate several with " / ". For housing, write "Developer: My Home Construction".' ),
		'status'       => array( 'Status', 'text', '"Ongoing", "Completed 2025", or empty.' ),
		'note'         => array( 'Note under the name', 'text', 'Optional, e.g. External electrification: Ariel, Vesta, MLCP.' ),
		'source'       => array( 'Source (internal)', 'text', 'Where these facts come from (profile, brochure, client letter). Never shown on the website.' ),
		'featured'     => array( 'Show as a large card in “Selected work”', 'bool', 'Needs a Card photo (box on the right). Cards are shown in the order set in Card order.' ),
		'card_label'   => array( 'Card label', 'text', 'Small red label above the name, e.g. Healthcare · Hyderabad. Leave empty to use the sector and city.' ),
		'card_details' => array( 'Card details', 'lines', 'One per line, written "Label: value", e.g. Architect: ARKK Consulting.' ),
		'card_note'    => array( 'Card note', 'text', 'Optional line at the bottom of the card.' ),
	);
}

/** A project field value ('' when empty). */
function aaykay_project_meta( $post_id, $key ) {
	return (string) get_post_meta( $post_id, '_aaykay_' . $key, true );
}

/** Sector term fields, stored as term meta "aaykay_<key>". */
function aaykay_sector_fields() {
	return array(
		'short_label' => array( 'Short label', 'text', 'Used on homepage cards, e.g. "Enterprise" for "Enterprise & IT".' ),
		'description' => array( 'Description', 'text', 'One sentence for the Sectors list on the home page. A sector appears on the website once a published project uses it.' ),
		'clients'     => array( 'Example clients', 'text', 'Separate with " · ", e.g. Yashoda · KIMS.' ),
		'icon'        => array( 'Icon', 'icon', 'Shown next to the sector everywhere on the page.' ),
		'order'       => array( 'Order', 'int', 'Lower numbers are listed first.' ),
	);
}

function aaykay_sector_meta( $term_id, $key ) {
	return (string) get_term_meta( $term_id, 'aaykay_' . $key, true );
}

add_action(
	'init',
	function () {
		$private = array(
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		);

		register_post_type(
			'aaykay_project',
			array_merge(
				$private,
				array(
					'labels'        => array(
						'name'                  => 'Projects',
						'singular_name'         => 'Project',
						'add_new'               => 'Add project',
						'add_new_item'          => 'Add project',
						'edit_item'             => 'Edit project',
						'new_item'              => 'New project',
						'view_item'             => 'View project',
						'search_items'          => 'Search projects',
						'not_found'             => 'No projects found',
						'not_found_in_trash'    => 'No projects in the bin',
						'all_items'             => 'All projects',
						'menu_name'             => 'Projects',
						'featured_image'        => 'Card photo',
						'set_featured_image'    => 'Set card photo',
						'remove_featured_image' => 'Remove card photo',
						'use_featured_image'    => 'Use as card photo',
						'attributes'            => 'Card order',
						'item_published'        => 'Project published. It is now on the website.',
						'item_updated'          => 'Project updated.',
					),
					'menu_icon'     => 'dashicons-building',
					'menu_position' => 20,
					'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
				)
			)
		);

		register_post_type(
			'aaykay_client',
			array_merge(
				$private,
				array(
					'labels'        => array(
						'name'                  => 'Client logos',
						'singular_name'         => 'Client logo',
						'add_new'               => 'Add client',
						'add_new_item'          => 'Add client',
						'edit_item'             => 'Edit client',
						'search_items'          => 'Search clients',
						'not_found'             => 'No clients found',
						'not_found_in_trash'    => 'No clients in the bin',
						'all_items'             => 'All clients',
						'menu_name'             => 'Client logos',
						'featured_image'        => 'Logo',
						'set_featured_image'    => 'Set logo',
						'remove_featured_image' => 'Remove logo',
						'use_featured_image'    => 'Use as logo',
						'attributes'            => 'Display order',
						'item_published'        => 'Client published. It is now on the website.',
						'item_updated'          => 'Client updated.',
					),
					'menu_icon'     => 'dashicons-awards',
					'menu_position' => 21,
					'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
				)
			)
		);

		register_post_type(
			'aaykay_firm',
			array_merge(
				$private,
				array(
					'labels'        => array(
						'name'                  => 'Architects & consultants',
						'singular_name'         => 'Architect or consultant',
						'add_new'               => 'Add firm',
						'add_new_item'          => 'Add architect, consultant or PMC',
						'edit_item'             => 'Edit firm',
						'search_items'          => 'Search firms',
						'not_found'             => 'No firms found',
						'not_found_in_trash'    => 'No firms in the bin',
						'all_items'             => 'All firms',
						'menu_name'             => 'Architects & consultants',
						'featured_image'        => 'Logo',
						'set_featured_image'    => 'Set logo',
						'remove_featured_image' => 'Remove logo',
						'use_featured_image'    => 'Use as logo',
						'item_published'        => 'Firm published. It is now on the website.',
						'item_updated'          => 'Firm updated.',
					),
					'menu_icon'     => 'dashicons-groups',
					'menu_position' => 22,
					'supports'      => array( 'title', 'thumbnail' ),
				)
			)
		);

		register_taxonomy(
			'aaykay_sector',
			'aaykay_project',
			array(
				'labels'             => array(
					'name'          => 'Sectors',
					'singular_name' => 'Sector',
					'add_new_item'  => 'Add sector',
					'edit_item'     => 'Edit sector',
					'search_items'  => 'Search sectors',
					'not_found'     => 'No sectors found',
					'menu_name'     => 'Sectors',
				),
				'public'             => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => false,
				'show_in_rest'       => false,
				'show_admin_column'  => true,
				'hierarchical'       => false,
				'rewrite'            => false,
				'query_var'          => false,
				'meta_box_cb'        => 'aaykay_sector_metabox',
			)
		);
	}
);
