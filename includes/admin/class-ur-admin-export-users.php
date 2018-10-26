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

		$users = get_users( array(
    		'ur_form_id'     => $form_id,
		));

		if( count( $users ) === 0 ) {
  	 	 	echo '<div id="message" class="updated inline notice notice-error"><p><strong>'. __( 'No users found with this form id.', 'user-registration' ) .'</strong></p></div>';
  	 	 	return;
  	 	}

		$columns = $this->generate_columns( $form_id );
		$rows 	 = $this->generate_rows( $users );

		$form_name = strtolower( str_replace( " ", "-", get_the_title( $form_id ) ) );
		$file_name = $form_name . "-" . current_time( 'Y-m-d_H:i:s' ) . '.csv';

 		// Set the CSV headers.
		$this->send_headers( $file_name );
 		$handle = fopen( "php://output", 'w' );

 		// Handle UTF-8 chars conversion for CSV.
		fprintf( $handle, chr(0xEF).chr(0xBB).chr(0xBF) );

 		// Put the column headers.
		fputcsv( $handle, array_values( $columns ) );

 		// Put the entry values.
		foreach ( $rows as $row ) {
			fputcsv( $handle, $row );
		}

 		fclose( $handle );
		exit;
	}

	/**
	 * Set the export headers.
	 *
	 * @param string $file_name File name.
	 */
	private function send_headers( $file_name = '' ) {

		if ( function_exists( 'gc_enable' ) ) {
			gc_enable(); // phpcs:ignore PHPCompatibility.PHP.NewFunctions.gc_enableFound
		}

		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 ); // @codingStandardsIgnoreLine
		}

		@ini_set( 'zlib.output_compression', 'Off' ); // @codingStandardsIgnoreLine
		@ini_set( 'output_buffering', 'Off' ); // @codingStandardsIgnoreLine
		@ini_set( 'output_handler', '' ); // @codingStandardsIgnoreLine

		ignore_user_abort( true );

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}

	    $now = gmdate("D, d M Y H:i:s");
	    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	    header("Last-Modified: {$now} GMT");

		header( "Content-Type: application/force-download" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Type: application/download" );
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}

	/**
	 * Generate Columns for CSV export.
	 * @param  int 		$form_id  Form ID.
	 * @return array    $columns  CSV Export Columns.
	 */
	public function generate_columns( $form_id ) {

		// Default Columns.
		$default_columns = apply_filters( 'user_registration_csv_export_default_columns', array(
			'user_role'     	  => __( 'User Device', 'user-registration' ),
			'date_created'    	  => __( 'Date Created', 'user-registration' ),
			'date_created_gmt'    => __( 'Date Created GMT', 'user-registration' ),
		) );

		// User ID Column.
		$user_id_column = array(
			'user_id'	=> __( 'User ID', 'user-registration' )
		);

		$columns = ur_get_meta_key_label( $form_id );

		$exclude_columns = apply_filters( 'user_registration_csv_export_exclude_columns', array(
			'user_pass',
			'user_confirm_password',
		) );

		foreach( $exclude_columns as $exclude_column ) {
			unset( $columns[ $exclude_column ]);
		}

		$columns = array_merge( $user_id_column, $columns );
		$columns = array_merge( $columns, $default_columns );

		return apply_filters( 'user_registration_csv_export_columns', $columns );
	}

	/**
	 * Generate rows for CSV export
	 * @param  obj 		$users 	 Users Data
	 * @return array 	$rows	 CSV export rows.
	 */
	public function generate_rows( $users ) {

  	 	$rows = array();
  	 	foreach( $users as $user ) {
  	 		$rows[] = isset( $user->data->ID ) ? ur_get_user_extra_fields( $user->data->ID ) : array();
  	 	}

		return apply_filters( 'user_registration_csv_export_rows', $rows, $form_id );
	}
}

new UR_Admin_Export_Users();
