<?php

namespace HubspotCompanySync\Admin;

use HubspotCompanySync\Interfaces\InitRegister;
use HubspotCompanySync\Services\PushService;

/**
 * Class PostSaveTaxonomies
 *
 * This class is responsible for syncing WordPress taxonomies with HubSpot properties.
 * It implements the InitRegister interface, which requires a register method for registering plugin hooks.
 *
 * @package HubspotCompanySync\Admin
 */
class PostSaveTaxonomies implements InitRegister {
	/**
	 * The HubSpot properties that correspond to WordPress taxonomies.
	 *
	 * @var array
	 */
	public array $properties = array(
		'services' => 'hubsync_services',
	);

	/**
	 * Registers the plugin hooks.
	 *
	 * This method is called to register the 'created_term' and 'edited_term' action hooks.
	 * The 'hubsync_created_term' method is set as the callback function for these hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'created_term', array( $this, 'hubsync_created_term' ), null, 3 );
		add_action( 'edited_term', array( $this, 'hubsync_created_term' ), null, 3 );
	}

	/**
	 * Callback function for the 'created_term' and 'edited_term' action hooks.
	 *
	 * This method is called when a term is created or edited.
	 * It calls the 'sync_taxonomy_terms_with_hubspot' method to sync the term with HubSpot properties.
	 *
	 * @param int $term_id Term ID.
	 * @param int $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return void
	 */
	public function hubsync_created_term( int $term_id, int $tt_id, string $taxonomy ): void {
		$this->sync_taxonomy_terms_with_hubspot( $taxonomy );
	}

	/**
	 * Syncs taxonomy terms with HubSpot properties.
	 *
	 * This method retrieves the terms in the specified taxonomy, converts them into options,
	 * and syncs the options with a HubSpot property using the PushService.
	 *
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return array|null An array of options for the HubSpot property, or null if the taxonomy does not have a corresponding HubSpot property.
	 */
	public function sync_taxonomy_terms_with_hubspot( string $taxonomy ): ?array {

		if ( empty( $this->properties[ $taxonomy ] ) ) {
			return [];
		}

		// Get the terms in the taxonomy.
		$categories = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'names',
			)
		);

		// Create an array of options for the HubSpot property.
		$options = array();

		foreach ( $categories as $category ) {
			$options[] = array(
				'value' => str_replace( '-', '_', strtoupper( sanitize_title( $category ) ) ),
				'label' => str_replace( '&amp;', '&', $category ),
			);
		}

		return PushService::post(
			"https://api.hubapi.com/crm/v3/properties/0-2/{$this->properties[ $taxonomy ]}",
			array(
				'options' => $options,
			),
			'PATCH'
		);
	}
}