<?php
/**
 * Home page: opening screen, with the slideshow of background photos (final client brief,
 * 13 Oct 2026). Content: Homepage content > Opening screen. The first photo is part of the
 * page and loads first; site.js (initSlideshow) adds the controls and changes photos every
 * 7 seconds, unless the visitor asks for reduced motion. Without JavaScript the first
 * photo simply stays.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_lines = aaykay_lines( 'hero', 'title' );
$aaykay_slides = aaykay_hero_slides();
$aaykay_count  = count( $aaykay_slides );
$aaykay_kpis  = array();
foreach ( aaykay_rows( 'hero', 'kpis' ) as $aaykay_row ) {
	if ( ! aaykay_token_empty( isset( $aaykay_row['value'] ) ? $aaykay_row['value'] : '' ) ) {
		$aaykay_kpis[] = $aaykay_row;
	}
}
?>
  <section class="hero grain" id="top" aria-labelledby="hero-title">
    <div class="hero-media"<?php echo $aaykay_count > 1 ? ' role="group" aria-roledescription="slideshow" aria-label="Photos of AAYKAY installations"' : ''; ?>>
<?php foreach ( $aaykay_slides as $aaykay_i => $aaykay_slide ) : ?>
      <div class="hero-slide<?php echo 0 === $aaykay_i ? ' is-active' : ''; ?>" data-caption="<?php echo esc_attr( $aaykay_slide['caption'] ); ?>"<?php echo $aaykay_count > 1 ? ' role="group" aria-roledescription="slide" aria-label="' . esc_attr( ( $aaykay_i + 1 ) . ' of ' . $aaykay_count ) . '"' : ''; ?><?php echo $aaykay_i ? ' aria-hidden="true"' : ''; ?>>
<?php
	$aaykay_img = aaykay_img( $aaykay_slide['image'], 0 === $aaykay_i ? array( 'class' => 'hero-img', 'sizes' => '100vw', 'loading' => 'eager', 'fetchpriority' => 'high' ) : array( 'class' => 'hero-img', 'sizes' => '100vw', 'loading' => 'lazy' ) );
	if ( $aaykay_i ) {
		// The other photos load only after the page has loaded (site.js, initSlideshow), so
		// they never compete with the first one; without JavaScript they are never shown.
		$aaykay_img = str_replace( array( ' src="', ' srcset="' ), array( ' data-src="', ' data-srcset="' ), $aaykay_img );
	}
?>
        <?php echo $aaykay_img; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

      </div>
<?php endforeach; ?>
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
        <a class="btn btn--primary" href="<?php echo esc_url( aaykay_section_url( 'contact' ) ); ?>"><?php aaykay_e( 'hero', 'primary_label' ); ?> <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a>
<?php if ( '' !== aaykay_t( 'hero', 'secondary_label' ) ) : ?>
        <a class="btn btn--outline-light" href="<?php echo esc_url( aaykay_section_url( 'record' ) ); ?>"><?php aaykay_e( 'hero', 'secondary_label' ); ?></a>
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
<?php if ( $aaykay_count ) : ?>
      <div class="hero-meta">
        <p class="fig-cap-hero" id="hero-caption"><?php echo esc_html( $aaykay_slides[0]['caption'] ); ?></p>
<?php if ( $aaykay_count > 1 ) : ?>
        <div class="hero-controls" hidden>
          <div class="hero-dots">
<?php for ( $aaykay_i = 0; $aaykay_i < $aaykay_count; $aaykay_i++ ) : ?>
            <button class="hero-dot" type="button" aria-label="<?php echo esc_attr( 'Photo ' . ( $aaykay_i + 1 ) . ' of ' . $aaykay_count ); ?>"<?php echo 0 === $aaykay_i ? ' aria-current="true"' : ''; ?>></button>
<?php endfor; ?>
          </div>
          <button class="hero-pause" type="button" aria-label="Pause the slideshow"><?php echo aaykay_icon( 'pause', 'ic--sm pause-ic' ) . aaykay_icon( 'play', 'ic--sm play-ic' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?></button>
        </div>
<?php endif; ?>
      </div>
<?php endif; ?>
    </div>
  </section>
