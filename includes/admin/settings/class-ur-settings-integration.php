<?php
/**
 * UserRegistration Integration Settings
 *
 * @class    UR_Settings_Integration
 * @version  1.0.0
 * @package  UserRegistrationPRO/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Integration ' ) ) :

	/**
	 * UR_Settings_Integration Class
	 */
	class UR_Settings_Integration extends UR_Settings_Page {

		/**
		 * Integrations classes.
		 *
		 * @var array
		 */
		public $integrations = array();

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'integration';

			/**
			 * Constructor.
			 */
		public function __construct() {

			$this->id    = 'integration';
			$this->label = __( 'Integration', 'user-registration' );

			
			$this->integrations = apply_filters( 'user_registration_integrations_classes', $this->integrations );
			if ( ! empty( $this->integrations ) ) {
				add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
				add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
				add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			}



		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$integrations = $this->get_integrations();

			$settings = apply_filters(
				'user_registration_integration_settings',
				array(
					'title'    => '',
					'sections' => $integrations,
				)
			);
			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Retrive Email Data.
		 */
		public function get_integrations() {
			return $this->integrations;
		}

		/**
		 * Save Email Settings.
		 */
		public function save() {
			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section, $hide_save_button;
			$settings         = $this->get_settings();
			$hide_save_button = true;
			UR_Admin_Settings::output_fields( $settings );
		}
	}

endif;

return new UR_Settings_Integration();
