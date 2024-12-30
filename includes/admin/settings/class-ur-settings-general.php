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
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'general';

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

			/**
			 * Filter to get the setings.
			 *
			 * @param array $settings Setting options to be enlisted.
			 */
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

			/**
			 * Filter to add the options settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_general_settings',
				array(
					'title'    => '',
					'sections' => array(
						'general_options'    => array(
							'title'    => __( 'General', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Prevent WP Dashboard Access', 'user-registration' ),
									'desc'     => __( 'Selected user roles will not be able to view and access the WP Dashboard area.', 'user-registration' ),
									'id'       => 'user_registration_general_setting_disabled_user_roles',
									'default'  => array( 'subscriber' ),
									'type'     => 'multiselect',
									'class'    => 'ur-enhanced-select',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
									'options'  => $all_roles_except_admin,
								),
								array(
									'title'    => __( 'Enable Hide/Show Password', 'user-registration' ),
									'desc'     => __( 'Check this option to enable hide/show password icon beside the password field in both registration and login form.', 'user-registration' ),
									'id'       => 'user_registration_login_option_hide_show_password',
									'type'     => 'toggle',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
							),
						),
						'my_account_options' => array(
							'title'    => __( 'My account Section', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'My Account Page', 'user-registration' ),
									'desc'     => sprintf( __( 'Select the page which contains your login form: [%s]', 'user-registration' ), apply_filters( 'user_registration_myaccount_shortcode_tag', 'user_registration_my_account' ) ), //phpcs:ignore
									'id'       => 'user_registration_myaccount_page_id',
									'type'     => 'single_select_page',
									'default'  => '',
									'class'    => 'ur-enhanced-select-nostd',
									'css'      => 'min-width:350px;',
									'desc_tip' => true,
								),
								array(
									'title'              => __( 'Layout', 'user-registration' ),
									'desc'               => __( 'This option lets you choose the layout for the user registration my account tabs.', 'user-registration' ),
									'id'                 => 'user_registration_my_account_layout',
									'default'            => 'horizontal',
									'type'               => 'radio-group',
									'css'                => 'min-width: 350px;',
									'desc_tip'           => true,
									'options'            => array(
										'horizontal' => __( 'Horizontal', 'user-registration' ),
										'vertical'   => __( 'Vertical', 'user-registration' ),
									),
									'radio-group-images' => array(
										'horizontal' => UR()->plugin_url() . '/assets/images/onboard-icons/horizontal.png',
										'vertical'   => UR()->plugin_url() . '/assets/images/onboard-icons/vertical.png',
									),
								),
								array(
									'title'    => __( 'Ajax Submission on Edit Profile', 'user-registration' ),
									'desc'     => __( 'Check to enable ajax form submission on edit profile i.e. saves profile details on save button click without reloading the page.', 'user-registration' ),
									'id'       => 'user_registration_ajax_form_submission_on_edit_profile',
									'type'     => 'toggle',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
								array(
									'title'    => __( 'Disable Profile Picture', 'user-registration' ),
									'desc'     => __( 'Check to disable profile picture in edit profile page.', 'user-registration' ),
									'id'       => 'user_registration_disable_profile_picture',
									'type'     => 'toggle',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
								array(
									'title'    => __( 'Sync Profile picture', 'user-registration' ),
									'desc'     => __( 'Check to enable if you want to display profile picture on edit profile if form have profile field', 'user-registration' ),
									'id'       => 'user_registration_sync_profile_picture',
									'type'     => 'toggle',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => '',
								),
								array(
									'title'    => __( 'Disable Logout Confirmation', 'user-registration' ),
									'desc'     => __( 'Check to disable logout confirmation.', 'user-registration' ),
									'id'       => 'user_registration_disable_logout_confirmation',
									'type'     => 'toggle',
									'desc_tip' => true,
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
							),
						),
						'endpoint_options'   => array(
							'title'    => __( 'Endpoints Section', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '<strong>' . __( 'Endpoints: ', 'user-registration' ) . '</strong>' . __( 'Endpoints are appended to your page URLs to handle specific actions on the accounts pages. They should be unique and can be left blank to disable the endpoint.', 'user-registration' ),
							'settings' => array(
								array(
									'title'    => __( 'Edit Profile', 'user-registration' ),
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
									'title'    => __( 'User Logout', 'user-registration' ),
									'desc'     => __( 'Endpoint for triggering logout. You can add this to your menus via a custom link: yoursite.com/?user-logout=true', 'user-registration' ),
									'id'       => 'user_registration_logout_endpoint',
									'type'     => 'text',
									'default'  => 'user-logout',
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Settings for frontend messages customization.
		 *
		 * @return array
		 */
		public function get_frontend_messages_settings() {
			/**
			 * Filter to add the frontend messages options settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_frontend_messages_settings',
				array(
					'title'    => '',
					'sections' => array(
						'frontend_success_messages_settings' => array(
							'title'    => __( 'Success Messages', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Auto Approval And Manual Login ', 'user-registration' ),
									'desc'     => __( 'Enter the text message after successful form submission when auto approval and manual login is selected.', 'user-registration' ),
									'id'       => 'user_registration_successful_form_submission_message_manual_registation',
									'type'     => 'textarea',
									'desc_tip' => true,
									'css'      => 'min-width: 350px; min-height: 100px;',
									'default'  => __( 'User successfully registered.', 'user-registration' ),
								),

								array(
									'title'    => __( 'Auto Approval After Email Confirmation', 'user-registration' ),
									'desc'     => __( 'Enter the text message after successful form submission when auto approval and email confirmation is selected.', 'user-registration' ),
									'id'       => 'user_registration_successful_form_submission_message_email_confirmation',
									'type'     => 'textarea',
									'desc_tip' => true,
									'css'      => 'min-width: 350px; min-height: 100px;',
									'default'  => __( 'User registered. Verify your email by clicking on the link sent to your email.', 'user-registration' ),
								),

								array(
									'title'    => __( 'Email Verification Completed', 'user-registration' ),
									'desc'     => __( 'Enter the text message that appears after the email is successfully verified and have access login access.', 'user-registration' ),
									'id'       => 'user_registration_successful_email_verified_message',
									'type'     => 'textarea',
									'desc_tip' => true,
									'css'      => 'min-width: 350px; min-height: 100px;',
									'default'  => __( 'User successfully registered. Login to continue.', 'user-registration' ),
								),

								array(
									'title'    => __( 'Admin Approval', 'user-registration' ),
									'desc'     => __( 'Enter the text message that appears after successful form submission when admin approval is selected.', 'user-registration' ),
									'id'       => 'user_registration_successful_form_submission_message_admin_approval',
									'type'     => 'textarea',
									'desc_tip' => true,
									'css'      => 'min-width: 350px; min-height: 100px;',
									'default'  => __( 'User registered. Wait until admin approves your registration.', 'user-registration' ),
								),
							),
						),
						'frontend_error_message_messages_settings' => array(
							'title'    => __( 'Error Messages', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
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
									'title'    => __( 'reCAPTCHA', 'user-registration' ),
									'desc'     => __( 'Enter the error message in form submission on recaptcha.', 'user-registration' ),
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

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Frontend Message Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_frontend_messages_settings_' . $this->id, $settings );
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
				$settings        = get_login_options_settings();
				$captcha_enabled = get_option( 'user_registration_login_options_enable_recaptcha' );

				if ( ur_string_to_bool( $captcha_enabled ) && ! ur_check_captch_keys( 'login' ) ) {
					echo '<div id="ur-captcha-error" class="notice notice-warning is-dismissible"><p><strong>' . sprintf(
						/* translators: %s - Integration tab url */
						'%s<a href="%s" rel="noreferrer noopener" target="_blank">Add Now.</a>',
						esc_html__( "Seems like you haven't added the CAPTCHA Keys. ", 'user-registration' ),
						esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=captcha' ) )
					) . '</strong></p></div>';
				}
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
			}

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_General();
