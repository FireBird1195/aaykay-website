<?php
/**
 * Site footer and the document viewer dialog.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact = aaykay_contact();
$aaykay_ids     = array_filter(
	array(
		'' !== trim( (string) aaykay_setting( 'cin' ) ) ? 'CIN ' . trim( (string) aaykay_setting( 'cin' ) ) : '',
		'' !== trim( (string) aaykay_setting( 'gstin' ) ) ? 'GSTIN ' . trim( (string) aaykay_setting( 'gstin' ) ) : '',
	)
);
$aaykay_regd    = trim( (string) aaykay_setting( 'registered_office' ) );
?>
<footer class="site-footer">
  <div class="wrap grid-12 footer-top">
    <div class="footer-brand">
      <a class="brand" href="<?php echo esc_attr( aaykay_section_url( 'top' ) ); ?>"><?php echo aaykay_brand_lockup( false ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the function. ?><span class="visually-hidden"> Electricals, back to top</span></a>
      <p><?php aaykay_e( 'brand', 'footer_blurb' ); ?></p>
    </div>
    <nav class="footer-nav" id="footer-nav" aria-label="Footer">
      <?php echo aaykay_render_nav( 'footer', '      ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

    </nav>
    <address class="footer-addr"><?php echo esc_html( aaykay_join( ', ', array( $aaykay_contact['street'], $aaykay_contact['area'], aaykay_join( ' ', array( $aaykay_contact['city'], $aaykay_contact['postcode'] ) ) ) ) ); ?><br><?php echo esc_html( $aaykay_contact['phone'] ); ?></address>
  </div>
  <div class="wrap">
    <div class="footer-bottom">
      <span><?php echo aaykay_dot_line( array_merge( array( '© ' . wp_date( 'Y' ) . ' ' . aaykay_legal_name() ), explode( ' · ', aaykay_t( 'brand', 'footer_bottom' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_dot_line(). ?></span>
<?php if ( $aaykay_ids || '' !== $aaykay_regd ) : ?>
      <span><?php echo esc_html( aaykay_join( ' · ', array_merge( $aaykay_ids, array( '' !== $aaykay_regd ? 'Registered office: ' . $aaykay_regd : '' ) ) ) ); ?></span>
<?php endif; ?>
<?php if ( get_privacy_policy_url() ) : ?>
      <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Privacy notice</a>
<?php endif; ?>
    </div>
  </div>
</footer>

<dialog class="lightbox" id="lightbox" aria-label="Document viewer" aria-describedby="lightbox-caption">
  <div class="lightbox-bar">
    <p class="lightbox-caption" id="lightbox-caption"></p>
    <button class="lightbox-close" type="button">Close</button>
  </div>
  <img alt="">
</dialog>

<?php wp_footer(); ?>
<!-- MADE BY TEJAS PANDE -->
</body>
</html>
