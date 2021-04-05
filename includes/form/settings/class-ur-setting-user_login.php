<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_User_login Class
 *
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_User_login extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'user_login_advance_setting';
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
			'username_length'          => array(
				'label'       => __( 'Limit Username Length', 'user-registration' ),
				'data-id'     => $this->field_id . '_username_length',
				'name'        => $this->field_id . '[username_length]',
				'class'       => $this->default_class . ' ur-settings-min',
				'type'        => 'number',
				'required'    => false,
				'default'     => $this->field_id . '_username_length',
				'placeholder' => __( 'Min Value', 'user-registration' ),
				'tip'         => __( 'Enter minimum number of length of username.', 'user-registration' ),
			),
			'username_character'          => array(
				'label'       => __( 'Allow Special Character', 'user-registration' ),
				'data-id'     => $this->field_id . '_username_character',
				'name'        => $this->field_id . '[username_character]',
				'class'       => $this->default_class . ' ur-settings-character',
				'type'        => 'select',
				'required'    => false,
				'options'     => array(
				'no'  => __( 'No', 'user-registration' ),
				'yes' => __( 'Yes', 'user-registration' ),
			),
				'default'     => 'yes',
				'placeholder' =>'',
				'tip'         => __( 'Check this option to validate the Username for Special Character', 'user-registration' ),
			)
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_User_login();
