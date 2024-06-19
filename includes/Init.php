<?php

namespace HubspotCompanySync;

final class Init {
	/**
	 * Loop through the classes, initialize them, and call the register() method if it exists
	 *
	 * @return void
	 */
	public static function register_services(): void {
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

	/**
	 * Store all the classes inside an array
	 *
	 * @return array Full list of classes
	 */
	public static function get_services(): array {
		return array(
			Admin\SettingsApiKey::class,
			Admin\PostMetaBoxes::class,

		);
	}

	/**
	 * Initialize the class
	 *
	 * @param mixed $class
	 *
	 * @return mixed instance new instance of the class
	 */
	private static function instantiate( $class ): mixed {
		return new $class();
	}
}
