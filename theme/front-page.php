<?php
/**
 * The home page: the whole website is this one page.
 * Sections are in template-parts/home/, in the order they appear.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">

<?php
foreach ( array( 'hero', 'clients', 'services', 'work', 'record', 'photos', 'deliver', 'quality', 'company', 'letter', 'prequal', 'contact' ) as $aaykay_part ) {
	get_template_part( 'template-parts/home/' . $aaykay_part );
	echo "\n";
}
?>
</main>

<?php
get_footer();
