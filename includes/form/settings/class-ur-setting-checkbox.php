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
			),

			'choices'      => array(
				'label'       => __( 'Choices', 'user-registration' ),
				'data-id'     => $this->field_id . '_choices',
				'name'        => $this->field_id . '[choices]',
				'class'       => $this->default_class . ' ur-settings-choices',
				'type'        => 'textarea',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( "Please input your text here to create the checkbox list like:\nCheckbox 1,\nCheckbox 2,\nCheckbox 3", 'user-registration' ),
			),
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Checkbox();
