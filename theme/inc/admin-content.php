<?php
/**
 * Dashboard > Homepage content: the editor for aaykay_content (schema in inc/content.php).
 *
 * One form, one tab per section. Lists can be added to, removed from and reordered
 * (assets/admin/content-editor.js); photos are picked from the Media Library; icons from
 * the sprite. Every save keeps the previous version, and "Undo last save" puts it back.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

const AAYKAY_CONTENT_CAP = 'edit_others_posts'; // Editors and Administrators.

add_action(
	'admin_menu',
	function () {
		add_menu_page( 'Homepage content', 'Homepage content', AAYKAY_CONTENT_CAP, 'aaykay-content', 'aaykay_render_content_page', 'dashicons-edit-page', 23 );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'aaykay_content',
			AAYKAY_CONTENT_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'aaykay_sanitize_content',
				'default'           => array(),
			)
		);
	}
);

add_filter(
	'option_page_capability_aaykay_content',
	function () {
		return AAYKAY_CONTENT_CAP;
	}
);

// Keep the version before each save, for "Undo last save".
add_action(
	'update_option_' . AAYKAY_CONTENT_OPTION,
	function ( $old ) {
		update_option( 'aaykay_content_previous', $old, false );
	}
);
add_action(
	'add_option_' . AAYKAY_CONTENT_OPTION,
	function () {
		update_option( 'aaykay_content_previous', aaykay_content_defaults(), false );
	}
);
add_action( 'update_option_' . AAYKAY_CONTENT_OPTION, 'aaykay_purge_page_cache' );
add_action( 'add_option_' . AAYKAY_CONTENT_OPTION, 'aaykay_purge_page_cache' );

add_action(
	'admin_post_aaykay_content_undo',
	function () {
		if ( ! current_user_can( AAYKAY_CONTENT_CAP ) || ! check_admin_referer( 'aaykay_content_undo' ) ) {
			wp_die( 'Not allowed.' );
		}
		$previous = get_option( 'aaykay_content_previous', null );
		if ( is_array( $previous ) ) {
			$current = get_option( AAYKAY_CONTENT_OPTION, array() );
			remove_all_actions( 'update_option_' . AAYKAY_CONTENT_OPTION );
			update_option( AAYKAY_CONTENT_OPTION, $previous );
			update_option( 'aaykay_content_previous', $current, false );
			aaykay_purge_page_cache();
		}
		wp_safe_redirect( admin_url( 'admin.php?page=aaykay-content&undone=1' ) );
		exit;
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'toplevel_page_aaykay-content' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'aaykay-content-editor', aaykay_asset( 'assets/admin/content-editor.css' ), array(), aaykay_asset_version( 'assets/admin/content-editor.css' ) );
		wp_enqueue_script( 'aaykay-content-editor', aaykay_asset( 'assets/admin/content-editor.js' ), array( 'jquery' ), aaykay_asset_version( 'assets/admin/content-editor.js' ), true );
		wp_localize_script( 'aaykay-content-editor', 'AAYKAY_EDITOR', array( 'sprite' => aaykay_asset( 'assets/icons/icons.svg' ) ) );
	}
);

function aaykay_render_content_page() {
	if ( ! current_user_can( AAYKAY_CONTENT_CAP ) ) {
		return;
	}
	$schema   = aaykay_content_schema();
	$previous = get_option( 'aaykay_content_previous', null );
	?>
	<div class="wrap aaykay-editor">
		<h1>Homepage content</h1>
		<p class="aaykay-intro">Every heading, text, photo and icon on the home page, section by section, in the order they appear. Change what you need and click <strong>Save changes</strong>. <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">View the website</a></p>
		<?php settings_errors(); ?>
		<?php if ( isset( $_GET['undone'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only. ?>
			<div class="notice notice-success is-dismissible"><p>The last save was undone. The page shows the previous version again.</p></div>
		<?php endif; ?>
		<div class="ak-layout">
			<nav class="ak-tabs" aria-label="Sections">
				<?php foreach ( $schema as $sid => $section ) : ?>
					<a href="#ak-<?php echo esc_attr( $sid ); ?>" data-tab="<?php echo esc_attr( $sid ); ?>"><?php echo esc_html( $section['title'] ); ?></a>
				<?php endforeach; ?>
				<?php if ( is_array( $previous ) ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ak-undo">
						<input type="hidden" name="action" value="aaykay_content_undo">
						<?php wp_nonce_field( 'aaykay_content_undo' ); ?>
						<button type="submit" class="button-link">Undo last save</button>
					</form>
				<?php endif; ?>
			</nav>
			<form method="post" action="options.php" class="ak-form">
				<?php settings_fields( 'aaykay_content' ); ?>
				<?php foreach ( $schema as $sid => $section ) : ?>
					<section class="ak-section" id="ak-<?php echo esc_attr( $sid ); ?>" data-section="<?php echo esc_attr( $sid ); ?>">
						<h2><?php echo esc_html( $section['title'] ); ?></h2>
						<p class="description"><?php echo esc_html( $section['desc'] ); ?></p>
						<?php if ( ! empty( $section['toggle'] ) ) : ?>
							<p class="ak-toggle"><label><input type="checkbox" name="<?php echo esc_attr( AAYKAY_CONTENT_OPTION . "[{$sid}][enabled]" ); ?>" value="1"<?php checked( aaykay_section_on( $sid ) ); ?>> Show this section on the website</label></p>
						<?php endif; ?>
						<?php
						foreach ( $section['fields'] as $key => $field ) {
							aaykay_editor_field( AAYKAY_CONTENT_OPTION . "[{$sid}][{$key}]", "ak-{$sid}-{$key}", $field, aaykay_c( $sid, $key ) );
						}
						?>
					</section>
				<?php endforeach; ?>
				<div class="ak-save"><?php submit_button( 'Save changes', 'primary', 'submit', false ); ?> <span class="description">Saves every section. You can undo the last save.</span></div>
			</form>
		</div>
	</div>
	<?php
}

/** One field in the editor. $name is the form name, $id a unique id. */
function aaykay_editor_field( $name, $id, $field, $value, $in_list = false ) {
	list( $type, $label ) = $field;
	$help                 = isset( $field[2] ) ? $field[2] : '';
	$describe             = $help ? ' aria-describedby="' . esc_attr( $id ) . '-help"' : '';
	$help_html            = $help ? '<p class="description" id="' . esc_attr( $id ) . '-help">' . esc_html( $help ) . '</p>' : '';
	echo '<div class="ak-field ak-field--' . esc_attr( $type ) . '">';
	switch ( $type ) {
		case 'bool':
			echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( ! empty( $value ), true, false ) . $describe . '> ' . esc_html( $label ) . '</label>' . $help_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped parts.
			break;
		case 'text':
			echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label><input type="text" class="large-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '"' . $describe . '>' . $help_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped parts.
			break;
		case 'textarea':
		case 'lines':
			$rows = 'lines' === $type ? max( 3, min( 12, substr_count( (string) $value, "\n" ) + 2 ) ) : max( 2, min( 10, (int) ceil( strlen( (string) $value ) / 90 ) + 1 ) );
			echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label><textarea class="large-text" rows="' . (int) $rows . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $describe . '>' . esc_textarea( (string) $value ) . '</textarea>' . $help_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped parts.
			break;
		case 'icon':
			$value = in_array( $value, aaykay_icon_ids(), true ) ? $value : 'gauge';
			echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label><span class="ak-icon-pick"><svg class="ak-icon" aria-hidden="true"><use href="' . esc_url( aaykay_asset( 'assets/icons/icons.svg' ) ) . '#' . esc_attr( $value ) . '"/></svg><select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( aaykay_icon_ids() as $icon ) {
				echo '<option value="' . esc_attr( $icon ) . '"' . selected( $value, $icon, false ) . '>' . esc_html( str_replace( '-', ' ', $icon ) ) . '</option>';
			}
			echo '</select></span>' . $help_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped parts.
			break;
		case 'image':
			aaykay_editor_image( $name, $id, $label, $value, $help_html );
			break;
		case 'list':
			aaykay_editor_list( $name, $id, $field, $value, $help_html );
			break;
	}
	echo '</div>';
}

