<?php
/**
 * Contact Us page: contact details (each phone number and the email with a copy button),
 * the enquiry form, and the branches with the map of India (inc/map.php). Every other page
 * ends with the same section without the map ($args['compact'], see aaykay_page_end()). Texts: Homepage
 * content > Contact Us. Address, phones, email, branch states: Site settings. Form
 * handling: inc/enquiry.php.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_c     = aaykay_contact();
$aaykay_note  = esc_html( aaykay_t( 'contact', 'note' ) );
// The email address in the note becomes a link.
if ( '' !== $aaykay_c['email'] ) {
	$aaykay_note = str_replace( esc_html( $aaykay_c['email'] ), '<a href="mailto:' . esc_attr( $aaykay_c['email'] ) . '">' . esc_html( $aaykay_c['email'] ) . '</a>', $aaykay_note );
}
// A link such as "Request pre-qualification documents" (About Us) arrives with ?type=…
$aaykay_want  = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only preselects a list option.
$aaykay_compact  = ! empty( $args['compact'] ); // At the end of the other pages: no branches map.
$aaykay_branches = $aaykay_compact ? array() : aaykay_branches();
$aaykay_map      = $aaykay_branches ? aaykay_branch_map() : '';
$aaykay_extra = array(
	array( 'careers', '' ),
	array( 'suppliers', '' ),
);
?>
  <section class="section section--dark grain contact<?php echo $aaykay_compact ? ' contact--footer' : ''; ?>" id="contact" aria-labelledby="contact-title">
    <div class="wrap grid-12 contact-grid">
      <div class="contact-info">
        <h2 class="h2" id="contact-title" data-reveal><?php aaykay_e( 'contact', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'contact', 'lead' ) ) : ?>
        <p class="lead"><?php aaykay_e( 'contact', 'lead' ); ?></p>
<?php endif; ?>
        <dl class="contact-dl">
          <div><dt class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#map-pin"/></svg>Head office</dt><dd><?php echo '' !== $aaykay_c['street'] ? esc_html( $aaykay_c['street'] ) . ',<br>' : ''; ?><?php echo esc_html( aaykay_join( ', ', array( $aaykay_c['area'], aaykay_join( ' ', array( $aaykay_c['city'], $aaykay_c['postcode'] ) ), $aaykay_c['state'] ) ) ); ?><?php if ( '' !== $aaykay_c['maps_url'] ) : ?><br><a href="<?php echo esc_url( $aaykay_c['maps_url'] ); ?>" target="_blank" rel="noopener">Open in Google Maps</a><?php endif; ?></dd></div>
          <div><dt class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#phone"/></svg>Phone</dt><dd><span class="copy-row"><a href="tel:<?php echo esc_attr( $aaykay_c['tel'] ); ?>" id="c-phone"><?php echo esc_html( $aaykay_c['phone'] ); ?></a><?php echo aaykay_copy_button( $aaykay_c['phone'], 'c-phone', 'phone number' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?></span><?php if ( '' !== $aaykay_c['phone2'] ) : ?><span class="copy-row"><a href="tel:<?php echo esc_attr( $aaykay_c['tel2'] ); ?>" id="c-phone2"><?php echo esc_html( $aaykay_c['phone2'] ); ?></a><?php echo aaykay_copy_button( $aaykay_c['phone2'], 'c-phone2', 'phone number' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?></span><?php endif; ?></dd></div>
          <div><dt class="label icon-label"><svg class="ic" aria-hidden="true"><use href="<?php aaykay_a( 'assets/icons/icons.svg' ); ?>#mail"/></svg>Email</dt><dd><span class="copy-row"><a href="mailto:<?php echo esc_attr( $aaykay_c['email'] ); ?>" id="c-email"><?php echo esc_html( $aaykay_c['email'] ); ?></a><?php echo aaykay_copy_button( $aaykay_c['email'], 'c-email', 'email address' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper. ?></span></dd></div>
        </dl>
        <p class="visually-hidden" id="copy-status" role="status"></p>
<?php
foreach ( $aaykay_extra as $aaykay_x ) :
	$aaykay_text  = aaykay_t( 'contact', $aaykay_x[0] . '_text' );
	$aaykay_email = sanitize_email( aaykay_t( 'contact', $aaykay_x[0] . '_email' ) );
	if ( '' === $aaykay_text ) {
		continue;
	}
	?>
        <div class="contact-extra">
          <h3 class="label"><?php aaykay_e( 'contact', $aaykay_x[0] . '_heading' ); ?></h3>
          <p><?php echo esc_html( $aaykay_text ); ?><?php echo $aaykay_email ? ' <a href="mailto:' . esc_attr( $aaykay_email ) . '">' . esc_html( $aaykay_email ) . '</a>' : ''; ?><?php echo '' !== $aaykay_x[1] ? ' ' . esc_html( $aaykay_x[1] ) : ''; ?></p>
        </div>
<?php endforeach; ?>
      </div>

      <form class="contact-form" id="enquiry" data-email="<?php echo esc_attr( $aaykay_c['email'] ); ?>" data-phone="<?php echo esc_attr( $aaykay_c['phone'] ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <h3><?php aaykay_e( 'contact', 'form_heading' ); ?></h3>
        <input type="hidden" name="action" value="aaykay_enquiry">
        <input type="hidden" name="elapsed" value="">
        <div class="visually-hidden" aria-hidden="true"><label for="f-website">Leave this empty</label><input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
        <div class="form-grid">
          <div class="field">
            <label for="f-name">Name <span class="req" aria-hidden="true">*</span></label>
            <input id="f-name" name="name" type="text" autocomplete="name" maxlength="100" required data-label="name" aria-describedby="f-name-error">
            <p class="field-error" id="f-name-error"></p>
          </div>
          <div class="field">
            <label for="f-company">Company <span class="req" aria-hidden="true">*</span></label>
            <input id="f-company" name="company" type="text" autocomplete="organization" maxlength="150" required data-label="company name" aria-describedby="f-company-error">
            <p class="field-error" id="f-company-error"></p>
          </div>
          <div class="field">
            <label for="f-email">Work email <span class="req" aria-hidden="true">*</span></label>
            <input id="f-email" name="email" type="email" autocomplete="email" maxlength="254" required data-label="email address" aria-describedby="f-email-error">
            <p class="field-error" id="f-email-error"></p>
          </div>
          <div class="field">
            <label for="f-phone">Phone</label>
            <input id="f-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="20" pattern="[+]?[0-9 \(\)\-]{7,20}" aria-describedby="f-phone-error">
            <p class="field-error" id="f-phone-error"></p>
          </div>
          <div class="field">
            <label for="f-type">Project type</label>
            <select id="f-type" name="type">
              <option value="">Select one</option>
<?php foreach ( aaykay_project_types() as $aaykay_type ) : ?>
              <option<?php selected( $aaykay_want, $aaykay_type ); ?>><?php echo esc_html( $aaykay_type ); ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="f-city">City</label>
            <input id="f-city" name="city" type="text" autocomplete="address-level2" maxlength="100" aria-describedby="f-city-error">
            <p class="field-error" id="f-city-error"></p>
          </div>
          <div class="field field--full">
            <label for="f-message">Scope and timeline <span class="req" aria-hidden="true">*</span></label>
            <textarea id="f-message" name="message" maxlength="5000" required data-label="project scope" aria-describedby="f-message-error"></textarea>
            <p class="field-error" id="f-message-error"></p>
          </div>
        </div>
        <div class="form-foot">
          <p class="form-note"><?php echo $aaykay_note; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?><?php if ( get_privacy_policy_url() ) : ?> <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">How we use your details</a>.<?php endif; ?></p>
          <button class="btn btn--primary" type="submit"><?php aaykay_e( 'contact', 'button' ); ?> <?php echo aaykay_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></button>
        </div>
        <p class="form-status" id="form-status" role="status"><?php echo esc_html( aaykay_enquiry_status_text() ); ?></p>
      </form>
    </div>
<?php if ( $aaykay_branches ) : ?>
    <div class="wrap branches" id="branches">
      <div class="sec-head">
        <h3 class="h2" id="branches-title"><?php aaykay_e( 'contact', 'branches_heading' ); ?></h3>
<?php if ( '' !== aaykay_t( 'contact', 'branches_sub' ) ) : ?>
        <p class="sec-sub"><?php aaykay_e( 'contact', 'branches_sub' ); ?></p>
<?php endif; ?>
      </div>
      <div class="branch-grid grid-12">
<?php if ( '' !== $aaykay_map ) : ?>
        <div class="branch-map"><?php echo $aaykay_map; // phpcs:ignore WordPress.Security.EscapeOutput -- built from the theme's own data, attributes escaped in aaykay_branch_map(). ?></div>
<?php endif; ?>
        <ul class="branch-list" aria-labelledby="branches-title">
<?php foreach ( $aaykay_branches as $aaykay_i => $aaykay_b ) : ?>
          <li class="<?php echo 0 === $aaykay_i ? 'is-hq' : ''; ?>"><span class="branch-pin" aria-hidden="true"></span><span class="branch-name"><?php echo esc_html( $aaykay_b ); ?></span><?php if ( 0 === $aaykay_i ) : ?> <span class="pq-tag">Head office<?php echo '' !== $aaykay_c['city'] ? ', ' . esc_html( $aaykay_c['city'] ) : ''; ?></span><?php endif; ?></li>
<?php endforeach; ?>
        </ul>
      </div>
    </div>
<?php endif; ?>
  </section>
