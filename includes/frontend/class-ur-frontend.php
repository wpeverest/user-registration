<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Frontend
 * @version  1.0.0
 * @package  UserRegistration/Frontend
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Frontend Class
 */
class UR_Frontend {

	private static $_instance;

	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'login_init', array( $this, 'prevent_core_login_page' ) );
		add_filter( 'user_registration_my_account_shortcode', array( $this, 'user_registration_my_account_layout' ) );
	}

	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function includes() {
		include_once UR_ABSPATH . 'includes' . UR_DS . 'frontend' . UR_DS . 'class-ur-frontend-form-handler.php';
	}

	/**
	 * Includes any classes we need within admin.
	 */
	public function user_registration_frontend_form( $field_object, $form_id ) {

		$class_name = ur_load_form_field_class( $field_object->field_key );

		if ( class_exists( $class_name ) ) {
			$instance                   = $class_name::get_instance();
			$setting['general_setting'] = $field_object->general_setting;
			$setting['advance_setting'] = $field_object->advance_setting;
			$field_type                 = ur_get_field_type( $field_object->field_key );
			$instance->frontend_includes( $setting, $form_id, $field_type, $field_object->field_key );
		}
	}

	/**
	 * My Account layouts(vertical/horizontal) by adding class.
	 *
	 * @param $attributes
	 * @since  1.4.2
	 * @return  $attributes
	 */
	public function user_registration_my_account_layout( $attributes ) {

		if ( is_user_logged_in() ) {
			$layout              = get_option( 'user_registration_my_account_layout', 'horizontal' );
			$attributes['class'] = $attributes['class'] . ' ' . $layout;
		}
		return $attributes;
	}

	/**
	 * Prevents Core Login page.
	 *
	 * @since 1.6.0
	 */
	public function prevent_core_login_page() {
		global $action;
		$login_option_redirection = get_option( 'user_registration_login_options_login_redirect_url', 'unset' );
		$login_page               = get_post( $login_option_redirection );
		$myaccount_page           = get_post( get_option( 'user_registration_myaccount_page_id' ) );

		if ( ! empty( $login_page ) ) {
			$matched      = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $login_page->post_content );
			$redirect_url = $login_page->guid;
		} elseif ( ! empty( $myaccount_page ) ) {
			$matched      = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $myaccount_page->post_content );
			$redirect_url = $myaccount_page->guid;
		}

		if ( ! ( defined( 'UR_DISABLE_PREVENT_CORE_LOGIN' ) && true === UR_DISABLE_PREVENT_CORE_LOGIN ) && 'yes' === get_option( 'user_registration_login_options_prevent_core_login', 'no' ) && 1 <= absint( $matched ) ) {
			if ( 'register' === $action || 'login' === $action ) {
				// $myaccount_page = add_query_arg( $_GET, get_permalink( $redirect_url ) ); // phpcs:ignore WordPress.Security.NonceVerification
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}
}

return new UR_Frontend();
