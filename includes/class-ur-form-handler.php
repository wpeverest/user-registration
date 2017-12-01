<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle frontend forms.
 *
 * @class       UR_Form_Handler
 * @version     1.0.0
 * @package     UserRegistration/Classes/
 * @category    Class
 * @author      WPEverest
 */
class UR_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'redirect_reset_password_link' ) );
		add_action( 'template_redirect', array( __CLASS__, 'save_profile_details' ) );
		add_action( 'template_redirect', array( __CLASS__, 'save_account_details' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'process_login' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_lost_password' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_reset_password' ), 20 );
	}

	/**
	 * Remove key and login from querystring, set cookie, and redirect to account page to show the form.
	 */
	public static function redirect_reset_password_link() {
		if ( is_ur_account_page() && ! empty( $_GET['key'] ) && ! empty( $_GET['login'] ) ) {
			$value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
			UR_Shortcode_My_Account::set_reset_password_cookie( $value );

			wp_safe_redirect( add_query_arg( 'show-reset-form', 'true', ur_lostpassword_url() ) );
			exit;
		}
	}

	/**
	 * Save and update a profie fields if the form was submitted through the user account page.
	 */
	public static function save_profile_details() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_profile_details' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_profile_details' ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$form_id_array = get_user_meta( $user_id, 'ur_form_id' );

		$form_id = 0;


		if ( isset( $form_id_array[0] ) ) {

			$form_id = $form_id_array[0];
		}
		$profile = user_registration_form_data( $user_id, $form_id );

		foreach ( $profile as $key => $field ) {

			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			// Get Value.
			switch ( $field['type'] ) {
				case 'checkbox' :
				echo "<pre>"; print_r($_POST);die;

					$_POST[ $key ] = (int) isset( $_POST[ $key ] );
					break;
				default :
					$_POST[ $key ] = isset( $_POST[ $key ] ) ? ur_clean( $_POST[ $key ] ) : '';
					break;
			}

			// Hook to allow modification of value.
			$_POST[ $key ] = apply_filters( 'user_registration_process_myaccount_field_' . $key, $_POST[ $key ] );


			// Validation: Required fields.
			if ( ! empty( $field['required'] ) && empty( $_POST[ $key ] ) ) {
				ur_add_notice( sprintf( __( '%s is a required field.', 'user-registration' ), $field['label'] ), 'error' );
			}

			if ( ! empty( $_POST[ $key ] ) ) {

				// Validation rules.
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'email' :
								$_POST[ $key ] = strtolower( $_POST[ $key ] );

								if ( ! is_email( $_POST[ $key ] ) ) {
									ur_add_notice( sprintf( __( '%s is not a valid email address.', 'user-registration' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
								}
								break;
						}
					}
				}
			}
		}// End foreach().

		do_action( 'user_registration_after_save_profile_validation', $user_id, $profile );


		if ( 0 === ur_notice_count( 'error' ) ) {

			$user_data = array();

			foreach ( $profile as $key => $field ) {

				$new_key = str_replace( 'user_registration_', '', $key );


				if ( in_array( $new_key, ur_get_user_table_fields() ) ) {

					if ( $new_key === 'user_display_name' ) {

						$user_data['display_name'] = $_POST[ $key ];
					} else {
						$user_data[ $new_key ] = $_POST[ $key ];


					}


				} else {

					$update_key = $key;

					if ( in_array( $new_key, ur_get_registered_user_meta_fields() ) ) {

						$update_key = str_replace( 'user_', '', $new_key );
					}
					update_user_meta( $user_id, $update_key, $_POST[ $key ] );
				}

			}
			if ( count( $user_data ) > 0 ) {


				$user_data["ID"] = get_current_user_id();

				wp_update_user( $user_data );

			}
			ur_add_notice( __( 'User profile updated successfully.', 'user-registration' ) );

			do_action( 'user_registration_save_profile_details', $user_id );

			wp_safe_redirect( ur_get_endpoint_url( 'edit-profile', '', ur_get_page_permalink( 'myaccount' ) ) );
			exit;
		}
	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function save_account_details() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' ) ) {
			return;
		}

		$errors = new WP_Error();
		$user   = new stdClass();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ( $user->ID <= 0 ) {
			return;
		}

		$account_first_name = ! empty( $_POST['account_first_name'] ) ? ur_clean( $_POST['account_first_name'] ) : '';
		$account_last_name  = ! empty( $_POST['account_last_name'] ) ? ur_clean( $_POST['account_last_name'] ) : '';
		$account_email      = ! empty( $_POST['account_email'] ) ? ur_clean( $_POST['account_email'] ) : '';
		$pass_cur           = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : '';
		$pass1              = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
		$pass2              = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';
		$save_pass          = true;

		$user->first_name = $account_first_name;
		$user->last_name  = $account_last_name;

		// Prevent emails being displayed, or leave alone.
		$user->display_name = is_email( $current_user->display_name ) ? $user->first_name : $current_user->display_name;

		// Handle required fields
		$required_fields = apply_filters( 'user_registration_save_account_details_required_fields', array(
			'account_first_name' => __( 'First name', 'user-registration' ),
			'account_last_name'  => __( 'Last name', 'user-registration' ),
			'account_email'      => __( 'Email address', 'user-registration' ),
		) );

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				ur_add_notice( sprintf( __( '%s is a required field.', 'user-registration' ), '<strong>' . esc_html( $field_name ) . '</strong>' ), 'error' );
			}
		}

		if ( $account_email ) {
			$account_email = sanitize_email( $account_email );
			if ( ! is_email( $account_email ) ) {
				ur_add_notice( __( 'Please provide a valid email address.', 'user-registration' ), 'error' );
			} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				ur_add_notice( __( 'This email address is already registered.', 'user-registration' ), 'error' );
			}
			$user->user_email = $account_email;
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			ur_add_notice( __( 'Please fill out all password fields.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			ur_add_notice( __( 'Please enter your current password.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			ur_add_notice( __( 'Please re-enter your password.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			ur_add_notice( __( 'New passwords do not match.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			ur_add_notice( __( 'Your current password is incorrect.', 'user-registration' ), 'error' );
			$save_pass = false;
		}

		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}

		// Allow plugins to return their own errors.
		do_action_ref_array( 'user_registration_save_account_details_errors', array( &$errors, &$user ) );

		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				ur_add_notice( $error, 'error' );
			}
		}

		if ( ur_notice_count( 'error' ) === 0 ) {

			wp_update_user( $user );

			ur_add_notice( __( 'Account details changed successfully.', 'user-registration' ) );

			do_action( 'user_registration_save_account_details', $user->ID );

			wp_safe_redirect( ur_get_page_permalink( 'myaccount' ) );
			exit;
		}
	}

	/**
	 * Process the login form.
	 */
	public static function process_login() {

		$nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value = isset( $_POST['user-registration-login-nonce'] ) ? $_POST['user-registration-login-nonce'] : $nonce_value;

		if ( ! empty( $_POST['login'] ) && wp_verify_nonce( $nonce_value, 'user-registration-login' ) ) {

			try {
				$creds = array(
					'user_password' => $_POST['password'],
					'remember'      => isset( $_POST['rememberme'] ),
				);

				$username         = trim( $_POST['username'] );
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'user_registration_process_login_errors', $validation_error, $_POST['username'], $_POST['password'] );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . __( 'Error:', 'user-registration' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $username ) ) {
					throw new Exception( '<strong>' . __( 'Error:', 'user-registration' ) . '</strong> ' . __( 'Username is required.', 'user-registration' ) );
				}

				if ( is_email( $username ) && apply_filters( 'user_registration_get_username_from_email', true ) ) {
					$user = get_user_by( 'email', $username );

					if ( isset( $user->user_login ) ) {
						$creds['user_login'] = $user->user_login;
					} else {
						throw new Exception( '<strong>' . __( 'Error:', 'user-registration' ) . '</strong> ' . __( 'A user could not be found with this email address.', 'user-registration' ) );
					}
				} else {
					$creds['user_login'] = $username;
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( 'login', $username );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
					}
				}

				// Perform the login
				$user = wp_signon( apply_filters( 'user_registration_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
					throw new Exception( $message );
				} else {

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = $_POST['redirect'];
					} elseif ( wp_get_raw_referer() ) {
						$redirect = wp_get_raw_referer();
					} else {
						$redirect = get_home_url();
					}

					wp_redirect( wp_validate_redirect( apply_filters( 'user_registration_login_redirect', $redirect, $user ), ur_get_page_permalink( 'myaccount' ) ) );
					exit;
				}
			}
			catch ( Exception $e ) {
				ur_add_notice( apply_filters( 'login_errors', $e->getMessage() ), 'error' );
				do_action( 'user_registration_login_failed' );
			}
		}
	}

	/**
	 * Handle lost password form.
	 */
	public static function process_lost_password() {
		if ( isset( $_POST['ur_reset_password'] ) && isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'lost_password' ) ) {
			$success = UR_Shortcode_My_Account::retrieve_password();

			// If successful, redirect to my account with query arg set
			if ( $success ) {
				wp_redirect( add_query_arg( 'reset-link-sent', 'true', remove_query_arg( array(
					'key',
					'login',
					'reset'
				) ) ) );
				exit;
			}
		}
	}

	/**
	 * Handle reset password form.
	 */
	public static function process_reset_password() {
		$posted_fields = array(
			'ur_reset_password',
			'password_1',
			'password_2',
			'reset_key',
			'reset_login',
			'_wpnonce'
		);

		foreach ( $posted_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				return;
			}
			$posted_fields[ $field ] = $_POST[ $field ];
		}

		if ( ! wp_verify_nonce( $posted_fields['_wpnonce'], 'reset_password' ) ) {
			return;
		}

		$user = UR_Shortcode_My_Account::check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );

		if ( $user instanceof WP_User ) {
			if ( empty( $posted_fields['password_1'] ) ) {
				ur_add_notice( __( 'Please enter your password.', 'user-registration' ), 'error' );
			}

			if ( $posted_fields['password_1'] !== $posted_fields['password_2'] ) {
				ur_add_notice( __( 'Passwords do not match.', 'user-registration' ), 'error' );
			}

			$errors = new WP_Error();

			do_action( 'validate_password_reset', $errors, $user );

			ur_add_wp_error_notices( $errors );

			if ( 0 === ur_notice_count( 'error' ) ) {
				UR_Shortcode_My_Account::reset_password( $user, $posted_fields['password_1'] );

				do_action( 'user_registration_reset_password', $user );

				wp_redirect( add_query_arg( 'password-reset', 'true', ur_get_page_permalink( 'myaccount' ) ) );
				exit;
			}
		}
	}
}

UR_Form_Handler::init();
