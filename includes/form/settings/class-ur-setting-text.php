<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR Setting Text Class
 *
 * @version  1.0.0
 * @package  UserRegistration/Form/Settings
 * @author   WPEverest
 */
class UR_Setting_Text extends UR_Field_Settings {


	public function __construct() {

		$this->field_id = 'text_advance_setting';

	}

	public function output( $field_data = array() ) {

		$this->field_data = $field_data;

		$this->register_fields();

		$field_html = $this->fields_html;

		return $field_html;
	}

	public function register_fields() {
		$fields = array(

			'size'          => array(

				'label' => __( 'Size','user-registration' ),

				'data-id' => $this->field_id . '_size',

				'name' => $this->field_id . '[size]',

				'class' => $this->default_class . ' ur-settings-size',

				'type' => 'text',

				'required' => false,

				'default' => '20',

				'placeholder' => __( 'Size','user-registration' ),

			),
			'custom_class'  => array(

				'label' => __( 'Custom Class', 'user-registration' ),

				'id' => $this->field_id . '_custom_class',

				'name' => $this->field_id . '[custom_class]',

				'class' => $this->default_class . ' ur-settings-custom-class',

				'type' => 'text',

				'required' => false,

				'default' => '',

				'placeholder' => __( 'Custom Class','user-registration' ),

			),

		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Text();
