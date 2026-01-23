<?php
/**
 * Blocks controller class.
 *
 * @since 3.2.0
 *
 * @package  UserRegistration/Classes
 */

defined( 'ABSPATH' ) || exit;

use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\Admin\Services\MembershipService;

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
	 * @return void
	 * @since 2.1.4
	 *
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

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/role-list',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_role_list_list' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/cr-data',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_content_restriction_data' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/access-role-list',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_access_role_list' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/membership-role-list',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_membership_role_list' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/groups',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_active_groups' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/membership-list',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_active_memberships' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/pages',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_pages' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/verify-pages',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_verify_pages' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/get-content-rules',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_content_rules' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
	}

	/**
	 * Get Addons Lists.
	 *
	 * @return array Addon lists.
	 * @since 3.2.0
	 *
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
	 * ur_get_content_rules
	 *
	 * @return WP_REST_Response
	 */
	public static function ur_get_content_rules() {
		$rule_lists = urcr_get_rules();

		return new \WP_REST_Response(
			array(
				'success'    => true,
				'rule_lists' => $rule_lists,
			),
			200
		);
	}

	/**
	 * ur_get_pages
	 *
	 * @return WP_REST_Response
	 */
	public static function ur_get_pages() {
		$page_lists = ur_get_all_pages();

		return new \WP_REST_Response(
			array(
				'success'    => true,
				'page_lists' => $page_lists,
			),
			200
		);
	}

	/**
	 * ur_get_pages
	 *
	 * @return WP_REST_Response
	 */
	public static function ur_verify_pages( WP_REST_Request $request ) {
		$params = json_decode( $request->get_json_params(), true );

		$membership_service = new \WPEverest\URMembership\Admin\Services\MembershipService();
		$response           = $membership_service->verify_page_content( sanitize_text_field( $params['type'] ), absint( $params['page_id'] ) );
		if ( ! $response['status'] ) {
			return new \WP_REST_Response(
				$response,
				404
			);
		} else {
			return new \WP_REST_Response(
				$response,
				200
			);
		}
	}

	/**
	 * Get active membership Lists.
	 *
	 * @return WP_REST_Response Groups lists.
	 * @since xx.xx.xx
	 *
	 */
	public static function ur_get_active_memberships() {
		$service         = new MembershipService();
		$membership_list = $service->list_active_memberships();

		return new \WP_REST_Response(
			array(
				'success'         => true,
				'membership_list' => $membership_list,
			),
			200
		);
	}

	/**
	 * Get Groups Lists.
	 *
	 * @return WP_REST_Response Groups lists.
	 * @since 4.2.1
	 *
	 */
	public static function ur_get_active_groups() {
		$group_service = new MembershipGroupService();
		$group_lists   = $group_service->get_membership_groups();

		return new \WP_REST_Response(
			array(
				'success'     => true,
				'group_lists' => $group_lists,
			),
			200
		);
	}

	/**
	 * Get role Lists.
	 *
	 * @return array Role lists.
	 * @since 4.0
	 *
	 */
	public static function ur_get_role_list_list() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		$roles = array();

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$roles     = $wp_roles->roles;
		$all_roles = array();

		foreach ( $roles as $role_key => $role ) {
			$all_roles[ $role_key ] = $role['name'];
		}

		return new \WP_REST_Response(
			array(
				'success'    => true,
				'role_lists' => $all_roles,
			),
			200
		);
	}

	/**
	 * Return content restriction data
	 *
	 * @return WP_REST_Response
	 * @since 4.0
	 *
	 */
	public static function ur_get_content_restriction_data() {
		$message = get_option( 'user_registration_content_restriction_message' );

		$message = ( false === $message ) ? esc_html__( 'This content is restricted!', 'user-registration' ) : $message;

		return new \WP_REST_Response(
			array(
				'success' => true,
				'cr_data' => array(
					'default_message' => $message,
				),
			),
			200
		);
	}

	/**
	 * Get access role list.
	 *
	 * @return array Role lists.
	 */
	public static function ur_get_access_role_list() {
		$access_options = array(
			'all_logged_in_users'   => 'All Logged In Users',
			'choose_specific_roles' => 'Choose Specific Roles',
			'guest_users'           => 'Guest Users',
		);

		if ( ur_check_module_activation( 'membership' ) ) {
			$access_options['memberships'] = 'Memberships';
		}

		return new \WP_REST_Response(
			array(
				'success'     => true,
				'access_data' => array(
					'access_role_list' => $access_options,
				),
			),
			200
		);
	}

	/**
	 * Retrieves the list of active membership roles.
	 *
	 * This method fetches the active membership roles using the `get_active_membership_id_name` function
	 * and returns the data as a REST API response.
	 *
	 * @return \WP_REST_Response REST API response containing:
	 *                            - 'success' (bool): Indicates the success of the operation.
	 *                            - 'membership_roles_list' (array): List of active membership roles.
	 */
	public static function ur_get_membership_role_list() {
		$membership_roles_options = get_active_membership_id_name();

		return new \WP_REST_Response(
			array(
				'success'               => true,
				'membership_roles_list' => $membership_roles_options,
			),
			200
		);
	}

	/**
	 * Check if a given request has access to update a setting
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public static function check_admin_permissions( $request ) {
		return current_user_can( 'manage_options' );
	}
}
