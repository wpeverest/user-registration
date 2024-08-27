<?php
/**
 * UserRegistation Elementor
 *
 * @package UserRegistration\Class
 * @version 3.0.5
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
	 *
	 * @since 3.0.5
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
	 *
	 * @since 1.8.5
	 */
	public function register_widget() {
			// Include Widget files.
			require_once UR_ABSPATH . 'includes/3rd-party/elementor/class-ur-widget.php';

			ElementorPlugin::instance()->widgets_manager->register( new UR_Widget() );
	}

	/**
	 * Custom Widgets Category.
	 *
	 * @param object $elements_manager Elementor elements manager.
	 *
	 * @since 1.8.5
	 */
	public function ur_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'user-registration',
			array(
				'title' => esc_html__( 'User Registration', 'user-registration' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Load assets in the elementor document.
	 */
	public function editor_assets() {
		wp_register_style( 'user-registration-admin', UR()->plugin_url() . '/assets/css/admin.css', array(), UR()->version );
		wp_enqueue_style( 'user-registration-admin' );
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
