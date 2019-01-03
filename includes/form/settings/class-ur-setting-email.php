<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Setting_Email Class
 *
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Email extends UR_Field_Settings {

	public function __construct() {
		$this->field_id = 'email_advance_setting';
	}

	public function output( $field_data = array() ) {
		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	public function register_fields() {
		$fields = array(
			'confirm_email' => array(
				'label' 		=> __( 'Confirm Email', 'user-registration' ),
				'data-id' 		=> $this->field_id . '_confirm_email',
				'name' 			=> $this->field_id . '[confirm_email]',
				'class'			=> $this->default_class . ' ur-settings-confirm-email',
				'type'			=> 'select',
				'options'		=> array(
					'no'	=> __('No', 'user-registration'),
					'yes'	=> __('Yes', 'user-registration'),
				),
				'required'		=> false,
				'default'		=> 'no',
				'placeholder'	=> __( 'Confirm Email', 'user-registration' ),
			),
			'custom_class' 	=> array(
				'label' 		=> __( 'Custom Class','user-registration' ),
				'data-id' 		=> $this->field_id . '_custom_class',
				'name' 			=> $this->field_id . '[custom_class]',
				'class' 		=> $this->default_class . ' ur-settings-custom-class',
				'type' 			=> 'text',
				'required' 		=> false,
				'default' 		=> '',
				'placeholder'	=> __( 'Custom Class' ,'user-registration' ),
			),
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Email();
