<?php
/**
 * User Registration Form for Oxygen Builder.
 *
 * @package UserRegistration\Class
 * @version 3.3.5
 */

/**
 * Oxygen Form Widget.
 */
class UR_OXYGEN_WIDGET extends \OxyEl {


	/**
	 * Indicates whether the CSS has been added.
	 *
	 * @var bool
	 */
	protected $css_added = false;

	/**
	 * Indicates whether the JavaScript has been added.
	 *
	 * @var bool
	 */
	protected $js_added = false;

	/**
	 * The name of the widget.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The slug for the widget.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The icon for the widget.
	 *
	 * @var string
	 */
	protected $icon;

	/**
	 * The button priority for the widget.
	 *
	 * @var int
	 */
	protected $priority;


	/**
	 * Constructor for the Oxygen Form Widget.
	 *
	 * @param string $name     The name of the widget.
	 * @param string $slug     The slug for the widget.
	 * @param string $icon     The icon for the widget.
	 * @param int    $priority The button priority for the widget.
	 */
	public function __construct( $name = 'Default Name', $slug = 'default_slug', $icon = null, $priority = 10 ) {
		parent::__construct();
		$this->name     = $name;
		$this->slug     = $slug;
		$this->icon     = $icon;
		$this->priority = $priority;
	}


	/**
	 * Get the name of the widget.
	 *
	 * @return string The name.
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * Get the slug for the widget.
	 *
	 * @return string The slug.
	 */
	public function slug() {
		return $this->slug;
	}

	/**
	 * Get the icon for the widget.
	 *
	 * @return string The icon.
	 */
	public function icon() {
		return $this->icon;
	}

	/**
	 * Get the button priority for the widget.
	 *
	 * @return int The button priority.
	 */
	public function button_priority() {
		return $this->priority;
	}


	/**
	 * Get the class names for the widget.
	 *
	 * @return array The class names.
	 */
	public function class_names() {
		return array(
			'oxy-ur-widget',
			'oxy-form-widget-' . $this->slug(),
		);
	}

	/**
	 * Get the button place for the widget.
	 *
	 * @return string The button place.
	 */
	public function button_place() {
		$button_place = $this->accordion_button_place();

		if ( $button_place ) {
			return 'user-registration::' . $button_place;
		}

		return '';
	}

	/**
	 * Get the accordion button place for the widget.
	 *
	 * @return string The accordion button place.
	 */
	public function accordion_button_place() {
		return 'forms';
	}

	/**
	 * Get the icon in SVG format.
	 *
	 * @param string $svg The SVG content.
	 * @return string The base64 encoded SVG.
	 */
	public function get_icon_svg( $svg ) {
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}


	/**
	 * Get forms based on type.
	 *
	 * @param string $type The type of form to retrieve.
	 * @return array The forms array.
	 */
	public function get_forms( $type ) {

		$forms = array();
		if ( 'popup' === $type ) {
			$forms[0] = __( 'Select a Form', 'user-registration' );
			$ur_forms = ur_get_all_user_registration_pop();
		}

		if ( 'registration' === $type ) {
			$forms[0] = __( 'Select a Form', 'user-registration' );
			$ur_forms = ur_get_all_user_registration_form();
		}

		if ( ! empty( $ur_forms ) ) {
			foreach ( $ur_forms as $form_value => $form_name ) {
				$forms[ $form_value ] = $form_name;
			}
		}
		return $forms;
	}


