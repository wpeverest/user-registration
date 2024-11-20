<?php
/**
 * Plugin Name: User Registration Membership
 * Plugin URI: https://wpuserregistration.com/features/membership/
 * Description: Membership addon for user registration plugin.
 * Version: 1.0.2
 * Author: WPEverest
 * Author URI: https://wpeverest.com
 * Text Domain: user-registration-membership
 * Domain Path: /languages/
 * UR Pro requires at least: 4.2.0
 * UR tested up to: 4.3.4
 *
 * Copyright: © 2020 WPEverest.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package User_Registration_MEMBERSHIP
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

/**
 * Initialization of Membership instance.
 **/
function user_registration_membership() {
	return Admin::get_instance();
}

user_registration_membership();
