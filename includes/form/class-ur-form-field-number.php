<?php
/**
 * UR_Form_Field_Number.
 *
 * @class    UR_Form_Field_Number
 * @since    1.0.5
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Number Class
 */
class UR_Form_Field_Number extends UR_Form_Field {

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
		$field_label = isset( $form_data->label ) ? $form_data->label : '';
		$value       = isset( $form_data->value ) ? $form_data->value : '';

		if ( ! is_numeric( $value ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $field_label ) {
					/* translators: %1$s - Field Label */
					return sprintf( __( '%1$s must be numeric value.', 'user-registration' ), $field_label );
				}
			);
		}
	}
}

return UR_Form_Field_Number::get_instance();
