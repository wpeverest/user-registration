<?php
/**
 * UserRegistration General Settings
 *
 * @class    UR_Settings_Integration
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
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
			$recaptcha_type = get_option( 'user_registration_integration_setting_recaptcha_version', 'v2' );
			$invisible      = get_option( 'user_registration_integration_setting_invisible_recaptcha_v2', 'no' );
			$settings       = apply_filters(
				'user_registration_integration_settings',
				array(
					'title'    => '',
					'sections' => array(
						'integration_options' => array(
							'title'    => __( 'Captcha', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Captcha Type', 'user-registration' ),
									'desc'     => __( 'Select the Captcha type', 'user-registration' ),
									'id'       => 'user_registration_integration_setting_recaptcha_version',
									'default'  => 'v2',
									'type'     => 'radio',
									'class'    => '',
									'desc_tip' => true,
									'options'  => array(
										'v2' => 'reCaptcha v2',
										'v3' => 'reCaptcha v3',
										'hCaptcha' => 'hCaptcha',
									),
								),
								array(
									'title'    => __( 'Site Key (reCaptcha v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'     => sprintf( __( 'Get site key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'       => 'user_registration_integration_setting_recaptcha_site_key',
									'default'  => '',
									'type'     => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'no' === $invisible,
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,

								),
								array(
									'title'    => __( 'Secret Key ( reCaptcha v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'     => sprintf( __( 'Get secret key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'       => 'user_registration_integration_setting_recaptcha_site_secret',
									'default'  => '',
									'type'     => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'no' === $invisible,
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Site Key (reCaptcha v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'     => sprintf( __( 'Get site key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'       => 'user_registration_integration_setting_recaptcha_invisible_site_key',
									'default'  => '',
									'type'     => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'yes' === $invisible,
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,

								),
								array(
									'title'    => __( 'Secret Key (reCaptcha v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'     => sprintf( __( 'Get secret key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'       => 'user_registration_integration_setting_recaptcha_invisible_site_secret',
									'default'  => '',
									'type'     => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'yes' === $invisible,
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Invisible reCAPTCHA', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'     => sprintf( __( 'check this to enable invisible recaptcha.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'       => 'user_registration_integration_setting_invisible_recaptcha_v2',
									'default'  => 'no',
									'type'     => 'checkbox',
									'is_visible' => 'v2' === $recaptcha_type,
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Site Key (reCaptcha v3)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'     => sprintf( __( 'Get site key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'       => 'user_registration_integration_setting_recaptcha_site_key_v3',
									'default'  => '',
									'type'     => 'text',
									'is_visible' => 'v3' === $recaptcha_type,
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,

								),
								array(
									'title'    => __( 'Secret Key (reCaptcha v3)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'     => sprintf( __( 'Get secret key from google %1$s reCaptcha %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'       => 'user_registration_integration_setting_recaptcha_site_secret_v3',
									'default'  => '',
									'type'     => 'text',
									'is_visible' => 'v3' === $recaptcha_type,
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Site Key ( hCaptcha )', 'user-registration' ),
									'desc'     => sprintf( __( 'Get site key from %1$s hCaptcha %2$s.', 'user-registration' ), '<a href="https://www.hcaptcha.com/" target="_blank">', '</a>' ), //phpcs:ignore
									'id'       => 'user_registration_integration_setting_recaptcha_site_key_hcaptcha',
									'default'  => '',
									'type'     => 'text',
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,

								),
								array(
									'title'    => __( 'Secret Key ( hCaptcha )', 'user-registration' ),
									'desc'     => sprintf( __( 'Get secret key from %1$s hCaptcha %2$s.', 'user-registration' ), '<a href="https://www.hcaptcha.com/" target="_blank">', '</a>' ), 	//phpcs:ignore
									'id'       => 'user_registration_integration_setting_recaptcha_site_secret_hcaptcha',
									'default'  => '',
									'type'     => 'text',
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Threshold score', 'user-registration' ),
									'desc'     => esc_html__( 'reCAPTCHA v3 returns a score (1.0 is very likely a good interaction, 0.0 is very likely a bot). If the score less than or equal to this threshold.', 'user-registration' ),
									'id'       => 'user_registration_integration_setting_recaptcha_threshold_score_v3',
									'type'     => 'number',
									'is_visible'        => 'v3' === $recaptcha_type,
									'custom_attributes' => array(
										'step' => '0.1',
										'min'  => '0.0',
										'max'  => '1.0',
									),
									'default'  => '0.4',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),

							),
						),
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

return new UR_Settings_Integration();
