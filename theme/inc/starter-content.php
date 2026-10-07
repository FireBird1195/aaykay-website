<?php
/**
 * Starter content: seed/content.json (38 projects, 5 sectors, 14 clients, 31 firms, the
 * five homepage cards and the Site settings) is imported once, the first time the theme
 * is activated. After that WordPress is the source of truth.
 *
 * Tools > AAYKAY starter content runs the import again. It only adds what is missing
 * (matched by project slug, sector slug, client or firm name) and never changes or
 * deletes anything that exists, so it is safe to run on the live site.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/** seed/content.json as an array (read once per request). */
function aaykay_seed_data() {
	static $seed = null;
	if ( null === $seed ) {
		$json = file_get_contents( get_theme_file_path( 'seed/content.json' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		$seed = json_decode( (string) $json, true );
		$seed = is_array( $seed ) ? $seed : array();
	}
	return $seed;
}

add_action(
	'after_switch_theme',
	function () {
		if ( ! get_option( 'aaykay_starter_imported' ) ) {
			aaykay_import_starter_content();
			update_option( 'aaykay_starter_imported', gmdate( 'c' ), false );
		}
	}
);

/**
 * Add whatever starter content is missing. Returns counts of what was added.
 */
function aaykay_import_starter_content() {
	$seed  = aaykay_seed_data();
	$added = array(
		'sectors'  => 0,
		'projects' => 0,
		'photos'   => 0,
		'clients'  => 0,
		'firms'    => 0,
		'settings' => 0,
	);
	if ( ! $seed ) {
		return $added;
	}

	if ( false === get_option( AAYKAY_SETTINGS_OPTION, false ) ) {
		add_option( AAYKAY_SETTINGS_OPTION, aaykay_settings_defaults() );
		$added['settings'] = 1;
	}

	foreach ( $seed['sectors'] as $i => $s ) {
		if ( term_exists( $s['key'], 'aaykay_sector' ) ) {
			continue;
		}
		$term = wp_insert_term( $s['label'], 'aaykay_sector', array( 'slug' => $s['key'] ) );
		if ( is_wp_error( $term ) ) {
			continue;
		}
		update_term_meta( $term['term_id'], 'aaykay_short_label', $s['short_label'] );
		update_term_meta( $term['term_id'], 'aaykay_description', $s['description'] );
		update_term_meta( $term['term_id'], 'aaykay_clients', $s['clients'] );
		update_term_meta( $term['term_id'], 'aaykay_icon', $s['icon'] );
		update_term_meta( $term['term_id'], 'aaykay_order', ( $i + 1 ) * 10 );
		++$added['sectors'];
	}

	foreach ( $seed['projects'] as $p ) {
		if ( get_page_by_path( $p['slug'], OBJECT, 'aaykay_project' ) ) {
			continue;
		}
		$card    = isset( $p['featured'] ) && is_array( $p['featured'] ) ? $p['featured'] : null;
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'aaykay_project',
				'post_status' => 'publish',
				'post_title'  => $p['name'],
				'post_name'   => $p['slug'],
				'menu_order'  => $card ? (int) $card['order'] : 0,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			continue;
		}
		foreach ( array( 'location', 'area_sqft', 'floors', 'order_value', 'design_team', 'status', 'note', 'source' ) as $key ) {
			if ( isset( $p[ $key ] ) && null !== $p[ $key ] && '' !== $p[ $key ] ) {
				update_post_meta( $post_id, '_aaykay_' . $key, (string) $p[ $key ] );
			}
		}
		wp_set_object_terms( $post_id, $p['sector'], 'aaykay_sector' );
		if ( $card ) {
			update_post_meta( $post_id, '_aaykay_featured', '1' );
			update_post_meta( $post_id, '_aaykay_card_label', $card['label'] );
			update_post_meta( $post_id, '_aaykay_card_details', $card['details'] );
			if ( ! empty( $card['note'] ) ) {
				update_post_meta( $post_id, '_aaykay_card_note', $card['note'] );
			}
			$photo = aaykay_sideload_theme_image( $card['image'], $post_id, $card['alt'] );
			if ( $photo ) {
				set_post_thumbnail( $post_id, $photo );
				++$added['photos'];
			}
		}
		++$added['projects'];
	}

	foreach ( $seed['clients'] as $i => $c ) {
		if ( aaykay_post_exists_by_title( $c['name'], 'aaykay_client' ) ) {
			continue;
		}
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'aaykay_client',
				'post_status' => 'publish',
				'post_title'  => $c['name'],
				'menu_order'  => ( $i + 1 ) * 10,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			continue;
		}
		if ( ! empty( $c['logo'] ) ) {
			update_post_meta( $post_id, '_aaykay_bundled_logo', $c['logo'] );
		}
		if ( ! empty( $c['source'] ) ) {
			update_post_meta( $post_id, '_aaykay_source', $c['source'] );
		}
		++$added['clients'];
	}

	foreach ( $seed['firms'] as $f ) {
		if ( aaykay_post_exists_by_title( $f['name'], 'aaykay_firm' ) ) {
			continue;
		}
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'aaykay_firm',
				'post_status' => 'publish',
				'post_title'  => $f['name'],
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			continue;
		}
		if ( ! empty( $f['logo'] ) ) {
			update_post_meta( $post_id, '_aaykay_bundled_logo', $f['logo'] );
		}
		if ( ! empty( $f['mark'] ) ) {
			update_post_meta( $post_id, '_aaykay_mark', $f['mark'] );
		}
		++$added['firms'];
	}

	aaykay_bin_untouched_samples();
	return $added;
}

