<?php

/*
* Plugin Name: Hubspot Company Sync
* Plugin URI: https://github.com/adnnco/hubspot-company-sync
* Description: This plugin synchronizes company data with Hubspot API.
* Version: 1.0.0
* Requires at least: 6.4.1
* Requires PHP:      8.2
* Author: adnnco
* Author URI: https://github.com/adnnco/
*/


/**
 * This action is added to the 'plugins_loaded' hook, which is triggered after all active plugins have been loaded.
 * It checks if the current domain is in the list of inactive domains, and if so, deactivates the plugin.
 */
add_action( 'plugins_loaded', 'hubsync_activation_check' );

/**
 * This function checks if the current domain is in the list of inactive domains.
 * If the current domain is in the list, it deactivates the plugin.
 *
 * @return void
 */
function hubsync_activation_check(): void {
	// List of domains where the plugin should be inactive
	$inactive_domains = array(
		'localhost:8000',
	);

	// Check if the current domain is in the list of inactive domains
	if ( in_array( $_SERVER['HTTP_HOST'], $inactive_domains ) ) {
		// If the current domain is in the list, deactivate the plugin
		deactivate_plugins( '/hubspot-company-sync.php' );
	}
}