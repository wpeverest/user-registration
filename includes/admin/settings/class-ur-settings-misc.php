<?php
/**
 * UserRegistration Import Export Settings
 *
 * @class    UR_Settings_Misc
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Misc' ) ) :

	/**
	 * UR_Settings_Misc Class
	 */
	class UR_Settings_Misc extends UR_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'misc';
			$this->label = __( 'Misc', 'user-registration' );

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
				''    => __( 'Advanced', 'user-registration' ),
			);

			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings = apply_filters(
				'user_registration_advanced_settings',
				array(
					'title'    => __( '', 'user-registration' ),
					'sections' => array(
						'advanced_settings' => array(
							'title'    => __( 'Advanced', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Uninstall User Registration', 'user-registration' ),
									'desc'     => __( '<strong>Heads Up!</strong> Check this if you would like to remove ALL User Registration data upon plugin deletion.', 'user-registration' ),
									'id'       => 'user_registration_advanced_uninstall_option',
									'type'     => 'checkbox',
									'desc_tip' => 'All user registration forms, pages and users collected custom data will be deleted upon plugin uninstallation.',
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
							),
						),
					),
				)
			);

			return apply_filters( 'user_registration_get_advanced_settings_' . $this->id, $settings );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;
			$settings = $this->get_settings();
			
			$settings = apply_filters( 'user_registration_get_output_settings_' . $this->id, $settings );

			$settings = isset( $settings) ? $settings : $this->get_settings();

			UR_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings
		 */
		public function save() {

			global $current_section;
			$settings = $this->get_settings();

			$settings = apply_filters( 'user_registration_get_save_settings_' . $this->id, $settings );

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_Misc();
