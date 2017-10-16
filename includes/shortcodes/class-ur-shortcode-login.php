<?php
/**
 * Login Shortcodes
 *
 * Show the login form 
 *
 * @class    UR_Shortcode_Login
 * @version  1.0.0
 * @package  UserRegistration/Shortcodes/Login
 * @category Shortcodes
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Shortcode_Login Class.
 */
class UR_Shortcode_Login {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function get( $atts ) {

		return UR_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @param array $atts
	 */

	public static function output( $atts ) {
		global $wp, $post;

		if ( ! is_user_logged_in() ) {
			
			if ( isset( $wp->query_vars['lost-password'] ) ) {
				UR_Shortcode_My_Account::lost_password();
			} else {
				ur_get_template( 'myaccount/form-login.php' );
			}
		}
		else
		{		
			echo __( sprintf( 'You are already logged in. <a href="%s">%s</a>', ur_logout_url() ,'Logout' ), 'user-registration' );	
		}		
	}

}
