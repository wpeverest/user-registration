<?php
/**
 * UR_Setting_Number Class.
 *
 * @package  UserRegistration/Form/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Number Class.
 *
 * @package  UserRegistration/Form/Settings
 */
class UR_Setting_Number extends UR_Field_Settings {


	/**
	 * UR_Setting_Number Class Constructor.
	 */
	public function __construct() {
		$this->field_id = 'number_advance_setting';
	}

	/**
	 * Output.
	 *
	 * @param array $field_data field data.
	 * @return string $field_html Field HTML.
	 */
	public function output( $field_data = array() ) {
		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	/**
	 * Register Fields.
	 */
	public function register_fields() {
		$fields = array(
			'min'          => array(
				'label'       => __( 'Minimum Value', 'user-registration' ),
				'data-id'     => $this->field_id . '_min',
				'name'        => $this->field_id . '[min]',
				'class'       => $this->default_class . ' ur-settings-min',
				'type'        => 'number',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Min Value', 'user-registration' ),
				'tip'         => __( 'Minimum allowed number.', 'user-registration' ),
			),
			'max'          => array(
				'label'       => __( 'Maximum Value', 'user-registration' ),
				'data-id'     => $this->field_id . '_max',
				'name'        => $this->field_id . '[max]',
				'class'       => $this->default_class . ' ur-settings-max',
				'type'        => 'number',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Max Value', 'user-registration' ),
				'tip'         => __( 'Maximum allowed number.', 'user-registration' ),
			),
			'step'         => array(
				'label'       => __( 'Step', 'user-registration' ),
				'data-id'     => $this->field_id . '_step',
				'name'        => $this->field_id . '[step]',
				'class'       => $this->default_class . ' ur-settings-step',
				'type'        => 'number',
				'required'    => false,
				'default'     => 1,
				'placeholder' => __( 'Legal Number Intervals', 'user-registration' ),
				'tip'         => __( 'Amount to increment or decrement at one step.', 'user-registration' ),
			),
			'custom_class' => array(
				'label'       => __( 'Custom Class', 'user-registration' ),
				'id'          => $this->field_id . '_custom_class',
				'name'        => $this->field_id . '[custom_class]',
				'class'       => $this->default_class . ' ur-settings-custom-class',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Custom Class', 'user-registration' ),
				'tip'         => __( 'Class name to embed in this field.', 'user-registration' ),
			),
		);

		/**
		 * Filter to modify the number custom advance settings.
		 *
		 * @param string $fields Advance Settings Fields.
		 * @param int field_id Field ID.
		 * @param class default_class Default Class.
		 *
		 * @return string $fields.
		 */
		$fields = apply_filters( 'number_custom_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Number();
