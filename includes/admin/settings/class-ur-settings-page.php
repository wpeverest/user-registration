<?php
/**
 * UserRegistration Settings Page/Tab
 *
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Page', false ) ) :

	/**
	 * UR_Settings_Page.
	 */
	abstract class UR_Settings_Page {

		/**
		 * Setting page id.
		 *
		 * @var string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @var string
		 */
		protected $label = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings page ID.
		 *
		 * @return string
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get settings page label.
		 *
		 * @return string
		 */
		public function get_label() {
			return $this->label;
		}

		/**
		 * Add this page to settings.
		 *
		 * @param  array $pages Pages.
		 * @return mixed
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings() {
			/**
			 * Filter to retrieve the settings
			 *
			 * @param array Array of settings to be retrieved.
			 */
			return apply_filters( 'user_registration_get_settings_' . $this->id, array() );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			/**
			 * Filter to retrieve the sections.
			 *
			 * @param array Array of sections to retrieve.
			 */
			return apply_filters( 'user_registration_get_sections_' . $this->id, array() );
		}

		/**
		 * Output sections.
		 */
		public function output_sections() {
			global $current_section;

			$sections = $this->get_sections();

			if ( empty( $sections ) ) {
				return;
			}

			echo '<div class="ur-scroll-ui__scroll-nav"><ul class="subsubsub  ur-scroll-ui__items">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				if ( 'login-options' === $id ) {
					echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=user-registration-login-forms' ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . ' ur-scroll-ui__item">' . esc_html( $label ) . '</a></li>';
				} else {
					echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . ' ur-scroll-ui__item">' . esc_html( $label ) . '</a></li>';
				}
			}

			echo '</ul></div>';
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			$settings = $this->get_settings();

			UR_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );

			if ( $current_section ) {
				/**
				 * Action to update the options.
				 *
				 * @param mixed $current_section Section to be updated.
				 */
				do_action( 'user_registration_update_options_' . $this->id . '_' . $current_section );
			}
		}
	}

endif;
