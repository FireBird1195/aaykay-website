<?php
/**
 * Projects page: sectors served, the first section of the page (final client brief,
 * 13 Oct 2026; moved here from Our Capabilities). Heading: Homepage content > Sectors
 * served. Rows: Dashboard > Projects > Sectors; selecting one filters the project list
 * below (site.js, initRecord).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_rows = aaykay_render_sector_rows( '        ' );
if ( '' === $aaykay_rows ) {
	return;
}
?>
  <section class="section" id="sectors" aria-labelledby="sectors-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="sectors-title" data-reveal><?php aaykay_e( 'sectors', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'sectors', 'hint' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'sectors', 'hint' ); ?></p>
<?php endif; ?>
      </div>
      <div class="sectors">
        <ul class="sector-list">
          <?php echo $aaykay_rows; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

        </ul>
      </div>
    </div>
  </section>
