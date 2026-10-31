<?php
/**
 * A set of scanned documents (certificates, licences, letters): a card per document with its
 * thumbnail, title, description and a "View" link. The cards form one set in the image viewer
 * (data-gallery, see site.js initLightbox): a visitor opens one and steps through the rest
 * with Previous / Next or the arrow keys. A row with a title but no scan yet shows a "to
 * come" box (Site settings > Placeholders).
 *
 * Used by template-parts/home/appreciations.php and certificates.php.
 * $args['docs']: rows of image, title, meta, button, caption.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_docs = isset( $args['docs'] ) ? (array) $args['docs'] : array();
?>
        <div class="doc-grid" data-gallery>
<?php
foreach ( $aaykay_docs as $aaykay_d ) :
	$aaykay_data = aaykay_image_data( $aaykay_d['image'] );
	if ( ! $aaykay_data ) {
		echo '' !== trim( $aaykay_d['title'] ) ? '          ' . aaykay_pending( $aaykay_d['title'] ) . "\n" : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending().
		continue;
	}
	?>
          <a class="doc" href="<?php echo esc_url( $aaykay_data['full'] ); ?>" data-lightbox="<?php echo esc_url( $aaykay_data['full'] ); ?>" data-caption="<?php echo esc_attr( '' !== trim( $aaykay_d['caption'] ) ? $aaykay_d['caption'] : $aaykay_d['title'] ); ?>">
            <?php echo aaykay_img( $aaykay_d['image'], array( 'sizes' => '104px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

            <span>
              <span class="doc-title"><?php echo esc_html( $aaykay_d['title'] ); ?></span>
<?php if ( '' !== trim( $aaykay_d['meta'] ) ) : ?>
              <span class="doc-meta"><?php echo esc_html( $aaykay_d['meta'] ); ?></span>
<?php endif; ?>
              <span class="doc-open"><?php echo aaykay_icon( 'maximize-2', 'ic--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( '' !== trim( $aaykay_d['button'] ) ? $aaykay_d['button'] : 'View document' ); ?></span>
            </span>
          </a>
<?php endforeach; ?>
        </div>
