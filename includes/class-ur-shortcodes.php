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

	public static $parts = false;

	/**
	 * Init Shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'user_registration_form'       => __CLASS__ . '::form', // change it to user_registration_form ;)
			'user_registration_my_account' => __CLASS__ . '::my_account',
			'user_registration_login'      => __class__ . '::login',
			'user_registration_edit_profile' => __class__ . '::edit_profile',
		);
		add_filter( 'pre_do_shortcode_tag', array( UR_Shortcode_My_Account::class, 'pre_do_shortcode_tag' ), 10, 4 );

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
		wp_enqueue_script( 'ur-login' );

		return self::shortcode_wrapper(
			array( 'UR_Shortcode_My_Account', 'output' ),
			$atts,
			apply_filters(
				'user_registration_my_account_shortcode',
				array(
					'class'  => 'user-registration',
					'before' => null,
					'after'  => null,
				)
			)
		);
	}

	/**
	 * My account page shortcode.
	 *
	 * @param mixed $atts
	 *
	 * @return string
	 */
	public static function login( $atts ) {
		do_action( 'user_registration_my_account_enqueue_scripts', array(), 0 );
		wp_enqueue_script( 'ur-login' );

		return self::shortcode_wrapper(
			array( 'UR_Shortcode_Login', 'output' ),
			$atts,
			apply_filters(
				'user_registration_login_shortcode',
				array(
					'class'  => 'user-registration',
					'before' => null,
					'after'  => null,
				)
			)
		);
	}

	/**
	 * User Registration Edit profile form shortcode.
	 *
	 * @param mixed $atts
	 */
	public static function edit_profile( $atts ) {
		return UR_Shortcodes::shortcode_wrapper( array( __CLASS__, 'render_edit_profile' ), $atts );
	}

	/**
	 * Output for Edit-profile form .
	 *
	 */
	private static function render_edit_profile() {
		    $user_id = get_current_user_id();
			$form_id = get_user_meta( $user_id, 'ur_form_id', true );
			do_action( 'user_registration_my_account_enqueue_scripts', array(), $form_id );
			$has_date = ur_has_date_field( $form_id );

			if ( true === $has_date ) {
				wp_enqueue_style( 'flatpickr' );
				wp_enqueue_script( 'flatpickr' );
			}
		if ( ! is_user_logged_in() ) {
			$myaccount_page = get_post( get_option( 'user_registration_myaccount_page_id' ) );
			$matched        = 0;

			if ( ! empty( $myaccount_page ) ) {
				$matched = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $myaccount_page->post_content );
				if(1 > absint( $matched )) {
					$matched = preg_match( '/\[woocommerce_my_account(\s\S+){0,3}\]/', $myaccount_page->post_content );
				}
				if ( 1 === $matched ) {
					$page_id = $myaccount_page->ID;
				}
			}
			echo apply_filters( 'user_registration_logged_in_message', sprintf( __( 'Please Login to edit profile. <a href="%s">Login Here?</a>', 'user-registration' ), isset($page_id) ? get_permalink($page_id) : wp_login_url() ) );
		} else {
			include_once 'shortcodes/class-ur-shortcode-my-account.php';
			UR_Shortcode_My_Account::edit_profile();
		}
	}

	/**
	 * User Registration form shortcode.
	 *
	 * @param mixed $atts
	 */
	public static function form( $atts ) {

		if ( empty( $atts ) || ! isset( $atts['id'] ) ) {
			return '';
		}

		$users_can_register = apply_filters( 'ur_register_setting_override', get_option( 'users_can_register' ) );

		if ( ! is_user_logged_in() ) {
			if ( ! $users_can_register ) {
				return apply_filters( 'ur_register_pre_form_message', '<p class="alert" id="ur_register_pre_form_message">' . __( 'Only administrators can add new users.', 'user-registration' ) . '</p>' );
			}
		} else {

			$current_user_capability = apply_filters( 'ur_registration_user_capability', 'create_users' );

			if ( ! current_user_can( $current_user_capability ) ) {
				global $wp;

				$user_ID      = get_current_user_id();
				$user         = get_user_by( 'ID', $user_ID );
				$current_url  = home_url( add_query_arg( array(), $wp->request ) );
				$display_name = ! empty( $user->data->display_name ) ? $user->data->display_name : $user->data->user_email;

				return apply_filters( 'ur_register_pre_form_message', '<p class="alert" id="ur_register_pre_form_message">' . sprintf( __( 'You are currently logged in as %1$1s. %2$2s', 'user-registration' ), '<a href="#" title="' . $display_name . '">' . $display_name . '</a>', '<a href="' . wp_logout_url( $current_url ) . '" title="' . __( 'Log out of this account.', 'user-registration' ) . '">' . __( 'Logout', 'user-registration' ) . '  &raquo;</a>' ) . '</p>', $user_ID );

			}
		}

		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts,
			'user_registration_form'
		);

		do_action( 'user_registration_form_shortcode_scripts', $atts );

		ob_start();
		self::render_form( $atts['id'] );

		return ob_get_clean();
	}

	/**
	 * Output for registration form .
	 *
	 * @since 1.0.1 Recaptcha only
	 */
	private static function render_form( $form_id ) {
		$form_data_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();
		$form_row_ids    = '';

		if ( ! empty( $form_data_array ) ) {
			$form_row_ids = get_post_meta( $form_id, 'user_registration_form_row_ids', true );
		}
		$form_row_ids_array = json_decode( $form_row_ids );

		if ( gettype( $form_row_ids_array ) != 'array' ) {
			$form_row_ids_array = array();
		}

		$is_field_exists           = false;
		$enable_strong_password    = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password' );
		$minimum_password_strength = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_minimum_password_strength' );

		// Enqueue script.
		wp_enqueue_script( 'user-registration' );
		wp_enqueue_script( 'ur-form-validator' );

		do_action( 'user_registration_enqueue_scripts', $form_data_array, $form_id );

		$has_date = ur_has_date_field( $form_id );

		if ( true === $has_date ) {
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_script( 'flatpickr' );
		}

		if ( 'yes' === $enable_strong_password || '1' === $enable_strong_password ) {
			wp_enqueue_script( 'ur-password-strength-meter' );
		}

		$recaptcha_enabled = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_enable_recaptcha_support' );
		$recaptcha_node    = ur_get_recaptcha_node( $recaptcha_enabled, 'register' );

		$form_data_array = apply_filters( 'user_registration_before_registration_form_template', $form_data_array, $form_id );

		self::$parts = apply_filters( 'user_registration_parts_data', self::$parts, $form_id, $form_data_array );

		include_once UR_ABSPATH . 'includes/frontend/class-ur-frontend.php';
		ur_get_template(
			'form-registration.php',
			array(
				'form_data_array'           => $form_data_array,
				'is_field_exists'           => $is_field_exists,
				'form_id'                   => $form_id,
				'enable_strong_password'    => $enable_strong_password,
				'minimum_password_strength' => $minimum_password_strength,
				'recaptcha_node'            => $recaptcha_node,
				'parts'                     => self::$parts,
				'row_ids'                   => $form_row_ids_array,
				'recaptcha_enabled'			=> $recaptcha_enabled
			)
		);
	}
}
