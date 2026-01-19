<?php
/**
 * Description: Masteriyo module for user registration plugin.
 * @package User_Registration_MEMBERSHIP
 * @version 1.0.0
 */

use WPEverest\URM\Masteriyo\Main;

if ( file_exists( UR()->plugin_path() . '/vendor/autoload.php' ) ) {
	require_once UR()->plugin_path() . '/vendor/autoload.php';
}

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'URM_MASTERIYO_VERSION' ) ) {
	define( 'URM_MASTERIYO_VERSION', '1.0.0' );
}

// Define URM_MASTERIYO_FILE.
if ( ! defined( 'URM_MASTERIYO_FILE' ) ) {
	define( 'URM_MASTERIYO_FILE', __FILE__ );
}

// Define URM_MASTERIYO_DIR.
if ( ! defined( 'URM_MASTERIYO_DIR' ) ) {
	define( 'URM_MASTERIYO_DIR', plugin_dir_path( __FILE__ ) );
}

// Define URM_MASTERIYO_CSS_ASSETS_URL.
if ( ! defined( 'URM_MASTERIYO_CSS_ASSETS_URL' ) ) {
	define( 'URM_MASTERIYO_CSS_ASSETS_URL', UR()->plugin_url() . '/assets/css/modules/masteriyo' );
}

// Define URM_MASTERIYO_JS_ASSETS_URL.
if ( ! defined( 'URM_MASTERIYO_JS_ASSETS_URL' ) ) {
	define( 'URM_MASTERIYO_JS_ASSETS_URL', UR()->plugin_url() . '/assets/js/modules/masteriyo' );
}


if ( ! function_exists( 'urm_masteriyo_init' ) ) {
	/**
	 * Initialization of Membership instance.
	 **/
	function urm_masteriyo_init() {
		return Main::get_instance();
	}
}

urm_masteriyo_init();
