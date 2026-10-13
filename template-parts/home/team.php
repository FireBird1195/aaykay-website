<?php
/**
 * Home page, About Us: the team. First the leadership: the founder and the second director
 * side by side, with the Managing Director's message (Homepage content > About Us: company,
 * timeline and leadership). Then the team, one card per person: photo, name, designation
 * (Homepage content > About Us: team). Only members with a name are shown; while there are
 * none, eight "to come" cards hold the space (Site settings > Placeholders).
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;
$aaykay_creds   = aaykay_rows( 'company', 'creds' );
$aaykay_photo   = aaykay_img( aaykay_c( 'company', 'founder_photo' ), array( 'sizes' => '(min-width: 900px) 22vw, 45vw' ) );
$aaykay_photo2  = aaykay_img( aaykay_c( 'company', 'person2_photo' ), array( 'sizes' => '(min-width: 900px) 22vw, 45vw' ) );
$aaykay_message = aaykay_paragraphs( aaykay_t( 'company', 'quote' ) );
$aaykay_members = array_values(
	array_filter(
		aaykay_section_on( 'team' ) ? aaykay_rows( 'team', 'members' ) : array(),
		function ( $m ) {
			return '' !== trim( (string) $m['name'] );
		}
	)
);
$aaykay_slots   = ( $aaykay_members || ! aaykay_section_on( 'team' ) ) ? 0 : ( '' !== aaykay_pending( 'x' ) ? 8 : 0 );
?>
  <section class="section" id="team" aria-labelledby="team-title">
    <div class="wrap">
      <div class="sec-head">
        <h2 class="h2" id="team-title" data-reveal><?php aaykay_e( 'team', 'heading' ); ?></h2>
<?php if ( '' !== aaykay_t( 'team', 'sub' ) ) : ?>
        <p class="sec-sub" data-reveal><?php aaykay_e( 'team', 'sub' ); ?></p>
<?php endif; ?>
      </div>

      <div class="subhead">
        <h3><?php aaykay_e( 'company', 'leadership_heading' ); ?></h3>
      </div>
      <div class="company leadership grid-12">
        <div class="founder">
<?php if ( '' !== $aaykay_photo ) : ?>
          <div class="founder-photo">
            <?php echo $aaykay_photo; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

          </div>
<?php endif; ?>
          <h4 class="founder-name"><?php aaykay_e( 'company', 'founder_name' ); ?></h4>
          <p class="label"><?php aaykay_e( 'company', 'founder_title' ); ?></p>
<?php if ( $aaykay_creds ) : ?>
          <ul class="creds">
<?php foreach ( $aaykay_creds as $aaykay_cr ) : ?>
            <li class="icon-label"><?php echo aaykay_icon( $aaykay_cr['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_icon(). ?><?php echo esc_html( aaykay_tokens( $aaykay_cr['text'] ) ); ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
<?php if ( '' !== aaykay_t( 'company', 'person2_name' ) ) : ?>
          <div class="person">
            <div class="founder-photo">
<?php if ( '' !== $aaykay_photo2 ) : ?>
              <?php echo $aaykay_photo2; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

<?php else : ?>
              <?php echo aaykay_pending( 'Portrait of ' . aaykay_t( 'company', 'person2_name' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
            </div>
            <h4 class="founder-name"><?php aaykay_e( 'company', 'person2_name' ); ?></h4>
            <p class="label"><?php aaykay_e( 'company', 'person2_title' ); ?></p>
<?php if ( '' !== aaykay_t( 'company', 'person2_text' ) ) : ?>
            <p class="person-text"><?php aaykay_e( 'company', 'person2_text' ); ?></p>
<?php endif; ?>
          </div>
<?php endif; ?>
        </div>
        <div class="company-body">
<?php if ( $aaykay_message ) : ?>
          <figure class="pull message">
            <blockquote>
<?php foreach ( $aaykay_message as $aaykay_i => $aaykay_para ) : ?>
              <p<?php echo $aaykay_i ? ' class="message-more"' : ''; ?>><?php echo esc_html( $aaykay_para ); ?></p>
<?php endforeach; ?>
            </blockquote>
<?php if ( '' !== aaykay_t( 'company', 'quote_by' ) ) : ?>
            <figcaption><?php aaykay_e( 'company', 'quote_by' ); ?></figcaption>
<?php endif; ?>
          </figure>
<?php else : ?>
          <?php echo aaykay_pending( 'The Managing Director’s message, in the wording he approves', 'pull' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
        </div>
      </div>

<?php if ( $aaykay_members || $aaykay_slots ) : ?>
      <div class="subhead team-subhead">
        <h3><?php aaykay_e( 'team', 'members_heading' ); ?></h3>
      </div>
      <ul class="team-grid">
<?php foreach ( $aaykay_members as $aaykay_m ) : ?>
<?php $aaykay_img = aaykay_img( $aaykay_m['photo'], array( 'sizes' => '(min-width: 900px) 16vw, (min-width: 560px) 30vw, 45vw' ) ); ?>
        <li class="team-member">
          <div class="founder-photo">
<?php if ( '' !== $aaykay_img ) : ?>
            <?php echo $aaykay_img; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_img(). ?>

<?php else : ?>
            <?php echo aaykay_pending( 'Photo' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?>

<?php endif; ?>
          </div>
          <h4 class="founder-name"><?php echo esc_html( $aaykay_m['name'] ); ?></h4>
<?php if ( '' !== trim( $aaykay_m['role'] ) ) : ?>
          <p class="label"><?php echo esc_html( $aaykay_m['role'] ); ?></p>
<?php endif; ?>
        </li>
<?php endforeach; ?>
<?php for ( $aaykay_n = 0; $aaykay_n < $aaykay_slots; $aaykay_n++ ) : ?>
        <li class="team-member is-pending">
          <div class="founder-photo"><?php echo aaykay_pending( 'Photo' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in aaykay_pending(). ?></div>
          <p class="founder-name">Name</p>
          <p class="label">Designation</p>
        </li>
<?php endfor; ?>
      </ul>
<?php endif; ?>
    </div>
  </section>
