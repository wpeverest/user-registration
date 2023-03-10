<?php
/**
 * UR_Form_Field_Password.
 *
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Password Class
 */
class UR_Form_Field_Password extends UR_Form_Field {

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

		$this->id                       = 'user_registration_password';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Password Field', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-input-password',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Password Field', 'user-registration' ),
			'default_field_name' => 'password_' . ur_get_random_number(),
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
		$value = isset( $form_data->value ) ? $form_data->value : '';
		$label = $single_form_field->general_setting->label;

		// Validate size.
		if ( isset( $single_form_field->advance_setting->size ) ) {
			$max_size = $single_form_field->advance_setting->size;
			if ( is_wp_error( UR_Validation::validate_length( $value, $max_size ) ) ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $max_size, $label ) {
						return sprintf(
							'Please enter a password of length less than %d for %s',
							$max_size,
							"<strong>$label</strong>."
						);
					}
				);
			}
		}
	}
}

return UR_Form_Field_Password::get_instance();
