<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Frontend_Form_Handler
 * @version  1.0.0
 * @package  UserRegistration/Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles frontend user registration forms.
 *
 * @class UR_Frontend_Form_Handler
 * @version 1.0.0
 * @package UserRegistration/Frontend
 */
class UR_Frontend_Form_Handler {

	/**
	 * Form ID.
	 *
	 * @var int
	 */
	public static $form_id = 0;

	/**
	 * Response Data array.
	 *
	 * @var array
	 */
	public static $response_array = array();

	/**
	 * Valid Form data.
	 *
	 * @var array
	 */
	private static $valid_form_data = array();

	/**
	 * Handle frontend form POST data.
	 *
	 * @param  array $form_data Submitted form data.
	 * @param  int   $form_id   ID of the form.
	 * @return void
	 */
	public static function handle_form( $form_data, $form_id ) {

		self::$form_id      = $form_id;
		$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

		if ( gettype( $form_data ) != 'array' && gettype( $form_data ) != 'object' ) {
			$form_data = array();
		}

		$form_field_data = self::get_form_field_data( $post_content_array );

		$user_pass = '';

		/**
		 * Get form field data by post_content array passed.
		 *
		 * @param array $post_content_array Post Content Array.
		 * @return array
		 */
		apply_filters_ref_array(
			'user_registration_validate_form_data',
			array(
				&self::$valid_form_data,
				$form_field_data,
				$form_data,
				$form_id,
				&self::$response_array,
				&$user_pass,
			)
		);

		self::$response_array = apply_filters( 'user_registration_response_array', self::$response_array, $form_data, $form_id );

		if ( count( self::$response_array ) === 0 ) {
			$user_role = ! in_array( ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_default_user_role' ), array_keys( ur_get_default_admin_roles() ) ) ? 'subscriber' : ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_default_user_role' );
			$user_role = apply_filters( 'user_registration_user_role', $user_role, self::$valid_form_data, $form_id );
			$userdata  = array(
				'user_login'      => isset( self::$valid_form_data['user_login'] ) ? self::$valid_form_data['user_login']->value : '',
				'user_pass'       => $user_pass,
				'user_email'      => self::$valid_form_data['user_email']->value,
				'display_name'    => isset( self::$valid_form_data['display_name']->value ) ? self::$valid_form_data['display_name']->value : '',
				'user_url'        => isset( self::$valid_form_data['user_url']->value ) ? self::$valid_form_data['user_url']->value : '',
				// When creating an user, `user_pass` is expected.
				'role'            => $user_role,
				'user_registered' => current_time( 'Y-m-d H:i:s' ),
			);

			self::$valid_form_data = apply_filters( 'user_registration_before_register_user_filter', self::$valid_form_data, $form_id );
			do_action( 'user_registration_before_register_user_action', self::$valid_form_data, $form_id );

			if ( empty( $userdata['user_login'] ) ) {
				$part_of_email          = explode( '@', $userdata['user_email'] );
				$username               = check_username( $part_of_email[0] );
				$userdata['user_login'] = $username;
			}

			$userdata = apply_filters( 'user_registration_before_insert_user', $userdata, self::$valid_form_data, $form_id );

			// If spam and reject registration return early
			$akismet_result = apply_filters( 'user_registration_get_akismet_validate', $form_id, self::$valid_form_data );
			if ( $akismet_result ) {
				wp_send_json_error(
					array(
						'message' => __( 'Registration blocked due to potential spam. Reach out to support for help.', 'user-registration' ),
					)
				);
			}

			$user_id = wp_insert_user( $userdata ); // Insert user data in users table.

			$filtered_form_data = apply_filters( 'user_registration_before_user_meta_update', self::$valid_form_data, $user_id, $form_id );

			self::ur_update_user_meta( $user_id, $filtered_form_data, $form_id ); // Insert user data in usermeta table.

			if ( $user_id > 0 ) {
				do_action( 'user_registration_after_user_meta_update', self::$valid_form_data, $form_id, $user_id );

				$login_option   = ur_get_user_login_option( $user_id );
				$success_params = array(
					'username' => isset( $userdata['user_login'] ) ? $userdata['user_login'] : '',
				);

				if ( isset( $_POST['ur_stripe_payment_method'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

					if ( 'auto_login' === $login_option ) {
						$success_params['auto_login'] = true;
					}
				} elseif ( ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_enable_paypal_standard', false ) ) ) {
					if ( 'auto_login' === $login_option ) {
						$success_params['auto_login'] = false;
					}
				} elseif ( 'auto_login' === $login_option ) {

						wp_clear_auth_cookie();
						$remember = apply_filters( 'user_registration_autologin_remember_user', false );
						wp_set_auth_cookie( $user_id, $remember );
						$success_params['auto_login'] = true;
				}
				$success_params['success_message_positon'] = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_success_message_position', '1' );
				$success_params['form_login_option']       = $login_option;

				$redirect_timeout = (int) ur_get_single_post_meta( $form_id, 'user_registration_form_setting_redirect_after', '2' ) * 1000;

				$success_params['redirect_timeout'] = apply_filters( 'user_registration_hold_success_message_before_redirect', $redirect_timeout );

				$redirect_url = ur_get_form_redirect_url( $form_id );

				if ( ! empty( $redirect_url ) ) {
					$success_params['redirect_url'] = $redirect_url;
				}
				$success_params = apply_filters( 'user_registration_success_params', $success_params, self::$valid_form_data, $form_id, $user_id );

				foreach ( self::$valid_form_data as $field_key => $field_value ) {
					if ( isset( $field_value->extra_params ) && isset( $field_value->extra_params['field_key'] ) ) {
						if ( 'file' === $field_value->extra_params['field_key'] ) {
							$file_data   = explode( ',', get_user_meta( $user_id, 'user_registration_' . $field_value->field_name, true ) );
							$upload_data = array();

							foreach ( $file_data as $key => $file_value ) {
								$file = isset( $file_value ) ? wp_get_attachment_url( $file_value ) : '';
								array_push( $upload_data, $file );
							}

							$field_value->value                  = $upload_data;
							self::$valid_form_data[ $field_key ] = $field_value;
						}

						// Process for file upload.
						if ( 'profile_picture' === $field_value->extra_params['field_key'] ) {
							$profile_file_data                   = get_user_meta( $user_id, 'user_registration_' . $field_value->field_name, true );
							$profile_file                        = wp_get_attachment_url( $profile_file_data );
							$field_value->value                  = $profile_file;
							self::$valid_form_data[ $field_key ] = $field_value;
						}
					}
				}
				if ( ! isset( $success_params['stripe_process'] ) || $success_params['stripe_process'] == false ) {
					do_action( 'user_registration_after_register_user_action', self::$valid_form_data, $form_id, $user_id );
				}
				$success_params = apply_filters( 'user_registration_success_params_before_send_json', $success_params, self::$valid_form_data, $form_id, $user_id );
				wp_send_json_success( $success_params );
			}
			wp_send_json_error(
				array(
					'message' => __( 'Something went wrong! please try again', 'user-registration' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => array_unique( self::$response_array, SORT_REGULAR ),
				)
			);
		}// End if().
	}

	/**
	 * Get form field data by post_content array passed.
	 *
	 * @param array $post_content_array Post Content Array.
	 * @return array
	 */
	public static function get_form_field_data( $post_content_array ) {
		$form_field_data_array = array();
		foreach ( $post_content_array as $row_index => $row ) {
			foreach ( $row as $grid_index => $grid ) {
				foreach ( $grid as $field_index => $field ) {
					if ( isset( $field->general_setting->field_name ) && 'confirm_user_pass' != $field->general_setting->field_name ) {
						array_push( $form_field_data_array, $field );
					}
				}
			}
		}
		return ( $form_field_data_array );
	}

	/**
	 * Update form data to usermeta table.
	 *
	 * @param  int   $user_id         User ID.
	 * @param  array $valid_form_data All valid form data.
	 * @param  int   $form_id         Form ID.
	 * @return void
	 */
	public static function ur_update_user_meta( $user_id, $valid_form_data, $form_id ) {

		foreach ( $valid_form_data as $data ) {
			$field_key = isset( $data->extra_params['field_key'] ) ? $data->extra_params['field_key'] : '';

			if ( ! in_array( trim( $data->field_name ), ur_get_user_table_fields() ) && $field_key !== 'file' ) {
				$field_name            = $data->field_name;
				$fields_without_prefix = ur_get_fields_without_prefix();

				if ( ! in_array( $field_key, $fields_without_prefix ) ) {
					$field_name = 'user_registration_' . $field_name;
				}

				if ( isset( $data->extra_params['field_key'] ) && ( 'checkbox' === $data->extra_params['field_key'] || 'learndash_course' === $data->extra_params['field_key'] ) ) {
					$data->value = isset( $data->value ) && ! is_array( $data->value ) ? json_decode( $data->value ) : $data->value;
				} elseif ( isset( $data->extra_params['field_key'] ) && ( 'wysiwyg' === $data->extra_params['field_key'] ) ) {
					$data->value = sanitize_text_field( htmlentities( $data->value ) );
				}
				update_user_meta( $user_id, $field_name, $data->value );
			}
			if ( 'user_url' === $data->field_name ) {
				$data->value = sanitize_text_field( $data->value );
				update_user_meta( $user_id, $data->field_name, $data->value );
			}
		}
		update_user_meta( $user_id, 'ur_form_id', $form_id );

		/**
		 * Saving the user ip in user meta.
		 *
		 * @since  3.1.0
		 */
		$user_ip = ur_get_ip_address();
		update_user_meta( $user_id, 'ur_user_ip', $user_ip );

		$login_option = ur_get_user_login_option( $user_id );
		update_user_meta( $user_id, 'ur_login_option', $login_option );

		$current_language = ur_get_current_language();
		$current_language = isset( $_POST['registration_language'] ) ? ur_clean( $_POST['registration_language'] ) : $current_language; //phpcs:ignore.
		update_user_meta( $user_id, 'ur_registered_language', $current_language );
	}
}
return new UR_Frontend_Form_Handler();
