<?php
/**
 * Quality & Safety page. Content: Homepage content > Quality & safety.
 * Staff numbers: Site settings > Resources. Appreciation documents: appreciations.php.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_certs  = aaykay_rows( 'quality', 'certs' );
$aaykay_qrows  = aaykay_rows( 'quality', 'quality_rows' );
$aaykay_srows  = aaykay_rows( 'quality', 'safety_rows' );
$aaykay_photos = aaykay_rows( 'quality', 'photos' );
$aaykay_kit    = aaykay_lines( 'quality', 'kit_items' );
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
		// A photo described but not yet uploaded is a placeholder (Site settings > Placeholders).
		echo '' !== trim( $aaykay_f['caption'] ) ? '          ' . aaykay_pending( 'Photo: ' . $aaykay_f['caption'] ) . "\n" : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending().
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
    </div>
  </section>
