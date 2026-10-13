<?php
/**
 * Dashboard > Site settings.
 *
 * Facts that appear in several places on the page are entered once here and used
 * everywhere: the phone number and email (header menu, contact block, footer, structured
 * data), the branch states (hero figure, company facts, pre-qualification, contact, footer),
 * and the resource counts (pre-qualification and quality & safety). Turnover is not
 * published (client brief, 10 Oct 2026).
 *
 * Stored as one option, aaykay_settings. Editors can change it (capability
 * edit_others_posts), so AAYKAY staff do not need an Administrator account for this.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

const AAYKAY_SETTINGS_OPTION = 'aaykay_settings';
const AAYKAY_SETTINGS_CAP    = 'edit_others_posts';

/** Field definitions: key => array( section, label, type, help ). Drives the form and sanitising. */
function aaykay_settings_fields() {
	return array(
		'phone'             => array( 'contact', 'Phone number', 'text', 'As it should be shown, with the country code, e.g. +91 98667 00400.' ),
		'phone2'            => array( 'contact', 'Second phone number (optional)', 'text', 'Shown after the first one in the phone menu, the contact section and the footer.' ),
		'email'             => array( 'contact', 'Email address', 'email', 'Shown on the website.' ),
		'enquiry_to'        => array( 'contact', 'Send enquiries to', 'emails', 'Where website enquiries are emailed. Separate several addresses with commas. Every enquiry is also saved under Enquiries.' ),
		'show_address'      => array( 'contact', 'Show the street address on the website', 'bool', 'When unticked, only the city and state are shown (and the Google Maps link is hidden). The address below stays saved.' ),
		'street'            => array( 'contact', 'Address: building and street', 'text', 'e.g. Flat 1C, 1st Floor, Dhruvatara Apartments, 6-3-652/D/13, Panjagutta Road' ),
		'area'              => array( 'contact', 'Address: area', 'text', 'e.g. Somajiguda' ),
		'city'              => array( 'contact', 'Address: city', 'text', 'e.g. Hyderabad' ),
		'postcode'          => array( 'contact', 'Address: PIN code', 'text', 'e.g. 500 082' ),
		'state'             => array( 'contact', 'Address: state', 'text', 'e.g. Telangana' ),
		'maps_url'          => array( 'contact', 'Google Maps link (optional)', 'url', 'Leave empty to search Google Maps for the address above.' ),
		'area_delivered'    => array( 'company', 'Area of completed projects (sq ft)', 'text', 'As it should be shown in the opening screen, e.g. 69 lakh+. The launch figure is the total of the 55 completed projects in AAYKAY’s project list of 10 Oct 2026 (69.8 lakh sq ft). Leave empty to hide that figure.' ),
		'clients_served'    => array( 'company', 'Clients served', 'text', 'e.g. 100+. Shown in the hero figures, above the client logos and in the company timeline.' ),
		'branches'          => array( 'company', 'States with branches', 'lines', 'One state per line. Put the head office state first. The number of lines is shown as "States with branches".' ),
		'cin'               => array( 'company', 'CIN (optional)', 'text', 'Corporate Identification Number from the MCA record. Shown in the footer when filled in; procurement teams look for it.' ),
		'gstin'             => array( 'company', 'GSTIN (optional)', 'text', 'Shown in the footer when filled in.' ),
		'registered_office' => array( 'company', 'Registered office (optional)', 'text', 'Only if it differs from the head office. Shown in the footer.' ),
		'show_order_values' => array( 'company', 'Show order values on the website', 'bool', 'When ticked, the AAYKAY order value of each project is shown in the project record and on the cards. Untick to hide every value at once (the values stay saved). Publish values only with the managing director’s approval and, where contracts require it, the client’s.' ),
		'res_pm'            => array( 'resources', 'Project managers', 'count', '' ),
		'res_pe'            => array( 'resources', 'Project engineers', 'count', '' ),
		'res_sup'           => array( 'resources', 'Supervisors', 'count', 'Also shown in Quality & safety.' ),
		'res_quality'       => array( 'resources', 'Quality staff', 'count', 'Also shown in Quality & safety.' ),
		'res_safety'        => array( 'resources', 'Safety staff', 'count', 'Also shown in Quality & safety.' ),
		'res_office'        => array( 'resources', 'Office staff', 'count', '' ),
		'res_skilled'       => array( 'resources', 'Workforce: skilled', 'count', '' ),
		'res_unskilled'     => array( 'resources', 'Workforce: unskilled', 'count', '' ),
		'res_out_skilled'   => array( 'resources', 'Outsourced: skilled', 'count', '' ),
		'res_out_unskilled' => array( 'resources', 'Outsourced: unskilled', 'count', '' ),
		'brochure_url'      => array( 'brochure', 'Brochure (PDF) address', 'doc', 'The PDF that the “Brochure” tab on the left edge of the website opens. To use a new PDF: Media > Add New, upload it, open it, “Copy URL to clipboard”, and paste it here. A link to a PDF in the company’s cloud storage also works. Leave empty to hide the tab. Launch file: the company profile of Aug 2026 (theme file assets/docs/aaykay-company-profile.pdf), which still includes the turnover figures.' ),
		'brochure_label'    => array( 'brochure', 'Brochure tab text', 'text', 'One short word reads best on the vertical tab, e.g. Brochure.' ),
		'show_pending'      => array( 'pending', 'Show “to come” placeholders', 'bool', 'Dashed boxes marking content AAYKAY is still to supply (photos, the director’s message, team members, the Fire Safety text and link, project statuses, the careers form). Untick before launch to hide every placeholder at once.' ),
		'sheets_url'        => array( 'leads', 'Google Sheets connection (web app URL)', 'url', 'Optional. Paste the web app URL from the Google Sheet’s Apps Script (it starts with https://script.google.com/macros/s/). Every new enquiry is then also added as a row in that sheet. Steps: client guide, section C10.' ),
		'sheets_secret'     => array( 'leads', 'Google Sheets secret code', 'text', 'Paste the same code into the Apps Script, so only this website can add rows. Created automatically; change it only if it has been shared by mistake.' ),
		'gsc_verification'  => array( 'seo', 'Google Search Console code (optional)', 'text', 'If Search Console offers an “HTML tag”, paste only the code inside content="…". Verifying with a DNS record instead needs nothing here.' ),
		'cf_analytics'      => array( 'seo', 'Cloudflare Web Analytics token (optional)', 'text', 'Free, cookie-free visitor statistics. Paste the token from Cloudflare → Web Analytics → Add a site → “JS snippet” (the value of "token"). Leave empty for no analytics.' ),
		'seo_title'         => array( 'seo', 'Page title in search results', 'text', 'About 50–60 characters. Also the browser tab title.' ),
		'seo_description'   => array( 'seo', 'Description in search results', 'textarea', 'About 150–160 characters. Also used when the link is shared.' ),
	);
}

