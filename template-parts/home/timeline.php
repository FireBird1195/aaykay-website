<?php
/**
 * Home page, About Us: the company's timeline, as a compact row of milestones: the year set
 * large, the event under it (client review of 13 Oct: the timeline took too much space, and
 * it must not look like the project methodology's animated stages, so it has no track and no
 * animation). On phones each milestone is a row: year on the left, event on the right.
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
  <section class="section section--white section--tight" id="timeline" aria-labelledby="timeline-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="timeline-title" data-reveal><?php aaykay_e( 'company', 'timeline_heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'company', 'timeline_sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'company', 'timeline_sub' ); ?></p>
<?php endif; ?>
      </div>
      <ol class="milestones">
<?php foreach ( $aaykay_timeline as $aaykay_tl ) : ?>
        <li class="milestone">
          <h3 class="milestone-year"><?php echo esc_html( $aaykay_tl['year'] ); ?></h3>
          <p><?php echo esc_html( aaykay_tokens( $aaykay_tl['text'] ) ); ?></p>
        </li>
<?php endforeach; ?>
      </ol>
    </div>
  </section>
