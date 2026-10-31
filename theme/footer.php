<?php
/**
 * Site footer (company, quick links, contact details with copy buttons) and the image viewer
 * dialog (site.js, initLightbox). The contact details here are the only ones on pages other
 * than Contact Us (client review of 13 Oct).
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
      <a class="brand" href="<?php echo esc_url( aaykay_section_url( 'top' ) ); ?>"><?php echo aaykay_brand_lockup(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the function. ?><span class="visually-hidden"><?php echo is_front_page() ? ', back to top' : ', home'; ?></span></a>
      <p><?php aaykay_e( 'brand', 'footer_blurb' ); ?></p>
    </div>
    <nav class="footer-nav" id="footer-nav" aria-label="Footer">
      <h2 class="footer-h">Quick links</h2>
      <?php echo aaykay_render_nav( 'footer', '      ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

    </nav>
    <div class="footer-contact">
      <h2 class="footer-h">Contact</h2>
      <address class="footer-addr"><?php echo esc_html( aaykay_join( ', ', array( $aaykay_contact['street'], $aaykay_contact['area'], aaykay_join( ' ', array( $aaykay_contact['city'], $aaykay_contact['postcode'] ) ), $aaykay_contact['full'] ? '' : $aaykay_contact['state'] ) ) ); ?></address>
      <ul class="footer-lines">
        <li class="copy-row"><a href="tel:<?php echo esc_attr( $aaykay_contact['tel'] ); ?>" id="ft-phone"><?php echo esc_html( $aaykay_contact['phone'] ); ?></a><?php echo aaykay_copy_button( $aaykay_contact['phone'], 'ft-phone', 'phone number' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?></li>
<?php if ( '' !== $aaykay_contact['phone2'] ) : ?>
        <li class="copy-row"><a href="tel:<?php echo esc_attr( $aaykay_contact['tel2'] ); ?>" id="ft-phone2"><?php echo esc_html( $aaykay_contact['phone2'] ); ?></a><?php echo aaykay_copy_button( $aaykay_contact['phone2'], 'ft-phone2', 'phone number' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?></li>
<?php endif; ?>
<?php if ( '' !== $aaykay_contact['email'] ) : ?>
        <li class="copy-row"><a href="mailto:<?php echo esc_attr( $aaykay_contact['email'] ); ?>" id="ft-email"><?php echo esc_html( $aaykay_contact['email'] ); ?></a><?php echo aaykay_copy_button( $aaykay_contact['email'], 'ft-email', 'email address' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?></li>
<?php endif; ?>
      </ul>
    </div>
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

<p class="visually-hidden" id="copy-status" role="status"></p>

<dialog class="lightbox" id="lightbox" aria-label="Image viewer" aria-describedby="lightbox-caption">
  <div class="lightbox-bar">
    <p class="lightbox-caption" id="lightbox-caption"></p>
    <p class="lightbox-count" hidden></p>
    <button class="lightbox-close" type="button"><?php echo aaykay_icon( 'x', 'ic--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?>Close</button>
  </div>
  <div class="lightbox-stage">
    <button class="lightbox-nav lightbox-prev" type="button" hidden><?php echo aaykay_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><span class="visually-hidden">Previous</span></button>
    <img alt="">
    <button class="lightbox-nav lightbox-next" type="button" hidden><?php echo aaykay_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><span class="visually-hidden">Next</span></button>
  </div>
</dialog>

<?php wp_footer(); ?>
<!-- MADE BY TEJAS PANDE -->
</body>
</html>
