<?php
/**
 * Home page: contact.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_c = aaykay_contact();
?>
  <section class="section section--dark grain contact" id="contact" aria-labelledby="contact-title">
    <div class="wrap grid-12 contact-grid">
      <div class="contact-info">
        <h2 class="h2" id="contact-title" data-reveal>Start a project</h2>
        <p class="lead">Tell us the scope, location and timeline, and we’ll come back to you.</p>
        <dl class="contact-dl">
          <div><dt class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#map-pin"/></svg>Head office</dt><dd><?php echo esc_html( $aaykay_c['street'] ); ?>,<br><?php echo esc_html( $aaykay_c['area'] . ', ' . $aaykay_c['city'] . ' ' . $aaykay_c['postcode'] . ', ' . $aaykay_c['state'] ); ?><br><a href="<?php echo esc_url( $aaykay_c['maps_url'] ); ?>" target="_blank" rel="noopener">Open in Google Maps</a></dd></div>
          <div><dt class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#phone"/></svg>Phone</dt><dd><a href="tel:<?php echo esc_attr( $aaykay_c['tel'] ); ?>" id="c-phone"><?php echo esc_html( $aaykay_c['phone'] ); ?></a><button class="copy-btn" type="button" data-copy="<?php echo esc_attr( $aaykay_c['phone'] ); ?>" data-copy-target="c-phone" title="Copy" hidden><svg class="ic ic--sm copy-ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#copy"/></svg><svg class="ic ic--sm check-ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#check"/></svg><span class="visually-hidden">Copy phone number</span></button></dd></div>
          <div><dt class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#mail"/></svg>Email</dt><dd><a href="mailto:<?php echo esc_attr( $aaykay_c['email'] ); ?>" id="c-email"><?php echo esc_html( $aaykay_c['email'] ); ?></a><button class="copy-btn" type="button" data-copy="<?php echo esc_attr( $aaykay_c['email'] ); ?>" data-copy-target="c-email" title="Copy" hidden><svg class="ic ic--sm copy-ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#copy"/></svg><svg class="ic ic--sm check-ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#check"/></svg><span class="visually-hidden">Copy email address</span></button></dd></div>
          <div><dt class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#map"/></svg>Branches</dt><dd><?php echo esc_html( implode( ' · ', aaykay_branches() ) ); ?></dd></div>
        </dl>
        <p class="visually-hidden" id="copy-status" role="status"></p>
      </div>

      <form class="contact-form" id="enquiry" data-email="<?php echo esc_attr( $aaykay_c['email'] ); ?>" data-phone="<?php echo esc_attr( $aaykay_c['phone'] ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <h3>Project enquiry</h3>
        <input type="hidden" name="action" value="aaykay_enquiry">
        <input type="hidden" name="elapsed" value="">
        <div class="visually-hidden" aria-hidden="true"><label for="f-website">Leave this empty</label><input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
        <div class="form-grid">
          <div class="field">
            <label for="f-name">Name <span class="req" aria-hidden="true">*</span></label>
            <input id="f-name" name="name" type="text" autocomplete="name" required data-label="name" aria-describedby="f-name-error">
            <p class="field-error" id="f-name-error"></p>
          </div>
          <div class="field">
            <label for="f-company">Company <span class="req" aria-hidden="true">*</span></label>
            <input id="f-company" name="company" type="text" autocomplete="organization" required data-label="company name" aria-describedby="f-company-error">
            <p class="field-error" id="f-company-error"></p>
          </div>
          <div class="field">
            <label for="f-email">Work email <span class="req" aria-hidden="true">*</span></label>
            <input id="f-email" name="email" type="email" autocomplete="email" required data-label="email address" aria-describedby="f-email-error">
            <p class="field-error" id="f-email-error"></p>
          </div>
          <div class="field">
            <label for="f-phone">Phone</label>
            <input id="f-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel">
          </div>
          <div class="field">
            <label for="f-type">Project type</label>
            <select id="f-type" name="type">
              <option value="">Select one</option>
<?php foreach ( aaykay_project_types() as $aaykay_type ) : ?>
              <option><?php echo esc_html( $aaykay_type ); ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="f-city">City</label>
            <input id="f-city" name="city" type="text" autocomplete="address-level2">
          </div>
          <div class="field field--full">
            <label for="f-message">Scope and timeline <span class="req" aria-hidden="true">*</span></label>
            <textarea id="f-message" name="message" required data-label="project scope" aria-describedby="f-message-error"></textarea>
            <p class="field-error" id="f-message-error"></p>
          </div>
        </div>
        <div class="form-foot">
          <p class="form-note">To share drawings or a BOQ, email them to <a href="mailto:<?php echo esc_attr( $aaykay_c['email'] ); ?>"><?php echo esc_html( $aaykay_c['email'] ); ?></a>. Fields marked * are required.</p>
          <button class="btn btn--primary" type="submit">Send enquiry <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M1.5 8h12M9 3.5 13.5 8 9 12.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></button>
        </div>
        <p class="form-status" id="form-status" role="status"><?php echo esc_html( aaykay_enquiry_status_text() ); ?></p>
      </form>
    </div>
  </section>
