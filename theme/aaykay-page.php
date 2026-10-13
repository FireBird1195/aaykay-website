<?php
/**
 * The website's inner pages (About Us, Our Capabilities, Projects…). The page title in the
 * existing page-intro band (as on any other page), then the page's sections, reused from
 * template-parts/home/. Which sections: aaykay_page_registry() in inc/pages.php.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

$aaykay_key  = aaykay_current_page_key();
$aaykay_reg  = aaykay_page_registry();
$aaykay_tabs = array();
// The Projects pages share a row of links to each other (Project record, Clients…).
$aaykay_parent = isset( $aaykay_reg[ $aaykay_key ] ) && $aaykay_reg[ $aaykay_key ][2] ? $aaykay_reg[ $aaykay_key ][2] : $aaykay_key;
foreach ( aaykay_menu() as $aaykay_item ) {
	if ( $aaykay_item[0] === $aaykay_parent && $aaykay_item[3] ) {
		$aaykay_tabs = array_merge( array( array( $aaykay_item[0], $aaykay_item[1], $aaykay_item[2] ) ), $aaykay_item[3] );
	}
}

get_header();
?>

<main id="main">
  <section class="section section--dark grain page-intro" aria-labelledby="page-title">
    <div class="wrap">
      <h1 class="h2" id="page-title"><?php echo esc_html( aaykay_page_title( $aaykay_key ) ); ?></h1>
<?php if ( $aaykay_tabs ) : ?>
      <nav class="page-tabs" aria-label="<?php echo esc_attr( aaykay_page_title( $aaykay_parent ) ); ?>">
<?php foreach ( $aaykay_tabs as $aaykay_tab ) : ?>
        <a class="chip" href="<?php echo esc_url( $aaykay_tab[2] ); ?>"<?php echo $aaykay_tab[0] === $aaykay_key ? ' aria-current="page"' : ''; ?>><span class="chip-label"><?php echo esc_html( $aaykay_tab[1] ); ?></span></a>
<?php endforeach; ?>
      </nav>
<?php endif; ?>
    </div>
  </section>

<?php
foreach ( aaykay_page_sections( $aaykay_key ) as $aaykay_part ) {
	get_template_part( 'template-parts/home/' . $aaykay_part );
	echo "\n";
}
?>
</main>

<?php
get_footer();
