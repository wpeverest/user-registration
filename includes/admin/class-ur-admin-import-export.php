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
 * UR_Admin_Import_Export Class.
 */
class UR_Admin_Import_Export {

	/**
	 * Outputs Import/Export Page
	 * @return void
	 */
	public static function output() {
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-import-export.php' );
	}
}
