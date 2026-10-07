<?php
/**
 * Edit screens: the fields on Projects, Sectors, Client logos, Architects & consultants
 * and Enquiries, their list columns, the dashboard welcome box and a tidier admin menu.
 * Loaded in the dashboard only.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ admin look */

add_action(
	'admin_head',
	function () {
		echo '<style>
.aaykay-fields{display:grid;gap:14px;max-width:760px}
.aaykay-fields label{display:block;font-weight:600;margin-bottom:4px}
.aaykay-fields input[type=text],.aaykay-fields textarea{width:100%}
.aaykay-fields .description{margin:4px 0 0}
.aaykay-fields fieldset{border:1px solid #dcdcde;padding:12px 16px 16px;border-radius:4px}
.aaykay-fields legend{font-weight:600;padding:0 6px}
.aaykay-check{display:flex;gap:8px;align-items:flex-start;font-weight:600}
.aaykay-sector-choice label{display:block;margin:0 0 6px}
.aaykay-logo-preview{background:#fff;border:1px solid #dcdcde;padding:16px;display:inline-flex;min-width:160px;min-height:60px;align-items:center;justify-content:center}
.aaykay-logo-preview img{max-height:48px;max-width:220px;width:auto;height:auto;filter:grayscale(1)}
.column-aaykay_logo img{max-height:28px;max-width:110px;width:auto;height:auto;filter:grayscale(1)}
.aaykay-icon{width:24px;height:24px;stroke:#1B3F8F;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle}
.aaykay-enquiry th{width:140px;text-align:left;vertical-align:top;padding:6px 12px 6px 0}
.aaykay-enquiry td{padding:6px 0;white-space:pre-wrap}
.aaykay-turnover th{text-align:left;padding:0 8px 4px 0;font-weight:400}
.aaykay-turnover td{padding:0 8px 6px 0}
.aaykay-welcome ul{margin:8px 0 0}
.aaykay-welcome li{margin:0 0 6px}
</style>';
	}
);

/* ------------------------------------------------------------------ menu and dashboard */

// Posts and comments are not used by this website; hiding them keeps the menu simple.
add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit.php' );
		remove_menu_page( 'edit-comments.php' );
	},
	999
);

add_action(
	'wp_dashboard_setup',
	function () {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		wp_add_dashboard_widget( 'aaykay_welcome', 'AAYKAY website', 'aaykay_render_welcome_widget' );
		// Put it first.
		global $wp_meta_boxes;
		$normal = $wp_meta_boxes['dashboard']['normal']['core'];
		$mine   = array( 'aaykay_welcome' => $normal['aaykay_welcome'] );
		unset( $normal['aaykay_welcome'] );
		$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $mine, $normal ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- documented way to order dashboard widgets.
	}
);
// The generic "Welcome to WordPress" panel (added after this file loads, so removed later).
add_action(
	'load-index.php',
	function () {
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}
);

function aaykay_render_welcome_widget() {
	$enquiries = wp_count_posts( 'aaykay_enquiry' );
	$new       = isset( $enquiries->private ) ? (int) $enquiries->private : 0;
	?>
	<div class="aaykay-welcome">
		<p>Everything on the website that changes is edited from the menu on the left.</p>
		<ul>
			<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=aaykay_project' ) ); ?>"><strong>Add a project</strong></a> or <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aaykay_project' ) ); ?>">edit projects</a></li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aaykay_client' ) ); ?>">Client logos</a> · <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aaykay_firm' ) ); ?>">Architects &amp; consultants</a></li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=aaykay-settings' ) ); ?>">Site settings</a>: phone, email, address, turnover, staff numbers</li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aaykay_enquiry' ) ); ?>">Enquiries</a> (<?php echo (int) $new; ?> received)</li>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">View the website</a></li>
		</ul>
	</div>
	<?php
}

