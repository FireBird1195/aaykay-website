<?php
/**
 * Home page: hero.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_turnover = aaykay_turnover();
$aaykay_clients  = trim( (string) aaykay_setting( 'clients_served' ) );
$aaykay_states   = count( aaykay_branches() );
?>
  <section class="hero grain" id="top" aria-labelledby="hero-title">
    <div class="hero-media">
      <img src="<?php aaykay_a( 'assets/img/hero-ups-room-960.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/hero-ups-room-640.webp' ); ?> 640w, <?php aaykay_a( 'assets/img/hero-ups-room-960.webp' ); ?> 960w, <?php aaykay_a( 'assets/img/hero-ups-room-1280.webp' ); ?> 1280w" width="1280" height="578" class="hero-img" sizes="100vw" loading="eager" fetchpriority="high"
           alt="A UPS battery room installed by AAYKAY: rows of battery racks under overhead cable trays." decoding="async">
    </div>
    <div class="hero-scrim" aria-hidden="true"></div>
    <div class="wrap hero-inner">
      <p class="label" data-hero-in>Electrical &amp; MEP contracting · Since 2008</p>
      <h1 class="hero-title" id="hero-title">
        <span class="line"><span>Electrical systems</span></span>
        <span class="line"><span>for buildings that</span></span>
        <span class="line"><span>can’t go dark<span class="stop">.</span></span></span>
      </h1>
      <p class="hero-lead" data-hero-in>AAYKAY designs, installs, tests and commissions electrical works for hospitals, enterprise campuses, data centres and high-rise towers, from shop drawing to handover.</p>
      <div class="hero-actions" data-hero-in>
        <a class="btn btn--primary" href="#contact">Start a project <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M1.5 8h12M9 3.5 13.5 8 9 12.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></a>
        <a class="btn btn--outline-light" href="#work">See our work</a>
      </div>
    </div>
    <div class="wrap hero-foot" data-hero-in>
      <dl class="kpis">
<?php if ( $aaykay_turnover ) : ?>
        <div class="kpi"><dt>Turnover, <?php echo esc_html( $aaykay_turnover[0]['label'] ); ?></dt><dd>₹<?php echo esc_html( aaykay_crore( $aaykay_turnover[0]['value'] ) ); ?> Cr</dd></div>
<?php endif; ?>
<?php if ( '' !== $aaykay_clients ) : ?>
        <div class="kpi"><dt>Clients served</dt><dd><?php echo esc_html( $aaykay_clients ); ?></dd></div>
<?php endif; ?>
<?php if ( $aaykay_states ) : ?>
        <div class="kpi"><dt>States with branches</dt><dd><?php echo (int) $aaykay_states; ?></dd></div>
<?php endif; ?>
        <div class="kpi"><dt>ISO 9001:2015 &amp; 45001:2018</dt><dd>Certified</dd></div>
      </dl>
      <p class="fig-cap-hero">Fig. 1 — UPS battery room, AAYKAY installation</p>
    </div>
  </section>
