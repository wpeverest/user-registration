<?php
/**
 * My Account Shortcodes
 *
 * Show the 'my account' section where the user can view profile and update their information.
 *
 * @class    UR_Shortcode_My_Account
 * @version  1.0.0
 * @package  UserRegistration/Shortcodes/My_Account
 * @category Shortcodes
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Shortcode_My_Account Class.
 */
class UR_Shortcode_My_Account {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts
	 * @return mixed
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

		// Enqueue user registration script.
		wp_enqueue_script( 'user-registration' );

		if ( ! is_user_logged_in() ) {
			$message = apply_filters( 'user_registration_my_account_message', '' );

			if ( ! empty( $message ) ) {
				ur_add_notice( $message );
			}

			// After password reset, add confirmation message.
			if ( ! empty( $_GET['password-reset'] ) ) {
				ur_add_notice( __( 'Your password has been reset successfully.', 'user-registration' ) );
			}

			if ( isset( $wp->query_vars['lost-password'] ) ) {
				self::lost_password();
			} else {
				ur_get_template( 'myaccount/form-login.php' );
			}
		} else {
			// Start output buffer since the html may need discarding for BW compatibility
			ob_start();

			if ( isset( $wp->query_vars['user-logout'] ) ) {
				ur_add_notice( sprintf( __( 'Are you sure you want to log out? <a href="%s">Confirm and log out</a>', 'user-registration' ), ur_logout_url() ) );
			}

			do_action( 'before-user-registration-my-account-shortcode' );

			// Collect notices before output
			include_once( UR_ABSPATH . 'includes/functions-ur-notice.php' );
			$notices = ur_get_notices();

			// Output the new account page
			self::my_account( $atts );

			// Send output buffer
			ob_end_flush();
		}
	}

	/**
	 * My account page.
	 *
	 * @param array $atts
	 */
	private static function my_account( $atts ) {
		ur_get_template( 'myaccount/my-account.php', array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		) );
	}

	/**
	 * Edit profile details page.
	 */
	public static function edit_profile() {

		$user_id=get_current_user_id();
		$form_id_array=get_user_meta($user_id,'ur_form_id');
		$form_id=0;

		if( isset($form_id_array[0]) ){
			$form_id = $form_id_array[0];
		}

 		$profile = user_registration_form_data( $user_id, $form_id );
		$user_data_obj = get_userdata($user_id);
		$user_data = $user_data_obj->data;

		if( count( $profile ) < 1 ) {
			return;
		}

		// Prepare values
		foreach ( $profile as $key => $field ) {
			$value = get_user_meta( get_current_user_id(), $key, true );
			$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $value, $key );
			$new_key=str_replace('user_registration_','',$key);

			if(in_array($new_key,ur_get_registered_user_meta_fields())){
				$value = get_user_meta( get_current_user_id(), (str_replace('user_','',$new_key)), true );
				$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $value, $key );
			}elseif(isset($user_data->$new_key) && in_array($new_key,ur_get_user_table_fields())){
 				$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $user_data->$new_key, $key );

			}else if(isset($user_data->display_name) && $key==='user_registration_display_name'){
				$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $user_data->display_name, $key );
			}
		}

		ur_get_template( 'myaccount/form-edit-profile.php', array(
			'profile' => apply_filters( 'user_registration_profile_to_edit', $profile ),
		) );
	}

	/**
	 * Edit account details page.
	 */
	public static function edit_account() {
		ur_get_template( 'myaccount/form-edit-password.php', array(
			'user' => get_user_by( 'id', get_current_user_id() ),
		) );
	}

	/**
	 * Lost password page handling.
	 */
	public static function lost_password() {
		/**
		 * After sending the reset link, don't show the form again.
		 */
		if ( ! empty( $_GET['reset-link-sent'] ) ) {
			return ur_get_template( 'myaccount/lost-password-confirmation.php' );

		/**
		 * Process reset key / login from email confirmation link
		 */
		} elseif ( ! empty( $_GET['show-reset-form'] ) ) {
			if ( isset( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ) && 0 < strpos( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ], ':' ) ) {
				list( $rp_login, $rp_key ) = array_map( 'ur_clean', explode( ':', wp_unslash( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ), 2 ) );
				$user = self::check_password_reset_key( $rp_key, $rp_login );

				// reset key / login is correct, display reset password form with hidden key / login values
				if ( is_object( $user ) ) {
					return ur_get_template( 'myaccount/form-reset-password.php', array(
						'key'   => $rp_key,
						'login' => $rp_login,
					) );
				} else {
					self::set_reset_password_cookie();
				}
			}
		}

		// Show lost password form by default
		ur_get_template( 'myaccount/form-lost-password.php', array(
			'form' => 'lost_password',
		) );
	}

	/**
	 * Handles sending password retrieval email to customer.
	 *
	 * Based on retrieve_password() in core wp-login.php.
	 *
	 * @uses $wpdb WordPress Database object
	 * @return bool True: when finish. False: on error
	 */
	public static function retrieve_password() {
		global $wpdb, $wp_hasher;

		$login = trim( $_POST['user_login'] );

		if ( empty( $login ) ) {
			ur_add_notice( __( 'Enter a username or email address.', 'user-registration' ), 'error' );
			return false;
		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) && apply_filters( 'user_registration_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', $login );
		}

		$errors = new WP_Error();
		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ) {
			ur_add_notice( $errors->get_error_message(), 'error' );
			return false;
		}

		if ( ! $user_data ) {
			ur_add_notice( __( 'Invalid username or email.', 'user-registration' ), 'error' );
			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			ur_add_notice( __( 'Invalid username or email.', 'user-registration' ), 'error' );
			return false;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		do_action( 'retrieve_password', $user_login );
		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {
			ur_add_notice( __( 'Password reset is not allowed for this user', 'user-registration' ), 'error' );
			return false;

		} elseif ( is_wp_error( $allow ) ) {
			ur_add_notice( $allow->get_error_message(), 'error' );
			return false;
		}

		// Get password reset key (function introduced in WordPress 4.4).
		$key = get_password_reset_key( $user_data );

		// Send email notification
		if( UR_Emailer::lost_password_email( $user_login, $user_data, $key) == false ) {	
			ur_add_notice( __( 'The email could not be sent. Contact your site administrator. ', 'user-registration' ), 'error' );
			return false;
		}

		return true;
	}

	/**
	 * Retrieves a user row based on password reset key and login.
	 *
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 * @param string $login The user login
	 * @return WP_User|bool User's database row on success, false for invalid keys
	 */
	public static function check_password_reset_key( $key, $login ) {
		// Check for the password reset key.
		// Get user data or an error message in case of invalid or expired key.
		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			ur_add_notice( $user->get_error_message(), 'error' );
			return false;
		}

		return $user;
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @param object $user The user
	 * @param string $new_pass New password for the user in plaintext
	 */
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );
		wp_set_password( $new_pass, $user->ID );
		self::set_reset_password_cookie();
		wp_password_change_notification( $user );
	}

	/**
	 * Set or unset the cookie.
	 */
	public static function set_reset_password_cookie( $value = '' ) {
		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );

		if ( $value ) {
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		} else {
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}
}
