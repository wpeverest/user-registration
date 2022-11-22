<?php
/**
 * Display notices in admin.
 *
 * @class    UR_Admin_Notices
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Admin_Notices Class.
 */
class UR_Admin_Notices {

	/**
	 * Stores notices.
	 *
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Array of notices - name => callback
	 *
	 * @var array
	 */
	private static $core_notices = array(
		'update'   => 'update_notice',
		'install'  => 'install_notice',
		'register' => 'register_notice',
	);

	/**
	 * Constructor.
	 */
	public static function init() {
		self::$notices = get_option( 'user_registration_admin_notices', array() );

		add_action( 'switch_theme', array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'user_registration_installed', array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'shutdown', array( __CLASS__, 'store_notices' ) );

		if ( current_user_can( 'manage_user_registration' ) ) {
			add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
			add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );
		}
	}

	/**
	 * Store notices to DB
	 */
	public static function store_notices() {
		update_option( 'user_registration_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices.
	 *
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {
		self::$notices = array();
	}

	/**
	 * Reset notices for themes when switched or a new version of UR is installed.
	 */
	public static function reset_admin_notices() {
		self::add_notice( 'register' );
	}

	/**
	 * Show a notice.
	 *
	 * @param string $name Name.
	 */
	public static function add_notice( $name ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );
	}

	/**
	 * Remove a notice from being displayed.
	 *
	 * @param string $name Name.
	 */
	public static function remove_notice( $name ) {
		self::$notices = array_diff( self::get_notices(), array( $name ) );
		delete_option( 'user_registration_admin_notice_' . $name );
	}

	/**
	 * See if a notice is being shown.
	 *
	 * @param  string $name Name.
	 * @return boolean
	 */
	public static function has_notice( $name ) {
		return in_array( $name, self::get_notices(), true );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {
		if ( isset( $_GET['ur-hide-notice'] ) && isset( $_GET['_ur_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_ur_notice_nonce'] ) ), 'user_registration_hide_notices_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Cheatin&#8217; huh?', 'user-registration' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['ur-hide-notice'] ) );
			self::remove_notice( $hide_notice );

			// Remove the onboarding skipped checker if install notice is removed.
			if ( 'install' === $hide_notice ) {
				delete_option( 'user_registration_onboarding_skipped' );
			}

			do_action( 'user_registration_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public static function add_notices() {
		$notices = self::get_notices();

		if ( $notices ) {
			wp_enqueue_style( 'user-registration-activation', UR()->plugin_url() . '/assets/css/activation.css', array(), UR_VERSION );

			// Add RTL support.
			wp_style_add_data( 'user-registration-activation', 'rtl', 'replace' );

			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'user_registration_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
				} else {
					add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
				}
			}
		}
	}

	/**
	 * Remove Notices other than user registration on user registration builder page.
	 *
	 * @since 1.4.5
	 */
	public static function hide_unrelated_notices() {
		global $wp_filter;

		// Array to define pages where notices are to be excluded.
		$pages_to_exclude = array(
			'add-new-registration',
			'user-registration-settings',
			'user-registration-email-templates',
			'user-registration-mailchimp',
		);

		$pages_to_exclude = apply_filters( 'user_registration_notice_excluded_pages', $pages_to_exclude );

		// Return on other than user registraion builder page.
		if ( empty( $_REQUEST['page'] ) || ! in_array( $_REQUEST['page'], $pages_to_exclude ) ) {
			return;
		}

		foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $wp_notice ) {

			if ( ! empty( $wp_filter[ $wp_notice ]->callbacks ) && is_array( $wp_filter[ $wp_notice ]->callbacks ) ) {

				foreach ( $wp_filter[ $wp_notice ]->callbacks as $priority => $hooks ) {
					foreach ( $hooks as $name => $arr ) {
						// Remove all notices if the page is form builder page.
						if ( 'add-new-registration' === $_REQUEST['page'] ) {
							unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
						} else {
							// Remove all notices except user registration plugins notices.
							if ( strstr( $name, 'user_registration_error_notices' ) ) {
								if ( ! isset( $_REQUEST['tab'] ) || 'license' !== $_REQUEST['tab'] ) {
									unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
								}
							} else if ( strpos( $name, 'user_registration_' ) || strpos( $name, 'UR_Admin_Notices' ) ) {
								break;
							} else {
								unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Add a custom notice.
	 *
	 * @param string $name Name.
	 * @param string $notice_html Notice.
	 */
	public static function add_custom_notice( $name, $notice_html ) {
		self::add_notice( $name );
		update_option( 'user_registration_admin_notice_' . sanitize_text_field( $name ), wp_kses_post( $notice_html ) );
	}

	/**
	 * Output any stored custom notices.
	 */
	public static function output_custom_notices() {
		$notices = self::get_notices();

		if ( $notices ) {
			foreach ( $notices as $notice ) {
				if ( empty( self::$core_notices[ $notice ] ) ) {
					$notice_html = get_option( 'user_registration_admin_notice_' . $notice );

					if ( $notice_html ) {
						include 'views/html-notice-custom.php';
					}
				}
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 */
	public static function update_notice() {

		if ( version_compare( get_option( 'user_registration_db_version' ), UR_VERSION, '<' ) ) {
			$updater = new UR_Background_Updater();

			if ( $updater->is_updating() || ! empty( $_GET['do_update_user_registration'] ) ) {
				include 'views/html-notice-updating.php';
			} else {
				include 'views/html-notice-update.php';
			}
		} else {
			include 'views/html-notice-updated.php';
		}
	}

	/**
	 * If we have just installed, show a message with the install pages button.
	 */
	public static function install_notice() {
		include 'views/html-notice-install.php';
	}

	/**
	 * If we have just installed, and allow registration option not enable
	 */
	public static function register_notice() {
		$users_can_register = apply_filters( 'ur_register_setting_override', get_option( 'users_can_register' ) );

		if ( ! $users_can_register && is_admin() && ! defined( 'DOING_AJAX' ) ) {
			include 'views/html-notice-registration.php';
		} else {
			self::remove_notice( 'register' );
		}
	}
}

UR_Admin_Notices::init();
