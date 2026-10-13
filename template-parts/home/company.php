<?php
/**
 * Home page, About Us: the company (client brief, 13 Oct 2026: after the company profile's
 * "Company brief" and "Our work" pages): key facts, the company brief, what AAYKAY does and
 * why clients choose it. Then come timeline.php, team.php and deliver.php.
 * Content: Homepage content > About Us: company, timeline and leadership. Head office and
 * branch count: Site settings.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact = aaykay_contact();
$aaykay_states  = count( aaykay_branches() );
$aaykay_work    = aaykay_rows( 'company', 'work_items' );
$aaykay_why     = aaykay_lines( 'company', 'why_items' );
?>
  <section class="section" id="about" aria-labelledby="about-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="about-title" data-reveal><?php aaykay_e( 'company', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'company', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'company', 'sub' ); ?></p>
<?php endif; ?>
      </div>
      <dl class="glance">
<?php if ( '' !== aaykay_t( 'company', 'glance_company' ) ) : ?>
        <div><dt>Company</dt><dd><?php aaykay_e( 'company', 'glance_company' ); ?></dd></div>
<?php endif; ?>
<?php if ( '' !== aaykay_t( 'company', 'glance_established' ) ) : ?>
        <div><dt>Established</dt><dd><?php aaykay_e( 'company', 'glance_established' ); ?></dd></div>
<?php endif; ?>
        <div><dt>Head office</dt><dd><?php echo esc_html( aaykay_join( ', ', array( $aaykay_contact['area'], $aaykay_contact['city'] ) ) ); ?></dd></div>
<?php if ( $aaykay_states ) : ?>
        <div><dt>Branches</dt><dd><?php echo esc_html( $aaykay_states . ( 1 === $aaykay_states ? ' state' : ' states' ) ); ?></dd></div>
<?php endif; ?>
      </dl>
      <div class="grid-12 about-grid">
        <div class="company-history">
<?php foreach ( aaykay_paragraphs( aaykay_t( 'company', 'paragraphs' ) ) as $aaykay_para ) : ?>
          <p><?php echo esc_html( $aaykay_para ); ?></p>
<?php endforeach; ?>
        </div>
<?php if ( $aaykay_work ) : ?>
        <div class="about-work">
          <h3 class="label"><?php aaykay_e( 'company', 'work_heading' ); ?></h3>
          <ul class="work-points">
<?php foreach ( $aaykay_work as $aaykay_w ) : ?>
            <li><?php echo aaykay_icon( $aaykay_w['icon'], 'ic--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><p><?php echo esc_html( aaykay_tokens( $aaykay_w['text'] ) ); ?></p></li>
<?php endforeach; ?>
          </ul>
        </div>
<?php endif; ?>
      </div>
<?php if ( $aaykay_why ) : ?>
      <div class="scope why">
        <div class="subhead">
          <h3><?php aaykay_e( 'company', 'why_heading' ); ?></h3>
        </div>
        <ul class="why-list">
<?php foreach ( $aaykay_why as $aaykay_line ) : ?>
          <li class="icon-label"><?php echo aaykay_icon( 'circle-check' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( aaykay_tokens( $aaykay_line ) ); ?></li>
<?php endforeach; ?>
        </ul>
      </div>
<?php endif; ?>
    </div>
  </section>
