<?php
/**
 * Import / Export Forms
 *
 * @package  UserRegistration/Admin
 * @since    1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Admin_Import_Export_Forms Class.
 */
class UR_Admin_Import_Export_Forms {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Outputs Export Users Page
	 *
	 * @return void
	 */
	public static function output() {
		$all_forms = ur_get_all_user_registration_form();
		include_once dirname( __FILE__ ) . '/views/html-admin-page-import-export-forms.php';
	}

	/**
	 * Exports form data along with extra information in JSON format.
	 *
	 * @param int $form_id Form Id.
	 * @return void
	 */
	public function export_json( $form_id ) {
		error_log( print_r( $form_id, true ) );
	}

}

new UR_Admin_Import_Export_Forms();
