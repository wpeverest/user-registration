<?php
/**
 * UserRegistration License Settings
 *
 * @class    UR_Settings_License
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_License' ) ) :

	/**
	 * UR_Settings_License Class
	 */
	class UR_Settings_License extends UR_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'license';
			$this->label = __( 'License', 'user-registration' );

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_filter( 'show_user_registration_setting_message', array( $this, 'filter_notice' ) );
			add_filter( 'user_registration_setting_save_label', array( $this, 'user_registration_license_setting_label' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'' => __( 'License', 'user-registration' ),
			);

			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get License activation settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_license_settings',
				array(
					'title'    => __( 'License Options', 'user-registration' ),
					'sections' => array(
						'license_options' => array(
							'title'    => __( 'License Activation', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '<strong>' . __( 'License: ', 'user-registration' ) . '</strong>' . __( 'Please enter the license key below inorder to use our premium addons smoothly.', 'user-registration' ),
							'settings' => array(
								array(
									'title'    => __( 'License Key', 'user-registration' ),
									'desc'     => __( 'Please enter the license key', 'user-registration' ),
									'id'       => 'user-registration_license_key',
									'default'  => '',
									'type'     => 'text',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);

			// Replace license input box and display deactivate license button when license is activated.
			if ( get_option( 'user-registration_license_key' ) ) {
				$settings['sections']['license_options']['settings'] = array(
					array(
						'title'    => __( 'Deactivate License', 'user-registration' ),
						'desc'     => '',
						'desc_tip' => __( 'Deactivate the license of User Registration plugin', 'user-registration' ),
						'type'     => 'link',
						'id'       => 'user-registration_deactivate-license_key',
						'css'      => 'background:red; border:none; color:white;',
						'buttons'  => array(
							array(
								'title' => __( 'Deactivate License', 'user-registration' ),
								'href'  => remove_query_arg( array( 'deactivated_license', 'activated_license' ), add_query_arg( 'user-registration_deactivate_license', 1 ) ),
								'class' => 'user_registration-deactivate-license-key',
							),
						),
					),
				);

				$settings['sections']['license_options']['desc'] = __( 'Your license has already been activated. Enjoy using <strong>User Registration</strong>.', 'user-registration' );

				// Hide save changes button from settings when license is activated.
				$GLOBALS['hide_save_button'] = true;

			}

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Output the settings.
		 */
		public function output() {

			global $current_section;
			if ( '' === $current_section ) {
				$settings = $this->get_settings();
			} else {
				$settings = array();
			}

			UR_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Filter Notice for license tab.
		 *
		 * @return bool
		 */
		public function filter_notice() {
			global $current_tab;

			if ( 'license' === $current_tab ) {
				return false;
			}

			return true;
		}

		/**
		 * Label for Save button.
		 *
		 * @return string
		 */
		public function user_registration_license_setting_label() {
			return esc_html__( 'Activate License', 'user-registration' );
		}

	}

endif;

return new UR_Settings_License();
