<?php
/**
 * Blocks controller class.
 *
 * @since 3.2.0
 *
 * @package  UserRegistration/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_AddonsClass
 */
class UR_Gutenberg_Blocks {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'user-registration/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'gutenberg-blocks';

	/**
	 * Register routes.
	 *
	 * @since 2.1.4
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/form-list',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_form_list' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
	}

	/**
	 * Get Addons Lists.
	 *
	 * @since 3.2.0
	 *
	 * @return array Addon lists.
	 */
	public static function ur_get_form_list() {
		$form_lists = ur_get_all_user_registration_form();
		return new \WP_REST_Response(
			array(
				'success'    => true,
				'form_lists' => $form_lists,
			),
			200
		);
	}
	/**
	 * Check if a given request has access to update a setting
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public static function check_admin_permissions( $request ) {
		return current_user_can( 'manage_options' );
	}
}
