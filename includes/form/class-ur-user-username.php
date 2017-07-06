<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_User_Username
 * @version  1.0.0
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_User_Username Class
 */
class UR_User_Username extends UR_Form_Field {

	private static $_instance;


	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->id = 'user_registration_user_username';

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

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	public function validation( $single_form_field, $form_data, $filter_hook ) {

		$username = isset( $form_data->value ) ? $form_data->value : '';

		$status = validate_username( $username );

		if ( ! $status ) {

			add_filter( $filter_hook, function ( $msg ) {

				return __( 'Invalid username', 'user-registration' );

			} );
		}

		if ( username_exists( $username ) ) {

			add_filter( $filter_hook, function ( $msg ) {

				return __( 'Username already exists.', 'user-registration' );

			} );

		}

	}


}

return UR_User_Username::get_instance();
