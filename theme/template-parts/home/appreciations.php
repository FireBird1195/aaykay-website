<?php
/**
 * Projects page: appreciation certificates and letters, each opening full size in the image
 * viewer, with Previous / Next through the set (template-parts/parts/doc-grid.php).
 * Content: Homepage content > Appreciations.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_docs = aaykay_rows( 'appreciations', 'docs' );
?>
  <section class="section section--dark grain" id="appreciations" aria-labelledby="appreciations-title">
    <div class="wrap">
      <div class="recog">
        <div class="subhead">
          <h2 id="appreciations-title"><?php aaykay_e( 'appreciations', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'appreciations', 'sub' ) ) : ?>
          <p><?php aaykay_e( 'appreciations', 'sub' ); ?></p>
<?php endif; ?>
        </div>
<?php get_template_part( 'template-parts/parts/doc-grid', null, array( 'docs' => $aaykay_docs ) ); ?>
      </div>
    </div>
  </section>
