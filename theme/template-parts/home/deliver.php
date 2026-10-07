<?php
/**
 * Home page: how we deliver.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
?>
  <section class="section" id="deliver" aria-labelledby="deliver-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="deliver-title" data-reveal>How we deliver</h2>
        <p class="sec-sub" data-reveal>Five stages, managed by our own project team, with the schedule risks dealt with first.</p>
      </div>

      <div class="insight grid-12">
        <p class="insight-statement" data-reveal>Electrical packages rarely slip on site. <em>They slip waiting on approvals and long-lead equipment, so that is where we start.</em></p>
        <div class="insight-panels">
          <div class="panel">
            <h3 class="label">Agreed at kickoff</h3>
            <ul class="ticks">
              <li>GFC drawings in hand before the kickoff meeting</li>
              <li>Samples and technical data sheets approved within 5 days</li>
              <li>Panel GA and shop drawings approved within one week</li>
              <li>Ceiling layouts, IT power and raceway needs, and HVAC power loads confirmed up front</li>
            </ul>
          </div>
          <div class="panel">
            <h3 class="label">Ordered early</h3>
            <ul class="ticks">
              <li>LT panels</li>
              <li>Light fixtures</li>
              <li>Lighting management systems</li>
              <li>Rising mains and busducts</li>
            </ul>
            <p class="panel-note">Order placement, submittals and approvals for each are closed on a fixed timeline.</p>
          </div>
        </div>
      </div>

      <ol class="stages">
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><svg class="ic stage-ic"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#drafting-compass"/></svg></span>
          <p class="stage-no">Stage 01</p>
          <h3>Design</h3>
          <p>We review the GFC drawings, then prepare shop and GA drawings to site conditions for approval by the PMC or consultant.</p>
          <ul class="tags"><li>GFC review</li><li>Shop drawings</li><li>Submittals</li></ul>
        </li>
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><svg class="ic stage-ic"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#package"/></svg></span>
          <p class="stage-no">Stage 02</p>
          <h3>Procurement</h3>
          <p>Final quantities go to procurement alongside design. Vendors are validated and material is checked against the BOQ.</p>
          <ul class="tags"><li>Vendor validation</li><li>ERP</li><li>Material tracking</li></ul>
        </li>
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><svg class="ic stage-ic"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#hard-hat"/></svg></span>
          <p class="stage-no">Stage 03</p>
          <h3>Construction</h3>
          <p>We secure the site, stage materials and carry out the works to approved drawings and method statements, under a risk assessment.</p>
          <ul class="tags"><li>Site logistics</li><li>Risk analysis</li><li>Checklists</li></ul>
        </li>
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><svg class="ic stage-ic"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#gauge"/></svg></span>
          <p class="stage-no">Stage 04</p>
          <h3>Testing &amp; commissioning</h3>
          <p>Functional and visual checks, quality and safety inspections, and test reports from our own commissioning team.</p>
          <ul class="tags"><li>Megger &amp; IR</li><li>Panel load</li><li>System test</li></ul>
        </li>
        <li class="stage">
          <span class="stage-node" aria-hidden="true"><svg class="ic stage-ic"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#key-round"/></svg></span>
          <p class="stage-no">Stage 05</p>
          <h3>Handover</h3>
          <p>As-built drawings, test reports and warranties. Snags are closed, the client’s team is trained, and a final walkthrough leads to sign-off.</p>
          <ul class="tags"><li>As-builts</li><li>Training</li><li>Sign-off</li></ul>
        </li>
      </ol>

      <div class="sheets grid-12">
        <div class="sheet">
          <p class="label">From our method statements</p>
          <h3 class="sheet-title">Standards our site teams work to</h3>
          <dl>
            <div><dt>Earth station resistance</dt><dd>2 ohms max., each</dd></div>
            <div><dt>Conduit saddle spacing</dt><dd>2.5 m horiz. · 1.5 m vert.</dd></div>
            <div><dt>Spare capacity in cable trays</dt><dd>20%</dd></div>
            <div><dt>Clear space behind LV panels</dt><dd>750 mm min.</dd></div>
            <div><dt>Buried cable depth</dt><dd>600 mm min., on 75 mm sand</dd></div>
            <div><dt>Switch / socket height above floor</dt><dd>1350 mm / 300 mm</dd></div>
          </dl>
          <p class="sheet-note">Unless the approved drawings or client specification say otherwise.</p>
        </div>
        <div class="sheet sheet--tools">
          <p class="label">Tools &amp; supply chain</p>
          <h3 class="sheet-title">Software, OEM channels and site tools</h3>
          <dl>
            <div><dt>Design</dt><dd>Autodesk Revit (BIM, MEP coordination) and AutoCAD (2D, quantity take-off)</dd></div>
            <div><dt>LT panels</dt><dd>Schneider, L&amp;T, ABB, Legrand</dd></div>
            <div><dt>Busducts &amp; rising mains</dt><dd>Schneider, L&amp;T, Legrand, EAE, C&amp;S</dd></div>
            <div><dt>Lighting management</dt><dd>Lutron, Crestron, Enlighten</dd></div>
            <div><dt>Light fixtures</dt><dd>Philips, Wipro, LT, ALW, XAL</dd></div>
            <div><dt>On site</dt><dd>Dust-free battery drilling with extraction, laser marking, hydraulic cable pulling, factory-made tray bends</dd></div>
          </dl>
        </div>
      </div>
    </div>
  </section>
