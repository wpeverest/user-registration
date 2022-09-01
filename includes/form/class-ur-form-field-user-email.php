<?php
/**
 * UR_Form_Field_User_Email.
 *
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_User_Email Class
 */
class UR_Form_Field_User_Email extends UR_Form_Field {

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

		$this->id                       = 'user_registration_user_email';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Email ', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-email',
		);

		$this->field_defaults = array(
			'default_label' => __( 'User Email', 'user-registration' ),
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

		$email  = isset( $form_data->value ) ? $form_data->value : '';
		$status = is_email( $email );

		if ( ! $status ) {
			add_filter(
				$filter_hook,
				function ( $msg ) {
					return __( 'Invalid email address.', 'user-registration' );
				}
			);
		}

		if ( email_exists( $email ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) {
					return __( 'Email already exists.', 'user-registration' );
				}
			);
		}
	}
}

return UR_Form_Field_User_Email::get_instance();
