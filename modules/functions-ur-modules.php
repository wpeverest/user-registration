<?php
/**
 * UserRegistration Modules
 *
 * Functions for the modules.
 *
 * @package  UserRegistration/Functions
 * @version  4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'ur_check_module_activation' ) ) {
	/**
	 * Check if provided module is activated or not.
	 *
	 * @param string Module key to check.
	 *
	 * @return bool
	 */
	function ur_check_module_activation( $module ) {
		$enabled_features = get_option( 'user_registration_enabled_features', array() );
		return in_array( 'user-registration-' . $module, $enabled_features, true ) ? true : false;
	}
}
