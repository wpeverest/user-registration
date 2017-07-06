<?php
/**
 * UserRegistration General Settings
 *
 * @class    UR_Settings_General
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$all_roles = ur_get_default_admin_roles();


			$settings = apply_filters(
				'user_registration_general_settings', array(

					array(
						'title' => __( 'General Options', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_options',
					),

					array(
						'title'    => __( 'Default user role', 'user-registration' ),
						'desc'     => __( 'This option lets you choose user role for frontend registration.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_default_user_role',
						'default'  => 'subscriber',
						'type'     => 'select',
						'class'    => 'ur-enhanced-select',
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,
						'options'  => $all_roles,
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
						'options'  => $all_roles,
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
						'display'  => 'none'
					),

					array(
						'title'    => __( 'Enable strong password', 'user-registration' ),
						'desc'     => __( 'Tick here if you want to use strong password on user registration form.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_enable_strong_password',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Form submit button label', 'user-registration' ),
						'desc'     => __( 'This option let you change the submit button label.', 'user-registration' ),
						'id'       => 'user_registration_general_setting_form_submit_label',
						'default'  => 'Submit',
						'type'     => 'text',
						'autoload' => false,
						'desc_tip' => true,
						'css'      => 'min-width: 350px;',


					),

					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),

					array(
						'title' => __( 'My account endpoints', 'user-registration' ),
						'type'  => 'title',
						'desc'  => __( 'Endpoints are appended to your page URLs to handle specific actions on the accounts pages. They should be unique and can be left blank to disable the endpoint.', 'user-registration' ),
						'id'    => 'account_endpoint_options',
					),

					array(
						'title'    => __( 'Edit account', 'user-registration' ),
						'desc'     => __( 'Endpoint for the "My account &rarr; Edit account" page.', 'user-registration' ),
						'id'       => 'user_registration_myaccount_edit_account_endpoint',
						'type'     => 'text',
						'default'  => 'edit-account',
						'desc_tip' => true,
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
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_General();
