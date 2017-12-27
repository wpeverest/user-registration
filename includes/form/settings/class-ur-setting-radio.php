<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract UR Setting Radio Class
 *
 * @version  1.0.0
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Radio extends UR_Field_Settings {


	public function __construct() {

		$this->field_id = 'radio_advance_setting';

	}

	public function output( $field_data = array() ) {

		// TODO: Implement output() method.
		$this->field_data = $field_data;

		$this->register_fields();

		$field_html = $this->fields_html;

		return $field_html;
	}

	public function register_fields() {
		// TODO: Implement register_fields() method.
		$fields = array(

			'custom_class' => array(

				'label' => __( 'Custom Class' ,'user-registration' ),

				'id' => $this->field_id . '_custom_class',

				'name' => $this->field_id . '[custom_class]',

				'class' => $this->default_class . ' ur-settings-custom-class',

				'type' => 'text',

				'required' => false,

				'default' => '',

				'placeholder' => __( 'Custom Class','user-registration' ),

			),
			'options'      => array(

				'label' => __( 'Options ', 'user-registration' ),

				'id' => $this->field_id . '_options',

				'name' => $this->field_id . '[options]',

				'class' => $this->default_class . ' ur-settings-options',

				'type' => 'textarea',

				'required' => false,

				'default' => '',

				'placeholder' => __( "Please input your text here to create the radio choices list like:\nOption 1,\nOption 2,\nOption 3",'user-registration' ),

			),

		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Radio();
