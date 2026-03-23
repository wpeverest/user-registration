<?php
/**
 * Description: Fixed Period Membership module for user registration plugin.
 * @package User_Registration_MEMBERSHIP
 * @version 1.0.0
 */

use WPEverest\URM\FixedPeriodMemebership\Main as FixedPeriodMemebershipMain;

if ( file_exists( UR()->plugin_path() . '/vendor/autoload.php' ) ) {
	require_once UR()->plugin_path() . '/vendor/autoload.php';
}

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'URM_FIXED_PERIOD_MEMBERSHIP_VERSION' ) ) {
	define( 'URM_FIXED_PERIOD_MEMBERSHIP_VERSION', '1.0.0' );
}

// Define URM_FIXED_PERIOD_MEMBERSHIP_FILE.
if ( ! defined( 'URM_FIXED_PERIOD_MEMBERSHIP_FILE' ) ) {
	define( 'URM_FIXED_PERIOD_MEMBERSHIP_FILE', __FILE__ );
}

// Define URM_FIXED_PERIOD_MEMBERSHIP_DIR.
if ( ! defined( 'URM_FIXED_PERIOD_MEMBERSHIP_DIR' ) ) {
	define( 'URM_FIXED_PERIOD_MEMBERSHIP_DIR', plugin_dir_path( __FILE__ ) );
}

// Define URM_FIXED_PERIOD_MEMBERSHIP_CSS_ASSETS_URL.
if ( ! defined( 'URM_FIXED_PERIOD_MEMBERSHIP_CSS_ASSETS_URL' ) ) {
	define( 'URM_FIXED_PERIOD_MEMBERSHIP_CSS_ASSETS_URL', UR()->plugin_url() . '/assets/css/modules/masteriyo' );
}

// Define URM_FIXED_PERIOD_MEMBERSHIP_JS_ASSETS_URL.
if ( ! defined( 'URM_FIXED_PERIOD_MEMBERSHIP_JS_ASSETS_URL' ) ) {
	define( 'URM_FIXED_PERIOD_MEMBERSHIP_JS_ASSETS_URL', UR()->plugin_url() . '/assets/js/modules/masteriyo' );
}


if ( ! function_exists( 'urm_fixed_period_membership_init' ) ) {
	/**
	 * Initialization of Membership instance.
	 **/
	function urm_fixed_period_membership_init() {
		return FixedPeriodMemebershipMain::get_instance();
	}
}

urm_fixed_period_membership_init();
