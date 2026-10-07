<?php
/**
 * Home page: company.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_contact = aaykay_contact();
$aaykay_states  = count( aaykay_branches() );
// ", with branches in seven states and more than 100 clients" (parts left out when not set).
$aaykay_with  = array();
if ( $aaykay_states ) {
	$aaykay_with[] = 'branches in ' . aaykay_number_word( $aaykay_states ) . ( 1 === $aaykay_states ? ' state' : ' states' );
}
if ( '' !== aaykay_clients_phrase() ) {
	$aaykay_with[] = aaykay_clients_phrase() . ' clients';
}
$aaykay_today = $aaykay_with ? ', with ' . implode( ' and ', $aaykay_with ) : '';
?>
  <section class="section" id="company" aria-labelledby="company-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="company-title" data-reveal>Company</h2>
        <p class="sec-sub" data-reveal>Founded in Hyderabad in 2008 by an electrical engineer who still leads the business.</p>
      </div>
      <div class="company grid-12">
        <div class="founder">
          <div class="founder-photo">
            <img src="<?php aaykay_a( 'assets/img/founder-413.webp' ); ?>" srcset="<?php aaykay_a( 'assets/img/founder-413.webp' ); ?> 413w" width="413" height="447" sizes="(min-width: 900px) 28vw, 22rem" alt="Portrait of Abdul Kareem P, Managing Director of AAYKAY Electricals." loading="lazy" decoding="async">
          </div>
          <p class="founder-name">Abdul Kareem P</p>
          <p class="label">Founder &amp; Managing Director</p>
          <ul class="creds">
            <li class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#graduation-cap"/></svg>B.Tech, electrical engineering</li>
            <li class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#id-card"/></svg>Supervisor’s licence, Electrical Inspectorate</li>
            <li class="icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#history"/></svg>30 years in the trade</li>
          </ul>
        </div>
        <div class="company-body">
          <dl class="glance">
            <div><dt>Company</dt><dd>AAYKAY Electricals Private Limited</dd></div>
            <div><dt>Established</dt><dd>2008</dd></div>
            <div><dt>Head office</dt><dd><?php echo esc_html( aaykay_join( ', ', array( $aaykay_contact['area'], $aaykay_contact['city'] ) ) ); ?></dd></div>
<?php if ( $aaykay_states ) : ?>
            <div><dt>Branches</dt><dd><?php echo esc_html( $aaykay_states . ( 1 === $aaykay_states ? ' state' : ' states' ) ); ?></dd></div>
<?php endif; ?>
          </dl>
          <p>Abdul Kareem P was Head of Operations at Naseer Electricals in Hyderabad before starting A K Electricals in 2008. His 30 years in the trade cover base-build, IT workspaces, labs, data centres, hospitals, industrial and high-rise projects.</p>
          <p>Repeat clients include HDFC Bank, Amazon, Accenture, Microsoft, Tech Mahindra, Cognizant, AMD, NCR and D. E. Shaw &amp; Co.</p>
          <figure class="pull">
            <blockquote><p>“Listen hard, change fast.”</p></blockquote>
            <figcaption>Abdul Kareem P, on how he asks the team to work</figcaption>
          </figure>
          <ol class="timeline">
            <li><span class="tl-year">2008</span><p>A K Electricals starts in Hyderabad as a small electrical contractor for residential and commercial clients.</p></li>
            <li><span class="tl-year">2012</span><p>Merges with AayKay Electrical Enterprises. Branches follow in Bengaluru and Chennai.</p></li>
            <li><span class="tl-year">Today</span><p>AAYKAY Electricals Private Limited: ISO 9001:2015 and ISO 45001:2018 certified<?php echo esc_html( $aaykay_today ); ?>.</p></li>
          </ol>
        </div>
      </div>
    </div>
  </section>
