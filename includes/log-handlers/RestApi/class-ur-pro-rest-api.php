<?php
/**
 * UserRegistration UR_PRO_REST_API
 *
 * API Handler
 *
 * @class    UR_PRO_REST_API
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_PRO_REST_API Class
 */
class UR_PRO_REST_API {

	/**
	 * REST API classes and endpoints.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $rest_classes = array();

	/**
	 * Hook into WordPress ready to init the REST API as needed.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		include __DIR__ . '/controllers/version1/class-ur-pro-gutenberg-blocks.php';
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public static function register_rest_routes() {
		foreach ( self::get_rest_classes() as $rest_namespace => $classes ) {
			foreach ( $classes as $class_name ) {
				self::$rest_classes[ $rest_namespace ][ $class_name ] = new $class_name();
				self::$rest_classes[ $rest_namespace ][ $class_name ]->register_routes();
			}
		}
	}

	/**
	 * Get API Classes - new classes should be registered here.
	 *
	 * @since 3.1.6
	 *
	 * @return array List of Classes.
	 */
	protected static function get_rest_classes() {
		/**
		 * Filters rest API controller classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array $rest_routes API namespace to API classes index array.
		 */
		return apply_filters(
			'user_registration_pro_rest_api_get_rest_namespaces',
			array(
				'user-registration-pro/v1' => self::get_v1_rest_classes(),
			)
		);
	}

	/**
	 * List of classes in the user-registration-pro/v1 namespace.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @return array
	 */
	protected static function get_v1_rest_classes() {
		return array(
			'pro-gutenberg-blocks' => 'UR_Pro_Gutenberg_Blocks',
		);
	}
}

UR_PRO_REST_API::init();
