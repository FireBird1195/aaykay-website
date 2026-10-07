<?php
/**
 * Home page: selected work.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_cards = aaykay_featured_projects();
$aaykay_total = count( aaykay_projects() );
// "Five projects from a record of more than thirty, across hospitals, corporate offices and
// high-rise towers." The counts follow the content. The closing clause describes the
// launch cards (healthcare, enterprise, residential) and is left out if cards from other
// sectors are chosen, so the sentence never claims something the cards don't show.
$aaykay_record = $aaykay_total >= 20 ? 'more than ' . aaykay_number_word( (int) floor( $aaykay_total / 10 ) * 10 ) : (string) $aaykay_total;
$aaykay_intro  = $aaykay_cards
	? ucfirst( aaykay_number_word( count( $aaykay_cards ) ) ) . ' project' . ( 1 === count( $aaykay_cards ) ? '' : 's' ) . ' from a record of ' . $aaykay_record
	: 'A record of ' . $aaykay_record . ' projects';
$aaykay_card_sectors = array_unique( wp_list_pluck( $aaykay_cards, 'sector' ) );
if ( $aaykay_cards && ! array_diff( $aaykay_card_sectors, array( 'healthcare', 'enterprise', 'residential' ) ) ) {
	$aaykay_intro .= ', across hospitals, corporate offices and high-rise towers';
}
$aaykay_intro .= '.';
?>
  <section class="section section--dark grain" id="work" aria-labelledby="work-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="work-title" data-reveal>Selected work</h2>
        <p class="sec-sub" data-reveal><?php echo esc_html( $aaykay_intro ); ?></p>
      </div>
<?php if ( $aaykay_cards ) : ?>
      <div class="work-grid">
<?php
foreach ( $aaykay_cards as $aaykay_i => $aaykay_card ) {
	echo preg_replace( '/^/m', '        ', aaykay_render_card( $aaykay_card, $aaykay_i ) ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer.
}
?>
      </div>
<?php endif; ?>
      <div class="work-foot">
        <a class="text-link" href="#record">Full project record <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M8 1.5v12M3.5 9 8 13.5 12.5 9" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></a>
      </div>
    </div>
  </section>
