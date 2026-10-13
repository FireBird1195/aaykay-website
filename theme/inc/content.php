<?php
/**
 * Homepage content: every heading, paragraph, list, photo, icon and button label on the
 * website's pages, editable in Dashboard > Homepage content (inc/admin-content.php).
 * Which page shows which section: aaykay_page_registry() in inc/pages.php.
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
	return 'Placeholders filled from Site settings: {clients} (e.g. 100+), {branches} (number of branch states), {branches_word} (seven), {area} (area of completed projects, e.g. 69 lakh+), {city} (head office city), {email}, {phone}.';
}

/**
 * The sections of the website, and their fields (the editor shows them in this order).
 * Field: key => array( type, label, help [, extra] ). Lists carry 'fields' (sub-fields)
 * and optionally 'max' and 'item' (what one row is called).
 */
function aaykay_content_schema() {
	$tokens = aaykay_token_help();
	return array(
		'brand'            => array(
			'title'  => 'Logo, header and footer',
			'desc'   => 'The logo mark, the company name and the short texts in the header and footer, on every page. The menu lists the pages (Dashboard > Pages); a page’s title is its name in the menu.',
			'toggle' => false,
			'fields' => array(
				'logo'          => array( 'image', 'Logo mark', 'The round AEK mark, shown before the company name in the header and footer. A square image with a transparent background outside the circle (PNG or WebP, at least 192 × 192 px). Remove it to show the drawn “A” instead.' ),
				'word'          => array( 'text', 'Company name', 'Shown large next to the mark.' ),
				'sub1'          => array( 'text', 'Small line 1 next to the name', 'With line 2, completes the registered name (Electricals / Private Limited).' ),
				'sub2'          => array( 'text', 'Small line 2 next to the name', '' ),
				'header_cta'    => array( 'text', 'Header button', 'The orange button at the top right (and the last item of the phone menu). It goes to the Contact Us page.' ),
				'footer_blurb'  => array( 'textarea', 'Footer sentence', 'Use {reach} for “, from Hyderabad to seven states” (worked out from Site settings). ' . $tokens ),
				'footer_bottom' => array( 'text', 'Footer, after “© year company name”', '' ),
			),
		),
		'hero'             => array(
			'title'  => 'Opening screen',
			'desc'   => 'The first screen: the slideshow of photos, the headline and the introduction.',
			'toggle' => false,
			'fields' => array(
				'label'           => array( 'text', 'Small label above the headline', '' ),
				'title'           => array( 'lines', 'Headline', 'One line of the headline per line. Three short lines look best. A full stop at the end is shown in red.' ),
				'lead'            => array( 'textarea', 'Introduction', 'One or two sentences under the headline.' ),
				'slides'          => array(
					'list',
					'Background photos (slideshow)',
					'The photos behind the headline, shown one after another (a new one every 7 seconds; visitors can pause). The first one loads first, so put the best photo at the top. Use wide landscape photos of AAYKAY’s own installations, at least 2000 px wide; the centre stays visible on phones. One photo shows without the slideshow controls.',
					array(
						'item'   => 'photo',
						'max'    => 12,
						'fields' => array(
							'image'   => array( 'image', 'Photo', '' ),
							'caption' => array( 'text', 'Caption', 'Small text at the bottom right on large screens, e.g. UPS battery room, AAYKAY installation.' ),
						),
					),
				),
			),
		),
		'menu'             => array(
			'title'  => 'Menu',
			'desc'   => 'The menu entry for About Us and the names of the links in the dropdowns under About Us and Projects (desktop menu). Each goes to that section. Leave a name empty to leave the link out; a section that is switched off is left out automatically. Other menu entries are the page titles (Dashboard > Pages).',
			'toggle' => false,
			'fields' => array(
				'about'         => array( 'text', 'About Us: name in the menu', 'Goes to the About Us part of the home page.' ),
				'company'       => array( 'text', 'About Us > the company', '' ),
				'timeline'      => array( 'text', 'About Us > timeline', '' ),
				'team'          => array( 'text', 'About Us > team', '' ),
				'deliver'       => array( 'text', 'About Us > project methodology', '' ),
				'sectors'       => array( 'text', 'Projects > sectors', '' ),
				'record'        => array( 'text', 'Projects > project list', '' ),
				'clients'       => array( 'text', 'Projects > clients', '' ),
				'firms'         => array( 'text', 'Projects > architects, consultants & PMCs', '' ),
				'appreciations' => array( 'text', 'Projects > appreciation certificates', '' ),
			),
		),
		'clients'          => array(
			'title'  => 'Clients',
			'desc'   => 'The heading above the client logos (Projects page). The logos themselves are under Client logos in the menu.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'sub'     => array( 'text', 'Line on the right', $tokens ),
			),
		),
		'services'         => array(
			'title'  => 'Our Capabilities',
			'desc'   => 'Introduction, the three strengths and the capability cards (Our Capabilities page).',
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
				'scope_heading'   => array( 'text', 'Capabilities heading', '' ),
				'scope'           => array(
					'list',
					'Capabilities',
					'One card each, in this order (drag to reorder). Nine fill three rows of three; four or eight fill rows of four. A card with a link address ends with “For more information click here” (“here” opens the address in a new tab). A card whose text is empty shows a “to come” box (Site settings > Placeholders).',
					array(
						'item'   => 'capability',
						'max'    => 12,
						'fields' => array(
							'icon'       => array( 'icon', 'Icon', '' ),
							'title'      => array( 'text', 'Title', '' ),
							'text'       => array( 'textarea', 'Text', '' ),
							'link_url'   => array( 'text', 'Link address (optional)', 'A full address starting with https://. The card then ends with “For more information click here”, and “here” opens this address in a new tab.' ),
							'link_label' => array( 'text', 'Link planned (note)', 'Any note, e.g. More about Fire Safety, marks a card that is meant to have a link. Until the address above is filled in, the line shows only while placeholders are switched on (Site settings > Placeholders), with “here” not yet clickable.' ),
						),
					),
				),
			),
		),
		'sectors'          => array(
			'title'  => 'Sectors served',
			'desc'   => 'The heading of the sector list at the top of the Projects page. The sectors themselves are under Projects → Sectors; selecting one filters the project list.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'hint'    => array( 'text', 'Line on the right', '' ),
			),
		),
		'work'             => array(
			'title'  => 'Selected work',
			'desc'   => 'Large project cards. Not shown on any page at present (client brief, 13 Oct 2026: the Projects page lists sectors, then the project list); kept so the cards can be brought back. Which projects appear is chosen on each project (Projects → tick “Show as a large card”).',
			'toggle' => true,
			'fields' => array(
				'heading'    => array( 'text', 'Heading', '' ),
				'intro'      => array( 'textarea', 'Introduction (optional)', 'Leave empty to use the automatic sentence, which counts the cards and projects for you (“Five projects from a record of more than thirty…”).' ),
				'link_label' => array( 'text', 'Link to the project record', '' ),
			),
		),
		'record'           => array(
			'title'  => 'Project record',
			'desc'   => 'Headings around the project list (Projects page). The rows are your Projects, in alphabetical order; a project without a status shows “To be confirmed” while placeholders are on.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'sub'     => array( 'textarea', 'Line on the right', '' ),
			),
		),
		'firms'            => array(
			'title'  => 'Architects, consultants and PMCs',
			'desc'   => 'The heading of the firm list (Projects page). The firms are under Architects & consultants in the menu.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
			),
		),
		'photos'           => array(
			'title'  => 'Site photographs',
			'desc'   => 'The dark gallery of installation photos (Our Capabilities page).',
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
		'safety_photos'    => array(
			'title'  => 'Site safety photographs',
			'desc'   => 'Photos of site safety procedures (toolbox meetings, barricading, medical check-ups…), shown in the photo gallery at the top of the Quality & Safety page after the quality & safety photos. Switch off to leave them out.',
			'toggle' => true,
			'fields' => array(
				'figures' => array(
					'list',
					'Photos',
					'Use AAYKAY’s own site photos only, with people wearing full safety gear. Each opens full size when clicked.',
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
		'deliver'          => array(
			'title'  => 'About Us: project methodology',
			'desc'   => 'About Us, home page: the project methodology. The five stages come first, on their animated track; under them the statement and the two panels on what is planned before site work.',
			'toggle' => true,
			'fields' => array(
				'heading'        => array( 'text', 'Heading', '' ),
				'sub'            => array( 'textarea', 'Line on the right', '' ),
				'insight'        => array( 'textarea', 'Statement, first part', 'Shown under the stages, in large type.' ),
				'insight_em'     => array( 'textarea', 'Statement, second part', 'Shown large in a lighter colour.' ),
				'panel1_heading' => array( 'text', 'Left panel heading', '' ),
				'panel1_items'   => array( 'lines', 'Left panel points', 'One point per line.' ),
				'panel2_heading' => array( 'text', 'Right panel heading', '' ),
				'panel2_items'   => array( 'lines', 'Right panel points', 'One point per line.' ),
				'panel2_note'    => array( 'textarea', 'Right panel note', '' ),
				'stages'         => array(
					'list',
					'Delivery stages',
					'Numbered automatically (Stage 01, 02…). Five fit the design best. Keep each text to one or two short sentences.',
					array(
						'item'   => 'stage',
						'max'    => 8,
						'fields' => array(
							'icon'  => array( 'icon', 'Icon', '' ),
							'title' => array( 'text', 'Title', '' ),
							'text'  => array( 'textarea', 'Text', '' ),
						),
					),
				),
			),
		),
		'quality'          => array(
			'title'  => 'Quality & safety',
			'desc'   => 'Photos first, then the ISO certificates and the quality and safety practice. Staff numbers come from Site settings.',
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
					'Shown first in the photo gallery at the top of the Quality & Safety page, before the site safety photographs. People must be wearing full safety gear and have agreed to appear. A photo with a caption but no picture yet shows a “to come” box (Site settings > Placeholders).',
					array(
						'item'   => 'photo',
						'max'    => 12,
						'fields' => array(
							'image'   => array( 'image', 'Photo', '' ),
							'fig'     => array( 'text', 'Number label', 'Optional.' ),
							'caption' => array( 'text', 'Caption', '' ),
						),
					),
				),
			),
		),
		'appreciations'    => array(
			'title'  => 'Appreciations',
			'desc'   => 'Certificates and letters of appreciation (Projects page). Each opens full size; visitors step through them with Previous / Next.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'sub'     => array( 'text', 'Line on the right', '' ),
				'docs'    => array(
					'list',
					'Documents',
					'Scans of certificates or letters. Describe each one exactly as the document says; visitors can open the scan and compare. A row with a title but no scan yet shows a “to come” box (Site settings > Placeholders).',
					array(
						'item'   => 'document',
						'max'    => 12,
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
		'certificates'     => array(
			'title'  => 'Certifications & licences',
			'desc'   => 'Scans of the company’s certificates, licences and registrations (Quality & Safety page, after the quality and safety practice). Each opens full size; visitors step through them with Previous / Next.',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'sub'     => array( 'text', 'Line on the right', '' ),
				'docs'    => array(
					'list',
					'Documents',
					'Scans of certificates, licences and registrations, in the order shown. Describe each one exactly as the document says. Check a document is still valid before it is shown, and leave out documents that carry identity numbers meant for officials only (PAN card, ESI and PF registrations). The GST registration certificate is shown at the client’s request (page 1 only; never the annexures, which list directors’ personal details). A row with a title but no scan yet shows a “to come” box (Site settings > Placeholders).',
					array(
						'item'   => 'document',
						'max'    => 16,
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
		'company'          => array(
			'title'  => 'About Us: company, timeline and leadership',
			'desc'   => 'The About Us part of the home page: the company (facts, company brief, our work), the timeline, and the directors with the Managing Director’s message. Head office and branch count come from Site settings.',
			'toggle' => true,
			'fields' => array(
				'heading'            => array( 'text', 'Heading', '' ),
				'sub'                => array( 'textarea', 'Line on the right', '' ),
				'work_heading'       => array( 'text', 'Our work: heading', '' ),
				'work_items'         => array(
					'list',
					'Our work',
					'Short statements with an icon (company profile, “Our work”). Four fit best.',
					array(
						'item'   => 'statement',
						'max'    => 6,
						'fields' => array(
							'icon' => array( 'icon', 'Icon', '' ),
							'text' => array( 'textarea', 'Text', '' ),
						),
					),
				),
				'timeline_heading'   => array( 'text', 'Timeline: heading', '' ),
				'timeline_sub'       => array( 'textarea', 'Timeline: line on the right', '' ),
				'leadership_heading' => array( 'text', 'Leadership: heading', 'Shown above the directors, in the team section.' ),
				'founder_photo'      => array( 'image', 'Founder photo', 'A portrait at least 1000 px tall, shown in black and white.' ),
				'founder_name'       => array( 'text', 'Founder name', 'Also given to Google as the company’s founder.' ),
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
				'person2_photo'      => array( 'image', 'Second director: photo', 'A portrait like the founder’s, at least 1000 px tall. Until one is uploaded, a “to come” box is shown.' ),
				'person2_name'       => array( 'text', 'Second director: name', 'Leave empty to show only the founder.' ),
				'person2_title'      => array( 'text', 'Second director: title', '' ),
				'person2_text'       => array( 'textarea', 'Second director: a line about them (optional)', '' ),
				'glance_company'     => array( 'text', 'Company name (facts box)', 'The registered company name. Also used in the footer copyright line and given to Google.' ),
				'glance_established' => array( 'text', 'Established (facts box)', 'The year, e.g. 2008. Also given to Google as the founding year.' ),
				'paragraphs'         => array( 'textarea', 'Company brief', 'Leave an empty line between paragraphs.' ),
				'quote'              => array( 'textarea', 'Director’s message', 'Publish only the wording the director has approved. Leave an empty line between paragraphs. While empty, a “to come” box is shown in its place (Site settings > Placeholders). Draft options for the director: docs/HANDOFF.md.' ),
				'quote_by'           => array( 'text', 'Message signed by', 'e.g. Abdul Kareem P, Managing Director' ),
				'timeline'           => array(
					'list',
					'Timeline',
					'The company’s history, oldest first, shown as a line of milestones. Three to five fit best. Use {with} for “, with branches in seven states and more than 100 clients” (from Site settings). ' . $tokens,
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
		'team'             => array(
			'title'  => 'About Us: team',
			'desc'   => 'The team section of the home page: the directors (edited under About Us: company, timeline and leadership), then the team, one card each (photo, name, designation). Only members with a name are shown; while there are none, eight “to come” cards mark the space (Site settings > Placeholders).',
			'toggle' => true,
			'fields' => array(
				'heading' => array( 'text', 'Heading', '' ),
				'sub'     => array( 'textarea', 'Line on the right', '' ),
				'members_heading' => array( 'text', 'Team: heading', 'Above the team cards, under the directors.' ),
				'members' => array(
					'list',
					'Team members',
					'Four to a row. Use real photos and titles, with each person’s agreement.',
					array(
						'item'   => 'person',
						'max'    => 12,
						'fields' => array(
							'photo' => array( 'image', 'Photo', 'A portrait at least 800 px tall, shown in black and white like the directors’.' ),
							'name'  => array( 'text', 'Name', '' ),
							'role'  => array( 'text', 'Designation', 'e.g. Project Manager' ),
						),
					),
				),
			),
		),
		'letter'           => array(
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
		'prequal'          => array(
			'title'  => 'Pre-qualification',
			'desc'   => 'Headings, certificates and registrations. Staff numbers and branches come from Site settings.',
			'toggle' => true,
			'fields' => array(
				'heading'           => array( 'text', 'Heading', '' ),
				'sub'               => array( 'textarea', 'Line on the right', '' ),
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
			),
		),
		'careers'          => array(
			'title'  => 'Careers',
			'desc'   => 'The Careers page: employee welfare, the open positions (job title, domain, location, experience and the job description) and how to apply.',
			'toggle' => true,
			'fields' => array(
				'heading'          => array( 'text', 'Heading', '' ),
				'sub'              => array( 'textarea', 'Line on the right', '' ),
				'benefits_heading' => array( 'text', 'Benefits heading', '' ),
				'benefits'         => array(
					'list',
					'Benefits',
					'',
					array(
						'item'   => 'benefit',
						'max'    => 10,
						'fields' => array(
							'icon' => array( 'icon', 'Icon', '' ),
							'key'  => array( 'text', 'Short label', '' ),
							'text' => array( 'textarea', 'Text', '' ),
						),
					),
				),
				'roles_heading'    => array( 'text', 'Open positions: heading', '' ),
				'roles_sub'        => array( 'textarea', 'Open positions: line on the right', '' ),
				'roles'            => array(
					'list',
					'Open positions',
					'One row per opening, shown as a list visitors can filter by domain and open for the full description. Remove a row as soon as the position is filled. While there are none, a “to come” box is shown.',
					array(
						'item'   => 'position',
						'max'    => 20,
						'fields' => array(
							'title'            => array( 'text', 'Job title', 'e.g. Project Engineer – Electrical' ),
							'domain'           => array( 'text', 'Domain', 'e.g. Project execution, Safety, Quality, Design, Procurement. Positions are grouped and filtered by this.' ),
							'location'         => array( 'text', 'Location', 'e.g. Hyderabad, or Hyderabad / Bengaluru' ),
							'experience'       => array( 'text', 'Experience', 'e.g. 3–6 years' ),
							'type'             => array( 'text', 'Employment type', 'e.g. Full-time' ),
							'summary'          => array( 'textarea', 'Role summary', 'Two or three sentences.' ),
							'responsibilities' => array( 'lines', 'Responsibilities', 'One per line.' ),
							'requirements'     => array( 'lines', 'Requirements', 'One per line (qualification, experience, skills).' ),
						),
					),
				),
				'apply_heading'    => array( 'text', 'How to apply: heading', '' ),
				'apply_text'       => array( 'textarea', 'How to apply', '' ),
				'form_url'         => array( 'text', 'Application form address (optional)', 'A link to an application form owned by the company account (e.g. a Google Form). When filled in, the Apply buttons open it in a new tab; otherwise they open an email to the careers address below.' ),
				'form_label'       => array( 'text', 'Apply button text', '' ),
				'email'            => array( 'text', 'Careers email', 'Applications are sent here (Apply buttons open an email with the job title as subject). Empty: the email in Site settings is used.' ),
				'note'             => array( 'textarea', 'Note (optional)', 'A small line at the end, e.g. that AAYKAY never asks for money for a job or an interview (only if the company confirms it).' ),
			),
		),
		'contact'          => array(
			'title'  => 'Contact Us',
			'desc'   => 'The Contact Us page: texts around the contact details and the enquiry form, the branches map, the project types, and optional careers and supplier notes. Address, phones, email and the branch states are in Site settings.',
			'toggle' => false,
			'fields' => array(
				'heading'           => array( 'text', 'Heading', '' ),
				'lead'              => array( 'textarea', 'Introduction', '' ),
				'branches_heading'  => array( 'text', 'Branches heading', 'Above the map of India. The states come from Site settings > States with branches (head office first).' ),
				'branches_sub'      => array( 'text', 'Branches: line on the right', '' ),
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
		'{area}'          => trim( (string) aaykay_setting( 'area_delivered' ) ),
		'{branches_word}' => $n ? aaykay_number_word( $n ) : '',
		'{city}'          => $contact['city'],
		'{email}'         => $contact['email'],
		'{phone}'         => $contact['phone'],
		'{with}'          => $with ? ', with ' . implode( ' and ', $with ) : '',
		'{reach}'         => $reach,
	);
	$text = strtr( $text, $map );
	return trim( preg_replace( '/ {2,}/', ' ', $text ) );
}

/** True if a figure's value would be empty once placeholders are filled. */
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
			// Full size: the file named as such, else the widest in the srcset, else src.
			'full'   => aaykay_asset( ! empty( $value['full'] ) ? $value['full'] : ( ! empty( $value['srcset'] ) ? $value['srcset'][ count( $value['srcset'] ) - 1 ][0] : $value['src'] ) ),
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

/** The opening screen's slideshow: the rows of Background photos that have a photo. */
function aaykay_hero_slides() {
	$slides = array();
	foreach ( aaykay_rows( 'hero', 'slides' ) as $row ) {
		$d = isset( $row['image'] ) ? aaykay_image_data( $row['image'] ) : null;
		if ( $d && '' !== $d['url'] ) {
			$slides[] = $row;
		}
	}
	return $slides;
}

/**
 * Address for a link typed in the editor: "#record" (a section, wherever its page is),
 * "{brochure}" (the brochure PDF), or a full https:// address. '' if it is none of these.
 */
function aaykay_link_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	if ( '{brochure}' === $value ) {
		return aaykay_brochure_url();
	}
	if ( '#' === $value[0] ) {
		return aaykay_section_url( substr( $value, 1 ) );
	}
	return esc_url( $value, array( 'http', 'https', 'mailto', 'tel' ) );
}

/** Whether a link typed in the editor leaves the website (opened in a new tab). */
function aaykay_link_is_external( $value ) {
	$value = trim( (string) $value );
	if ( '{brochure}' === $value ) {
		return true;
	}
	$host = wp_parse_url( $value, PHP_URL_HOST );
	return is_string( $host ) && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host;
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
			// No upload: keep the launch photo (with the alt text as edited) if there is one. The
			// editor posts it as JSON in 'default'; a value that was already cleaned (WordPress
			// sanitises a new option twice) carries it directly as 'src'.
			if ( isset( $raw['default'] ) ) {
				$default = json_decode( (string) $raw['default'], true );
			} else {
				$default = ! empty( $raw['src'] ) ? $raw : null;
			}
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
 * The brand in the header and footer: the round logo mark (Homepage content > Logo,
 * header and footer; the drawn "A" if there is none), then the company name: the name
 * and the two small lines next to it (AAYKAY · Electricals / Private Limited). The mark is
 * decorative: the name says the same thing.
 */
function aaykay_brand_lockup() {
	$logo = aaykay_image_data( aaykay_c( 'brand', 'logo' ) );
	if ( $logo && $logo['url'] ) {
		$html = '<img class="brand-mark brand-mark--img" src="' . esc_url( $logo['url'] ) . '"' . ( '' !== $logo['srcset'] ? ' srcset="' . esc_attr( $logo['srcset'] ) . '" sizes="2.25rem"' : '' ) . ' width="36" height="36" alt="">';
	} else {
		$html = '<svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="M3.5 28 14.6 4h2.8L28.5 28h-4.4L16 10.4 7.9 28Z" fill="currentColor"/><path d="M6.5 21.6 26.6 13l-1.5 3.5L8 24.8Z" fill="#E0452B"/></svg>';
	}
	$html .= '<span class="brand-word">' . esc_html( aaykay_t( 'brand', 'word' ) ) . '</span>';
	if ( '' !== aaykay_t( 'brand', 'sub1' ) || '' !== aaykay_t( 'brand', 'sub2' ) ) {
		$html .= '<span class="brand-sub">' . esc_html( aaykay_t( 'brand', 'sub1' ) ) . '<br>' . esc_html( aaykay_t( 'brand', 'sub2' ) ) . '</span>';
	}
	return $html;
}
