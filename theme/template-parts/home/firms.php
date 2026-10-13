<?php
/**
 * Architects, Consultants & PMCs page: the firms AAYKAY has worked with.
 * Heading: Homepage content > Architects, consultants and PMCs. Firms: Dashboard >
 * Architects & consultants (a logo, or letters when there is none).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_firms = aaykay_render_firms( '          ' );
if ( '' === $aaykay_firms ) {
	return;
}
?>
  <section class="section" id="firms" aria-labelledby="firms-title">
    <div class="wrap">
      <div class="firms grid-12">
        <h2 id="firms-title"><?php aaykay_e( 'firms', 'heading' ); ?></h2>
        <ul class="firm-list">
          <?php echo $aaykay_firms; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

        </ul>
      </div>
    </div>
  </section>
