<?php
/**
 * Home page, About Us: project methodology (client review of 13 Oct). First the five stages
 * on the animated track (Stage 01 Design → … → Stage 05 Handover; the line fills as it
 * scrolls into view, see site.js initStages; shown complete without JavaScript or with
 * reduced motion). Under the track, the planning that comes before site work: the statement
 * on approvals and long-lead equipment, the kick-off requirements and the early procurement
 * list. Content: Homepage content > About Us: project methodology.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_stages = aaykay_rows( 'deliver', 'stages' );
$aaykay_p1     = aaykay_lines( 'deliver', 'panel1_items' );
$aaykay_p2     = aaykay_lines( 'deliver', 'panel2_items' );
$aaykay_lead   = '' !== aaykay_t( 'deliver', 'insight' ) . aaykay_t( 'deliver', 'insight_em' );
?>
  <section class="section" id="deliver" aria-labelledby="deliver-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="deliver-title" data-reveal><?php aaykay_e( 'deliver', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'deliver', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'deliver', 'sub' ); ?></p>
<?php endif; ?>
      </div>

<?php if ( $aaykay_stages ) : ?>
      <ol class="stages" style="--steps: <?php echo (int) count( $aaykay_stages ); ?>">
<?php foreach ( $aaykay_stages as $aaykay_i => $aaykay_st ) : ?>
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><?php echo aaykay_icon( $aaykay_st['icon'], 'stage-ic' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?></span>
          <p class="stage-no">Stage <?php echo esc_html( sprintf( '%02d', $aaykay_i + 1 ) ); ?></p>
          <h3><?php echo esc_html( aaykay_tokens( $aaykay_st['title'] ) ); ?></h3>
<?php if ( '' !== trim( $aaykay_st['text'] ) ) : ?>
          <p><?php echo esc_html( aaykay_tokens( $aaykay_st['text'] ) ); ?></p>
<?php endif; ?>
        </li>
<?php endforeach; ?>
      </ol>
<?php endif; ?>

<?php if ( $aaykay_lead || $aaykay_p1 || $aaykay_p2 ) : ?>
      <div class="plan grid-12">
<?php if ( $aaykay_lead ) : ?>
        <p class="plan-statement"><?php aaykay_e( 'deliver', 'insight' ); ?><?php echo '' !== aaykay_t( 'deliver', 'insight_em' ) ? ' <em>' . esc_html( aaykay_t( 'deliver', 'insight_em' ) ) . '</em>' : ''; ?></p>
<?php endif; ?>
<?php foreach ( array( array( 'panel1', $aaykay_p1 ), array( 'panel2', $aaykay_p2 ) ) as $aaykay_panel ) : ?>
<?php if ( $aaykay_panel[1] ) : ?>
        <div class="plan-panel">
          <h3 class="label"><?php aaykay_e( 'deliver', $aaykay_panel[0] . '_heading' ); ?></h3>
          <ul class="ticks">
<?php foreach ( $aaykay_panel[1] as $aaykay_li ) : ?>
            <li><?php echo esc_html( aaykay_tokens( $aaykay_li ) ); ?></li>
<?php endforeach; ?>
          </ul>
<?php if ( 'panel2' === $aaykay_panel[0] && '' !== aaykay_t( 'deliver', 'panel2_note' ) ) : ?>
          <p class="panel-note"><?php aaykay_e( 'deliver', 'panel2_note' ); ?></p>
<?php endif; ?>
        </div>
<?php endif; ?>
<?php endforeach; ?>
      </div>
<?php endif; ?>
    </div>
  </section>
