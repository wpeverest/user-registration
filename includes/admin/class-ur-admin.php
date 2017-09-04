<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Admin
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Admin Class
 */
class UR_Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ), 10, 2 );
		if ( 'admin_approval' === get_option( 'user_registration_general_setting_login_options' ) ) {
			new UR_Admin_User_List_Manager();
		}

	}

	/**
	 * Includes any classes we need within admin.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/functions-ur-admin.php' );
		include_once( dirname( __FILE__ ) . '/class-ur-admin-notices.php' );
		include_once( dirname( __FILE__ ) . '/class-ur-admin-menus.php' );

		// Abstract class
		include_once( UR_ABSPATH . 'includes' . UR_DS . 'admin' . UR_DS . 'class-ur-admin-assets.php' );
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		if ( ! $screen = get_current_screen() ) {
			return;
		}

		switch ( $screen->id ) {
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				include( 'class-ur-admin-profile.php' );
				break;
		}
	}

	/**
	 * Prevent any user who cannot 'edit_posts' from accessing admin.
	 */
	public function prevent_admin_access() {
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}
		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {
			$user_meta    = get_userdata( $user_id );
			$user_roles   = $user_meta->roles;
			$option_roles = get_option( 'user_registration_general_setting_disabled_user_roles', array() );
			if ( ! is_array( $option_roles ) ) {
				$option_roles = array();
			}
			$result = array_intersect( $user_roles, $option_roles );

			if ( count( $result ) > 0 && apply_filters( 'user_registration_prevent_admin_access', true ) ) {
				wp_safe_redirect( ur_get_page_permalink( 'myaccount' ) );
				exit;
			}
		}
	}
}

return new UR_Admin();
