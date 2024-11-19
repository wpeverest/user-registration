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

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 999 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_filter( 'show_user_registration_setting_message', array( $this, 'filter_notice' ) );

			if ( isset( $_GET['tab'] ) && 'license' === $_GET['tab'] ) { // phpcs:ignore
				add_filter( 'user_registration_setting_save_label', array( $this, 'user_registration_license_setting_label' ) );
				add_filter( 'user_registration_admin_field_license_options', array( $this, 'license_options_settings' ), 10, 2 );
			}
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

			/**
			 * Filter to get the sections.
			 *
			 * @param array $sections Sections to be enlisted.
			 */
			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get License activation settings
		 *
		 * @return array
		 */
		public function get_settings() {
			/**
			 * Filter to add the license setting.
			 *
			 * @param array License sections to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_license_settings',
				array(
					'title'    => '',
					'sections' => array(
						'license_options_settings' => array(
							'title'    => __( 'License Activation', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '<strong>' . __( 'License: ', 'user-registration' ) . '</strong>' . __( 'Please enter the license key below in order to use our premium addons smoothly.', 'user-registration' ),
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
								array(
									'id'     => 'ur_license_nonce',
									'action' => '_ur_license_nonce',
									'type'   => 'nonce',
								),
							),
						),
					),
				)
			);

			// Replace license input box and display deactivate license button when license is activated.
			if ( get_option( 'user-registration_license_key' ) ) {
				$settings['sections']['license_options_settings']['settings'] = array(
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
								'href'  => wp_nonce_url( remove_query_arg( array( 'deactivated_license', 'activated_license' ), add_query_arg( 'user-registration_deactivate_license', 1 ), ), '_ur_license_nonce' ),
								'class' => 'user_registration-deactivate-license-key',
							),
						),
					),
					array(
						'type' => 'license_options',
						'id'   => 'user_registration_license_section_settings',
					),
				);

				/* translators: %1$s - WPeverest My Account url */
				$settings['sections']['license_options_settings']['desc'] = sprintf( __( 'Your license has been activated. Enjoy using <strong>User Registration</strong>. Please go to %1$sMy Account Page%2$s for more details ', 'user-registration' ), '<a href="https://wpeverest.com/login/" rel="noreferrer noopener" target="_blank">', '</a>' );

				// Hide save changes button from settings when license is activated.
				$GLOBALS['hide_save_button'] = true;

			}

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Setting options to be enlisted.
			 */
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

		/**
		 * License Expiry Settings.
		 *
		 * @param string $settings Settings.
		 * @param mixed  $value Value.
		 */
		public function license_options_settings( $settings, $value ) {
			$license_data     = ur_get_license_plan();
			$license_date_str = ! empty( $license_data->expires ) ? $license_data->expires : '';
			if ( 'lifetime' === $license_date_str || '' === $license_date_str ) {
				$license_date_formatted = $license_date_str;
			} else {
				$license_date_obj       = new DateTime( $license_date_str );
				$license_date_formatted = $license_date_obj->format( 'jS F Y h:i A' );
			}
			$settings .= '<div class="user-registration-global-settings">';
			$settings .= '<label for="user-registration_license_plan">' . esc_html__( 'License Plan', 'user-registration' ) . '</label>';
			$settings .= '<div class="user-registration-global-settings--field">';
			$settings .= ! empty( $license_data->item_name ) ? $license_data->item_name : '';
			$settings .= '</div></div>';
			$settings .= '<div class="user-registration-global-settings">';
			$settings .= '<label for="user-registration_license_plan">' . esc_html__( 'License Expiry Date', 'user-registration' ) . '</label>';
			$settings .= '<div class="user-registration-global-settings--field">';
			$settings .= $license_date_formatted;
			$settings .= '</div></div>';
			return $settings;
		}
	}

endif;

return new UR_Settings_License();
