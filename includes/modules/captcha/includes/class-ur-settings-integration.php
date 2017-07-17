<?php
/**
 * UserRegistration General Settings
 *
 * @class    UR_Settings_Integration
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
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
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'integration';
			$this->label = __( 'Integration', 'user-registration' );

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_integration_settings', array(

					array(
						'title' => __( 'Google reCaptcha Integation', 'user-registration' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'integration_options',
					),

					array(
						'title'    => __( 'Site Key', 'user-registration' ),
						'desc'     => sprintf( __('Get site key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),						'id'       => 'user_registration_integration_setting_recaptcha_site_key',
						'default'  => '',
						'type'     => 'text',
						'class'    => '',
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,

					),
					array(
						'title'    => __( 'Secret Key', 'user-registration' ),
						'desc'     => sprintf( __('Get secret key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
						'id'       => 'user_registration_integration_setting_recaptcha_site_secret',
						'default'  => '',
						'type'     => 'text',
						'class'    => '',
						'css'      => 'min-width: 350px;',
						'desc_tip' => true,

					),


					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),


				)
			);

			return apply_filters( 'user_registration_get_integration_settings_' . $this->id, $settings );
		}

		/**
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new UR_Settings_Integration ();
