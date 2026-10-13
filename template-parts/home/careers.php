<?php
/**
 * Careers page: what AAYKAY provides its people, current openings and how to apply.
 * Content: Homepage content > Careers. Applications go to a form AAYKAY owns (e.g. a
 * Google Form); until its address is entered, a "to come" placeholder marks it.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_benefits = aaykay_rows( 'careers', 'benefits' );
$aaykay_roles    = aaykay_lines( 'careers', 'roles' );
$aaykay_form     = esc_url( aaykay_t( 'careers', 'form_url' ) );
$aaykay_email    = sanitize_email( aaykay_t( 'careers', 'email' ) );
?>
  <section class="section section--dark grain" id="careers" aria-labelledby="careers-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="careers-title" data-reveal><?php aaykay_e( 'careers', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'careers', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'careers', 'sub' ); ?></p>
<?php endif; ?>
      </div>
      <div class="qs grid-12">
        <div class="qs-text">
<?php if ( $aaykay_benefits ) : ?>
          <div class="qs-block">
            <h3 class="icon-label"><?php echo aaykay_icon( 'users' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'careers', 'benefits_heading' ); ?></h3>
            <ul class="qs-list">
<?php foreach ( $aaykay_benefits as $aaykay_r ) : ?>
              <li><span class="qs-key"><?php echo esc_html( $aaykay_r['key'] ); ?></span><span><?php echo esc_html( $aaykay_r['text'] ); ?></span></li>
<?php endforeach; ?>
            </ul>
          </div>
<?php endif; ?>
        </div>
        <div class="qs-apply">
          <div class="qs-block">
            <h3 class="icon-label"><?php echo aaykay_icon( 'hard-hat' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php aaykay_e( 'careers', 'roles_heading' ); ?></h3>
<?php if ( $aaykay_roles ) : ?>
            <ul class="qs-list">
<?php foreach ( $aaykay_roles as $aaykay_role ) : ?>
              <li><span><?php echo esc_html( $aaykay_role ); ?></span></li>
<?php endforeach; ?>
            </ul>
<?php else : ?>
            <?php echo aaykay_pending( 'Current openings' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
          </div>
<?php if ( '' !== aaykay_t( 'careers', 'apply_text' ) ) : ?>
          <p class="careers-text"><?php aaykay_e( 'careers', 'apply_text' ); ?></p>
<?php endif; ?>
<?php if ( '' !== $aaykay_form ) : ?>
          <p class="page-actions"><a class="btn btn--primary" href="<?php echo $aaykay_form; // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url() above. ?>" target="_blank" rel="noopener"><?php echo esc_html( '' !== aaykay_t( 'careers', 'form_label' ) ? aaykay_t( 'careers', 'form_label' ) : 'Apply' ); ?><span class="visually-hidden"> (opens in a new tab)</span> <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a></p>
<?php else : ?>
          <div class="page-actions"><?php echo aaykay_pending( 'Link to the job application form' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?></div>
<?php endif; ?>
<?php if ( $aaykay_email ) : ?>
          <p class="careers-text">Or email your CV to <a href="mailto:<?php echo esc_attr( $aaykay_email ); ?>"><?php echo esc_html( $aaykay_email ); ?></a>.</p>
<?php endif; ?>
<?php if ( '' !== aaykay_t( 'careers', 'note' ) ) : ?>
          <p class="careers-note"><?php aaykay_e( 'careers', 'note' ); ?></p>
<?php endif; ?>
        </div>
      </div>
    </div>
  </section>
