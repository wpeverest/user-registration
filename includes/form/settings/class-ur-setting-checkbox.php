<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Checkbox Class
 *
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Checkbox extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'checkbox_advance_setting';
	}

	public function output( $field_data = array() ) {

		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	public function register_fields() {
		$fields = array(

			'custom_class' => array(
				'label'       => __( 'Custom Class', 'user-registration' ),
				'data-id'     => $this->field_id . '_custom_class',
				'name'        => $this->field_id . '[custom_class]',
				'class'       => $this->default_class . ' ur-settings-custom-class',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Custom Class', 'user-registration' ),
				'tip'         => __( 'Class name to embed in this field.', 'user-registration' ),
			),
			'choice_limit'          => array(
				'label'       => __( 'Choice Limit', 'user-registration' ),
				'data-id'     => $this->field_id . '_choice_limit',
				'name'        => $this->field_id . '[choice_limit]',
				'class'       => $this->default_class . ' ur-settings-min',
				'type'        => 'number',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Choice Limit', 'user-registration' ),
				'tip'         => __( 'Enter minimum number choices that can be selected.', 'user-registration' ),
			),
			'select_all'          => array(
				'label'       => __( 'Select All ', 'user-registration' ),
				'data-id'     => $this->field_id . '_select_all',
				'name'        => $this->field_id . '[select_all]',
				'class'       => $this->default_class . ' ur-settings-select',
				'type'        => 'select',
				'required'    => false,
				'options'     => array(
				'no'  => __( 'No', 'user-registration' ),
				'yes' => __( 'Yes', 'user-registration' ),
			),
				'default'     => 'no',
				'placeholder' =>'',
				'tip'         => __( 'Enable this option to select all the options', 'user-registration' ),
			)
		);

		$fields = apply_filters( 'checkbox_custom_advance_settings', $this->field_id, $this->default_class, $fields );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Checkbox();
