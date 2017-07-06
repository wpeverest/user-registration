<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_User_Password
 * @version  1.0.0
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_User_Password Class
 */
class UR_User_Password extends UR_Form_Field {

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

		$this->id = 'user_registration_user_password';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'Password ', 'user-registration' ),

			'icon' => 'dashicons dashicons-lock',
		);
		$this->field_defaults           = array(

			'default_label' => __( 'User Password', 'user-registration' ),
		);
	}


	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	public function validation( $single_form_field, $form_data, $filter_hook ) {

		$password = isset( $form_data->value ) ? $form_data->value : '';

		if (  empty( $password ) ) {

			add_filter( $filter_hook, function ( $msg ) {

				return __( 'Empty password.', 'user-registration' );

			} );
		}

		if ( get_option( 'user_registration_general_setting_enable_strong_password' ) == 'yes' ) {

			if ( self::check_password_length( $password ) < 2 ) {

				add_filter( $filter_hook, function ( $msg ) {

					return __( 'Weak password, please try again.', 'user-registration' );

				} );

			}
		}

	}

	// 1 - weak
	// 2 - not weak
	// 3 - acceptable
	// 4 - strong
	private static function check_password_length( $password ) {

		$strength = 0;
		$patterns = array( '#[a-z]#', '#[A-Z]#', '#[0-9]#', '/[¬!"£$%^&*()`{}\[\]:@~;\'#<>?,.\/\\-=_+\|]/' );
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $password, $matches ) ) {
				$strength ++;
			}
		}

		return $strength;

	}
}

return UR_User_Password::get_instance();
