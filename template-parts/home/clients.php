<?php
/**
 * Home page: clients.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
?>
  <section class="clients" aria-labelledby="clients-title">
    <div class="wrap">
      <div class="clients-head">
        <h2 class="label" id="clients-title">Clients include</h2>
        <p>A selection of our <?php echo esc_html( aaykay_setting( 'clients_served' ) ); ?> clients</p>
      </div>
      <ul class="client-list">
        <?php echo aaykay_render_clients( '        ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

      </ul>
    </div>
  </section>
