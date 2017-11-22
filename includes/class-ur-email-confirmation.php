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
		add_action( 'wp_authenticate', array($this, 'check_token_before_authenticate'), 30, 2);
	
	}

	public function check_token_before_authenticate()
	{
		if(!isset($_GET['token']) && !isset($_GET['user_id'])){
			return;
		}
		else
		{		
			$output = str_split($GET['token'], 50);

			$user_id = md5($output);
			
			$user_token = get_user_meta($user_id,'ur_confirm_email_token',true);

			if($user_token == $_GET['token'])
			{
				update_user_meta($user_id,'ur_confirm_email',1);

				echo apply_filters( 'login_message', __('User successfully registered!','user-registration'));
			}
			else
			{
				$message = '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong> ' . __( 'Token Mismatch!', 'user-registration' );

				return new WP_Error( 'user_email_not_verified', $message );
			}
		}

		do_action('user_registration_check_token_complete');

	}

	public function getToken($user_id)
	{
		 $length = 50;
	     $token = "";
	     $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	     $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	     $codeAlphabet.= "0123456789";
	     $max = strlen($codeAlphabet); 

	    for ($i=0; $i < $length; $i++) {
	        $token .= $codeAlphabet[random_int(0, $max-1)];
	    }

	    $token .=md5($user_id);

	    return $token;

	    do_action('user_registration_get_token');
	}

	public function set_email_status( $user_id ) {
		
		if('email_confirmation' === get_option('user_registration_general_setting_login_options'))
		{
			$token = $this->getToken($user_id);
			update_user_meta( $user_id, 'ur_confirm_email', 0);
			update_user_meta( $user_id, 'ur_confirm_email_token', $token);	
		}
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