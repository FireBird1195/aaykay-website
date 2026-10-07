<?php
/**
 * Homepage content: every heading, paragraph, list, photo, icon and button label on the
 * page, editable in Dashboard > Homepage content (inc/admin-content.php).
 *
 * How it fits together
 *   - aaykay_content_schema() describes each section and its fields (type, label, help).
 *     The editor screen is drawn from it and saving is checked against it, so adding a
 *     field means adding one line here and using it in a template.
 *   - The launch copy is the default: seed/content.json "content". Until someone saves the
 *     editor, the page shows exactly that.
 *   - Saved values live in one option, aaykay_content (section => field => value).
 *   - Templates read values with aaykay_c( 'section', 'field' ) and print photos with
 *     aaykay_image( 'section', 'field', ... ), escaping everything on output.
 *
 * Field types
 *   text      one line                        textarea  several lines / paragraphs
 *   lines     one item per line (a list)      bool      a tick box
 *   icon      a symbol from the icon sprite   image     a photo: Media Library ID + alt text
 *   list      repeatable rows of sub-fields (add, remove, reorder in the editor)
 *
 * Placeholders such as {clients} are replaced with values from Site settings, so a fact is
 * typed once (see aaykay_tokens()).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

const AAYKAY_CONTENT_OPTION = 'aaykay_content';

/** Placeholder help, shown in the editor next to fields that accept them. */
function aaykay_token_help() {
	return 'Placeholders filled from Site settings: {clients} (e.g. 100+), {branches} (number of branch states), {branches_word} (seven), {turnover} (latest, e.g. 40.11), {turnover_year} (e.g. FY 2025–26), {city} (head office city), {email}, {phone}.';
}

/**
 * The sections of the home page, in page order, and their fields.
 * Field: key => array( type, label, help [, extra] ). Lists carry 'fields' (sub-fields)
 * and optionally 'max' and 'item' (what one row is called).
 */