/** Plain "saved" messages (the defaults talk about posts and offer links to pages that don't exist). */
add_filter(
	'post_updated_messages',
	function ( $messages ) {
		$view = ' <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">View the website</a>';
		foreach ( array(
			'aaykay_project' => 'Project',
			'aaykay_client'  => 'Client',
			'aaykay_firm'    => 'Firm',
		) as $type => $noun ) {
			$messages[ $type ] = array(
				0  => '',
				1  => $noun . ' updated.' . $view,
				4  => $noun . ' updated.' . $view,
				6  => $noun . ' published. It is now on the website.' . $view,
				7  => $noun . ' saved.',
				8  => $noun . ' submitted.',
				10 => $noun . ' draft updated. Drafts are not shown on the website until published.',
			);
		}
		return $messages;
	}
);

/* ------------------------------------------------------------------ projects */

add_action(
	'add_meta_boxes_aaykay_project',
	function () {
		add_meta_box( 'aaykay_project_details', 'Project details', 'aaykay_render_project_box', 'aaykay_project', 'normal', 'high' );
	}
);

function aaykay_render_project_box( $post ) {
	wp_nonce_field( 'aaykay_project_save', 'aaykay_project_nonce' );
	$fields = aaykay_project_fields();
	$record = array( 'location', 'area_sqft', 'floors', 'order_value', 'design_team', 'status', 'note', 'source' );
	echo '<div class="aaykay-fields">';
	echo '<p>The project name is the title above. These details fill its row in the <strong>Project record</strong> table. Leave anything you don’t know empty; never estimate.</p>';
	foreach ( $record as $key ) {
		aaykay_render_field( $post->ID, $key, $fields[ $key ] );
	}
	echo '<fieldset><legend>Homepage card (optional)</legend>';
	echo '<p class="description">Up to five or six projects work best as large cards under “Selected work”. The first card is shown wide, the second medium, the rest in a row of three. Set the order with the <strong>Card order</strong> box on the right (1 = first).</p>';
	foreach ( array( 'featured', 'card_label', 'card_details', 'card_note' ) as $key ) {
		aaykay_render_field( $post->ID, $key, $fields[ $key ] );
	}
	echo '</fieldset></div>';
}

/** One labelled field for the project box. */
function aaykay_render_field( $post_id, $key, $f ) {
	$id    = 'aaykay-' . $key;
	$name  = 'aaykay[' . $key . ']';
	$value = aaykay_project_meta( $post_id, $key );
	$help  = $f[2] ? '<p class="description" id="' . esc_attr( $id ) . '-help">' . esc_html( $f[2] ) . '</p>' : '';
	$desc  = $f[2] ? ' aria-describedby="' . esc_attr( $id ) . '-help"' : '';
	echo '<div>';
	if ( 'bool' === $f[1] ) {
		echo '<label class="aaykay-check" for="' . esc_attr( $id ) . '"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . checked( '1', $value, false ) . $desc . '> ' . esc_html( $f[0] ) . '</label>' . $help; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
	} elseif ( 'lines' === $f[1] ) {
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label><textarea rows="3" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $desc . '>' . esc_textarea( $value ) . '</textarea>' . $help; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
	} else {
		$extra = 'int' === $f[1] ? ' inputmode="numeric" pattern="[0-9,]*"' : '';
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label><input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . $extra . $desc . '>' . $help; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
	}
	echo '</div>';
}

/** Sector chooser: one sector per project, as radio buttons. */
function aaykay_sector_metabox( $post ) {
	$terms   = get_terms(
		array(
			'taxonomy'   => 'aaykay_sector',
			'hide_empty' => false,
		)
	);
	$current = wp_get_object_terms( $post->ID, 'aaykay_sector', array( 'fields' => 'ids' ) );
	$current = is_wp_error( $current ) || ! $current ? 0 : (int) $current[0];
	echo '<div class="aaykay-sector-choice" role="radiogroup" aria-label="Sector">';
	if ( ! $terms || is_wp_error( $terms ) ) {
		echo '<p>No sectors yet. <a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=aaykay_sector&post_type=aaykay_project' ) ) . '">Add one</a>.</p>';
	} else {
		foreach ( $terms as $t ) {
			echo '<label><input type="radio" name="aaykay_sector" value="' . (int) $t->term_id . '"' . checked( $current, $t->term_id, false ) . '> ' . esc_html( $t->name ) . '</label>';
		}
	}
	echo '</div><p class="description">Required. A project without a sector is not shown.</p>';
}