	/**
	 * Form contrainer style controls.
	 *
	 * @since 3.3.5
	 */
	public function form_container_style_controls() {
		$section_container = $this->addControlSection(
			'ur_container',
			__( 'Form Container', 'user-registration' ),
			'assets/icon.png',
			$this
		);
		$selector          = '.user-registration';
		$section_container->addStyleControls(
			array(
				array(
					'name'     => __( 'Background Color', 'user-registration' ),
					'selector' => $selector,
					'property' => 'background-color',
				),
				array(
					'name'     => __( 'Max Width', 'user-registration' ),
					'selector' => $selector,
					'property' => 'width',
				),
			)
		);

		$section_container->addPreset(
			'padding',
			'ur_container_padding',
			__( 'Padding', 'user-registration' ),
			$selector
		)->whiteList();

		$section_container->addPreset(
			'margin',
			'ur_container_margin',
			__( 'Margin', 'user-registration' ),
			$selector
		)->whiteList();

		$section_container->addPreset(
			'border',
			'ur_container_border',
			__( 'Border', 'user-registration' ),
			$selector
		)->whiteList();

		$section_container->addPreset(
			'border-radius',
			'ur_container_radius',
			__( 'Border Radius', 'user-registration' ),
			$selector
		)->whiteList();

		$section_container->boxShadowSection(
			__( 'Box Shadow', 'user-registration' ),
			$selector,
			$this
		);
	}

	/**
	 * Field input label styles.
	 *
	 * @since 3.3.5
	 */
	public function form_input_labels_style() {
		$section_label = $this->addControlSection(
			'ur-label',
			__( 'Labels', 'user-registration' ),
			'assets/icon.png',
			$this
		);

		$selector = '.ur-label';
		$section_label->typographySection( __( 'Typography' ), $selector, $this );
		$section_label->addStyleControls(
			array(
				array(
					'name'     => __( 'Text Color', 'user-registration' ),
					'selector' => $selector,
					'property' => 'color',
				),
			)
		);
		$section_label->addStyleControl(
			array(
				'name'     => __( 'Asterisk Color', 'user-registration' ),
				'selector' => '.ur-label .required',
				'property' => 'color',
			)
		);
	}

	/**
	 * Submit button style.
	 *
	 * @since 3.3.5
	 */
	public function submit_btn_style( $selector = '.ur-submit-button' ) {
		$section_submit_btn = $this->addControlSection(
			'ur-submit-button',
			__( 'Submit Button', 'user-registration' ),
			'assets/icon.png',
			$this
		);

		$selector_submit_bttn = $selector;
		$section_submit_btn->addStyleControls(
			array(
				array(
					'name'     => __( 'Color', 'user-registration' ),
					'selector' => $selector_submit_bttn,
					'property' => 'color',
				),
				array(
					'name'     => __( 'Background Color', 'user-registration' ),
					'selector' => $selector_submit_bttn,
					'property' => 'background-color',
				),
				array(
					'name'     => __( 'Hover Color', 'user-registration' ),
					'selector' => '.ur-submit-button:hover',
					'property' => 'background-color',
				),
				array(
					'name'         => __( 'Width', 'user-registration' ),
					'selector'     => $selector_submit_bttn,
					'property'     => 'width',
					'control_type' => 'slider-measurebox',
					'unit'         => 'px',
				),
				array(
					'name'         => __( 'Margin Top', 'user-registration' ),
					'selector'     => $selector_submit_bttn,
					'property'     => 'margin-top',
					'control_type' => 'slider-measurebox',
					'unit'         => 'px',
				),
			)
		);

		$section_submit_btn->addPreset(
			'padding',
			'ur_submit_bttn_padding',
			__( 'Padding', 'user-registration' ),
			$selector_submit_bttn
		)->whiteList();

		$section_submit_btn->addPreset(
			'margin',
			'ur_submit_bttn_margin',
			__( 'Margin', 'user-registration' ),
			$selector_submit_bttn
		)->whiteList();

		$section_submit_btn->typographySection( __( 'Typography', 'user-registration' ), $selector_submit_bttn, $this );
		$section_submit_btn->borderSection( __( 'Border', 'user-registration' ), $selector_submit_bttn, $this );
		$section_submit_btn->borderSection( __( 'Hover Border', 'user-registration' ), $selector_submit_bttn . ':hover', $this );
		$section_submit_btn->boxShadowSection( __( 'Box Shadow', 'user-registration' ), $selector_submit_bttn, $this );
		$section_submit_btn->boxShadowSection( __( 'Hover Box Shadow', 'user-registration' ), $selector_submit_bttn . ':hover', $this );
	}
}
