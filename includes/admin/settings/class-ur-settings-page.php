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
		 * List of sections.
		 */
		protected $sections = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			//nav link (left sidebar).
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			
			//vertical tab-like view for sections.
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			
			//main content : options fields as UI.
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			
			//default section. ( automatically selects first section if not set ).
			add_filter( "user_registration_settings_{$this->id}_default_section", array( $this, 'get_default_section' ) );

			//save settings.
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get default section.
		 */
		public function get_default_section( $default_section ) {
			//COMPAT: php >= 7.3
			return $this->get_sections() ? array_key_first( $this->get_sections() ) : $default_section;
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
			$settings = apply_filters( 'user_registration_get_settings_' . $this->id, array() );
			/**
			 * Backward compatibility: previous settings section.
			 */
			// $settings = apply_filters( 'user_registration_' . $this->id . '_settings', $settings );
			return $settings;
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
			return apply_filters( 'user_registration_get_sections_' . $this->id, $this->sections );
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

			echo '<ul class="subsubsub  ur-scroll-ui__items" style="display: flex; flex-direction: column;">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li' .  ( $current_section === $id ? ' class="current" ' : '' ) . '><a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . ' ur-scroll-ui__item"><span class="timeline"></span><span class="submenu">' . esc_html( $label ) . '</span></a></li>';
			}

			echo '</ul>';
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			$settings = $this->get_settings();

			UR_Admin_Settings::output_fields( $settings );
		}

		public function upgrade_to_pro_setting() {
			global $current_section;
			global $current_tab;
			add_filter( 'user_registration_settings_hide_save_button', '__return_true' );
			$title = ucwords( str_replace( '-', ' ', $current_section ) );
			$setting = ucwords( str_replace( '_', ' ', $current_tab ) );

			//in case of integration, list all email marketing addons.
			$args = array();
			if( 'integration' === $current_tab ) {
				$addons = array( 'activecampaign', 'brevo', 'convertkit', 'klaviyo', 'mailchimp', 'mailerlite', 'mailpoet' );
				foreach( $addons as $addon ) {
					$args[] = array(
						'id' => $addon,
						'slug' => 'user-registration-' . $addon,
						'name' => ucwords( $addon )
					);
				}
			} elseif ( 'security' === $current_tab && '2fa' === $current_section ) {
				$args[] = array(
					'id' => 'two-factor-authentication',
					'slug' => 'user-registration-two-factor-authentication',
					'name' => 'Two Factor Authentication' 
				);
			} else {
				$args[] = array(
					'id' => $current_section,
					'slug' => 'user-registration-' . $current_section,
					'name' => 'User Registration - ' . $title
				);
			}

			return apply_filters( 'user_registration_upgrade_to_pro_setting',
				array(
					'title' => '',
					'sections' => array(
						'premium_setting_section' => array(
							'type' => 'card',
							'is_premium' => true,
							'title' => $title,
							'before_desc' => "$setting > $title is only available in User Registration & Membership Pro.",
							'desc' => 'To unlock this setting, consider upgrading to <a href="https://wpuserregistration.com/upgrade/?utm_source=ur-settings-desc&utm_medium=upgrade-link&utm-campaign=lite-version">Pro</a>.',
							'class' => 'ur-upgrade--link',
							'button' => array(
								'button_type' => 'upgrade_link',
								'button_text' => 'Upgrade to Pro',
								'button_link' => 'https://wpuserregistration.com/upgrade/?utm_source=ur-settings-' . $current_section . '&utm_medium=upgrade-link&utm_campaign=lite-version',
							),
						),
					),
				),
				$args
			);
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
