<?php
/**
 * Projects page: the project list (alphabetical, numbered like spreadsheet rows; the numbers
 * restart for each sector filter, site.js initRecord). Headings: Homepage content > Project record.
 * Rows: Dashboard > Projects, alphabetical.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
?>
  <section class="section" id="record" aria-labelledby="record-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="record-title" data-reveal><?php aaykay_e( 'record', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'record', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'record', 'sub' ); ?></p>
<?php endif; ?>
      </div>

      <div class="filters" role="group" aria-label="Filter projects by sector" hidden>
        <?php echo aaykay_render_chips( '        ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

      </div>
      <p class="record-status visually-hidden" id="record-status" aria-live="polite"></p>
      <p class="scroll-cue" aria-hidden="true">More columns <span class="scroll-cue-arrow">→</span> scroll sideways</p>
      <div class="table-scroll" role="region" aria-labelledby="record-caption" tabindex="0">
        <table class="record" id="record-table">
          <caption id="record-caption" class="visually-hidden">AAYKAY project list, as in AAYKAY’s list of executed and ongoing projects</caption>
          <thead>
            <tr>
              <th scope="col" class="sno">No.</th>
              <?php foreach ( aaykay_record_columns() as $aaykay_key => $aaykay_label ) : ?>
              <th scope="col" class="c-<?php echo esc_attr( $aaykay_key ); ?>"><?php echo esc_html( $aaykay_label ); ?></th>
<?php endforeach; ?>
            </tr>
          </thead>
          <tbody id="record-rows">
            <?php echo aaykay_render_record_rows( '            ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

          </tbody>
        </table>
      </div>

      <div class="record-more">
        <button class="btn btn--outline" type="button" id="record-more" aria-controls="record-table" hidden>Show all projects</button>
      </div>
    </div>
  </section>
