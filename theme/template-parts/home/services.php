<?php
/**
 * Home page: what we do + sectors. Content: Homepage content > What we do.
 * Sector rows: Dashboard > Projects > Sectors.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_pillars = aaykay_rows( 'services', 'pillars' );
$aaykay_scope   = aaykay_rows( 'services', 'scope' );
?>
  <section class="section" id="services" aria-labelledby="intro-title">
    <div class="wrap">
      <div class="intro grid-12">
        <h2 class="h2" id="intro-title" data-reveal><?php aaykay_e( 'services', 'heading' ); ?></h2>
        <div class="intro-body">
<?php if ( '' !== aaykay_t( 'services', 'lead' ) ) : ?>
          <p class="lead" data-reveal><?php aaykay_e( 'services', 'lead' ); ?></p>
<?php endif; ?>
<?php if ( $aaykay_pillars ) : ?>
          <div class="pillars">
<?php foreach ( $aaykay_pillars as $aaykay_p ) : ?>
            <div class="pillar">
              <h3 class="icon-label"><?php echo aaykay_icon( $aaykay_p['icon'], 'ic--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( aaykay_tokens( $aaykay_p['title'] ) ); ?></h3>
              <p><?php echo esc_html( aaykay_tokens( $aaykay_p['text'] ) ); ?></p>
            </div>
<?php endforeach; ?>
          </div>
<?php endif; ?>
        </div>
      </div>

<?php if ( $aaykay_scope ) : ?>
      <div class="scope">
        <div class="subhead">
          <h3><?php aaykay_e( 'services', 'scope_heading' ); ?></h3>
        </div>
        <div class="scope-grid">
<?php foreach ( $aaykay_scope as $aaykay_s ) : ?>
          <div class="scope-item"><h4 class="icon-label"><?php echo aaykay_icon( $aaykay_s['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( aaykay_tokens( $aaykay_s['title'] ) ); ?></h4><p><?php echo esc_html( aaykay_tokens( $aaykay_s['text'] ) ); ?></p></div>
<?php endforeach; ?>
        </div>
      </div>
<?php endif; ?>

      <div class="sectors" id="sectors">
        <div class="sectors-head">
          <h3 class="label"><?php aaykay_e( 'services', 'sectors_heading' ); ?></h3>
<?php if ( '' !== aaykay_t( 'services', 'sectors_hint' ) ) : ?>
          <p><?php aaykay_e( 'services', 'sectors_hint' ); ?></p>
<?php endif; ?>
        </div>
        <ul class="sector-list">
          <?php echo aaykay_render_sector_rows( '          ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

        </ul>
      </div>
    </div>
  </section>