add_action(
	'save_post_aaykay_project',
	function ( $post_id ) {
		if ( ! isset( $_POST['aaykay_project_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aaykay_project_nonce'] ) ), 'aaykay_project_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$input = isset( $_POST['aaykay'] ) && is_array( $_POST['aaykay'] ) ? wp_unslash( $_POST['aaykay'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field below.
		foreach ( aaykay_project_fields() as $key => $f ) {
			$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';
			switch ( $f[1] ) {
				case 'bool':
					$value = '1' === $raw ? '1' : '';
					break;
				case 'int':
					$digits = preg_replace( '/\D/', '', (string) $raw );
					$value  = '' !== $digits && (int) $digits > 0 ? (string) (int) $digits : '';
					break;
				case 'lines':
					$value = sanitize_textarea_field( $raw );
					break;
				default:
					$value = sanitize_text_field( $raw );
			}
			if ( '' === $value ) {
				delete_post_meta( $post_id, '_aaykay_' . $key );
			} else {
				update_post_meta( $post_id, '_aaykay_' . $key, $value );
			}
		}
		$sector = isset( $_POST['aaykay_sector'] ) ? absint( $_POST['aaykay_sector'] ) : 0;
		if ( $sector && term_exists( $sector, 'aaykay_sector' ) ) {
			wp_set_object_terms( $post_id, array( $sector ), 'aaykay_sector' );
		}
	}
);

/** Warnings on the project screen for things that would stop it showing as intended. */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'aaykay_project' !== $screen->id || empty( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
			return;
		}
		$post_id  = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
		$problems = array();
		if ( ! has_term( '', 'aaykay_sector', $post_id ) ) {
			$problems[] = 'Choose a <strong>Sector</strong> (box on the right). Until then this project is not shown on the website.';
		}
		if ( '1' === aaykay_project_meta( $post_id, 'featured' ) ) {
			$thumb = (int) get_post_thumbnail_id( $post_id );
			if ( ! $thumb ) {
				$problems[] = 'This project is marked for a homepage card but has no <strong>Card photo</strong> (box on the right), so the card is not shown.';
			} elseif ( '' === trim( (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) ) ) {
				$problems[] = 'The card photo has no <strong>Alternative text</strong>. Click the photo, then describe it in one sentence (e.g. “Yashoda Hospitals building in Somajiguda, Hyderabad.”). It is read aloud to blind visitors and used by search engines.';
			}
		}
		foreach ( $problems as $p ) {
			echo '<div class="notice notice-warning"><p>' . wp_kses( $p, array( 'strong' => array() ) ) . '</p></div>';
		}
	}
);

add_filter(
	'manage_aaykay_project_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['aaykay_location'] = 'Location';
				$new['aaykay_value']    = 'Order value';
			}
		}
		$new['aaykay_card'] = 'Homepage card';
		unset( $new['date'] );
		return $new;
	}
);

add_action(
	'manage_aaykay_project_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'aaykay_location' === $col ) {
			echo esc_html( aaykay_project_meta( $post_id, 'location' ) );
		} elseif ( 'aaykay_value' === $col ) {
			echo esc_html( aaykay_project_meta( $post_id, 'order_value' ) );
		} elseif ( 'aaykay_card' === $col && '1' === aaykay_project_meta( $post_id, 'featured' ) ) {
			echo esc_html( 'Yes, position ' . get_post_field( 'menu_order', $post_id ) );
		}
	},
	10,
	2
);

