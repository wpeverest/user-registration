<?php
/**
 * UR_Form_Field_Date.
 *
 * @class    UR_Form_Field_Date
 * @since    1.0.5
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Date Class
 */
class UR_Form_Field_Date extends UR_Form_Field {

	private static $_instance;

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->id                       = 'user_registration_date';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Date', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-calendar',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Date', 'user-registration' ),
			'default_field_name' => 'date_box_' . ur_get_random_number(),
		);
	}

	public function get_registered_admin_fields() {

		return '<li id="' . esc_attr( $this->id ) . '_list " class="ur-registered-item draggable" data-field-id="' .esc_attr( $this->id ) . '"><span class="' . esc_attr( $this->registered_fields_config['icon'] ). '"></span>' . esc_html( $this->registered_fields_config['label'] ) . '</li>';
	}

	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {

		$is_enable_date_range = isset( $single_form_field->advance_setting->enable_date_range ) ? $single_form_field->advance_setting->enable_date_range : '';
		$required             = isset( $single_form_field->general_setting->required ) ? $single_form_field->general_setting->required : 'no';
		$field_label          = isset( $form_data->label ) ? $form_data->label : '';
		$value                = isset( $form_data->value ) ? $form_data->value : '';
		$urcl_hide_fields = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array();
		$field_name       = isset( $single_form_field->general_setting->field_name ) ? $single_form_field->general_setting->field_name : '';

		if ( ! in_array( $field_name, $urcl_hide_fields, true ) && 'yes' == $required && empty( $value ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $field_label ) {
					return esc_html__( $field_label . ' is required.', 'user-registration' );
				}
			);
		}
	}

	/**
	 * Checks for valid date
	 *
	 * @param string $date_string
	 */
	private function is_valid_date( $date_string ) {

		$date = date_parse( $date_string );

		if ( $date['error_count'] == 0 && checkdate( $date['month'], $date['day'], $date['year'] ) ) {
			return true;
		}

		return false;
	}
}

return UR_Form_Field_Date::get_instance();
