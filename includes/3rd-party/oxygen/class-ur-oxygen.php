<?php

/**
 * UserRegistation Oxygen
 *
 * @package UserRegistration\Class
 * @since 3.2.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Oxygen class.
 */

class UR_OXYGEN {

	/**
	 * Constructor.
	 *
	 * @since 3.3.5
	 */
	public function __construct() {

		// $this->setup();
		add_action( 'wp_loaded', array( $this, 'setup' ) );
	}

	/**
	 * Init.
	 *
	 * @since 3.3.5
	 */
	public function setup() {
		if ( ! class_exists( 'OxyEl' ) ) {
			return;
		}

		add_action( 'oxygen_add_plus_sections', array( $this, 'add_accordion_section' ) );
		add_action( 'oxygen_add_plus_user-registration_section_content', array( $this, 'register_add_plus_subsections' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'custom_init' ) );
		add_action( 'oxygen_enqueue_scripts', array( $this, 'enqueue_oxygen_editor_styles' ) );

		$this->register_widgets();
	}

	/**
	 * Add accordin section in the elements.
	 *
	 * @since 3.3.5
	 */
	public function add_accordion_section() {
		$brand_name = __( 'User Registration & Membership', 'user-registration' );
		\CT_Toolbar::oxygen_add_plus_accordion_section( 'user-registration', $brand_name );
	}

	/**
	 * Enqueue the styles.
	 *
	 * @since 3.3.5
	 */
	public function custom_init() {
		wp_register_style( 'user-registration-general', UR()->plugin_url() . '/assets/css/user-registration.css', array(), UR()->version );
		wp_register_style( 'user-registration-my-account', UR()->plugin_url() . '/assets/css/my-account-layout.css', array(), UR()->version );

		wp_enqueue_style( 'user-registration-general' );
		wp_enqueue_style( 'user-registration-my-account' );
	}

	public function enqueue_oxygen_editor_styles() {
		if ( ! isset( $_GET['ct_builder'] ) ) {
			return; // not Oxygen panel
		}
		wp_register_style( 'user-registration-admin', UR()->plugin_url() . '/assets/css/admin.css', array(), UR()->version );
		wp_enqueue_style( 'user-registration-admin' );
	}

	/**
	 * Add subsection.
	 *
	 * @since 3.3.5
	 */
	public function register_add_plus_subsections() {
		do_action( 'oxygen_add_plus_user-registration_forms' );
	}

	/**
	 * Register widgets.
	 *
	 * @since 3.3.5
	 */
	public function register_widgets() {
		require_once UR_ABSPATH . 'includes/3rd-party/oxygen/widgets/class-ur-oxygen-widget-registration.php';
		require_once UR_ABSPATH . 'includes/3rd-party/oxygen/widgets/class-ur-oxygen-widget-login.php';
		require_once UR_ABSPATH . 'includes/3rd-party/oxygen/widgets/class-ur-oxygen-widget-myaccount.php';
		require_once UR_ABSPATH . 'includes/3rd-party/oxygen/widgets/class-ur-oxygen-widget-editprofile.php';
		require_once UR_ABSPATH . 'includes/3rd-party/oxygen/widgets/class-ur-oxygen-widget-editpassword.php';

		new UR_OXYGEN_WIDGET_REGISTRATION();
		new UR_OXYGEN_WIDGET_LOGIN();
		new UR_OXYGEN_WIDGET_MYACCOUNT();
		new UR_OXYGEN_WIDGET_EDITPROFILE();
		new UR_OXYGEN_WIDGET_EDITPASSWORD();

		// include if pro version
		if ( is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
			require_once UR_ABSPATH . 'includes/3rd-party/oxygen/widgets/class-ur-oxygen-widget-popup.php';
			require_once UR_ABSPATH . 'includes/3rd-party/oxygen/widgets/class-ur-oxygen-widget-profile-details.php';

			new UR_OXYGEN_WIDGET_PROFILE_DETAILS();
			new UR_OXYGEN_WIDGET_POPUP();
		}
	}
}
new UR_OXYGEN();
