<?php
/**
 * Home page: what we do + sectors.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
?>
  <section class="section" id="services" aria-labelledby="intro-title">
    <div class="wrap">
      <div class="intro grid-12">
        <h2 class="h2" id="intro-title" data-reveal>What we do</h2>
        <div class="intro-body">
          <p class="lead" data-reveal>We take on the complete electrical package: containment, cabling, panels, lighting, earthing and critical power. We coordinate it in Revit, install it to written method statements and test it with our own commissioning team before handover.</p>
          <div class="pillars">
            <div class="pillar">
              <h3 class="icon-label"><svg class="ic ic--lg" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#drafting-compass"/></svg>Shop drawings &amp; BIM</h3>
              <p>GFC review, shop and GA drawings, and MEP coordination in Autodesk Revit and AutoCAD.</p>
            </div>
            <div class="pillar">
              <h3 class="icon-label"><svg class="ic ic--lg" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#gauge"/></svg>Testing &amp; commissioning</h3>
              <p>Our own team and test instruments for megger, IR, continuity, load and integrated system tests.</p>
            </div>
            <div class="pillar">
              <h3 class="icon-label"><svg class="ic ic--lg" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#shield-check"/></svg>Site safety</h3>
              <p>Dedicated safety staff, HIRA risk assessments and an emergency response team on call 24/7.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="scope">
        <div class="subhead">
          <h3>What we install</h3>
        </div>
        <div class="scope-grid">
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#rows-3"/></svg>Containment</h4><p>Conduit in slabs, walls and floors; sleeves and back boxes; GI cable trays, ladders, trunking and raceways.</p></div>
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#cable"/></svg>Wiring &amp; cabling</h4><p>Power and lighting wiring, DB-to-equipment wiring, HT/LT cable laying, dressing, tagging, glanding and termination.</p></div>
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#inspection-panel"/></svg>Panels &amp; distribution</h4><p>LT panels and capacitor banks, MDBs, SMDBs and DBs, control wiring and panel earthing.</p></div>
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#arrow-up-from-line"/></svg>Busducts &amp; rising mains</h4><p>Busbar trunking and rising mains, supplied through OEM channels and installed to approved GA drawings.</p></div>
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#lightbulb"/></svg>Lighting &amp; accessories</h4><p>LED fixtures, lighting management systems, switches and sockets, fans and junction boxes.</p></div>
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#battery-charging"/></svg>Critical power</h4><p>UPS and battery installation, server-room electrical works, AC unit connections, pumps and motors.</p></div>
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#earth-ground"/></svg>Earthing &amp; protection</h4><p>Earth pits and grids, GI and copper strip, surge protection devices and lightning arresters.</p></div>
          <div class="scope-item"><h4 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#gauge"/></svg>Testing</h4><p>Megger, IR and continuity tests, panel load tests, circuit verification and HT/LT equipment testing.</p></div>
        </div>
      </div>

      <div class="sectors" id="sectors">
        <div class="sectors-head">
          <h3 class="label">Sectors</h3>
          <p>Select a sector to see its projects.</p>
        </div>
        <ul class="sector-list">
          <?php echo aaykay_render_sector_rows( '          ' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer. ?>

        </ul>
      </div>
    </div>
  </section>
