<?php
/**
 * UR_Form_Field_Privacy_Policy.
 *
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Form_Field_Privacy_Policy Class
 */
class UR_Form_Field_Privacy_Policy extends UR_Form_Field {

	private static $_instance;

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->id = 'user_registration_privacy_policy';
		$this->form_id = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Privacy Policy', 'user-registration' ),
			'icon' => 'dashicons dashicons-yes',
		);

		$this->field_defaults = array(
			'default_label' => __( 'Privacy Policy', 'user-registration' ),
			'default_field_name' => 'privacy_policy_' . ur_get_random_number(),
		);
	}

	/**
	 * @return string
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list" class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	/**
	 * @param $single_form_field
	 * @param $form_data
	 * @param $filter_hook
	 * @param $form_id
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		$required = isset( $single_form_field->general_setting->required ) ? $single_form_field->general_setting->required : 'no';
		$field_label = isset( $form_data->label ) ? $form_data->label : '';
		$value = isset( $form_data->value ) ? $form_data->value : '';

		if ( 'yes' == $required && empty( $value ) ) {
			add_filter( $filter_hook, function ( $msg ) use ( $field_label ) {
				return __( $field_label . ' is required.', 'user-registration' );
			});
		}
	}
}

return UR_Form_Field_Privacy_Policy::get_instance();
