<?php
/**
 * Emailer class
 *
 * @class    UR_Emailer
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Emailer Class.
 */
class UR_Emailer {

	/** @public array Query vars to add to wp */
	public $query_vars = array();

	/**
	 * Init function.
	 */
	public static function init() {

		if('yes' == get_option('user_registration_email_setting_disable_email')){
			return;
		}

		add_filter( 'wp_mail_from', array( __CLASS__, 'ur_sender_email' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'ur_sender_name' ) );

		add_action( 'user_registration_after_register_user_action', array(
			__CLASS__,
			'ur_after_register_mail'
		), 10, 3 );
	}

	public static function ur_sender_email() {
		$sender_email = get_option( 'user_registration_email_from_address', get_option( 'admin_email' ) );
		return $sender_email;
	}

	public static function ur_sender_name() {
		$sender_name = get_option( 'user_registration_email_from_name', esc_attr( get_bloginfo( 'name', 'display' ) ) );
		return $sender_name;
	}

	public static function ur_get_header() {
		$header = "From: ". self::ur_sender_name()." <".self::ur_sender_email().">\r\n";
		$header .= "Reply-To: ".self::ur_sender_email()."\r\n";
		$header .= "Content-Type: text/html\r\n; charset=UTF-8";

		return $header; 
	}

	/**
	 * @param $valid_form_data
	 * @param $form_id
	 * @param $user_id
	 */
	public static function ur_after_register_mail( $valid_form_data, $form_id, $user_id ) {

		$data_html = '';
		$valid_form_data = isset( $valid_form_data ) ? $valid_form_data : '';

		foreach( $valid_form_data as $field_meta => $form_data ) {
			if( $field_meta === 'user_confirm_password' ) {
				continue;
			}

			if( isset( $field_meta->extra_params['field_key'] ) && $field_meta->extra_params['field_key'] === 'privacy_policy') {
				continue;
			}

			$label = isset( $form_data->extra_params['label'] ) ? $form_data->extra_params['label'] : '';
			$value = isset( $form_data->value ) ? $form_data->value : '';

			if( $field_meta === 'user_password') {
				$value = __('Chosen Password', 'user-registration'); 
			}


			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}

			$data_html .= $label . ' : ' . $value . '<br/>';
		}

		$email_object = isset( $valid_form_data['user_email'] ) ? $valid_form_data['user_email'] : array();

		$user_username_object = isset( $valid_form_data['user_username'] ) ? $valid_form_data['user_username'] : array();

		$email = isset( $email_object->value ) && ! empty( $email_object->value ) ? $email_object->value : '';

		$username = isset( $user_username_object->value ) && ! empty( $user_username_object->value ) ? $user_username_object->value : '';

