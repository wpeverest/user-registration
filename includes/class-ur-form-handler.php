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

		if ( 'no' === get_option( 'user_registration_ajax_form_submission_on_edit_profile', 'no' ) ) {
			add_action( 'template_redirect', array( __CLASS__, 'save_profile_details' ) );
		}

		add_action( 'template_redirect', array( __CLASS__, 'save_change_password' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'process_login' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_lost_password' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_reset_password' ), 20 );
		add_action( 'user_registration_before_customer_login_form', array( __CLASS__, 'export_confirmation_request' ) );
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
	 *
	 * @return mixed
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

		if ( has_action( 'uraf_profile_picture_buttons' ) ) {
			if ( isset( $_POST['profile_pic_url'] ) && ! empty( $_POST['profile_pic_url'] ) ) {
				update_user_meta( $user_id, 'user_registration_profile_pic_url', $_POST['profile_pic_url'] );
			}
		} else {
			if ( isset( $_FILES['profile-pic'] ) && $_FILES['profile-pic']['size'] ) {

				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				$upload           = $_FILES['profile-pic'];
				$upload_overrides = array(
					'action' => 'save_profile_details',
				);
				$uploaded         = wp_handle_upload( $upload, $upload_overrides );

				if ( $uploaded && ! isset( $uploaded['error'] ) ) {
					$image = wp_get_image_editor( $uploaded['file'] );

					if ( ! is_wp_error( $image ) ) {
						$image->resize( 150, 150, true );
						$image->save( $uploaded['file'] );
					}
					update_user_meta( $user_id, 'user_registration_profile_pic_url', $uploaded['url'] );
				} else {
					ur_add_notice( $uploaded['error'], 'error' );
				}
			} elseif ( UPLOAD_ERR_NO_FILE !== $_FILES['profile-pic']['error'] ) {

				switch ( $_FILES['profile-pic']['error'] ) {
					case UPLOAD_ERR_INI_SIZE:
						ur_add_notice( __( 'File size exceed, please check your file size.', 'user-registration' ), 'error' );
						break;
					default:
						ur_add_notice( __( 'Something went wrong while uploading, please contact your site administrator.', 'user-registration' ), 'error' );
						break;
				}
			} elseif ( empty( $_POST['profile-pic-url'] ) ) {
				$upload_dir  = wp_upload_dir();
				$profile_url = get_user_meta( $user_id, 'user_registration_profile_pic_url', true );

				// Check if profile already set?
				if ( $profile_url ) {

					// Then delete file and user meta.
					$profile_url = $upload_dir['basedir'] . explode( '/uploads', $profile_url )[1];

					if ( ! empty( $profile_url ) && file_exists( $profile_url ) ) {
						@unlink( $profile_url );
					}
					delete_user_meta( $user_id, 'user_registration_profile_pic_url' );
				}
			}
		}

		$form_id_array = get_user_meta( $user_id, 'ur_form_id' );
		$form_id       = 0;

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
				case 'checkbox':
					if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
						$_POST[ $key ] = $_POST[ $key ];
					} else {
						$_POST[ $key ] = (int) isset( $_POST[ $key ] );
					}
					break;
				default:
					$_POST[ $key ] = isset( $_POST[ $key ] ) ? ur_clean( $_POST[ $key ] ) : '';
					break;
			}

			// Hook to allow modification of value.
			$_POST[ $key ] = apply_filters( 'user_registration_process_myaccount_field_' . $key, $_POST[ $key ] );

			$disabled = false;
			if ( isset( $field['custom_attributes'] ) && isset( $field['custom_attributes']['readonly'] ) && isset( $field['custom_attributes']['disabled'] ) ) {
				if ( 'readonly' === $field['custom_attributes']['readonly'] || 'disabled' === $field['custom_attributes']['disabled'] ) {
					$disabled = true;
				}
			}

			// Validation: Required fields.
			if ( ! empty( $field['required'] ) && empty( $_POST[ $key ] ) && ! $disabled ) {
				ur_add_notice( sprintf( __( '%s is a required field.', 'user-registration' ), $field['label'] ), 'error' );
			}

			if ( 'user_email' === $field['field_key'] ) {
				do_action( 'user_registration_validate_email_whitelist', $_POST[ $key ], '' );

				// Check if email already exists before updating user details.
				if ( email_exists( $_POST[ $key ] ) === 1 ) {
					ur_add_notice( __( 'Email already exists', 'user-registration' ), 'error' );
				}
			}

			if ( ! empty( $_POST[ $key ] ) ) {

				// Validation rules.
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'email':
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

					if ( $new_key === 'display_name' ) {
						$user_data['display_name'] = $_POST[ $key ];
					} else {
						$user_data[ $new_key ] = $_POST[ $key ];
					}
				} else {
					$update_key = $key;

					if ( in_array( $new_key, ur_get_registered_user_meta_fields() ) ) {
						$update_key = str_replace( 'user_', '', $new_key );
					}
					$disabled = isset( $field['custom_attributes']['disabled'] ) ? $field['custom_attributes']['disabled'] : '';

					if ( $disabled !== 'disabled' ) {
						update_user_meta( $user_id, $update_key, $_POST[ $key ] );
					}
				}
			}

			if ( count( $user_data ) > 0 ) {
				$user_data['ID'] = get_current_user_id();
				wp_update_user( $user_data );
			}

			ur_add_notice( __( 'User profile updated successfully.', 'user-registration' ) );

			do_action( 'user_registration_save_profile_details', $user_id, $form_id );

			wp_safe_redirect( ur_get_endpoint_url( 'edit-profile', '', ur_get_page_permalink( 'myaccount' ) ) );
			exit;
		}
	}

	/**
	 * @deprecated 1.4.1
	 * @param $user_id
	 * @return void
	 */
	public function save_account_details( $user_id ) {
		ur_deprecated_function( 'UR_Form_Handler::save_account_details', '1.4.1', 'UR_Form_Handler::save_change_password' );
	}

	/**
	 * Save the password and redirect back to the my account page.
	 */
	public static function save_change_password() {
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_change_password' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_change_password' ) ) {
			return;
		}

		$errors = new WP_Error();
		$user   = new stdClass();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ( $user->ID <= 0 ) {
			return;
		}

		$pass_cur                = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : '';
		$pass1                   = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
		$pass2                   = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';
		$save_pass               = true;
		$bypass_current_password = apply_filters( 'user_registration_save_account_bypass_current_password', false );

		if ( empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			ur_add_notice( __( 'Please fill out all password fields.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( ! $bypass_current_password && empty( $pass_cur ) ) {
			ur_add_notice( __( 'Please enter your current password.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( empty( $pass1 ) ) {
			ur_add_notice( __( 'Please enter your new password.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( empty( $pass2 ) ) {
			ur_add_notice( __( 'Please re-enter your password.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( $pass1 !== $pass2 ) {
			ur_add_notice( __( 'New passwords do not match.', 'user-registration' ), 'error' );
			$save_pass = false;
		} elseif ( ! $bypass_current_password && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
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

			ur_add_notice( __( 'Password changed successfully.', 'user-registration' ) );

			do_action( 'user_registration_save_account_details', $user->ID );

			wp_safe_redirect( ur_get_page_permalink( 'myaccount' ) );
			exit;
		}
	}

	/**
	 * Process the login form.
	 */
	public static function process_login() {

		// Custom error messages.
		$messages = array(
			'username_is_required' => get_option( 'user_registration_message_username_required', __( 'Username is required.', 'user-registration' ) ),
			'empty_password'       => get_option( 'user_registration_message_empty_password', null ),
			'invalid_username'     => get_option( 'user_registration_message_invalid_username', null ),
			'unknown_email'        => get_option( 'user_registration_message_unknown_email', __( 'A user could not be found with this email address.', 'user-registration' ) ),
			'pending_approval'     => get_option( 'user_registration_message_pending_approval', null ),
			'denied_access'        => get_option( 'user_registration_message_denied_account', null ),
		);

		$nonce_value     = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value     = isset( $_POST['user-registration-login-nonce'] ) ? $_POST['user-registration-login-nonce'] : $nonce_value;
		$recaptcha_value = isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '';

		$recaptcha_enabled = get_option( 'user_registration_login_options_enable_recaptcha', 'no' );
		$recaptcha_version = get_option( 'user_registration_integration_setting_recaptcha_version' );
		$secret_key        = 'v3' === $recaptcha_version ? get_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' ) : get_option( 'user_registration_integration_setting_recaptcha_site_secret' );

		if ( ! empty( $_POST['login'] ) && wp_verify_nonce( $nonce_value, 'user-registration-login' ) ) {

			try {
				$creds = array(
					'user_password' => $_POST['password'],
					'remember'      => isset( $_POST['rememberme'] ),
				);

				$username         = trim( $_POST['username'] );
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'user_registration_process_login_errors', $validation_error, $_POST['username'], $_POST['password'] );

				if ( 'yes' == $recaptcha_enabled || '1' == $recaptcha_enabled ) {
					if ( ! empty( $recaptcha_value ) ) {

						$data = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_value );
						$data = json_decode( wp_remote_retrieve_body( $data ) );

						if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_recaptcha_v3_threshold', 0.5 ) ) ) {
							throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . __( 'Error on google reCaptcha. Contact your site administrator.', 'user-registration' ) );
						}
					} else {
						throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . get_option( 'user_registration_form_submission_error_message_recaptcha', __( 'Captcha code error, please try again.', 'user-registration' ) ) );
					}
				}

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . $validation_error->get_error_message() );
				}

				if ( empty( $username ) ) {
					throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . $messages['username_is_required'] );
				}

				if ( is_email( $username ) && apply_filters( 'user_registration_get_username_from_email', true ) ) {
					$user = get_user_by( 'email', $username );

					if ( isset( $user->user_login ) ) {
						$creds['user_login'] = $user->user_login;
					} else {
						throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . $messages['unknown_email'] );
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
					// Set custom error messages.
					if ( ! empty( $user->errors['empty_password'] ) && ! empty( $messages['empty_password'] ) ) {
						$user->errors['empty_password'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['empty_password'] );
					}
					if ( ! empty( $user->errors['invalid_username'] ) && ! empty( $messages['invalid_username'] ) ) {
						$user->errors['invalid_username'][0] = $messages['invalid_username'];
					}
					if ( ! empty( $user->errors['pending_approval'] ) && ! empty( $messages['pending_approval'] ) ) {
						$user->errors['pending_approval'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['pending_approval'] );
					}
					if ( ! empty( $user->errors['denied_access'] ) && ! empty( $messages['denied_access'] ) ) {
						$user->errors['denied_access'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['denied_access'] );
					}

					$message = $user->get_error_message();
					$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
					throw new Exception( $message );
				} else {
					if ( in_array( 'administrator', $user->roles ) && 'yes' === get_option( 'user_registration_login_options_prevent_core_login', 'no' ) ) {
						$redirect = admin_url();
					} else {
						if ( ! empty( $_POST['redirect'] ) ) {
							$redirect = $_POST['redirect'];
						} elseif ( wp_get_raw_referer() ) {
							$redirect = wp_get_raw_referer();
						} else {
							$redirect = get_home_url();
						}
					}

					wp_redirect( wp_validate_redirect( apply_filters( 'user_registration_login_redirect', $redirect, $user ), $redirect ) );
					exit;
				}
			} catch ( Exception $e ) {
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
				wp_redirect(
					add_query_arg(
						'reset-link-sent',
						'true',
						remove_query_arg(
							array(
								'key',
								'login',
								'reset',
							)
						)
					)
				);
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
			'_wpnonce',
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

	/**
	 * Handle Export Personal data confirmation request.
	 */
	public static function export_confirmation_request() {
		if ( isset( $_REQUEST['action'] ) && 'confirmaction' === $_REQUEST['action'] ) {
			if ( ! isset( $_GET['request_id'] ) ) {
				return;
			}

			$request_id = (int) $_GET['request_id'];

			if ( isset( $_GET['confirm_key'] ) ) {
				$key    = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
				$result = wp_validate_user_request_key( $request_id, $key );
			} else {
				$result = new WP_Error( 'invalid_key', __( 'Invalid Key', 'user-registration' ) );
			}

			if ( is_wp_error( $result ) ) {
				ur_add_notice( $result->get_error_message(), 'error' );
				ur_print_notices();
				return;
			}

			do_action( 'user_request_action_confirmed', $request_id );

			$request = wp_get_user_request( $request_id );

			if ( $request && in_array( $request->action_name, _wp_privacy_action_request_types(), true ) ) {
				if ( 'export_personal_data' === $request->action_name ) {
					$message = apply_filters( 'user_registration_export_personal_data_confirmation_message', __( 'Thanks for confirming your export request.', 'user-registration' ) );
				} elseif ( 'remove_personal_data' === $request->action_name ) {
					$message = apply_filters( 'user_registration_remove_personal_data_confirmation_message', __( 'Thanks for confirming your erasure request.', 'user-registration' ) );
				}
				ur_add_notice( $message, 'success' );
				ur_print_notices();
			}
		}
	}

	/**
	 * Get Form data.
	 *
	 * @param int   $id Form ID.
	 * @param array $args Form Arguments.
	 *
	 * @since 1.7.2
	 */
	public function get_form( $id = '', $args = array() ) {
		$forms = array();
		$args  = apply_filters( 'user_registration_get_form_args', $args );

		if ( is_numeric( $id ) ) {
			$the_post = get_post( absint( $id ) );

			if ( $the_post && 'user_registration' === $the_post->post_type ) {
				if ( isset( $args['publish'] ) ) {
					if ( ( $args['publish'] && 'publish' === $the_post->post_type ) || ( ! $args['publish'] && 'publish' !== $the_post->post_type ) ) {
						return array();
					}
				}
				$forms = empty( $args['content_only'] ) ? $the_post : json_decode( $the_post->post_content );
			}
		} else {
			// No ID provided, get multiple forms.
			$defaults = array(
				'post_type'     => 'user_registration',
				'orderby'       => 'ID',
				'order'         => 'DESC',
				'no_found_rows' => true,
				'nopaging'      => true,
			);

			$args = wp_parse_args( $args, $defaults );

			$args['post_type'] = 'user_registration';

			$forms = get_posts( $args );
		}

		return $forms;
	}
}

UR_Form_Handler::init();
