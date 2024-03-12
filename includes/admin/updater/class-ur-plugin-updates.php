<?php
/**
 * Class for displaying plugin warning notifications and determining 3rd party plugin compatibility.
 *
 * @package  UserRegistration/Admin
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Plugin_Updates Class.
 */
class UR_Plugin_Updates {

	/**
	 * This is the header used by extensions to show requirements.
	 *
	 * @var string
	 */
	const VERSION_REQUIRED_HEADER = 'UR requires at least';

	/**
	 * This is the header used by extensions to show testing.
	 *
	 * @var string
	 */
	const VERSION_TESTED_HEADER = 'UR tested up to';

	/**
	 * Get plugins that have a valid value for a specific header.
	 *
	 * @param string $header Header.
	 * @return array of plugin info arrays
	 */
	protected function get_plugins_with_header( $header ) {
		$plugins = get_plugins();
		$matches = array();

		foreach ( $plugins as $file => $plugin ) {
			if ( ! empty( $plugin[ $header ] ) ) {
				$matches[ $file ] = $plugin;
			}
		}

		/**
		 * Filter to modify plugins information with header.
		 *
		 * @param array $matches Array of information.
		 * @param string $header Header information.
		 * @param array $plugins Array of plugins.
		 */
		return apply_filters( 'user_registration_get_plugins_with_header', $matches, $header, $plugins );
	}
}
