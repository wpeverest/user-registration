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

			$this->id    = 'advanced';
			$this->label = __( 'Advanced', 'user-registration' );

			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_filter( 'user_registration_get_section_parts_' . $this->id, array( $this, 'get_parts' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_parts( $sections ) {
			global $current_section;

			if( 'import-export' !== $current_section ) return $sections;

			$sections = array(
				''                    => __( 'Export Users', 'user-registration' ),
				'import-export-forms' => __( 'Import/Export Forms', 'user-registration' ),
			);

			return $sections;
		}

		/**
		 * Output the settings.
		 */
		public function output() {

			global $current_section;
			global $current_section_part;
			if( 'import-export' !== $current_section ) return;

			add_filter( 'user_registration_settings_hide_save_button', '__return_true' );
			if ( '' === $current_section_part ) {
				$settings = array();
				UR_Admin_Export_Users::output();
			} elseif ( 'import-export-forms' === $current_section_part ) {
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
			global $current_section_part;
			if ( 'import-export' !== $current_section ) return;
			$settings = $this->get_settings();

			if ( '' === $current_section_part ) {
				$settings = array();
			} elseif ( 'import-export-forms' === $current_section_part ) {
				$settings = array();
			} else {
				$settings = array();
			}

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_Import_Export();
