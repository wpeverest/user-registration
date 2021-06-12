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
				''                  => __( 'General Options', 'user-registration' ),
				'login-options'     => __( 'Login Options', 'user-registration' ),
				'frontend-messages' => __( 'Frontend Messages', 'user-registration' ),
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
					'title' =>  __( 'General Options', 'user-registration' ),
					'sections' => array (
						'general_options' => array(
							'title' => __( 'General', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
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
									'title'    => __( 'Enable hide/show password', 'user-registration' ),
									'desc'     => __( 'Check to enable hide/show password icon.', 'user-registration' ),
									'id'       => 'user_registration_login_option_hide_show_password',
									'type'     => 'checkbox',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
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
							),
						),
						'my_account_options' => array(
							'title' => __( 'My account Section', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
								array(
									'title'    => __( 'My account page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains your login form: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_my_account' ) ),
									'id'       => 'user_registration_myaccount_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => 'min-width:350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Ajax submission on edit profile', 'user-registration' ),
									'desc'     => __( 'Check to enable ajax form submission on edit profile', 'user-registration' ),
									'id'       => 'user_registration_ajax_form_submission_on_edit_profile',
									'type'     => 'checkbox',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
								array(
									'title'    => __( 'Disable profile picture', 'user-registration' ),
									'desc'     => __( 'Check to disable profile picture in edit profile page.', 'user-registration' ),
									'id'       => 'user_registration_disable_profile_picture',
									'type'     => 'checkbox',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
								array(
									'title'    => __( 'Disable logout confirmation', 'user-registration' ),
									'desc'     => __( 'Check to disable logout confirmation.', 'user-registration' ),
									'id'       => 'user_registration_disable_logout_confirmation',
									'type'     => 'checkbox',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
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
							),
						),
						'endpoint_options' => array(
							'title' => __( 'Endpoints Section', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '<strong>' . __( 'Endpoints: ', 'user-registration' ) . '</strong>' . __( 'Endpoints are appended to your page URLs to handle specific actions on the accounts pages. They should be unique and can be left blank to disable the endpoint.', 'user-registration' ),
							'settings' => array(
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
							),
						),
					),
				),
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
					'title' =>  __( 'Frontend Messages', 'user-registration' ),
					'sections' => array (
						'frontend_success_messages_settings' => array(
							'title' => __( 'Success Messages', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
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
							),
						),
						'frontend_error_message_messages_settings' => array(
							'title' => __( 'Error Messages', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
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
									'title'    => __( 'Special Character Validation in Username', 'user-registration' ),
									'desc'     => __( 'Enter the error message in form submission on username', 'user-registration' ),
									'id'       => 'user_registration_form_submission_error_message_disallow_username_character',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => __( 'Please enter the valid username', 'user-registration' ),
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
							),
						),
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
					'title' => __( 'Login Options', 'user-registration' ),
					'sections' => array (
						'login_options_settings' => array(
							'title' => __( 'General', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
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
									'title'    => __( 'Enable Ajax Login', 'user-registration' ),
									'desc'     => __( 'This option lets you to enable the ajax form submission', 'user-registration' ),
									'id'       => 'ur_login_ajax_submission',
									'type'     => 'checkbox',
									'desc_tip' => __( 'Check to field to enable the ajax form submission.', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
								array(
									'title'    => __( 'Enable remember me', 'user-registration' ),
									'desc'     => __( 'Enable', 'user-registration' ),
									'id'       => 'user_registration_login_options_remember_me',
									'type'     => 'checkbox',
									'desc_tip' => __( 'Check to enable/disable remember me.', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'default'  => 'yes',
								),

								array(
									'title'    => __( 'Enable lost password', 'user-registration' ),
									'desc'     => __( 'Enable', 'user-registration' ),
									'id'       => 'user_registration_login_options_lost_password',
									'type'     => 'checkbox',
									'desc_tip' => __( 'Check to enable/disable lost password.', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'default'  => 'yes',
								),

								array(
									'title'    => __( 'Hide Field Labels', 'user-registration' ),
									'desc'     => __( 'Hide', 'user-registration' ),
									'id'       => 'user_registration_login_options_hide_labels',
									'type'     => 'checkbox',
									'desc_tip' => __( 'Check to hide field labels.', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),

								array(
									'title'    => __( 'Enable google reCaptcha', 'user-registration' ),
									'desc'     => __( 'Enable', 'user-registration' ),
									'id'       => 'user_registration_login_options_enable_recaptcha',
									'type'     => 'checkbox',
									'desc_tip' => sprintf( __( 'Enable %1$s %2$s reCaptcha %3$s support', 'user-registration' ), '<a title="', 'Please make sure the site key and secret are not empty in setting page." href="' . admin_url() . 'admin.php?page=user-registration-settings&tab=integration" target="_blank">', '</a>' ),
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
									'title'      => __( 'Prevent Core Login', 'user-registration' ),
									'desc'       => __( 'Enable Prevent Core Login', 'user-registration' ),
									'id'         => 'user_registration_login_options_prevent_core_login',
									'type'       => 'checkbox',
									'desc_tip'   => __( 'Check to disable WordPress default login or registration page.', 'user-registration' ),
									'css'        => 'min-width: 350px;',
									'default'    => 'no',
									'desc_field' => __( 'Please make sure that you have created a login or my-account page which has a login form before enabling this option. Learn how to create a login form <a href="https://docs.wpeverest.com/docs/user-registration/registration-form-and-login-form/how-to-show-login-form/" target="_blank">here</a>.', 'user-registration' ),
								),

								array(
									'title'    => __( 'Redirect to Login Page', 'user-registration' ),
									'desc'     => __( 'Select the login page where you wants to redirect.', 'user-registration' ),
									'id'       => 'user_registration_login_options_login_redirect_url',
									'type'     => 'single_select_page',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'class'    => 'ur-redirect-to-login-page',
									'default'  => '',
								),
							),
						),
						'login_form_labels_settings' => array(
							'title' => __( 'Labels', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
								array(
									'title'    => __( 'Username or Email', 'user-registration' ),
									'desc'     => __( 'This option lets you edit the "Username or Email" field label.', 'user-registration' ),
									'id'       => 'user_registration_label_username_or_email',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => __( 'Username or email address', 'user-registration' ),
								),

								array(
									'title'    => __( 'Password', 'user-registration' ),
									'desc'     => __( 'This option lets you edit the "Password" field label.', 'user-registration' ),
									'id'       => 'user_registration_label_password',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => __( 'Password', 'user-registration' ),
								),

								array(
									'title'    => __( 'Remember me', 'user-registration' ),
									'desc'     => __( 'This option lets you edit the "Remember me" option label.', 'user-registration' ),
									'id'       => 'user_registration_label_remember_me',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => __( 'Remember me', 'user-registration' ),
								),

								array(
									'title'    => __( 'Login', 'user-registration' ),
									'desc'     => __( 'This option lets you edit the "Login" button label.', 'user-registration' ),
									'id'       => 'user_registration_label_login',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => __( 'Login', 'user-registration' ),
								),

								array(
									'title'    => __( 'Lost your password?', 'user-registration' ),
									'desc'     => __( 'This option lets you edit the "Lost your password?" option label.', 'user-registration' ),
									'id'       => 'user_registration_label_lost_your_password',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => __( 'Lost your password?', 'user-registration' ),
								),
							),
						),
						'login_form_placeholders_settings' => array(
							'title' => __( 'Placeholders', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
								array(
									'title'    => __( 'Username or Email Field', 'user-registration' ),
									'desc'     => __( 'This option lets you set placeholder for the "Username or Email" field.', 'user-registration' ),
									'id'       => 'user_registration_placeholder_username_or_email',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => '',
								),

								array(
									'title'    => __( 'Password Field', 'user-registration' ),
									'desc'     => __( 'This option lets you set placeholder for the "Username or Email" field.', 'user-registration' ),
									'id'       => 'user_registration_placeholder_password',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => '',
								),
							),
						),
						'login_form_messages_settings' => array(
							'title' => __( 'Messages', 'user-registration' ),
							'type'  => 'card',
							'desc'  => '',
							'settings' => array(
								array(
									'title'    => __( 'Username Required', 'user-registration' ),
									'desc'     => __( 'Show this message when username is empty.', 'user-registration' ),
									'id'       => 'user_registration_message_username_required',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'Username is required.',
								),

								array(
									'title'       => __( 'Empty Password', 'user-registration' ),
									'desc'        => __( 'Show this message when password is empty.', 'user-registration' ),
									'id'          => 'user_registration_message_empty_password',
									'type'        => 'text',
									'desc_tip'    => true,
									'css'         => 'min-width: 350px;',
									'default'     => '',
									'placeholder' => 'Default message from WordPress',
								),

								array(
									'title'       => __( 'Invalid/Unknown Username', 'user-registration' ),
									'desc'        => __( 'Show this message when username is unknown or invalid.', 'user-registration' ),
									'id'          => 'user_registration_message_invalid_username',
									'type'        => 'text',
									'desc_tip'    => true,
									'css'         => 'min-width: 350px;',
									'default'     => '',
									'placeholder' => 'Default message from WordPress',
								),

								array(
									'title'    => __( 'Unknown Email', 'user-registration' ),
									'desc'     => __( 'Show this message when email is unknown.', 'user-registration' ),
									'id'       => 'user_registration_message_unknown_email',
									'type'     => 'text',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'A user could not be found with this email address.',
								),

								array(
									'title'       => __( 'Pending Approval', 'user-registration' ),
									'desc'        => __( 'Show this message when an account is pending approval.', 'user-registration' ),
									'id'          => 'user_registration_message_pending_approval',
									'type'        => 'text',
									'desc_tip'    => true,
									'css'         => 'min-width: 350px;',
									'default'     => '',
									'placeholder' => 'Default message from WordPress',
								),

								array(
									'title'       => __( 'Denied Account', 'user-registration' ),
									'desc'        => __( 'Show this message when an account is has been denied.', 'user-registration' ),
									'id'          => 'user_registration_message_denied_account',
									'type'        => 'text',
									'desc_tip'    => true,
									'css'         => 'min-width: 350px;',
									'default'     => '',
									'placeholder' => 'Default message from WordPress',
								),
							),
						),
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
			}

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_General();
