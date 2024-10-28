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
require_once 'widgets/class-ur-oxygen-widgets.php';
require_once 'widgets/class-ur-oxygen-widget-registration.php';

class UR_OXYGEN {

	/**
	 * Constructor.
	 *
	 * @since xx.xx.xx
	 */
	public function __construct() {

		$this->setup();
	}

	/**
	 * Init.
	 *
	 * @since xx.xx.xx
	 */
	public function setup() {
		if ( ! class_exists( 'OxyEl' ) ) {
			return;
		}

		add_action( 'oxygen_add_plus_sections', array( $this, 'add_accordion_section' ) );
		add_action( 'oxygen_add_plus_user-registration_section_content', array( $this, 'register_add_plus_subsections' ) );
		// new UR_OXYGEN_WIDGET();
		new UR_OXYGEN_WIDGET_REGISTRATION();
	}

	/**
	 * Add accordin section in the elements.
	 *
	 * @since xx.xx.xx
	 */
	public function add_accordion_section() {
		$brand_name = __( 'User Registration', 'user-registration' );
		\CT_Toolbar::oxygen_add_plus_accordion_section( 'user-registration', $brand_name );
	}

	/**
	 * Add subsection.
	 *
	 * @since xx.xx.xx
	 */
	public function register_add_plus_subsections() {
		do_action( 'oxygen_add_plus_user-registration_forms' );
	}
}
new UR_OXYGEN();
