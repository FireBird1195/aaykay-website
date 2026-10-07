<?php
/**
 * Home page: quality & safety. Content: Homepage content > Quality & safety.
 * Staff numbers: Site settings > Resources.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_certs  = aaykay_rows( 'quality', 'certs' );
$aaykay_qrows  = aaykay_rows( 'quality', 'quality_rows' );
$aaykay_srows  = aaykay_rows( 'quality', 'safety_rows' );
$aaykay_photos = aaykay_rows( 'quality', 'photos' );
$aaykay_kit    = aaykay_lines( 'quality', 'kit_items' );
$aaykay_docs   = aaykay_rows( 'quality', 'docs' );
$aaykay_psizes = array( '(min-width: 960px) 40vw, 100vw', '(min-width: 960px) 20vw, 50vw', '(min-width: 960px) 20vw, 50vw' );
?>
  <section class="section section--dark grain" id="quality" aria-labelledby="quality-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="quality-title" data-reveal><?php aaykay_e( 'quality', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'quality', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'quality', 'sub' ); ?></p>
<?php endif; ?>
      </div>

      <div class="qs grid-12">
        <div class="qs-text">
<?php if ( $aaykay_certs ) : ?>
          <ul class="certs">
<?php foreach ( $aaykay_certs as $aaykay_cert ) : ?>
            <li class="cert"><?php echo aaykay_icon( $aaykay_cert['icon'], 'ic--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><span><b><?php echo esc_html( $aaykay_cert['title'] ); ?></b> <?php echo esc_html( $aaykay_cert['text'] ); ?></span></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
          <dl class="qs-stats">
<?php
foreach ( array( 'res_quality' => 'Quality staff', 'res_safety' => 'Safety staff', 'res_sup' => 'Supervisors' ) as $aaykay_key => $aaykay_label ) :
	$aaykay_n = aaykay_setting( $aaykay_key );
	if ( '' === $aaykay_n ) {
		continue;
	}
	?>
            <div class="qs-stat"><dt><?php echo esc_html( $aaykay_label ); ?></dt><dd><?php echo esc_html( $aaykay_n ); ?></dd></div>
<?php endforeach; ?>
          </dl>
<?php foreach ( array( array( 'quality', $aaykay_qrows ), array( 'safety', $aaykay_srows ) ) as $aaykay_block ) : ?>
<?php if ( $aaykay_block[1] ) : ?>
          <div class="qs-block">
            <h3 class="icon-label"><?php echo aaykay_icon( aaykay_c( 'quality', $aaykay_block[0] . '_icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'quality', $aaykay_block[0] . '_heading' ); ?></h3>
            <ul class="qs-list">
<?php foreach ( $aaykay_block[1] as $aaykay_r ) : ?>
              <li><span class="qs-key"><?php echo esc_html( $aaykay_r['key'] ); ?></span><span><?php echo esc_html( aaykay_tokens( $aaykay_r['text'] ) ); ?></span></li>
<?php endforeach; ?>
            </ul>
          </div>
<?php endif; ?>
<?php endforeach; ?>
        </div>
<?php if ( $aaykay_photos ) : ?>
        <div class="qs-photos">
<?php
foreach ( $aaykay_photos as $aaykay_i => $aaykay_f ) :
	$aaykay_img = aaykay_img( $aaykay_f['image'], array( 'sizes' => $aaykay_psizes[ min( $aaykay_i, 2 ) ] ) );
	if ( '' === $aaykay_img ) {
		continue;
	}
	?>
          <figure data-reveal-media>
            <?php echo $aaykay_img; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

<?php if ( '' !== trim( $aaykay_f['caption'] . $aaykay_f['fig'] ) ) : ?>
            <figcaption class="fig-chip"><?php echo '' !== trim( $aaykay_f['fig'] ) ? '<b>' . esc_html( $aaykay_f['fig'] ) . '</b>' : ''; ?><?php echo esc_html( $aaykay_f['caption'] ); ?></figcaption>
<?php endif; ?>
          </figure>
<?php endforeach; ?>
        </div>
<?php endif; ?>
      </div>

<?php if ( aaykay_c( 'quality', 'kit_enabled' ) && $aaykay_kit ) : ?>
      <div class="kit">
        <div class="subhead">
          <h3><?php aaykay_e( 'quality', 'kit_heading' ); ?></h3>
<?php if ( '' !== aaykay_t( 'quality', 'kit_sub' ) ) : ?>
          <p><?php aaykay_e( 'quality', 'kit_sub' ); ?></p>
<?php endif; ?>
        </div>
        <ul class="kit-list">
<?php foreach ( $aaykay_kit as $aaykay_item ) : ?>
          <li><?php echo esc_html( $aaykay_item ); ?></li>
<?php endforeach; ?>
        </ul>
      </div>
<?php endif; ?>

<?php if ( aaykay_c( 'quality', 'docs_enabled' ) && $aaykay_docs ) : ?>
      <div class="recog">
        <div class="subhead">
          <h3><?php aaykay_e( 'quality', 'docs_heading' ); ?></h3>
<?php if ( '' !== aaykay_t( 'quality', 'docs_sub' ) ) : ?>
          <p><?php aaykay_e( 'quality', 'docs_sub' ); ?></p>
<?php endif; ?>
        </div>
        <div class="doc-grid">
<?php
foreach ( $aaykay_docs as $aaykay_d ) :
	$aaykay_data = aaykay_image_data( $aaykay_d['image'] );
	if ( ! $aaykay_data ) {
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
<?php endif; ?>
    </div>
  </section>
