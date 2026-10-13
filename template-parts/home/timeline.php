<?php
/**
 * Home page, About Us: the company's timeline. The milestones sit on the same animated
 * track as the project methodology stages (a line that fills as it scrolls into view, see
 * site.js initStages; shown complete without JavaScript or with reduced motion).
 * Content: Homepage content > About Us: company, timeline and leadership > Timeline.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_timeline = aaykay_rows( 'company', 'timeline' );
if ( ! $aaykay_timeline ) {
	return;
}
?>
  <section class="section section--white" id="timeline" aria-labelledby="timeline-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="timeline-title" data-reveal><?php aaykay_e( 'company', 'timeline_heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'company', 'timeline_sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'company', 'timeline_sub' ); ?></p>
<?php endif; ?>
      </div>
      <ol class="stages stages--timeline" style="--steps: <?php echo (int) count( $aaykay_timeline ); ?>">
<?php foreach ( $aaykay_timeline as $aaykay_tl ) : ?>
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><?php echo aaykay_icon( 'history', 'stage-ic' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?></span>
          <h3 class="tl-year"><?php echo esc_html( $aaykay_tl['year'] ); ?></h3>
          <p><?php echo esc_html( aaykay_tokens( $aaykay_tl['text'] ) ); ?></p>
        </li>
<?php endforeach; ?>
      </ol>
    </div>
  </section>
