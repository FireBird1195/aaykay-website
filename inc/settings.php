<?php
/**
 * Dashboard > Site settings.
 *
 * Facts that appear in several places on the page are entered once here and used
 * everywhere: the phone number and email (header menu, contact block, footer, structured
 * data), the branch states (hero figure, company facts, pre-qualification, contact, footer),
 * the turnover table (hero figure and pre-qualification chart) and the resource counts
 * (pre-qualification and quality & safety).
 *
 * Stored as one option, aaykay_settings. Editors can change it (capability
 * edit_others_posts), so AAYKAY staff do not need an Administrator account for this.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

const AAYKAY_SETTINGS_OPTION = 'aaykay_settings';
const AAYKAY_SETTINGS_CAP    = 'edit_others_posts';
const AAYKAY_TURNOVER_ROWS   = 6;

/** Field definitions: key => array( section, label, type, help ). Drives the form and sanitising. */
function aaykay_settings_fields() {
	return array(
		'phone'             => array( 'contact', 'Phone number', 'text', 'As it should be shown, with the country code, e.g. +91 40 6636 2094.' ),
		'email'             => array( 'contact', 'Email address', 'email', 'Shown on the website.' ),
		'enquiry_to'        => array( 'contact', 'Send enquiries to', 'emails', 'Where website enquiries are emailed. Separate several addresses with commas. Every enquiry is also saved under Enquiries.' ),
		'street'            => array( 'contact', 'Address: building and street', 'text', 'e.g. #3D, 3rd Floor, Dhruva Tara Apartments' ),
		'area'              => array( 'contact', 'Address: area', 'text', 'e.g. Somajiguda' ),
		'city'              => array( 'contact', 'Address: city', 'text', 'e.g. Hyderabad' ),
		'postcode'          => array( 'contact', 'Address: PIN code', 'text', 'e.g. 500 082' ),
		'state'             => array( 'contact', 'Address: state', 'text', 'e.g. Telangana' ),
		'maps_url'          => array( 'contact', 'Google Maps link (optional)', 'url', 'Leave empty to search Google Maps for the address above.' ),
		'clients_served'    => array( 'company', 'Clients served', 'text', 'e.g. 100+. Shown in the hero figures, above the client logos and in the company timeline.' ),
		'branches'          => array( 'company', 'States with branches', 'lines', 'One state per line. Put the head office state first. The number of lines is shown as "States with branches".' ),
		'turnover'          => array( 'turnover', 'Annual turnover', 'turnover', 'One row per financial year, in crore rupees (numbers only, e.g. 40.11). Rows can be in any order: the website shows them newest first, uses the newest year as the headline figure in the hero and pre-qualification sections, and works out the change against the year before. To add a year, use an empty row; when all rows are full, clear the oldest.' ),
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
		'seo_title'         => array( 'seo', 'Page title in search results', 'text', 'About 50–60 characters. Also the browser tab title.' ),
		'seo_description'   => array( 'seo', 'Description in search results', 'textarea', 'About 150–160 characters. Also used when the link is shared.' ),
	);
}

