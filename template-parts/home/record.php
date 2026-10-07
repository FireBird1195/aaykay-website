<?php
/**
 * Home page: project record. Headings: Homepage content > Project record.
 * Rows: Dashboard > Projects. Firms: Dashboard > Architects & consultants.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_values = aaykay_show_values();
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
      <p class="record-status" id="record-status" aria-live="polite"></p>
      <div class="table-scroll" role="region" aria-labelledby="record-caption" tabindex="0">
        <table class="record" id="record-table">
          <caption id="record-caption" class="visually-hidden">AAYKAY project record: project, location, built-up area, floors, <?php echo $aaykay_values ? 'AAYKAY order value ' : ''; ?>and design team</caption>
          <thead>
            <tr>
              <th scope="col">Project</th>
              <th scope="col">Location</th>
              <th scope="col" class="num">Area (sq ft)</th>
              <th scope="col" class="floors">Floors</th>
<?php if ( $aaykay_values ) : ?>
              <th scope="col" class="num">Order value</th>
<?php endif; ?>
              <th scope="col" class="team">Architect / electrical consultant</th>
            </tr>
          </thead>
          <tbody id="record-rows">
            <?php echo aaykay_render_record_rows( '            ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

          </tbody>
        </table>
      </div>
      <p class="scroll-hint">Scroll the table sideways to see every column.</p>
      <div class="record-more">
        <button class="btn btn--outline" type="button" id="record-more" aria-controls="record-table" hidden>Show all projects</button>
      </div>

      <div class="firms grid-12">
        <h3><?php aaykay_e( 'record', 'firms_heading' ); ?></h3>
        <ul class="firm-list">
          <?php echo aaykay_render_firms( '          ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

        </ul>
      </div>
    </div>
  </section>
