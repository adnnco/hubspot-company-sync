<?php

namespace HubspotCompanySync\Admin;

use DateTime;
use HubspotCompanySync\Interfaces\InitRegister;
use HubspotCompanySync\Services\ContactService;
use HubspotCompanySync\Services\MergeService;
use HubspotCompanySync\Services\PushService;

class PostDataCompanySync implements InitRegister {

	/**
	 * @return void
	 */
	public function register(): void {
		add_action( 'edit_post_company', array( $this, 'save_post_company' ), 90 );
		add_action( 'save_post_company_cron', array( $this, 'save_post_company_cron' ), 91 );
	}

	/**
	 * A cron job function for the save_post_company event.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return void
	 */
	public function save_post_company_cron( int $post_id ): void {
		$hubspot_company_id = get_post_meta( $post_id, 'hubsync_company_id', true );

		if ( empty( $hubspot_company_id ) ) {
			return;
		}

		$company_data = $this->get_company_data( $post_id );
		$company      = $company_data['properties'];

		PushService::post(
			'https://api.hubapi.com/crm/v3/objects/companies/' . $hubspot_company_id,
			$company_data,
			'PATCH'
		);

		if ( $company['domain'] ) {
			MergeService::mergeCompany( $hubspot_company_id, $company['domain'] );

			PushService::post(
				'https://api.hubapi.com/crm/v3/objects/contacts',
				 ContactService::get_contact_data( $post_id )
			);
		}

		// Clear the scheduled hook when the operation is complete
		wp_clear_scheduled_hook( 'save_post_company_cron', array( $post_id ) );
	}

	public function get_company_data( $post_id ): array {
		$company = get_post( $post_id );

		$website = parse_url( get_field( 'website', $post_id ) );
		$website = "{$website['scheme']}://{$website['host']}";

		$domain = str_ireplace( 'www.', '', parse_url( $website, PHP_URL_HOST ) );

		$service_map = $this->get_sanitized_terms( $post_id, 'services' );

		$submission_date = get_field( 'submission_date', $post_id ) ?? null;

		if ( $submission_date ) {
			$submission_date = DateTime::createFromFormat( 'd/m/Y', $submission_date );
			$submission_date = $submission_date->format( 'Y-m-d' );
		}

		return array(
			'properties' => array(
				// Property Group: Hubspot
				'name'               => $company->post_title,
				'domain'             => $domain,
				'website'            => $website,
				'type'               => get_field( 'type', $post_id ),
				// Property Group: Hubsync Information
				'hubsync_company_id' => "{$post_id}",
				//'hubsync_company_status' => strtoupper( get_post_status( $post_id ) ),
				'hubsync_services'   => implode( ';', $service_map ),
			),
		);
	}

	/**
	 * Get sanitized terms for a given taxonomy and post ID.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param int $post_id Post ID.
	 *
	 * @return array An array of sanitized term names.
	 */
	function get_sanitized_terms( int $post_id, string $taxonomy ): array {

		$terms = wp_get_post_terms(
			$post_id,
			$taxonomy,
			array(
				'order'  => 'ASC',
				'fields' => 'names',
			)
		);

		// Sanitize the terms.
		return array_map(
			function ( $term ) {
				$sanitized = sanitize_title( $term );
				$sanitized = strtoupper( $sanitized );

				return str_replace( '-', '_', $sanitized );
			},
			$terms
		);
	}

	/**
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function save_post_company( int $post_id ): void {
		if ( ! isset( $_POST['hubspot_company_sync_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['hubspot_company_sync_nonce'], 'hubspot_company_sync' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if company already has a Hubspot Company ID
		$hubspot_company_id = get_post_meta( $post_id, 'hubsync_company_id', true );

		$api_url = 'https://api.hubapi.com/crm/v3/objects/companies';
		$method  = 'POST';

		if ( $hubspot_company_id ) {
			$api_url = 'https://api.hubapi.com/crm/v3/objects/companies/' . $hubspot_company_id;
			$method  = 'PATCH';
		}

		$company_data = $this->get_company_data( $post_id );
		$company      = $company_data['properties'];

		$response = PushService::post(
			$api_url,
			$company_data,
			$method
		);

		if ( isset( $response['id'] ) ) {
			if ( empty( $hubspot_company_id ) ) {
				update_post_meta( $post_id, 'hubsync_company_id', $response['id'] );
			}
			update_post_meta( $post_id, 'hubsync_company_updatedAt', current_time( 'mysql' ) );
		}

		// Schedule a cron job when the operation is complete
		if ( ! wp_next_scheduled( 'save_post_company_cron', $post_id ) ) {
			wp_schedule_single_event( time() + 14, 'save_post_company_cron', array( $post_id ) );
		}

		if ( $company['domain'] ) {

			PushService::post(
				'https://api.hubapi.com/crm/v3/objects/contacts',
				ContactService::get_contact_data( $post_id )
			);
		}


	}
}
