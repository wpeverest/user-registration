<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract UR Setting Password Class
 *
 * @version  1.0.0
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Password extends UR_Field_Settings {


	public function __construct() {

		$this->field_id = 'password_advance_setting';

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

			'size' => array(

				'label' => __( 'Password Size','user-registration' ),

				'data-id' => $this->field_id . '_size',

				'name' => $this->field_id . '[size]',

				'class' => $this->default_class . ' ur-settings-size',

				'type' => 'text',

				'required' => false,

				'default' => '5',

				'placeholder' => __( 'Password Size' ,'user-registration' ),

			),

		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Password();
