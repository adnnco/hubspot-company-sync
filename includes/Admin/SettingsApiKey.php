<?php

namespace HubspotCompanySync\Admin;

use HubspotCompanySync\Interfaces\InitRegister;

class SettingsApiKey implements InitRegister {
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Registers the plugin's menu page.
	 */
	public function register_menu(): void {
		add_submenu_page(
			'options-general.php',
			'Hubspot Company Sync',
			'Hubspot Company Sync',
			'manage_options',
			'hubspot_company_sync',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Renders the plugin's settings page.
	 */
	public function settings_page(): void {
		require HUBSYNC_PATH . 'admin/views/settings_page.php';
	}

	public function register_settings(): void {
		add_settings_section(
			'hubspot_company_sync_section',
			'Hubspot Company Sync Settings',
			array( $this, 'hubspot_company_sync_section_callback' ),
			'hubspot_company_sync_settings'
		);

		add_settings_field(
			'hubspot_company_sync_api_key',
			'API Key',
			array( $this, 'hubspot_company_sync_api_key_callback' ),
			'hubspot_company_sync_settings',
			'hubspot_company_sync_section'
		);

		register_setting(
			'hubspot_company_sync_settings',
			'hubspot_company_sync_api_key'
		);
	}

	public function hubspot_company_sync_section_callback() {
		echo 'Enter your Hubspot API key below:';
	}

	public function hubspot_company_sync_api_key_callback() {
		$api_key = get_option( 'hubspot_company_sync_api_key' );
		echo '<input type="text" name="hubspot_company_sync_api_key" value="' . esc_attr( $api_key ) . '" />';
	}
}
