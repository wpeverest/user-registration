<?php
/**
 * UserRegistration General Settings
 *
 * @class    UR_Settings_Captcha
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Captcha ' ) ) :

	/**
	 * UR_Settings_Captcha Class
	 */
	class UR_Settings_Captcha extends UR_Settings_Page {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'captcha';

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'captcha';
			$this->label = __( 'Captcha', 'user-registration' );

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
			$recaptcha_type = get_option( 'user_registration_captcha_setting_recaptcha_version', 'v2' );
			$invisible      = get_option( 'user_registration_captcha_setting_invisible_recaptcha_v2', 'no' );
			$settings       = apply_filters(
				'user_registration_captcha_settings',
				array(
					'title'    => '',
					'sections' => array(
						'captcha_options' => array(
							'title'    => __( 'Captcha', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Captcha Type', 'user-registration' ),
									'desc'     => __( 'Select the Captcha type', 'user-registration' ),
									'id'       => 'user_registration_captcha_setting_recaptcha_version',
									'default'  => 'v2',
									'type'     => 'radio',
									'class'    => '',
									'desc_tip' => true,
									'options'  => array(
										'v2'         => 'reCAPTCHA v2',
										'v3'         => 'reCAPTCHA v3',
										'hCaptcha'   => 'hCaptcha',
										'cloudflare' => 'Cloudflare Turnstile',
									),
								),
								array(
									'title'      => __( 'Site Key (reCAPTCHA v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'       => sprintf( __( 'Get site key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'         => 'user_registration_captcha_setting_recaptcha_site_key',
									'default'    => '',
									'type'       => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'no' === $invisible,
									'class'      => '',
									'css'        => 'min-width: 350px;',
									'desc_tip'   => true,

								),
								array(
									'title'      => __( 'Secret Key ( reCAPTCHA v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'       => sprintf( __( 'Get secret key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'         => 'user_registration_captcha_setting_recaptcha_site_secret',
									'default'    => '',
									'type'       => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'no' === $invisible,
									'class'      => '',
									'css'        => 'min-width: 350px;',
									'desc_tip'   => true,
								),
								array(
									'title'      => __( 'Site Key (reCAPTCHA v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'       => sprintf( __( 'Get site key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'         => 'user_registration_captcha_setting_recaptcha_invisible_site_key',
									'default'    => '',
									'type'       => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'yes' === $invisible,
									'class'      => '',
									'css'        => 'min-width: 350px;',
									'desc_tip'   => true,

								),
								array(
									'title'      => __( 'Secret Key (reCAPTCHA v2)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'       => sprintf( __( 'Get secret key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'         => 'user_registration_captcha_setting_recaptcha_invisible_site_secret',
									'default'    => '',
									'type'       => 'text',
									'is_visible' => 'v2' === $recaptcha_type && 'yes' === $invisible,
									'class'      => '',
									'css'        => 'min-width: 350px;',
									'desc_tip'   => true,
								),
								array(
									'title'      => __( 'Invisible reCAPTCHA', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'       => sprintf( __( 'check this to enable invisible reCAPTCHA.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'         => 'user_registration_captcha_setting_invisible_recaptcha_v2',
									'default'    => 'no',
									'type'       => 'toggle',
									'is_visible' => 'v2' === $recaptcha_type,
									'css'        => 'min-width: 350px;',
									'desc_tip'   => true,
								),
								array(
									'title'      => __( 'Site Key (reCAPTCHA v3)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'       => sprintf( __( 'Get site key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'         => 'user_registration_captcha_setting_recaptcha_site_key_v3',
									'default'    => '',
									'type'       => 'text',
									'is_visible' => 'v3' === $recaptcha_type,
									'class'      => '',
									'css'        => 'min-width: 350px;',
									'desc_tip'   => true,

								),
								array(
									'title'      => __( 'Secret Key (reCAPTCHA v3)', 'user-registration' ),
									/* translators: %1$s - Google reCAPTCHA docs url */
									'desc'       => sprintf( __( 'Get secret key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" target="_blank">', '</a>' ),
									'id'         => 'user_registration_captcha_setting_recaptcha_site_secret_v3',
									'default'    => '',
									'type'       => 'text',
									'is_visible' => 'v3' === $recaptcha_type,
									'class'      => '',
									'css'        => 'min-width: 350px;',
									'desc_tip'   => true,
								),
								array(
									'title'    => __( 'Site Key (hCaptcha)', 'user-registration' ),
									'desc'     => sprintf( __( 'Get site key from %1$s hCaptcha %2$s.', 'user-registration' ), '<a href="https://www.hcaptcha.com/" target="_blank">', '</a>' ), //phpcs:ignore
									'id'       => 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha',
									'default'  => '',
									'type'     => 'text',
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,

								),
								array(
									'title'    => __( 'Secret Key (hCaptcha)', 'user-registration' ),
									'desc'     => sprintf( __( 'Get secret key from %1$s hCaptcha %2$s.', 'user-registration' ), '<a href="https://www.hcaptcha.com/" target="_blank">', '</a>' ), 	//phpcs:ignore
									'id'       => 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha',
									'default'  => '',
									'type'     => 'text',
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'             => __( 'Threshold score', 'user-registration' ),
									'desc'              => esc_html__( 'reCAPTCHA v3 returns a score (1.0 is very likely a good interaction, 0.0 is very likely a bot). If the score less than or equal to this threshold.', 'user-registration' ),
									'id'                => 'user_registration_captcha_setting_recaptcha_threshold_score_v3',
									'type'              => 'number',
									'is_visible'        => 'v3' === $recaptcha_type,
									'custom_attributes' => array(
										'step' => '0.1',
										'min'  => '0.0',
										'max'  => '1.0',
									),
									'default'           => '0.4',
									'css'               => 'min-width: 350px;',
									'desc_tip'          => true,
								),
								array(
									'title'    => __( 'Site Key (Cloudflare Turnstile)', 'user-registration' ),
									'desc'     => sprintf( __( 'Get site key from %1$s Cloudflare Turnstile %2$s.', 'user-registration' ), '<a href="https://www.cloudflare.com/products/turnstile/" target="_blank">', '</a>' ), //phpcs:ignore
									'id'       => 'user_registration_captcha_setting_recaptcha_site_key_cloudflare',
									'default'  => '',
									'type'     => 'text',
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,

								),
								array(
									'title'    => __( 'Secret Key (Cloudflare Turnstile)', 'user-registration' ),
									'desc'     => sprintf( __( 'Get secret key from %1$s Cloudflare Turnstile %2$s.', 'user-registration' ), '<a href="https://www.cloudflare.com/products/turnstile/" target="_blank">', '</a>' ), 	//phpcs:ignore
									'id'       => 'user_registration_captcha_setting_recaptcha_site_secret_cloudflare',
									'default'  => '',
									'type'     => 'text',
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Theme', 'user-registration' ),
									'desc'     => sprintf( esc_html__( 'Please select theme mode for your Cloudflare Turnstile. <a href="%1$s" target="_blank">Learn More</a>', 'user-registration' ), esc_url( 'https://www.cloudflare.com/products/turnstile/' ) ),
									'id'       => 'user_registration_captcha_setting_recaptcha_cloudflare_theme',
									'options'  => array(
										'auto'  => esc_html__( 'Auto', 'user-registration' ),
										'light' => esc_html__( 'Light', 'user-registration' ),
										'dark'  => esc_html__( 'Dark', 'user-registration' ),
									),
									'type'     => 'select',
									'class'    => '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);

			return apply_filters( 'user_registration_get_captcha_settings_' . $this->id, $settings );
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

return new UR_Settings_Captcha();
