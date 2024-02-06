<?php
/**
 * UserRegistration Import Export Settings
 *
 * @class    UR_Settings_Import_Export
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Import_Export' ) ) :

	/**
	 * UR_Settings_Import_Export Class
	 */
	class UR_Settings_Import_Export extends UR_Settings_Page {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'import_export';

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'import_export';
			$this->label = __( 'Import/Export', 'user-registration' );

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
				''                    => __( 'Export Users', 'user-registration' ),
				'import-export-forms' => __( 'Import/Export Forms', 'user-registration' ),
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Options Settings to be enlisted.
			 */
			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 */
		public function output() {

			global $current_section;
			if ( '' === $current_section ) {
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
				$settings = array();
			} elseif ( 'import-export-forms' === $current_section ) {
				$settings = array();
			} else {
				$settings = array();
			}

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_Import_Export();
