<?php
/**
 * Any other page WordPress may show, such as a privacy policy page. The website itself
 * is front-page.php.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">
<?php
while ( have_posts() ) :
	the_post();
	?>
  <section class="section section--dark grain page-intro" aria-labelledby="page-title">
    <div class="wrap">
      <h1 class="h2" id="page-title"><?php the_title(); ?></h1>
    </div>
  </section>
  <section class="section">
    <div class="wrap prose">
      <?php the_content(); ?>
    </div>
  </section>
	<?php
endwhile;
?>
</main>

<?php
get_footer();
