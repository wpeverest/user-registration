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

		if ( ! ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
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

		$profile_endpoint = get_option( 'user_registration_myaccount_edit_profile_endpoint', 'edit-profile' );
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

		$form_id_array = get_user_meta( $user_id, 'ur_form_id' );
		$form_id       = 0;

		if ( isset( $form_id_array[0] ) ) {
			$form_id = $form_id_array[0];
		}

		$profile = user_registration_form_data( $user_id, $form_id );

		do_action( 'user_registration_validate_profile_update_post', $profile, $form_id );

		do_action( 'user_registration_after_save_profile_validation', $user_id, $profile );

		if ( 0 === ur_notice_count( 'error' ) ) {
			$user_data = array();

			$profile = apply_filters( 'user_registration_before_save_profile_details', $profile, $user_id, $form_id );

			$is_email_change_confirmation = (bool) apply_filters( 'user_registration_email_change_confirmation', true );
			$email_updated                = false;
			$pending_email                = '';
			$user                         = wp_get_current_user();

			foreach ( $profile as $key => $field ) {
				if ( isset( $field['field_key'] ) ) {
					$new_key = str_replace( 'user_registration_', '', $key );

					if ( $is_email_change_confirmation && 'user_email' === $new_key ) {

						if ( $user ) {
							if ( sanitize_email( wp_unslash( $_POST[ $key ] ) ) !== $user->user_email ) { // phpcs:ignore
								$email_updated = true;
								$pending_email = sanitize_email( wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore
							}
							continue;
						}
					}

					if ( in_array( $new_key, ur_get_user_table_fields() ) ) {

						if ( 'display_name' === $new_key ) {
							$user_data['display_name'] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
						} else {
							$user_data[ $new_key ] = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						}
					} else {
						$update_key = $key;

						if ( in_array( $new_key, ur_get_registered_user_meta_fields(), true ) ) {
							$update_key = str_replace( 'user_', '', $new_key );
						}
						$disabled = isset( $field['custom_attributes']['disabled'] ) ? $field['custom_attributes']['disabled'] : '';
						if ( 'disabled' !== $disabled ) {
							if ( isset( $_POST[ $key ] ) ) {
								update_user_meta( $user_id, $update_key, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							}
						}
					}
				}
			}

			if ( count( $user_data ) > 0 ) {
				$user_data['ID'] = get_current_user_id();
				wp_update_user( $user_data );
			}

			$message = apply_filters( 'user_registration_profile_update_success_message', __( 'User profile updated successfully.', 'user-registration' ) );

			if ( $email_updated ) {
				self::send_confirmation_email( $user, $pending_email );
				/* translators: user_email */
				$user_email_update_message = sprintf( __( 'Your email address has not been updated yet. Please check your inbox at <strong>%s</strong> for a confirmation email.', 'user-registration' ), $pending_email );
				ur_add_notice( $user_email_update_message, 'notice' );
			}

			ur_add_notice( $message );

			do_action( 'user_registration_save_profile_details', $user_id, $form_id );

			wp_safe_redirect( ur_get_account_endpoint_url( $profile_endpoint ) );
			exit;
		}
	}

	/**
	 * Send confirmation email.
	 *
	 * @param object $user User.
	 * @param email  $new_email Email.
	 * @return void
	 */
	public static function send_confirmation_email( $user, $new_email ) {
		// Generate a confirmation key for the email change.
		$confirm_key = wp_generate_password( 20, false );

		// Save the confirmation key.
		update_user_meta( $user->ID, 'user_registration_email_confirm_key', $confirm_key );

		// Send an email to the new address with confirmation link.
		$confirm_link = add_query_arg( 'confirm_email', $user->ID, add_query_arg( 'confirm_key', $confirm_key, ur_get_my_account_url() . get_option( 'user_registration_myaccount_edit_profile_endpoint', 'edit-profile' ) ) );
		$to           = $new_email;
		$subject      = apply_filters( 'user_registration_email_change_email_subject', __( 'Confirm Your Email Address Change', 'user-registration' ) );
		$message      = sprintf(
		/* translators: %1$s is the display name of the user, %2$s is the new email, %3$s is the confirmation link, %4$s is the blog name. */
			__(
				'Dear %1$s,<br /><br />
		You recently requested to change your email address associated with your account to %2$s.<br /><br />
		To confirm this change, please click on the following link:<br />
		<a href="%3$s">%3$s</a><br /><br />
		This link will only be active for 24 hours. If you did not request this change, please ignore this email or contact us for assistance.<br /><br />
		Best regards,<br />
		%4$s',
				'user-registration'
			),
			$user->display_name,
			$new_email,
			$confirm_link,
			get_bloginfo( 'name' )
		);
		$message  = apply_filters( 'user_registration_email_change_email_content', $message );
		$headers  = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		wp_mail( $to, $subject, $message, $headers );

		update_user_meta( $user->ID, 'user_registration_email_confirm_key', $confirm_key );
		update_user_meta( $user->ID, 'user_registration_pending_email', $new_email );
		update_user_meta( $user->ID, 'user_registration_pending_email_expiration', time() + DAY_IN_SECONDS );
	}

	/**
	 * Delete a pending email change.
	 *
	 * @param integer $user_id User ID.
	 * @return void
	 */
	public static function delete_pending_email_change( $user_id ) {
		delete_user_meta( $user_id, 'user_registration_email_confirm_key' );
		delete_user_meta( $user_id, 'user_registration_pending_email' );
		delete_user_meta( $user_id, 'user_registration_pending_email_expiration' );
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

		$pass_cur                = ! empty( $_POST['password_current'] ) ? wp_unslash( $_POST['password_current'] ) : ''; //phpcs:ignore;
		$pass1                   = ! empty( $_POST['password_1'] ) ? wp_unslash( $_POST['password_1'] ) : ''; //phpcs:ignore;
		$pass2                   = ! empty( $_POST['password_2'] ) ? wp_unslash( $_POST['password_2'] ) : ''; //phpcs:ignore;
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
		$nonce_value = isset( $_POST['_wpnonce'] ) ? sanitize_key( $_POST['_wpnonce'] ) : '';
		$nonce_value = isset( $_POST['user-registration-login-nonce'] ) ? sanitize_key( $_POST['user-registration-login-nonce'] ) : $nonce_value;

		if ( ! empty( $_POST['login'] ) && wp_verify_nonce( $nonce_value, 'user-registration-login' ) ) {
			ur_process_login( $nonce_value );
		}
	}

	/**
	 * Handle lost password form.
	 */
	public static function process_lost_password() {
		if ( isset( $_POST['ur_reset_password'] ) && isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'lost_password' ) ) {

			$hcaptca_response    = isset( $_POST['h-captcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['h-captcha-response'] ) ) : '';
			$recaptcha_value     = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : $hcaptca_response;
			$recaptcha_enabled   = ur_string_to_bool( apply_filters( 'user_registration_lost_password_options_enable_recaptcha', false ) );
			$recaptcha_type      = get_option( 'user_registration_captcha_setting_recaptcha_version', 'v2' );
			$invisible_recaptcha = ur_option_checked( 'user_registration_captcha_setting_invisible_recaptcha_v2', false );

			if ( 'v2' === $recaptcha_type && ! $invisible_recaptcha ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret' );
			} elseif ( 'v2' === $recaptcha_type && $invisible_recaptcha ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_key' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_invisible_site_secret' );
			} elseif ( 'v3' === $recaptcha_type ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_v3' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_v3' );
			} elseif ( 'hCaptcha' === $recaptcha_type ) {
				$site_key   = get_option( 'user_registration_captcha_setting_recaptcha_site_key_hcaptcha' );
				$secret_key = get_option( 'user_registration_captcha_setting_recaptcha_site_secret_hcaptcha' );
			}

			if ( $recaptcha_enabled && ! empty( $site_key ) && ! empty( $secret_key ) ) {
				if ( ! empty( $recaptcha_value ) ) {
					if ( 'hCaptcha' === $recaptcha_type ) {
						$data = wp_remote_get( 'https://hcaptcha.com/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_value );
						$data = json_decode( wp_remote_retrieve_body( $data ) );

						if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_hcaptcha_threshold', 0.5 ) ) ) {
							ur_add_notice( __( 'Error on hCaptcha. Contact your site administrator.', 'user-registration' ), 'error' );
							return false;
						}
					} else {
						$data = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_value );
						$data = json_decode( wp_remote_retrieve_body( $data ) );
						if ( empty( $data->success ) || ( isset( $data->score ) && $data->score <= get_option( 'user_registration_captcha_setting_recaptcha_threshold_score_v3', apply_filters( 'user_registration_recaptcha_v3_threshold', 0.5 ) ) ) ) {
							ur_add_notice( __( 'Error on google reCaptcha. Contact your site administrator.', 'user-registration' ), 'error' );
							return false;
						}
					}
				} else {
					ur_add_notice( get_option( 'user_registration_form_submission_error_message_recaptcha', __( 'Captcha code error, please try again.', 'user-registration' ) ), 'error' );
					return false;
				}
			}

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

				set_transient( 'ur_password_resetted_flag', true, 60 );
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

		$templates = UR_Admin_Form_Templates::get_template_data();

		$templates = is_array( $templates ) ? $templates : array();

		$form_data = array();

		if ( ! empty( $templates ) ) {
			foreach ( $templates as $template_data ) {
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
			add_post_meta( $form_id, 'user_registration_imported_form_template_slug', $template );
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
