<?php
/**
 * UR_Form_Field_Number.
 *
 * @class    UR_Form_Field_Number
 * @since    1.0.5
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Number Class
 */
class UR_Form_Field_Number extends UR_Form_Field {

	private static $_instance;

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->id                       = 'user_registration_number';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Number', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-number',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Number', 'user-registration' ),
			'default_field_name' => 'number_box_' . ur_get_random_number(),
		);
	}

	public function get_registered_admin_fields() {

		return '<li id="' . esc_attr( $this->id ) . '_list " class="ur-registered-item draggable" data-field-id="' .esc_attr( $this->id ) . '"><span class="' . esc_attr( $this->registered_fields_config['icon'] ). '"></span>' . esc_html( $this->registered_fields_config['label'] ) . '</li>';
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

		if ( ! is_numeric( $value ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $field_label ) {
					return __( $field_label . ' must be numeric value.', 'user-registration' );
				}
			);
		}
	}
}

return UR_Form_Field_Number::get_instance();
