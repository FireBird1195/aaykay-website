<?php
/**
 * Home page: client letter. Content: Homepage content > Client letter.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
if ( '' === aaykay_t( 'letter', 'quote' ) ) {
	return;
}
$aaykay_doc = aaykay_image_data( aaykay_c( 'letter', 'image' ) );
?>
  <section class="letter-band grain<?php echo $aaykay_doc ? '' : ' letter-band--text'; ?>" aria-labelledby="letter-title">
    <div class="wrap grid-12 letter-grid">
      <figure class="letter-quote">
        <h2 class="label" id="letter-title"><?php aaykay_e( 'letter', 'label' ); ?></h2>
        <blockquote><p><?php aaykay_e( 'letter', 'quote' ); ?></p></blockquote>
        <figcaption><?php echo '' !== aaykay_t( 'letter', 'from' ) ? '<strong>' . esc_html( aaykay_t( 'letter', 'from' ) ) . '</strong>' . ( '' !== aaykay_t( 'letter', 'from_rest' ) ? ', ' : '' ) : ''; ?><?php aaykay_e( 'letter', 'from_rest' ); ?><?php echo '' !== aaykay_t( 'letter', 'note' ) ? '<br>' . esc_html( aaykay_t( 'letter', 'note' ) ) : ''; ?></figcaption>
      </figure>
<?php if ( $aaykay_doc ) : ?>
      <a class="letter-doc" href="<?php echo esc_url( $aaykay_doc['full'] ); ?>" data-lightbox="<?php echo esc_url( $aaykay_doc['full'] ); ?>" data-caption="<?php echo esc_attr( aaykay_t( 'letter', 'caption' ) ); ?>">
        <?php echo aaykay_img( aaykay_c( 'letter', 'image' ), array( 'sizes' => '(min-width: 900px) 224px, 176px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

        <span><?php aaykay_e( 'letter', 'link_label' ); ?></span>
      </a>
<?php endif; ?>
    </div>
  </section>
