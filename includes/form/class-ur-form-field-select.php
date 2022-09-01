<?php
/**
 * UR_Form_Field_Selec.
 *
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Select Class
 */
class UR_Form_Field_Select extends UR_Form_Field {

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

		$this->id                       = 'user_registration_select';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Select', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-drop-down',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Select', 'user-registration' ),
			'default_field_name' => 'select_' . ur_get_random_number(),
			'default_options'    => array(
				__( 'First Choice', 'user-registration' ),
				__( 'Second Choice', 'user-registration' ),
				__( 'Third Choice', 'user-registration' ),
			),
		);
	}

	/**
	 * Get Registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list " class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
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
		$required             = isset( $single_form_field->general_setting->required ) ? $single_form_field->general_setting->required : 'no';
		$field_label          = isset( $form_data->label ) ? $form_data->label : '';
		$value                = isset( $form_data->value ) ? $form_data->value : '';
		$urcl_hide_fields = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array(); //phpcs:ignore;
		$field_name       = isset( $single_form_field->general_setting->field_name ) ? $single_form_field->general_setting->field_name : '';

		if ( ! in_array( $field_name, $urcl_hide_fields, true ) && 'yes' == $required && empty( $value ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $field_label ) {
					/* translators: %1$s - Field Label */
					return sprintf( __( '%1$s is required.', 'user-registration' ), $field_label );
				}
			);
		}
	}
}

return UR_Form_Field_Select::get_instance();
