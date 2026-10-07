<?php
/**
 * Home page: pre-qualification.
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
        <h2 class="h2" id="pq-title" data-reveal>Pre-qualification</h2>
        <p class="sec-sub" data-reveal>The facts procurement and tender teams usually ask for first.</p>
      </div>
      <div class="pq-grid">
        <div class="pq-block">
          <h3 class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#chart-column-increasing"/></svg>Annual turnover</h3>
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
          <h3 class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#users"/></svg>Resources</h3>
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
          <h3 class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#badge-check"/></svg>Certifications &amp; registrations</h3>
          <p class="pq-group">ISO certification</p>
          <ul class="pq-plain">
            <li><b>ISO 9001:2015</b> quality management</li>
            <li><b>ISO 45001:2018</b> occupational health &amp; safety</li>
          </ul>
          <p class="pq-group">Electrical contractor licences</p>
          <ul class="pq-plain">
            <li><b>Hyderabad</b></li>
            <li><b>Bengaluru</b></li>
          </ul>
          <p class="pq-group">Company registrations</p>
          <ul class="pq-plain">
            <li>Private limited company, Certificate of Incorporation</li>
            <li>MSME and GST registered</li>
            <li>ESI and EPF registered</li>
            <li>D&amp;B certified</li>
          </ul>
        </div>
        <div class="pq-block">
          <h3 class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#map-pin"/></svg>Branches</h3>
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
      </div>
    </div>
  </section>
