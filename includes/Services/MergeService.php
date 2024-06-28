<?php

namespace HubspotCompanySync\Services;

final class MergeService {

	/**
	 * Merge company information based on the provided main ID and domain.
	 *
	 * @param int $hubspot_company_id The hubspot company ID.
	 * @param string $website The domain for company search.
	 *
	 * @return void Merged company information. Empty array if no valid data found.
	 */
	public static function mergeCompany( int $hubspot_company_id, string $website ): void {
		$company_ids = SearchService::getByCompanyIds( $website );

		if ( empty( $company_ids ) ) {
			return;
		}
		// Check if the response is valid and contains more than one ID.
		$hubspot_company_id_index = array_search( $hubspot_company_id, $company_ids );

		// If the main ID is found, unset it and assign the remaining ID as secondary ID.
		if ( $hubspot_company_id_index !== false ) {
			unset( $company_ids[ $hubspot_company_id_index ] );
			// Get the first remaining ID.
		}

		$object_id = (int) reset( $company_ids );

		if ( empty( $object_id ) ) {
			return;
		}

		PushService::post(
			'https://api.hubapi.com/crm/v3/objects/companies/merge',
			array(
				'objectIdToMerge' => $object_id,
				'primaryObjectId' => $hubspot_company_id,
			),
			'POST'
		);
	}
}