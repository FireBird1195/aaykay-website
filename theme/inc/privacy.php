<?php
/**
 * Personal data held by the website: the enquiries (name, company, email, phone, city and
 * what the visitor wrote). Nothing else about visitors is stored; the theme sets no cookies.
 *
 *   - Tools > Export Personal Data and Tools > Erase Personal Data (WordPress's own privacy
 *     tools) find enquiries by the email address they were sent from, so a request to see or
 *     delete one's details can be answered in a few clicks (India's DPDP Act 2023 and the
 *     GDPR both give people these rights).
 *   - Site settings > "Delete enquiries after" (months): once set, enquiries older than that
 *     are deleted for good once a day. Empty keeps them until someone deletes them. How long
 *     to keep them is AAYKAY's decision and belongs in the privacy notice.
 *
 * @package aaykay
 */

defined( 'ABSPATH' ) || exit;

/** Enquiry IDs sent from an email address (any status, including the bin). */
function aaykay_enquiries_by_email( $email, $page = 1, $per_page = 50 ) {
	$email = sanitize_email( $email );
	if ( '' === $email ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'      => 'aaykay_enquiry',
			'post_status'    => 'any',
			'posts_per_page' => $per_page,
			'paged'          => max( 1, (int) $page ),
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_key'       => '_aaykay_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin-only privacy request, a handful of rows.
			'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- as above.
		)
	);
}

add_filter(
	'wp_privacy_personal_data_exporters',
	function ( $exporters ) {
		$exporters['aaykay-enquiries'] = array(
			'exporter_friendly_name' => 'Website enquiries',
			'callback'               => function ( $email, $page = 1 ) {
				$items = array();
				$ids   = aaykay_enquiries_by_email( $email, $page );
				foreach ( $ids as $id ) {
					$data = array(
						array(
							'name'  => 'Received',
							'value' => get_the_date( 'Y-m-d H:i', $id ),
						),
					);
					foreach ( aaykay_enquiry_fields() as $key => $f ) {
						$value = (string) get_post_meta( $id, '_aaykay_' . $key, true );
						if ( '' !== $value ) {
							$data[] = array(
								'name'  => $f[0],
								'value' => $value,
							);
						}
					}
					$items[] = array(
						'group_id'    => 'aaykay-enquiries',
						'group_label' => 'Website enquiries',
						'item_id'     => 'enquiry-' . $id,
						'data'        => $data,
					);
				}
				return array(
					'data' => $items,
					'done' => count( $ids ) < 50,
				);
			},
		);
		return $exporters;
	}
);

add_filter(
	'wp_privacy_personal_data_erasers',
	function ( $erasers ) {
		$erasers['aaykay-enquiries'] = array(
			'eraser_friendly_name' => 'Website enquiries',
			'callback'             => function ( $email ) {
				// Always page 1: each pass deletes what it found, so the next batch moves up.
				$ids     = aaykay_enquiries_by_email( $email, 1 );
				$removed = 0;
				foreach ( $ids as $id ) {
					if ( wp_delete_post( $id, true ) ) {
						++$removed;
					}
				}
				return array(
					'items_removed'  => $removed,
					'items_retained' => 0,
					'messages'       => array(),
					'done'           => count( $ids ) < 50,
				);
			},
		);
		return $erasers;
	}
);

/* ------------------------------------------------------------------ retention */

const AAYKAY_RETENTION_HOOK = 'aaykay_enquiry_retention';

/** Months to keep enquiries (Site settings), or 0 to keep them until deleted by hand. */
function aaykay_retention_months() {
	$months = (int) preg_replace( '/\D/', '', (string) aaykay_setting( 'enquiry_retention_months' ) );
	return $months > 0 ? min( $months, 120 ) : 0;
}

add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( AAYKAY_RETENTION_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', AAYKAY_RETENTION_HOOK );
		}
	}
);

add_action( 'switch_theme', 'aaykay_unschedule_retention' );
function aaykay_unschedule_retention() {
	wp_clear_scheduled_hook( AAYKAY_RETENTION_HOOK );
}

/**
 * Delete enquiries received more than the set number of months ago (for good, not to the
 * bin). Runs daily; does nothing while the setting is empty. Returns how many were deleted.
 */
function aaykay_apply_retention() {
	$months = aaykay_retention_months();
	if ( ! $months ) {
		return 0;
	}
	$deleted = 0;
	do {
		$ids = get_posts(
			array(
				'post_type'      => 'aaykay_enquiry',
				'post_status'    => 'any',
				'posts_per_page' => 100,
				'fields'         => 'ids',
				'date_query'     => array(
					array(
						'before' => gmdate( 'Y-m-d H:i:s', strtotime( '-' . $months . ' months' ) ),
						'column' => 'post_date_gmt',
					),
				),
			)
		);
		foreach ( $ids as $id ) {
			if ( wp_delete_post( $id, true ) ) {
				++$deleted;
			}
		}
	} while ( count( $ids ) === 100 && $deleted < 5000 );
	return $deleted;
}
add_action( AAYKAY_RETENTION_HOOK, 'aaykay_apply_retention' );

/* ------------------------------------------------------------------ privacy guide */

// Settings > Privacy > Policy guide: what this theme does with personal data, for the
// person writing the privacy notice (suggested wording, not legal advice).
add_action(
	'admin_init',
	function () {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		wp_add_privacy_policy_content(
			'AAYKAY Electricals theme',
			'<p>' . esc_html( 'The enquiry form collects the name, company, work email and, if given, phone number, city, project type and message. Each enquiry is stored in the website database, emailed to the address in Site settings > Send enquiries to, and, if connected, copied to a Google Sheet. Visitors are not tracked and the theme sets no cookies. If Cloudflare Web Analytics is switched on in Site settings, visits are counted without cookies. Enquiries can be exported or erased with Tools > Export / Erase Personal Data, and are deleted automatically after the period set in Site settings > Delete enquiries after (if set).' ) . '</p>'
		);
	}
);
