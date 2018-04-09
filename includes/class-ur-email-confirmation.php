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

		if( 'email_confirmation' !== get_option( 'user_registration_general_setting_login_options' ) ) {
			return;
		}

		add_filter( 'wp_authenticate_user', array( $this, 'check_email_status' ),10,2);
		add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ), 10, 2 );
		add_action( 'user_registration_after_register_user_action', array( $this, 'set_email_status' ), 9, 3 );
		add_action( 'wp', array( $this, 'check_token_before_authenticate' ), 30, 2);
		add_action( 'wp_authenticate', array($this, 'check_token_before_authenticate'), 40, 2);

	}

	public function ur_enqueue_script()
	{
		wp_register_style( 'user-registration-css', UR()->plugin_url().'/assets/css/user-registration.css', array(), UR_VERSION ); 
		wp_enqueue_style('user-registration-css');
	}

	public function custom_registration_message()
	{
		return ur_print_notice( __('User successfully registered. Login to continue.','user-registration'));
	}

	public function custom_registration_error_message()
	{
		return ur_print_notice( __('Token Mismatch!','user-registration'), 'error' );
	}

	public function check_token_before_authenticate()
	{	
		if( ! isset( $_GET['ur_token'] ) ) {
			return;
		}
		else
		{		
			$output = str_split( $_GET['ur_token'], 50 );

			$user_id = $this->my_simple_crypt( $output[1], 'd');
			
			$user_token = get_user_meta( $user_id, 'ur_confirm_email_token', true );
			add_action( 'login_enqueue_scripts', array( $this, 'ur_enqueue_script' ), 1 );
			
			if( $user_token == $_GET['ur_token'] )
			{				
				update_user_meta( $user_id, 'ur_confirm_email', 1 );
				delete_user_meta( $user_id, 'ur_confirm_email_token');
				add_filter('login_message', array( $this,'custom_registration_message' ) );
				add_filter('user_registration_login_form_before_notice', array( $this,'custom_registration_message' ) );
			}
			else
			{
				add_filter('login_message', array( $this,'custom_registration_error_message' ) );
				add_filter('user_registration_login_form_before_notice', array( $this,'custom_registration_error_message' ) );
				;
			}
		}

		do_action('user_registration_check_token_complete');

	}

	public function my_simple_crypt( $string, $action = 'e' ) {
	    
	    $secret_key = 'my_simple_secret_key';
	    $secret_iv = 'my_simple_secret_iv';
	 
	    $output = false;
	    $encrypt_method = "AES-256-CBC";
	    $key = hash( 'sha256', $secret_key );
	    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
	 
	    if( $action == 'e' ) {
	        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
	    }
	    else if( $action == 'd' ){
	        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
	    }
	 
	    return $output;
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

	    $token .=$this->my_simple_crypt($user_id,'e');

	    return $token;

	    do_action('user_registration_get_token');
	}

	public function set_email_status( $valid_form_data, $form_id, $user_id ) {
		
		if( 'email_confirmation' === get_option( 'user_registration_general_setting_login_options' ) ) {
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

			$error_message = __( 'Email not verified!', 'user-registration' );
			$result = new WP_Error( 'user_email_not_verified', $error_message );
		}

		return $result;
	}
}

new UR_Email_Confirmation();