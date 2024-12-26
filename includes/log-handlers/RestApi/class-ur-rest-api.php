<?php
/**
 * UserRegistration UR_REST_API
 *
 * API Handler
 *
 * @class    UR_REST_API
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_REST_API Class
 */
class UR_REST_API {

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
		include __DIR__ . '/controllers/version1/class-ur-getting-started.php';
		include __DIR__ . '/controllers/version1/class-ur-modules.php';
		include __DIR__ . '/controllers/version1/class-ur-changelog.php';
		include __DIR__ . '/controllers/version1/class-ur-gutenberg-blocks.php';
		include __DIR__ . '/controllers/version1/class-ur-form-templates.php';
		include __DIR__ . '/controllers/version1/class-ur-plugin-status.php';

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
	 * @since 3.2.0
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
			'user_registration_rest_api_get_rest_namespaces',
			array(
				'user-registration/v1' => self::get_v1_rest_classes(),
			)
		);
	}

	/**
	 * List of classes in the user-registration/v1 namespace.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @return array
	 */
	protected static function get_v1_rest_classes() {
		return array(
			'getting-started'  => 'UR_Getting_Started',
			'modules'          => 'UR_Modules',
			'changelog'        => 'UR_Changelog',
			'gutenberg-blocks' => 'UR_Gutenberg_Blocks',
			'form-templates'   => 'UR_Form_Templates',
			'plugin'           => 'UR_Plugin_Status',
		);
	}
}

UR_REST_API::init();
