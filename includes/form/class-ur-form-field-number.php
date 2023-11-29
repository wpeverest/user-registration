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
		$label = $single_form_field->general_setting->field_name;
		$value = isset( $form_data->value ) ? $form_data->value : '';

		if ( isset( $single_form_field->advance_setting->max ) && '' !== $single_form_field->advance_setting->max ) {
			$max_value = $single_form_field->advance_setting->max;
			if ( floatval( $value ) > floatval( $max_value ) ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $max_value, $label ) {
						$message = array(
							/* translators: %s - validation message */
							$label       => sprintf( __( 'Please enter a value less than %d', 'user-registration' ), $max_value ),
							'individual' => true,
						);
						wp_send_json_error(
							array(
								'message' => $message,
							)
						);
					}
				);
			}
		}

		if ( isset( $single_form_field->advance_setting->min ) && '' !== $single_form_field->advance_setting->min ) {
			$min_value = $single_form_field->advance_setting->min;
			if ( floatval( $value ) < floatval( $min_value ) ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $min_value, $label ) {
						return sprintf(
							'Please enter a value greater than %d for %s',
							$min_value,
							"<strong>$label</strong>."
						);
					}
				);
			}
		}
	}
}

return UR_Form_Field_Number::get_instance();
