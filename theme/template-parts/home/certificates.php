<?php
/**
 * Quality & Safety page: the company's certificates, licences and registrations, scanned
 * from the company profile (client review of 13 Oct: "include all the certifications"),
 * each opening full size with Previous / Next through the set
 * (template-parts/parts/doc-grid.php). Content: Homepage content > Certifications & licences.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_docs = aaykay_rows( 'certificates', 'docs' );
if ( ! $aaykay_docs ) {
	return;
}
?>
  <section class="section section--dark grain" id="certificates" aria-labelledby="certificates-title">
    <div class="wrap">
      <div class="recog">
        <div class="subhead">
          <h2 id="certificates-title"><?php aaykay_e( 'certificates', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'certificates', 'sub' ) ) : ?>
          <p><?php aaykay_e( 'certificates', 'sub' ); ?></p>
<?php endif; ?>
        </div>
<?php get_template_part( 'template-parts/parts/doc-grid', null, array( 'docs' => $aaykay_docs ) ); ?>
      </div>
    </div>
  </section>
