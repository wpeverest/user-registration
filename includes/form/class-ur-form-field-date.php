<?php
/**
 * UR_Form_Field_Date.
 *
 * @class    UR_Form_Field_Date
 * @since    1.0.5
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Date Class
 */
class UR_Form_Field_Date extends UR_Form_Field {

	/**
	 * Instance Variable.
	 *
	 * @var [mixed]
	 */
	private static $_instance;

	/**
	 * Get Instance of class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
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

	/**
	 * Get Registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . esc_attr( $this->id ) . '_list " class="ur-registered-item draggable" data-field-id="' . esc_attr( $this->id ) . '"><span class="' . esc_attr( $this->registered_fields_config['icon'] ) . '"></span>' . esc_html( $this->registered_fields_config['label'] ) . '</li>';
	}

	/**
	 * Validate field.
	 *
	 * @param [object] $single_form_field Field Data.
	 * @param [object] $form_data Form Data.
	 * @param [string] $filter_hook Hook.
	 * @param [int]    $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {

		$is_enable_date_range = isset( $single_form_field->advance_setting->enable_date_range ) ? $single_form_field->advance_setting->enable_date_range : '';
		// Perform custom validation for the field here ...
	}

	/**
	 * Checks for valid date
	 *
	 * @param string $date_string Date.
	 */
	private function is_valid_date( $date_string ) {

		$date = date_parse( $date_string );

		if ( 0 == $date['error_count'] && checkdate( $date['month'], $date['day'], $date['year'] ) ) {
			return true;
		}

		return false;
	}
}

return UR_Form_Field_Date::get_instance();
