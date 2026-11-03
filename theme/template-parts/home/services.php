<?php
/**
 * Credentials page (was Our Capabilities; client changes "WEB CUT 1", Nov 2026: the
 * capabilities are long, several paragraphs each, so a numbered index sits beside them (sticky on
 * desktop), each capability: icon and title, then the client's paragraphs in one text style; with
 * JavaScript only the first shows, and a Read more / Show less button sits after the text). Introduction, strengths and the capability cards (final client
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
<?php if ( '' !== aaykay_t( 'services', 'lead' ) || $aaykay_pillars ) : ?>
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
<?php endif; ?>
      </div>

<?php if ( $aaykay_scope ) : ?>
      <div class="scope" id="capabilities">
<?php if ( '' !== aaykay_t( 'services', 'scope_heading' ) ) : ?>
        <div class="subhead">
          <h3><?php aaykay_e( 'services', 'scope_heading' ); ?></h3>
        </div>
<?php endif; ?>
        <div class="cap-layout">
          <nav class="cap-index" aria-label="<?php echo esc_attr( aaykay_t( 'services', 'heading' ) ); ?>">
            <ol>
<?php foreach ( $aaykay_scope as $aaykay_i => $aaykay_s ) : ?>
              <li><a href="#cap-<?php echo esc_attr( $aaykay_i + 1 ); ?>"><span class="cap-num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $aaykay_i + 1 ) ); ?></span><?php echo esc_html( aaykay_tokens( $aaykay_s['title'] ) ); ?></a></li>
<?php endforeach; ?>
            </ol>
          </nav>
          <div class="cap-list">
<?php foreach ( $aaykay_scope as $aaykay_i => $aaykay_s ) : ?>
<?php
	$aaykay_paras = aaykay_paragraphs( trim( aaykay_tokens( $aaykay_s['text'] ) ) );
	$aaykay_url   = isset( $aaykay_s['link_url'] ) ? esc_url( trim( $aaykay_s['link_url'] ), array( 'http', 'https' ) ) : '';
	$aaykay_label = isset( $aaykay_s['link_label'] ) ? trim( aaykay_tokens( $aaykay_s['link_label'] ) ) : '';
	$aaykay_title = aaykay_tokens( $aaykay_s['title'] );
?>
            <article class="cap" id="cap-<?php echo esc_attr( $aaykay_i + 1 ); ?>" aria-labelledby="cap-<?php echo esc_attr( $aaykay_i + 1 ); ?>-title">
              <h3 class="cap-title" id="cap-<?php echo esc_attr( $aaykay_i + 1 ); ?>-title"><?php echo aaykay_icon( $aaykay_s['icon'], 'cap-ic' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><span><?php echo esc_html( $aaykay_title ); ?></span></h3>
<?php if ( $aaykay_paras ) : ?>
              <div class="cap-text" id="cap-<?php echo esc_attr( $aaykay_i + 1 ); ?>-text">
<?php foreach ( $aaykay_paras as $aaykay_j => $aaykay_para ) : ?>
                <p<?php echo $aaykay_j ? ' class="cap-extra"' : ''; ?>><?php echo esc_html( $aaykay_para ); ?></p>
<?php endforeach; ?>
              </div>
<?php if ( count( $aaykay_paras ) > 1 ) : ?>
              <button class="cap-toggle" type="button" aria-expanded="false" aria-controls="cap-<?php echo esc_attr( $aaykay_i + 1 ); ?>-text" hidden><span class="cap-toggle-more">Read more</span><span class="cap-toggle-less">Show less</span><?php echo aaykay_icon( 'chevron-down', 'ic--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><span class="visually-hidden"> about <?php echo esc_html( $aaykay_title ); ?></span></button>
<?php endif; ?>
<?php else : ?>
              <?php echo aaykay_pending( 'What AAYKAY offers in ' . $aaykay_title . ', in a few lines', 'scope-pending' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
<?php if ( '' !== $aaykay_url ) : ?>
              <p class="scope-link">For more information click <a href="<?php echo $aaykay_url; // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url() above. ?>" target="_blank" rel="noopener">here<span class="visually-hidden"> (more about <?php echo esc_html( $aaykay_title ); ?>, opens in a new tab)</span></a>.</p>
<?php elseif ( '' !== $aaykay_label && '1' === (string) aaykay_setting( 'show_pending' ) ) : ?>
              <p class="scope-link">For more information click <span class="scope-link-todo" title="Link address to come">here</span>.</p>
<?php endif; ?>
            </article>
<?php endforeach; ?>
          </div>
        </div>
      </div>
<?php endif; ?>
    </div>
  </section>
