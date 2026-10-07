<?php
/**
 * Home page: how we deliver. Content: Homepage content > How we deliver.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_stages = aaykay_rows( 'deliver', 'stages' );
$aaykay_p1     = aaykay_lines( 'deliver', 'panel1_items' );
$aaykay_p2     = aaykay_lines( 'deliver', 'panel2_items' );
$aaykay_s1     = aaykay_rows( 'deliver', 'sheet1_rows' );
$aaykay_s2     = aaykay_rows( 'deliver', 'sheet2_rows' );
?>
  <section class="section" id="deliver" aria-labelledby="deliver-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="deliver-title" data-reveal><?php aaykay_e( 'deliver', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'deliver', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'deliver', 'sub' ); ?></p>
<?php endif; ?>
      </div>

      <div class="insight grid-12">
<?php if ( '' !== aaykay_t( 'deliver', 'insight' ) . aaykay_t( 'deliver', 'insight_em' ) ) : ?>
        <p class="insight-statement" data-reveal><?php aaykay_e( 'deliver', 'insight' ); ?><?php echo '' !== aaykay_t( 'deliver', 'insight_em' ) ? ' <em>' . esc_html( aaykay_t( 'deliver', 'insight_em' ) ) . '</em>' : ''; ?></p>
<?php endif; ?>
        <div class="insight-panels">
<?php if ( $aaykay_p1 ) : ?>
          <div class="panel">
            <h3 class="label"><?php aaykay_e( 'deliver', 'panel1_heading' ); ?></h3>
            <ul class="ticks">
<?php foreach ( $aaykay_p1 as $aaykay_li ) : ?>
              <li><?php echo esc_html( aaykay_tokens( $aaykay_li ) ); ?></li>
<?php endforeach; ?>
            </ul>
          </div>
<?php endif; ?>
<?php if ( $aaykay_p2 ) : ?>
          <div class="panel">
            <h3 class="label"><?php aaykay_e( 'deliver', 'panel2_heading' ); ?></h3>
            <ul class="ticks">
<?php foreach ( $aaykay_p2 as $aaykay_li ) : ?>
              <li><?php echo esc_html( aaykay_tokens( $aaykay_li ) ); ?></li>
<?php endforeach; ?>
            </ul>
<?php if ( '' !== aaykay_t( 'deliver', 'panel2_note' ) ) : ?>
            <p class="panel-note"><?php aaykay_e( 'deliver', 'panel2_note' ); ?></p>
<?php endif; ?>
          </div>
<?php endif; ?>
        </div>
      </div>

<?php if ( $aaykay_stages ) : ?>
      <ol class="stages">
<?php foreach ( $aaykay_stages as $aaykay_i => $aaykay_st ) : ?>
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><svg class="ic stage-ic"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#<?php echo esc_attr( $aaykay_st['icon'] ); ?>"/></svg></span>
          <p class="stage-no">Stage <?php echo esc_html( sprintf( '%02d', $aaykay_i + 1 ) ); ?></p>
          <h3><?php echo esc_html( aaykay_tokens( $aaykay_st['title'] ) ); ?></h3>
          <p><?php echo esc_html( aaykay_tokens( $aaykay_st['text'] ) ); ?></p>
<?php $aaykay_tags = array_filter( array_map( 'trim', explode( ',', $aaykay_st['tags'] ) ), 'strlen' ); ?>
<?php if ( $aaykay_tags ) : ?>
          <ul class="tags"><?php foreach ( $aaykay_tags as $aaykay_tag ) : ?><li><?php echo esc_html( $aaykay_tag ); ?></li><?php endforeach; ?></ul>
<?php endif; ?>
        </li>
<?php endforeach; ?>
      </ol>
<?php endif; ?>

<?php if ( $aaykay_s1 || $aaykay_s2 ) : ?>
      <div class="sheets grid-12">
<?php if ( $aaykay_s1 ) : ?>
        <div class="sheet">
          <p class="label"><?php aaykay_e( 'deliver', 'sheet1_label' ); ?></p>
          <h3 class="sheet-title"><?php aaykay_e( 'deliver', 'sheet1_title' ); ?></h3>
          <dl>
<?php foreach ( $aaykay_s1 as $aaykay_r ) : ?>
            <div><dt><?php echo esc_html( $aaykay_r['term'] ); ?></dt><dd><?php echo esc_html( $aaykay_r['value'] ); ?></dd></div>
<?php endforeach; ?>
          </dl>
<?php if ( '' !== aaykay_t( 'deliver', 'sheet1_note' ) ) : ?>
          <p class="sheet-note"><?php aaykay_e( 'deliver', 'sheet1_note' ); ?></p>
<?php endif; ?>
        </div>
<?php endif; ?>
<?php if ( $aaykay_s2 ) : ?>
        <div class="sheet sheet--tools">
          <p class="label"><?php aaykay_e( 'deliver', 'sheet2_label' ); ?></p>
          <h3 class="sheet-title"><?php aaykay_e( 'deliver', 'sheet2_title' ); ?></h3>
          <dl>
<?php foreach ( $aaykay_s2 as $aaykay_r ) : ?>
            <div><dt><?php echo esc_html( $aaykay_r['term'] ); ?></dt><dd><?php echo esc_html( $aaykay_r['value'] ); ?></dd></div>
<?php endforeach; ?>
          </dl>
        </div>
<?php endif; ?>
      </div>
<?php endif; ?>
    </div>
  </section>
