<?php
/**
 * Handles the Activation API responses.
 *
 * @class    UR_Plugin_Updater_Key_API
 * @version  1.1.0
 * @package  UserRegistration/Updates
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Plugin_Updater_Key_API Class.
 */
class UR_Plugin_Updater_Key_API {

	private static $endpoint = 'http://www.themetest.tk/?';

	/**
	 * Attempt to activate a plugin license.
	 * @return string JSON response
	 */
	public static function activate( $args ) {
		$defaults = array(
			'edd_action' => 'activate_license'
		);

		$args    = wp_parse_args( $defaults, $args );
		$request = wp_remote_get( self::$endpoint . '&' . http_build_query( $args, '', '&' ) );

		if ( is_wp_error( $request ) ) {
			return json_encode( array( 'error_code' => $request->get_error_code(), 'error' => $request->get_error_message() ) );
		}

		if ( wp_remote_retrieve_response_code( $request ) != 200 ) {
			return json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $request ) ) );
		}

		return wp_remote_retrieve_body( $request );
	}

	/**
	 * Attempt to deactivate a plugin license.
	 */
	public static function deactivate( $args ) {
		$defaults = array(
			'edd_action' => 'deactivate_license',
		);

		$args    = wp_parse_args( $defaults, $args );
		$request = wp_remote_get( self::$endpoint . '&' . http_build_query( $args, '', '&' ) );

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		} else {
			return wp_remote_retrieve_body( $request );
		}
	}
}
