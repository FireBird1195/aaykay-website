<?php
/**
 * The website's inner pages (About Us, Our Capabilities, Projects…): the page's
 * sections, reused from template-parts/home/. Which sections: aaykay_page_registry() in
 * inc/pages.php.
 *
 * The page title is the page's main heading for screen readers and search engines, but is
 * not shown as a large banner (final client brief, 13 Oct 2026: a page opens straight on
 * its content, not on a heading repeating the menu item). The header is solid on these
 * pages (site-header--solid), since they may open on a light section.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

$aaykay_key = aaykay_current_page_key();

get_header();
?>

<main id="main" class="page-main">
  <h1 class="visually-hidden"><?php echo esc_html( aaykay_page_title( $aaykay_key ) ); ?></h1>

<?php
foreach ( aaykay_page_sections( $aaykay_key ) as $aaykay_part ) {
	get_template_part( 'template-parts/home/' . $aaykay_part );
	echo "\n";
}
?>
</main>

<?php
get_footer();
