<?php
/**
 * Compliance & pre-qualification (Quality & Safety page): resources, certificates and
 * branches. No request button (client review of 13 Oct). Headings and certificates:
 * Homepage content > Pre-qualification. Resources and branches: Site settings. Turnover is not published
 * (client brief, 10 Oct 2026).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact  = aaykay_contact();
// Review meeting (Nov 2026): the staff list sits directly under "Resources"; the company's
// own workforce and the outsourced workforce are two groups of skilled / unskilled.
$aaykay_groups = array(
	''           => array(
		'Project managers'  => aaykay_setting( 'res_pm' ),
		'Project engineers' => aaykay_setting( 'res_pe' ),
		'Supervisors'       => aaykay_setting( 'res_sup' ),
		'Quality staff'     => aaykay_setting( 'res_quality' ),
		'Safety staff'      => aaykay_setting( 'res_safety' ),
		'Office staff'      => aaykay_setting( 'res_office' ),
	),
	'Workforce'  => array(
		'Skilled'   => aaykay_setting( 'res_skilled' ),
		'Unskilled' => aaykay_setting( 'res_unskilled' ),
	),
	'Outsourced' => array(
		'Skilled'   => aaykay_setting( 'res_out_skilled' ),
		'Unskilled' => aaykay_setting( 'res_out_unskilled' ),
	),
);
?>
  <section class="section section--white" id="prequal" aria-labelledby="pq-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="pq-title" data-reveal><?php aaykay_e( 'prequal', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'prequal', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'prequal', 'sub' ); ?></p>
<?php endif; ?>
      </div>
      <div class="pq-grid">
        <div class="pq-block">
          <h3 class="label icon-label"><?php echo aaykay_icon( aaykay_c( 'prequal', 'resources_icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'prequal', 'resources_heading' ); ?></h3>
<?php
foreach ( $aaykay_groups as $aaykay_group => $aaykay_rows ) :
	$aaykay_rows = array_filter( $aaykay_rows, 'strlen' );
	if ( ! $aaykay_rows ) {
		continue;
	}
	?>
<?php if ( '' !== $aaykay_group ) : ?>
          <p class="pq-group"><?php echo esc_html( $aaykay_group ); ?></p>
<?php endif; ?>
          <ul class="pq-list">
<?php foreach ( $aaykay_rows as $aaykay_label => $aaykay_n ) : ?>
            <li><?php echo esc_html( $aaykay_label ); ?> <b><?php echo esc_html( $aaykay_n ); ?></b></li>
<?php endforeach; ?>
          </ul>
<?php endforeach; ?>
        </div>
        <div class="pq-block">
          <h3 class="label icon-label"><?php echo aaykay_icon( aaykay_c( 'prequal', 'certs_icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'prequal', 'certs_heading' ); ?></h3>
<?php foreach ( aaykay_rows( 'prequal', 'cert_groups' ) as $aaykay_g ) : ?>
<?php $aaykay_items = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $aaykay_g['items'] ) ), 'strlen' ); ?>
<?php if ( '' !== trim( $aaykay_g['heading'] ) ) : ?>
          <p class="pq-group"><?php echo esc_html( $aaykay_g['heading'] ); ?></p>
<?php endif; ?>
<?php if ( $aaykay_items ) : ?>
          <ul class="pq-plain">
<?php foreach ( $aaykay_items as $aaykay_item ) : ?>
            <li><?php echo aaykay_bold_line( $aaykay_item ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_bold_line(). ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
<?php endforeach; ?>
        </div>
<?php if ( aaykay_branches() ) : ?>
        <div class="pq-block">
          <h3 class="label icon-label"><?php echo aaykay_icon( aaykay_c( 'prequal', 'branches_icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'prequal', 'branches_heading' ); ?></h3>
          <ul class="pq-plain">
<?php foreach ( aaykay_branches() as $aaykay_i => $aaykay_state ) : ?>
<?php if ( 0 === $aaykay_i ) : ?>
            <li><b><?php echo esc_html( $aaykay_state ); ?></b> <span class="pq-tag">Head office, <?php echo esc_html( $aaykay_contact['city'] ); ?></span></li>
<?php else : ?>
            <li><?php echo esc_html( $aaykay_state ); ?></li>
<?php endif; ?>
<?php endforeach; ?>
          </ul>
        </div>
<?php endif; ?>
      </div>
    </div>
  </section>
