<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Setting_Country Class
 *
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Country extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'country_advance_setting';
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
				'label' => __( 'Custom Class','user-registration' ),
				'data-id' => $this->field_id . '_custom_class',
				'name' => $this->field_id . '[custom_class]',
				'class' => $this->default_class . ' ur-settings-custom-class',
				'type' => 'text',
				'required' => false,
				'default' => '',
				'placeholder' => __( 'Custom Class' ,'user-registration' ),
			),
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Country();
