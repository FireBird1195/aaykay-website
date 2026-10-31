<?php
/**
 * Keeping an eye on the live site.
 *
 *   - GET /wp-json/aaykay/v1/health answers {"status":"ok"} when WordPress and its database
 *     are working. Point an uptime monitor at it (OPERATIONS_RUNBOOK.md). It is never cached
 *     and says nothing else about the site.
 *   - Tools > Site Health gets the checks that matter for this website: enquiry email, failed
 *     enquiry emails, launch switches (search engines, placeholders) and the privacy notice.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'aaykay/v1',
			'/health',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true', // Public on purpose: it reveals nothing but "ok".
				'callback'            => function () {
					global $wpdb;
					$db_ok    = '1' === (string) $wpdb->get_var( 'SELECT 1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- a constant query that checks the connection; must not be cached.
					$response = new WP_REST_Response( array( 'status' => $db_ok ? 'ok' : 'error' ), $db_ok ? 200 : 503 );
					$response->header( 'Cache-Control', 'no-store, max-age=0' );
					$response->header( 'X-LiteSpeed-Cache-Control', 'no-cache' );
					return $response;
				},
			)
		);
	}
);

/** Enquiries received in the last $days days whose email notification failed. */
function aaykay_failed_enquiry_emails( $days = 30 ) {
	return get_posts(
		array(
			'post_type'      => 'aaykay_enquiry',
			'post_status'    => 'any',
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'date_query'     => array( array( 'after' => $days . ' days ago' ) ),
			'meta_key'       => '_aaykay_mailed', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin-only check, small table.
			'meta_value'     => '0', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- as above.
		)
	);
}

add_filter(
	'site_status_tests',
	function ( $tests ) {
		$result = function ( $label, $status, $description, $action = '' ) {
			return array(
				'label'       => $label,
				'status'      => $status, // good, recommended or critical.
				'badge'       => array(
					'label' => 'AAYKAY website',
					'color' => 'critical' === $status ? 'red' : ( 'good' === $status ? 'green' : 'orange' ),
				),
				'description' => '<p>' . esc_html( $description ) . '</p>',
				'actions'     => $action,
				'test'        => 'aaykay', // Replaced below by each check's own id.
			);
		};
		$link = function ( $url, $text ) {
			return '<p><a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a></p>';
		};

		$tests['direct']['aaykay_email'] = array(
			'label' => 'Enquiry email',
			'test'  => function () use ( $result ) {
				$failed = count( aaykay_failed_enquiry_emails() );
				if ( $failed ) {
					return $result( sprintf( '%d enquir%s in the last 30 days could not be emailed', $failed, 1 === $failed ? 'y' : 'ies' ), 'critical', 'They are saved under Enquiries; reply to them from there. Then fix sending: set up SMTP in wp-config.php (HOSTINGER_DEPLOYMENT_GUIDE.md, "Email") and send a test enquiry.', '<p><a href="' . esc_url( admin_url( 'edit.php?post_type=aaykay_enquiry' ) ) . '">Open Enquiries</a></p>' );
				}
				if ( ! aaykay_smtp_configured() ) {
					return $result( 'Enquiries are emailed with the server’s own mail, not a mailbox', 'recommended', 'Mail sent this way is often filtered as spam. Every enquiry is still saved under Enquiries. Set up SMTP with a mailbox (HOSTINGER_DEPLOYMENT_GUIDE.md, "Email").' );
				}
				return $result( 'Enquiries are emailed through a mailbox (SMTP)', 'good', 'No failed enquiry emails in the last 30 days.' );
			},
		);

		$tests['direct']['aaykay_launch'] = array(
			'label' => 'Launch switches',
			'test'  => function () use ( $result, $link ) {
				$open    = '1' === (string) get_option( 'blog_public' );
				$pending = '1' === (string) aaykay_setting( 'show_pending' );
				if ( $open && $pending ) {
					return $result( '“To come” placeholders are visible on the public website', 'critical', 'Search engines may index the site, and dashed “To come” boxes are still shown. Untick Site settings > Placeholders, or supply the missing content.', $link( admin_url( 'admin.php?page=aaykay-settings' ), 'Site settings' ) );
				}
				if ( ! $open ) {
					return $result( 'Search engines are asked not to index the website', 'recommended', 'Right before launch. On launch day untick Settings > Reading > “Discourage search engines” and submit the sitemap in Google Search Console.', $link( admin_url( 'options-reading.php' ), 'Settings > Reading' ) );
				}
				return $result( 'The website is open to search engines and shows no placeholders', 'good', 'Launch switches are set for a live site.' );
			},
		);

		$tests['direct']['aaykay_privacy'] = array(
			'label' => 'Privacy notice',
			'test'  => function () use ( $result, $link ) {
				$id = (int) get_option( 'wp_page_for_privacy_policy' );
				if ( $id && 'publish' === get_post_status( $id ) ) {
					return $result( 'The privacy notice is published', 'good', 'The enquiry form links to it.' );
				}
				return $result( 'The privacy notice is not published', 'recommended', 'The enquiry form collects personal details. Complete the draft privacy notice (Settings > Privacy), have it checked, and publish it; the form then links to it.', $link( admin_url( 'options-privacy.php' ), 'Settings > Privacy' ) );
			},
		);
		// Site Health builds element ids from 'test', so each check needs its own.
		foreach ( array( 'aaykay_email', 'aaykay_launch', 'aaykay_privacy' ) as $id ) {
			$inner                         = $tests['direct'][ $id ]['test'];
			$tests['direct'][ $id ]['test'] = function () use ( $inner, $id ) {
				$r         = $inner();
				$r['test'] = $id;
				return $r;
			};
		}
		return $tests;
	}
);
