<?php
/**
 * UserRegistration UR_AJAX
 *
 * AJAX Event Handler
 *
 * @class    UR_AJAX
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_AJAX Class
 */
class UR_AJAX {

	/**
	 * Field key array
	 *
	 * @var array
	 */
	private static $field_key_aray = array();
	/**
	 * Check whether is field key pass
	 *
	 * @var bool
	 */
	private static $is_field_key_pass = true;
	/**
	 * Field key value
	 *
	 * @var array
	 */
	private static $failed_key_value = array();

	/**
	 * Initialization of ajax.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'user_input_dropped'     => true,
			'form_save_action'       => true,
			'user_form_submit'       => true,
			'update_profile_details' => true,
			'profile_pic_upload'     => true,
			'profile_pic_remove'     => true,
			'ajax_login_submit'      => true,
			'send_test_email'        => true,
			'deactivation_notice'    => false,
			'rated'                  => false,
			'dashboard_widget'       => false,
			'dismiss_notice'         => false,
			'import_form_action'     => false,
			'template_licence_check' => false,
			'install_extension'      => false,
			'create_form'            => true,
			'cancel_email_change'    => false,
			'email_setting_status'   => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_user_registration_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_user_registration_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get Post data on frontend form submit
	 *
	 * @return void
	 */
	public static function user_form_submit() {

		$current_user_capability = apply_filters( 'ur_registration_user_capability', 'create_users' );

		if ( is_user_logged_in() && ! current_user_can( 'administrator' ) && ! current_user_can( $current_user_capability ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are already logged in.', 'user-registration' ),
				)
			);
		}