		if ( ! empty( $email ) && ! empty ( $username ) && ! empty( $user_id ) ) {

			self::send_mail_to_user( $email, $username, $user_id, $data_html );

			self::send_mail_to_admin( $email, $username, $user_id, $data_html );
		}
	}


	/**
	 * @param $email
	 */

	private static function send_mail_to_user( $email, $username, $user_id, $data_html ) {

		$status = ur_get_user_approval_status( $user_id );

		$email_status = get_user_meta($user_id, 'ur_confirm_email', true);

		$email_token = get_user_meta($user_id, 'ur_confirm_email_token', true);

		$to_replace = array("{{username}}", "{{email}}", "{{blog_info}}", "{{home_url}}", "{{email_token}}", "{{all_fields}}");

		$replace_with = array( $username, $email, get_bloginfo(), get_home_url(), $email_token, $data_html );

		if( $email_status === '0' ) {

			$subject = get_option('user_registration_email_confirmation_subject', __('Please confirm your registration on {{blog_info}}', 'user-registration') );

			$message = new UR_Settings_Email_Confirmation();

			$message = $message->ur_get_email_confirmation();

			$message = get_option( 'user_registration_email_confirmation', $message );

			$message = str_replace( $to_replace, $replace_with, $message );

			$subject = str_replace( $to_replace, $replace_with, $subject );

			wp_mail( $email, $subject, $message, self::ur_get_header() );

		}

		else if ( $status == 0 ) {

			$subject = get_option( 'user_registration_awaiting_admin_approval_email_subject', __('Thank you for registration on {{blog_info}}', 'user-registration') );

			$message = new UR_Settings_Awaiting_Admin_Approval_Email();

			$message = $message->ur_get_awaiting_admin_approval_email();

			$message = get_option( 'user_registration_awaiting_admin_approval_email', $message );

			$message = str_replace( $to_replace, $replace_with, $message );

			$subject = str_replace( $to_replace, $replace_with, $subject );

			if ( 'yes' == get_option( 'user_registration_enable_awaiting_admin_approval_email', 'yes' ) ){
				wp_mail( $email, $subject, $message, self::ur_get_header() );			
			}


		} else if ( $status == - 1 ) {

			$subject = get_option( 'user_registration_registration_denied_email_subject', __('Sorry! Registration denied on {{blog_info}}', 'user-registration') );

			$message = new UR_Settings_Registration_Denied_Email();

			$message = $message->ur_get_registration_denied_email();

			$message = get_option( 'user_registration_registration_denied_email', $message );

			$message = str_replace( $to_replace, $replace_with, $message );

			$subject = str_replace( $to_replace, $replace_with, $subject );

			if ( 'yes' == get_option( 'user_registration_enable_registration_denied_email', 'yes' ) ){
				wp_mail( $email, $subject, $message, self::ur_get_header() );			
			}

		} else {
			$subject = get_option( 'user_registration_successfully_registered_email_subject',__('Congratulations! Registration Complete on {{blog_info}}', 'user-registration') );

			$message = new UR_Settings_Successfully_Registered_Email();

			$message = $message->ur_get_successfully_registered_email();

			$message = get_option( 'user_registration_successfully_registered_email', $message );

			$message = str_replace( $to_replace, $replace_with, $message );

			$subject = str_replace( $to_replace, $replace_with, $subject );

			if ( 'yes' == get_option( 'user_registration_enable_successfully_registered_email', 'yes' ) ){
				wp_mail( $email, $subject, $message, self::ur_get_header()  );			
			}
		}
	}

	/**
	 * @param $user_email
	 */
	private static function send_mail_to_admin( $user_email, $username, $user_id, $data_html ) {

		$header = "Reply-To: {{email}} \r\n";
		
		$header .= "Content-Type: text/html; charset=UTF-8";

		$admin_email = get_option( 'admin_email' );

		$subject = get_option( 'user_registration_admin_email_subject', __('A New User Registered', 'user-registration') );

		$message = new UR_Settings_Admin_Email();

		$message = $message->ur_get_admin_email();

		$message = get_option( 'user_registration_admin_email', $message );

		$to_replace = array("{{username}}", "{{email}}", "{{blog_info}}", "{{home_url}}", "{{all_fields}}");

		$replace_with = array( $username, $user_email, get_bloginfo(), get_home_url(), $data_html );

		$message = str_replace( $to_replace, $replace_with, $message );

		$subject = str_replace( $to_replace, $replace_with, $subject );
		
		$header = str_replace( $to_replace, $replace_with, $header );

		if ( 'yes' == get_option(' user_registration_enable_admin_email ', 'yes') ){
			wp_mail( $admin_email, $subject, $message, $header );
		}
	}

	/**
	 * @param $email
	 * @param $username
	 * @param $status
	 */
	public static function status_change_email( $email, $username, $status ) {

		$to_replace = array( "{{username}}", "{{email}}", "{{blog_info}}", "{{home_url}}" );

		$replace_with = array( $username, $email, get_bloginfo(), get_home_url() );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		if ( $status == 0 ) {

			$subject = get_option( 'user_registration_registration_pending_email_subject', __('Sorry! Registration changed to pending on {{blog_info}}', 'user-registration') );

			$message = new UR_Settings_Registration_Pending_Email();

			$message = $message->ur_get_registration_pending_email();

			$message = get_option( 'user_registration_registration_pending_email', $message );

			$message = str_replace( $to_replace, $replace_with, $message );

			$subject = str_replace( $to_replace, $replace_with, $subject );

			if ( 'yes' == get_option( 'user_registration_enable_registration_pending_email', 'yes' ) ){
				wp_mail( $email, $subject, $message, self::ur_get_header()  );			
			}

		} else if ( $status == - 1 ) {

			$subject = get_option( 'user_registration_registration_denied_email_subject', __('Sorry! Registration denied on {{blog_info}}', 'user-registration') );

			$message = new UR_Settings_Registration_Denied_Email();

			$message = $message->ur_get_registration_denied_email();

			$message = get_option( 'user_registration_registration_denied_email', $message );

			$message = str_replace( $to_replace, $replace_with, $message );

			$subject = str_replace( $to_replace, $replace_with, $subject );

			if ( 'yes' == get_option( 'user_registration_enable_registration_denied_email', 'yes' ) ){
				wp_mail( $email, $subject, $message, self::ur_get_header() );			
			}

		} else {
			$subject = get_option( 'user_registration_registration_approved_email_subject',  __('Congratulations! Registration approved on {{blog_info}}', 'user-registration') );
			
			$message = new UR_Settings_Registration_Approved_Email();

			$message = $message->ur_get_registration_approved_email();

			$message = get_option( 'user_registration_registration_approved_email', $message );

			$message = str_replace( $to_replace, $replace_with, $message );

			$subject = str_replace( $to_replace, $replace_with, $subject );

			if ( 'yes' == get_option( 'user_registration_enable_registration_approved_email', 'yes' ) ){
				wp_mail( $email, $subject, $message, self::ur_get_header() );			
			}


		}
	}

	/**
	 * @param $user_login
	 * @param $user_data
	 * @param $key
	 */
	public static function lost_password_email($user_login,$user_data,$key)
	{
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$blog_info = get_bloginfo();
		$subject = apply_filters( 'retrieve_password_title', __( sprintf( 'Password Reset Email %s', $blog_info ), 'user-registration' ), $user_login, $user_data );
		$message = __('Someone has requested a password reset for the following account:','user-registration') . "<br/>";
		$message .= network_home_url( '/' ) . "<br/>";
		$message .= __(sprintf('Username: %s', $user_login),'user-registration') . "<br/>";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.','user-registration') . "<br/>";
		$message .= __('To reset your password, visit the following address:','user-registration') . "<br/>";
		$redirectUrl=network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
		$message .= __( sprintf( '<a href="%s">%s</a>', $redirectUrl ,$redirectUrl ), 'user-registration' );
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		if(wp_mail( $user_data->user_email, $subject, $message, $headers))
		{
			return true;
		}
		return false;
	}

}

UR_Emailer::init();
