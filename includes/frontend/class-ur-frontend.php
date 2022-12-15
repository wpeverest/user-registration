<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Frontend
 * @version  1.0.0
 * @package  UserRegistration/Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Frontend Class
 */
class UR_Frontend {

	/**
	 * Instance of the class.
	 *
	 * @var UR_Frontend
	 */
	private static $_instance;

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'after_setup_theme', array( $this, 'prevent_admin_access' ) );
		add_action( 'login_init', array( $this, 'prevent_core_login_page' ) );
		add_filter( 'user_registration_my_account_shortcode', array( $this, 'user_registration_my_account_layout' ) );
	}

		/**
		 * Prevent any user who cannot 'edit_posts' from accessing admin.
		 */
	public function prevent_admin_access() {
		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {
			$user_meta    = get_userdata( $user_id );
			$user_roles   = $user_meta->roles;
			$option_roles = get_option( 'user_registration_general_setting_disabled_user_roles', array() );
			if ( ! is_array( $option_roles ) ) {
				$option_roles = array();
			}

			if ( ! in_array( 'administrator', $user_roles, true ) ) {
				$result = array_intersect( $user_roles, $option_roles );

				if ( count( $result ) > 0 && apply_filters( 'user_registration_prevent_admin_access', true ) ) {
					show_admin_bar( false );
				}
			}
		}
	}

	/**
	 * Set instance.
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Includes files.
	 */
	public function includes() {
		include_once UR_ABSPATH . 'includes' . UR_DS . 'frontend' . UR_DS . 'class-ur-frontend-form-handler.php';
	}

	/**
	 * Includes any classes we need within admin.
	 *
	 * @param mixed $field_object Field Object.
	 * @param int   $form_id Form ID.
	 */
	public function user_registration_frontend_form( $field_object, $form_id ) {

		$class_name = ur_load_form_field_class( $field_object->field_key );

		if ( class_exists( $class_name ) ) {
			$instance                   = $class_name::get_instance();
			$setting['general_setting'] = $field_object->general_setting;
			$setting['advance_setting'] = $field_object->advance_setting;
			$setting['icon']            = isset( $field_object->icon ) ? $field_object->icon : '';
			$field_type                 = ur_get_field_type( $field_object->field_key );

			// Force drop the custom class because it has been addressed in prior container.
			if ( ! empty( $setting['advance_setting']->custom_class ) ) {
				unset( $setting['advance_setting']->custom_class );
			}
			$instance->frontend_includes( $form_id, $field_type, $field_object->field_key, $setting );
		}
	}

	/**
	 * My Account layouts(vertical/horizontal) by adding class.
	 *
	 * @param array $attributes Attributes.
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
		$login_page     = get_post( get_option( 'user_registration_login_options_login_redirect_url', 'unset' ) );
		$myaccount_page = get_post( get_option( 'user_registration_myaccount_page_id' ) );
		$matched        = 0;

		if ( ( isset( $_POST['learndash-login-form'] ) || isset( $_POST['learndash-registration-form'] ) ) ) { //phpcs:ignore
			return;
		}

		if ( ! empty( $login_page ) ) {
			$shortcodes = parse_blocks( $login_page->post_content );
			foreach ( $shortcodes as $shortcode ) {
				if ( ! empty( $shortcode['blockName'] ) ) {
					if ( 'user-registration/form-selector' === $shortcode['blockName'] && isset( $shortcode['attrs']['shortcode'] ) ) {
						$matched = 1;
						break;
					} elseif ( ( 'core/shortcode' === $shortcode['blockName'] || 'core/paragraph' === $shortcode['blockName'] ) && isset( $shortcode['innerHTML'] ) ) {
						$matched = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $shortcode['innerHTML'] );
						if ( 1 > absint( $matched ) ) {
							$matched = preg_match( '/\[woocommerce_my_account(\s\S+){0,3}\]/', $shortcode['innerHTML'] );
						}
						if ( 0 < absint( $matched ) ) {
							break;
						}
					}
				} else {
					$matched = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $login_page->post_content );
					if ( 1 > absint( $matched ) ) {
						$matched = preg_match( '/\[woocommerce_my_account(\s\S+){0,3}\]/', $login_page->post_content );
					}
					if ( 0 < absint( $matched ) ) {
						break;
					}
				}
			}
			$page_id = $login_page->ID;
		} elseif ( ! empty( $myaccount_page ) ) {
			$shortcodes = parse_blocks( $myaccount_page->post_content );
			foreach ( $shortcodes as $shortcode ) {
				if ( ! empty( $shortcode['blockName'] ) ) {
					if ( 'user-registration/form-selector' === $shortcode['blockName'] && isset( $shortcode['attrs']['shortcode'] ) ) {
						$matched = 1;
						break;
					} elseif ( ( 'core/shortcode' === $shortcode['blockName'] || 'core/paragraph' === $shortcode['blockName'] ) && isset( $shortcode['innerHTML'] ) ) {
						$matched = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $shortcode['innerHTML'] );
						if ( 1 > absint( $matched ) ) {
							$matched = preg_match( '/\[woocommerce_my_account(\s\S+){0,3}\]/', $shortcode['innerHTML'] );
						}
						if ( 0 < absint( $matched ) ) {
							break;
						}
					}
				} else {
					$matched = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $myaccount_page->post_content );
					if ( 1 > absint( $matched ) ) {
						$matched = preg_match( '/\[woocommerce_my_account(\s\S+){0,3}\]/', $myaccount_page->post_content );
					}
					if ( 0 < absint( $matched ) ) {
						break;
					}
				}
			}
			$page_id = $myaccount_page->ID;
		}

		if ( ! ( defined( 'UR_DISABLE_PREVENT_CORE_LOGIN' ) && true === UR_DISABLE_PREVENT_CORE_LOGIN ) && 'yes' === get_option( 'user_registration_login_options_prevent_core_login', 'no' ) && 1 <= absint( $matched ) ) {

			// Redirect to core login reset password page on multisite.
			if ( is_multisite() && ( 'lostpassword' === $action || 'resetpass' === $action ) ) {
				return;
			}

			if ( 'register' === $action || 'login' === $action || 'lostpassword' === $action || 'resetpass' === $action ) {
				$myaccount_page = apply_filters( 'user_registration_myaccount_redirect_url', get_permalink( $page_id ), $page_id );
				wp_safe_redirect( $myaccount_page );
				exit;
			}
		}
	}
}

return new UR_Frontend();