function aaykay_settings_sections() {
	return array(
		'contact'   => array( 'Contact details', 'Used in the menu, the contact section, the footer and for search engines.' ),
		'company'   => array( 'Company figures', '' ),
		'resources' => array( 'Resources', 'Head counts for the pre-qualification section. Leave a box empty to hide that line.' ),
		'leads'     => array( 'Enquiries in Google Sheets', 'Every enquiry is always saved under Enquiries and emailed. Connecting a Google Sheet is optional: it gives the office a live spreadsheet of leads.' ),
		'brochure'  => array( 'Brochure', 'The tab on the left edge of every page (on phones: in the menu).' ),
		'seo'       => array( 'Search engines', '' ),
		'pending'   => array( 'Placeholders', '' ),
	);
}

/** Defaults come from seed/content.json, so the site renders correctly even before anything is saved. */
function aaykay_settings_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$seed     = aaykay_seed_data();
		$defaults = isset( $seed['settings'] ) && is_array( $seed['settings'] ) ? $seed['settings'] : array();
	}
	return $defaults;
}

/** One setting, falling back to the default. */
function aaykay_setting( $key ) {
	$saved = get_option( AAYKAY_SETTINGS_OPTION, array() );
	if ( is_array( $saved ) && array_key_exists( $key, $saved ) ) {
		return $saved[ $key ];
	}
	$defaults = aaykay_settings_defaults();
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/* ------------------------------------------------------------------ derived values */

function aaykay_contact() {
	$c = array();
	foreach ( array( 'phone', 'email', 'street', 'area', 'city', 'postcode', 'state', 'maps_url' ) as $k ) {
		$c[ $k ] = trim( (string) aaykay_setting( $k ) );
	}
	$c['phone2'] = trim( (string) aaykay_setting( 'phone2' ) );
	$c['tel']    = aaykay_tel( $c['phone'] );
	$c['tel2']   = aaykay_tel( $c['phone2'] );
	// Street, area and PIN are published only when Site settings say so (client brief, 10 Oct 2026).
	$c['full'] = '1' === (string) aaykay_setting( 'show_address' );
	if ( ! $c['full'] ) {
		$c['street']   = '';
		$c['area']     = '';
		$c['postcode'] = '';
		$c['maps_url'] = '';
	} elseif ( '' === $c['maps_url'] ) {
		$query         = trim( preg_replace( '/^#?[\w-]+,\s*(\d+\w*\s+Floor,\s*)?/i', '', $c['street'] ) . ' ' . $c['area'] . ' ' . $c['city'] );
		$c['maps_url'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $query );
	}
	return $c;
}

/** Branch states, head office state first. */
function aaykay_branches() {
	$lines = preg_split( '/\r\n|\r|\n/', (string) aaykay_setting( 'branches' ) );
	return array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
}

/** Address of the brochure PDF (Site settings > Brochure), or '' when there is none. */
function aaykay_brochure_url() {
	$v = trim( (string) aaykay_setting( 'brochure_url' ) );
	if ( '' === $v ) {
		return '';
	}
	if ( 0 === strpos( $v, 'assets/docs/' ) ) {
		return is_readable( get_theme_file_path( $v ) ) ? aaykay_asset( $v ) : '';
	}
	return esc_url( $v );
}

/** Text on the brochure tab. */
function aaykay_brochure_label() {
	$v = trim( (string) aaykay_setting( 'brochure_label' ) );
	return '' !== $v ? $v : 'Brochure';
}

/** "100+" -> "more than 100" for running text. */
function aaykay_clients_phrase() {
	$v = trim( (string) aaykay_setting( 'clients_served' ) );
	return preg_match( '/^(\d[\d,]*)\+$/', $v, $m ) ? 'more than ' . $m[1] : $v;
}

/* ------------------------------------------------------------------ admin page */

add_action(
	'admin_menu',
	function () {
		add_menu_page( 'Site settings', 'Site settings', AAYKAY_SETTINGS_CAP, 'aaykay-settings', 'aaykay_render_settings_page', 'dashicons-admin-settings', 25 );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'aaykay_settings',
			AAYKAY_SETTINGS_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'aaykay_sanitize_settings',
				'default'           => array(),
			)
		);
	}
);

