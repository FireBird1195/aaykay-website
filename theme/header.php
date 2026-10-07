<?php
/**
 * Document head, skip link, site header and the phone menu.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact = aaykay_contact();
?>
<!doctype html>
<html lang="en-IN" class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" data-state="top">
  <div class="wrap header-row">
    <a class="brand" href="<?php echo esc_attr( aaykay_section_url( 'top' ) ); ?>">
      <?php echo aaykay_brand_lockup( true ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the function. ?>
      <span class="visually-hidden">, back to top</span>
    </a>
    <nav class="site-nav" aria-label="Primary">
      <ul class="nav-list">
        <?php echo aaykay_render_nav( 'header', '        ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

      </ul>
    </nav>
    <a class="btn btn--sm btn--primary header-cta" href="<?php echo esc_attr( aaykay_section_url( 'contact' ) ); ?>"><?php aaykay_e( 'brand', 'header_cta' ); ?></a>
    <a class="btn btn--sm btn--outline-light menu-fallback" href="#footer-nav">Menu</a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="mobile-menu">
      <span class="menu-icon" aria-hidden="true"></span><span class="visually-hidden">Menu</span>
    </button>
  </div>
</header>

<div class="mobile-menu" id="mobile-menu" hidden>
  <nav aria-label="Mobile">
    <ul class="m-links">
      <?php echo aaykay_render_nav( 'mobile', '      ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

    </ul>
    <div class="m-foot">
      <p>Head office: <?php echo esc_html( aaykay_join( ', ', array( $aaykay_contact['area'], $aaykay_contact['city'] ) ) ); ?></p>
      <p><a href="tel:<?php echo esc_attr( $aaykay_contact['tel'] ); ?>"><?php echo esc_html( $aaykay_contact['phone'] ); ?></a> · <a href="mailto:<?php echo esc_attr( $aaykay_contact['email'] ); ?>"><?php echo esc_html( $aaykay_contact['email'] ); ?></a></p>
    </div>
  </nav>
</div>
