<?php
/**
 * UserRegistration General Settings
 *
 * @class    UR_Settings_General
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_General' ) ) :

	/**
	 * UR_Settings_General Class
	 */
	class UR_Settings_General extends UR_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'general';
			$this->label = __( 'General', 'user-registration' );

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''                    => __( 'General Options', 'user-registration' ),
				'login-options'       => __( 'Login Options', 'user-registration' ),
				'frontend-messages'   => __( 'Frontend Messages', 'user-registration' ),
				'export-users'        => __( 'Export Users', 'user-registration' ),
				'import-export-forms' => __( 'Import/Export Forms', 'user-registration' ),
			);

			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get General settings settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$all_roles = ur_get_default_admin_roles();

			$all_roles_except_admin = $all_roles;

			unset( $all_roles_except_admin['administrator'] );

			$settings = apply_filters(
				'user_registration_general_settings',
				array(

					array(
						'title' => __( 'General Options', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_options',
					),
					array(
						'title'    => __( 'User login option', 'user-registration' ),
						'desc'     => __( 'This option lets you choose login option after user registration.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_login_options',
						'default'  => 'default',
						'type'     => 'select',
						'class'    => 'ur-enhanced-select',
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
						'options'  => ur_login_option(),
					),
					array(
						'title'    => __( 'Prevent dashboard access', 'user-registration' ),
						'desc'     => __( 'This option lets you limit which roles you are willing to prevent dashboard access.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_disabled_user_roles',
						'default'  => array( 'subscriber' ),
						'type'     => 'multiselect',
						'class'    => 'ur-enhanced-select',
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
						'options'  => $all_roles_except_admin,
					),
					array(
						'title'    => __( 'Uninstall Option', 'user-registration' ),
						'desc'     => __( 'Heads Up! Check this if you would like to remove ALL User Registration data upon plugin deletion.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_uninstall_option',
						'type'     => 'checkbox',
						'desc_tip' => 'All user registration forms, pages and users data will be unrecoverable.',
						'css'      => 'min-width: 350px;',
						'default'  => 'no',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),

					array(
						'title' => __( 'My account Section', 'user-registration' ),
						'type'  => 'title',
						'id'    => 'my_account_options',
					),
					array(
						'title'    => __( 'My account page', 'user-registration' ),
						'desc'     => sprintf( __( 'Page contents: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_my_account' ) ),
						'id'       => 'user_registration_myaccount_page_id',
						'type'     => 'single_select_page',
						'default'  => '',
						'class'    => 'ur-enhanced-select-nostd',
						'css'      => 'min-width:350px;',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Layout', 'user-registration' ),
						'desc'     => __( 'This option lets you choose layout for user registration my account tab.', 'user-registration' ),
						'id'       => 'user_registration_my_account_layout',
						'default'  => 'horizontal',
						'type'     => 'select',
						'class'    => 'ur-enhanced-select',
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
						'options'  => array(
							'horizontal' => __( 'Horizontal', 'user-registration' ),
							'vertical'   => __( 'Vertical', 'user-registration' ),
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'my_account_options',
					),
					array(
						'title' => __( '', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '<strong>' . __( 'Endpoints: ', 'user-registration' ) . '</strong>' . __( 'Endpoints are appended to your page URLs to handle specific actions on the accounts pages. They should be unique and can be left blank to disable the endpoint.', 'user-registration' ),
						'css'   => 'min-width: 250px;',
						'id'    => 'account_endpoint_options',
					),
					array(
						'title'    => __( 'Edit profile', 'user-registration' ),
						'desc'     => __( 'Endpoint for the "My account &rarr; Edit profile" page.', 'user-registration' ),
						'id'       => 'user_registration_myaccount_edit_profile_endpoint',
						'type'     => 'text',
						'default'  => 'edit-profile',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Change Password', 'user-registration' ),
						'desc'     => __( 'Endpoint for the "My account &rarr; Change Password" page.', 'user-registration' ),
						'id'       => 'user_registration_myaccount_change_password_endpoint',
						'type'     => 'text',
						'default'  => 'edit-password',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'Lost password', 'user-registration' ),
						'desc'     => __( 'Endpoint for the "My account &rarr; Lost password" page.', 'user-registration' ),
						'id'       => 'user_registration_myaccount_lost_password_endpoint',
						'type'     => 'text',
						'default'  => 'lost-password',
						'desc_tip' => true,
					),
					array(
						'title'    => __( 'User logout', 'user-registration' ),
						'desc'     => __( 'Endpoint for the triggering logout. You can add this to your menus via a custom link: yoursite.com/?user-logout=true', 'user-registration' ),
						'id'       => 'user_registration_logout_endpoint',
						'type'     => 'text',
						'default'  => 'user-logout',
						'desc_tip' => true,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'account_endpoint_options',
					),
				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Settings for frontend messages customization.
		 *
		 * @return array
		 */
		public function get_frontend_messages_settings() {

			$settings = apply_filters(
				'user_registration_frontend_messages_settings',
				array(

					array(
						'title' => __( 'Success Messages', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'frontend_success_messages_settings',
					),

					array(
						'title'    => __( 'Manual login after registration', 'user-registration' ),
						'desc'     => __( 'Enter the text message after successful form submission on manual login after registration.', 'user-registration' ),
						'id'       => 'user_registration_successful_form_submission_message_manual_registation',
						'type'     => 'textarea',
						'desc_tip' => true,
						'css'      => 'min-width: 350px; min-height: 100px;',
						'default'  => __( 'User successfully registered.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Email confirmation to login', 'user-registration' ),
						'desc'     => __( 'Enter the text message after successful form submission on email confirmation to login.', 'user-registration' ),
						'id'       => 'user_registration_successful_form_submission_message_email_confirmation',
						'type'     => 'textarea',
						'desc_tip' => true,
						'css'      => 'min-width: 350px; min-height: 100px;',
						'default'  => __( 'User registered. Verify your email by clicking on the link sent to your email.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Admin approval after registration', 'user-registration' ),
						'desc'     => __( 'Enter the text message after successful form submission on admin approval after registration.', 'user-registration' ),
						'id'       => 'user_registration_successful_form_submission_message_admin_approval',
						'type'     => 'textarea',
						'desc_tip' => true,
						'css'      => 'min-width: 350px; min-height: 100px;',
						'default'  => __( 'User registered. Wait until admin approves your registration.', 'user-registration' ),
					),

					array(
						'type' => 'sectionend',
						'id'   => 'frontend_success_messages_settings',
					),

					array(
						'title' => __( 'Error Messages', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'frontend_error_message_messages_settings',
					),

					array(
						'title'    => __( 'Required', 'user-registration' ),
						'desc'     => __( 'Enter the error message in form submission on required fields.', 'user-registration' ),
						'id'       => 'user_registration_form_submission_error_message_required_fields',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'This field is required.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Email', 'user-registration' ),
						'desc'     => __( 'Enter the error message in form submission on Email.', 'user-registration' ),
						'id'       => 'user_registration_form_submission_error_message_email',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'Please enter a valid email address.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Website URL', 'user-registration' ),
						'desc'     => __( 'Enter the error message in form submission on website/URL.', 'user-registration' ),
						'id'       => 'user_registration_form_submission_error_message_website_URL',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'Please enter a valid URL.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Number', 'user-registration' ),
						'desc'     => __( 'Enter the error message in form submission on Number.', 'user-registration' ),
						'id'       => 'user_registration_form_submission_error_message_number',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'Please enter a valid number.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Confirm Email', 'user-registration' ),
						'desc'     => __( 'Enter the error message in form submission on Confim Email.', 'user-registration' ),
						'id'       => 'user_registration_form_submission_error_message_confirm_email',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'Email and confirm email not matched.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Confirm Password', 'user-registration' ),
						'desc'     => __( 'Enter the error message in form submission on Confim Password.', 'user-registration' ),
						'id'       => 'user_registration_form_submission_error_message_confirm_password',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'Password and confirm password not matched.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Google reCaptcha', 'user-registration' ),
						'desc'     => __( 'Enter the error message in form submission on google recaptcha.', 'user-registration' ),
						'id'       => 'user_registration_form_submission_error_message_recaptcha',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'Captcha code error, please try again.', 'user-registration' ),
					),

					array(
						'type' => 'sectionend',
						'id'   => 'frontend_error_messages_settings',
					),
				)
			);

			return apply_filters( 'user_registration_get_frontend_messages_settings_' . $this->id, $settings );
		}

		/**
		 * Get settings for login form
		 *
		 * @return array
		 */
		public function get_login_options_settings() {
			$settings = apply_filters(
				'user_registration_login_options_settings',
				array(

					array(
						'title' => __( 'Login Options', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'login_options_settings',
					),

					array(
						'title'    => __( 'Form Template', 'user-registration' ),
						'desc'     => __( 'Choose the login form template.', 'user-registration' ),
						'id'       => 'user_registration_login_options_form_template',
						'type'     => 'select',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => 'default',
						'options'  => array(
							'default'      => __( 'Default', 'user-registration' ),
							'bordered'     => __( 'Bordered', 'user-registration' ),
							'flat'         => __( 'Flat', 'user-registration' ),
							'rounded'      => __( 'Rounded', 'user-registration' ),
							'rounded_edge' => __( 'Rounded Edge', 'user-registration' ),
						),
					),

					array(
						'title'    => __( 'Enable remember me', 'user-registration' ),
						'desc'     => __( 'Check to enable/disable remember me.', 'user-registration' ),
						'id'       => 'user_registration_login_options_remember_me',
						'type'     => 'checkbox',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => 'yes',
					),

					array(
						'title'    => __( 'Enable hide/show password', 'user-registration' ),
						'desc'     => __( 'Check to enable hide/show password icon in login form.', 'user-registration' ),
						'id'       => 'user_registration_login_option_hide_show_password',
						'type'     => 'checkbox',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => 'no',
					),

					array(
						'title'    => __( 'Enable lost password', 'user-registration' ),
						'desc'     => __( 'Check to enable/disable lost password.', 'user-registration' ),
						'id'       => 'user_registration_login_options_lost_password',
						'type'     => 'checkbox',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => 'yes',
					),

					array(
						'title'    => __( 'Enable google reCaptcha', 'user-registration' ),
						'desc'     => sprintf( __( 'Enable %1$s %2$s reCaptcha %3$s support', 'user-registration' ), '<a title="', 'Please make sure the site key and secret are not empty in setting page." href="' . admin_url() . 'admin.php?page=user-registration-settings&tab=integration" target="_blank">', '</a>' ),
						'id'       => 'user_registration_login_options_enable_recaptcha',
						'type'     => 'checkbox',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => 'no',
					),

					array(
						'title'    => __( 'Registration URL', 'user-registration' ),
						'desc'     => __( 'This option lets you enter the registration page url in login form.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_registration_url_options',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
					),

					array(
						'title'    => __( 'Registration URL label', 'user-registration' ),
						'desc'     => __( 'This option lets you enter the label to registration url in login form.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_registration_label',
						'type'     => 'text',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => __( 'Not a member yet? Register now.', 'user-registration' ),
					),

					array(
						'title'    => __( 'Prevent Core Login', 'user-registration' ),
						'desc'     => __( 'Check to disable WordPress default login or registration page.', 'user-registration' ),
						'id'       => 'user_registration_login_options_prevent_core_login',
						'type'     => 'checkbox',
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',
						'default'  => 'no',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'login_options_settings',
					),
				)
			);

			return apply_filters( 'user_registration_get_login_options_settings_' . $this->id, $settings );
		}

		/**
		 * Output the settings.
		 */
		public function output() {

			global $current_section;
			if ( '' === $current_section ) {
				$settings = $this->get_settings();

			} elseif ( 'frontend-messages' === $current_section ) {
				$settings = $this->get_frontend_messages_settings();
			} elseif ( 'login-options' === $current_section ) {
				$settings = $this->get_login_options_settings();
			} elseif ( 'export-users' === $current_section ) {
				$settings = array();
				UR_Admin_Export_Users::output();
			} elseif ( 'import-export-forms' === $current_section ) {
				$settings = array();
				UR_Admin_Import_Export_Forms::output();
			} else {
				$settings = array();
			}

			UR_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings
		 */
		public function save() {

			global $current_section;
			$settings = $this->get_settings();

			if ( '' === $current_section ) {
				$settings = $this->get_settings();

			} elseif ( 'frontend-messages' === $current_section ) {
				$settings = $this->get_frontend_messages_settings();
			} elseif ( 'login-options' === $current_section ) {
				$settings = $this->get_login_options_settings();
			} elseif ( 'export-users' === $current_section ) {
				$settings = array();
			} elseif ( 'import-export-forms' === $current_section ) {
				$settings = array();
			}

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_General();
