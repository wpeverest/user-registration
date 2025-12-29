<?php
/**
 * Content Restriction REST API registration.
 *
 * @since 4.0
 *
 * @package  UserRegistrationContentRestriction/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * URCR_REST_API Class
 */
class URCR_REST_API {

	/**
	 * Hook into WordPress ready to init the REST API as needed.
	 *
	 * @since 4.0
	 */
	public static function init() {
		include __DIR__ . '/controllers/version1/class-urcr-content-access-rules.php';

		add_filter( 'user_registration_rest_api_get_rest_namespaces', array( __CLASS__, 'register_rest_namespace' ) );
	}

	/**
	 * Register REST API namespace.
	 *
	 * @since 4.0
	 *
	 * @param array $namespaces Existing namespaces.
	 * @return array
	 */
	public static function register_rest_namespace( $namespaces ) {
		if ( ! isset( $namespaces['user-registration/v1'] ) ) {
			$namespaces['user-registration/v1'] = array();
		}

		$namespaces['user-registration/v1'][] = 'URCR_Content_Access_Rules';

		return $namespaces;
	}
}

URCR_REST_API::init();

