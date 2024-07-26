<?php
/**
 * My Account Shortcodes
 *
 * Show the 'my account' section where the user can view profile and update their information.
 *
 * @class    UR_Shortcode_My_Account
 * @version  1.0.0
 * @package  UserRegistration/Shortcodes/My_Account
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Shortcode_My_Account Class.
 */
class UR_Shortcode_My_Account {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return mixed
	 */
	public static function get( $atts ) {
		return UR_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Determine whether the shortcode should be rendered or not.
	 * For example: we don't need to render shortcode when a page containing this shortcode
	 * is being edited by Elementor.
	 *
	 * @since 1.8.5
	 *
	 * @param mixed  $return Content to return. If returned false, the shortcode will be rendered.
	 * @param string $tag Current shortocode tag.
	 * @param array  $attr List of shortcode attributes.
	 * @param array  $matches List of matches obtained while doing regex for shortcodes.
	 */
	public static function pre_do_shortcode_tag( $return, $tag, $attr, $matches ) {
		// Prevent shortcode rendering for Elementor.
		if ( 'user_registration_my_account' === $tag && is_elementor_editing_page() ) {
			$return = $matches[0];
		}

		return $return;
	}

	/**
	 * Output the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function output( $atts ) {

		global $wp, $post;
		wp_enqueue_script( 'tooltipster' );
		wp_enqueue_script( 'user-registration' );
		wp_enqueue_script( 'ur-common' );

		if ( ! is_user_logged_in() ) {

			$redirect_url = isset( $atts['redirect_url'] ) ? trim( $atts['redirect_url'] ) : '';
			$redirect_url = UR_Shortcodes::check_is_valid_redirect_url( $redirect_url );
			$redirect_url = esc_url( $redirect_url );
			$redirect_url      = ( isset( $_GET['redirect_to'] ) && empty( $redirect_url ) ) ? esc_url( wp_unslash( $_GET['redirect_to'] ) ) : $redirect_url; // @codingStandardsIgnoreLine
			$form_id      = isset( $atts['form_id'] ) ? absint( $atts['form_id'] ) : 0;
			$message      = apply_filters( 'user_registration_my_account_message', '' );

			if ( ! empty( $message ) ) {
				ur_add_notice( $message );
			}

			// After password reset, add confirmation message.
			$is_password_resetted = get_transient( 'ur_password_resetted_flag' );
			if ( ! empty( $is_password_resetted ) ) {
				ur_add_notice( __( 'Your password has been reset successfully.', 'user-registration' ) );
				delete_transient( 'ur_password_resetted_flag' );
			}

			$render_default = apply_filters( 'user_registration_my_account_render_default', true, $atts );

			if ( isset( $wp->query_vars['ur-lost-password'] ) ) {
				self::lost_password();
			} elseif ( $render_default ) {
				$recaptcha_enabled = ur_option_checked( 'user_registration_login_options_enable_recaptcha', false );
				$recaptcha_node    = ur_get_recaptcha_node( 'login', $recaptcha_enabled );
				ob_start();

				ur_get_template(
					'myaccount/form-login.php',
					array(
						'recaptcha_node' => $recaptcha_node,
						'redirect'       => $redirect_url,
					)
				);

				$login_form = ob_get_clean();

				if ( 0 < $form_id ) {
					$form_attr = array(
						'id' => $form_id,
					);
					array_merge( $form_attr, $atts );
					$registration_form = UR_Shortcodes::form( $form_attr );

					ur_get_template(
						'form-login-registration.php',
						array(
							'form_id'           => $form_id,
							'registration_form' => $registration_form,
							'login_form'        => $login_form,
						)
					);
				} else {
					echo $login_form; // phpcs:ignore;
				}
			} else {
				/**
				 * Action to handles custom rendering logic for User Registration my Account page.
				 */
				do_action( 'user_registration_my_account_custom_render' );
			}
		} else {

			// Enqueue script.
			$user_id = get_current_user_id();
			$form_id = get_user_meta( $user_id, 'ur_form_id', true );

			if ( ! empty( $form_id ) ) {

				/**
				* Action to handles for enqueuing scripts for User Registration my Account page.
				*
				* @param int $form_id Form ID.
				*/
				do_action( 'user_registration_my_account_enqueue_scripts', array(), $form_id );
				$has_flatpickr = ur_has_flatpickr_field( $form_id );

				if ( true === $has_flatpickr ) {
					wp_enqueue_style( 'flatpickr' );
					wp_enqueue_script( 'flatpickr' );
				}
			}

			// Start output buffer since the html may need discarding for BW compatibility.
			ob_start();

			if ( isset( $wp->query_vars['user-logout'] ) ) {
				/* translators: %s - Link to logout */
				ur_add_notice( sprintf( __( 'Are you sure you want to log out?&nbsp;<a href="%s">Confirm and log out</a>', 'user-registration' ), ur_logout_url() ) );
			}

			/**
			 * Action to handel before rendering User Registration my account page shortcode.
			 */
			do_action( 'before-user-registration-my-account-shortcode' );

			// Collect notices before output.
			include_once UR_ABSPATH . 'includes/functions-ur-notice.php';
			$notices = ur_get_notices();

			// Output the new account page.
			self::my_account( $atts );

			// Send output buffer.
			ob_end_flush();
		}
	}

	/**
	 * My account page.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	private static function my_account( $atts ) {
		$is_disabled = get_user_meta( get_current_user_id(), 'ur_disable_users', true );
		if($is_disabled){
			wp_logout();
		}else{

		ur_get_template(
			'myaccount/my-account.php',
			array(
				'current_user' => get_user_by( 'id', get_current_user_id() ),
			)
		);
	  	}
	}

	/**
	 * Edit profile details page.
	 */
	public static function edit_profile() {
		wp_enqueue_media();
		wp_enqueue_script( 'ur-my-account' );
		wp_enqueue_script( 'ur-form-validator' );

		$user_id = get_current_user_id();
		$form_id = ur_get_form_id_by_userid( $user_id );

		$profile = user_registration_form_data( $user_id, $form_id );

		$user_data = get_userdata( $user_id );
		$user_data = $user_data->data;

		$form_data_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

		if ( ! empty( $form_data_array ) ) {

			$form_row_ids       = get_post_meta( $form_id, 'user_registration_form_row_ids', true );
			$form_row_ids_array = json_decode( $form_row_ids );

			if ( gettype( $form_row_ids_array ) != 'array' ) {
				$form_row_ids_array = array();
			}

			if ( count( $profile ) < 1 ) {
				return;
			}

			// Prepare values.
			foreach ( $profile as $key => $field ) {
				if ( isset( $field['custom_attributes']['data-locale'] ) ) {
					if ( wp_script_is( 'flatpickr' ) && 'en' !== $field['custom_attributes']['data-locale'] ) {
						wp_enqueue_script( 'flatpickr-localization_' . $field['custom_attributes']['data-locale'], UR()->plugin_url() . '/assets/js/flatpickr/dist/I10n/' . $field['custom_attributes']['data-locale'] . '.js', array(), UR_VERSION, true );
					}
				}
				$value                    = get_user_meta( get_current_user_id(), $key, true );
				$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $value, $key );
				$new_key                  = str_replace( 'user_registration_', '', $key );

				if ( in_array( $new_key, ur_get_registered_user_meta_fields() ) ) {
					$value                    = get_user_meta( get_current_user_id(), ( str_replace( 'user_', '', $new_key ) ), true );
					$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $value, $key );
				} elseif ( isset( $user_data->$new_key ) && in_array( $new_key, ur_get_user_table_fields() ) ) {
					$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $user_data->$new_key, $key );

				} elseif ( isset( $user_data->display_name ) && 'user_registration_display_name' === $key ) {
					$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $user_data->display_name, $key );
				}
			}

			include_once UR_ABSPATH . 'includes/functions-ur-notice.php';
			$notices = ur_get_notices();
			ur_print_notices();

			ur_get_template(
				'myaccount/form-edit-profile.php',
				array(
					'profile'         => apply_filters( 'user_registration_profile_to_edit', $profile ),
					'form_data_array' => apply_filters( 'user_registration_form_data_to_edit', $form_data_array ),
					'row_ids'         => $form_row_ids_array,
				)
			);
		} else {
			echo '<h1>' . esc_html__( 'No profile details found.', 'user-registration' ) . '</h1>';
		}
	}

	/**
	 * Edit account details page.
	 */
	public static function edit_account() {
		$user_id                   = get_current_user_id();
		$form_id                   = ur_get_form_id_by_userid( $user_id );
		$enable_strong_password    = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password' ) );
		$minimum_password_strength = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_minimum_password_strength' );

		wp_enqueue_script( 'ur-form-validator' );

		if ( $enable_strong_password ) {
			wp_dequeue_script( 'wc-password-strength-meter' );
			wp_enqueue_script( 'ur-password-strength-meter' );
		}

		ur_get_template(
			'myaccount/form-edit-password.php',
			array(
				'user'                      => get_user_by( 'id', get_current_user_id() ),
				'enable_strong_password'    => $enable_strong_password,
				'minimum_password_strength' => $minimum_password_strength,
			)
		);
	}

	/**
	 * Change Password page.
	 *
	 * @since 2.2.7
	 */
	public static function edit_password() {
		$user_id                   = get_current_user_id();
		$form_id                   = ur_get_form_id_by_userid( $user_id );
		$enable_strong_password    = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password' ) );
		$minimum_password_strength = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_minimum_password_strength' );

		wp_enqueue_script( 'ur-form-validator' );

		if ( $enable_strong_password ) {
			wp_dequeue_script( 'wc-password-strength-meter' );
			wp_enqueue_script( 'ur-password-strength-meter' );
		}

		ur_get_template(
			'myaccount/form-edit-password.php',
			array(
				'user'                      => get_user_by( 'id', get_current_user_id() ),
				'enable_strong_password'    => $enable_strong_password,
				'minimum_password_strength' => $minimum_password_strength,
			)
		);
	}

	/**
	 * Lost password page handling.
	 */
	public static function lost_password() {
		/**
		 * After sending the reset link, don't show the form again.
		 */
		if ( ! empty( $_GET['reset-link-sent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return ur_get_template( 'myaccount/lost-password-confirmation.php' );

			/**
			 * Process reset key / login from email confirmation link
			 */
		} elseif ( ! empty( $_GET['show-reset-form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ) && 0 < strpos( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ], ':' ) ) { // phpcs:ignore
				list( $rp_login, $rp_key ) = array_map( 'ur_clean', explode( ':', wp_unslash( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ), 2 ) ); // phpcs:ignore
				$user                      = get_user_by( 'id', $rp_login );
				$rp_login                  = isset( $user->user_login ) ? $user->user_login : $rp_login;

				$user = self::check_password_reset_key( $rp_key, $rp_login );

				if ( ! empty( $user ) ) {
					$form_id                   = ur_get_form_id_by_userid( $user->ID );
					$enable_strong_password    = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_strong_password' ) );
					$minimum_password_strength = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_minimum_password_strength' );

					if ( $enable_strong_password ) {

						// Enqueue script.
						wp_enqueue_script( 'ur-password-strength-meter' );
					}

					// reset key / login is correct, display reset password form with hidden key / login values.
					if ( is_object( $user ) ) {
						return ur_get_template(
							'myaccount/form-reset-password.php',
							array(
								'key'                    => $rp_key,
								'login'                  => $rp_login,
								'enable_strong_password' => $enable_strong_password,
								'minimum_password_strength' => $minimum_password_strength,
							)
						);
					} else {
						self::set_reset_password_cookie();
					}
				}
			}
		}

		/**
		 * Filters to enable or disable caption in lost password page.
		 * Default parameter value is false.
		 */
		$recaptcha_enabled = ur_string_to_bool( apply_filters( 'user_registration_lost_password_options_enable_recaptcha', false ) );

		if ( $recaptcha_enabled ) {
			wp_enqueue_script( 'user-registration' );
		}
		$recaptcha_node = ur_get_recaptcha_node( 'lost_password', $recaptcha_enabled );

		// Show lost password form by default.
		ur_get_template(
			'myaccount/form-lost-password.php',
			array(
				'form'           => 'lost_password',
				'recaptcha_node' => $recaptcha_node,

			)
		);
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

		$login = isset( $_POST['user_login'] ) ? trim( wp_unslash( $_POST['user_login'] ) ) : null; // phpcs:ignore

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
		do_action( 'lostpassword_post', $errors, $user_data );

		if ( $errors->get_error_code() ) {
			ur_add_notice( $errors->get_error_message(), 'error' );
			return false;
		}

		/**
		 * Filter to modify invalid username or email error message.
		 */
		$error_message = apply_filters( 'user_registration_invalid_username_or_email_error_message', __( 'Invalid username or email.', 'user-registration' ) );

		if ( ! $user_data || ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) ) {
			ur_add_notice( $error_message, 'error' );
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

		// Send email notification.
		if ( UR_Emailer::lost_password_email( $user_login, $user_data, $key ) == false ) {
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
	 * @param string $key Hash to validate sending user's password.
	 * @param string $login The user login.
	 * @return WP_User|bool User's database row on success, false for invalid keys.
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
	 * @param object $user The user.
	 * @param string $new_pass New password for the user in plaintext.
	 */
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );
		wp_set_password( $new_pass, $user->ID );
		self::set_reset_password_cookie();
		wp_password_change_notification( $user );

		/**
		 * Filter hook to modify the automatic user login on reset password.
		 *
		 * @param bool The result to allow automatic user login. Default is false.
		 */
		$allow_automatic_user_login = apply_filters( 'user_registration_allow_automatic_user_login_reset_password', false );
		if ( $allow_automatic_user_login ) {
			ur_automatic_user_login( $user );
		}
	}

	/**
	 * Set or unset the cookie.
	 *
	 * @param string $value Value.
	 */
	public static function set_reset_password_cookie( $value = '' ) {
		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = isset( $_SERVER['REQUEST_URI'] ) ? current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : ''; // phpcs:ignore

		if ( $value ) {
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		} else {
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}
}