function aaykay_content_schema() {
	$tokens = aaykay_token_help();
	return array(
		'brand'    => array(
			'title'  => 'Logo, header and footer',
			'desc'   => 'The logo and the short texts in the header and footer, on every page.',
			'toggle' => false,
			'fields' => array(
				'logo'          => array( 'image', 'Logo (optional)', 'Upload the official logo in a light/white version (PNG or WebP, transparent background, at least 400 px wide): the header and footer are dark. Until a logo is uploaded, the drawn “A” mark and the name below are shown.' ),
				'word'          => array( 'text', 'Name next to the mark', 'Shown when no logo image is uploaded.' ),
				'sub1'          => array( 'text', 'Small line 1 next to the name', '' ),
				'sub2'          => array( 'text', 'Small line 2 next to the name', '' ),
				'header_cta'    => array( 'text', 'Header button', 'The orange button at the top right. It always goes to the enquiry form.' ),
				'menu_services' => array( 'text', 'Menu: What we do', 'The menu labels, in page order. A section switched off is left out of the menu automatically.' ),
				'menu_sectors'  => array( 'text', 'Menu: Sectors (footer only)', '' ),
				'menu_work'     => array( 'text', 'Menu: Projects', '' ),
				'menu_record'   => array( 'text', 'Menu: Project record (footer only)', '' ),
				'menu_deliver'  => array( 'text', 'Menu: How we deliver', '' ),
				'menu_quality'  => array( 'text', 'Menu: Quality & safety', '' ),
				'menu_company'  => array( 'text', 'Menu: Company', '' ),
				'menu_prequal'  => array( 'text', 'Menu: Pre-qualification', '' ),
				'menu_contact'  => array( 'text', 'Menu: Contact (footer only)', '' ),
				'footer_blurb'  => array( 'textarea', 'Footer sentence', 'Use {reach} for “, from Hyderabad to seven states” (worked out from Site settings). ' . $tokens ),
				'footer_bottom' => array( 'text', 'Footer, after “© year company name”', '' ),
			),
		),
		'hero'     => array(
			'title'  => 'Opening screen',
			'desc'   => 'The first screen: photo, headline, introduction, buttons and key figures.',
			'toggle' => false,
			'fields' => array(
				'label'           => array( 'text', 'Small label above the headline', '' ),
				'title'           => array( 'lines', 'Headline', 'One line of the headline per line. Three short lines look best. A full stop at the end is shown in red.' ),
				'lead'            => array( 'textarea', 'Introduction', 'One or two sentences under the headline.' ),
				'primary_label'   => array( 'text', 'Orange button', 'Goes to the enquiry form.' ),
				'secondary_label' => array( 'text', 'Outline button', 'Goes to Selected work.' ),
				'image'           => array( 'image', 'Background photo', 'A wide landscape photo of AAYKAY’s own installation, at least 2400 px wide. The centre of the photo stays visible on phones.' ),
				'caption'         => array( 'text', 'Photo caption', 'Small text at the bottom right on large screens.' ),
				'kpis'            => array(
					'list',
					'Key figures',
					'Up to four figures along the bottom of the opening screen. A figure whose value is empty is not shown. ' . $tokens,
					array(
						'item'   => 'figure',
						'max'    => 4,
						'fields' => array(
							'label' => array( 'text', 'Label', '' ),
							'value' => array( 'text', 'Figure', '' ),
						),
					),
				),
			),
		),
		'clients'  => array(
			'title'  => 'Client logos strip',
			'desc'   => 'The heading above the client logos. The logos themselves are under Client logos in the menu.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'sub'     => array( 'text', 'Line on the right', $tokens ),
			),
		),
		'services' => array(
			'title'  => 'What we do',
			'desc'   => 'Introduction, the three strengths, the “What we install” grid and the Sectors heading. Sector rows themselves are under Projects → Sectors.',
			'toggle' => true,
			'fields' => array(
				'heading'         => array( 'text', 'Heading', '' ),
				'lead'            => array( 'textarea', 'Introduction', '' ),
				'pillars'         => array(
					'list',
					'Strengths',
					'Three work best.',
					array(
						'item'   => 'strength',
						'max'    => 6,
						'fields' => array(
							'icon'  => array( 'icon', 'Icon', '' ),
							'title' => array( 'text', 'Title', '' ),
							'text'  => array( 'textarea', 'Text', '' ),
						),
					),
				),
				'scope_heading'   => array( 'text', '“What we install” heading', '' ),
				'scope'           => array(
					'list',
					'What we install',
					'Four or eight items fill the grid evenly.',
					array(
						'item'   => 'item',
						'max'    => 12,
						'fields' => array(
							'icon'  => array( 'icon', 'Icon', '' ),
							'title' => array( 'text', 'Title', '' ),
							'text'  => array( 'textarea', 'Text', '' ),
						),
					),
				),
				'sectors_heading' => array( 'text', 'Sectors heading', '' ),
				'sectors_hint'    => array( 'text', 'Sectors hint', '' ),
			),
		),
		'work'     => array(
			'title'  => 'Selected work',
			'desc'   => 'The heading of the large project cards. Which projects appear is chosen on each project (Projects → tick “Show as a large card”).',
			'toggle' => true,
			'fields' => array(
				'heading'    => array( 'text', 'Heading', '' ),
				'intro'      => array( 'textarea', 'Introduction (optional)', 'Leave empty to use the automatic sentence, which counts the cards and projects for you (“Five projects from a record of more than thirty…”).' ),
				'link_label' => array( 'text', 'Link to the project record', '' ),
			),
		),
		'record'   => array(
			'title'  => 'Project record',
			'desc'   => 'Headings around the project table. The rows are your Projects; the firm list is Architects & consultants.',
			'toggle' => true,
			'fields' => array(
				'heading'       => array( 'text', 'Heading', '' ),
				'sub'           => array( 'textarea', 'Line on the right', '' ),
				'firms_heading' => array( 'text', 'Architects and consultants heading', '' ),
			),
		),
		'photos'   => array(
			'title'  => 'Site photographs',
			'desc'   => 'The dark gallery of installation photos.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'sub'     => array( 'textarea', 'Line on the right', '' ),
				'figures' => array(
					'list',
					'Photos',
					'Six photos fill the mosaic exactly; other numbers also work. Use AAYKAY’s own installation photos only.',
					array(
						'item'   => 'photo',
						'max'    => 12,
						'fields' => array(
							'image'   => array( 'image', 'Photo', '' ),
							'fig'     => array( 'text', 'Number label (e.g. Fig. 2)', 'Optional.' ),
							'caption' => array( 'text', 'Caption', 'What it shows; add the project and year if known.' ),
						),
					),
				),
			),
		),
		'deliver'  => array(
			'title'  => 'How we deliver',
			'desc'   => 'The delivery statement, the two panels, the five stages and the two reference sheets.',
			'toggle' => true,
			'fields' => array(
				'heading'        => array( 'text', 'Heading', '' ),
				'sub'            => array( 'textarea', 'Line on the right', '' ),
				'insight'        => array( 'textarea', 'Statement, first part', 'Shown large.' ),
				'insight_em'     => array( 'textarea', 'Statement, second part', 'Shown large in a lighter colour.' ),
				'panel1_heading' => array( 'text', 'Left panel heading', '' ),
				'panel1_items'   => array( 'lines', 'Left panel points', 'One point per line.' ),
				'panel2_heading' => array( 'text', 'Right panel heading', '' ),
				'panel2_items'   => array( 'lines', 'Right panel points', 'One point per line.' ),
				'panel2_note'    => array( 'textarea', 'Right panel note', '' ),
				'stages'         => array(
					'list',
					'Delivery stages',
					'Numbered automatically (Stage 01, 02…). Five fit the design best.',
					array(
						'item'   => 'stage',
						'max'    => 8,
						'fields' => array(
							'icon'  => array( 'icon', 'Icon', '' ),
							'title' => array( 'text', 'Title', '' ),
							'text'  => array( 'textarea', 'Text', '' ),
							'tags'  => array( 'text', 'Tags', 'Separate with commas.' ),
						),
					),
				),
				'sheet1_label'   => array( 'text', 'Sheet 1: small label', '' ),
				'sheet1_title'   => array( 'text', 'Sheet 1: title', '' ),
				'sheet1_rows'    => array(
					'list',
					'Sheet 1: rows',
					'',
					array(
						'item'   => 'row',
						'max'    => 12,
						'fields' => array(
							'term'  => array( 'text', 'Item', '' ),
							'value' => array( 'text', 'Value', '' ),
						),
					),
				),
				'sheet1_note'    => array( 'text', 'Sheet 1: note', '' ),
				'sheet2_label'   => array( 'text', 'Sheet 2: small label', '' ),
				'sheet2_title'   => array( 'text', 'Sheet 2: title', '' ),
				'sheet2_rows'    => array(
					'list',
					'Sheet 2: rows',
					'',
					array(
						'item'   => 'row',
						'max'    => 12,
						'fields' => array(
							'term'  => array( 'text', 'Item', '' ),
							'value' => array( 'textarea', 'Value', '' ),
						),
					),
				),
			),
		),
		'quality'  => array(
			'title'  => 'Quality & safety',
			'desc'   => 'Certificates, quality and safety practice, photos, the testing kit and the appreciation documents. Staff numbers come from Site settings.',
			'toggle' => true,
			'fields' => array(
				'heading'         => array( 'text', 'Heading', '' ),
				'sub'             => array( 'textarea', 'Line on the right', '' ),
				'certs'           => array(
					'list',
					'Certificates',
					'Only certificates that are current. Remove one the day it expires.',
					array(
						'item'   => 'certificate',
						'max'    => 6,
						'fields' => array(
							'icon'  => array( 'icon', 'Icon', '' ),
							'title' => array( 'text', 'Standard (bold)', 'e.g. ISO 9001:2015' ),
							'text'  => array( 'text', 'What it covers', 'e.g. Quality management' ),
						),
					),
				),
				'quality_icon'    => array( 'icon', 'Quality block icon', '' ),
				'quality_heading' => array( 'text', 'Quality block heading', '' ),
				'quality_rows'    => array(
					'list',
					'Quality points',
					'',
					array(
						'item'   => 'point',
						'max'    => 8,
						'fields' => array(
							'key'  => array( 'text', 'Short label', '' ),
							'text' => array( 'textarea', 'Text', '' ),
						),
					),
				),
				'safety_icon'     => array( 'icon', 'Safety block icon', '' ),
				'safety_heading'  => array( 'text', 'Safety block heading', '' ),
				'safety_rows'     => array(
					'list',
					'Safety points',
					'',
					array(
						'item'   => 'point',
						'max'    => 8,
						'fields' => array(
							'key'  => array( 'text', 'Short label', '' ),
							'text' => array( 'textarea', 'Text', '' ),
						),
					),
				),
				'photos'          => array(
					'list',
					'Photos',
					'Three fit the layout: one large, two small. People must be wearing full safety gear and have agreed to appear.',
					array(
						'item'   => 'photo',
						'max'    => 3,
						'fields' => array(
							'image'   => array( 'image', 'Photo', '' ),
							'fig'     => array( 'text', 'Number label', 'Optional.' ),
							'caption' => array( 'text', 'Caption', '' ),
						),
					),
				),
				'kit_enabled'     => array( 'bool', 'Show the testing kit list', '' ),
				'kit_heading'     => array( 'text', 'Testing kit heading', '' ),
				'kit_sub'         => array( 'text', 'Testing kit line', '' ),
				'kit_items'       => array( 'lines', 'Testing kit', 'One instrument per line.' ),
				'docs_enabled'    => array( 'bool', 'Show the appreciation documents', '' ),
				'docs_heading'    => array( 'text', 'Documents heading', '' ),
				'docs_sub'        => array( 'text', 'Documents line', '' ),
				'docs'            => array(
					'list',
					'Documents',
					'Scans of certificates or letters. Describe each one exactly as the document says; visitors can open the scan and compare.',
					array(
						'item'   => 'document',
						'max'    => 8,
						'fields' => array(
							'image'   => array( 'image', 'Scan', 'A flat, sharp scan; it opens full size when clicked.' ),
							'title'   => array( 'text', 'Title', '' ),
							'meta'    => array( 'textarea', 'Description', '' ),
							'button'  => array( 'text', 'Link text', 'e.g. View certificate' ),
							'caption' => array( 'textarea', 'Caption in the viewer', '' ),
						),
					),
				),
			),
		),
		'company'  => array(
			'title'  => 'Company',
			'desc'   => 'Founder, company facts, history text, quote and timeline. Head office and branch count come from Site settings.',
			'toggle' => true,
			'fields' => array(
				'heading'            => array( 'text', 'Heading', '' ),
				'sub'                => array( 'textarea', 'Line on the right', '' ),
				'founder_photo'      => array( 'image', 'Founder photo', 'A portrait at least 1000 px tall, shown in black and white.' ),
				'founder_name'       => array( 'text', 'Founder name', '' ),
				'founder_title'      => array( 'text', 'Founder title', '' ),
				'creds'              => array(
					'list',
					'Founder credentials',
					'',
					array(
						'item'   => 'credential',
						'max'    => 6,
						'fields' => array(
							'icon' => array( 'icon', 'Icon', '' ),
							'text' => array( 'text', 'Text', '' ),
						),
					),
				),
				'glance_company'     => array( 'text', 'Company name (facts box)', '' ),
				'glance_established' => array( 'text', 'Established (facts box)', '' ),
				'paragraphs'         => array( 'textarea', 'History text', 'Leave an empty line between paragraphs.' ),
				'quote'              => array( 'textarea', 'Quote (optional)', 'Leave empty to hide the quote.' ),
				'quote_by'           => array( 'text', 'Quote attribution', '' ),
				'timeline'           => array(
					'list',
					'Timeline',
					'Use {with} for “, with branches in seven states and more than 100 clients” (from Site settings). ' . $tokens,
					array(
						'item'   => 'year',
						'max'    => 10,
						'fields' => array(
							'year' => array( 'text', 'Year', '' ),
							'text' => array( 'textarea', 'What happened', '' ),
						),
					),
				),
			),
		),
		'letter'   => array(
			'title'  => 'Client letter',
			'desc'   => 'The quote from a client letter, with a scan of the letter.',
			'toggle' => true,
			'fields' => array(
				'label'      => array( 'text', 'Small label', '' ),
				'quote'      => array( 'textarea', 'Quote', 'Copy the words exactly as they appear in the letter.' ),
				'from'       => array( 'text', 'From (bold)', 'e.g. Ramky Group' ),
				'from_rest'  => array( 'text', 'Letter and date', '' ),
				'note'       => array( 'text', 'Note', '' ),
				'image'      => array( 'image', 'Scan of the letter', '' ),
				'link_label' => array( 'text', 'Link text', '' ),
				'caption'    => array( 'text', 'Caption in the viewer', '' ),
			),
		),
		'prequal'  => array(
			'title'  => 'Pre-qualification',
			'desc'   => 'Headings, certificates and registrations. Turnover, staff numbers and branches come from Site settings.',
			'toggle' => true,
			'fields' => array(
				'heading'           => array( 'text', 'Heading', '' ),
				'sub'               => array( 'textarea', 'Line on the right', '' ),
				'turnover_icon'     => array( 'icon', 'Turnover icon', '' ),
				'turnover_heading'  => array( 'text', 'Turnover heading', '' ),
				'resources_icon'    => array( 'icon', 'Resources icon', '' ),
				'resources_heading' => array( 'text', 'Resources heading', '' ),
				'certs_icon'        => array( 'icon', 'Certifications icon', '' ),
				'certs_heading'     => array( 'text', 'Certifications heading', '' ),
				'cert_groups'       => array(
					'list',
					'Certifications and registrations',
					'',
					array(
						'item'   => 'group',
						'max'    => 6,
						'fields' => array(
							'heading' => array( 'text', 'Group heading', '' ),
							'items'   => array( 'lines', 'Items', 'One per line. To make the first words bold, put a | after them: “ISO 9001:2015 | quality management”. A line ending in | is all bold.' ),
						),
					),
				),
				'branches_icon'     => array( 'icon', 'Branches icon', '' ),
				'branches_heading'  => array( 'text', 'Branches heading', '' ),
				'request_label'     => array( 'text', 'Request button', 'A button that opens the enquiry form with “Pre-qualification documents” chosen. Leave empty to hide it.' ),
			),
		),
		'contact'  => array(
			'title'  => 'Contact and enquiry form',
			'desc'   => 'Texts around the enquiry form, the project types, and optional careers and supplier notes. Address, phone and email are in Site settings.',
			'toggle' => false,
			'fields' => array(
				'heading'           => array( 'text', 'Heading', '' ),
				'lead'              => array( 'textarea', 'Introduction', '' ),
				'form_heading'      => array( 'text', 'Form heading', '' ),
				'button'            => array( 'text', 'Send button', '' ),
				'note'              => array( 'textarea', 'Note under the form', $tokens ),
				'project_types'     => array( 'lines', 'Project types', 'The choices in the “Project type” list, one per line. “Other” is always added at the end.' ),
				'careers_heading'   => array( 'text', 'Careers heading', '' ),
				'careers_text'      => array( 'textarea', 'Careers note (optional)', 'For job seekers, e.g. “Engineers and supervisors: send your CV to the address below. AAYKAY never asks for money for a job or an interview.” Leave empty to hide.' ),
				'careers_email'     => array( 'text', 'Careers email (optional)', '' ),
				'suppliers_heading' => array( 'text', 'Suppliers heading', '' ),
				'suppliers_text'    => array( 'textarea', 'Suppliers note (optional)', 'For vendors and subcontractors. Leave empty to hide.' ),
				'suppliers_email'   => array( 'text', 'Suppliers email (optional)', '' ),
			),
		),
	);
}

