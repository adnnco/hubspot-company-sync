<?php

namespace HubspotCompanySync\Services;

final class ContactService {

	public static function get_contact_data( int $post_id ): array {
		$contact_data = array(
			'properties' => array(
				'email'          => get_field( 'contact_email', $post_id ),
				'firstname'      => get_field( 'contact_first_name', $post_id ),
				'lastname'       => get_field( 'contact_last_name', $post_id ),
				'company'        => get_the_title( $post_id ),
				'website'        => get_field( 'website', $post_id ),
				'hubsync_contact' => true,
			),
		);

		return $contact_data;
	}

	/**
	 * @param array $contact
	 * @param int $post_id
	 *
	 * @return void
	 */
	public static function contactEditAndMerge( array $contact, int $post_id = 0 ): void {
		$email        = $contact['properties']['email'];
		$email_domain = self::email_domain( $email );

		if ( empty( $email ) ) {
			return;
		}

		$contact_ids = SearchService::getByContactIds( $email );
		if ( empty( $contact_ids ) ) {
			return;
		}

		$contact_id = $contact_ids[0];

		if ( empty( $contact_id ) ) {
			return;
		}

		PushService::post( 'https://api.hubapi.com/crm/v3/objects/contacts/' . $contact_id, $contact, 'PATCH' );

		$company_website = self::website_domain( get_post_meta( $post_id, 'website', true ) );
		if ( $company_website !== $email_domain ) {
			$hubspot_company_id = get_post_meta( $post_id, 'hubsync_company_id', true );
			MergeService::mergeCompany( $hubspot_company_id, $email_domain );
		}

	}

	/**
	 * @param mixed $email
	 *
	 * @return string
	 */
	private static function email_domain( mixed $email ): string {
		$email = explode( '@', $email );

		return $email[1];
	}

	/**
	 * @param mixed $website
	 *
	 * @return string
	 */
	private static function website_domain( mixed $website ): string {
		$website = parse_url( $website );

		return $website['host'];
	}
}