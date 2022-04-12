<?php
/**
 * UserRegistation Elementor
 *
 * @package UserRegistration\Class
 * @version 2.1.6
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
	 * @since 2.1.6
	 */
	public function init() {

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'ur_elementor_widget_categories' ) );
	}

	/**
	 * Register User Registration forms Widget.
	 *
	 * @since 1.8.5
	 */
	public function register_widget() {
			// Include Widget files.
			require_once UR_ABSPATH . 'includes/elementor/class-ur-widget.php';

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

}

new UR_Elementor();
