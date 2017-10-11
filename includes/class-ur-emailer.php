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

		add_action( 'user_registration_after_register_user_action', array(
			__CLASS__,
			'ur_after_register_mail'
		), 10, 3 );

	}

	/**
	 * @param $valid_form_data
	 * @param $form_id
	 * @param $user_id
	 */
	public static function ur_after_register_mail( $valid_form_data, $form_id, $user_id ) {

		$email_object = isset( $valid_form_data['user_email'] ) ? $valid_form_data['user_email'] : array();

		$user_username_object = isset( $valid_form_data['user_username'] ) ? $valid_form_data['user_username'] : array();

		$email = isset( $email_object->value ) && ! empty( $email_object->value ) ? $email_object->value : '';

		$username = isset( $user_username_object->value ) && ! empty( $user_username_object->value ) ? $user_username_object->value : '';

		if ( ! empty( $email ) && ! empty ( $username ) && ! empty( $user_id ) ) {

			self::send_mail_to_user( $email, $username, $user_id );

			self::send_mail_to_admin( $email, $username, $user_id );
		}
	}


	/**
	 * @param $email
	 */
	private static function send_mail_to_user( $email, $username, $user_id ) {

		$status = ur_get_user_approval_status( $user_id );

		$blog_info = get_bloginfo();

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		if ( $status == 0 ) {

			$subject = __( sprintf( 'Thank you for Registration on %s', $blog_info ), 'user-registration' );

			$message = apply_filters( 'user_registration_user_email_message', __( sprintf(

				'Hi %s,
 					<br/>
 					You have registered on <a href="%s">%s</a>.
 					<br/>
 					Please wait untill the site admin approves your registration.
 					<br/>
 					You will be notified after it is approved.
 					<br/>
 					<br/>
 					Thank you :) ',
				$username, get_home_url(), $blog_info, get_home_url(), $blog_info ), 'user-registration' ) );


		} else if ( $status == - 1 ) {

			$subject = __( sprintf( 'Thank you for Registration on %s', $blog_info ), 'user-registration' );

			$message = apply_filters( 'user_registration_user_email_message', __( sprintf(

				'Hi %s,
 					<br/>
 					You have registered on <a href="%s">%s</a>.
 					<br/>
 					Unfortunately your registration is denied.
 					<br/>
 					Sorry for the inconvenience.
 					<br/>
 					<br/>
 					Thank you :) ',
				$username, get_home_url(), $blog_info, get_home_url(), $blog_info ), 'user-registration' ) );

		} else {
			$subject = __( sprintf( 'Congratulations! Registration Complete on %s', $blog_info ), 'user-registration' );

			$message = apply_filters( 'user_registration_user_email_message', __( sprintf(

				'Hi %s,
 					<br/>
 					You have successfully completed user registration on <a href="%s">%s</a>.
 					<br/>
 					Please visit \'<b>My Account</b>\' page to edit your account details and create your user profile on <a href="%s">%s</a>.',
				$username, get_home_url(), $blog_info, get_home_url(), $blog_info ), 'user-registration' ) );

		}
		wp_mail( $email, $subject, $message, $headers );

	}

	/**
	 * @param $user_email
	 */
	private static function send_mail_to_admin( $user_email, $username, $user_id ) {

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$admin_email = get_option( 'admin_email' );

		$blog_info = get_bloginfo();

		$subject = __( 'A New User Registered', 'user-registration' );

		$message = apply_filters( 'user_registration_admin_email_message', __( sprintf(

			'Hi Admin,
					<br/>
					A new user (%s - %s) has successfully registered to your site <a href="%s">%s</a>.
					<br/>
					Please review the user role and details at \'<b>Users</b>\' menu in your WP dashboard.<br/>
					Thank you!', $username, $user_email, get_home_url(), $blog_info ), 'user-registration' ) );

		wp_mail( $admin_email, $subject, $message, $headers );

	}

	/**
	 * @param $email
	 * @param $username
	 * @param $status
	 */
	public static function status_change_email( $email, $username, $status ) {

		$blog_info = get_bloginfo();

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		if ( $status == 0 ) {

			$subject = __( sprintf( 'Sorry! Registration changed to pending on %s', $blog_info ), 'user-registration' );

			$message = apply_filters( 'user_registration_user_status_change_email_message', __( sprintf(

				'Hi %s,
 					<br/>
 					Your registration on <a href="%s">%s</a> has been changed to pending.
 					<br/>
 					Sorry for the inconvenience.
 					<br/>
 					You will be notified after it is approved.
 					<br/>
 					<br/>
 					Thank you :)',
				$username, get_home_url(), $blog_info, get_home_url(), $blog_info ), 'user-registration' ) );


		} else if ( $status == - 1 ) {

			$subject = __( sprintf( 'Sorry! Registration denied on %s', $blog_info ), 'user-registration' );

			$message = apply_filters( 'user_registration_user_status_change_email_message', __( sprintf(

				'Hi %s,
 					<br/>
 					Your registration on <a href="%s">%s</a> has been denied.
 					<br/>
 					Sorry for the inconvenience.
 					<br/>
 					<br/>
 					Thank you :) ',
				$username, get_home_url(), $blog_info, get_home_url(), $blog_info ), 'user-registration' ) );

		} else {
			$subject = __( sprintf( 'Congratulations! Registration approved on %s', $blog_info ), 'user-registration' );

			$message = apply_filters( 'user_registration_user_email_message', __( sprintf(

				'Hi %s,
 					<br/>
 					Your registration on <a href="%s">%s</a>  has been approved.
 					<br/>
 					Please visit \'<b>My Account</b>\' page to edit your account details and create your user profile on <a href="%s">%s</a>.',
				$username, get_home_url(), $blog_info, get_home_url(), $blog_info ), 'user-registration' ) );

		}
		wp_mail( $email, $subject, $message, $headers );


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
		wp_mail( $user_data->user_email, $subject, $message, $headers);

	}

}

UR_Emailer::init();
