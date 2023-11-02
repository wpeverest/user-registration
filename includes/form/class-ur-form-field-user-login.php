<?php
/**
 * UR_Form_Field_User_Login.
 *
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_User_Login Class
 */
class UR_Form_Field_User_Login extends UR_Form_Field {

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

		$this->id                       = 'user_registration_user_login';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Username', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-user',
		);

		$this->field_defaults = array(
			'default_label' => __( 'Username', 'user-registration' ),
		);
	}

	/**
	 * Html to display in builder fields list.
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
		$username = isset( $form_data->value ) ? $form_data->value : '';
		$label    = $single_form_field->general_setting->field_name;
		if ( username_exists( $username ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $label ) {
					$message = array(
						/* translators: %s - validation message */
						$label       => sprintf( __( 'Username already exists.', 'user-registration' ) ),
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

		if ( empty( $username ) ) {
			$status = true;
		} else {
			$status = validate_username( $username );
		}

		if ( ! $status ) {
			add_filter(
				$filter_hook,
				function ( $msg ) {
					$invalid_msg = get_option( 'user_registration_form_submission_error_message_disallow_username_character', esc_html__( 'Please enter a valid username.', 'user-registration' ) );

					if ( empty( $invalid_msg ) ) {
						$invalid_msg = esc_html__( 'Please enter a valid username.', 'user-registration' );
					}

					return $invalid_msg;
				}
			);
		}
	}
}

return UR_Form_Field_User_Login::get_instance();
