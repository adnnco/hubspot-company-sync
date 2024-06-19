<?php

namespace HubspotCompanySync\Services;

final class PushService {

	/**
	 * @param string $api_url
	 * @param array $data
	 * @param string $method
	 *
	 * @return array|null
	 */
	public static function post( string $api_url, array $data, string $method = 'POST' ): ?array {

		// Make the API request.
		$response = wp_remote_post(
			esc_url_raw( $api_url ),
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . get_option( 'hubspot_company_sync_api_key' ),
					'Content-Type'  => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode( $data ),
				'method'  => $method,
			)
		);

		$body             = wp_remote_retrieve_body( $response );
		$json             = json_decode( $body, true );
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = $json['message'];

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			// Get the error message.
			$error_message = $response->get_error_message();

			// Log the error message.
			error_log( $error_message );

			// Save the error message as an option.
			self::save_error_message( $error_message );

		} else {

			if ( $response_code >= 400 ) {
				// Log the error message.
				error_log( $response_message );

				// Save the error message as an option.
				self::save_error_message( $response_message );
			} else {
				if ( isset( $json['status'] ) && $json['status'] === 'error' ) {
					if ( isset( $json['message'] ) ) {
						// Save the error message as an option.
						self::save_error_message( $response_message );
					}
				} else {
					return $json;
				}
			}
		}

		return null;

	}

	/**
	 * Save the error message as an option.
	 *
	 * @param string $error_message
	 */
	private static function save_error_message( string $error_message ): void {
		update_option( 'hubspot_sync_error_message', $error_message );

	}

	/**
	 * Display the HubSpot sync error notice.
	 */
	public static function show_hubspot_sync_error(): void {
		// Get the error message.
		$error_message = get_option( 'hubspot_sync_error_message' );

		// Display the error notice.
		if ( $error_message ) {
			echo '<div class="error">';
			echo '<p>There was an error syncing the taxonomy with HubSpot.</p>';
			echo '<p>Error message: ' . $error_message . '</p>';
			echo '</div>';

			// Reset the error message.
			delete_option( 'hubspot_sync_error_message' );
		}
	}
}
