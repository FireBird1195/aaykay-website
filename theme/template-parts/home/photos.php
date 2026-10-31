<?php
/**
 * A photo mosaic: the site photographs on Our Capabilities (Homepage content > Site
 * photographs). $args['section'] and $args['id'] let another section reuse it.
 *
 * The mosaic in site.css is laid out for six photos (.m-a … .m-f). With more, the pattern
 * repeats; with fewer, the first slots are used.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_sid   = isset( $args['section'] ) ? $args['section'] : 'photos';
$aaykay_id    = isset( $args['id'] ) ? $args['id'] : 'site-photos';
$aaykay_figs  = aaykay_rows( $aaykay_sid, 'figures' );
// Widths the photos are shown at (the mosaic in site.css); min() because the page stops
// widening at --container (85rem), so 42vw would overstate the size on wide screens.
$aaykay_slots = array(
	'm-a' => '(min-width: 760px) min(42vw, 570px), 50vw',
	'm-b' => '(min-width: 760px) min(58vw, 800px), 100vw',
	'm-c' => '(min-width: 760px) min(33vw, 450px), 50vw',
	'm-d' => '(min-width: 760px) min(25vw, 330px), 50vw',
	'm-e' => '(min-width: 760px) min(58vw, 800px), 100vw',
	'm-f' => '(min-width: 760px) min(42vw, 570px), 100vw',
);
$aaykay_keys  = array_keys( $aaykay_slots );
if ( ! $aaykay_figs ) {
	return;
}
?>
  <section class="section section--dark grain" id="<?php echo esc_attr( $aaykay_id ); ?>" aria-labelledby="<?php echo esc_attr( $aaykay_id ); ?>-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="<?php echo esc_attr( $aaykay_id ); ?>-title" data-reveal><?php aaykay_e( $aaykay_sid, 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( $aaykay_sid, 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( $aaykay_sid, 'sub' ); ?></p>
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