function aaykay_settings_sections() {
	return array(
		'contact'   => array( 'Contact details', 'Used in the menu, the contact section, the footer and for search engines.' ),
		'company'   => array( 'Company figures', '' ),
		'turnover'  => array( 'Turnover', '' ),
		'resources' => array( 'Resources', 'Head counts for the pre-qualification section. Leave a box empty to hide that line.' ),
		'seo'       => array( 'Search engines', '' ),
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
	$c['tel'] = aaykay_tel( $c['phone'] );
	if ( '' === $c['maps_url'] ) {
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

/** Turnover rows with a value: array( array( 'label' => 'FY 2025–26', 'value' => 40.11 ), ... ). */
function aaykay_turnover() {
	$rows = array();
	foreach ( (array) aaykay_setting( 'turnover' ) as $row ) {
		if ( ! is_array( $row ) || count( $row ) < 2 || '' === trim( (string) $row[0] ) || '' === trim( (string) $row[1] ) ) {
			continue;
		}
		$rows[] = array(
			'label' => trim( (string) $row[0] ),
			'value' => (float) $row[1],
		);
	}
	// Newest year first, whatever order the rows were typed in ("FY 2026–27" > "FY 2025–26").
	usort(
		$rows,
		function ( $a, $b ) {
			return strnatcasecmp( $b['label'], $a['label'] );
		}
	);
	return $rows;
}

/** "40.11" style display of a crore figure (no trailing zeros beyond two decimals). */
function aaykay_crore( $value ) {
	return rtrim( rtrim( number_format( (float) $value, 2, '.', ',' ), '0' ), '.' );
}

/** e.g. "FY 2025–26, up 7.5% on FY 2024–25" (empty with fewer than two years). */
function aaykay_turnover_change() {
	$rows = aaykay_turnover();
	if ( count( $rows ) < 2 || $rows[1]['value'] <= 0 ) {
		return isset( $rows[0] ) ? $rows[0]['label'] : '';
	}
	$pct  = ( $rows[0]['value'] - $rows[1]['value'] ) / $rows[1]['value'] * 100;
	$word = $pct >= 0 ? 'up' : 'down';
	$num  = rtrim( rtrim( number_format( abs( $pct ), 1 ), '0' ), '.' );
	return sprintf( '%s, %s %s%% on %s', $rows[0]['label'], $word, $num, $rows[1]['label'] );
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
		if ( 'turnover' !== $f[2] && ! is_string( $raw ) ) {
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
				$out[ $key ] = esc_url_raw( trim( (string) $raw ) );
				break;
			case 'lines':
			case 'textarea':
				$out[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'count':
				$out[ $key ] = preg_replace( '/[^\d,+]/', '', (string) $raw );
				break;
			case 'turnover':
				$raw  = is_array( $raw ) ? $raw : array();
				$rows = array();
				for ( $i = 0; $i < AAYKAY_TURNOVER_ROWS; $i++ ) {
					$label = isset( $raw[ $i ][0] ) ? sanitize_text_field( $raw[ $i ][0] ) : '';
					$value = isset( $raw[ $i ][1] ) ? preg_replace( '/[^\d.]/', '', (string) $raw[ $i ][1] ) : '';
					if ( '' !== $value && ! is_numeric( $value ) ) {
						add_settings_error( AAYKAY_SETTINGS_OPTION, 'turnover', 'A turnover value was not a number and was left out.' );
						$value = '';
					}
					if ( '' !== $label || '' !== $value ) {
						$rows[] = array( $label, $value );
					}
				}
				$out[ $key ] = $rows;
				break;
			default:
				$out[ $key ] = sanitize_text_field( $raw );
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
								<?php if ( 'turnover' === $f[2] ) : ?>
									<?php echo esc_html( $f[1] ); ?>
								<?php else : ?>
									<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $f[1] ); ?></label>
								<?php endif; ?>
							</th>
							<td>
								<?php if ( 'turnover' === $f[2] ) : ?>
									<table class="aaykay-turnover" id="<?php echo esc_attr( $id ); ?>"<?php echo $help; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>>
										<thead><tr><th scope="col">Financial year</th><th scope="col">₹ crore</th></tr></thead>
										<tbody>
										<?php
										$rows = array_values( (array) $value );
										for ( $i = 0; $i < AAYKAY_TURNOVER_ROWS; $i++ ) :
											$label = isset( $rows[ $i ][0] ) ? $rows[ $i ][0] : '';
											$num   = isset( $rows[ $i ][1] ) ? $rows[ $i ][1] : '';
											?>
											<tr>
												<td><input type="text" class="regular-text" name="<?php echo esc_attr( "{$name}[{$key}][{$i}][0]" ); ?>" value="<?php echo esc_attr( $label ); ?>" placeholder="FY 2026–27" aria-label="<?php echo esc_attr( 'Year, row ' . ( $i + 1 ) ); ?>"></td>
												<td><input type="text" inputmode="decimal" class="small-text" name="<?php echo esc_attr( "{$name}[{$key}][{$i}][1]" ); ?>" value="<?php echo esc_attr( $num ); ?>" placeholder="0.00" aria-label="<?php echo esc_attr( 'Turnover in crore, row ' . ( $i + 1 ) ); ?>"></td>
											</tr>
										<?php endfor; ?>
										</tbody>
									</table>
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
	</div>
	<?php
}
