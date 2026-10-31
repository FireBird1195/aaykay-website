<?php
/**
 * Careers page (client brief, 13 Oct 2026): employee welfare as icon cards, then the open
 * positions as a list a visitor can scan (job title, domain, location, experience) and
 * filter by domain, each opening to its job description with an Apply button, then how to
 * apply. Content: Homepage content > Careers.
 *
 * Each position is a native <details> element, so it opens and closes with the keyboard and
 * without JavaScript. The domain filter (site.js, initJobs) is an addition: without
 * JavaScript every position is listed. Apply opens the application form when one is set,
 * otherwise an email to the careers address with the job title as subject.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_benefits = aaykay_rows( 'careers', 'benefits' );
$aaykay_roles    = array_values(
	array_filter(
		aaykay_rows( 'careers', 'roles' ),
		function ( $r ) {
			return '' !== trim( (string) $r['title'] );
		}
	)
);
$aaykay_form     = esc_url( aaykay_t( 'careers', 'form_url' ), array( 'http', 'https' ) );
$aaykay_email    = sanitize_email( aaykay_t( 'careers', 'email' ) );
$aaykay_email    = $aaykay_email ? $aaykay_email : sanitize_email( aaykay_contact()['email'] );
$aaykay_label    = '' !== aaykay_t( 'careers', 'form_label' ) ? aaykay_t( 'careers', 'form_label' ) : 'Apply';
$aaykay_domains  = array();
foreach ( $aaykay_roles as $aaykay_r ) {
	$aaykay_d = trim( (string) $aaykay_r['domain'] );
	if ( '' !== $aaykay_d ) {
		$aaykay_domains[ sanitize_title( $aaykay_d ) ] = $aaykay_d;
	}
}
?>
  <section class="section section--dark grain" id="careers" aria-labelledby="careers-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="careers-title" data-reveal><?php aaykay_e( 'careers', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'careers', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'careers', 'sub' ); ?></p>
<?php endif; ?>
      </div>

      <div class="scope jobs" id="positions">
        <div class="subhead">
          <h3><?php aaykay_e( 'careers', 'roles_heading' ); ?></h3>
<?php if ( $aaykay_roles ) : ?>
          <p><span id="jobs-count"><?php echo esc_html( count( $aaykay_roles ) . ( 1 === count( $aaykay_roles ) ? ' position' : ' positions' ) ); ?></span></p>
<?php endif; ?>
        </div>
<?php if ( '' !== aaykay_t( 'careers', 'roles_sub' ) ) : ?>
        <p class="jobs-sub"><?php aaykay_e( 'careers', 'roles_sub' ); ?></p>
<?php endif; ?>
<?php if ( $aaykay_roles ) : ?>
<?php if ( count( $aaykay_domains ) > 1 ) : ?>
        <div class="filters job-filters" role="group" aria-label="Filter positions by domain" hidden>
          <button class="chip" type="button" data-domain="all" aria-pressed="true"><span class="chip-label">All domains</span> <span class="chip-count"><?php echo (int) count( $aaykay_roles ); ?></span></button>
<?php foreach ( $aaykay_domains as $aaykay_key => $aaykay_d ) : ?>
<?php
	$aaykay_n = count(
		array_filter(
			$aaykay_roles,
			function ( $r ) use ( $aaykay_key ) {
				return sanitize_title( trim( (string) $r['domain'] ) ) === $aaykay_key;
			}
		)
	);
?>
          <button class="chip" type="button" data-domain="<?php echo esc_attr( $aaykay_key ); ?>" aria-pressed="false"><span class="chip-label"><?php echo esc_html( $aaykay_d ); ?></span> <span class="chip-count"><?php echo (int) $aaykay_n; ?></span></button>
<?php endforeach; ?>
        </div>
<?php endif; ?>
        <ul class="job-list">
<?php foreach ( $aaykay_roles as $aaykay_r ) : ?>
<?php
	$aaykay_meta  = array_filter( array_map( 'trim', array( $aaykay_r['domain'], $aaykay_r['location'], $aaykay_r['experience'], $aaykay_r['type'] ) ), 'strlen' );
	$aaykay_resp  = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $aaykay_r['responsibilities'] ) ), 'strlen' );
	$aaykay_req   = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $aaykay_r['requirements'] ) ), 'strlen' );
	$aaykay_apply = '' !== $aaykay_form ? $aaykay_form : ( $aaykay_email ? 'mailto:' . $aaykay_email . '?subject=' . rawurlencode( 'Application: ' . $aaykay_r['title'] ) : '' );
?>
          <li class="job" data-domain="<?php echo esc_attr( sanitize_title( trim( (string) $aaykay_r['domain'] ) ) ); ?>">
            <details>
              <summary>
                <span class="job-title"><?php echo esc_html( $aaykay_r['title'] ); ?></span>
                <span class="job-meta">
<?php foreach ( $aaykay_meta as $aaykay_m ) : ?>
                  <span class="job-tag"><?php echo esc_html( $aaykay_m ); ?></span>
<?php endforeach; ?>
                </span>
                <span class="job-toggle" aria-hidden="true"><?php echo aaykay_icon( 'chevron-down', 'ic--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?></span>
              </summary>
              <div class="job-body">
<?php if ( '' !== trim( $aaykay_r['summary'] ) ) : ?>
                <p class="job-summary"><?php echo esc_html( $aaykay_r['summary'] ); ?></p>
<?php endif; ?>
                <div class="job-cols">
<?php if ( $aaykay_resp ) : ?>
                  <div>
                    <h4 class="label">Responsibilities</h4>
                    <ul class="ticks">
<?php foreach ( $aaykay_resp as $aaykay_li ) : ?>
                      <li><?php echo esc_html( $aaykay_li ); ?></li>
<?php endforeach; ?>
                    </ul>
                  </div>
<?php endif; ?>
<?php if ( $aaykay_req ) : ?>
                  <div>
                    <h4 class="label">Requirements</h4>
                    <ul class="ticks">
<?php foreach ( $aaykay_req as $aaykay_li ) : ?>
                      <li><?php echo esc_html( $aaykay_li ); ?></li>
<?php endforeach; ?>
                    </ul>
                  </div>
<?php endif; ?>
                </div>
<?php if ( '' !== $aaykay_apply ) : ?>
                <p class="job-apply"><a class="btn btn--primary" href="<?php echo esc_url( $aaykay_apply, array( 'http', 'https', 'mailto' ) ); ?>"<?php echo '' !== $aaykay_form ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $aaykay_label ); ?><span class="visually-hidden"> for <?php echo esc_html( $aaykay_r['title'] ); ?><?php echo '' !== $aaykay_form ? ' (opens in a new tab)' : ' (opens your email)'; ?></span> <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a></p>
<?php endif; ?>
              </div>
            </details>
          </li>
<?php endforeach; ?>
        </ul>
<?php else : ?>
        <?php echo aaykay_pending( 'Open positions' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
      </div>

<?php if ( $aaykay_benefits ) : ?>
      <div class="scope careers-benefits">
        <div class="subhead">
          <h3><?php aaykay_e( 'careers', 'benefits_heading' ); ?></h3>
        </div>
        <ul class="scope-grid scope-grid--3">
<?php foreach ( $aaykay_benefits as $aaykay_r ) : ?>
          <li class="scope-item"><h4 class="icon-label"><?php echo aaykay_icon( ! empty( $aaykay_r['icon'] ) ? $aaykay_r['icon'] : 'circle-check' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( $aaykay_r['key'] ); ?></h4><p><?php echo esc_html( $aaykay_r['text'] ); ?></p></li>
<?php endforeach; ?>
        </ul>
      </div>
<?php endif; ?>

      <div class="scope careers-how">
        <div class="subhead">
          <h3><?php aaykay_e( 'careers', 'apply_heading' ); ?></h3>
        </div>
<?php if ( '' !== aaykay_t( 'careers', 'apply_text' ) ) : ?>
        <p class="careers-text"><?php aaykay_e( 'careers', 'apply_text' ); ?></p>
<?php endif; ?>
<?php if ( $aaykay_email ) : ?>
        <p class="careers-text">Email your CV to <a href="mailto:<?php echo esc_attr( $aaykay_email ); ?>"><?php echo esc_html( $aaykay_email ); ?></a><?php echo '' !== $aaykay_form ? ', or <a href="' . esc_url( $aaykay_form ) . '" target="_blank" rel="noopener">apply online<span class="visually-hidden"> (opens in a new tab)</span></a>' : ''; ?>.</p>
<?php endif; ?>
<?php if ( '' !== aaykay_t( 'careers', 'note' ) ) : ?>
        <p class="careers-note"><?php aaykay_e( 'careers', 'note' ); ?></p>
<?php endif; ?>
      </div>
    </div>
  </section>