function aaykay_editor_image( $name, $id, $label, $value, $help_html ) {
	$value   = is_array( $value ) ? $value : array();
	$data    = aaykay_image_data( $value );
	$upload  = ! empty( $value['id'] ) ? (int) $value['id'] : 0;
	$default = ! empty( $value['src'] ) ? $value : ( isset( $value['default'] ) ? $value['default'] : null );
	$alt     = $data ? $data['alt'] : '';
	echo '<fieldset class="ak-image" data-has-default="' . ( $default ? '1' : '0' ) . '"><legend>' . esc_html( $label ) . '</legend>';
	echo '<div class="ak-image-preview">' . ( $data ? '<img src="' . esc_url( $data['url'] ) . '" alt="">' : '<span>No photo</span>' ) . '</div>';
	echo '<div class="ak-image-controls">';
	echo '<input type="hidden" class="ak-image-id" name="' . esc_attr( $name ) . '[id]" value="' . (int) $upload . '">';
	echo '<input type="hidden" class="ak-image-remove" name="' . esc_attr( $name ) . '[remove]" value="">';
	if ( $default && ! empty( $default['src'] ) ) {
		$keep = array_intersect_key( $default, array_flip( array( 'src', 'srcset', 'w', 'h', 'full' ) ) );
		echo '<input type="hidden" name="' . esc_attr( $name ) . '[default]" value="' . esc_attr( wp_json_encode( $keep ) ) . '" data-default-url="' . esc_url( aaykay_asset( $default['src'] ) ) . '">';
	}
	echo '<p><button type="button" class="button ak-image-choose">' . ( $data ? 'Replace photo' : 'Choose photo' ) . '</button> ';
	echo '<button type="button" class="button-link ak-image-reset"' . ( $upload && $default ? '' : ' hidden' ) . '>Go back to the original photo</button> ';
	echo '<button type="button" class="button-link button-link-delete ak-image-clear"' . ( $data ? '' : ' hidden' ) . '>Remove photo</button></p>';
	echo '<label for="' . esc_attr( $id ) . '-alt">Describe the photo (alt text)</label><input type="text" class="large-text" id="' . esc_attr( $id ) . '-alt" name="' . esc_attr( $name ) . '[alt]" value="' . esc_attr( $alt ) . '" placeholder="e.g. LT panel line-up in the plant room at Yashoda Hospitals">';
	echo $help_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	echo '</div></fieldset>';
}

