<?php
/**
 * The home page. Its sections are listed in aaykay_page_registry() (inc/pages.php); the
 * other pages reuse the remaining sections in template-parts/home/ (aaykay-page.php).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">

<?php
foreach ( aaykay_page_sections( 'home' ) as $aaykay_part ) {
	get_template_part( 'template-parts/home/' . $aaykay_part );
	echo "\n";
}
aaykay_page_end();
?>
</main>

<?php
get_footer();