function aaykay_post_exists_by_title( $title, $type ) {
	$found = get_posts(
		array(
			'post_type'      => $type,
			'post_status'    => 'any',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	return ! empty( $found );
}

/**
 * Copy an image bundled in the theme into the Media Library (WordPress then makes the
 * resized versions the page uses). Returns the attachment ID or 0.
 */
function aaykay_sideload_theme_image( $path, $post_id, $alt ) {
	$source = get_theme_file_path( $path );
	if ( ! is_readable( $source ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( basename( $source ) );
	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		return 0;
	}
	// "work-shell-xenon-1200.webp" -> "shell-xenon.webp"
	$name = preg_replace( array( '/^work-/', '/-\d+(\.\w+)$/' ), array( '', '$1' ), basename( $source ) );
	$id   = media_handle_sideload(
		array(
			'name'     => $name,
			'tmp_name' => $tmp,
		),
		$post_id,
		get_the_title( $post_id )
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/**
 * A fresh WordPress install comes with a "Hello world!" post and a "Sample Page". Move
 * them to the bin if nobody has edited them (they can be restored from there).
 */
function aaykay_bin_untouched_samples() {
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $sample ) {
		$post = get_page_by_path( $sample[0], OBJECT, $sample[1] );
		if ( $post && 'publish' === $post->post_status && $post->post_modified_gmt === $post->post_date_gmt ) {
			wp_trash_post( $post->ID );
		}
	}
}

/* ------------------------------------------------------------------ Tools page */

add_action(
	'admin_menu',
	function () {
		add_management_page( 'AAYKAY starter content', 'AAYKAY starter content', 'manage_options', 'aaykay-starter', 'aaykay_render_starter_page' );
	}
);

function aaykay_render_starter_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$result = null;
	if ( isset( $_POST['aaykay_import'] ) && check_admin_referer( 'aaykay_import' ) ) {
		$result = aaykay_import_starter_content();
		update_option( 'aaykay_starter_imported', gmdate( 'c' ), false );
	}
	$seed = aaykay_seed_data();
	?>
	<div class="wrap">
		<h1>AAYKAY starter content</h1>
		<?php if ( $result ) : ?>
			<div class="notice notice-success"><p>
				<?php
				$parts = array();
				foreach ( $result as $what => $n ) {
					if ( $n ) {
						$parts[] = $n . ' ' . ( 'settings' === $what ? 'settings' : $what );
					}
				}
				echo esc_html( $parts ? 'Added: ' . implode( ', ', $parts ) . '.' : 'Nothing was missing, so nothing was added.' );
				?>
			</p></div>
		<?php endif; ?>
		<p>The theme comes with the content the website launched with: <?php echo (int) count( $seed['projects'] ); ?> projects, <?php echo (int) count( $seed['sectors'] ); ?> sectors, <?php echo (int) count( $seed['clients'] ); ?> clients, <?php echo (int) count( $seed['firms'] ); ?> architects and consultants, and the Site settings. It was imported when the theme was first activated.</p>
		<p>Importing again only adds items that are missing (for example, one deleted by mistake). It never changes or removes anything that exists.</p>
		<form method="post">
			<?php wp_nonce_field( 'aaykay_import' ); ?>
			<?php submit_button( 'Add missing starter content', 'secondary', 'aaykay_import' ); ?>
		</form>
	</div>
	<?php
}
