<?php
/**
 * Import/Export Page
 *
 * @author   WPEverest
 * @category Admin
 * @package  UserRegistration/Admin
 * @since    1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Admin_Export_Users Class.
 */
class UR_Admin_Export_Users {

	/**
	 * Outputs Import/Export Page
	 * @return void
	 */
	public static function output() {
		$all_forms = ur_get_all_user_registration_form();
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-export-users.php' );
	}
}
