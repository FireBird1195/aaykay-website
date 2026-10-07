<?php
/**
 * Home page: pre-qualification. Headings and certificates: Homepage content >
 * Pre-qualification. Turnover, resources and branches: Site settings.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact  = aaykay_contact();
$aaykay_turnover = aaykay_turnover();
// Bars are drawn against the largest year plus 5% headroom.
$aaykay_scale = $aaykay_turnover ? round( max( wp_list_pluck( $aaykay_turnover, 'value' ) ) * 1.05, 2 ) : 1;
$aaykay_os    = aaykay_setting( 'res_out_skilled' );
$aaykay_ou    = aaykay_setting( 'res_out_unskilled' );
if ( '' !== $aaykay_os && '' !== $aaykay_ou ) {
	$aaykay_out = array( 'Outsourced, skilled / unskilled' => $aaykay_os . ' / ' . $aaykay_ou );
} else {
	$aaykay_out = array(
		'Outsourced, skilled'   => $aaykay_os,
		'Outsourced, unskilled' => $aaykay_ou,
	);
}
$aaykay_groups = array(
	'Management & supervision' => array(
		'Project managers'  => aaykay_setting( 'res_pm' ),
		'Project engineers' => aaykay_setting( 'res_pe' ),
		'Supervisors'       => aaykay_setting( 'res_sup' ),
		'Quality staff'     => aaykay_setting( 'res_quality' ),
		'Safety staff'      => aaykay_setting( 'res_safety' ),
		'Office staff'      => aaykay_setting( 'res_office' ),
	),
	'Workforce'                => array(
		'Skilled'   => aaykay_setting( 'res_skilled' ),
		'Unskilled' => aaykay_setting( 'res_unskilled' ),
	) + $aaykay_out,
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
          <h3 class="label icon-label"><?php echo aaykay_icon( aaykay_c( 'prequal', 'turnover_icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'prequal', 'turnover_heading' ); ?></h3>
<?php if ( $aaykay_turnover ) : ?>
          <p class="stat-value">₹<?php echo esc_html( aaykay_crore( $aaykay_turnover[0]['value'] ) ); ?> Cr</p>
          <p class="stat-sub"><?php echo esc_html( aaykay_turnover_change() ); ?></p>
          <table class="fy" style="--max:<?php echo esc_attr( $aaykay_scale ); ?>">
            <caption class="visually-hidden">Annual turnover by financial year, in crore rupees</caption>
            <tbody>
<?php foreach ( $aaykay_turnover as $aaykay_i => $aaykay_row ) : ?>
              <tr><th scope="row"><?php echo esc_html( $aaykay_row['label'] ); ?></th><td><span class="bar-row"><span class="bar-track"><span class="bar<?php echo 0 === $aaykay_i ? ' bar--now' : ''; ?>" style="--v:<?php echo esc_attr( (string) $aaykay_row['value'] ); ?>"></span></span><span class="bar-val"><?php echo esc_html( aaykay_crore( $aaykay_row['value'] ) ); ?></span></span></td></tr>
<?php endforeach; ?>
            </tbody>
          </table>
<?php endif; ?>
        </div>
        <div class="pq-block">
          <h3 class="label icon-label"><?php echo aaykay_icon( aaykay_c( 'prequal', 'resources_icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'prequal', 'resources_heading' ); ?></h3>
<?php
foreach ( $aaykay_groups as $aaykay_group => $aaykay_rows ) :
	$aaykay_rows = array_filter( $aaykay_rows, 'strlen' );
	if ( ! $aaykay_rows ) {
		continue;
	}
	?>
          <p class="pq-group"><?php echo esc_html( $aaykay_group ); ?></p>
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
<?php if ( '' !== aaykay_t( 'prequal', 'request_label' ) ) : ?>
      <p class="pq-request"><a class="btn btn--primary" href="#contact" data-enquiry-type="Pre-qualification documents"><?php aaykay_e( 'prequal', 'request_label' ); ?> <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a></p>
<?php endif; ?>
    </div>
  </section>
