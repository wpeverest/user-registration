<?php
/**
 * Export Users
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
	 * Exports users data along with extra information in CSV format.
	 * @return void
	 */
	public function __construct() {

		// Check for non empty $_POST.
		if ( ! empty( $_POST ) && isset( $_POST['user_registration_export_users'] ) ) {
		}
	}

	/**
	 * Outputs Export Users Page
	 * @return void
	 */
	public static function output() {
		$all_forms = ur_get_all_user_registration_form();
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-export-users.php' );
	}
}

new UR_Admin_Export_Users();
