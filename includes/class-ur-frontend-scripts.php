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
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Get styles for the frontend.
	 *
	 * @return array
	 */
	public static function get_styles() {
		return apply_filters( 'user_registration_enqueue_styles', array(
			'user-registration-smallscreen' => array(
				'src'     => self::get_asset_url( 'assets/css/user-registration-smallscreen.css' ),
				'deps'    => '',
				'version' => UR_VERSION,
				'media'   => 'only screen and (max-width: ' . apply_filters( 'user_registration_style_smallscreen_breakpoint', $breakpoint = '768px' ) . ')',
				'has_rtl' => true,
			),
			'user-registration-general'     => array(
				'src'     => self::get_asset_url( 'assets/css/user-registration.css' ),
				'deps'    => '',
				'version' => UR_VERSION,
				'media'   => 'all',
				'has_rtl' => true,
			),
		) );
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
		$suffix           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$register_scripts = array(
			'user-registration'          => array(
				'src'     => self::get_asset_url( 'assets/js/frontend/user-registration' . $suffix . '.js' ),
				'deps'    => array( 'jquery' ),
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
				'version' => UR_VERSION,
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
			'select2' => array(
				'src'     => self::get_asset_url( 'assets/css/select2.css' ),
				'deps'    => array(),
				'version' => UR_VERSION,
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

		if ( is_ur_account_page() || ur_post_content_has_shortcode( 'user_registration_form' ) ) {

			self::enqueue_script( 'user-registration' );

			if ( 'yes' == get_option( 'user_registration_general_setting_enable_strong_password' ) ) {

				self::enqueue_script( 'ur-password-strength-meter' );
			}
		}
		if ( is_ur_lost_password_page() ) {
			self::enqueue_script( 'ur-lost-password' );
		}

		// CSS Styles
		if ( $enqueue_styles = self::get_styles() ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				if ( ! isset( $args['has_rtl'] ) ) {
					$args['has_rtl'] = false;
				}
				if ( is_ur_account_page() || ur_post_content_has_shortcode( 'user_registration_form' ) ) {
					self::enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl'] );
				}
			}
		}
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
		global $wp;

		switch ( $handle ) {
			case 'user-registration' :
				

				return array(

					'ajax_url'                         => admin_url( 'admin-ajax.php' ),
					'user_registration_form_data_save' => wp_create_nonce( 'user_registration_form_data_save_nonce' ),
					'form_required_fields'             => ur_get_required_fields(),
					'home_url'                         => get_option('user_registration_general_setting_redirect_options'),

					'ursL10n'                          => array(
						'user_successfully_saved' => __( 'User successfully registered.', 'user-registration' ),
						'captcha_error'           => __( 'Captcha code error, please try again.', 'user-registration' ),

					),
				);
				
			break;
			
			case 'ur-password-strength-meter' :
				return array(
					'home_url'              => home_url(),
					'min_password_strength' => 3,
					'i18n_password_error'   => __( 'Confirm password', 'user-registration' ),
					'pwsL10n'               => array(
						'shortpw'  => __( 'Too short password', 'user-registration' ),
						'bad'      => __( 'Bad password', 'user-registration' ),
						'good'     => __( 'Good password', 'user-registration' ),
						'strong'   => __( 'Strong password', 'user-registration' ),
						'mismatch' => __( 'Password with confirm password not matched.', 'user-registration' ),

					),
					'i18n_password_hint'    => __( 'Hint: The password should be a at least seven characters long. To make it stronger, user upper and lower case letters, numbers and symbol like ! * ? $ % ^ & ).', 'user-registration' ),
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
