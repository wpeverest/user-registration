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
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
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
			$link_html              = sprintf( '<a href="%s">%s</a>', $access_rules_list_link, __( 'Go to Content Rules page for advanced restrictions', 'user-registration' ) );

			return apply_filters(
				'user_registration_content_restriction_settings',
				array(
					'title'    => __( 'Content Restriction Settings', 'user-registration' ),
					'sections' => array(
						'user_registration_content_restriction_settings' => array(
							'title'    => __( 'General', 'user-registration' ),
							'type'     => 'card',
							'desc'     => UR_PRO_ACTIVE ? $link_html : '',
							'settings' => array(
								array(
									'row_class' => 'urcr_enable_disable urcr_content_restriction_enable',
									'title'     => __( 'Enable Content Restriction', 'user-registration' ),
									'desc'      => __( 'Check To Enable Content Restriction', 'user-registration' ),
									'id'        => 'user_registration_content_restriction_enable',
									'default'   => 'yes',
									'desc_tip'  => true,
									'type'      => 'toggle',
									'autoload'  => false,
								),

								array(
									'row_class' => 'urcr_content_restriction_allow_access_to',
									'title'     => __( 'Allow Access To', 'user-registration' ),
									'desc'      => __( 'Select Option To Allow Access To', 'user-registration' ),
									'id'        => 'user_registration_content_restriction_allow_access_to',
									'type'      => 'select',
									'class'     => 'ur-enhanced-select',
									'css'       => 'min-width: 350px;',
									'desc_tip'  => true,
									'options'   => array( 'All Logged In Users', 'Choose Specific Roles', 'Guest Users', 'Memberships' ),
								),

								array(
									'row_class' => 'urcr_content_restriction_allow_access_to_roles',
									'title'     => __( 'Select Roles', 'user-registration' ),
									'desc'      => __( 'The roles selected here will have access to restricted content.', 'user-registration' ),
									'id'        => 'user_registration_content_restriction_allow_to_roles',
									'default'   => array( 'administrator' ),
									'type'      => 'multiselect',
									'class'     => 'ur-enhanced-select',
									'css'       => 'min-width: 350px; ' . ( '1' != get_option( 'user_registration_content_restriction_allow_access_to', '0' ) ) ? 'display:none;' : '',
									'desc_tip'  => true,
									'options'   => ur_get_all_roles(),
								),

								array(
									'title'    => __( 'Restricted Content Message', 'user-registration' ),
									'desc'     => __( 'The message you would like to display in restricted content.', 'user-registration' ),
									'id'       => 'user_registration_content_restriction_message',
									'type'     => 'tinymce',
									'default'  => 'This content is restricted!',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
							),
						),
					),
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

			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new URCR_Settings_File();
