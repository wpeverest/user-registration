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

		add_action( 'user_registration_after_register_user_action', array( __CLASS__,'ur_after_register_mail' ), 10, 3 );

	}

	/**
	 * @param $valid_form_data
	 * @param $form_id
	 * @param $user_id
	 */
	public static function ur_after_register_mail( $valid_form_data, $form_id, $user_id ) {

		$email_object = isset( $valid_form_data['user_email'] ) ? $valid_form_data['user_email'] : array();

		$email = isset( $email_object->value ) && ! empty( $email_object->value ) ? $email_object->value : '';

		if ( ! empty( $email ) ) {

			self::send_mail_to_user( $email );

			self::send_mail_to_admin( $email );
		}
	}

	/**
	 * @param $email
	 */
	private static function send_mail_to_user( $email ) {

		$blog_info = get_bloginfo();

		$headers = array('Content-Type: text/html; charset=UTF-8');

		$subject = __( sprintf( 'You have successfully registered on %s', $blog_info ), 'user-registration' );

		$message = apply_filters( 'user_registration_user_email_message', __( sprintf( 'Thank you for registration on <a href="%s">%s</a>', get_home_url(), $blog_info ), 'user-registration' ) );

		wp_mail( $email, $subject, $message, $headers );

	}

	/**
	 * @param $user_email
	 */
	private static function send_mail_to_admin( $user_email ) {

		$headers = array('Content-Type: text/html; charset=UTF-8');

		$admin_email = get_option( 'admin_email' );

		$blog_info = get_bloginfo();

		$subject = __( sprintf( 'New user register on %s', $blog_info ), 'user-registration' );

		$message = apply_filters('user_registration_admin_email_message', __( sprintf( 'New user registered on <a href="%s">%s</a>\nEmail:%s', get_home_url(), $blog_info, $admin_email ), 'user-registration' ) );

		wp_mail( $admin_email, $subject, $message, $headers );

	}

}

UR_Emailer::init();
