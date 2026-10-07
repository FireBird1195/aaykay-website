<?php
/**
 * Page not found.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">
  <section class="section section--dark grain page-intro" aria-labelledby="page-title">
    <div class="wrap">
      <h1 class="h2" id="page-title">Page not found</h1>
      <p class="lead">This address doesn’t lead anywhere on the AAYKAY website. Everything is on the home page.</p>
      <p class="page-actions"><a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the home page <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a></p>
    </div>
  </section>
</main>

<?php
get_footer();
