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

		$redirect_url = isset( $atts['redirect_url'] ) ? $atts['redirect_url'] : '';
		$enable_recaptcha = isset( $atts['enable_recaptcha'] ) ? $atts['enable_recaptcha'] : '';

		if ( ! is_user_logged_in() ) {
			if ( isset( $wp->query_vars['lost-password'] ) ) {
				UR_Shortcode_My_Account::lost_password();
			} else {
				ur_get_template( 'myaccount/form-login.php', set_query_var( "recaptcha", $enable_recaptcha )
				);
			}
		}
		else {
			echo sprintf( __('You are already logged in. <a href="%s">Log out?</a>', 'user-registration' ),  ur_logout_url() ) ;
		}
	}

}
