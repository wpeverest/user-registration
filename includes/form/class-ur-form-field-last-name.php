<?php
/**
 * UR_Form_Field_Last_Name.
 *
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Last_Name Class
 */
class UR_Form_Field_Last_Name extends UR_Form_Field {

	private static $_instance;

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->id                       = 'user_registration_last_name';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Last Name ', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-input-last-name',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Last Name', 'user-registration' ),
			'default_field_name' => 'last_name',
		);
	}

	public function get_registered_admin_fields() {

		return '<li id="' . esc_attr( $this->id ) . '_list " class="ur-registered-item draggable" data-field-id="' .esc_attr( $this->id ) . '"><span class="' . esc_attr( $this->registered_fields_config['icon'] ). '"></span>' . esc_html( $this->registered_fields_config['label'] ) . '</li>';
	}

	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		$required             = isset( $single_form_field->general_setting->required ) ? $single_form_field->general_setting->required : 'no';
		$field_label          = isset( $form_data->label ) ? $form_data->label : '';
		$value                = isset( $form_data->value ) ? $form_data->value : '';
		$urcl_hide_fields = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array();
		$field_name       = isset( $single_form_field->general_setting->field_name ) ? $single_form_field->general_setting->field_name : '';

		if ( ! in_array( $field_name, $urcl_hide_fields, true ) && 'yes' == $required && empty( $value ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $field_label ) {
					return __( $field_label . ' is required.', 'user-registration' );
				}
			);
		}
	}
}

return UR_Form_Field_Last_Name::get_instance();
