<?php
/**
 * Getting started controller class.
 *
 * @since 2.1.4
 *
 * @package  UserRegistration/Classes
 */

use WPEverest\URMembership\Admin\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * UR_Getting_Started Class
 */
class UR_Getting_Started {
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
	protected $rest_base = 'getting-started';

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
			'/' . $this->rest_base,
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'ur_get_getting_started_settings' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_save_getting_started_settings' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/save-allow-usage-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_save_allow_usage_data' ),
				'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/registration-type-selected',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ur_registration_type_install_pages' ),
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

		$settings_to_update   = $request['settings'];
		$default_form_page_id = get_option( 'user_registration_default_form_page_id' );

		if ( isset( $settings_to_update['user_registration_general_setting_login_options'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_login_options', $settings_to_update['user_registration_general_setting_login_options'] );
		}

		if ( isset( $settings_to_update['user_registration_form_setting_enable_strong_password'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_enable_strong_password', $settings_to_update['user_registration_form_setting_enable_strong_password'] );
		}
		if ( isset( $settings_to_update['user_registration_form_setting_minimum_password_strength'] ) ) {
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_minimum_password_strength', $settings_to_update['user_registration_form_setting_minimum_password_strength'] );
		}

		if ( isset( $settings_to_update['user_registration_end_setup_wizard'] ) ) {
			update_option( 'user_registration_first_time_activation_flag', false );
			update_option( 'user_registration_onboarding_skipped', false );
			delete_option( 'user_registration_onboarding_skipped_step' );
			unset( $settings_to_update['user_registration_end_setup_wizard'] );
		}

		if ( isset( $settings_to_update['user_registration_form_setting_default_user_role'] ) ) {
			$all_roles      = ur_get_default_admin_roles();
			$role_to_update = $settings_to_update['user_registration_form_setting_default_user_role'];
			if ( ! isset( $all_roles[ $role_to_update ] ) ) {
				$role_to_update = isset( array_keys( $all_roles )[ $role_to_update ] ) ? array_keys( $all_roles )[ $role_to_update ] : 'subscriber';
			}
			update_post_meta( absint( $default_form_page_id ), 'user_registration_form_setting_default_user_role', $role_to_update );
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
	 * Save settings for allow usage data.
	 *
	 * @since 4.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return array settings.
	 */
	public static function ur_save_allow_usage_data( $request ) {

		if ( ! isset( $request['settings'] ) ) {
			return;
		}

		$settings_to_update = $request['settings'];

		foreach ( $settings_to_update as $option => $value ) {

			if ( 'yes' === $value || 'no' === $value ) {
				$value = ur_string_to_bool( $value );
			}

			if ( 'user_registration_allow_email_updates' === $option && $value ) {
				$admin_email = get_option( 'new_admin_email', '' );
				if ( isset( $settings_to_update['user_registration_updates_admin_email'] ) && ! empty( $settings_to_update['user_registration_updates_admin_email'] ) ) {
					$admin_email = $settings_to_update['user_registration_updates_admin_email'];
				}

				self::send_email_to_tracking_server( $admin_email );
			}
			update_option( $option, $value );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Settings submitted successfully', 'user-registration' ),
			),
			200
		);
	}

	/**
	 * Install required pages as per registration type selected.
	 *
	 * @since 4.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return array settings.
	 */
	public static function ur_registration_type_install_pages( $request ) {

		if ( ! isset( $request['registrationType'] ) ) {
			return;
		}

		update_option( 'users_can_register', true );
		update_option( 'user_registration_login_options_prevent_core_login', true );

		include_once untrailingslashit( plugin_dir_path( UR_PLUGIN_FILE ) ) . '/includes/admin/functions-ur-admin.php';

		$page_details = array(
			'anyone_can_register' => array(
				'title'         => esc_html__( 'Guest Registration', 'user-registration' ),
				'desc'          => esc_html__( 'Users will be allowed to register through User Registration Form', 'user-registration' ),
				'page_url'      => '',
				'page_url_text' => '',
				'page_slug'     => '',
				'status'        => 'enabled',
				'status_label'  => esc_html__( 'Enabled', 'user-registration' ),
			),
		);

		$page_details['default_wordpress_login'] = array(
			'title'         => esc_html__( 'Default WordPress Login/Registration', 'user-registration' ),
			'desc'          => esc_html__( 'Default WordPress login page wp-login.php will be disabled.', 'user-registration' ),
			'page_url'      => '',
			'page_url_text' => '',
			'page_slug'     => '',
			'status'        => 'disabled',
			'status_label'  => esc_html__( 'Disabled', 'user-registration' ),
		);

		$pages           = apply_filters( 'user_registration_create_pages', array() );
		$default_post_id = 0;
		$hasposts        = get_posts( 'post_type=user_registration' );

		$post_content          = '';
		$membership_field_name = 'membership_field_' . ur_get_random_number();
		if ( 'user_registration_normal_registration' === $request['registrationType'] ) {
			if ( 0 === count( $hasposts ) ) {
				$post_content = '[[[{"field_key":"user_login","general_setting":{"label":"Username","description":"","field_name":"user_login","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":"","username_length":"","username_character":"1"},"icon":"ur-icon ur-icon-user"}],[{"field_key":"user_email","general_setting":{"label":"User Email","description":"","field_name":"user_email","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-email"}]],[[{"field_key":"user_pass","general_setting":{"label":"User Password","description":"","field_name":"user_pass","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password"}],[{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","description":"","field_name":"user_confirm_password","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password-confirm"}]]]';
			}
		} elseif ( 0 === count( $hasposts ) ) {
			$post_content = '[[[{"field_key":"user_login","general_setting":{"label":"Username","description":"","field_name":"user_login","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":"","username_length":"","username_character":"1"},"icon":"ur-icon ur-icon-user"}],[{"field_key":"user_email","general_setting":{"label":"User Email","description":"","field_name":"user_email","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-email"}]],[[{"field_key":"user_pass","general_setting":{"label":"User Password","description":"","field_name":"user_pass","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password"}],[{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","description":"","field_name":"user_confirm_password","placeholder":"","required":"1","hide_label":"false"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-password-confirm"}]],[[{"field_key":"membership","general_setting":{"label":"Membership Field","description":"","field_name":"' . $membership_field_name . '","placeholder":"","required":"false","hide_label":"false","membership_listing_option":"all"},"advance_setting":{"custom_class":""},"icon":"ur-icon ur-icon-membership-field"}]]]';
		}

		if ( 0 === count( $hasposts ) ) {
			// Insert default form.
			$default_post_id = wp_insert_post(
				array(
					'post_type'      => 'user_registration',
					'post_title'     => esc_html__( 'Default form', 'user-registration' ),
					'post_content'   => $post_content,
					'post_status'    => 'publish',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);

			update_option( 'user_registration_default_form_page_id', $default_post_id );
		}

		$default_form_page_id = get_option( 'user_registration_default_form_page_id', $default_post_id );

		$page_details['default_form_id'] = array(
			'title'         => esc_html__( 'Default Registration Form', 'user-registration' ),
			'page_url'      => admin_url( 'admin.php?page=add-new-registration&edit-registration=' . $default_form_page_id ),
			'page_url_text' => esc_html__( 'View Form', 'user-registration' ),
			'page_slug'     => sprintf( esc_html__( 'Form Id: %s', 'user-registration' ), $default_form_page_id ),
			'status'        => 'enabled',
			'status_label'  => esc_html__( 'Created', 'user-registration' ),
		);

		$pages['myaccount'] = array(
			'name'    => _x( 'my-account', 'Page slug', 'user-registration' ),
			'title'   => _x( 'My Account', 'Page title', 'user-registration' ),
			'content' => '[' . apply_filters( 'user_registration_my_account_shortcode_tag', 'user_registration_my_account' ) . ']',
		);

		$pages['login'] = array(
			'name'    => _x( 'login', 'Page slug', 'user-registration' ),
			'title'   => _x( 'Login', 'Page title', 'user-registration' ),
			'content' => '[' . apply_filters( 'user_registration_login_shortcode_tag', 'user_registration_login' ) . ']',
		);

		$pages['lost_password'] = array(
			'name'    => _x( 'lost-password', 'Page slug', 'user-registration' ),
			'title'   => _x( 'Lost Password', 'Page title', 'user-registration' ),
			'content' => '[user_registration_lost_password]',
		);

		if ( 'user_registration_normal_registration' === $request['registrationType'] ) {
			if ( $default_form_page_id ) {
				$pages['registration'] = array(
					'name'    => _x( 'registration', 'Page slug', 'user-registration' ),
					'title'   => _x( 'Registration', 'Page title', 'user-registration' ),
					'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . esc_attr( $default_form_page_id ) . '"]',
				);
			}
		} else {
			$enabled_features = get_option( 'user_registration_enabled_features', array() );
			array_push( $enabled_features, 'user-registration-membership' );
			array_push( $enabled_features, 'user-registration-payment-history' );
			array_push( $enabled_features, 'user-registration-content-restriction' );
			update_option( 'user_registration_enabled_features', $enabled_features );
			update_option( 'user_registration_membership_installed_flag', true );
			Database::create_tables();
			update_option( 'ur_membership_default_membership_field_name', $membership_field_name );
			UR_Install::create_default_membership();
//			$membership_group_id = UR_Install::create_default_membership_group( array( array( 'ID' => "$membership_id" ) ) ); //removed currently since we decided not go forward with a required group.

			if ( $default_form_page_id ) {
				$pages['membership_registration'] = array(
					'name'    => _x( 'membership-registration', 'Page slug', 'user-registration' ),
					'title'   => _x( 'Membership Registration', 'Page title', 'user-registration' ),
					'option'  => 'user_registration_member_registration_page_id',
					'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . esc_attr( $default_form_page_id ) . '"]',
				);
			}

			$pages['membership_pricing']  = array(
				'name'    => _x( 'membership-pricing', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Membership Pricing', 'Page title', 'user-registration' ),
				'option'  => '',
				'content' => '[user_registration_groups]',
			);
			$pages['membership_thankyou'] = array(
				'name'    => _x( 'membership-thankyou', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Membership ThankYou', 'Page title', 'user-registration' ),
				'option'  => 'user_registration_thank_you_page_id',
				'content' => '[user_registration_membership_thank_you]',
			);

			$page_details['membership_details'] = array(
				'page_url'      => admin_url( 'admin.php?page=user-registration-membership&action=add_new_membership' ),
				'page_url_text' => esc_html__( 'Create Membership', 'user-registration' ),
				'title'         => esc_html__( '+ Create Membership', 'user-registration' ),
				'page_slug'     => '',
			);
		}

		foreach ( $pages as $key => $page ) {
			$post_id = ur_create_page( esc_sql( $page['name'] ), 'user_registration_' . $key . '_page_id', wp_kses_post( ( $page['title'] ) ), wp_kses_post( $page['content'] ) );

			if ( 'login' === $key ) {
				update_option( 'user_registration_login_options_login_redirect_url', $post_id );
			}

			if ( ! empty( $page['option'] ) ) {
				update_option( $page['option'], $post_id );
			}
			$page_details[ get_post_field( 'post_name', $post_id ) ] = array(
				'page_url'      => get_permalink( $post_id ),
				'page_url_text' => esc_html__( 'View Page', 'user-registration' ),
				'title'         => get_the_title( $post_id ) . esc_html__( ' Page', 'user-registration' ),
				'page_slug'     => '/' . get_post_field( 'post_name', $post_id ),
			);
		}

		return new \WP_REST_Response(
			array(
				'success'      => true,
				'page_details' => $page_details,
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
			'general_settings' => array(
				'title'    => __( 'General', 'user-registration' ),
				'settings' => array(
					'general'      => array(
						array(
							'title'   => __( 'User Approval And Login Option', 'user-registration' ),
							'desc'    => __( 'This option lets you choose login option after user registration.', 'user-registration' ),
							'id'      => 'user_registration_general_setting_login_options',
							'type'    => 'select',
							'default' => 0,
							'options' => ur_login_option(),
						),
						array(
							'title'   => __( 'Prevent WP Dashboard Access', 'user-registration' ),
							'desc'    => __( 'Selected user roles will not be able to view and access the WP Dashboard area.', 'user-registration' ),
							'id'      => 'user_registration_general_setting_disabled_user_roles',
							'type'    => 'multiselect',
							'default' => array( array_search( 'subscriber', array_keys( $all_roles_except_admin ) ) => 'subscriber' ),
							'options' => $all_roles_except_admin,
						),
					),
					'registration' => array(
						array(
							'title'   => __( 'Enable Strong Password', 'user-registration' ),
							'desc'    => __( 'Enforce strong password.', 'user-registration' ),
							'id'      => 'user_registration_form_setting_enable_strong_password',
							'type'    => 'switch',
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
							'default' => 'subscriber',
							'options' => $all_roles,
						),
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
	 * Send email to tracking server.
	 *
	 * @since 4.0
	 *
	 * @param string $email Email address.
	 *
	 * @return void
	 */
	private static function send_email_to_tracking_server( $email ) {
		wp_remote_post(
			'https://stats.wpeverest.com/wp-json/tgreporting/v1/process-email/',
			array(
				'method'      => 'POST',
				'timeout'     => 10,
				'redirection' => 5,
				'httpversion' => '1.0',
				'headers'     => array(
					'user-agent' => 'UserRegistration/' . UR()->version . '; ' . get_bloginfo( 'url' ),
				),
				'body'        => array(
					'data' => array(
						'email'       => $email,
						'website_url' => get_bloginfo( 'url' ),
						'plugin_name' => UR_PRO_ACTIVE ? 'User Registration PRO' : 'User Registration',
						'plugin_slug' => plugin_basename( UR_PLUGIN_FILE ),
					),
				),
			)
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