function aaykay_editor_list( $name, $id, $field, $value, $help_html ) {
	$extra = $field[3];
	$item  = isset( $extra['item'] ) ? $extra['item'] : 'item';
	$max   = isset( $extra['max'] ) ? (int) $extra['max'] : 20;
	$rows  = is_array( $value ) ? array_values( $value ) : array();
	echo '<fieldset class="ak-list" data-max="' . (int) $max . '" data-item="' . esc_attr( $item ) . '"><legend>' . esc_html( $field[1] ) . '</legend>' . $help_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	echo '<ol class="ak-rows">';
	foreach ( $rows as $i => $row ) {
		aaykay_editor_row( $name, $id, $extra, $row, (string) $i, $item );
	}
	echo '</ol>';
	// Template for a new row; __i__ is replaced by the script with a unique number.
	echo '<template class="ak-row-template">';
	aaykay_editor_row( $name, $id, $extra, array(), '__i__', $item );
	echo '</template>';
	echo '<p><button type="button" class="button ak-add">Add ' . esc_html( $item ) . '</button></p></fieldset>';
}

function aaykay_editor_row( $name, $id, $extra, $row, $i, $item ) {
	echo '<li class="ak-row"><div class="ak-row-bar"><span class="ak-row-title">' . esc_html( ucfirst( $item ) ) . '</span>';
	echo '<button type="button" class="button-link ak-up" aria-label="Move ' . esc_attr( $item ) . ' up">↑ Up</button> <button type="button" class="button-link ak-down" aria-label="Move ' . esc_attr( $item ) . ' down">↓ Down</button> <button type="button" class="button-link button-link-delete ak-remove">Remove</button></div>';
	foreach ( $extra['fields'] as $sub_key => $sub ) {
		$value = isset( $row[ $sub_key ] ) ? $row[ $sub_key ] : ( 'icon' === $sub[0] ? 'gauge' : ( 'image' === $sub[0] ? array() : '' ) );
		aaykay_editor_field( "{$name}[{$i}][{$sub_key}]", "{$id}-{$i}-{$sub_key}", $sub, $value, true );
	}
	echo '</li>';
}