// Lists open sorted by name rather than by date.
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! $query->is_main_query() || $query->get( 'orderby' ) ) {
			return;
		}
		$type = $query->get( 'post_type' );
		if ( in_array( $type, array( 'aaykay_project', 'aaykay_firm' ), true ) ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		} elseif ( 'aaykay_client' === $type ) {
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		}
	}
);

/* ------------------------------------------------------------------ sectors */

/** Extra sector fields. On "Add sector" WordPress passes the taxonomy name; on edit, the term. */
function aaykay_render_sector_inputs( $term = null ) {
	$term    = $term instanceof WP_Term ? $term : null;
	$is_edit = (bool) $term;
	wp_nonce_field( 'aaykay_sector_save', 'aaykay_sector_nonce' );
	foreach ( aaykay_sector_fields() as $key => $f ) {
		$id    = 'aaykay-sector-' . $key;
		$value = $term ? aaykay_sector_meta( $term->term_id, $key ) : '';
		if ( 'icon' === $f[1] ) {
			$input = '<select id="' . esc_attr( $id ) . '" name="aaykay_sector_meta[icon]">';
			foreach ( aaykay_icon_ids() as $icon ) {
				$input .= '<option value="' . esc_attr( $icon ) . '"' . selected( $value, $icon, false ) . '>' . esc_html( $icon ) . '</option>';
			}
			$input .= '</select>';
			if ( $value ) {
				$input .= ' <svg class="aaykay-icon" aria-hidden="true"><use href="' . esc_url( aaykay_asset( 'assets/icons/icons.svg' ) ) . '#' . esc_attr( $value ) . '"/></svg>';
			}
		} else {
			$input = '<input type="text" id="' . esc_attr( $id ) . '" name="aaykay_sector_meta[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '"' . ( 'int' === $f[1] ? ' class="small-text" inputmode="numeric"' : '' ) . '>';
		}
		$help = '<p class="description">' . esc_html( $f[2] ) . '</p>';
		if ( $is_edit ) {
			echo '<tr class="form-field"><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label></th><td>' . $input . $help . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
		} else {
			echo '<div class="form-field"><label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label>' . $input . $help . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
		}
	}
}

add_action( 'aaykay_sector_add_form_fields', 'aaykay_render_sector_inputs' );
add_action( 'aaykay_sector_edit_form_fields', 'aaykay_render_sector_inputs' );

