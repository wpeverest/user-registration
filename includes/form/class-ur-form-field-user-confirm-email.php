<?php
/**
 * UR_Form_Field_User_Confirm_Email.
 *
 * @package  UserRegistration/Form
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_User_Confirm_Email Class
 */
class UR_Form_Field_User_Confirm_Email extends UR_Form_Field {

	/**
	 * Instance of UR_Form_Field_User_Confirm_Email class.
	 *
	 * @var obj
	 */
	private static $_instance;

	/**
	 * Get instance of UR_Form_Field_User_Confirm_Email class.
	 *
	 * @return obj
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * UR_Form_Field_User_Confirm_Email Construct.
	 */
	public function __construct() {

		$this->id                       = 'user_registration_user_confirm_email';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Confirm Email ', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-email-confirm',
		);

		$this->field_defaults = array(
			'default_label' => __( 'Confirm Email', 'user-registration' ),
		);
	}

	/**
	 * Html to display in builder fields list.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "  class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
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
	}
}

return UR_Form_Field_User_Confirm_Email::get_instance();