/** Launch content from seed/content.json. */
function aaykay_content_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$seed     = aaykay_seed_data();
		$defaults = isset( $seed['content'] ) && is_array( $seed['content'] ) ? $seed['content'] : array();
	}
	return $defaults;
}

/** A content value: what was saved in the editor, otherwise the launch default. */
function aaykay_c( $section, $field ) {
	static $saved = null;
	if ( null === $saved ) {
		$saved = get_option( AAYKAY_CONTENT_OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
	}
	if ( isset( $saved[ $section ] ) && is_array( $saved[ $section ] ) && array_key_exists( $field, $saved[ $section ] ) ) {
		return $saved[ $section ][ $field ];
	}
	$defaults = aaykay_content_defaults();
	return isset( $defaults[ $section ][ $field ] ) ? $defaults[ $section ][ $field ] : '';
}

/** Whether a section is switched on (sections without a switch are always on). */
function aaykay_section_on( $section ) {
	$schema = aaykay_content_schema();
	if ( empty( $schema[ $section ]['toggle'] ) ) {
		return true;
	}
	$on = aaykay_c( $section, 'enabled' );
	return '' === $on || ! empty( $on );
}

/** A "lines" field as an array of non-empty lines. */
function aaykay_lines( $section, $field ) {
	$value = aaykay_c( $section, $field );
	$lines = is_array( $value ) ? $value : preg_split( '/\r\n|\r|\n/', (string) $value );
	return array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
}

/** A "list" field as an array of rows. */
function aaykay_rows( $section, $field ) {
	$rows = aaykay_c( $section, $field );
	return is_array( $rows ) ? array_values( array_filter( $rows, 'is_array' ) ) : array();
}

/** Paragraphs from a textarea (an empty line separates them). */
function aaykay_paragraphs( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\n\s*\n/', str_replace( "\r", '', (string) $text ) ) ), 'strlen' ) );
}

/** Replace placeholders with values from Site settings. */
function aaykay_tokens( $text ) {
	$text = (string) $text;
	if ( false === strpos( $text, '{' ) ) {
		return $text;
	}
	$contact  = aaykay_contact();
	$branches = aaykay_branches();
	$turnover = aaykay_turnover();
	$n        = count( $branches );
	$with     = array();
	if ( $n ) {
		$with[] = 'branches in ' . aaykay_number_word( $n ) . ( 1 === $n ? ' state' : ' states' );
	}
	if ( '' !== aaykay_clients_phrase() ) {
		$with[] = aaykay_clients_phrase() . ' clients';
	}
	$reach = '';
	if ( $n ) {
		$reach = ', ' . aaykay_join( ' ', array( '' !== $contact['city'] ? 'from ' . $contact['city'] : '', 'to ' . aaykay_number_word( $n ) . ( 1 === $n ? ' state' : ' states' ) ) );
	}
	$map  = array(
		'{clients}'       => trim( (string) aaykay_setting( 'clients_served' ) ),
		'{branches}'      => $n ? (string) $n : '',
		'{branches_word}' => $n ? aaykay_number_word( $n ) : '',
		'{turnover}'      => $turnover ? aaykay_crore( $turnover[0]['value'] ) : '',
		'{turnover_year}' => $turnover ? $turnover[0]['label'] : '',
		'{city}'          => $contact['city'],
		'{email}'         => $contact['email'],
		'{phone}'         => $contact['phone'],
		'{with}'          => $with ? ', with ' . implode( ' and ', $with ) : '',
		'{reach}'         => $reach,
	);
	$text = strtr( $text, $map );
	return trim( preg_replace( '/ {2,}/', ' ', $text ) );
}

/** True if a figure's value would be empty once placeholders are filled ("₹ Cr" counts as empty). */
function aaykay_token_empty( $value ) {
	if ( preg_match_all( '/\{[a-z_]+\}/', (string) $value, $m ) ) {
		foreach ( $m[0] as $token ) {
			if ( '' === aaykay_tokens( $token ) ) {
				return true;
			}
		}
	}
	return '' === trim( aaykay_tokens( $value ) );
}

/* ------------------------------------------------------------------ photos */

/**
 * Data for an image value: either a Media Library upload (array( 'id' => 12, 'alt' => … ))
 * or a launch default from the theme (array( 'src' => 'assets/img/…', 'srcset' => …,
 * 'w', 'h', 'alt', optional 'full' )). Returns url, srcset, width, height, alt, full, id;
 * or null when there is no image.
 */
function aaykay_image_data( $value ) {
	if ( ! is_array( $value ) ) {
		return null;
	}
	$id = isset( $value['id'] ) ? (int) $value['id'] : 0;
	if ( $id && wp_attachment_is_image( $id ) ) {
		$src  = wp_get_attachment_image_src( $id, 'large' );
		$full = wp_get_attachment_image_src( $id, 'full' );
		$alt  = isset( $value['alt'] ) && '' !== trim( $value['alt'] ) ? $value['alt'] : (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		return array(
			'id'     => $id,
			'url'    => $src ? $src[0] : '',
			'srcset' => (string) wp_get_attachment_image_srcset( $id, 'large' ),
			'w'      => $src ? (int) $src[1] : 0,
			'h'      => $src ? (int) $src[2] : 0,
			'alt'    => $alt,
			'full'   => $full ? $full[0] : '',
		);
	}
	if ( ! empty( $value['src'] ) ) {
		$srcset = array();
		foreach ( (array) ( isset( $value['srcset'] ) ? $value['srcset'] : array() ) as $pair ) {
			$srcset[] = esc_url( aaykay_asset( $pair[0] ) ) . ' ' . (int) $pair[1] . 'w';
		}
		return array(
			'id'     => 0,
			'url'    => aaykay_asset( $value['src'] ),
			'srcset' => implode( ', ', $srcset ),
			'w'      => (int) $value['w'],
			'h'      => (int) $value['h'],
			'alt'    => isset( $value['alt'] ) ? (string) $value['alt'] : '',
			'full'   => aaykay_asset( ! empty( $value['full'] ) ? $value['full'] : $value['src'] ),
		);
	}
	return null;
}

/**
 * An <img> for an image value. $attrs: sizes, class, loading (default lazy),
 * fetchpriority, alt (override, e.g. '' for decorative). Returns '' without an image.
 */
function aaykay_img( $value, $attrs = array() ) {
	$d = aaykay_image_data( $value );
	if ( ! $d || '' === $d['url'] ) {
		return '';
	}
	$attrs = array_merge(
		array(
			'sizes'   => '100vw',
			'loading' => 'lazy',
		),
		$attrs
	);
	$alt   = array_key_exists( 'alt', $attrs ) ? $attrs['alt'] : $d['alt'];
	$html  = '<img src="' . esc_url( $d['url'] ) . '"';
	if ( '' !== $d['srcset'] ) {
		$html .= ' srcset="' . esc_attr( $d['srcset'] ) . '" sizes="' . esc_attr( $attrs['sizes'] ) . '"';
	}
	if ( $d['w'] && $d['h'] ) {
		$html .= ' width="' . (int) $d['w'] . '" height="' . (int) $d['h'] . '"';
	}
	if ( ! empty( $attrs['class'] ) ) {
		$html .= ' class="' . esc_attr( $attrs['class'] ) . '"';
	}
	$html .= ' alt="' . esc_attr( $alt ) . '" loading="' . esc_attr( $attrs['loading'] ) . '"';
	if ( ! empty( $attrs['fetchpriority'] ) ) {
		$html .= ' fetchpriority="' . esc_attr( $attrs['fetchpriority'] ) . '"';
	}
	return $html . ' decoding="async">';
}

/** "ISO 9001:2015 | quality management" -> <b>ISO 9001:2015</b> quality management. */
function aaykay_bold_line( $line ) {
	$parts = explode( '|', $line, 2 );
	if ( 2 !== count( $parts ) ) {
		return esc_html( trim( $line ) );
	}
	$rest = trim( $parts[1] );
	return '<b>' . esc_html( trim( $parts[0] ) ) . '</b>' . ( '' !== $rest ? ' ' . esc_html( $rest ) : '' );
}

/* ------------------------------------------------------------------ saving */

/** Clean a submitted editor form against the schema (also used for the undo copy). */
function aaykay_sanitize_content( $input ) {
	$input  = is_array( $input ) ? wp_unslash( $input ) : array();
	$schema = aaykay_content_schema();
	$out    = array();
	foreach ( $schema as $sid => $section ) {
		$in          = isset( $input[ $sid ] ) && is_array( $input[ $sid ] ) ? $input[ $sid ] : array();
		$out[ $sid ] = array();
		if ( ! empty( $section['toggle'] ) ) {
			$out[ $sid ]['enabled'] = ! empty( $in['enabled'] );
		}
		foreach ( $section['fields'] as $key => $field ) {
			$out[ $sid ][ $key ] = aaykay_sanitize_field( $field, isset( $in[ $key ] ) ? $in[ $key ] : null, $sid, $key );
		}
	}
	return $out;
}

function aaykay_sanitize_field( $field, $raw, $sid = '', $key = '' ) {
	switch ( $field[0] ) {
		case 'bool':
			return ! empty( $raw );
		case 'icon':
			return is_string( $raw ) && in_array( $raw, aaykay_icon_ids(), true ) ? $raw : 'gauge';
		case 'textarea':
		case 'lines':
			return is_string( $raw ) ? sanitize_textarea_field( $raw ) : '';
		case 'image':
			$raw = is_array( $raw ) ? $raw : array();
			$id  = isset( $raw['id'] ) ? absint( $raw['id'] ) : 0;
			$alt = isset( $raw['alt'] ) && is_string( $raw['alt'] ) ? sanitize_text_field( $raw['alt'] ) : '';
			if ( $id && wp_attachment_is_image( $id ) ) {
				return array(
					'id'  => $id,
					'alt' => $alt,
				);
			}
			// No upload: keep the launch photo (with the alt text as edited) if there is one.
			$default = isset( $raw['default'] ) ? json_decode( (string) $raw['default'], true ) : null;
			if ( is_array( $default ) && ! empty( $default['src'] ) && 0 === strpos( (string) $default['src'], 'assets/img/' ) && false === strpos( (string) $default['src'], '..' ) && empty( $raw['remove'] ) ) {
				$clean = array(
					'src'    => (string) $default['src'],
					'srcset' => array(),
					'w'      => (int) ( isset( $default['w'] ) ? $default['w'] : 0 ),
					'h'      => (int) ( isset( $default['h'] ) ? $default['h'] : 0 ),
					'alt'    => $alt,
				);
				foreach ( (array) ( isset( $default['srcset'] ) ? $default['srcset'] : array() ) as $pair ) {
					if ( is_array( $pair ) && isset( $pair[0], $pair[1] ) && 0 === strpos( (string) $pair[0], 'assets/img/' ) && false === strpos( (string) $pair[0], '..' ) ) {
						$clean['srcset'][] = array( (string) $pair[0], (int) $pair[1] );
					}
				}
				if ( ! empty( $default['full'] ) && 0 === strpos( (string) $default['full'], 'assets/img/' ) && false === strpos( (string) $default['full'], '..' ) ) {
					$clean['full'] = (string) $default['full'];
				}
				return $clean;
			}
			return array(
				'id'  => 0,
				'alt' => '',
			);
		case 'list':
			$rows  = array();
			$extra = isset( $field[3] ) ? $field[3] : array();
			$max   = isset( $extra['max'] ) ? (int) $extra['max'] : 20;
			foreach ( is_array( $raw ) ? $raw : array() as $row ) {
				if ( ! is_array( $row ) || count( $rows ) >= $max ) {
					continue;
				}
				$clean = array();
				$empty = true;
				foreach ( $extra['fields'] as $sub_key => $sub ) {
					$clean[ $sub_key ] = aaykay_sanitize_field( $sub, isset( $row[ $sub_key ] ) ? $row[ $sub_key ] : null );
					if ( in_array( $sub[0], array( 'text', 'textarea', 'lines' ), true ) && '' !== $clean[ $sub_key ] ) {
						$empty = false;
					}
					if ( 'image' === $sub[0] && ( ! empty( $clean[ $sub_key ]['id'] ) || ! empty( $clean[ $sub_key ]['src'] ) ) ) {
						$empty = false;
					}
				}
				if ( ! $empty ) {
					$rows[] = $clean;
				}
			}
			return $rows;
		default:
			return is_string( $raw ) ? sanitize_text_field( $raw ) : '';
	}
}

/* ------------------------------------------------------------------ template shorthands */

/** A text value with placeholders filled (not escaped: escape it where it is printed). */
function aaykay_t( $section, $field ) {
	$value = aaykay_c( $section, $field );
	return is_string( $value ) ? aaykay_tokens( $value ) : '';
}

/** Print a text value, escaped. */
function aaykay_e( $section, $field ) {
	echo esc_html( aaykay_t( $section, $field ) );
}

/**
 * The brand in the header and footer: the uploaded logo (Homepage content > Logo, header
 * and footer), or the drawn "A" mark with the name. $with_sub adds the two small lines
 * next to the name (header only).
 */
function aaykay_brand_lockup( $with_sub ) {
	$logo = aaykay_image_data( aaykay_c( 'brand', 'logo' ) );
	if ( $logo && $logo['url'] ) {
		return '<img class="brand-logo" src="' . esc_url( $logo['url'] ) . '"' . ( $logo['w'] && $logo['h'] ? ' width="' . (int) $logo['w'] . '" height="' . (int) $logo['h'] . '"' : '' ) . ' alt="' . esc_attr( '' !== $logo['alt'] ? $logo['alt'] : 'AAYKAY Electricals' ) . '">';
	}
	$html  = '<svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="M3.5 28 14.6 4h2.8L28.5 28h-4.4L16 10.4 7.9 28Z" fill="currentColor"/><path d="M6.5 21.6 26.6 13l-1.5 3.5L8 24.8Z" fill="#E0452B"/></svg>';
	$html .= '<span class="brand-word">' . esc_html( aaykay_t( 'brand', 'word' ) ) . '</span>';
	if ( $with_sub && ( '' !== aaykay_t( 'brand', 'sub1' ) || '' !== aaykay_t( 'brand', 'sub2' ) ) ) {
		$html .= '<span class="brand-sub">' . esc_html( aaykay_t( 'brand', 'sub1' ) ) . '<br>' . esc_html( aaykay_t( 'brand', 'sub2' ) ) . '</span>';
	}
	return $html;
}
