<?php
/**
 * Description: Membership module for user registration plugin.
 * @package User_Registration_MEMBERSHIP
 * @version 1.0.2
 */


if ( file_exists( UR()->plugin_path() . '/vendor/autoload.php' ) ) {
	require_once UR()->plugin_path() . '/vendor/autoload.php';
}

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'UR_MEMBERSHIP_VERSION' ) ) {
	define( 'UR_MEMBERSHIP_VERSION', '1.0.2' );
}

// Define UR_MEMBERSHIP_PLUGIN_FILE.
if ( ! defined( 'UR_MEMBERSHIP_PLUGIN_FILE' ) ) {
	define( 'UR_MEMBERSHIP_PLUGIN_FILE', __FILE__ );
}

// Define UR_MEMBERSHIP_DIR.
if ( ! defined( 'UR_MEMBERSHIP_DIR' ) ) {
	define( 'UR_MEMBERSHIP_DIR', plugin_dir_path( __FILE__ ) );
}

// Define UR_MEMBERSHIP_DS.
if ( ! defined( 'UR_MEMBERSHIP_DS' ) ) {
	define( 'UR_MEMBERSHIP_DS', DIRECTORY_SEPARATOR );
}

// Define UR_MEMBERSHIP_URL.
if ( ! defined( 'UR_MEMBERSHIP_URL' ) ) {
	define( 'UR_MEMBERSHIP_URL', plugin_dir_url( __FILE__ ) );
}

// Define UR_MEMBERSHIP_ASSETS_URL.
if ( ! defined( 'UR_MEMBERSHIP_ASSETS_URL' ) ) {
	define( 'UR_MEMBERSHIP_ASSETS_URL', UR()->plugin_url() . '/assets' );
}
// Define UR_MEMBERSHIP_CSS_ASSETS_URL.
if ( ! defined( 'UR_MEMBERSHIP_CSS_ASSETS_URL' ) ) {
	define( 'UR_MEMBERSHIP_CSS_ASSETS_URL', UR()->plugin_url() . '/assets/css/modules/membership' );
}
// Define UR_MEMBERSHIP_JS_ASSETS_URL.
if ( ! defined( 'UR_MEMBERSHIP_JS_ASSETS_URL' ) ) {
	define( 'UR_MEMBERSHIP_JS_ASSETS_URL', UR()->plugin_url() . '/assets/js/modules/membership' );
}
// Define UR_MEMBERSHIP_TEMPLATE_PATH.
if ( ! defined( ' UR_MEMBERSHIP_TEMPLATE_PATH' ) ) {
	define( 'UR_MEMBERSHIP_TEMPLATE_PATH', UR_MEMBERSHIP_DIR . 'templates' );
}

use WPEverest\URMembership\Admin;

if( !function_exists('user_registration_membership') ) {
	/**
	 * Initialization of Membership instance.
	 **/
	function user_registration_membership() {
		return Admin::get_instance();
	}
}

user_registration_membership();
