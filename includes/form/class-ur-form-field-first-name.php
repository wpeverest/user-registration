<?php
/**
 * UR_Form_Field_First_Name.
 *
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_First_Name Class
 */
class UR_Form_Field_First_Name extends UR_Form_Field {

	private static $_instance;

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->id                       = 'user_registration_first_name';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'First Name', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-input-first-name',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'First Name', 'user-registration' ),
			'default_field_name' => 'first_name',
		);
	}

	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list " class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}

	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		$is_condition_enabled = isset( $single_form_field->advance_setting->enable_conditional_logic ) ? $single_form_field->advance_setting->enable_conditional_logic : '0';
		$required             = isset( $single_form_field->general_setting->required ) ? $single_form_field->general_setting->required : 'no';
		$field_label          = isset( $form_data->label ) ? $form_data->label : '';
		$value                = isset( $form_data->value ) ? $form_data->value : '';

		if ( $is_condition_enabled !== '1' && 'yes' == $required && empty( $value ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $field_label ) {
					return __( $field_label . ' is required.', 'user-registration' );
				}
			);
		}
	}
}

return UR_Form_Field_First_Name::get_instance();
