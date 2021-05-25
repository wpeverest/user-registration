<?php
/**
 * UserRegistration fronted scripts
 *
 * @class    UR_Frontend_Scripts
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Frontend_Scripts Class
 */
class UR_Frontend_Scripts {

	/**
	 * Contains an array of script handles registered by UR.
	 *
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of script handles registered by UR.
	 *
	 * @var array
	 */
	private static $styles = array();

	/**
	 * Contains an array of script handles localized by UR.
	 *
	 * @var array
	 */
	private static $wp_localize_scripts = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 5 );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Get styles for the frontend.
	 *
	 * @return array
	 */
	public static function get_styles() {

		return apply_filters(
			'user_registration_enqueue_styles',
			array(
				'sweetalert2'                         => array(
					'src'     => UR()->plugin_url() . '/assets/css/sweetalert2/sweetalert2.min.css',
					'deps'    => '',
					'version' => '10.16.7',
					'media'   => 'all',
				),
				'user-registration-general'           => array(
					'src'     => self::get_asset_url( 'assets/css/user-registration.css' ),
					'deps'    => '',
					'version' => UR_VERSION,
					'media'   => 'all',
					'has_rtl' => true,
				),
				'user-registration-smallscreen'       => array(
					'src'     => self::get_asset_url( 'assets/css/user-registration-smallscreen.css' ),
					'deps'    => '',
					'version' => UR_VERSION,
					'media'   => 'only screen and (max-width: ' . apply_filters( 'user_registration_style_smallscreen_breakpoint', $breakpoint = '768px' ) . ')',
					'has_rtl' => true,
				),
				'user-registration-my-account-layout' => array(
					'src'     => self::get_asset_url( 'assets/css/my-account-layout.css' ),
					'deps'    => '',
					'version' => UR_VERSION,
					'media'   => 'all',
				),
			)
		);
	}

	/**
	 * Return asset URL.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	private static function get_asset_url( $path ) {
		return apply_filters( 'user_registration_get_asset_url', plugins_url( $path, UR_PLUGIN_FILE ), $path );
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = UR_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = UR_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register a style for use.
	 *
	 * @uses   wp_register_style()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 * @param  boolean  $has_rtl
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = UR_VERSION, $media = 'all', $has_rtl = false ) {
		self::$styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 * @param  boolean  $has_rtl
	 */
	private static function enqueue_style( $handle, $path = '', $deps = array(), $version = UR_VERSION, $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, self::$styles ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register all UR scripts.
	 */
	private static function register_scripts() {
		$suffix                = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$recaptcha_site_key_v3 = get_option( 'user_registration_integration_setting_recaptcha_site_key_v3' );
		$register_scripts      = array(
			'ur-inputmask'               => array(
				'src'     => self::get_asset_url( 'assets/js/inputmask/jquery.inputmask.bundle' . $suffix . '.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '4.0.0-beta.58',
			),
			'flatpickr'                  => array(
				'src'     => self::get_asset_url( 'assets/js/flatpickr/flatpickr.min.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '4.6.9',
			),
			'ur-jquery-validate'         => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/jquery.validate' . $suffix . '.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '1.15.1',
			),
			'user-registration'          => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/user-registration' . $suffix . '.js' ),
				'deps'    => array( 'jquery', 'ur-jquery-validate', 'ur-inputmask' ),
				'version' => UR_VERSION,
			),
			'ur-form-validator'          => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/user-registration-form-validator' . $suffix . '.js' ),
				'deps'    => array( 'jquery', 'user-registration' ),
				'version' => UR_VERSION,
			),
			'ur-lost-password'           => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/lost-password' . $suffix . '.js' ),
				'deps'    => array( 'jquery', 'user-registration' ),
				'version' => UR_VERSION,
			),
			'ur-password-strength-meter' => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/password-strength-meter' . $suffix . '.js' ),
				'deps'    => array( 'jquery', 'password-strength-meter' ),
				'version' => UR_VERSION,
			),
			'ur-google-recaptcha'        => array(
				'src'     => 'https://www.google.com/recaptcha/api.js?onload=onloadURCallback&render=explicit',
				'deps'    => array(),
				'version' => '2.0.0',
			),
			'ur-google-recaptcha-v3'     => array(
				'src'     => 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_site_key_v3,
				'deps'    => array(),
				'version' => '3.0.0',
			),
			'ur-my-account'              => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/my-account' . $suffix . '.js' ),
				'deps'    => array( 'jquery', 'user-registration' ),
				'version' => UR_VERSION,
			),
			'ur-login'              => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/ur-login' . $suffix . '.js' ),
				'deps'    => array( 'jquery'),
				'version' => UR_VERSION,
			),
			'jquery-tiptip'              => array(
				'src'     => self::get_asset_url( 'assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '1.3.0',
			),
			'selectWoo'                  => array(
				'src'     => self::get_asset_url( 'assets/js/selectWoo/selectWoo.full' . $suffix . '.js' ),
				'deps'    => array( 'jquery' ),
				'version' => '1.0.9',
			),
			'sweetalert2'                => array(
				'src'     => UR()->plugin_url() . '/assets/js/sweetalert2/sweetalert2.min.js',
				'deps'    => array( 'jquery' ),
				'version' => '10.16.7',
			),
		);
		foreach ( $register_scripts as $name => $props ) {
			self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
		}
	}

	/**
	 * Register all UR styles.
	 */
	private static function register_styles() {
		$register_styles = array(
			'jquery-ui-css' => array(
				'src'     => self::get_asset_url( 'assets/css/jquery-ui/jquery-ui.css' ),
				'deps'    => '',
				'version' => '1.12.1',
				'media'   => 'all',
				'has_rtl' => false,
			),
			'flatpickr'     => array(
				'src'     => self::get_asset_url( 'assets/css/flatpickr/flatpickr.min.css' ),
				'deps'    => array(),
				'version' => '4.6.9',
				'media'   => 'all',
				'has_rtl' => false,
			),
			'select2'       => array(
				'src'     => self::get_asset_url( 'assets/css/select2/select2.css' ),
				'deps'    => array(),
				'version' => '4.1.0',
				'has_rtl' => false,
			),
		);

		foreach ( $register_styles as $name => $props ) {
			self::register_style( $name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl'] );
		}
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		global $post;

		if ( ! did_action( 'before_user_registration_init' ) ) {
			return;
		}

		self::register_scripts();
		self::register_styles();

		if ( is_ur_lost_password_page() ) {
			self::enqueue_script( 'ur-lost-password' );
		}

		// CSS Styles
		if ( $enqueue_styles = self::get_styles() ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				if ( ! isset( $args['has_rtl'] ) ) {
					$args['has_rtl'] = false;
				}

				self::enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl'] );
			}
		}

		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Localize a UR script once.
	 *
	 * @access private
	 *
	 * @param  string $handle
	 */
	private static function localize_script( $handle ) {
		if ( ! in_array( $handle, self::$wp_localize_scripts ) && wp_script_is( $handle ) && ( $data = self::get_script_data( $handle ) ) ) {
			$name                        = str_replace( '-', '_', $handle ) . '_params';
			self::$wp_localize_scripts[] = $handle;
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Return data for script handles.
	 *
	 * @access private
	 *
	 * @param  string $handle
	 *
	 * @return array|bool
	 */
	private static function get_script_data( $handle ) {
		switch ( $handle ) {
			case 'user-registration':
				return array(
					'ajax_url'                         => admin_url( 'admin-ajax.php' ),
					'user_registration_form_data_save' => wp_create_nonce( 'user_registration_form_data_save_nonce' ),
					'user_registration_profile_details_save' => wp_create_nonce( 'user_registration_profile_details_save_nonce' ),
					'user_registration_profile_picture_upload_nonce' => wp_create_nonce( 'user_registration_profile_picture_upload_nonce' ),
					'form_required_fields'             => ur_get_required_fields(),
					'login_option'                     => get_option( 'user_registration_general_setting_login_options' ),
					'user_registration_profile_picture_uploading' => __( 'Uploading...', 'user-registration' ),
					'ajax_submission_on_edit_profile'  => get_option( 'user_registration_ajax_form_submission_on_edit_profile', 'no' ),
					'message_required_fields'          => get_option( 'user_registration_form_submission_error_message_required_fields', __( 'This field is required.', 'user-registration' ) ),
					'message_email_fields'             => get_option( 'user_registration_form_submission_error_message_email', __( 'Please enter a valid email address.', 'user-registration' ) ),
					'message_url_fields'               => get_option( 'user_registration_form_submission_error_message_website_URL', __( 'Please enter a valid URL.', 'user-registration' ) ),
					'message_number_fields'            => get_option( 'user_registration_form_submission_error_message_number', __( 'Please enter a valid number.', 'user-registration' ) ),
					'message_confirm_password_fields'  => get_option( 'user_registration_form_submission_error_message_confirm_password', __( 'Password and confirm password not matched.', 'user-registration' ) ),
					'message_validate_phone_number'    => get_option( 'user_registration_form_submission_error_message_phone_number', __( 'Please enter a valid phone number.', 'user-registration' ) ),
					'message_username_character_fields'   => get_option( 'user_registration_form_submission_error_message_disallow_username_character', __( 'Please enter a valid username.', 'user-registration' ) ),
					'message_confirm_email_fields'     => get_option( 'user_registration_form_submission_error_message_confirm_email', __( 'Email and confirm email not matched.', 'user-registration' ) ),
					'message_confirm_number_field_max'     => __( 'Please enter a value less than or equal to %qty%.', 'user-registration' ),
					'message_confirm_number_field_min'     => __( 'Please enter a value greater than or equal to %qty%.', 'user-registration' ),
					'message_confirm_number_field_step'     => __( 'Please enter a multiple of %qty%.', 'user-registration' ),
					'ursL10n'                          => array(
						'user_successfully_saved' => get_option( 'user_registration_successful_form_submission_message_manual_registation', __( 'User successfully registered.', 'user-registration' ) ),
						'user_under_approval'     => get_option( 'user_registration_successful_form_submission_message_admin_approval', __( 'User registered. Wait until admin approves your registration.', 'user-registration' ) ),
						'user_email_pending'      => get_option( 'user_registration_successful_form_submission_message_email_confirmation', __( 'User registered. Verify your email by clicking on the link sent to your email.', 'user-registration' ) ),
						'captcha_error'           => get_option( 'user_registration_form_submission_error_message_recaptcha', __( 'Captcha code error, please try again.', 'user-registration' ) ),
						'hide_password_title'     => __( 'Hide Password', 'user-registration' ),
						'show_password_title'     => __( 'Show Password', 'user-registration' ),
						'password_strength_error' => __( 'Password strength is not strong enough', 'user-registration' ),
					),
				);
			break;

			case 'ur-password-strength-meter':
				return array(
					'home_url'            => home_url(),
					'i18n_password_error' => esc_attr__( 'Please enter a stronger password.', 'user-registration' ),
					'pwsL10n'             => array(
						'shortpw'  => __( 'Very Weak', 'user-registration' ),
						'bad'      => __( 'Weak', 'user-registration' ),
						'good'     => __( 'Medium', 'user-registration' ),
						'strong'   => __( 'Strong', 'user-registration' ),
						'mismatch' => __( 'Password with confirm password not matched.', 'user-registration' ),

					),
					'i18n_password_hint'  => apply_filters( 'user_registration_strong_password_message', __( 'Hint: To make password stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).', 'user-registration' ) ),
				);
				break;
				case 'ur-login':
					return array(
							'ajax_url'                         => admin_url( 'admin-ajax.php' ),
							'ur_login_form_save_nonce' 		   => wp_create_nonce( 'ur_login_form_save_nonce' ),
							'ajax_submission_on_ur_login'  	   => get_option('ur_login_ajax_submission', 'no' ),
					);
					break;
		}

		return false;
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public static function localize_printed_scripts() {
		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}
}

UR_Frontend_Scripts::init();
