<?php
require_once 'class-ur-oxygen-widgets.php';

class UR_OXYGEN_WIDGET_POPUP extends UR_OXYGEN_WIDGET {

	/**
	 * Constructor for the Oxygen Form Widget.
	 *
	 * @since 3.3.5
	 */
	public function __construct() {
		$this->name     = __( 'Popup', 'user-registration' );
		$this->slug     = 'ur-popup';
		$this->icon     = $this->get_icon_svg( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" size="24" class="css-f0g03s"><path fill="#FFFFFF" fill-rule="evenodd" d="M7.2 13.8a.84.84 0 0 1 .84-.84h7.92a.84.84 0 0 1 0 1.68H8.04a.84.84 0 0 1-.84-.84Zm0 3.36a.84.84 0 0 1 .84-.84h7.92a.84.84 0 0 1 0 1.68H8.04a.84.84 0 0 1-.84-.84Zm10.685-7.111a.72.72 0 0 0 0-1.018l-.949-.95.949-.948a.72.72 0 1 0-1.018-1.018l-.95.949-.948-.95a.72.72 0 1 0-1.018 1.019l.949.949-.95.949a.72.72 0 1 0 1.019 1.018l.949-.949.949.949a.72.72 0 0 0 1.018 0Z" clip-rule="evenodd"></path><path fill="#FFFFFF" fill-rule="evenodd" d="M1.92 4.8A2.88 2.88 0 0 1 4.8 1.92h14.4a2.88 2.88 0 0 1 2.88 2.88v14.4a2.88 2.88 0 0 1-2.88 2.88H4.8a2.88 2.88 0 0 1-2.88-2.88V4.8Zm2.88-.878h14.4c.485 0 .878.393.878.878v14.4a.878.878 0 0 1-.878.878H4.8a.878.878 0 0 1-.878-.878V4.8c0-.485.393-.878.878-.878Z" clip-rule="evenodd"></path></svg>	' );
		$this->priority = 1;
		parent::__construct();
	}

	/**
	 * Register widget controls.
	 *
	 * @since 3.3.5
	 */
	public function controls() {
		$templates_control = $this->addOptionControl(
			array(
				'type'    => 'dropdown',
				'name'    => __( 'Select a Form', 'user-registration' ),
				'slug'    => 'ur_oxygen_popup',
				'value'   => $this->get_forms( 'popup' ),
				'default' => 0,
				'css'     => false,
			)
		);

		$templates_control->rebuildElementOnChange();
		$this->form_container_style_controls();
		$this->form_input_labels_style();
		$this->submit_btn_style( '.button' );
	}

	/**
	 * Render the widget.
	 *
	 * @since 3.3.5
	 */
	public function render( $options, $defaults, $content ) {

		$shortcode = isset( $options['ur_oxygen_popup'] ) ? $options['ur_oxygen_popup'] : 0;
		$this->add_form( $shortcode );
	}

	/**
	 * Add the form.
	 *
	 * @since 3.3.5
	 */
	public function add_form( $shortcode ) {
		if ( 0 === $shortcode || '0' === $shortcode ) {
			$forms = $this->get_forms( 'popup' );
			if ( count( $forms ) <= 1 ) {
				echo__( 'You have not created a popup, Please Create a popup first', 'user-registration' );
			}
			return;
		}
		echo do_shortcode( '[user_registration_popup id="' . $shortcode . '"]' );
	}
}
