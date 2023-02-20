<?php
/**
 * Login Shortcodes
 *
 * Show the login form
 *
 * @class    UR_Shortcode_Login
 * @version  1.0.0
 * @package  UserRegistration/Shortcodes/Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Shortcode_Login Class.
 */
class UR_Shortcode_Login {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function get( $atts ) {
		return UR_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function output( $atts ) {
		global $wp, $post;

		$redirect_url = isset( $atts['redirect_url'] ) ? trim( $atts['redirect_url'] ) : '';

		if ( ! is_user_logged_in() ) {
			// After password reset, add confirmation message.
			$is_password_resetted = get_transient( 'ur_password_resetted_flag' );
			if ( ! empty( $is_password_resetted ) ) {
				ur_add_notice( __( 'Your password has been reset successfully.', 'user-registration' ) );
				delete_transient( 'ur_password_resetted_flag' );
			}
			if ( isset( $wp->query_vars['ur-lost-password'] ) ) {
				UR_Shortcode_My_Account::lost_password();
			} else {
				$recaptcha_enabled = get_option( 'user_registration_login_options_enable_recaptcha', 'no' );
				wp_enqueue_script( 'ur-common' );
				if ( 'yes' == $recaptcha_enabled || '1' == $recaptcha_enabled ) {
					wp_enqueue_script( 'user-registration' );
				}
				$recaptcha_node = ur_get_recaptcha_node( 'login', $recaptcha_enabled );

				ur_get_template(
					'myaccount/form-login.php',
					array(
						'recaptcha_node' => $recaptcha_node,
						'redirect'       => esc_url_raw( $redirect_url ),
					)
				);
			}
		} else {

			/* translators: %s - Link to logout. */
			echo wp_kses_post( apply_filters( 'user_registration_logged_in_message', sprintf( __( 'You are already logged in. <a href="%s">Log out?</a>', 'user-registration' ), ur_logout_url() ) ) );
		}
	}
}
