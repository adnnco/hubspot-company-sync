<?php

namespace HubspotCompanySync\Services;

use WP_Error;

final class SearchService {

	/**
	 * Search for companies using the HubSpot CRM API.
	 *
	 * @param string $query The query string for company search.
	 *
	 * @return WP_Error|array The API response or an error object.
	 */
	public static function getByCompanyIds( string $query ): WP_Error|array {
		$result = PushService::post( 'https://api.hubapi.com/crm/v3/objects/companies/search', array( 'query' => $query ) );

		if ( $result['total'] < 1 ) {
			return [];
		}

		$result['results'] = array_map(
			function ( $result ) {
				return $result['id'];
			},
			$result['results']
		);

		return $result['results'];
	}

	/**
	 * Search for contacts using the HubSpot CRM API.
	 *
	 * @param string $query The query string for contact search.
	 *
	 * @return WP_Error|array The API response or an error object.
	 */
	public static function getByContactIds( string $query ): WP_Error|array {
		// Make the API request.
		$result = PushService::post( 'https://api.hubapi.com/crm/v3/objects/contacts/search', array( 'query' => $query ) );

		if ( $result['total'] < 1 ) {
			return [];
		}

		$result['results'] = array_map(
			function ( $result ) {
				return $result['id'];
			},
			$result['results']
		);

		return $result['results'];
	}

}