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
	 * Hook into WordPress ready to init the REST API as needed.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public static function register_rest_routes() {
		register_rest_route(
			'user-registration/v1',
			'getting-started',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_getting_started_settings' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			'user-registration/v1',
			'getting-started/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_save_getting_started_settings' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			'user-registration/v1',
			'getting-started/install-pages',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_getting_started_install_pages' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
	}

	/**
	 * Save settings for getting started page.
	 *
	 * @since 2.1.4
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return array settings.
	 */
	public static function ur_save_getting_started_settings( $request ) {

		if ( ! isset( $request['settings'] ) ) {
			return;
		}

		$settings_to_update = $request['settings'];
		$default_form_page_id = get_option( 'user_registration_default_form_page_id' );

		if ( isset( $settings_to_update['user_registration_general_setting_login_options'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_login_options', $settings_to_update['user_registration_general_setting_login_options'] );
		}

		if ( isset( $settings_to_update['user_registration_form_template'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_template', ucwords( str_replace( '_', ' ', $settings_to_update['user_registration_form_template'] ) ) );
		}

		if ( isset( $settings_to_update['user_registration_form_setting_enable_strong_password'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_enable_strong_password', $settings_to_update['user_registration_form_setting_enable_strong_password'] );
		}
		if ( isset( $settings_to_update['user_registration_form_setting_minimum_password_strength'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_minimum_password_strength', $settings_to_update['user_registration_form_setting_minimum_password_strength'] );
		}
		if ( isset( $settings_to_update['user_registration_form_setting_default_user_role'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_default_user_role', $settings_to_update['user_registration_form_setting_default_user_role'] );
		}

		foreach ( $settings_to_update as $option => $value ) {

			if ( 'users_can_register' === $option ) {
				$value = ur_string_to_bool( $value );
			}
			update_option( $option, $value );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'OnBoarding completed successfully', 'user-registration' ),
			),
			200
		);
	}

	/**
	 * Install default pages when user hits Install & Proceed button in setup wizard.
	 *
	 * @since 2.1.4
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return array settings.
	 */
	public static function ur_getting_started_install_pages( $request ) {

		if ( ! isset( $request['install_pages'] ) || ! $request['install_pages'] ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Pages cannot be installed', 'user-registration' ),
				),
				200
			);
		}

		include_once untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) ) . '/includes/admin/functions-ur-admin.php';

		$pages                = apply_filters( 'user_registration_create_pages', array() );
		$default_form_page_id = get_option( 'user_registration_default_form_page_id' );

		if ( $default_form_page_id ) {
			$pages['registration'] = array(
				'name'    => _x( 'registration', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Registration', 'Page title', 'user-registration' ),
				'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . esc_attr( $default_form_page_id ) . '"]',
			);
		}

		$pages['myaccount'] = array(
			'name'    => _x( 'my-account', 'Page slug', 'user-registration' ),
			'title'   => _x( 'My Account', 'Page title', 'user-registration' ),
			'content' => '[' . apply_filters( 'user_registration_my_account_shortcode_tag', 'user_registration_my_account' ) . ']',
		);

		$page_slug = array();
		foreach ( $pages as $key => $page ) {
			$post_id = ur_create_page( esc_sql( $page['name'] ), 'user_registration_' . $key . '_page_id', wp_kses_post( ( $page['title'] ) ), wp_kses_post( $page['content'] ) );
			array_push( $page_slug, get_post_field( 'post_name', $post_id ) );
		}

		return new \WP_REST_Response(
			array(
				'success'         => true,
				'page_slug'       => $page_slug,
				'default_form_id' => $default_form_page_id,
			),
			200
		);
	}

	/**
	 * Get settings for getting started page.
	 *
	 * @since 2.1.4
	 *
	 * @return array settings.
	 */
	public static function ur_get_getting_started_settings() {

		$all_roles = ur_get_default_admin_roles();

		$all_roles_except_admin = $all_roles;

		unset( $all_roles_except_admin['administrator'] );

		$settings = array(
			'general_settings'      => array(
				'title'    => __( 'General', 'user-registration' ),
				'settings' => array(
					array(
						'title'   => __( 'Anyone can register', 'user-registration' ),
						'desc'    => __( 'Check to enable users to register', 'user-registration' ),
						'id'      => 'users_can_register',
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					array(
						'title'   => __( 'User login option', 'user-registration' ),
						'desc'    => __( 'This option lets you choose login option after user registration.', 'user-registration' ),
						'id'      => 'user_registration_general_setting_login_options',
						'type'    => 'select',
						'default' => 0,
						'options' => ur_login_option(),
					),
					array(
						'title'   => __( 'Prevent dashboard access', 'user-registration' ),
						'desc'    => __( 'This option lets you limit which roles you are willing to prevent dashboard access.', 'user-registration' ),
						'id'      => 'user_registration_general_setting_disabled_user_roles',
						'type'    => 'multiselect',
						'default' => array( array_search( 'subscriber', array_keys( $all_roles_except_admin ) ) => 'subscriber' ),
						'options' => $all_roles_except_admin,
					),
				),
			),
			'registration_settings' => array(
				'title'    => __( 'Registration', 'user-registration' ),
				'settings' => array(
					array(
						'title'   => __( 'Form Template', 'user-registration' ),
						'desc'    => __( 'Choose form template to use.', 'user-registration' ),
						'id'      => 'user_registration_form_template',
						'type'    => 'radio',
						'default' => 0,
						'options' => array(
							'default'      => __( 'Default', 'user-registration' ),
							'bordered'     => __( 'Bordered', 'user-registration' ),
							'flat'         => __( 'Flat', 'user-registration' ),
							'rounded'      => __( 'Rounded', 'user-registration' ),
							'rounded_edge' => __( 'Rounded Edge', 'user-registration' ),
						),
					),
					array(
						'title'   => __( 'Enable Strong Password', 'user-registration' ),
						'desc'    => __( 'Make strong password compulsary.', 'user-registration' ),
						'id'      => 'user_registration_form_setting_enable_strong_password',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					array(
						'title'   => __( 'Minimum Password Strength', 'user-registration' ),
						'desc'    => __( 'Set minimum required password strength.', 'user-registration' ),
						'id'      => 'user_registration_form_setting_minimum_password_strength',
						'type'    => 'radio',
						'default' => 3,
						'options' => array(
							'0' => __( 'Very Weak', 'user-registration' ),
							'1' => __( 'Weak', 'user-registration' ),
							'2' => __( 'Medium', 'user-registration' ),
							'3' => __( 'Strong', 'user-registration' ),
						),
					),
					array(
						'title'   => __( 'Default User Role', 'user-registration' ),
						'desc'    => __( 'Default role for the users registered through this form.', 'user-registration' ),
						'id'      => 'user_registration_form_setting_default_user_role',
						'type'    => 'select',
						'default' => array_search( 'subscriber', array_keys( $all_roles ) ),
						'options' => $all_roles,
					),
				),
			),
			'login_settings'        => array(
				'title'    => __( 'Login', 'user-registration' ),
				'settings' => array(
					array(
						'title'   => __( 'Form Template', 'user-registration' ),
						'desc'    => __( 'Choose the login form template.', 'user-registration' ),
						'id'      => 'user_registration_login_options_form_template',
						'type'    => 'radio',
						'default' => 0,
						'options' => array(
							'default'      => __( 'Default', 'user-registration' ),
							'bordered'     => __( 'Bordered', 'user-registration' ),
							'flat'         => __( 'Flat', 'user-registration' ),
							'rounded'      => __( 'Rounded', 'user-registration' ),
							'rounded_edge' => __( 'Rounded Edge', 'user-registration' ),
						),
					),
					array(
						'title'   => __( 'Enable lost password', 'user-registration' ),
						'desc'    => __( 'Check to enable/disable lost password.', 'user-registration' ),
						'id'      => 'user_registration_login_options_lost_password',
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					array(
						'title'   => __( 'Enable remember me', 'user-registration' ),
						'desc'    => __( 'Check to enable/disable remember me.', 'user-registration' ),
						'id'      => 'user_registration_login_options_remember_me',
						'type'    => 'checkbox',
						'default' => 'yes',
					),

					array(
						'title'   => __( 'Enable hide/show password', 'user-registration' ),
						'desc'    => __( 'Check to enable hide/show password icon.', 'user-registration' ),
						'id'      => 'user_registration_login_option_hide_show_password',
						'type'    => 'checkbox',
						'default' => 'no',
					),
				),
			),
			'my_account_settings'   => array(
				'title'    => __( 'My Account', 'user-registration' ),
				'settings' => array(
					array(
						'title'   => __( 'My Account Page Layout', 'user-registration' ),
						'desc'    => __( 'This option lets you choose layout for user registration my account tab.', 'user-registration' ),
						'id'      => 'user_registration_my_account_layout',
						'type'    => 'radio',
						'default' => 0,
						'options' => array(
							'horizontal' => __( 'Horizontal', 'user-registration' ),
							'vertical'   => __( 'Vertical', 'user-registration' ),
						),
					),
					array(
						'title'   => __( 'Disable profile picture', 'user-registration' ),
						'desc'    => __( 'Check to disable profile picture in edit profile page.', 'user-registration' ),
						'id'      => 'user_registration_disable_profile_picture',
						'type'    => 'checkbox',
						'default' => 'no',
					),
				),
			),
		);

		return new \WP_REST_Response(
			array(
				'success' => true,
				'options' => $settings,
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

UR_REST_API::init();
