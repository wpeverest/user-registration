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
	 * Constructor
	 */
	public function __construct() {

		// Check for non empty $_POST.
		if ( ! empty( $_POST ) && isset( $_POST['user_registration_export_users'] ) ) {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'user-registration-settings' ) ) {
				die( __( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
			} else {

				$form_id = isset( $_POST['export_users'] ) ? $_POST['export_users'] : 0;
				$this->export_csv( $form_id );
			}
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

	/**
	 * Exports users data along with extra information in CSV format.
	 * @return void
	 */
	public function export_csv( $form_id ) {

		// Return if form id is not set and current user doesnot have export capability.
		if( ! isset( $form_id ) || ! current_user_can( 'export' ) ) {
			return;
		}

		// Default Columns.
		$default_columns = apply_filters( 'user_registration_csv_exporter_default_columns', array(
			'user_role'     	  => __( 'User Device', 'user-registration' ),
			'date_created'    	  => __( 'Date Created', 'user-registration' ),
			'date_created_gmt'    => __( 'Date Created GMT', 'user-registration' ),
		) );

		// User ID Column.
		$user_id_column = array(
			'user_id'	=> __( 'User ID', 'user-registration' )
		);
	}
}

new UR_Admin_Export_Users();
