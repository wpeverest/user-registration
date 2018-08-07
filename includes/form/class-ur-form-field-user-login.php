<?php
/**
 * UR_Form_Field_User_Login.
 *
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Form_Field_User_Login Class
 */
class UR_Form_Field_User_Login extends UR_Form_Field {

	private static $_instance;

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->id = 'user_registration_user_login';
		$this->form_id = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Username','user-registration' ),
			'icon' => 'dashicons dashicons-id',
		);

		$this->field_defaults = array(
			'default_label' => __( 'Username','user-registration' ),
		);
	}

	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list " class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		$username = isset( $form_data->value ) ? $form_data->value : '';

		if ( username_exists( $username ) ) {
			add_filter( $filter_hook, function ( $msg ) {
				return __( 'Username already exists.', 'user-registration' );
			} );
		}

		$status = validate_username( $username );

		if ( ! $status ) {
			add_filter( $filter_hook, function ( $msg ) {
				return __( 'Invalid username ! ', 'user-registration' );
			});
		}
	}
}

return UR_Form_Field_User_Login::get_instance();
