<?php
/**
 * Home page: project record.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
?>
  <section class="section" id="record" aria-labelledby="record-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="record-title" data-reveal>Project record</h2>
        <p class="sec-sub" data-reveal>Scale, order value and design team for each project. Order values are the value of AAYKAY’s electrical work order.</p>
      </div>

      <div class="filters" role="group" aria-label="Filter projects by sector" hidden>
        <?php echo aaykay_render_chips( '        ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

      </div>
      <p class="record-status" id="record-status" aria-live="polite"></p>
      <div class="table-scroll" role="region" aria-labelledby="record-caption" tabindex="0">
        <table class="record" id="record-table">
          <caption id="record-caption" class="visually-hidden">AAYKAY project record: project, location, built-up area, floors, AAYKAY order value and design team</caption>
          <thead>
            <tr>
              <th scope="col">Project</th>
              <th scope="col">Location</th>
              <th scope="col" class="num">Area (sq ft)</th>
              <th scope="col" class="floors">Floors</th>
              <th scope="col" class="num">Order value</th>
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
        <h3>Architects, consultants and PMCs we have worked with</h3>
        <ul class="firm-list">
          <?php echo aaykay_render_firms( '          ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

        </ul>
      </div>
    </div>
  </section>
