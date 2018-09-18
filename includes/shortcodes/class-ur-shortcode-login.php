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
		wp_enqueue_script( 'user-registration' );

		$redirect_url = isset( $atts['redirect_url']) ? $atts['redirect_url'] : '';
		$recaptcha_enable = 'yes';

		$recaptcha_site_key = get_option( 'user_registration_integration_setting_recaptcha_site_key' );
		$recaptcha_site_secret = get_option( 'user_registration_integration_setting_recaptcha_site_secret' );

		if ( 'yes' == $recaptcha_enable && ! empty( $recaptcha_site_key ) && ! empty( $recaptcha_site_secret ) ) {
			wp_enqueue_script( 'ur-google-recaptcha' );
			wp_localize_script( 'ur-google-recaptcha', 'ur_google_recaptcha_code', array(
				'site_key' => $recaptcha_site_key,
				'site_secret' => $recaptcha_site_secret,
				'is_captcha_enable' => true,
			) );

			$recaptcha_node = '<div id="node_recaptcha" class="g-recaptcha" style="margin-left:11px;transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>';
		} else {
			$recaptcha_node = '';
		}

		if ( ! is_user_logged_in() ) {

			if ( isset( $wp->query_vars['lost-password'] ) ) {
				UR_Shortcode_My_Account::lost_password();
			} else {
				ur_get_template( 'myaccount/form-login.php',array( 'recaptcha_node' => $recaptcha_node ) );
			}
		}
		else
		{
			echo sprintf( __('You are already logged in. <a href="%s">Log out?</a>', 'user-registration' ),  ur_logout_url() ) ;
		}
	}
}
