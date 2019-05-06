<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Number Class
 *
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Number extends UR_Field_Settings {

	public function __construct() {
		$this->field_id = 'number_advance_setting';
	}

	public function output( $field_data = array() ) {
		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

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
			),
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Number();
