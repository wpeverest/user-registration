<?php
/**
 * User Registration Shortcodes.
 *
 * @class    UR_Shortcodes
 * @version  1.4.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Shortcodes Class
 */
class UR_Shortcodes {

	/**
	 * Init Shortcodes.
	 */
	public static function init() {


		$shortcodes = array(
			'user_registration_form'       => __CLASS__ . '::form', // change it to user_registration_form ;)
			'user_registration_my_account' => __CLASS__ . '::my_account',
			'user_registration_login'	=>__class__ . '::login',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function
	 * @param array    $atts (default: array())
	 * @param array    $wrapper
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'user-registration',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		echo empty( $wrapper['before'] ) ? '<div id="user-registration" class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		return ob_get_clean();
	}

	/**
	 * My account page shortcode.
	 *
	 * @param mixed $atts
	 *
	 * @return string
	 */
	public static function my_account( $atts ) {
		return self::shortcode_wrapper( array( 'UR_Shortcode_My_Account', 'output' ), $atts,apply_filters('user_registration_my_account_shortcode',array(
			'class'  => 'user-registration',
			'before' => null,
			'after'  => null,
		) ));
	}

	public static function login( $atts ) {

		return self::shortcode_wrapper( array( 'UR_Shortcode_Login', 'output' ), $atts,apply_filters('user_registration_login_shortcode',array(
			'class'  => 'user-registration',
			'before' => null,
			'after'  => null,
		) ));
	}

	/**
	 * User Registration form shortcode.
	 */
	public static function form( $atts ) {
		if ( empty( $atts ) ) {
			return '';
		}

		$users_can_register = apply_filters( 'ur_register_setting_override', get_option( 'users_can_register' ) );

		if ( ! is_user_logged_in() ) {

			if ( ! $users_can_register ) {

				return apply_filters( 'ur_register_pre_form_message', '<p class="alert" id="ur_register_pre_form_message">' . __( 'Only an administrator can add new users.', 'user-registration' ) . '</p>' );
			}
		} else {

			$current_user_capability = apply_filters( 'ur_registration_user_capability', 'create_users' );

			if ( ! current_user_can( $current_user_capability ) ) {

				$user_ID = get_current_user_id();

				$user = get_user_by( 'ID', $user_ID );

				global $wp;

				$current_url = home_url( add_query_arg( array(), $wp->request ) );

				$display_name = ! empty( $user->data->display_name ) ? $user->data->display_name : $user->data->user_email;

				return apply_filters( 'ur_register_pre_form_message', '<p class="alert" id="ur_register_pre_form_message">' . sprintf( __( "You are currently logged in as %1s. You don't need another account. %2s", 'user-registration' ), '<a href="#" title="' . $display_name . '">' . $display_name . '</a>', '<a href="' . wp_logout_url( $current_url ) . '" title="' . __( 'Log out of this account.', 'user-registration' ) . '">' . __( 'Logout', 'user-registration' ) . '  &raquo;</a>' ) . '</p>', $user_ID );

			}

		}


		if ( ! isset( $atts['id'] ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'id' => '',
		), $atts, 'user_registration_form' );

		ob_start();

		self::render_form( $atts['id'] );
		
		return ob_get_clean();
	}

	/**
	 * Output for registration form .
	 * @since 1.0.1 Recaptcha only
	 */
	private static function render_form( $form_id ) {
		$args = array(
			'post_type'   => 'user_registration',
			'post_status' => 'publish',
			'post__in'    => array( $form_id ),
		);

		$post_data = get_posts( $args );
		$form_data = '';

		if ( isset( $post_data[0] ) ) {
			$form_data = $post_data[0]->post_content;
		}
		$form_data_array = json_decode( $form_data );

		if ( gettype( $form_data_array ) != 'array' ) {
			$form_data_array = array();
		}

		$is_field_exists = false;

		$enable_strong_password = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password' );

		wp_localize_script( 'ur-password-strength-meter', 'enable_strong_password', $enable_strong_password );

		$recaptcha_enable = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_enable_recaptcha_support' );

		$recaptcha_site_key = get_option( 'user_registration_integration_setting_recaptcha_site_key', - 1 );


		$recaptcha_site_secret = get_option( 'user_registration_integration_setting_recaptcha_site_secret', - 1 );

		if ( empty( $recaptcha_site_key ) ) {

			$recaptcha_site_key = - 1;
		}
		if ( empty( $recaptcha_site_secret ) ) {

			$recaptcha_site_secret = - 1;
		}
		if ( 'yes' == $recaptcha_enable ) {

			wp_enqueue_script( 'ur-google-recaptcha' );

			wp_localize_script( 'ur-google-recaptcha', 'ur_google_recaptcha_code', array(

				'site_key' => $recaptcha_site_key,

				'site_secret' => $recaptcha_site_secret,

				'is_captcha_enable' => true,

			) );
		}
		$recaptcha_node = '<div id="node_recaptcha" class="g-recaptcha" style="margin-left:11px;transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>';

		if ( 'no' === $recaptcha_enable ) {

			$recaptcha_node = '';

		}
		if ( 'yes' === $recaptcha_enable && - 1 !== $recaptcha_site_key && - 1 !== $recaptcha_site_secret ) {

			$recaptcha_node = '<div id="node_recaptcha" class="g-recaptcha" style="margin-left:11px;transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>';

		}

		ur_get_template( 'form-registration.php', array(
			'form_data_array'        => $form_data_array,
			'is_field_exists'        => $is_field_exists,
			'form_id'                => $form_id,
			'enable_strong_password' => $enable_strong_password,
			'recaptcha_node'         => $recaptcha_node,

		) );

	}
}
