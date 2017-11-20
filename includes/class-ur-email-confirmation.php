<?php
/**
 * User Registration Email Confirmation.
 *
 * @class    UR_Email_Confirmation
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UR_Email_Confirmation
 */

class UR_Email_Confirmation {
	
	public function __construct() {

		add_filter( 'wp_authenticate_user', array( $this, 'check_email_status' ),10,2);
		add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ), 10, 2 );
		add_action( 'user_register', array( $this, 'set_email_status' ) );
	
	}

	public function set_email_status( $user_id ) {
	
	}

	public function check_email_status( WP_User $user )
	{
		$email_status = get_user_meta($user->ID, 'ur_confirm_email', true);

		do_action( 'ur_user_before_check_email_status_on_login', $email_status, $user );

		if( $email_status === '0' )
		{
			$message = '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong> ' . __( 'Your account is still pending approval. Verifiy your email by clicking on the link sent to your email.', 'user-registration' );

			return new WP_Error( 'user_email_not_verified', $message );
		}

		return $user;
	}

	/**
	 * If the user is not approved, disalow to reset the password fom Lost Passwod form and display an error message
	 *
	 * @param $result
	 * @param $user_id
	 *
	 * @return \WP_Error
	 */
	
	public function allow_password_reset( $result, $user_id ) {
	
		$email_status = get_user_meta($user_id, 'ur_confirm_email', true);

		if ( $email_status === '0' ) {

			$error_message = 'Email not verified!';
			$result = new WP_Error( 'user_email_not_verified', $error_message );
		}

		return $result;
	}
}

new UR_Email_Confirmation();