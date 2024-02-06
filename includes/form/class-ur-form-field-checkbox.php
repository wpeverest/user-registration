<?php
/**
 * UR_Form_Field_Checkbox.
 *
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Admin Class
 */
class UR_Form_Field_Checkbox extends UR_Form_Field {

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

		$this->id                       = 'user_registration_checkbox';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => esc_html__( 'Checkbox', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-input-checkbox',
		);

		$this->field_defaults = array(
			'default_label'         => esc_html__( 'Checkbox', 'user-registration' ),
			'default_field_name'    => 'check_box_' . ur_get_random_number(),
			'default_options'       => array(
				esc_html__( 'First Choice', 'user-registration' ),
				esc_html__( 'Second Choice', 'user-registration' ),
				esc_html__( 'Third Choice', 'user-registration' ),
			),
			'default_image_options' => array(
				array(
					'label' => __( 'First Choice', 'user-registration' ),
					'image' => '',
				),
				array(
					'label' => __( 'Second Choice', 'user-registration' ),
					'image' => '',
				),
				array(
					'label' => __( 'Third Choice', 'user-registration' ),
					'image' => '',
				),
			),
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
		// Custom Field Validation here..

		$field_label = $single_form_field->general_setting->field_name;
		$value       = $form_data->value;

		if ( ! empty( $single_form_field->advance_setting->choice_limit ) ) {

			$checked_count = is_array( $value ) ? count( $value ) : ( ( json_decode( $value ) && ! is_null( $value ) ) ? count( json_decode( $value ) ) : 0 );
			$limit         = $single_form_field->advance_setting->choice_limit;

			if ( $checked_count > $limit ) {
				$message = array(
					/* translators: %s - validation message */
					$field_label => sprintf( __( 'Only %d options can be selected.', 'user-registration' ), $limit ),
					'individual' => true,
				);
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $field_label, $message ) {
						if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
							return sprintf( $message[ $field_label ] );
						} else {
							wp_send_json_error(
								array(
									'message' => $message,
								)
							);
						}
					}
				);
			}
		}
	}
}

return UR_Form_Field_Checkbox::get_instance();
