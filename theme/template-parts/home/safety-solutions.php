<?php
/**
 * Our Capabilities page: AAYKAY Safety Solutions, with a link to its website.
 * Content: Homepage content > AAYKAY Safety Solutions. Until the description and the
 * address are supplied, "to come" placeholders mark them (Site settings > Placeholders).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_paras = aaykay_paragraphs( aaykay_t( 'safety_solutions', 'text' ) );
$aaykay_url   = esc_url( aaykay_t( 'safety_solutions', 'link_url' ) );
?>
  <section class="section section--white" id="safety-solutions" aria-labelledby="safety-solutions-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="safety-solutions-title" data-reveal><?php aaykay_e( 'safety_solutions', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'safety_solutions', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'safety_solutions', 'sub' ); ?></p>
<?php endif; ?>
      </div>
      <div class="prose">
<?php if ( $aaykay_paras ) : ?>
<?php foreach ( $aaykay_paras as $aaykay_para ) : ?>
        <p><?php echo esc_html( $aaykay_para ); ?></p>
<?php endforeach; ?>
<?php else : ?>
        <?php echo aaykay_pending( 'What AAYKAY Safety Solutions does, in a few lines' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
      </div>
<?php if ( '' !== $aaykay_url ) : ?>
      <p class="page-actions"><a class="btn btn--outline" href="<?php echo $aaykay_url; // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url() above. ?>" target="_blank" rel="noopener"><?php echo esc_html( '' !== aaykay_t( 'safety_solutions', 'link_label' ) ? aaykay_t( 'safety_solutions', 'link_label' ) : 'Visit the website' ); ?><span class="visually-hidden"> (opens in a new tab)</span> <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a></p>
<?php else : ?>
      <div class="prose page-actions"><?php echo aaykay_pending( 'Link to the AAYKAY Safety Solutions website' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?></div>
<?php endif; ?>
    </div>
  </section>
