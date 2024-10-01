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
			add_filter( 'user_registration_admin_field_recaptcha_test', array( $this, 'output_captcha_test' ), 10, 2 );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {
			$recaptcha_type = get_option( 'user_registration_captcha_setting_recaptcha_version', 'v2' );
			$invisible      = get_option( 'user_registration_captcha_setting_invisible_recaptcha_v2', 'no' );

			/**
			 * Filter to add the options on settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_captcha_settings',
				array(
					'title'    => '',
					'sections' => $this->get_captcha_global_settings(),
				)
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Captcha Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_captcha_settings_' . $this->id, $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			$settings = $this->get_settings();
			UR_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Add html for Test Captcha button and captcha node.
		 *
		 * @param [string] $settings Captcha settings html.
		 * @param [string] $value Value.
		 * @return string
		 */
		public function output_captcha_test( $settings, $value ) {

			$active_captcha = self::get_active_captcha( $value );
			if ( ! $active_captcha ) {
				return $settings;
			}

			$captcha_type = $value['captcha_type'];
			$test_captcha = <<<HTML
				<div class="user-registration-global-settings">
					<div class="ur-captcha-test-button">
						<button class="button ur-button user_registration_captcha_setting_captcha_test" id="user_registration_captcha_setting_%s_captcha_test" data-captcha-type="%s">%s<span class="spinner" style="display:none"></span></button>
					</div>
					<div class="ur-captcha-test">
						<div class="ur-captcha-test-container" data-captcha-type="%s">
							<div class="ur-captcha-node">%s</div>
							<div class="ur-captcha-notice">
								<span class="ur-captcha-notice--icon"></span>
								<span class="ur-captcha-notice--text"></span>
							</div>
						</div>
					</div>
				</div>
			HTML;  //phpcs:ignore;

			$captcha_node = ur_get_recaptcha_node( 'test_captcha', $captcha_type );
			$test_captcha = sprintf(
				$test_captcha,
				$captcha_type,
				$captcha_type,
				__( 'Test Captcha', 'user-registration' ),
				$captcha_type,
				$captcha_node
			);

			$settings .= $test_captcha;

			return $settings;
		}

		/**
		 * Returns the active captcha settings.
		 * Returns false if captcha is not set or settings empty.
		 *
		 * @return array or boolean
		 */
		public static function get_active_captcha( $value ) {
			$captcha_type = $value['captcha_type'];

			switch ( $captcha_type ) {
				case 'v2':
					$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key' );
					$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret' );
					$invisible  = get_option( 'user_registration_captcha_setting_invisible_recaptcha_v2' );

					if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
						return array(
							'type'       => 'v2',
							'site_key'   => $site_key,
							'secret_key' => $secret_key,
							'invisible'  => $invisible,
						);
					}

					if ( $invisible ) {
						$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_key' );
						$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_secret' );

						if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
							return array(
								'type'       => 'invisible_v2',
								'site_key'   => $site_key,
								'secret_key' => $secret_key,
								'invisible'  => $invisible,
							);
						}
					}

					break;

				case 'v3':
					$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_v3' );
					$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_v3' );
					$threshold  = get_option( 'user_registration_captcha_setting_recaptcha_threshold_score_v3', '0.4' );

					if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
						return array(
							'type'       => 'v2',
							'site_key'   => $site_key,
							'secret_key' => $secret_key,
							'threshold'  => $threshold,
						);
					}
					break;

				case 'hCaptcha':
					$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha' );
					$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha' );

					if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
						return array(
							'type'       => 'hCaptcha',
							'site_key'   => $site_key,
							'secret_key' => $secret_key,
						);
					}
					break;

				case 'cloudflare':
					$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_cloudflare' );
					$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_cloudflare' );

					if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
						return array(
							'type'       => 'cloudflare',
							'site_key'   => $site_key,
							'secret_key' => $secret_key,
						);
					}
					break;

					/**
					 * Filter to change the status of recaptcha.
					 *
					 * @param bool false Status for the recaptcha.
					 */
				return apply_filters( 'user_registration_active_recaptcha', false );
			}
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section, $hide_save_button;
			$settings         = $this->get_settings();
			// $hide_save_button = true;

			UR_Admin_Settings::output_fields( $settings );
		}

		public function get_captcha_global_settings() {
			$captcha_global_settings = array();

			$captcha_global_settings = array(
				'v2' => array(
					'title'     => 'reCAPTCHA v2',
					'type'      => 'accordian',
					'id'        => 'v2',
					'settings' => array(
						array(
							'title'    => __( 'Enable reCAPTCHA v2', 'user-registration' ),
							/* translators: %1$s - hCaptcha docs url */
							'desc'     => __( 'Enable v2 for the site.', 'user-registration' ), //phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_enable_v2',
							'default'  => '',
							'type'     => 'toggle',
							'class'    => 'ur-captcha-enable',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
						array(
							'title'      => __( 'Site Key (reCAPTCHA v2)', 'user-registration' ),
							/* translators: %1$s - Google reCAPTCHA docs url */
							'desc'       => sprintf( __( 'Get site key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" rel="noreferrer noopener" target="_blank">', '</a>' ),
							'id'         => 'user_registration_captcha_setting_recaptcha_site_key',
							'default'    => '',
							'type'       => 'text',
							// 'is_visible' => 'v2' === $recaptcha_type && 'no' === $invisible,
							'class'      => '',
							'css'        => 'min-width: 350px;',
							'desc_tip'   => true,
						),
						array(
							'title'      => __( 'Secret Key (reCAPTCHA v2)', 'user-registration' ),
							/* translators: %1$s - Google reCAPTCHA docs url */
							'desc'       => sprintf( __( 'Get secret key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" rel="noreferrer noopener" target="_blank">', '</a>' ),
							'id'         => 'user_registration_captcha_setting_recaptcha_site_secret',
							'default'    => '',
							'type'       => 'text',
							'class'      => '',
							'css'        => 'min-width: 350px;',
							'desc_tip'   => true,
						),
						array(
							'title'      => __( 'Site Key (Invisible reCAPTCHA v2)', 'user-registration' ),
							/* translators: %1$s - Google reCAPTCHA docs url */
							'desc'       => sprintf( __( 'Get site key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" rel="noreferrer noopener" target="_blank">', '</a>' ),
							'id'         => 'user_registration_captcha_setting_recaptcha_invisible_site_key',
							'default'    => '',
							'type'       => 'text',
							'class'      => '',
							'css'        => 'min-width: 350px;',
							'desc_tip'   => true,

						),
						array(
							'title'      => __( 'Secret Key (Invisible reCAPTCHA v2)', 'user-registration' ),
							/* translators: %1$s - Google reCAPTCHA docs url */
							'desc'       => sprintf( __( 'Get secret key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" rel="noreferrer noopener" target="_blank">', '</a>' ),
							'id'         => 'user_registration_captcha_setting_recaptcha_invisible_site_secret',
							'default'    => '',
							'type'       => 'text',
							'class'      => '',
							'css'        => 'min-width: 350px;',
							'desc_tip'   => true,
						),
						array(
							'title'      => __( 'Invisible reCAPTCHA', 'user-registration' ),
							/* translators: %1$s - Google reCAPTCHA docs url */
							'desc'       => sprintf( __( 'check this to enable invisible reCAPTCHA.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" rel="noreferrer noopener" target="_blank">', '</a>' ),
							'id'         => 'user_registration_captcha_setting_invisible_recaptcha_v2',
							'default'    => 'no',
							'type'       => 'toggle',
							'css'        => 'min-width: 350px;',
							'desc_tip'   => true,
						),
						array(
							'title' => __( 'Test Captcha', 'user-registration' ),
							'id'    => 'user_registration_captcha_setting_v2_recaptcha_test',
							'type'  => 'recaptcha_test',
							'captcha_type'  => 'v2',
						),
					),
					'settings_type' => 'captcha'
				),
				'v3' => array(
					'title'     => 'reCAPTCHA v3',
					'type'      => 'accordian',
					'settings_type' => 'captcha',
					'id'        => 'v3',
					'settings' => array(
						array(
							'title'    => __( 'Enable reCAPTCHA v3', 'user-registration' ),
							/* translators: %1$s - recaptchav3 docs url */
							'desc'     => __( 'Enable reCAPTCHA v3 for the site.', 'user-registration' ), //phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_enable_v3',
							'default'  => '',
							'type'     => 'toggle',
							'class'    => 'ur-captcha-enable',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
						array(
							'title'      => __( 'Site Key (reCAPTCHA v3)', 'user-registration' ),
							/* translators: %1$s - Google reCAPTCHA docs url */
							'desc'       => sprintf( __( 'Get site key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" rel="noreferrer noopener" target="_blank">', '</a>' ),
							'id'         => 'user_registration_captcha_setting_recaptcha_site_key_v3',
							'default'    => '',
							'type'       => 'text',
							'class'      => '',
							'css'        => 'min-width: 350px;',
							'desc_tip'   => true,

						),
						array(
							'title'      => __( 'Secret Key (reCAPTCHA v3)', 'user-registration' ),
							/* translators: %1$s - Google reCAPTCHA docs url */
							'desc'       => sprintf( __( 'Get secret key from google %1$s reCAPTCHA %2$s.', 'user-registration' ), '<a href="https://www.google.com/recaptcha" rel="noreferrer noopener" target="_blank">', '</a>' ),
							'id'         => 'user_registration_captcha_setting_recaptcha_site_secret_v3',
							'default'    => '',
							'type'       => 'text',
							'class'      => '',
							'css'        => 'min-width: 350px;',
							'desc_tip'   => true,
						),
						array(
							'title'             => __( 'Threshold score', 'user-registration' ),
							'desc'              => esc_html__( 'reCAPTCHA v3 returns a score (1.0 is very likely a good interaction, 0.0 is very likely a bot). If the score less than or equal to this threshold.', 'user-registration' ),
							'id'                => 'user_registration_captcha_setting_recaptcha_threshold_score_v3',
							'type'              => 'number',
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
							'title' => __( 'Test Captcha', 'user-registration' ),
							'id'    => 'user_registration_captcha_setting_v3_recaptcha_test',
							'captcha_type'  => 'v3',
							'type'  => 'recaptcha_test',
						),
					),
				),
				'hCaptcha' => array(
					'title'     => 'hCaptcha',
					'type'      => 'accordian',
					'settings_type' => 'captcha',
					'id'        => 'hCaptcha',
					'settings'  => array(
						array(
							'title'    => __( 'Enable hCaptcha', 'user-registration' ),
							/* translators: %1$s - hCaptcha docs url */
							'desc'     => __( 'Enable hCaptcha for the site.', 'user-registration' ), //phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_enable_hcaptcha',
							'default'  => '',
							'type'     => 'toggle',
							'class'    => 'ur-captcha-enable',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
						array(
							'title'    => __( 'Site Key (hCaptcha)', 'user-registration' ),
							/* translators: %1$s - hCaptcha docs url */
							'desc'     => sprintf( __( 'Get site key from %1$s hCaptcha %2$s.', 'user-registration' ), '<a href="https://www.hcaptcha.com/" rel="noreferrer noopener" target="_blank">', '</a>' ), //phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha',
							'default'  => '',
							'type'     => 'text',
							'class'    => '',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
						array(
							'title'    => __( 'Secret Key (hCaptcha)', 'user-registration' ),
							/* translators: %1$s - hCaptcha docs url */
							'desc'     => sprintf( __( 'Get secret key from %1$s hCaptcha %2$s.', 'user-registration' ), '<a href="https://www.hcaptcha.com/" rel="noreferrer noopener" target="_blank">', '</a>' ), 	//phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha',
							'default'  => '',
							'type'     => 'text',
							'class'    => '',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
						array(
							'title' => __( 'Test Captcha', 'user-registration' ),
							'id'    => 'user_registration_captcha_setting_hcaptcha_recaptcha_test',
							'captcha_type'  => 'hCaptcha',
							'type'  => 'recaptcha_test',
						),
					),
				),
				'cloudflare' => array(
					'title'     => 'Cloudflare Turnstile',
					'type'      => 'accordian',
					'settings_type' => 'captcha',
					'id'        => 'cloudflare',
					'settings'  => array(
						array(
							'title'    => __( 'Enable Cloudflare Turnstile', 'user-registration' ),
							/* translators: %1$s - hCaptcha docs url */
							'desc'     => __( 'Enable Cloudflare Turnstile for the site.', 'user-registration' ), //phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_enable_cloudflare',
							'default'  => '',
							'type'     => 'toggle',
							'class'    => 'ur-captcha-enable',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
						array(
							'title'    => __( 'Site Key (Cloudflare Turnstile)', 'user-registration' ),
							/* translators: %1$s - Cloudflare Turnstile docs url */
							'desc'     => sprintf( __( 'Get site key from %1$s Cloudflare Turnstile %2$s.', 'user-registration' ), '<a href="https://www.cloudflare.com/products/turnstile/" rel="noreferrer noopener" target="_blank">', '</a>' ), //phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_site_key_cloudflare',
							'default'  => '',
							'type'     => 'text',
							'class'    => '',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,

						),
						array(
							'title'    => __( 'Secret Key (Cloudflare Turnstile)', 'user-registration' ),
							/* translators: %1$s - Cloudflare Turnstile docs url */
							'desc'     => sprintf( __( 'Get secret key from %1$s Cloudflare Turnstile %2$s.', 'user-registration' ), '<a href="https://www.cloudflare.com/products/turnstile/" rel="noreferrer noopener" target="_blank">', '</a>' ), 	//phpcs:ignore
							'id'       => 'user_registration_captcha_setting_recaptcha_site_secret_cloudflare',
							'default'  => '',
							'type'     => 'text',
							'class'    => '',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
						array(
							'title'    => __( 'Theme', 'user-registration' ),
							/* translators: %1$s - Cloudflare Turnstile docs url */
							'desc'     => sprintf( esc_html__( 'Please select theme mode for your Cloudflare Turnstile. <a href="%1$s" rel="noreferrer noopener" target="_blank">Learn More</a>', 'user-registration' ), esc_url( 'https://www.cloudflare.com/products/turnstile/' ) ),
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
						array(
							'title' => __( 'Test Captcha', 'user-registration' ),
							'id'    => 'user_registration_captcha_setting_cloudflare_recaptcha_test',
							'captcha_type'  => 'cloudflare',
							'type'  => 'recaptcha_test',
						),
					),
				)
			);

			return $captcha_global_settings;
		}
	}

endif;

return new UR_Settings_Captcha();
