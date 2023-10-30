<?php
/**
 * User Registration Shortcodes.
 *
 * @class    UR_Shortcodes
 * @version  1.4.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Shortcodes Class
 */
class UR_Shortcodes {

	public static $parts = false; // phpcs:ignore

	/**
	 * Init Shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'user_registration_form'          => __CLASS__ . '::form', // change it to user_registration_form.
			'user_registration_my_account'    => __CLASS__ . '::my_account',
			'user_registration_login'         => __class__ . '::login',
			'user_registration_edit_profile'  => __class__ . '::edit_profile',
			'user_registration_edit_password' => __class__ . '::edit_password',
		);
		add_filter( 'pre_do_shortcode_tag', array( UR_Shortcode_My_Account::class, 'pre_do_shortcode_tag' ), 10, 4 ); // phpcs:ignore

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function Callback function.
	 * @param array    $atts (default: array()) Extra attributes.
	 * @param array    $wrapper Shortcode wrapper.
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
		include_once UR_ABSPATH . 'includes/functions-ur-notice.php';
		$notices = ur_get_notices();
		ur_print_notices();
		$wrap_before = empty( $wrapper['before'] ) ? '<div id="user-registration" class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		echo wp_kses_post( $wrap_before );
		call_user_func( $function, $atts );
		$wrap_after = empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];
		echo wp_kses_post( $wrap_after );

		return ob_get_clean();
	}

	/**
	 * My account page shortcode.
	 *
	 * @param mixed $atts Extra attributes.
	 *
	 * @return string
	 */
	public static function my_account( $atts ) {
		do_action( 'user_registration_my_account_enqueue_scripts', array(), 0 );
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
	 * @param mixed $atts Extra attributes.
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
	 * User Registration Edit password form shortcode.
	 *
	 * @param mixed $atts Extra attributes.
	 *
	 * @since 2.2.7
	 */
	public static function edit_password( $atts ) {
		return self::shortcode_wrapper( array( __CLASS__, 'render_edit_password' ), $atts );
	}

	/**
	 * Edit password page shortcode.
	 *
	 * @since 2.2.7
	 */
	public static function render_edit_password() {
		if ( is_user_logged_in() ) {
			include_once 'shortcodes/class-ur-shortcode-my-account.php';
			UR_Shortcode_My_Account::edit_password();
		} else {
			do_action( 'user_registration_edit_password_shortcode' );

			/* translators: %s - Link to login form. */
			echo wp_kses_post( apply_filters( 'user_registration_edit_password_shortcode_message', sprintf( __( 'Please Login to edit password. <a href="%s">Login Here?</a>', 'user-registration' ), ur_get_my_account_url() ) ) );
		}
	}

	/**
	 * User Registration Edit profile form shortcode.
	 *
	 * @param mixed $atts Extra attributes.
	 */
	public static function edit_profile( $atts ) {
		return self::shortcode_wrapper( array( __CLASS__, 'render_edit_profile' ), $atts );
	}

	/**
	 * Output for Edit-profile form .
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
				if ( 1 > absint( $matched ) ) {
					$matched = preg_match( '/\[woocommerce_my_account(\s\S+){0,3}\]/', $myaccount_page->post_content );
				}
				if ( 1 === $matched ) {
					$page_id = $myaccount_page->ID;
				}
			}

			/* translators: %s - Link to login form. */
			echo wp_kses_post( apply_filters( 'user_registration_logged_in_message', sprintf( __( 'Please Login to edit profile. <a href="%s">Login Here?</a>', 'user-registration' ), isset( $page_id ) ? get_permalink( $page_id ) : wp_login_url() ) ) );
		} else {
			include_once 'shortcodes/class-ur-shortcode-my-account.php';
			UR_Shortcode_My_Account::edit_profile();
		}
	}

	/**
	 * User Registration form shortcode.
	 *
	 * @param mixed $atts Extra attributes.
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

				/* translators: 1: Link and username of user 2: Logout url */
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
	 * @param int $form_id Form ID.
	 * @since 1.0.1 Recaptcha only
	 */
	private static function render_form( $form_id ) {
		$form_data_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();
		$form_json_data  = wp_json_encode( $form_data_array );

		$values = array(
			'form_id' => $form_id,
		);

		$content         = apply_filters( 'user_registration_process_smart_tags', $form_json_data, $values, array() );
		$form_data_array = json_decode( $content );
		$form_row_ids    = '';

		if ( ! empty( $form_data_array ) ) {
			$form_row_ids = get_post_meta( $form_id, 'user_registration_form_row_ids', true );
		}
		$form_row_ids_array = json_decode( $form_row_ids );

		if ( gettype( $form_row_ids_array ) != 'array' ) {
			$form_row_ids_array = array();
		}

		$is_field_exists           = false;
		$enable_strong_password    = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password' ) );
		$minimum_password_strength = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_minimum_password_strength' );

		// Enqueue script.
		wp_enqueue_script( 'user-registration' );
		wp_enqueue_script( 'ur-form-validator' );
		wp_enqueue_script( 'ur-common' );

		do_action( 'user_registration_enqueue_scripts', $form_data_array, $form_id );

		$has_date = ur_has_date_field( $form_id );

		if ( true === $has_date ) {
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_script( 'flatpickr' );
		}

		if ( $enable_strong_password ) {
			wp_enqueue_script( 'ur-password-strength-meter' );
		}

		$recaptcha_enabled = ur_string_to_bool( ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_enable_recaptcha_support', false ) );
		$recaptcha_node    = ur_get_recaptcha_node( 'register', $recaptcha_enabled );
		$form_data_array   = apply_filters( 'user_registration_before_registration_form_template', $form_data_array, $form_id );

		/** Allow filter to return early if some condition is not meet.
		 *
		 * @since 4.1.0
		 */
		if ( ! apply_filters( 'user_registration_frontend_before_load', true, $form_data_array, $form_id ) ) {
			do_action( 'user_registration_frontend_not_loaded', $form_data_array, $form_id );
			return;
		}

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
				'recaptcha_enabled'         => $recaptcha_enabled,
			)
		);
	}
	/**
	 * Check the redirection url is valid url or slug.
	 *
	 * @param string $redirect_url redirection url.
	 */
	public static function check_is_valid_redirect_url( $redirect_url ) {
		if ( filter_var( $redirect_url, FILTER_VALIDATE_URL ) === false ) {
			$all_page_slug = ur_get_all_page_slugs();
			if ( in_array( $redirect_url, $all_page_slug, true ) ) {
				$redirect_url = site_url( $redirect_url );
			} elseif( '' === $redirect_url ) {
				$redirect_url;
			} else {
				$redirect_url = home_url();
			}
		}
		return $redirect_url;
	}
}
