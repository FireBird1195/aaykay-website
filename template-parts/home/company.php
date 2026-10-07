<?php
/**
 * Home page: company. Content: Homepage content > Company.
 * Head office and branch count: Site settings.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact  = aaykay_contact();
$aaykay_states   = count( aaykay_branches() );
$aaykay_creds    = aaykay_rows( 'company', 'creds' );
$aaykay_timeline = aaykay_rows( 'company', 'timeline' );
$aaykay_photo    = aaykay_img( aaykay_c( 'company', 'founder_photo' ), array( 'sizes' => '(min-width: 900px) 28vw, 22rem' ) );
?>
  <section class="section" id="company" aria-labelledby="company-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="company-title" data-reveal><?php aaykay_e( 'company', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'company', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'company', 'sub' ); ?></p>
<?php endif; ?>
      </div>
      <div class="company grid-12">
        <div class="founder">
<?php if ( '' !== $aaykay_photo ) : ?>
          <div class="founder-photo">
            <?php echo $aaykay_photo; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

          </div>
<?php endif; ?>
          <p class="founder-name"><?php aaykay_e( 'company', 'founder_name' ); ?></p>
          <p class="label"><?php aaykay_e( 'company', 'founder_title' ); ?></p>
<?php if ( $aaykay_creds ) : ?>
          <ul class="creds">
<?php foreach ( $aaykay_creds as $aaykay_cr ) : ?>
            <li class="icon-label"><?php echo aaykay_icon( $aaykay_cr['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( aaykay_tokens( $aaykay_cr['text'] ) ); ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
        </div>
        <div class="company-body">
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
<?php foreach ( aaykay_paragraphs( aaykay_t( 'company', 'paragraphs' ) ) as $aaykay_para ) : ?>
          <p><?php echo esc_html( $aaykay_para ); ?></p>
<?php endforeach; ?>
<?php if ( '' !== aaykay_t( 'company', 'quote' ) ) : ?>
          <figure class="pull">
            <blockquote><p><?php aaykay_e( 'company', 'quote' ); ?></p></blockquote>
<?php if ( '' !== aaykay_t( 'company', 'quote_by' ) ) : ?>
            <figcaption><?php aaykay_e( 'company', 'quote_by' ); ?></figcaption>
<?php endif; ?>
          </figure>
<?php endif; ?>
<?php if ( $aaykay_timeline ) : ?>
          <ol class="timeline">
<?php foreach ( $aaykay_timeline as $aaykay_tl ) : ?>
            <li><span class="tl-year"><?php echo esc_html( $aaykay_tl['year'] ); ?></span><p><?php echo esc_html( aaykay_tokens( $aaykay_tl['text'] ) ); ?></p></li>
<?php endforeach; ?>
          </ol>
<?php endif; ?>
        </div>
      </div>
    </div>
  </section>
