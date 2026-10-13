<?php
/**
 * Our Capabilities page: introduction, strengths and the capability cards (final client
 * brief, 13 Oct 2026: "Our Capabilities", nine cards, Fire Safety ninth with a link to its
 * website). Content: Homepage content > Our Capabilities; card order = order in the editor.
 * The sectors moved to the Projects page (sectors.php).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_pillars = aaykay_rows( 'services', 'pillars' );
$aaykay_scope   = aaykay_rows( 'services', 'scope' );
?>
  <section class="section" id="services" aria-labelledby="intro-title">
    <div class="wrap">
      <div class="intro grid-12">
        <h2 class="h2" id="intro-title" data-reveal><?php aaykay_e( 'services', 'heading' ); ?></h2>
        <div class="intro-body">
<?php if ( '' !== aaykay_t( 'services', 'lead' ) ) : ?>
          <p class="lead" data-reveal><?php aaykay_e( 'services', 'lead' ); ?></p>
<?php endif; ?>
<?php if ( $aaykay_pillars ) : ?>
          <div class="pillars">
<?php foreach ( $aaykay_pillars as $aaykay_p ) : ?>
            <div class="pillar">
              <h3 class="icon-label"><?php echo aaykay_icon( $aaykay_p['icon'], 'ic--lg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( aaykay_tokens( $aaykay_p['title'] ) ); ?></h3>
              <p><?php echo esc_html( aaykay_tokens( $aaykay_p['text'] ) ); ?></p>
            </div>
<?php endforeach; ?>
          </div>
<?php endif; ?>
        </div>
      </div>

<?php if ( $aaykay_scope ) : ?>
      <div class="scope" id="capabilities">
        <div class="subhead">
          <h3><?php aaykay_e( 'services', 'scope_heading' ); ?></h3>
        </div>
        <div class="scope-grid<?php echo ( 0 === count( $aaykay_scope ) % 3 && 0 !== count( $aaykay_scope ) % 4 ) ? ' scope-grid--3' : ''; ?>">
<?php foreach ( $aaykay_scope as $aaykay_s ) : ?>
<?php
	$aaykay_text  = trim( aaykay_tokens( $aaykay_s['text'] ) );
	$aaykay_url   = isset( $aaykay_s['link_url'] ) ? esc_url( trim( $aaykay_s['link_url'] ), array( 'http', 'https' ) ) : '';
	$aaykay_label = isset( $aaykay_s['link_label'] ) ? trim( aaykay_tokens( $aaykay_s['link_label'] ) ) : '';
?>
          <div class="scope-item">
            <h4 class="icon-label"><?php echo aaykay_icon( $aaykay_s['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( aaykay_tokens( $aaykay_s['title'] ) ); ?></h4>
<?php if ( '' !== $aaykay_text ) : ?>
            <p><?php echo esc_html( $aaykay_text ); ?></p>
<?php else : ?>
            <?php echo aaykay_pending( 'What AAYKAY offers in ' . aaykay_tokens( $aaykay_s['title'] ) . ', in a few lines', 'scope-pending' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
<?php if ( '' !== $aaykay_url ) : ?>
            <p class="scope-link">For more information click <a href="<?php echo $aaykay_url; // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url() above. ?>" target="_blank" rel="noopener">here<span class="visually-hidden"> (more about <?php echo esc_html( aaykay_tokens( $aaykay_s['title'] ) ); ?>, opens in a new tab)</span></a>.</p>
<?php elseif ( '' !== $aaykay_label && '1' === (string) aaykay_setting( 'show_pending' ) ) : ?>
            <p class="scope-link">For more information click <span class="scope-link-todo" title="Link address to come">here</span>.</p>
<?php endif; ?>
          </div>
<?php endforeach; ?>
        </div>
      </div>
<?php endif; ?>
    </div>
  </section>
