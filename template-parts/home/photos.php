<?php
/**
 * Home page: site photographs. Content: Homepage content > Site photographs.
 *
 * The mosaic in site.css is laid out for six photos (.m-a … .m-f). With more, the pattern
 * repeats; with fewer, the first slots are used.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_figs  = aaykay_rows( 'photos', 'figures' );
$aaykay_slots = array(
	'm-a' => '(min-width: 760px) 42vw, 82vw',
	'm-b' => '(min-width: 760px) 58vw, 100vw',
	'm-c' => '(min-width: 760px) 33vw, 50vw',
	'm-d' => '(min-width: 760px) 25vw, 50vw',
	'm-e' => '(min-width: 760px) 58vw, 100vw',
	'm-f' => '(min-width: 760px) 42vw, 100vw',
);
$aaykay_keys  = array_keys( $aaykay_slots );
if ( ! $aaykay_figs ) {
	return;
}
?>
  <section class="section section--dark grain" id="site-photos" aria-labelledby="photos-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="photos-title" data-reveal><?php aaykay_e( 'photos', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'photos', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'photos', 'sub' ); ?></p>
<?php endif; ?>
      </div>
      <div class="mosaic">
<?php
foreach ( $aaykay_figs as $aaykay_i => $aaykay_f ) :
	$aaykay_slot = $aaykay_keys[ $aaykay_i % count( $aaykay_keys ) ];
	$aaykay_img  = aaykay_img( $aaykay_f['image'], array( 'sizes' => $aaykay_slots[ $aaykay_slot ] ) );
	if ( '' === $aaykay_img ) {
		continue;
	}
	?>
        <figure class="<?php echo esc_attr( $aaykay_slot ); ?>" data-reveal-media>
          <?php echo $aaykay_img; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

<?php if ( '' !== trim( $aaykay_f['caption'] . $aaykay_f['fig'] ) ) : ?>
          <figcaption class="fig-chip"><?php echo '' !== trim( $aaykay_f['fig'] ) ? '<b>' . esc_html( $aaykay_f['fig'] ) . '</b>' : ''; ?><?php echo esc_html( $aaykay_f['caption'] ); ?></figcaption>
<?php endif; ?>
        </figure>
<?php endforeach; ?>
      </div>
    </div>
  </section>
