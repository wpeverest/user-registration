<?php
/**
 * UR_Form_Field_Radio.
 *
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Radio Class
 */
class UR_Form_Field_Radio extends UR_Form_Field {

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

		$this->id                       = 'user_registration_radio';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Radio', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-radio',
		);

		$this->field_defaults = array(
			'default_label'         => __( 'Radio', 'user-registration' ),
			'default_field_name'    => 'radio_' . ur_get_random_number(),
			'default_options'       => array(
				__( 'First Choice', 'user-registration' ),
				__( 'Second Choice', 'user-registration' ),
				__( 'Third Choice', 'user-registration' ),
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

		$value   = isset( $form_data->value ) ? $form_data->value : '';
		$label   = $single_form_field->general_setting->field_name;
		$options = $single_form_field->general_setting->options;

		if ( ! empty( $value ) && ! in_array( $value, $options, true ) ) {
			$message = array(
				/* translators: %s - validation message */
				$label       => sprintf( __( 'Please choose a valid option', 'user-registration' ) ),
				'individual' => true,
			);
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $label, $message ) {
					if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
						return sprintf( $message[ $label ] );
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

return UR_Form_Field_Radio::get_instance();
