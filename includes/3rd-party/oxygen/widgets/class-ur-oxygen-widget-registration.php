<?php
require_once 'class-ur-oxygen-widgets.php';

class UR_OXYGEN_WIDGET_REGISTRATION extends UR_OXYGEN_WIDGET {

	/**
	 * Constructor for the Oxygen Form Widget.
	 *
	 * @since xx.xx.xx
	 */
	public function __construct() {
		$this->name     = __( 'Registration Form ', 'user-registration' );
		$this->slug     = 'ur-registration';
		$this->icon     = '';
		$this->priority = 1;
		parent::__construct();
	}

	/**
	 * Render the widget.
	 *
	 * @since xx.xx.xx
	 */
	public function render( $options, $defaults, $content ) {
		// $this->add_css();
		// $this->add_js();
		$this->add_form();
	}

	/**
	 * Add the form.
	 *
	 * @since xx.xx.xx
	 */
	public function add_form() {
		$shortcode = '[user_registration_form id="186"]';
		echo do_shortcode( $shortcode );
	}
}