		if ( ! check_ajax_referer( 'user_registration_form_data_save_nonce', 'security', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration' ),
				)
			);
		}

		$form_id             = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$nonce               = isset( $_POST['ur_frontend_form_nonce'] ) ? wp_unslash( sanitize_key( $_POST['ur_frontend_form_nonce'] ) ) : '';
		$captcha_response    = isset( $_POST['captchaResponse'] ) ? ur_clean( wp_unslash( $_POST['captchaResponse'] ) ) : ''; //phpcs:ignore
		$flag                = wp_verify_nonce( $nonce, 'ur_frontend_form_id-' . $form_id );
		$recaptcha_enabled   = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_enable_recaptcha_support', 'no' );
		$recaptcha_type      = get_option( 'user_registration_integration_setting_recaptcha_version', 'v2' );
		$invisible_recaptcha = get_option( 'user_registration_integration_setting_invisible_recaptcha_v2', 'no' );

		if ( 'v2' === $recaptcha_type && 'no' === $invisible_recaptcha ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret' );
		} elseif ( 'v2' === $recaptcha_type && 'yes' === $invisible_recaptcha ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_key' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_secret' );
		} elseif ( 'v3' === $recaptcha_type ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key_v3' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' );
		} elseif ( 'hCaptcha' === $recaptcha_type ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key_hcaptcha' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret_hcaptcha' );
		}
		if ( ( 'yes' == $recaptcha_enabled || '1' == $recaptcha_enabled ) && ! empty( $site_key ) && ! empty( $secret_key ) ) {
			if ( ! empty( $captcha_response ) ) {
				if ( 'hCaptcha' === $recaptcha_type ) {
					$data = wp_safe_remote_get( 'https://hcaptcha.com/siteverify?secret=' . $secret_key . '&response=' . $captcha_response );
					$data = json_decode( wp_remote_retrieve_body( $data ) );

					if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_hcaptcha_threshold', 0.5 ) ) ) {
						wp_send_json_error(
							array(
								'message' => __( 'Error on hCaptcha. Contact your site administrator.', 'user-registration' ),
							)
						);
					}
				} else {
					$data = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha_response );
					$data = json_decode( wp_remote_retrieve_body( $data ) );

					if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_recaptcha_v3_threshold', 0.5 ) ) ) {
						wp_send_json_error(
							array(
								'message' => __( 'Error on google reCaptcha. Contact your site administrator.', 'user-registration' ),
							)
						);
					}
				}
			} else {
				wp_send_json_error(
					array(
						'message' => get_option( 'user_registration_form_submission_error_message_recaptcha', __( 'Captcha code error, please try again.', 'user-registration' ) ),
					)
				);
			}
		}

		if ( true != $flag || is_wp_error( $flag ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration' ),
				)
			);
		}

		$users_can_register = apply_filters( 'ur_register_setting_override', get_option( 'users_can_register' ) );

		if ( ! is_user_logged_in() ) {

			if ( ! $users_can_register ) {
				wp_send_json_error(
					array(
						'message' => apply_filters( 'ur_register_pre_form_message', __( 'Only administrators can add new users.', 'user-registration' ) ),
					)
				);
			}
		} else {

			$current_user_capability = apply_filters( 'ur_registration_user_capability', 'create_users' );

			if ( ! current_user_can( $current_user_capability ) ) {
				global $wp;

				$user_ID      = get_current_user_id();
				$user         = get_user_by( 'ID', $user_ID );
				$current_url  = home_url( add_query_arg( array(), $wp->request ) );
				$display_name = ! empty( $user->data->display_name ) ? $user->data->display_name : $user->data->user_email;

				wp_send_json_error(
					array(
						/* translators: %s - Link to logout. */
						'message' => apply_filters( 'ur_register_pre_form_message', '<p class="alert" id="ur_register_pre_form_message">' . sprintf( __( 'You are currently logged in as %1$1s. %2$2s', 'user-registration' ), '<a href="#" title="' . $display_name . '">' . $display_name . '</a>', '<a href="' . wp_logout_url( $current_url ) . '" title="' . __( 'Log out of this account.', 'user-registration' ) . '">' . __( 'Logout', 'user-registration' ) . '  &raquo;</a>' ) . '</p>', $user_ID ),
					)
				);
			}
		}

		$form_data = array();

		if ( isset( $_POST['form_data'] ) ) {
			$form_data = json_decode( wp_unslash( $_POST['form_data'] ) );  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		UR_Frontend_Form_Handler::handle_form( $form_data, $form_id );
	}


	/**
	 * Get Post data on frontend form submit
	 *
	 * @return void
	 */
	public static function update_profile_details() {

		if ( ! check_ajax_referer( 'user_registration_profile_details_save_nonce', 'security', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration' ),
				)
			);
		}

		// Current user id.
		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		// Get form id of the form from which current user is registered.
		$form_id_array = get_user_meta( $user_id, 'ur_form_id' );
		$form_id       = 0;

		if ( isset( $form_id_array[0] ) ) {
			$form_id = $form_id_array[0];
		}

		// Make the schema of form data compatible with processing below.
		$form_data    = array();
		$single_field = array();

		if ( isset( $_POST['form_data'] ) ) {
			$form_data = json_decode( wp_unslash( $_POST['form_data'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $form_data as $data ) {
				$single_field[ $data->field_name ] = isset( $data->value ) ? $data->value : '';
				$data->field_name                  = substr( $data->field_name, 18 );
			}
		}

		$profile_picture_attachment_id = isset( $single_field['user_registration_profile_pic_url'] ) ? $single_field['user_registration_profile_pic_url'] : '';

		if ( 'no' === get_option( 'user_registration_disable_profile_picture', 'no' ) ) {

			if ( ! is_numeric( $profile_picture_attachment_id ) ) {
				$profile_picture_attachment_id = attachment_url_to_postid( $profile_picture_attachment_id );
			}

			if ( '' === $profile_picture_attachment_id ) {
				update_user_meta( $user_id, 'user_registration_profile_pic_url', '' );
			} else {
				update_user_meta( $user_id, 'user_registration_profile_pic_url', absint( $profile_picture_attachment_id ) );
			}
		}

		$profile = user_registration_form_data( $user_id, $form_id );

		if ( isset( $profile['user_registration_profile_pic_url'] ) ) {
			unset( $profile['user_registration_profile_pic_url'] );
		}

		foreach ( $profile as $key => $field ) {

			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}
			// Get Value.
			switch ( $field['type'] ) {
				case 'checkbox':
					if ( isset( $single_field[ $key ] ) ) {
						// Serialize values fo checkbox field.
						$single_field[ $key ] = ( json_decode( $single_field[ $key ] ) !== null ) ? json_decode( $single_field[ $key ] ) : sanitize_text_field( $single_field[ $key ] );
					}
					break;
				case 'wysiwyg':
					if ( isset( $single_field[ $key ] ) ) {
						$single_field[ $key ] = sanitize_text_field( htmlentities( $single_field[ $key ] ) );
					} else {
						$single_field[ $key ] = '';
					}
					break;
				default:
					$single_field[ $key ] = isset( $single_field[ $key ] ) ? $single_field[ $key ] : '';
					break;
			}

			// Hook to allow modification of value.
			$single_field[ $key ] = apply_filters( 'user_registration_process_myaccount_field_' . $key, $single_field[ $key ] );

			if ( 'email' === $field['type'] ) {
				do_action( 'user_registration_validate_email_whitelist', $single_field[ $key ], '', $single_field, $form_id );
			}

			if ( 'user_registration_user_email' === $key ) {

				// Check if email already exists before updating user details.
				if ( email_exists( $single_field[ $key ] ) && email_exists( $single_field[ $key ] ) !== $user_id ) {
					wp_send_json_error(
						array(
							'message' => __( 'Email already exists.', 'user-registration' ),
						)
					);
				}
			}

			$disabled = false;
			if ( isset( $field['custom_attributes'] ) && isset( $field['custom_attributes']['readonly'] ) && isset( $field['custom_attributes']['disabled'] ) ) {
				if ( 'readonly' === $field['custom_attributes']['readonly'] || 'disabled' === $field['custom_attributes']['disabled'] ) {
					$disabled = true;
				}
			}

			// Action to add extra validation to edit profile fields.
			do_action( 'user_registration_validate_' . $key, $single_field[ $key ] );

		}// End foreach().

		do_action( 'user_registration_after_save_profile_validation', $user_id, $profile );

		if ( 0 === ur_notice_count( 'error' ) ) {
			$user_data                    = array();
			$is_email_change_confirmation = (bool) apply_filters( 'user_registration_email_change_confirmation', true );
			$email_updated                = false;
			$pending_email                = '';
			$user                         = wp_get_current_user();

			foreach ( $profile as $key => $field ) {
				$new_key = str_replace( 'user_registration_', '', $key );

				if ( $is_email_change_confirmation && 'user_email' === $new_key ) {
					if ( $user ) {
						if ( sanitize_email( wp_unslash( $single_field[ $key ] ) ) !== $user->user_email ) {
							$email_updated = true;
							$pending_email = sanitize_email( wp_unslash( $single_field[ $key ] ) );
						}
						continue;
					}
				}

				if ( in_array( $new_key, ur_get_user_table_fields() ) ) {

					if ( 'display_name' === $new_key ) {
						$user_data['display_name'] = sanitize_text_field( ( $single_field[ $key ] ) );
					} else {
						$user_data[ $new_key ] = sanitize_text_field( $single_field[ $key ] );
					}
				} else {
					$update_key = $key;

					if ( in_array( $new_key, ur_get_registered_user_meta_fields() ) ) {
						$update_key = str_replace( 'user_', '', $new_key );
					}
					$disabled = isset( $field['custom_attributes']['disabled'] ) ? $field['custom_attributes']['disabled'] : '';

					if ( 'disabled' !== $disabled ) {

						update_user_meta( $user_id, $update_key, $single_field[ $key ] );
					}
				}
			}

			if ( count( $user_data ) > 0 ) {
				$user_data['ID'] = get_current_user_id();
				wp_update_user( $user_data );
			}

			$message = apply_filters( 'user_registration_profile_update_success_message', __( 'User profile updated successfully.', 'user-registration' ) );
			do_action( 'user_registration_save_profile_details', $user_id, $form_id );

			$response = array(
				'message' => $message,
			);

			if ( $email_updated ) {
				UR_Form_Handler::send_confirmation_email( $user, $pending_email );
				$response['oldUserEmail'] = $user->user_email;
				/* translators: %s : user email */
				$response['userEmailUpdateMessage'] = sprintf( __( 'Your email address has not been updated yet. Please check your inbox at <strong>%s</strong> for a confirmation email.', 'user-registration' ), $pending_email );

				$cancel_url = esc_url(
					add_query_arg(
						array(
							'cancel_email_change' => $user_id,
							'_wpnonce'            => wp_create_nonce( 'cancel_email_change_nonce' ),
						),
						ur_get_my_account_url() . get_option( 'user_registration_myaccount_edit_profile_endpoint', 'edit-profile' )
					)
				);

				$response['userEmailPendingMessage'] = sprintf(
					/* translators: %s - Email Change Pending Message. */
					'<div class="email-updated inline"><p>%s</p></div>',
					sprintf(
						/* translators: 1: Pending email message 2: Cancel Link */
						__( 'There is a pending change of your email to <code>%1$s</code>. <a href="%2$s">Cancel</a>', 'user-registration' ),
						$pending_email,
						$cancel_url
					)
				);

			}

			wp_send_json_success(
				$response
			);

		}
	}

	/**
	 * Get Post data on frontend form submit
	 *
	 * @return void
	 */
	public static function profile_pic_upload() {

		check_ajax_referer( 'user_registration_profile_picture_upload_nonce', 'security' );

		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'user_registration_profile_picture_upload_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration' ),
				)
			);
		}
		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		if ( isset( $_FILES['file']['size'] ) && wp_unslash( sanitize_key( $_FILES['file']['size'] ) ) ) {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$upload = isset( $_FILES['file'] ) ? $_FILES['file'] : array(); // phpcs:ignore

			// valid extension for image.
			$valid_extensions = 'image/jpeg,image/jpg,image/gif,image/png';

			$form_id    = ur_get_form_id_by_userid( $user_id );
			$field_data = ur_get_field_data_by_field_name( $form_id, 'profile_pic_url' );

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
				wp_send_json_error(
					array(
						'message' => __( 'Invalid file type, please contact with site administrator.', 'user-registration' ),
					)
				);
			}

			$upload_dir  = wp_upload_dir();
			$upload_path = apply_filters( 'user_registration_profile_pic_upload_url', $upload_dir['basedir'] . '/user_registration_uploads/profile-pictures' ); /*Get path of upload dir of WordPress*/

			// Checks if the upload directory exists and create one if not.
			if ( ! file_exists( $upload_path ) ) {
				wp_mkdir_p( $upload_path );
			}

			if ( ! is_writable( $upload_path ) ) {  /*Check if upload dir is writable*/
				wp_send_json_error(
					array(

						'message' => __( 'Upload path permission deny.', 'user-registration' ),
					)
				);

			}

			$upload_path = $upload_path . '/';
			$file_ext    = strtolower( pathinfo( $upload['name'], PATHINFO_EXTENSION ) );

			$file_name = wp_unique_filename( $upload_path, $upload['name'] );

			$file_path = $upload_path . sanitize_file_name( $file_name );

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

				$url = wp_get_attachment_url( $attachment_id );

				if ( empty( $url ) ) {
					$url = home_url() . '/wp-includes/images/media/text.png';
				}

				wp_send_json_success(
					array(
						'url'           => $url,
						'attachment_id' => $attachment_id,
					)
				);
			} else {
				wp_send_json_error(
					array(
						'message' => __( 'File cannot be uploaded.', 'user-registration' ),
					)
				);
			}
		} elseif ( isset( $_FILES['file']['error'] ) && UPLOAD_ERR_NO_FILE !== $_FILES['file']['error'] ) {

			switch ( $_FILES['file']['error'] ) {
				case UPLOAD_ERR_INI_SIZE:
					wp_send_json_error(
						array(
							'message' => __( 'File size exceed, please check your file size.', 'user-registration' ),
						)
					);
					break;
				default:
					wp_send_json_error(
						array(
							'message' => __( 'Something went wrong while uploading, please contact your site administrator.', 'user-registration' ),
						)
					);
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

	/**
	 * Login from Using Ajax
	 */
	public static function ajax_login_submit() {
		// Custom error messages.
		$messages = array(
			'empty_username'   => get_option( 'user_registration_message_username_required', __( 'Username is required.', 'user-registration' ) ),
			'empty_password'   => get_option( 'user_registration_message_empty_password', null ),
			'invalid_username' => get_option( 'user_registration_message_invalid_username', null ),
			'unknown_email'    => get_option( 'user_registration_message_unknown_email', __( 'A user could not be found with this email address.', 'user-registration' ) ),
			'pending_approval' => get_option( 'user_registration_message_pending_approval', null ),
			'denied_access'    => get_option( 'user_registration_message_denied_account', null ),
		);

		check_ajax_referer( 'ur_login_form_save_nonce', 'security' );

		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'ur_login_form_save_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration' ),
				)
			);
		}

		$info                  = array();
		$info['user_login']    = sanitize_user( isset( $_POST['username'] ) ? wp_unslash( sanitize_text_field( $_POST['username'] ) ) : '' ); //phpcs:ignore
		$info['user_password'] = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; //phpcs:ignore
		$info['remember']      = isset( $_POST['rememberme'] );
		$captcha_response      = isset( $_POST['CaptchaResponse'] ) ? $_POST['CaptchaResponse'] : ''; //phpcs:ignore
		$recaptcha_enabled     = get_option( 'user_registration_login_options_enable_recaptcha', 'no' );
		$recaptcha_type        = get_option( 'user_registration_integration_setting_recaptcha_version', 'v2' );
		$invisible_recaptcha   = get_option( 'user_registration_integration_setting_invisible_recaptcha_v2', 'no' );

		if ( 'v2' === $recaptcha_type && 'no' === $invisible_recaptcha ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret' );
		} elseif ( 'v2' === $recaptcha_type && 'yes' === $invisible_recaptcha ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_key' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_invisible_site_secret' );
		} elseif ( 'v3' === $recaptcha_type ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key_v3' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' );
		} elseif ( 'hCaptcha' === $recaptcha_type ) {
			$site_key   = get_option( 'user_registration_integration_setting_recaptcha_site_key_hcaptcha' );
			$secret_key = get_option( 'user_registration_integration_setting_recaptcha_site_secret_hcaptcha' );
		}

		if ( ( 'yes' == $recaptcha_enabled || '1' == $recaptcha_enabled ) && ! empty( $site_key ) && ! empty( $secret_key ) ) {
			if ( ! empty( $captcha_response ) ) {
				if ( 'hCaptcha' === $recaptcha_type ) {
					$data = wp_remote_get( 'https://hcaptcha.com/siteverify?secret=' . $secret_key . '&response=' . $captcha_response );
					$data = json_decode( wp_remote_retrieve_body( $data ) );

					if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_hcaptcha_threshold', 0.5 ) ) ) {
						wp_send_json_error(
							array(
								'message' => __( 'Error on hCaptcha. Contact your site administrator.', 'user-registration' ),
							)
						);
					}
				} else {
					$data = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha_response );
					$data = json_decode( wp_remote_retrieve_body( $data ) );

					if ( empty( $data->success ) || ( isset( $data->score ) && $data->score <= get_option( 'user_registration_integration_setting_recaptcha_threshold_score_v3', apply_filters( 'user_registration_recaptcha_v3_threshold', 0.5 ) ) ) ) {
						wp_send_json_error(
							array(
								'message' => __( 'Error on google reCaptcha. Contact your site administrator.', 'user-registration' ),
							)
						);
					}
				}
			} else {
				wp_send_json_error(
					array(
						'message' => get_option( 'user_registration_form_submission_error_message_recaptcha', __( 'Captcha code error, please try again.', 'user-registration' ) ),
					)
				);
			}
		}

		// To check the specific login.
		if ( 'email' === get_option( 'user_registration_general_setting_login_options_with', array() ) ) {
			$user_data          = get_user_by( 'email', $info['user_login'] );
			$info['user_login'] = isset( $user_data->user_email ) ? $user_data->user_email : is_email( $info['user_login'] );
		} elseif ( 'username' === get_option( 'user_registration_general_setting_login_options_with', array() ) ) {
			$user_data          = get_user_by( 'login', $info['user_login'] );
			$info['user_login'] = isset( $user_data->user_login ) ? $user_data->user_login : ! is_email( $info['user_login'] );
		} else {
			$info['user_login'] = $info['user_login'];
		}

		// perform the table login.
		$user = wp_signon( $info );

		if ( is_wp_error( $user ) ) {

			// set the custom error message.
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
			if ( ! empty( $user->errors['invalid_email'] ) ) {
				if ( empty( $messages['unknown_email'] ) ) {
					$messages['unknown_email'] = __( 'A user could not be found with this email address.', 'user-registration' );
				}

				$user->errors['invalid_email'][0] = apply_filters( 'user_registration_invalid_email_error_message', sprintf( '<strong>%s:</strong> %s', __( 'ERROR', 'user-registration' ), $messages['unknown_email'] ) );
			}
			if ( ! empty( $user->errors['incorrect_password'] ) ) {
				/* translators: 1 User login, 2: lost password url */
				$user->errors['incorrect_password'][0] = apply_filters( 'user_registration_incorrect_password_error_message', sprintf( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . __( 'The password you entered for username %1$1s is incorrect. %2$2s', 'user-registration' ), $info['user_login'], "<a href='" . esc_url( wp_lostpassword_url() ) . "'>" . __( 'Lost Your Password?', 'user-registration' ) . '</a>' ) );
			}
			$message = $user->get_error_message();
			wp_send_json_error( array( 'message' => $message ) );
		} else {
			if ( in_array( 'administrator', $user->roles ) && 'yes' === get_option( 'user_registration_login_options_prevent_core_login', 'no' ) ) {
				$redirect = admin_url();
			} else {
				if ( ! empty( $_POST['redirect'] ) ) {
					$redirect = wp_unslash( esc_url_raw( $_POST['redirect'] ) ); //phpcs:ignore
				} elseif ( wp_get_raw_referer() ) {
					$redirect = wp_get_raw_referer();
				} else {
					$redirect = get_home_url();
				}
			}
			$redirect = apply_filters( 'user_registration_login_redirect', $redirect, $user );
			wp_send_json_success( array( 'message' => $redirect ) );
		}
		wp_send_json( $user );
	}
	/**
	 * Send test email.
	 *
	 * @since 1.9.9
	 */
	public static function send_test_email() {
		$from_name    = apply_filters( 'wp_mail_from_name', get_option( 'user_registration_email_from_name', esc_attr( get_bloginfo( 'name', 'display' ) ) ) );
		$sender_email = apply_filters( 'wp_mail_from', get_option( 'user_registration_email_from_address', get_option( 'admin_email' ) ) );
		$email        = sanitize_email( isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
		/* translators: %s - WP mail from name */
		$subject = 'User Registration: ' . sprintf( esc_html__( 'Test email from %s', 'user-registration' ), $from_name );
		$header  = 'From: ' . $from_name . ' <' . $sender_email . ">\r\n";
		$header .= 'Reply-To: ' . $sender_email . "\r\n";
		$header .= "Content-Type: text/html; charset=UTF-8\r\n";
		$message =
		'Congratulations,<br>
		Your test email has been received successfully.<br>
		We thank you for trying out User Registration and joining our mission to make sure you get your emails delivered.<br>
		Regards,<br>
		User Registration Team';

		$status = wp_mail( $email, $subject, $message, $header );

		if ( $status ) {
			wp_send_json_success( array( 'message' => __( 'Test email was sent successfully! Please check your inbox to make sure it is delivered.', 'user-registration' ) ) );
		} {
			wp_send_json_error( array( 'message' => __( 'Test email was unsuccessful! Something went wrong.', 'user-registration' ) ) );
		}
	}
	/**
	 * User input dropped function
	 *
	 * @throws Exception Throws If Empty Form Data.
	 */
	public static function user_input_dropped() {

		try {

			check_ajax_referer( 'user_input_dropped_nonce', 'security' );

			$form_field_id = ( isset( $_POST['form_field_id'] ) ) ? $_POST['form_field_id'] : null; //phpcs:ignore

			if ( null == $form_field_id || '' == $form_field_id ) {
				throw  new Exception( 'Empty form data' );
			}

			$class_file_name = str_replace( 'user_registration_', '', $form_field_id );
			$class_name      = ur_load_form_field_class( $class_file_name );

			if ( empty( $class_name ) ) {
				throw  new Exception( 'class not exists' );
			}

			$templates = $class_name::get_instance()->get_admin_template();

			wp_send_json_success( $templates );

		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'error' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Import Form ajax.
	 *
	 * @throws Exception Post data mot set.
	 */
	public static function import_form_action() {
		try {
			check_ajax_referer( 'ur_import_form_save_nonce', 'security' );
			UR_Admin_Import_Export_Forms::import_form();
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);

		}
	}

	/**
	 * Form save from backend
	 *
	 * @return void
	 *
	 * @throws Exception Throw if any issue while saving form data.
	 */
	public static function form_save_action() {

		try {

			check_ajax_referer( 'ur_form_save_nonce', 'security' );

			if ( ! isset( $_POST['data'] ) || ( isset( $_POST['data'] ) && gettype( wp_unslash( $_POST['data'] ) ) != 'array' ) ) { //phpcs:ignore
				throw new Exception( __( 'post data not set', 'user-registration' ) );

			} elseif ( ! isset( $_POST['data']['form_data'] )
			|| ( isset( $_POST['data']['form_data'] )
			&& gettype( wp_unslash( $_POST['data']['form_data'] ) ) != 'string' ) ) { //phpcs:ignore

				throw new Exception( __( 'post data not set', 'user-registration' ) );
			}

			$post_data = json_decode( wp_unslash( $_POST['data']['form_data'] ) ); //phpcs:ignore

			$post_data = self::ur_add_to_advanced_settings( $post_data ); // Backward compatibility method. Since @1.5.7.

			self::sweep_array( $post_data );

			if ( isset( self::$failed_key_value['value'] ) && '' != self::$failed_key_value['value'] ) {

				if ( in_array( self::$failed_key_value['value'], self::$field_key_aray ) ) {
					throw  new Exception( sprintf( "Could not save form. Duplicate field name <span style='color:red'>%s</span>", self::$failed_key_value['value'] ) );
				}
			}

			if ( false === self::$is_field_key_pass ) {
				throw  new Exception( __( 'Could not save form. Invalid field name. Please check all field name', 'user-registration' ) );
			}

			$required_fields = array(
				'user_email',
				'user_pass',
			);

			$contains_search = count( array_intersect( $required_fields, self::$field_key_aray ) ) == count( $required_fields );

			if ( false === $contains_search ) {
				throw  new Exception( __( 'Could not save form, ' . join( ', ', $required_fields ) . ' fields are required.! ', 'user-registration' ) ); //phpcs:ignore
			}

			$form_name    = sanitize_text_field( $_POST['data']['form_name'] ); //phpcs:ignore
			$form_row_ids = sanitize_text_field( $_POST['data']['form_row_ids'] ); //phpcs:ignore
			$form_id      = sanitize_text_field( $_POST['data']['form_id'] ); //phpcs:ignore

			$post_data = array(
				'post_type'      => 'user_registration',
				'post_title'     => sanitize_text_field( $form_name ),
				'post_content'   => wp_json_encode( $post_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
				'post_status'    => 'publish',
				'comment_status' => 'closed',   // if you prefer.
				'ping_status'    => 'closed',      // if you prefer.
			);

			if ( $form_id > 0 && is_numeric( $form_id ) ) {
				$post_data['ID'] = $form_id;
			}

			remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );

			$post_id = wp_insert_post( wp_slash( $post_data ) );

			if ( $post_id > 0 ) {
				$_POST['data']['form_id'] = $post_id; // Form id for new form.

				$post_data_setting = isset( $_POST['data']['form_setting_data'] ) ? $_POST['data']['form_setting_data'] : array(); //phpcs:ignore
				ur_update_form_settings( $post_data_setting, $post_id );

				// Form row_id save.
				update_post_meta( $form_id, 'user_registration_form_row_ids', $form_row_ids );
			}

			do_action( 'user_registration_after_form_settings_save', wp_unslash( $_POST['data'] ) ); //phpcs:ignore

			wp_send_json_success(
				array(
					'data'    => $post_data,
					'post_id' => $post_id,
				)
			);

		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);

		}// End try().
	}

	/**
	 * AJAX plugin deactivation notice.
	 *
	 * @since  1.4.2
	 */
	public static function deactivation_notice() {

		check_ajax_referer( 'deactivation-notice', 'security' );

		ob_start();
		include_once UR_ABSPATH . 'includes/admin/views/html-notice-deactivation.php';

		$content = ob_get_clean();
		wp_send_json( $content ); // WPCS: XSS OK.
	}

	/**
	 * Dashboard Widget data.
	 *
	 * @since 1.5.8
	 */
	public static function dashboard_widget() {

		check_ajax_referer( 'dashboard-widget', 'security' );

		$form_id = isset( $_POST['form_id'] ) ? wp_unslash( absint( $_POST['form_id'] ) ) : 0;

		$user_report = $form_id ? ur_get_user_report( $form_id ) : array();
		$forms       = ! $form_id ? ur_get_all_user_registration_form() : array();

		wp_send_json(
			array(
				'user_report' => $user_report,
				'forms'       => $forms,
			)
		); // WPCS: XSS OK.
	}

	/**
	 * Checks if the string passes the regex
	 *
	 * @param  string $value Value.
	 * @return boolean
	 */
	private static function is_regex_pass( $value ) {

		$field_regex = "/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/";

		if ( preg_match( $field_regex, $value, $match ) ) :

			if ( null !== $match && count( $match ) == 1 && $match[0] === $value ) {
				return true;
			}

			endif;

		return false;
	}

	/**
	 * Sanitize values of form field in backend
	 *
	 * @param  array $array Array.
	 */
	public static function sweep_array( &$array ) {

		foreach ( $array as $key => &$value ) {

			if ( 'field_key' === $key ) {
				$field_key = $value;
			}

			if ( isset( $field_key ) && 'checkbox' === $field_key ) {

				if ( gettype( $value ) == 'object' ) {

					if ( isset( $value->options ) && is_array( $value->options ) && ! empty( $value->options ) ) {
						$sanitized_options = array();
						$allowed_tags      = array(
							array(),
							'a' => array(
								'href'      => true,
								'title'     => true,
								'target'    => true,
								'download'  => true,
								'rel'       => true,
								'hreflang'  => true,
								'type'      => true,
								'name'      => true,
								'accesskey' => true,
								'tabindex'  => true,
							),
						);

						foreach ( $value->options as $index => $option_value ) {
							$option_value = str_replace( '"', "'", $option_value );
							$option_value = wp_kses( trim( $option_value ), $allowed_tags );

							// Check if the option_value contains an open <a> tag but not a closing </a> tag.
							if ( preg_match( '/<a\s[^>]*>/i', $option_value ) && ! preg_match( '/<\/a>/i', $option_value ) ) {
								// Add a closing </a> tag to the end of the option_value.
								$option_value .= '</a>';
							}

							$sanitized_options [] = $option_value;
						}

						$value->options = $sanitized_options;
					}
				}
			} elseif ( is_array( $value ) || gettype( $value ) === 'object' ) {
				if ( isset( $value->field_key ) ) {
					$value = apply_filters( 'user_registration_field_setting_' . $value->field_key, $value );
				}
				self::sweep_array( $value );

			} else {

				if ( 'field_name' === $key ) {
					$regex_status = self::is_regex_pass( $value );

					if ( ! $regex_status || in_array( $value, self::$field_key_aray ) ) {
						self::$is_field_key_pass = false;
						self::$failed_key_value  = array(
							'key'   => $key,
							'value' => $value,
						);

						return;
					}
					array_push( self::$field_key_aray, $value );
				}
				if ( 'description' === $key ) {
					$value = str_replace( '"', "'", $value );
					$value = wp_kses(
						$value,
						array(
							'a'          => array(
								'href'   => array(),
								'title'  => array(),
								'target' => array(),
							),
							'br'         => array(),
							'em'         => array(),
							'strong'     => array(),
							'u'          => array(),
							'i'          => array(),
							'q'          => array(),
							'b'          => array(),
							'ul'         => array(),
							'ol'         => array(),
							'li'         => array(),
							'hr'         => array(),
							'blockquote' => array(),
							'del'        => array(),
							'strike'     => array(),
							'code'       => array(),
							'div'        => array(),
							'span'       => array(),
							'p'          => array(),
							'h1'         => array(),
							'h2'         => array(),
							'h3'         => array(),
							'h4'         => array(),
							'h5'         => array(),
							'h6'         => array(),
						)
					);

				} elseif ( 'html' === $key ) {

					if ( ! current_user_can( 'unfiltered_html' ) ) {
						$value = wp_kses_post( $value );
					}
				} else {
						$value = sanitize_text_field( $value );
				}
			}
		}
	}

	/**
	 * Triggered when clicking the rating footer.
	 *
	 * @since 1.1.2
	 */
	public static function rated() {
		if ( ! current_user_can( 'manage_user_registration' ) ) {
			wp_die( - 1 );
		}
		update_option( 'user_registration_admin_footer_text_rated', 1 );
		wp_die();
	}

	/**
	 * Migrate the choices/options from the general settings to advanced settings.
	 *
	 * Backward compatibility code. Modified @since 1.5.7.
	 *
	 * @param  array $post_data All fields data.
	 * @return array    Modified fields data.
	 */
	private static function ur_add_to_advanced_settings( $post_data ) {

		$modifiying_keys = array( 'radio', 'select', 'checkbox' );

		foreach ( $post_data as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {
					if ( isset( $field->field_key ) ) {
						if ( ! in_array( $field->field_key, $modifiying_keys ) ) {
							continue;
						}

						if ( isset( $field->general_setting->options ) ) {

							$options = implode( ',', $field->general_setting->options );

							if ( 'checkbox' === $field->field_key ) {
								$field->advance_setting->choices = $options;
							} else {
								$field->advance_setting->options = $options;
							}
						}
					}
				}
			}
		}

		return $post_data;
	}

	/**
	 * Dismiss user registration notices.
	 *
	 * @since  1.5.8
	 *
	 * @return void
	 **/
	public static function dismiss_notice() {
		$notice_type = isset( $_POST['notice_type'] ) ? wp_unslash( sanitize_key( $_POST['notice_type'] ) ) : '';   // phpcs:ignore WordPress.Security.NonceVerification
		check_admin_referer( $notice_type . '-nonce', 'security' );

		if ( ! empty( $_POST['dismissed'] ) ) {
			if ( ! empty( $_POST['dismiss_forever'] ) && 'true' === $_POST['dismiss_forever'] ) {
				update_option( 'user_registration_' . $notice_type . '_notice_dismissed', 'yes' );
				update_option( 'user_registration_review_notice_dismissed_temporarily', '' );
			} else {
				update_option( 'user_registration_' . $notice_type . '_notice_dismissed_temporarily', current_time( 'Y-m-d' ) );
			}
		}
	}

	/**
	 * Remove profile picture ajax method.
	 */
	public static function profile_pic_remove() {
		check_ajax_referer( 'user_registration_profile_picture_remove_nonce', 'security' );
		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'user_registration_profile_picture_remove_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration' ),
				)
			);
		}

		$attachment_id = isset( $_POST['attachment_id'] ) ? intval( wp_unslash( $_POST['attachment_id'] ) ) : '';

		if ( file_exists( get_attached_file( $attachment_id ) ) && ! unlink( get_attached_file( $attachment_id ) ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'File cannot be removed', 'user-registration' ),
				)
			);
		}

		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {
			update_user_meta( $user_id, 'user_registration_profile_pic_url', '' );
		}

		wp_send_json_success(
			array(
				'message' => __( 'User profile picture removed successfully', 'user-registration' ),
			)
		);

	}

	/**
	 * Ajax handler for licence check.
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 */
	public static function template_licence_check() {
		check_ajax_referer( 'user_registration_template_licence_check', 'security' );

		if ( empty( $_POST['plan'] ) ) {
			wp_send_json_error(
				array(
					'plan'         => '',
					'errorCode'    => 'no_plan_specified',
					'errorMessage' => esc_html__( 'No Plan specified.', 'user-registration' ),
				)
			);
		}

		$addons        = array();
		$template_data = ur_get_json_file_contents( 'assets/extensions-json/templates/all_templates.json' );

		if ( ! empty( $template_data->templates ) ) {
			foreach ( $template_data->templates as $template ) {

				if ( isset( $_POST['slug'] ) && $template->slug === $_POST['slug'] && in_array( trim( $_POST['plan'] ), $template->plan, true ) ) {
					$addons = $template->addons;
				}
			}
		}

		$output  = '<div class="user-registration-recommend-addons">';
		$output .= '<h3>' . esc_html__( 'This form template requires the following addons.', 'user-registration' ) . '</h3>';
		$output .= '<table class="plugins-list-table widefat striped">';
		$output .= '<thead><tr><th scope="col" class="manage-column required-plugins" colspan="2">' . esc_html__( 'Required Addons', 'user-registration' ) . '</th></tr></thead><tbody id="the-list">';
		$output .= '</div>';

		$activated = true;

		foreach ( $addons as $slug => $addon ) {

			$plugin = 'user-registration-pro' === $slug ? $slug . '/user-registration.php' : $slug . '/' . $slug . '.php';

			if ( is_plugin_active( $plugin ) ) {
				$class        = 'active';
				$parent_class = '';
			} elseif ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				$class        = 'activate-now';
				$parent_class = 'inactive';
				$activated    = false;
			} else {
				$class        = 'install-now';
				$parent_class = 'inactive';
				$activated    = false;
			}

			$output .= '<tr class="plugin-card-' . $slug . ' plugin ' . $parent_class . '" data-slug="' . $slug . '" data-plugin="' . $plugin . '" data-name="' . $addon . '">';
			$output .= '<td class="plugin-name">' . $addon . '</td>';
			$output .= '<td class="plugin-status"><span class="' . esc_attr( $class ) . '"></span></td>';
			$output .= '</tr>';
		}
		$output .= '</tbody></table></div>';

		wp_send_json_success(
			array(
				'html'     => $output,
				'activate' => $activated,
			)
		);
	}

	/**
	 * Ajax handler for installing a extension.
	 *
	 * @since 1.2.0
	 *
	 * @see Plugin_Upgrader
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 */
	public static function install_extension() {
		check_ajax_referer( 'updates' );

		if ( empty( $_POST['slug'] ) ) {
			wp_send_json_error(
				array(
					'slug'         => '',
					'errorCode'    => 'no_plugin_specified',
					'errorMessage' => esc_html__( 'No plugin specified.', 'user-registration' ),
				)
			);
		}

		$slug        = sanitize_key( wp_unslash( $_POST['slug'] ) );
		$plugin_slug = 'user-registration-pro' === $slug ? wp_unslash( $_POST['slug'] . '/user-registration.php' ) : wp_unslash( $_POST['slug'] . '/' . $_POST['slug'] . '.php' ); // phpcs:ignore
		$plugin      = plugin_basename( sanitize_text_field( $plugin_slug ) );
		$status      = array(
			'install' => 'plugin',
			'slug'    => sanitize_key( wp_unslash( $_POST['slug'] ) ),
		);

		if ( ! current_user_can( 'install_plugins' ) ) {
			$status['errorMessage'] = esc_html__( 'Sorry, you are not allowed to install plugins on this site.', 'user-registration' );
			wp_send_json_error( $status );
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
			$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$status['plugin']     = $plugin;
			$status['pluginName'] = $plugin_data['Name'];

			if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
				$result = activate_plugin( $plugin );

				if ( is_wp_error( $result ) ) {
					$status['errorCode']    = $result->get_error_code();
					$status['errorMessage'] = $result->get_error_message();
					wp_send_json_error( $status );
				}

				wp_send_json_success( $status );
			}
		}

		$api = json_decode(
			UR_Updater_Key_API::version(
				array(
					'license'   => get_option( 'user-registration_license_key' ),
					'item_name' => ! empty( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
				)
			)
		);

		if ( is_wp_error( $api ) ) {
			$status['errorMessage'] = $api->get_error_message();
			wp_send_json_error( $status );
		}

		$status['pluginName'] = $api->name;

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['errorMessage'] = $skin->get_error_messages();
			wp_send_json_error( $status );
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = esc_html__( 'Unable to connect to the filesystem. Please confirm your credentials.', 'user-registration' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			wp_send_json_error( $status );
		}

		$api->version   = isset( $api->new_version ) ? $api->new_version : '';
		$install_status = install_plugin_install_status( $api );

		if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
			if ( isset( $_POST['page'] ) && 'user-registration_page_add-new-registration' === $_POST['page'] ) {
				activate_plugin( $install_status['file'] );
			} else {
				$status['activateUrl'] =
				esc_url_raw(
					add_query_arg(
						array(
							'action'   => 'activate',
							'plugin'   => $install_status['file'],
							'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $install_status['file'] ),
						),
						admin_url( 'admin.php?page=user-registration-addons' )
					)
				);
			}
		}

		wp_send_json_success( $status );
	}

	/**
	 * AJAX create new form.
	 */
	public static function create_form() {
		ob_start();

		check_ajax_referer( 'user_registration_create_form', 'security' );

		$title    = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : esc_html__( 'Blank Form', 'user-registration' );
		$template = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : 'blank';

		$form_id = UR()->form->create( $title, $template );

		if ( $form_id ) {
			$data = array(
				'id'       => $form_id,
				'redirect' => add_query_arg(
					array(
						'tab'     => 'fields',
						'form_id' => $form_id,
					),
					admin_url( 'admin.php?page=add-new-registration&edit-registration=' . $form_id )
				),
			);

			wp_send_json_success( $data );
		}

		wp_send_json_error(
			array(
				'error' => esc_html__( 'Something went wrong, please try again later', 'user-registration' ),
			)
		);
	}

	/**
	 * Cancel a pending email change.
	 *
	 * @return void
	 */
	public static function cancel_email_change() {
		check_ajax_referer( 'cancel_email_change_nonce', '_wpnonce' );

		$user_id = isset( $_POST['cancel_email_change'] ) ? absint( wp_unslash( $_POST['cancel_email_change'] ) ) : false;

		if ( ! $user_id ) {
			wp_die( -1 );
		}

		// Remove the confirmation key, pending email and expiry date.
		UR_Form_Handler::delete_pending_email_change( $user_id );

		wp_send_json_success(
			array(
				'message' => __( 'Changed email cancelled successfully.', 'user-registration' ),
			)
		);
	}

	/**
	 * Email setting status
	 */
	public static function email_setting_status() {

		if ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_key( $_POST['security'] ), 'email_setting_status_nonce' ) ) {
			$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : null;
			$id     = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : null;
			$value  = 'on' === $status ? 'yes' : 'no';
			$key    = 'user_registration_enable_' . $id;
			if ( update_option( $key, $value ) ) {
				wp_send_json_success( 'Successfully Updated' );
			} else {
				wp_send_json_error( 'Update failed !' );
			};

		}
	}
}

UR_AJAX::init();
