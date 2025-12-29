<?php
/**
 * UserRegistrationContentRestriction Settings
 *
 * @class    URCR_Settings_File
 * @version  4.0
 * @package  UserRegistrationContentRestriction/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'URCR_Settings_File ' ) ) :

	/**
	 * URCR_Settings_File Class
	 */
	class URCR_Settings_File extends UR_Settings_Page {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'content_restriction';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'content_restriction';
			$this->label = __( 'Content Restriction', 'user-registration' );
			// add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_filter( 'show_user_registration_setting_message', array( $this, 'urcr_setting_message_show' ) );
			add_action( ' admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		}

		public function register_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'custom-js', UR()->plugin_url() . '/assets/js/modules/content-restriction/admin/urcr-custom' . $suffix . '.js', array( 'jquery' ), UR_VERSION );
		}

		public function urcr_setting_message_show() {
			return true;
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$settings = $this->urcr_settings();
			return apply_filters( 'user_registration_content_restriction_settings' . $this->id, $settings );
		}


		public function urcr_settings() {

			$access_rules_list_link = admin_url( 'admin.php?page=user-registration-content-restriction' );
			$link_html              = sprintf( __( 'Go to <a href="%s">Content Rules page</a> for advanced restrictions', 'user-registration' ), $access_rules_list_link );

			// Build settings array for Advanced section
			$advanced_settings = array();

			// Only include "Enable Advance Logic" option if Pro version is active
			if ( UR_PRO_ACTIVE ) {
				$advanced_settings[] = array(
					'row_class' => 'urcr_enable_disable urcr_content_access_rule_is_advanced_logic_enabled',
					'title'     => __( 'Enable Advance Logic', 'user-registration' ),
					'desc'      => __( 'Check this option to enable advance grouping and logic gates. ', 'user-registration' ),
					'id'        => 'urcr_is_advanced_logic_enabled',
					'default'   => false,
					'desc_tip'  => true,
					'type'      => 'toggle',
					'autoload'  => false,
					'class' => 'urcr-advance-logic-toggle'
				);
			}

			// Build sections array
			$sections = array();

			// Only include Advanced section if it has settings (i.e., if Pro is active)
			if ( ! empty( $advanced_settings ) ) {
				$sections['user_registration_site_restriction_settings'] = array(
					'title'    => __( 'Advanced', 'user-registration' ),
					'type'     => 'card',
					'desc'     => '',
					'settings' => $advanced_settings,
				);
			}

			$sections['user_registration_content_restriction_settings'] = array(
				'title'    => __( 'Global Restriction Settings', 'user-registration' ),
				'type'     => 'card',
				'desc'     => sprintf( __( 'These settings affect whole site restriction as well as individual page/post restriction if enabled. <a href="%1$s" target="_blank" style="text-decoration: underline;" >Learn More.</a>', 'user-registration' ), esc_url_raw( 'https://docs.wpuserregistration.com/docs/content-restriction/' ) ),
				'settings' => array(
					array(
						'title'    => __( 'Restricted Content Message', 'user-registration' ),
						'desc'     => __( 'The message you would like to display in restricted content.', 'user-registration' ),
						'id'       => 'user_registration_content_restriction_message',
						'type'     => 'tinymce',
						'default'  => 'This content is restricted!',
						'css'      => '',
						'show-smart-tags-button' => false,
						'desc_tip' => true,
					),
				),
			);

			return apply_filters(
				'user_registration_content_restriction_settings',
				array(
					'title'    => __( 'Content Restriction Settings', 'user-registration' ),
					'desc'     => UR_PRO_ACTIVE ? $link_html : '',
					'sections' => $sections,
				)
			);
		}

		public function output() {

			wp_enqueue_script( 'custom-js' );

			global $current_section;

			$settings = $this->get_settings( $current_section );

			UR_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings
		 */
		public function save() {

			global $current_section;

			$settings = $this->get_settings( $current_section );

			// Validate advanced logic setting before saving
			if ( isset( $_POST['urcr_is_advanced_logic_enabled'] ) ) {
				$new_value = ur_string_to_bool( $_POST['urcr_is_advanced_logic_enabled'] );
				$old_value = ur_string_to_bool( get_option( 'urcr_is_advanced_logic_enabled', 'no' ) );

				// If trying to disable advanced logic, check if rules with advanced logic exist
				if ( $old_value && ! $new_value ) {
					if ( function_exists( 'urcr_has_rules_with_advanced_logic' ) ) {
						$has_advanced_logic = urcr_has_rules_with_advanced_logic();
						if ( $has_advanced_logic ) {
							UR_Admin_Settings::add_error(
								esc_html__( 'Rules with advanced logic already exist. Please delete them first before disabling advanced logic.', 'user-registration' )
							);
							// Prevent saving by unsetting the POST value
							unset( $_POST['urcr_is_advanced_logic_enabled'] );
							return;
						}
					}
				}
			}

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new URCR_Settings_File();
