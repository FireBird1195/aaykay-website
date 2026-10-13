<?php
/**
 * Quality & Safety page (client review of 13 Oct: images first, then text; all the photos in
 * one place; no testing equipment list).
 *
 *   1. Every photo of the subject in one gallery: the quality & safety photos (Homepage
 *      content > Quality & safety > Photos), then the site safety photographs (Homepage
 *      content > Site safety photographs). Each opens full size in the image viewer, with
 *      Previous / Next through all of them (site.js, initLightbox).
 *   2. The ISO certificates, the staff numbers (Site settings > Resources) and the quality
 *      and safety practice, side by side.
 *
 * The certificate scans follow in certificates.php.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_certs = aaykay_rows( 'quality', 'certs' );
$aaykay_figs  = aaykay_rows( 'quality', 'photos' );
if ( aaykay_section_on( 'safety_photos' ) ) {
	$aaykay_figs = array_merge( $aaykay_figs, aaykay_rows( 'safety_photos', 'figures' ) );
}
$aaykay_blocks = array(
	'quality' => aaykay_rows( 'quality', 'quality_rows' ),
	'safety'  => aaykay_rows( 'quality', 'safety_rows' ),
);
?>
  <section class="section section--dark grain" id="quality" aria-labelledby="quality-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="quality-title" data-reveal><?php aaykay_e( 'quality', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'quality', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'quality', 'sub' ); ?></p>
<?php endif; ?>
      </div>

<?php if ( $aaykay_figs ) : ?>
      <ul class="gallery" data-gallery>
<?php
foreach ( $aaykay_figs as $aaykay_f ) :
	$aaykay_data = aaykay_image_data( $aaykay_f['image'] );
	$aaykay_cap  = trim( $aaykay_f['caption'] );
	if ( ! $aaykay_data ) {
		// A photo described but not yet uploaded is a placeholder (Site settings > Placeholders).
		echo '' !== $aaykay_cap ? '        <li>' . aaykay_pending( 'Photo: ' . $aaykay_cap ) . "</li>\n" : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending().
		continue;
	}
	?>
        <li>
          <a class="gallery-item" href="<?php echo esc_url( $aaykay_data['full'] ); ?>" data-lightbox="<?php echo esc_url( $aaykay_data['full'] ); ?>" data-caption="<?php echo esc_attr( $aaykay_cap ); ?>">
            <?php echo aaykay_img( $aaykay_f['image'], array( 'sizes' => '(min-width: 1000px) 24vw, (min-width: 560px) 45vw, 90vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

<?php if ( '' !== $aaykay_cap ) : ?>
            <span class="gallery-cap"><?php echo esc_html( $aaykay_cap ); ?></span>
<?php endif; ?>
          </a>
        </li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>

      <div class="qs">
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
        <div class="qs-blocks grid-12">
<?php foreach ( $aaykay_blocks as $aaykay_which => $aaykay_rows ) : ?>
<?php if ( $aaykay_rows ) : ?>
          <div class="qs-block">
            <h3 class="icon-label"><?php echo aaykay_icon( aaykay_c( 'quality', $aaykay_which . '_icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'quality', $aaykay_which . '_heading' ); ?></h3>
            <ul class="qs-list">
<?php foreach ( $aaykay_rows as $aaykay_r ) : ?>
              <li><span class="qs-key"><?php echo esc_html( $aaykay_r['key'] ); ?></span><span><?php echo esc_html( aaykay_tokens( $aaykay_r['text'] ) ); ?></span></li>
<?php endforeach; ?>
            </ul>
          </div>
<?php endif; ?>
<?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