// options.php normally requires manage_options; let Editors save this one group.
add_filter(
	'option_page_capability_aaykay_settings',
	function () {
		return AAYKAY_SETTINGS_CAP;
	}
);

function aaykay_sanitize_settings( $input ) {
	$input = is_array( $input ) ? wp_unslash( $input ) : array();
	$out   = array();
	foreach ( aaykay_settings_fields() as $key => $f ) {
		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';
		if ( 'bool' === $f[2] && ! isset( $input[ $key ] ) ) {
			$raw = '';
		}
		if ( ! is_string( $raw ) ) {
			$raw = '';
		}
		switch ( $f[2] ) {
			case 'email':
				$out[ $key ] = sanitize_email( $raw );
				if ( '' === $out[ $key ] ) {
					add_settings_error( AAYKAY_SETTINGS_OPTION, $key, '' === trim( $raw ) ? 'The email address can’t be empty, so the previous one was kept.' : 'The email address doesn’t look right, so the previous one was kept.' );
					$out[ $key ] = aaykay_setting( $key );
				}
				break;
			case 'emails':
				$list = array_filter( array_map( 'sanitize_email', explode( ',', $raw ) ) );
				if ( ! $list ) {
					add_settings_error( AAYKAY_SETTINGS_OPTION, $key, '' === trim( $raw ) ? 'Enquiries need an address to go to, so the previous one was kept.' : 'The enquiry address doesn’t look right, so the previous one was kept.' );
				}
				$out[ $key ] = $list ? implode( ', ', $list ) : aaykay_setting( $key );
				break;
			case 'url':
				$out[ $key ] = esc_url_raw( trim( $raw ) );
				if ( 'sheets_url' === $key && '' !== $out[ $key ] && 0 !== strpos( $out[ $key ], 'https://script.google.com/' ) ) {
					add_settings_error( AAYKAY_SETTINGS_OPTION, $key, 'The Google Sheets address must start with https://script.google.com/, so it was not saved.' );
					$out[ $key ] = '';
				}
				break;
			case 'doc':
				// A web address, or the launch file inside the theme (assets/docs/…).
				$raw = trim( $raw );
				if ( preg_match( '#^assets/docs/[\w.-]+\.pdf$#', $raw ) ) {
					$out[ $key ] = $raw;
				} else {
					$out[ $key ] = esc_url_raw( $raw, array( 'http', 'https' ) );
					if ( '' !== $raw && '' === $out[ $key ] ) {
						add_settings_error( AAYKAY_SETTINGS_OPTION, $key, 'The brochure address must start with https://, so it was not saved.' );
					}
				}
				break;
			case 'lines':
			case 'textarea':
				$out[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'bool':
				$out[ $key ] = ! empty( $raw ) ? '1' : '0';
				break;
			case 'count':
				$out[ $key ] = preg_replace( '/[^\d,+]/', '', (string) $raw );
				break;
			default:
				$out[ $key ] = sanitize_text_field( $raw );
				if ( 'sheets_secret' === $key && strlen( $out[ $key ] ) < 12 ) {
					$out[ $key ] = wp_generate_password( 24, false );
				}
				if ( 'phone' === $key && '' === $out[ $key ] ) {
					add_settings_error( AAYKAY_SETTINGS_OPTION, $key, 'The phone number can’t be empty, so the previous one was kept.' );
					$out[ $key ] = aaykay_setting( $key );
				}
		}
	}
	return $out;
}

function aaykay_render_settings_page() {
	if ( ! current_user_can( AAYKAY_SETTINGS_CAP ) ) {
		return;
	}
	$fields = aaykay_settings_fields();
	$name   = AAYKAY_SETTINGS_OPTION;
	?>
	<div class="wrap aaykay-admin">
		<h1>Site settings</h1>
		<p class="aaykay-intro">Facts that appear in more than one place on the website. Change them here and every place updates. <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">View the website</a></p>
		<?php settings_errors(); // All groups: includes WordPress's own "Settings saved." message. ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'aaykay_settings' ); ?>
			<?php foreach ( aaykay_settings_sections() as $section => $meta ) : ?>
				<h2 class="title"><?php echo esc_html( $meta[0] ); ?></h2>
				<?php if ( $meta[1] ) : ?>
					<p><?php echo esc_html( $meta[1] ); ?></p>
				<?php endif; ?>
				<table class="form-table" role="presentation">
					<?php
					foreach ( $fields as $key => $f ) :
						if ( $f[0] !== $section ) {
							continue;
						}
						$id    = 'aaykay-' . $key;
						$value = aaykay_setting( $key );
						$help  = $f[3] ? ' aria-describedby="' . esc_attr( $id ) . '-help"' : '';
						?>
						<tr>
							<th scope="row">
								<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $f[1] ); ?></label>
							</th>
							<td>
								<?php if ( 'bool' === $f[2] ) : ?>
									<label><input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( "{$name}[{$key}]" ); ?>" value="1"<?php checked( '1', (string) $value ); ?><?php echo $help; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>> Yes</label>
								<?php elseif ( 'lines' === $f[2] || 'textarea' === $f[2] ) : ?>
									<textarea class="large-text" rows="<?php echo 'lines' === $f[2] ? 8 : 3; ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( "{$name}[{$key}]" ); ?>"<?php echo $help; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>><?php echo esc_textarea( $value ); ?></textarea>
								<?php else : ?>
									<input
										type="<?php echo in_array( $f[2], array( 'email', 'url' ), true ) ? esc_attr( $f[2] ) : 'text'; ?>"
										class="<?php echo 'count' === $f[2] ? 'small-text' : 'regular-text'; ?>"
										<?php echo 'count' === $f[2] ? 'inputmode="numeric"' : ''; ?>
										id="<?php echo esc_attr( $id ); ?>"
										name="<?php echo esc_attr( "{$name}[{$key}]" ); ?>"
										value="<?php echo esc_attr( $value ); ?>"<?php echo $help; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>>
								<?php endif; ?>
								<?php if ( $f[3] ) : ?>
									<p class="description" id="<?php echo esc_attr( $id ); ?>-help"><?php echo esc_html( $f[3] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button( 'Save settings' ); ?>
		</form>
		<?php aaykay_render_sheets_test(); ?>
	</div>
	<?php
}
