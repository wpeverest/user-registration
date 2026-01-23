<?php
/**
 * Description: Masteriyo module for user registration plugin.
 * @package User_Registration_MEMBERSHIP
 * @version 1.0.0
 */

use WPEverest\URM\ContentDrip\Main;

if ( file_exists( UR()->plugin_path() . '/vendor/autoload.php' ) ) {
	require_once UR()->plugin_path() . '/vendor/autoload.php';
}

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'URM_CONTENT_DRIP_VERSION' ) ) {
	define( 'URM_CONTENT_DRIP_VERSION', '1.0.0' );
}

// Define URM_CONTENT_DRIP_FILE.
if ( ! defined( 'URM_CONTENT_DRIP_FILE' ) ) {
	define( 'URM_CONTENT_DRIP_FILE', __FILE__ );
}

// Define URM_CONTENT_DRIP_DIR.
if ( ! defined( 'URM_CONTENT_DRIP_DIR' ) ) {
	define( 'URM_CONTENT_DRIP_DIR', plugin_dir_path( __FILE__ ) );
}

// Define URM_CONTENT_DRIP_CSS_ASSETS_URL.
if ( ! defined( 'URM_CONTENT_DRIP_CSS_ASSETS_URL' ) ) {
	define( 'URM_CONTENT_DRIP_CSS_ASSETS_URL', UR()->plugin_url() . '/assets/css/modules/masteriyo' );
}

// Define URM_CONTENT_DRIP_JS_ASSETS_URL.
if ( ! defined( 'URM_CONTENT_DRIP_JS_ASSETS_URL' ) ) {
	define( 'URM_CONTENT_DRIP_JS_ASSETS_URL', UR()->plugin_url() . '/assets/js/modules/masteriyo' );
}


if ( ! function_exists( 'urm_content_drip_init' ) ) {
	/**
	 * Initialization of Membership instance.
	 **/
	function urm_content_drip_init() {
		return Main::get_instance();
	}
}

urm_content_drip_init();