function aaykay_save_sector( $term_id ) {
	if ( ! isset( $_POST['aaykay_sector_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aaykay_sector_nonce'] ) ), 'aaykay_sector_save' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$input = isset( $_POST['aaykay_sector_meta'] ) && is_array( $_POST['aaykay_sector_meta'] ) ? wp_unslash( $_POST['aaykay_sector_meta'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field below.
	foreach ( aaykay_sector_fields() as $key => $f ) {
		$raw = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
		if ( 'order' === $key && '' === trim( $raw ) ) {
			// No order given: put the sector after the existing ones.
			$max = 0;
			foreach ( get_terms( array( 'taxonomy' => 'aaykay_sector', 'hide_empty' => false, 'exclude' => array( $term_id ) ) ) as $t ) {
				$max = max( $max, (int) aaykay_sector_meta( $t->term_id, 'order' ) );
			}
			$value = (string) ( $max + 10 );
		} elseif ( 'int' === $f[1] ) {
			$value = (string) (int) $raw;
		} elseif ( 'icon' === $f[1] ) {
			$value = in_array( $raw, aaykay_icon_ids(), true ) ? $raw : 'building-2';
		} else {
			$value = sanitize_text_field( $raw );
		}
		update_term_meta( $term_id, 'aaykay_' . $key, $value );
	}
}
add_action( 'created_aaykay_sector', 'aaykay_save_sector' );
add_action( 'edited_aaykay_sector', 'aaykay_save_sector' );

add_filter(
	'manage_edit-aaykay_sector_columns',
	function ( $cols ) {
		unset( $cols['description'], $cols['slug'] );
		$cols['aaykay_order'] = 'Order';
		return $cols;
	}
);
add_filter(
	'manage_aaykay_sector_custom_column',
	function ( $out, $col, $term_id ) {
		return 'aaykay_order' === $col ? esc_html( aaykay_sector_meta( $term_id, 'order' ) ) : $out;
	},
	10,
	3
);
// The core "Description" box on the sector form is replaced by ours; hide it.
add_action(
	'admin_head-edit-tags.php',
	function () {
		if ( isset( $_GET['taxonomy'] ) && 'aaykay_sector' === $_GET['taxonomy'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
			echo '<style>.term-description-wrap,.term-slug-wrap{display:none}</style>';
		}
	}
);
add_action(
	'admin_head-term.php',
	function () {
		if ( isset( $_GET['taxonomy'] ) && 'aaykay_sector' === $_GET['taxonomy'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
			echo '<style>.term-description-wrap{display:none}</style>';
		}
	}
);

/* ------------------------------------------------------------------ client logos and firms */

add_action(
	'add_meta_boxes',
	function ( $post_type ) {
		if ( 'aaykay_client' === $post_type ) {
			add_meta_box( 'aaykay_logo_help', 'How the logo is shown', 'aaykay_render_logo_box', 'aaykay_client', 'normal', 'high' );
		} elseif ( 'aaykay_firm' === $post_type ) {
			add_meta_box( 'aaykay_logo_help', 'How this firm is shown', 'aaykay_render_logo_box', 'aaykay_firm', 'normal', 'high' );
		}
	}
);

function aaykay_render_logo_box( $post ) {
	wp_nonce_field( 'aaykay_logo_save', 'aaykay_logo_nonce' );
	$logo    = aaykay_logo( $post->ID );
	$bundled = (string) get_post_meta( $post->ID, '_aaykay_bundled_logo', true );
	$is_firm = 'aaykay_firm' === $post->post_type;
	echo '<div class="aaykay-fields">';
	echo '<p>The name is the title above. To show a logo, use <strong>Set logo</strong> in the box on the right and upload a <strong>PNG or WebP</strong> file, at least 400 pixels wide, ideally on a transparent background. Any colour is fine: the website shows every logo in grey.</p>';
	echo '<div class="aaykay-logo-preview">';
	if ( $logo ) {
		echo '<img src="' . esc_url( $logo[0] ) . '" alt="">';
	} else {
		echo $is_firm ? '<strong>' . esc_html( aaykay_monogram( $post->post_title ) ?: 'Letters' ) . '</strong>' : '<strong>' . esc_html( $post->post_title ? $post->post_title : 'Name' ) . '</strong>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped.
	}
	echo '</div>';
	echo '<p class="description">' . ( $logo ? 'This is the logo shown on the website.' : ( $is_firm ? 'No logo yet: the website shows these letters on a grey plate beside the name.' : 'No logo yet: the website shows the name in the same space.' ) ) . '</p>';
	if ( $bundled && ! has_post_thumbnail( $post ) ) {
		echo '<p class="description">This logo is built into the website theme (' . esc_html( basename( $bundled ) ) . '). An uploaded logo replaces it.</p>';
		echo '<label class="aaykay-check"><input type="checkbox" name="aaykay_remove_bundled" value="1"> Stop showing the built-in logo</label>';
	}
	if ( $is_firm ) {
		$mark = (string) get_post_meta( $post->ID, '_aaykay_mark', true );
		echo '<div><label for="aaykay-mark">Letters (optional)</label><input type="text" class="small-text" maxlength="5" id="aaykay-mark" name="aaykay_mark" value="' . esc_attr( $mark ) . '" aria-describedby="aaykay-mark-help"><p class="description" id="aaykay-mark-help">Shown when there is no logo. Leave empty to use the initials of the name.</p></div>';
	} else {
		echo '<p class="description">Clients are shown in the order of the <strong>Display order</strong> box on the right (lower numbers first).</p>';
	}
	echo '</div>';
}

function aaykay_save_logo_box( $post_id ) {
	if ( ! isset( $_POST['aaykay_logo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aaykay_logo_nonce'] ) ), 'aaykay_logo_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! empty( $_POST['aaykay_remove_bundled'] ) ) {
		delete_post_meta( $post_id, '_aaykay_bundled_logo' );
	}
	if ( isset( $_POST['aaykay_mark'] ) ) {
		$mark = mb_substr( sanitize_text_field( wp_unslash( $_POST['aaykay_mark'] ) ), 0, 5 );
		if ( '' === $mark ) {
			delete_post_meta( $post_id, '_aaykay_mark' );
		} else {
			update_post_meta( $post_id, '_aaykay_mark', $mark );
		}
	}
}
add_action( 'save_post_aaykay_client', 'aaykay_save_logo_box' );
add_action( 'save_post_aaykay_firm', 'aaykay_save_logo_box' );

foreach ( array( 'aaykay_client', 'aaykay_firm' ) as $aaykay_type ) {
	add_filter(
		"manage_{$aaykay_type}_posts_columns",
		function ( $cols ) use ( $aaykay_type ) {
			$new = array(
				'cb'          => $cols['cb'],
				'aaykay_logo' => 'Logo',
				'title'       => $cols['title'],
			);
			if ( 'aaykay_client' === $aaykay_type ) {
				$new['aaykay_order'] = 'Order';
			}
			return $new;
		}
	);
	add_action(
		"manage_{$aaykay_type}_posts_custom_column",
		function ( $col, $post_id ) {
			if ( 'aaykay_logo' === $col ) {
				$logo = aaykay_logo( $post_id );
				echo $logo ? '<img src="' . esc_url( $logo[0] ) . '" alt="">' : '<span aria-hidden="true">—</span><span class="screen-reader-text">No logo</span>';
			} elseif ( 'aaykay_order' === $col ) {
				echo (int) get_post_field( 'menu_order', $post_id );
			}
		},
		10,
		2
	);
}

/* ------------------------------------------------------------------ enquiries */

add_action(
	'add_meta_boxes_aaykay_enquiry',
	function () {
		remove_meta_box( 'submitdiv', 'aaykay_enquiry', 'side' );
		remove_meta_box( 'slugdiv', 'aaykay_enquiry', 'normal' );
		add_meta_box( 'aaykay_enquiry_details', 'Enquiry', 'aaykay_render_enquiry_box', 'aaykay_enquiry', 'normal', 'high' );
	}
);

// An enquiry is a record of what was sent: it can be read and deleted, not edited.
add_action(
	'admin_head-post.php',
	function () {
		if ( 'aaykay_enquiry' === get_post_type() ) {
			echo '<style>#titlediv{display:none}</style>';
		}
	}
);

function aaykay_render_enquiry_box( $post ) {
	$mailed = get_post_meta( $post->ID, '_aaykay_mailed', true );
	echo '<table class="aaykay-enquiry"><tbody>';
	echo '<tr><th scope="row">Received</th><td>' . esc_html( get_the_date( 'j F Y, g:i a', $post ) ) . '</td></tr>';
	foreach ( aaykay_enquiry_fields() as $key => $f ) {
		$value = (string) get_post_meta( $post->ID, '_aaykay_' . $key, true );
		if ( 'email' === $key && $value ) {
			$value_html = '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
		} elseif ( 'phone' === $key && $value ) {
			$value_html = '<a href="tel:' . esc_attr( aaykay_tel( $value ) ) . '">' . esc_html( $value ) . '</a>';
		} else {
			$value_html = esc_html( $value );
		}
		echo '<tr><th scope="row">' . esc_html( $f[0] ) . '</th><td>' . $value_html . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	}
	$mail_note = array(
		'1'    => 'Sent.',
		'held' => 'Not emailed: many enquiries came from the same connection within an hour, so this one was only saved here. Check that it is genuine before replying.',
	);
	echo '<tr><th scope="row">Email notification</th><td>' . esc_html( isset( $mail_note[ $mailed ] ) ? $mail_note[ $mailed ] : 'Could not be sent. Reply to this enquiry directly, and ask your developer to check the email settings.' ) . '</td></tr>';
	echo '</tbody></table>';
	echo '<p><a class="button button-primary" href="' . esc_url( 'mailto:' . get_post_meta( $post->ID, '_aaykay_email', true ) . '?subject=' . rawurlencode( 'Re: your enquiry to AAYKAY Electricals' ) ) . '">Reply by email</a> <a class="button" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">Move to bin</a> <a class="button-link" href="' . esc_url( admin_url( 'edit.php?post_type=aaykay_enquiry' ) ) . '">Back to all enquiries</a></p>';
}

add_filter(
	'manage_aaykay_enquiry_posts_columns',
	function ( $cols ) {
		return array(
			'cb'             => $cols['cb'],
			'title'          => 'Company — name',
			'aaykay_email'   => 'Email',
			'aaykay_phone'   => 'Phone',
			'aaykay_type'    => 'Project type',
			'aaykay_city'    => 'City',
			'aaykay_mailed'  => 'Emailed',
			'date'           => 'Received',
		);
	}
);
add_action(
	'manage_aaykay_enquiry_posts_custom_column',
	function ( $col, $post_id ) {
		$key = substr( $col, 7 );
		if ( 'mailed' === $key ) {
			$mailed = get_post_meta( $post_id, '_aaykay_mailed', true );
			echo '1' === $mailed ? 'Yes' : ( 'held' === $mailed ? '<strong>Held</strong>' : '<strong>No</strong>' );
		} elseif ( in_array( $key, array( 'email', 'phone', 'type', 'city' ), true ) ) {
			echo esc_html( (string) get_post_meta( $post_id, '_aaykay_' . $key, true ) );
		}
	},
	10,
	2
);
// Enquiries are saved as "private"; WordPress would label each one "Private" in the list.
add_filter(
	'display_post_states',
	function ( $states, $post ) {
		if ( 'aaykay_enquiry' === $post->post_type ) {
			unset( $states['private'] );
		}
		return $states;
	},
	10,
	2
);
add_filter(
	'private_title_format',
	function ( $format, $post ) {
		return ( $post && 'aaykay_enquiry' === get_post_type( $post ) ) ? '%s' : $format;
	},
	10,
	2
);

// Enquiries are records: no Quick Edit or bulk editing, only reading and deleting.
add_filter(
	'post_row_actions',
	function ( $actions, $post ) {
		if ( 'aaykay_enquiry' === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'], $actions['edit'] );
			$actions = array( 'view_enquiry' => '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">Read</a>' ) + $actions;
		}
		return $actions;
	},
	10,
	2
);
add_filter(
	'bulk_actions-edit-aaykay_enquiry',
	function ( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}
);

/** On the Projects list: say if published projects are hidden because they have no sector. */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-aaykay_project' !== $screen->id ) {
			return;
		}
		$orphans = get_posts(
			array(
				'post_type'   => 'aaykay_project',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
				'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small admin-only query.
					array(
						'taxonomy' => 'aaykay_sector',
						'operator' => 'NOT EXISTS',
					),
				),
			)
		);
		if ( $orphans ) {
			$names = array_map( 'get_the_title', array_slice( $orphans, 0, 5 ) );
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html(
					sprintf(
						'%d published project%s %s not on the website because %s no sector: %s%s. Open each one and choose a sector.',
						count( $orphans ),
						1 === count( $orphans ) ? '' : 's',
						1 === count( $orphans ) ? 'is' : 'are',
						1 === count( $orphans ) ? 'it has' : 'they have',
						implode( ', ', $names ),
						count( $orphans ) > 5 ? '…' : ''
					)
				)
			);
		}
	}
);
