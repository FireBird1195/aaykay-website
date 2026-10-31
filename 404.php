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
      <p class="lead">This address doesn’t lead anywhere on the AAYKAY website. The page may have moved; these are the website’s pages.</p>
      <nav class="page-tabs" aria-label="Website pages">
<?php
foreach ( aaykay_menu() as $aaykay_item ) :
	if ( 'about' === $aaykay_item[0] ) {
		continue; // A part of the home page, not a page of its own.
	}
	?>
        <a class="chip" href="<?php echo esc_url( $aaykay_item[2] ); ?>"><?php echo esc_html( $aaykay_item[1] ); ?></a>
<?php endforeach; ?>
      </nav>
      <p class="page-actions"><a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the home page <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a></p>
    </div>
  </section>
</main>

<?php
get_footer();
