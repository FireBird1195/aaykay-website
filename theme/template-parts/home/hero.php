<?php
/**
 * Home page: opening screen. Content: Homepage content > Opening screen.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_lines = aaykay_lines( 'hero', 'title' );
$aaykay_kpis  = array();
foreach ( aaykay_rows( 'hero', 'kpis' ) as $aaykay_row ) {
	if ( ! aaykay_token_empty( isset( $aaykay_row['value'] ) ? $aaykay_row['value'] : '' ) ) {
		$aaykay_kpis[] = $aaykay_row;
	}
}
?>
  <section class="hero grain" id="top" aria-labelledby="hero-title">
    <div class="hero-media">
      <?php echo aaykay_img( aaykay_c( 'hero', 'image' ), array( 'class' => 'hero-img', 'sizes' => '100vw', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

    </div>
    <div class="hero-scrim" aria-hidden="true"></div>
    <div class="wrap hero-inner">
<?php if ( '' !== aaykay_t( 'hero', 'label' ) ) : ?>
      <p class="label" data-hero-in><span><?php echo aaykay_dot_line( aaykay_t( 'hero', 'label' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_dot_line(). ?></span></p>
<?php endif; ?>
      <h1 class="hero-title" id="hero-title">
<?php
foreach ( $aaykay_lines as $aaykay_i => $aaykay_line ) {
	$aaykay_line = aaykay_tokens( $aaykay_line );
	$aaykay_stop = '';
	// A full stop at the end of the last line is set in red.
	if ( count( $aaykay_lines ) - 1 === $aaykay_i && '.' === substr( $aaykay_line, -1 ) ) {
		$aaykay_line = substr( $aaykay_line, 0, -1 );
		$aaykay_stop = '<span class="stop">.</span>';
	}
	echo '        <span class="line"><span>' . esc_html( $aaykay_line ) . $aaykay_stop . "</span></span>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped.
}
?>
      </h1>
<?php if ( '' !== aaykay_t( 'hero', 'lead' ) ) : ?>
      <p class="hero-lead" data-hero-in><?php aaykay_e( 'hero', 'lead' ); ?></p>
<?php endif; ?>
      <div class="hero-actions" data-hero-in>
        <a class="btn btn--primary" href="#contact"><?php aaykay_e( 'hero', 'primary_label' ); ?> <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a>
<?php if ( '' !== aaykay_t( 'hero', 'secondary_label' ) ) : ?>
        <a class="btn btn--outline-light" href="#work"><?php aaykay_e( 'hero', 'secondary_label' ); ?></a>
<?php endif; ?>
      </div>
    </div>
    <div class="wrap hero-foot" data-hero-in>
<?php if ( $aaykay_kpis ) : ?>
      <dl class="kpis">
<?php foreach ( $aaykay_kpis as $aaykay_kpi ) : ?>
        <div class="kpi"><dt><?php echo esc_html( aaykay_tokens( $aaykay_kpi['label'] ) ); ?></dt><dd><?php echo esc_html( aaykay_tokens( $aaykay_kpi['value'] ) ); ?></dd></div>
<?php endforeach; ?>
      </dl>
<?php endif; ?>
<?php if ( '' !== aaykay_t( 'hero', 'caption' ) ) : ?>
      <p class="fig-cap-hero"><?php aaykay_e( 'hero', 'caption' ); ?></p>
<?php endif; ?>
    </div>
  </section>
