<?php
/**
 * UserRegistration UR_REST_API
 *
 * API Handler
 *
 * @class    UR_REST_API
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
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
		register_rest_route( 'user-registration/v1', 'getting-started', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'ur_get_getting_started_settings' )
		) );
		register_rest_route( 'user-registration/v1', 'getting-started/save', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'ur_save_getting_started_settings' )
		) );
		register_rest_route( 'user-registration/v1', 'getting-started/install-pages', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'ur_getting_started_install_pages' )
		) );
	}

	/**
	 * Save settings for getting started page.
	 *
	 * @since 2.1.4
	 *
	 * @return array settings.
	 */
	public static function ur_save_getting_started_settings($request) {
		foreach ($request['settings'] as $option => $value) {
			update_option( $option, $value );
		}
	}

	/**
	 * Install default pages when user hits Install & Proceed button in setup wizard.
	 *
	 * @since 2.1.4
	 *
	 * @return array settings.
	 */
	public static function ur_getting_started_install_pages($request) {
		include_once untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) ) . '/includes/admin/functions-ur-admin.php';

		$pages = apply_filters('user_registration_create_pages', array() );

		if ( $default_form_page_id = get_option( 'user_registration_default_form_page_id' ) ) {
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
			array_push( $page_slug , get_post_field( 'post_name', $post_id ) );
		}

		wp_send_json_success(array( 'page_slug' => $page_slug));
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
			'general_settings'    => array(
				'title'    => __( 'General', 'user-registration' ),
				'settings' => array(
					array(
						'title'    => __( 'Anyone can register', 'user-registration' ),
						'desc'     => __( 'Check to enable users to register', 'user-registration' ),
						'id'       => 'users_can_register',
						'type'     => 'checkbox',
						'default'  => "yes",
					),
					array(
						'title'    => __( 'User login option', 'user-registration' ),
						'desc'     => __( 'This option lets you choose login option after user registration.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_login_options',
						'type'     => 'select',
						'default'  => 0,
						'options'  => ur_login_option(),
					),
					array(
						'title'    => __( 'Prevent dashboard access', 'user-registration' ),
						'desc'     => __( 'This option lets you limit which roles you are willing to prevent dashboard access.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_disabled_user_roles',
						'type'     => 'multiselect',
						'default'  => array_search( 'subscriber', array_keys( $all_roles_except_admin ) ),
						'options'  => $all_roles_except_admin,
					),
				)
			),
			'registration_settings' => array(
				'title'    => __( 'Registration', 'user-registration' ),
				'settings' => array(
					array(
						'title'             => __( 'Form Template', 'user-registration' ),
						'desc'               => __( 'Choose form template to use.', 'user-registration' ),
						'id'                => 'user_registration_form_template',
						'type'              => 'radio',
						'default'           => 0,
						'options'           => array(
							'default'      => __( 'Default', 'user-registration' ),
							'bordered'     => __( 'Bordered', 'user-registration' ),
							'flat'         => __( 'Flat', 'user-registration' ),
							'rounded'      => __( 'Rounded', 'user-registration' ),
							'rounded_edge' => __( 'Rounded Edge', 'user-registration' ),
						),
					),
					array(
						'title'             => __( 'Enable Strong Password', 'user-registration' ),
						'desc'               => __( 'Make strong password compulsary.', 'user-registration' ),
						'id'                => 'user_registration_form_setting_enable_strong_password',
						'type'              => 'checkbox',
						'default'           => "no",
					),
					array(
						'title'             => __( 'Minimum Password Strength', 'user-registration' ),
						'desc'               => __( 'Set minimum required password strength.', 'user-registration' ),
						'id'                => 'user_registration_form_setting_minimum_password_strength',
						'type'              => 'select',
						'default'           => 3,
						'options'           => array(
							'0' => __( 'Very Weak', 'user-registration' ),
							'1' => __( 'Weak', 'user-registration' ),
							'2' => __( 'Medium', 'user-registration' ),
							'3' => __( 'Strong', 'user-registration' ),
						),
					),
					array(
						'title'             => __( 'Default User Role', 'user-registration' ),
						'desc'               => __( 'Default role for the users registered through this form.', 'user-registration' ),
						'id'                => 'user_registration_form_setting_default_user_role',
						'type'              => 'select',
						'default'           => array_search( 'subscriber', array_keys( $all_roles ) ),
						'options'           => $all_roles,
					),
				)
			),
			'login_settings' => array(
				'title'    => __( 'Login', 'user-registration' ),
				'settings' => array(
					array(
						'title'    => __( 'Form Template', 'user-registration' ),
						'desc'     => __( 'Choose the login form template.', 'user-registration' ),
						'id'       => 'user_registration_login_options_form_template',
						'type'     => 'radio',
						'default'  => 0,
						'options'  => array(
							'default'      => __( 'Default', 'user-registration' ),
							'bordered'     => __( 'Bordered', 'user-registration' ),
							'flat'         => __( 'Flat', 'user-registration' ),
							'rounded'      => __( 'Rounded', 'user-registration' ),
							'rounded_edge' => __( 'Rounded Edge', 'user-registration' ),
						),
					),
					array(
						'title'    => __( 'Enable lost password', 'user-registration' ),
						'desc' => __( 'Check to enable/disable lost password.', 'user-registration' ),
						'id'       => 'user_registration_login_options_lost_password',
						'type'     => 'checkbox',
						'default'  => "yes",
					),
					array(
						'title'    => __( 'Enable remember me', 'user-registration' ),
						'desc' => __( 'Check to enable/disable remember me.', 'user-registration' ),
						'id'       => 'user_registration_login_options_remember_me',
						'type'     => 'checkbox',
						'default'  => "yes",
					),

					array(
						'title'    => __( 'Enable hide/show password', 'user-registration' ),
						'desc'     => __( 'Check to enable hide/show password icon.', 'user-registration' ),
						'id'       => 'user_registration_login_option_hide_show_password',
						'type'     => 'checkbox',
						'default'  => "no",
					),
				),
			),
			'my_account_settings' => array(
				'title'    => __( 'My Account', 'user-registration' ),
				'settings' => array(
					array(
						'title'    => __( 'My Account Page Layout', 'user-registration' ),
						'desc'     => __( 'This option lets you choose layout for user registration my account tab.', 'user-registration' ),
						'id'       => 'user_registration_my_account_layout',
						'type'     => 'radio',
						'default'  => 0,
						'options'  => array(
							'horizontal' => __( 'Horizontal', 'user-registration' ),
							'vertical'   => __( 'Vertical', 'user-registration' ),
						),
					),
					array(
						'title'    => __( 'Disable profile picture', 'user-registration' ),
						'desc'     => __( 'Check to disable profile picture in edit profile page.', 'user-registration' ),
						'id'       => 'user_registration_disable_profile_picture',
						'type'     => 'checkbox',
						'default'  => "no",
					),
				)
			)
		);

		return $settings;
	}
}

UR_REST_API::init();
