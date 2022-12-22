<?php
/**
 * Handle frontend forms.
 *
 * @class       UR_Form_Handler
 * @version     1.0.0
 * @package     UserRegistration/Classes/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Handler Class.
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
		$ur_account_page_exists      = ur_get_page_id( 'myaccount' ) > 0;
		$is_ur_login_or_account_page = is_ur_account_page();

		if ( ! $ur_account_page_exists ) {
			$is_ur_login_or_account_page = is_ur_login_page();
		}

		if ( $is_ur_login_or_account_page && ! empty( $_GET['key'] ) && ! empty( $_GET['login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$value = sprintf( '%s:%s', sanitize_text_field( wp_unslash( $_GET['login'] ) ), sanitize_text_field( wp_unslash( $_GET['key'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
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

		global $wp;
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== strtoupper( wp_unslash( sanitize_key( $_SERVER['REQUEST_METHOD'] ) ) ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_profile_details' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_profile_details' ) ) { //phpcs:ignore
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}
		if ( has_action( 'uraf_profile_picture_buttons' ) ) {
			$profile_picture_attachment_id = isset( $_POST['profile_pic_url'] ) ? absint( wp_unslash( $_POST['profile_pic_url'] ) ) : '';

			if ( '' === $profile_picture_attachment_id ) {
				update_user_meta( $user_id, 'user_registration_profile_pic_url', '' );
			} else {
					update_user_meta( $user_id, 'user_registration_profile_pic_url', $profile_picture_attachment_id );
			}
		} else {
			if ( isset( $_FILES['profile-pic'] ) ) {

				if ( isset( $_FILES['profile-pic'] ) && ! empty( $_FILES['profile-pic']['size'] ) ) {

					$upload = $_FILES['profile-pic']; // phpcs:ignore

					$upload_dir  = wp_upload_dir();
					$upload_path = apply_filters( 'user_registration_profile_pic_upload_url', $upload_dir['basedir'] . '/user_registration_uploads/profile-pictures' ); /*Get path of upload dir of WordPress*/

					// Checks if the upload directory exists and create one if not.
					if ( ! file_exists( $upload_path ) ) {
						wp_mkdir_p( $upload_path );
					}

					if ( ! wp_is_writable( $upload_path ) ) {  /*Check if upload dir is writable*/
						ur_add_notice( 'Upload path permission deny.', 'error' );
					}

					$upload_path = $upload_path . '/';
					$file_ext    = strtolower( pathinfo( $upload['name'], PATHINFO_EXTENSION ) );

					$file_name = wp_unique_filename( $upload_path, $upload['name'] );

					$file_path = $upload_path . sanitize_file_name( $file_name );
					// valid extension for image.
					$valid_extensions     = 'image/jpeg,image/jpg,image/gif,image/png';
					$form_id              = ur_get_form_id_by_userid( $user_id );
					$field_data           = ur_get_field_data_by_field_name( $form_id, 'profile_pic_url' );
					$valid_extensions     = isset( $field_data['advance_setting']->valid_file_type ) ? implode( ', ', $field_data['advance_setting']->valid_file_type ) : $valid_extensions;
					$valid_extension_type = explode( ',', $valid_extensions );
					$valid_ext            = array();

					foreach ( $valid_extension_type as $key => $value ) {
						$image_extension   = explode( '/', $value );
						$valid_ext[ $key ] = $image_extension[1];
					}

					$src_file_name  = isset( $upload['name'] ) ? $upload['name'] : '';
					$file_extension = strtolower( pathinfo( $src_file_name, PATHINFO_EXTENSION ) );

					// Validates if the uploaded file has the acceptable extension.
					if ( ! in_array( $file_extension, $valid_ext ) ) {
						ur_add_notice( __( 'Invalid file type, please contact with site administrator.', 'user-registration' ), 'error' );
					} else {
						if ( move_uploaded_file( $upload['tmp_name'], $file_path ) ) {

							$attachment_id = wp_insert_attachment(
								array(
									'guid'           => $file_path,
									'post_mime_type' => $file_ext,
									'post_title'     => preg_replace( '/\.[^.]+$/', '', sanitize_file_name( $file_name ) ),
									'post_content'   => '',
									'post_status'    => 'inherit',
								),
								$file_path
							);

							if ( is_wp_error( $attachment_id ) ) {

								wp_send_json_error(
									array(

										'message' => $attachment_id->get_error_message(),
									)
								);
							}

							include_once ABSPATH . 'wp-admin/includes/image.php';

							// Generate and save the attachment metas into the database.
							wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );

							update_user_meta( $user_id, 'user_registration_profile_pic_url', $attachment_id );

						} else {
							ur_add_notice( 'File cannot be uploaded.', 'error' );
						}
					}
				} elseif ( isset( $_FILES['profile-pic']['error'] ) && UPLOAD_ERR_NO_FILE !== $_FILES['profile-pic']['error'] ) {

					switch ( isset( $_FILES['profile-pic']['error'] ) && $_FILES['profile-pic']['error'] ) {
						case UPLOAD_ERR_INI_SIZE:
							ur_add_notice( esc_html__( 'File size exceed, please check your file size.', 'user-registration' ), 'error' );
							break;
						default:
							ur_add_notice( esc_html__( 'Something went wrong while uploading, please contact your site administrator.', 'user-registration' ), 'error' );
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
		}

		$form_id_array = get_user_meta( $user_id, 'ur_form_id' );
		$form_id       = 0;

		if ( isset( $form_id_array[0] ) ) {
			$form_id = $form_id_array[0];
		}

		$profile = user_registration_form_data( $user_id, $form_id );

		foreach ( $profile as $key => $field ) {
			if ( isset( $field['field_key'] ) ) {
				if ( ! isset( $field['type'] ) ) {
					$field['type'] = 'text';
				}

				// Get Value.
				switch ( $field['type'] ) {
					case 'checkbox':
						if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
							$_POST[ $key ] = wp_unslash( $_POST[ $key ] ); // phpcs:ignore
						} else {
							$_POST[ $key ] = (int) isset( $_POST[ $key ] );
						}
						break;

					case 'wysiwyg':
						if ( isset( $_POST[ $key ] ) ) {
							$_POST[ $key ] = sanitize_text_field( htmlentities( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore
						} else {
							$_POST[ $key ] = '';
						}
						break;

					case 'email':
						if ( isset( $_POST[ $key ] ) ) {
							$_POST[ $key ] = sanitize_email( wp_unslash( $_POST[ $key ] ) );
						} else {
							$user_data     = get_userdata( $user_id );
							$_POST[ $key ] = $user_data->data->user_email;
						}
						break;

					default:
						$_POST[ $key ] = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						break;
				}

				// Hook to allow modification of value.
				$_POST[ $key ] = apply_filters( 'user_registration_process_myaccount_field_' . $key, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$disabled = false;
				if ( isset( $field['custom_attributes'] ) && isset( $field['custom_attributes']['readonly'] ) && isset( $field['custom_attributes']['disabled'] ) ) {
					if ( 'readonly' === $field['custom_attributes']['readonly'] || 'disabled' === $field['custom_attributes']['disabled'] ) {
						$disabled = true;
					}
				}

				$urcl_hide_fields = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array(); //phpcs:ignore;
				$new_key          = str_replace( 'user_registration_', '', $key );
				// Validation: Required fields.
				if ( ! in_array( $new_key, $urcl_hide_fields, true ) && 'yes' == $field['required'] && empty( $_POST[ $key ] ) && ! $disabled ) {
					/* translators: %s - Field Label */
					ur_add_notice( sprintf( esc_html__( '%s is a required field.', 'user-registration' ), $field['label'] ), 'error' );
				}

				if ( 'user_email' === $field['field_key'] ) {
					do_action( 'user_registration_validate_email_whitelist', sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), '' );

					// Check if email already exists before updating user details.
					if ( email_exists( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) && email_exists( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) !== $user_id ) {
						ur_add_notice( esc_html__( 'Email already exists', 'user-registration' ), 'error' );
					}
				}

				if ( ! empty( $_POST[ $key ] ) ) {

					// Validation rules.
					if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
						foreach ( $field['validate'] as $rule ) {
							switch ( $rule ) {
								case 'email':
									$_POST[ $key ] = strtolower( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );

									if ( ! is_email( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) ) {
										/* translators: %s - Field Label */
										ur_add_notice( wp_kses_post( sprintf( __( '%s is not a valid email address.', 'user-registration' ), '<strong>' . $field['label'] . '</strong>' ), 'error' ) );
									}

									break;
							}
						}
					}
				}
				// Action to add extra validation to edit profile fields.
				do_action( 'user_registration_validate_' . $key, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			}
		}

		do_action( 'user_registration_after_save_profile_validation', $user_id, $profile );

		if ( 0 === ur_notice_count( 'error' ) ) {
			$user_data = array();
			foreach ( $profile as $key => $field ) {
				if ( isset( $field['field_key'] ) ) {
					$new_key = str_replace( 'user_registration_', '', $key );

					if ( in_array( $new_key, ur_get_user_table_fields() ) ) {

						if ( 'display_name' === $new_key ) {
							$user_data['display_name'] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
						} else {
							$user_data[ $new_key ] = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						}
					} else {
						$update_key = $key;

						if ( in_array( $new_key, ur_get_registered_user_meta_fields() ) ) {
							$update_key = str_replace( 'user_', '', $new_key );
						}
						$disabled = isset( $field['custom_attributes']['disabled'] ) ? $field['custom_attributes']['disabled'] : '';
						if ( 'disabled' !== $disabled ) {
							update_user_meta( $user_id, $update_key, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						}
					}
				}
			}

			if ( count( $user_data ) > 0 ) {
				$user_data['ID'] = get_current_user_id();
				wp_update_user( $user_data );
			}

			ur_add_notice( apply_filters( 'user_registration_profile_update_success_message', __( 'User profile updated successfully.', 'user-registration' ) ) );

			do_action( 'user_registration_save_profile_details', $user_id, $form_id );

			wp_safe_redirect( home_url( add_query_arg( array(), $wp->request ) ) );
			exit;
		}
	}

	/**
	 * Save Account Details.
	 *
	 * @deprecated 1.4.1
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function save_account_details( $user_id ) {
		ur_deprecated_function( 'UR_Form_Handler::save_account_details', '1.4.1', 'UR_Form_Handler::save_change_password' );
	}

	/**
	 * Save the password and redirect back to the my account page.
	 */
	public static function save_change_password() {
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) ) {
			return;
		}

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : null;  // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $action ) || 'save_change_password' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_change_password' ) ) { // phpcs:ignore
			return;
		}

		$errors = new WP_Error();
		$user   = new stdClass();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ( $user->ID <= 0 ) {
			return;
		}

		$pass_cur                = ! empty( $_POST['password_current'] ) ? sanitize_text_field( wp_unslash( $_POST['password_current'] ) ) : '';
		$pass1                   = ! empty( $_POST['password_1'] ) ? sanitize_text_field( wp_unslash( $_POST['password_1'] ) ) : '';
		$pass2                   = ! empty( $_POST['password_2'] ) ? sanitize_text_field( wp_unslash( $_POST['password_2'] ) ) : '';
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
		} elseif ( wp_check_password( $pass1, $current_user->user_pass, $current_user->ID ) && $current_user ) {
			ur_add_notice( __( 'New password must not be same as old password', 'user-registration' ), 'error' );
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
	 *
	 * @throws Exception Login errors.
	 */
	public static function process_login() {

		// Custom error messages.
		$messages = array(
			'empty_username'   => get_option( 'user_registration_message_username_required', __( 'Username is required.', 'user-registration' ) ),
			'empty_password'   => get_option( 'user_registration_message_empty_password', null ),
			'invalid_username' => get_option( 'user_registration_message_invalid_username', null ),
			'unknown_email'    => get_option( 'user_registration_message_unknown_email', __( 'A user could not be found with this email address.', 'user-registration' ) ),
			'pending_approval' => get_option( 'user_registration_message_pending_approval', null ),
			'denied_access'    => get_option( 'user_registration_message_denied_account', null ),
		);

		$nonce_value         = isset( $_POST['_wpnonce'] ) ? sanitize_key( $_POST['_wpnonce'] ) : '';
		$nonce_value         = isset( $_POST['user-registration-login-nonce'] ) ? sanitize_key( $_POST['user-registration-login-nonce'] ) : $nonce_value;
		$hcaptca_response    = isset( $_POST['h-captcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['h-captcha-response'] ) ) : '';
		$recaptcha_value     = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : $hcaptca_response;
		$recaptcha_enabled   = get_option( 'user_registration_login_options_enable_recaptcha', 'no' );
		$recaptcha_type      = get_option( 'user_registration_integration_setting_recaptcha_version', 'v2' );
		$invisible_recaptcha = get_option( 'user_registration_integration_setting_invisible_recaptcha_v2', 'no' );

		if ( ! empty( $_POST['login'] ) && wp_verify_nonce( $nonce_value, 'user-registration-login' ) ) {

			try {
				$creds = array(
					'user_password' => isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '',
					'remember'      => isset( $_POST['rememberme'] ),
				);

				$username         = isset( $_POST['username'] ) ? trim( sanitize_user( wp_unslash( $_POST['username'] ) ) ) : '';
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'user_registration_process_login_errors', $validation_error, sanitize_user( wp_unslash( $_POST['username'] ) ), sanitize_user( wp_unslash( $_POST['password'] ) ) );

				if ( 'yes' == $recaptcha_enabled || '1' == $recaptcha_enabled ) {
					if ( ! empty( $recaptcha_value ) ) {
						if ( 'hCaptcha' === $recaptcha_type ) {
							$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret_hcaptcha' );
							$data       = wp_remote_get( 'https://hcaptcha.com/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_value );
							$data       = json_decode( wp_remote_retrieve_body( $data ) );

							if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_hcaptcha_threshold', 0.5 ) ) ) {
								throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . __( 'Error on hCaptcha. Contact your site administrator.', 'user-registration' ) );
							}
						} else {
							if ( 'v2' === $recaptcha_type && 'no' === $invisible_recaptcha ) {
								$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key' );
								$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret' );
							} elseif ( 'v2' === $recaptcha_type && 'yes' === $invisible_recaptcha ) {
								$site_key   = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_key' );
								$secret_key = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_secret' );
							} elseif ( 'v3' === $recaptcha_type ) {
								$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key_v3' );
								$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' );
							}
							$data = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_value );
							$data = json_decode( wp_remote_retrieve_body( $data ) );
							if ( empty( $data->success ) || ( isset( $data->score ) && $data->score <= get_option( 'user_registration_integration_setting_recaptcha_threshold_score_v3', apply_filters( 'user_registration_recaptcha_v3_threshold', 0.5 ) ) ) ) {
								throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . __( 'Error on google reCaptcha. Contact your site administrator.', 'user-registration' ) );
							}
						}
					} else {
						throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . get_option( 'user_registration_form_submission_error_message_recaptcha', __( 'Captcha code error, please try again.', 'user-registration' ) ) );
					}
				}

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . $validation_error->get_error_message() );
				}

				if ( empty( $username ) ) {
					throw new Exception( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . $messages['empty_username'] );
				}

				if ( is_email( $username ) && apply_filters( 'user_registration_get_username_from_email', true ) ) {
					$user = get_user_by( 'email', $username );

					if ( isset( $user->user_login ) ) {
						$creds['user_login'] = $user->user_login;
					} else {
						if ( empty( $messages['unknown_email'] ) ) {
							$messages['unknown_email'] = __( 'A user could not be found with this email address.', 'user-registration' );
						}

						throw new Exception( '<strong>' . __( 'ERROR: ', 'user-registration' ) . '</strong>' . $messages['unknown_email'] );
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

				// To check the specific login.
				if ( 'email' === get_option( 'user_registration_general_setting_login_options_with', array() ) ) {
					$user_data           = get_user_by( 'email', $username );
					$creds['user_login'] = isset( $user_data->user_email ) ? $user_data->user_email : is_email( $username );
				} elseif ( 'username' === get_option( 'user_registration_general_setting_login_options_with', array() ) ) {
					$user_data           = get_user_by( 'login', $username );
					$creds['user_login'] = isset( $user_data->user_login ) ? $user_data->user_login : ! is_email( $username );
				} else {
					$creds['user_login'] = $username;
				}

				// Perform the login.
				$user = wp_signon( apply_filters( 'user_registration_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					// Set custom error messages.
					if ( ! empty( $user->errors['empty_username'] ) && ! empty( $messages['empty_username'] ) ) {
						$user->errors['empty_username'][0] = sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['empty_username'] );
					}
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
							$redirect = $_POST['redirect']; //phpcs:ignore
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
		if ( isset( $_POST['ur_reset_password'] ) && isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'lost_password' ) ) {
			$success = UR_Shortcode_My_Account::retrieve_password();

			// If successful, redirect to my account with query arg set.
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
			$posted_fields[ $field ] = $_POST[ $field ]; // phpcs:ignore
		}

		if ( ! wp_verify_nonce( $posted_fields['_wpnonce'], 'reset_password' ) ) {
			return;
		}

		$user = UR_Shortcode_My_Account::check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );

		if ( $user instanceof WP_User ) {
			if ( empty( $posted_fields['password_1'] ) ) {
				ur_add_notice( esc_html__( 'Please enter your password.', 'user-registration' ), 'error' );
			}

			if ( $posted_fields['password_1'] !== $posted_fields['password_2'] ) {
				ur_add_notice( esc_html__( 'Passwords do not match.', 'user-registration' ), 'error' );
			}

			if ( wp_check_password( $posted_fields['password_1'], $user->user_pass, $user->ID ) ) {
				ur_add_notice( esc_html__( 'New password must not be same as old password.', 'user-registration' ), 'error' );
			}
			$errors = new WP_Error();

			do_action( 'validate_password_reset', $errors, $user );

			ur_add_wp_error_notices( $errors );

			if ( 0 === ur_notice_count( 'error' ) ) {
				UR_Shortcode_My_Account::reset_password( $user, $posted_fields['password_1'] );

				do_action( 'user_registration_reset_password', $user );

				$ur_account_page_exists   = ur_get_page_id( 'myaccount' ) > 0;
				$ur_login_or_account_page = ur_get_page_permalink( 'myaccount' );

				if ( ! $ur_account_page_exists ) {
					$ur_login_or_account_page = ur_get_page_permalink( 'login' );
				}

				wp_redirect( add_query_arg( 'password-reset', 'true', $ur_login_or_account_page ) );
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
				$the_post->post_content = str_replace( '"noopener noreferrer"', "'noopener noreferrer'", $the_post->post_content );
				$the_post->post_content = str_replace( '"noopener"', "'noopener'", $the_post->post_content );

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

	/**
	 * Create and return a dictionary of field_id->field_label for all form fields.
	 *
	 * @param [int] $form_id Form Id.
	 * @param array $args Extra arguments.
	 * @return array
	 *
	 * @since 2.2.3
	 */
	public function get_form_fields( $form_id, $args = array() ) {
		$hide_fields = array(
			'user_confirm_password',
			'user_confirm_email',
		);

		$fields_dict = array();

		if ( is_numeric( $form_id ) ) {

			$form_data = $this->get_form( $form_id, $args );

			foreach ( $form_data as $sec ) {
				foreach ( $sec as $fields ) {
					foreach ( $fields as $field ) {
						$field_id    = $field->general_setting->field_name;
						$field_label = $field->general_setting->label;
						if ( ! in_array( $field_id, $hide_fields, true ) ) {
							$fields_dict[ $field_id ] = $field_label;
						}
					}
				}
			}

			if ( isset( $args['hide_fields'] ) && true === $args['hide_fields'] ) {
				foreach ( $hide_fields as $hide_field ) {
					unset( $fields_dict[ $hide_field ] );
				}
			}
		}
		return $fields_dict;
	}


	/**
	 * Create new form.
	 *
	 * @since  2.2.4
	 * @param  string $title    Form title.
	 * @param  string $template Form template.
	 * @param  array  $args     Form Arguments.
	 * @param  array  $data     Additional data.
	 * @return int|bool Form ID on successful creation else false.
	 */
	public function create( $title = '', $template = 'blank', $args = array(), $data = array() ) {

		if ( empty( $title ) ) {
			return false;
		}

		$args = apply_filters( 'user_registration_create_form_args', $args, $data );

		// Prevent content filters from corrupting JSON in post_content.
		$has_kses = ( false !== has_filter( 'content_save_pre', 'wp_filter_post_kses' ) );
		if ( $has_kses ) {
			kses_remove_filters();
		}
		$has_targeted_link_rel_filters = ( false !== has_filter( 'content_save_pre', 'wp_targeted_link_rel' ) );
		if ( $has_targeted_link_rel_filters ) {
			wp_remove_targeted_link_rel_filters();
		}

		$templates = ur_get_json_file_contents( 'assets/extensions-json/templates/all_templates.json' );

		$form_data = array();

		if ( ! empty( $templates ) ) {
			foreach ( $templates->templates as $template_data ) {
				if ( $template_data->slug === $template && 'blank' !== $template_data->slug ) {
					$form_data                            = json_decode( base64_decode( $template_data->settings ), true );
					$form_data['form_post']['post_title'] = $title;
				}
			}
		}

		// check for non empty post data array.
		$form_data['form_post'] = isset( $form_data['form_post'] ) ? $form_data['form_post'] : array();
		$form_data['form_post'] = (object) $form_data['form_post'];

		$form_data = (object) $form_data;

		// If Form Title already exist concat it with imported tag.
		$args  = array( 'post_type' => 'user_registration' );
		$forms = get_posts( $args );
		foreach ( $forms as $key => $form_obj ) {
			if ( isset( $form_data->form_post->post_title ) && ( $form_data->form_post->post_title === $form_obj->post_title ) ) {
				$form_data->form_post->post_title = sanitize_text_field( $title );
				break;
			}
		}

		$form_content = (array) $form_data->form_post;

		if ( empty( $form_content ) ) {
			$post_content         = '[[[{"field_key":"user_login","general_setting":{"label":"Username","field_name":"user_login","placeholder":"","required":"yes"},"advance_setting":{}},{"field_key":"user_pass","general_setting":{"label":"User Password","field_name":"user_pass","placeholder":"","required":"yes"},"advance_setting":{}}],[{"field_key":"user_email","general_setting":{"label":"User Email","field_name":"user_email","placeholder":"","required":"yes"},"advance_setting":{}},{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","field_name":"user_confirm_password","placeholder":"","required":"yes"},"advance_setting":{}}]]]';
			$form_data->form_post = array(
				'post_type'      => 'user_registration',
				'post_title'     => $title,
				'post_content'   => $post_content,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			);
		}

		$form_id = wp_insert_post( $form_data->form_post );

		// Check for any error while inserting.
		if ( is_wp_error( $form_id ) ) {
			return $form_id;
		}
		if ( $form_id ) {

			// check for non empty post_meta array.
			if ( ! empty( $form_data->form_post_meta ) ) {
				$form_data->form_post_meta = (object) $form_data->form_post_meta;

				$all_roles = ur_get_default_admin_roles();

				foreach ( $form_data->form_post_meta  as $meta_key => $meta_value ) {

					// If user role does not exists in new site then set default as subscriber.
					if ( 'user_registration_form_setting_default_user_role' === $meta_key ) {
						$meta_value = array_key_exists( $meta_value, $all_roles ) ? $meta_value : 'subscriber';
					}
					add_post_meta( $form_id, $meta_key, $meta_value );
				}
			}
		}

		// Restore removed content filters.
		if ( $has_kses ) {
			kses_init_filters();
		}
		if ( $has_targeted_link_rel_filters ) {
			wp_init_targeted_link_rel_filters();
		}

		do_action( 'user_registration_create_form', $form_id, $form_data, $data );

		return $form_id;
	}
}

UR_Form_Handler::init();
