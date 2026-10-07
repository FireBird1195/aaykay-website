<?php
/**
 * Home page: quality & safety.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
?>
  <section class="section section--dark grain" id="quality" aria-labelledby="quality-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="quality-title" data-reveal>Quality &amp; safety</h2>
        <p class="sec-sub" data-reveal>Certified to ISO 9001:2015 and ISO 45001:2018, and run day to day by dedicated quality and safety staff.</p>
      </div>

      <div class="qs grid-12">
        <div class="qs-text">
          <ul class="certs">
            <li class="cert"><svg class="ic ic--lg" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#badge-check"/></svg><span><b>ISO 9001:2015</b> Quality management</span></li>
            <li class="cert"><svg class="ic ic--lg" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#shield-check"/></svg><span><b>ISO 45001:2018</b> Occupational health &amp; safety</span></li>
          </ul>
          <dl class="qs-stats">
<?php
foreach ( array( 'res_quality' => 'Quality staff', 'res_safety' => 'Safety staff', 'res_sup' => 'Supervisors' ) as $aaykay_key => $aaykay_label ) :
	$aaykay_n = aaykay_setting( $aaykay_key );
	if ( '' === $aaykay_n ) {
		continue;
	}
	?>
            <div class="qs-stat"><dt><?php echo esc_html( $aaykay_label ); ?></dt><dd><?php echo esc_html( $aaykay_n ); ?></dd></div>
<?php endforeach; ?>
          </dl>
          <div class="qs-block">
            <h3 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#badge-check"/></svg>Quality</h3>
            <ul class="qs-list">
              <li><span class="qs-key">Agreed up front</span><span>QA requirements agreed with the client at the start of each project.</span></li>
              <li><span class="qs-key">QC supervisor</span><span>A quality control supervisor runs QA checks, reports non-conformities and works with client QA inspectors.</span></li>
              <li><span class="qs-key">Checklists</span><span>Work-type checklists kept current by the site team as execution progresses.</span></li>
              <li><span class="qs-key">Sign-off</span><span>Every system tested, commissioned and signed off by the PMC or client.</span></li>
            </ul>
          </div>
          <div class="qs-block">
            <h3 class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#shield-check"/></svg>Safety</h3>
            <ul class="qs-list">
              <li><span class="qs-key">24/7 response</span><span>An emergency response team on call 24/7.</span></li>
              <li><span class="qs-key">Reviews</span><span>Project safety reviews every alternate day, and a weekly safety report reviewed by top management.</span></li>
              <li><span class="qs-key">Risk &amp; permits</span><span>HIRA risk assessments, height and hot-work permits, and lock-out tag-out before live connections.</span></li>
              <li><span class="qs-key">Training &amp; welfare</span><span>Toolbox talks, PPE for every worker, monthly medical camps and tie-ups with local hospitals.</span></li>
            </ul>
          </div>
        </div>
        <div class="qs-photos">
          <figure data-reveal-media>
            <img src="<?php aaykay_a( 'assets/img/safety-toolbox-talk-667.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/safety-toolbox-talk-667.webp' ); ?> 667w" width="667" height="517" sizes="(min-width: 960px) 40vw, 100vw" alt="A large site workforce in hard hats and hi-vis vests seated for a toolbox talk." loading="lazy" decoding="async">
            <figcaption class="fig-chip"><b>Fig. 8</b>Toolbox talk with the site workforce</figcaption>
          </figure>
          <figure data-reveal-media>
            <img src="<?php aaykay_a( 'assets/img/safety-cpr-709.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/safety-cpr-480.webp' ); ?> 480w, <?php aaykay_a( 'assets/img/safety-cpr-709.webp' ); ?> 709w" width="709" height="548" sizes="(min-width: 960px) 20vw, 50vw" alt="Workers in PPE watching a CPR demonstration on site." loading="lazy" decoding="async">
            <figcaption class="fig-chip"><b>Fig. 9</b>CPR training</figcaption>
          </figure>
          <figure data-reveal-media>
            <img src="<?php aaykay_a( 'assets/img/safety-briefing-726.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/safety-briefing-480.webp' ); ?> 480w, <?php aaykay_a( 'assets/img/safety-briefing-726.webp' ); ?> 726w" width="726" height="547" sizes="(min-width: 960px) 20vw, 50vw" alt="A site crew in red helmets and orange vests at a safety briefing." loading="lazy" decoding="async">
            <figcaption class="fig-chip"><b>Fig. 10</b>Safety briefing</figcaption>
          </figure>
        </div>
      </div>

      <div class="kit">
        <div class="subhead">
          <h3>Our testing kit</h3>
          <p>Used by our in-house commissioning team</p>
        </div>
        <ul class="kit-list">
          <li>Multimeter</li><li>Insulation tester</li><li>Multi-function tester</li><li>Digital light meter</li>
          <li>Loop impedance tester</li><li>RCD tester</li><li>Torque wrench</li><li>Thermal imager</li>
          <li>Infrared thermometer</li><li>Phase sequence meter</li><li>Digital clamp meter</li><li>Earth tester</li>
        </ul>
      </div>

      <div class="recog">
        <div class="subhead">
          <h3>Appreciation</h3>
          <p>Select a document to view it</p>
        </div>
        <div class="doc-grid">
          <a class="doc" href="<?php aaykay_a( 'assets/img/doc-savills-ehs-1024.webp' ); ?>" data-lightbox="<?php aaykay_a( 'assets/img/doc-savills-ehs-1024.webp' ); ?>" data-caption="Savills EHS Certificate of Appreciation, best performer, November 2021 to February 2022.">
            <img src="<?php aaykay_a( 'assets/img/doc-savills-ehs-1024.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/doc-savills-ehs-480.webp' ); ?> 480w, <?php aaykay_a( 'assets/img/doc-savills-ehs-1024.webp' ); ?> 1024w" width="1024" height="732" sizes="104px" alt="Savills EHS Certificate of Appreciation awarded to an AAYKAY site team member." loading="lazy" decoding="async">
            <span>
              <span class="doc-title">Savills · EHS certificate of appreciation</span>
              <span class="doc-meta">“Best performer”, Nov 2021 – Feb 2022, awarded to an AAYKAY site team member.</span>
              <span class="doc-open"><svg class="ic ic--sm" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#maximize-2"/></svg>View certificate</span>
            </span>
          </a>
          <a class="doc" href="<?php aaykay_a( 'assets/img/doc-efswin-551.webp' ); ?>" data-lightbox="<?php aaykay_a( 'assets/img/doc-efswin-551.webp' ); ?>" data-caption="EFSWIN 2012, Electrical and Fire Safety Workshop in India and Exhibition, Hyderabad, November 2012.">
            <img src="<?php aaykay_a( 'assets/img/doc-efswin-551.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/doc-efswin-400.webp' ); ?> 400w, <?php aaykay_a( 'assets/img/doc-efswin-551.webp' ); ?> 551w" width="551" height="774" sizes="104px" alt="EFSWIN 2012 plaque presented to Mr. Abdul Kareem for invaluable support." loading="lazy" decoding="async">
            <span>
              <span class="doc-title">EFSWIN 2012 · Electrical &amp; Fire Safety Workshop in India</span>
              <span class="doc-meta">Organised by FSAI with IEEE IAS. Presented to Abdul Kareem P “for invaluable support”, Hyderabad, November 2012.</span>
              <span class="doc-open"><svg class="ic ic--sm" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#maximize-2"/></svg>View plaque</span>
            </span>
          </a>
          <a class="doc" href="<?php aaykay_a( 'assets/img/doc-omsai-617.webp' ); ?>" data-lightbox="<?php aaykay_a( 'assets/img/doc-omsai-617.webp' ); ?>" data-caption="Om Sai Intex, Gratitude 15 partner appreciation, 4 May 2019.">
            <img src="<?php aaykay_a( 'assets/img/doc-omsai-617.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/doc-omsai-400.webp' ); ?> 400w, <?php aaykay_a( 'assets/img/doc-omsai-617.webp' ); ?> 617w" width="617" height="774" sizes="104px" alt="Om Sai Intex Gratitude 15 appreciation presented to AayKay Electrical Enterprises, Bangalore." loading="lazy" decoding="async">
            <span>
              <span class="doc-title">Om Sai Intex · Partner appreciation</span>
              <span class="doc-meta">“Gratitude 15”, presented to AayKay Electrical Enterprises, Bangalore, May 2019.</span>
              <span class="doc-open"><svg class="ic ic--sm" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#maximize-2"/></svg>View plaque</span>
            </span>
          </a>
        </div>
      </div>
    </div>
  </section>
