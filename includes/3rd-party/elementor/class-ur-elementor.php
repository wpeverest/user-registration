<?php
/**
 * UserRegistation Elementor
 *
 * @package UserRegistration\Class
 * @since 3.2.2
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Plugin as ElementorPlugin;

/**
 * Elementor class.
 */
class UR_Elementor {

	/**
	 * Initialize.
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize elementor hooks.
	 */
	public function init() {

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'ur_elementor_widget_categories' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
	}

	/**
	 * Register User Registration forms Widget.
	 */
	public function register_widget() {
			// Include Widget files.
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/widgets/class-ur-widgets-registration.php';
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/widgets/class-ur-widgets-login.php';
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/widgets/class-ur-widgets-myaccount.php';
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/widgets/class-ur-widgets-edit-profile.php';
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/widgets/class-ur-widgets-edit-password.php';

			ElementorPlugin::instance()->widgets_manager->register( new UR_Elementor_Widget_Registration() );
			ElementorPlugin::instance()->widgets_manager->register( new UR_Elementor_Widget_Login() );
			ElementorPlugin::instance()->widgets_manager->register( new UR_Elementor_Widget_MyAccount() );
			ElementorPlugin::instance()->widgets_manager->register( new UR_Elementor_Widget_Edit_Profile() );
			ElementorPlugin::instance()->widgets_manager->register( new UR_Elementor_Widget_Edit_Password() );

			// include if pro version
		if ( is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/widgets/class-ur-widgets-profile-details.php';
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/widgets/class-ur-widgets-popup.php';
			ElementorPlugin::instance()->widgets_manager->register( new UR_Elementor_Widget_View_Details() );
			ElementorPlugin::instance()->widgets_manager->register( new UR_Elementor_Widget_Popup() );
		}
	}

	/**
	 * Custom Widgets Category.
	 *
	 * @param object $elements_manager Elementor elements manager.
	 */
	public function ur_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'user-registration',
			array(
				'title' => esc_html__( 'User Registration & Membership', 'user-registration' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Load assets in the elementor document.
	 */
	public function editor_assets() {
		wp_register_style( 'user-registration-admin', UR()->plugin_url() . '/assets/css/admin.css', array(), UR()->version );
		wp_register_style( 'user-registration-my-account', UR()->plugin_url() . '/assets/css/my-account-layout.css', array(), UR()->version );

		wp_enqueue_style( 'user-registration-admin' );
		wp_enqueue_style( 'user-registration-my-account' );
	}

	/**
	 * Register widget styles.
	 */
	public function register_widget_styles() {
		wp_register_style( 'user-registration-general', UR()->plugin_url() . '/assets/css/user-registration.css', array(), UR()->version );
		wp_enqueue_style( 'user-registration-general' );
	}
}

new UR_Elementor();
