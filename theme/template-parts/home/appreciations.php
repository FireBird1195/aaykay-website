<?php
/**
 * Projects page, last section: appreciation certificates and letters, each opening full size in the document
 * viewer (footer.php). Content: Homepage content > Appreciations.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_docs = aaykay_rows( 'appreciations', 'docs' );
?>
  <section class="section section--dark grain" id="appreciations" aria-labelledby="appreciations-title">
    <div class="wrap">
      <div class="recog">
        <div class="subhead">
          <h2 id="appreciations-title"><?php aaykay_e( 'appreciations', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'appreciations', 'sub' ) ) : ?>
          <p><?php aaykay_e( 'appreciations', 'sub' ); ?></p>
<?php endif; ?>
        </div>
        <div class="doc-grid">
<?php
foreach ( $aaykay_docs as $aaykay_d ) :
	$aaykay_data = aaykay_image_data( $aaykay_d['image'] );
	if ( ! $aaykay_data ) {
		// A row with a title but no scan yet is a placeholder (Site settings > Placeholders).
		echo '' !== trim( $aaykay_d['title'] ) ? '          ' . aaykay_pending( $aaykay_d['title'] ) . "\n" : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending().
		continue;
	}
	?>
          <a class="doc" href="<?php echo esc_url( $aaykay_data['full'] ); ?>" data-lightbox="<?php echo esc_url( $aaykay_data['full'] ); ?>" data-caption="<?php echo esc_attr( $aaykay_d['caption'] ); ?>">
            <?php echo aaykay_img( $aaykay_d['image'], array( 'sizes' => '104px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

            <span>
              <span class="doc-title"><?php echo esc_html( $aaykay_d['title'] ); ?></span>
              <span class="doc-meta"><?php echo esc_html( $aaykay_d['meta'] ); ?></span>
              <span class="doc-open"><?php echo aaykay_icon( 'maximize-2', 'ic--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( '' !== $aaykay_d['button'] ? $aaykay_d['button'] : 'View document' ); ?></span>
            </span>
          </a>
<?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
