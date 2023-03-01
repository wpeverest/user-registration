<?php
/**
 * Class
 *
 * UR_Stats
 *
 * @package User_Registration_Pro
 * @since  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Stats' ) ) {

	/**
	 * UR_Stats class.
	 */
	class UR_Stats {

		/**
		 * Remote URl Constant.
		 */
		const REMOTE_URL = 'https://stats.wpeverest.com/wp-json/tgreporting/v1/process-free/';

		const LAST_RUN_STAMP = 'user_registration_send_usage_last_run';


		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			add_action( 'init', array( $this, 'init_usage' ), 4 );
			add_action( 'update_option_user_registration_allow_usage_tracking', array( $this, 'run_on_save' ), 10, 3 );
		}

		/**
		 * Get product license key.
		 */
		public function get_base_product_license() {
			return get_option( 'user-registration_license_key' );
		}

		/**
		 * Get Pro addon file.
		 */
		public function get_base_product() {
			if ( $this->is_premium() ) {
				return 'user-registration-pro/user-registration.php';
			} else {
				return 'user-registration/user-registration.php';
			}
		}

		/**
		 * Check if user is using premium version.
		 *
		 * @return boolean
		 */
		public function is_premium() {
			if ( is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Returns total users registered from User Registration forms.
		 *
		 * @return int
		 */
		public function get_user_count() {
			global $wpdb;

			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $wpdb->users
		LEFT JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id
		WHERE meta_key = %s
		AND meta_value != ''",
					'ur_form_id'
				)
			);
		}

		/**
		 * Returns total number of  registration forms created using this plugin.
		 *
		 * @return int
		 */
		public function get_form_count() {
			global $wpdb;

			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $wpdb->posts WHERE post_type=%s AND post_status=%s",
					'user_registration',
					'publish'
				)
			);
		}

		/**
		 * Returns list of all active plugins.
		 *
		 * @return array
		 */
		public function get_plugin_lists() {

			$is_premium = $this->is_premium();

			$base_product = $this->get_base_product();

			$active_plugins = get_option( 'active_plugins', array() );

			$base_product_name = $is_premium ? 'User Registration Pro' : 'User Registration';

			$product_meta = array();

			$product_meta['form_count'] = $this->get_form_count();

			$product_meta['user_count'] = $this->get_user_count();

			$license_key = $this->get_base_product_license();

			if ( $is_premium ) {
				$product_meta['license_key'] = $license_key;
			}

			$addons_data = array(
				$base_product => array(
					'product_name'    => $base_product_name,
					'product_version' => UR()->version,
					'product_meta'    => $product_meta,
					'product_type'    => 'plugin',
					'product_slug'    => $base_product,
					'is_premium'      => $is_premium,
				),
			);

			foreach ( $active_plugins as $plugin ) {

				$addon_file      = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin;
				$addon_file_data = get_plugin_data( $addon_file );
				if ( $base_product !== $plugin ) {
					$addons_data[ $plugin ] = array(
						'product_name'    => isset( $addon_file_data['Name'] ) ? trim( $addon_file_data['Name'] ) : '',
						'product_version' => isset( $addon_file_data['Version'] ) ? trim( $addon_file_data['Version'] ) : '',
						'product_type'    => 'plugin',
						'product_slug'    => $plugin,
					);
				}
			}

			return $addons_data;
		}


		/**
		 * Get non-sensitive settings.
		 *
		 * @return array
		 */
		public function get_global_settings() {
			$global_settings = array();
			$settings        = $this->setting_keys();
			$send_all        = false;
			$send_default    = false;

			foreach ( $settings as $product => $product_settings ) {
				foreach ( $product_settings as $setting_array ) {
					$setting_key     = $setting_array[0];
					$setting_default = $setting_array[1];
					$value           = get_option( $setting_key, 'NOT_SET' );

					// Set boolean values for certain settings.
					if ( isset( $setting_array[2] ) && 'NOT_SET' !== $value && $setting_default !== $value ) {
						$value = 1;
					}

					if ( 'NOT_SET' !== $value || $send_all ) {
						$setting_content = array(
							'value' => $value //phpcs:ignore
						);

						if ( $send_default ) {
							$setting_content['default'] = $setting_default;
						}

						$global_settings[ $product ][ $setting_key ] = $setting_content;
					}
				}
			}

			return $global_settings;
		}

		/**
		 * Checks if usage is allowed.
		 *
		 * @return boolean
		 */
		public function is_usage_allowed() {
			return 'yes' === get_option( 'user_registration_allow_usage_tracking', 'no' );
		}

		/**
		 * Start process.
		 *
		 * @return void
		 */
		public function init_usage() {
			if ( wp_doing_cron() ) {
				add_action( 'user_registration_usage_stats_scheduled_events', array( $this, 'process' ) );
			}
		}

		/**
		 * Run the process once when user gives consent.
		 *
		 * @param int   $old_value Old Value.
		 * @param int   $value Value.
		 * @param mixed $option Options.
		 *
		 * @return mixed
		 */
		public function run_on_save( $old_value, $value, $option ) {
			if ( $value !== $old_value && 'yes' === $value && ( false === get_option( self::LAST_RUN_STAMP ) ) ) {
				$this->process();
			}
			return $value;
		}

		/**
		 * Start process.
		 *
		 * @return void
		 */
		public function process() {

			if ( ! $this->is_usage_allowed() ) {
				return;
			}

			$last_send = get_option( self::LAST_RUN_STAMP );

			// Make sure we do not run it more than once on each 15 days.
			if (
				false !== $last_send &&
				( time() - $last_send ) < ( DAY_IN_SECONDS * 14 )
			) {
				return;
			}

			$this->call_api();
			// Update the last run option to the current timestamp.
			update_option( self::LAST_RUN_STAMP, time() );
		}

		/**
		 * Call API.
		 *
		 * @return void
		 */
		public function call_api() {
			global $wpdb;
			$theme                        = wp_get_theme();
			$data                         = array();
			$data['product_data']         = $this->get_plugin_lists();
			$data['admin_email']          = get_bloginfo( 'admin_email' );
			$data['website_url']          = get_bloginfo( 'url' );
			$data['wp_version']           = get_bloginfo( 'version' );
			$data['php_version']          = phpversion();
			$data['mysql_version']        = $wpdb->db_version();
			$data['server_software']      = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
			$data['is_ssl']               = is_ssl();
			$data['is_multisite']         = is_multisite();
			$data['is_wp_com']            = defined( 'IS_WPCOM' ) && IS_WPCOM;
			$data['is_wp_com_vip']        = ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) || ( function_exists( 'wpcom_is_vip' ) && wpcom_is_vip() );
			$data['is_wp_cache']          = defined( 'WP_CACHE' ) && WP_CACHE;
			$data['multi_site_count']     = $this->get_sites_total();
			$data['active_theme']         = $theme->name;
			$data['active_theme_version'] = $theme->version;
			$data['locale']               = get_locale();
			$data['timezone']             = $this->get_timezone_offset();
			$data['base_product']         = $this->get_base_product();
			$data['global_settings']      = $this->get_global_settings();

			$this->send_request( self::REMOTE_URL, $data );
		}

		/**
		 * Returns total sites.
		 *
		 * @return int
		 */
		private function get_sites_total() {

			return function_exists( 'get_blog_count' ) ? (int) get_blog_count() : 1;
		}

		/**
		 * Get Timezone Offset.
		 */
		private function get_timezone_offset() {

			// It was added in WordPress 5.3.
			if ( function_exists( 'wp_timezone_string' ) ) {
				return wp_timezone_string();
			}

			/*
			 * The code below is basically a copy-paste from that function.
			 */

			$timezone_string = get_option( 'timezone_string' );

			if ( $timezone_string ) {
				return $timezone_string;
			}

			$offset  = (float) get_option( 'gmt_offset' );
			$hours   = (int) $offset;
			$minutes = ( $offset - $hours );

			$sign      = ( $offset < 0 ) ? '-' : '+';
			$abs_hour  = abs( $hours );
			$abs_mins  = abs( $minutes * 60 );
			$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

			return $tz_offset;
		}

		/**
		 * Send Request to API.
		 *
		 * @param string $url URL.
		 * @param array  $data Data.
		 */
		public function send_request( $url, $data ) {
			$headers = array(
				'user-agent' => 'UserRegistration/' . UR()->version . '; ' . get_bloginfo( 'url' ),
			);

			$response = wp_remote_post(
				$url,
				array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => $headers,
					'body'        => array( 'free_data' => $data ),
				)
			);
			return json_decode( wp_remote_retrieve_body( $response ), true );
		}

		/**
		 * Returns non-sensitive setting keys.
		 *
		 * @return array
		 */
		private function setting_keys() {
			return array(
				'user-registration/user-registration.php' => array(
					array( 'user_registration_general_setting_disabled_user_roles', '["subscriber"]' ),
					array( 'user_registration_login_option_hide_show_password', 'no' ),
					array( 'user_registration_myaccount_page_id', '', true ),
					array( 'user_registration_my_account_layout', 'horizontal' ),
					array( 'user_registration_ajax_form_submission_on_edit_profile', 'no' ),
					array( 'user_registration_disable_profile_picture', 'no' ),
					array( 'user_registration_disable_logout_confirmation', 'no' ),
					array( 'user_registration_login_options_form_template', 'default' ),
					array( 'user_registration_general_setting_login_options_with', 'default' ),
					array( 'user_registration_login_title', 'no' ),
					array( 'ur_login_ajax_submission', 'no' ),
					array( 'user_registration_login_options_remember_me', 'yes' ),
					array( 'user_registration_login_options_lost_password', 'yes' ),
					array( 'user_registration_login_options_hide_labels', 'no' ),
					array( 'user_registration_login_options_enable_recaptcha', 'no' ),
					array( 'user_registration_general_setting_registration_url_options', '', true ),
					array( 'user_registration_login_options_prevent_core_login', 'no' ),
					array( 'user_registration_login_options_login_redirect_url', '', true ),
					array( 'user_registration_integration_setting_recaptcha_version', 'v2' ),
					array( 'user_registration_general_setting_uninstall_option', 'no' ),
					array( 'user_registration_allow_usage_tracking', 'no' ) //phpcs:ignore
				),
				'user-registration-pro/user-registration.php' => array(
					array( 'user_registration_pro_general_setting_delete_account', 'disable' ),
					array( 'user_registration_pro_general_setting_login_form', 'no' ),
					array( 'user_registration_pro_general_setting_prevent_active_login', 'no' ),
					array( 'user_registration_pro_general_setting_limited_login', '5' ),
					array( 'user_registration_pro_general_setting_redirect_back_to_previous_page', 'no' ),
					array( 'user_registration_pro_general_post_submission_settings', '' ),
					array( 'user_registration_pro_general_setting_post_submission', 'disable' ),
					array( 'user_registration_pro_role_based_redirection', 'no' ) //phpcs:ignore
				),
				'user-registration-content-restriction/user-registration-content-restriction.php' => array(
					array( 'user_registration_content_restriction_enable', 'yes' ),
					array( 'user_registration_content_restriction_allow_to_roles', '["administrator"]' ) //phpcs:ignore
				),
				'user-registration-file-upload/user-registration-file-upload.php' => array(
					array( 'user_registration_file_upload_setting_valid_file_type', '["pdf"]' ),
					array( 'user_registration_file_upload_setting_max_file_size', '1024' ) //phpcs:ignore
				),
				'user-registration-pdf-submission/user-registration-pdf-submission.php' => array(
					array( 'user_registration_pdf_template', 'default' ),
					array( 'user_registration_pdf_logo_image', '', true ),
					array( 'user_registration_pdf_setting_header', '' ),
					array( 'user_registration_pdf_custom_header_text', '', true ),
					array( 'user_registration_pdf_paper_size', '' ),
					array( 'user_registration_pdf_orientation', 'portrait' ),
					array( 'user_registration_pdf_font', '' ),
					array( 'user_registration_pdf_font_size', '12' ),
					array( 'user_registration_pdf_font_color', '#000000' ),
					array( 'user_registration_pdf_background_color', '#ffffff' ),
					array( 'user_registration_pdf_header_font_color', '#000000' ),
					array( 'user_registration_pdf_header_background_color', '#ffffff' ),
					array( 'user_registration_pdf_multiple_column', 'no' ),
					array( 'user_registration_pdf_rtl', 'no' ),
					array( 'user_registration_pdf_print_user_default_fields', 'no' ),
					array( 'user_registration_pdf_hide_empty_fields', 'no' ) //phpcs:ignore
				),
				'user-registration-social-connect/user-registration-social-connect.php' => array(
					array( 'user_registration_social_setting_enable_facebook_connect', '' ),
					array( 'user_registration_social_setting_enable_twitter_connect', '' ),
					array( 'user_registration_social_setting_enable_google_connect', '' ),
					array( 'user_registration_social_setting_enable_linkedin_connect', '' ),
					array( 'user_registration_social_setting_enable_social_registration', 'no' ),
					array( 'user_registration_social_setting_display_social_buttons_in_registration', 'no' ),
					array( 'user_registration_social_setting_default_user_role', 'subscriber' ),
					array( 'user_registration_social_login_position', 'bottom' ),
					array( 'user_registration_social_login_template', 'ursc_theme_4' ) //phpcs:ignore
				),
				'user-registration-two-factor-authentication/user-registration-two-factor-authentication.php' => array(
					array( 'user_registration_tfa_enable_disable', 'no' ),
					array( 'user_registration_tfa_roles', '["subscriber"]' ),
					array( 'user_registration_tfa_otp_length', '6' ),
					array( 'user_registration_tfa_otp_expiry_time', '10' ),
					array( 'user_registration_tfa_otp_resend_limit', '3' ),
					array( 'user_registration_tfa_incorrect_otp_limit', '5' ),
					array( 'user_registration_tfa_login_hold_period', '60' ) //phpcs:ignore
				),
				'user-registration-payments/user-registration-payments.php' => array(
					array( 'user_registration_payment_currency', 'USD' ) //phpcs:ignore
				) //phpcs:ignore
			);
		}
	}
}

new UR_Stats();
