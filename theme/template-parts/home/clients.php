<?php
/**
 * Home page: client logos strip. Heading: Homepage content > Client logos strip.
 * Logos: Dashboard > Client logos.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
?>
  <section class="clients" aria-labelledby="clients-title">
    <div class="wrap">
      <div class="clients-head">
        <h2 class="label" id="clients-title"><?php aaykay_e( 'clients', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'clients', 'sub' ) ) : ?>
        <p><?php aaykay_e( 'clients', 'sub' ); ?></p>
<?php endif; ?>
      </div>
      <ul class="client-list">
        <?php echo aaykay_render_clients( '        ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

      </ul>
    </div>
  </section>
