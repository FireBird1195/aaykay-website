<?php
/**
 * Site footer and the document viewer dialog.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact  = aaykay_contact();
$aaykay_branches = aaykay_branches();
?>
<footer class="site-footer">
  <div class="wrap grid-12 footer-top">
    <div class="footer-brand">
      <a class="brand" href="<?php echo esc_attr( aaykay_section_url( 'top' ) ); ?>"><svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="M3.5 28 14.6 4h2.8L28.5 28h-4.4L16 10.4 7.9 28Z" fill="currentColor"/><path d="M6.5 21.6 26.6 13l-1.5 3.5L8 24.8Z" fill="#E0452B"/></svg><span class="brand-word">AAYKAY</span><span class="visually-hidden"> Electricals, back to top</span></a>
      <p>AAYKAY Electricals Private Limited. Electrical and MEP contracting since 2008, from <?php echo esc_html( $aaykay_contact['city'] ); ?> to <?php echo esc_html( aaykay_number_word( count( $aaykay_branches ) ) ); ?> states.</p>
    </div>
    <nav class="footer-nav" id="footer-nav" aria-label="Footer">
      <?php echo aaykay_render_nav( 'footer', '      ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

    </nav>
    <address class="footer-addr"><?php echo esc_html( $aaykay_contact['street'] . ', ' . $aaykay_contact['area'] . ', ' . $aaykay_contact['city'] . ' ' . $aaykay_contact['postcode'] ); ?><br><?php echo esc_html( $aaykay_contact['phone'] ); ?></address>
  </div>
  <div class="wrap">
    <div class="footer-bottom">
      <span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> AAYKAY Electricals Private Limited · ISO 9001:2015 · ISO 45001:2018</span>
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
</body>
</html>
