<?php

namespace HubspotCompanySync\Admin;

use HubspotCompanySync\Interfaces\InitRegister;

class PostMetaBoxes implements InitRegister {

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	/**
	 * Adds the meta box for the hubsync_company_id field
	 *
	 * @return void
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'hubspot_company_sync',
			__( 'Hubspot Company Sync', 'hubspot_company_sync' ),
			array(
				$this,
				'meta_box_callback',
			),
			array( 'company' ),
			'side'
		);
	}


	/**
	 * Renders the meta box for the hubsync_company_id field
	 *
	 * @param $post
	 * @return void
	 */
	public function meta_box_callback( $post ): void {
		wp_nonce_field( 'hubspot_company_sync', 'hubspot_company_sync_nonce' );

		$hubspot_company_id        = get_post_meta( $post->ID, 'hubsync_company_id', true );
		$hubspot_company_sync_date = get_post_meta( $post->ID, 'hubsync_company_updatedAt', true );

		echo '<label for="hubspot_company_id_field">';
		_e( 'Hubspot Company ID', 'hubsync' );
		echo '</label> ';
		echo '<input type="text" id="hubspot_company_id_field" name="hubspot_company_id_field" value="' . esc_attr( $hubspot_company_id ) . '"><br><br>';

		echo '<label for="hubspot_company_sync_date_field">';
		_e( 'Hubspot Company Sync Date', 'hubsync' );
		echo '</label> ';
		echo '<input type="text" id="hubspot_company_sync_date_field" name="hubspot_company_sync_date_field" value="' . esc_attr( $hubspot_company_sync_date ) . '" disabled><br><br>';

		echo '<button type="button" class="button button-primary" onclick="window.open(\'https://app-eu1.hubspot.com/contacts/144900325/company/' . esc_attr( $hubspot_company_id ) . '\', \'_blank\');">';
		_e( 'View in Hubspot', 'hubsync' );
		echo '</button>';
	}
}
