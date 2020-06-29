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
 * UR_Frontend_Form_Handler Class
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
	 * Handle frontend form POST data
	 *
	 * @param  array $form_data Submitted form data.
	 * @param  int   $form_id ID of the form.
	 * @return void
	 */
	public static function handle_form( $form_data, $form_id ) {

		self::$form_id      = $form_id;
		$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

		if ( gettype( $form_data ) != 'array' && gettype( $form_data ) != 'object' ) {
			$form_data = array();
		}

		$form_field_data = self::get_form_field_data( $post_content_array );

		self::match_email( $form_field_data, $form_data );

		self::add_hook( $form_field_data, $form_data );
		$activated_form_list = get_option( 'user_registration_extras_auto_password_activated_forms', array() );

		if ( in_array( $form_id, $activated_form_list ) ) {
			do_action( 'user_registration_auto_generate_password' );
			$user_pass = wp_slash( apply_filters( 'user_registration_extras_auto_generated_password', 'user_pass' ) );
			self::validate_form_data( $form_field_data, $form_data, $form_id );
		} else {
			self::match_password( $form_field_data, $form_data );
			self::validate_form_data( $form_field_data, $form_data, $form_id );
			self::validate_password_data( $form_field_data, $form_data );
			$user_pass = wp_slash( self::$valid_form_data['user_pass']->value );
		}

		self::$response_array = apply_filters( 'user_registration_response_array', self::$response_array, $form_data, $form_id );

		if ( count( self::$response_array ) == 0 ) {
			$user_role = ! in_array( ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_default_user_role' ), array_keys( ur_get_default_admin_roles() ) ) ? 'subscriber' : ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_default_user_role' );
			$user_role = apply_filters( 'user_registration_user_role', $user_role, self::$valid_form_data, $form_id );
			$userdata  = array(
				'user_login'   => isset( self::$valid_form_data['user_login'] ) ? self::$valid_form_data['user_login']->value : '',
				'user_pass'    => $user_pass,
				'user_email'   => self::$valid_form_data['user_email']->value,
				'display_name' => isset( self::$valid_form_data['display_name']->value ) ? self::$valid_form_data['display_name']->value : '',
				'user_url'     => isset( self::$valid_form_data['user_url']->value ) ? self::$valid_form_data['user_url']->value : '',
				// When creating an user, `user_pass` is expected.
				'role'         => $user_role,
			);

			self::$valid_form_data = apply_filters( 'user_registration_before_register_user_filter', self::$valid_form_data, $form_id );
			do_action( 'user_registration_before_register_user_action', self::$valid_form_data, $form_id );

			if ( empty( $userdata['user_login'] ) ) {
				$part_of_email          = explode( '@', $userdata['user_email'] );
				$username               = check_username( $part_of_email[0] );
				$userdata['user_login'] = $username;
			}

			$user_id = wp_insert_user( $userdata ); // Insert user data in users table.

			self::ur_update_user_meta( $user_id, self::$valid_form_data, $form_id ); // Insert user data in usermeta table.
			do_action( 'user_registration_after_register_user_action', self::$valid_form_data, $form_id, $user_id );

			if ( $user_id > 0 ) {
				$login_option   = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );
				$success_params = array(
					'username' => isset( self::$valid_form_data['user_login'] ) ? self::$valid_form_data['user_login']->value : '',
				);

				if ( 'auto_login' === $login_option ) {
					wp_clear_auth_cookie();
					wp_set_auth_cookie( $user_id );
					$success_params['auto_login'] = true;
				}

				$success_params['form_login_option'] = $login_option;
				$success_params                      = apply_filters( 'user_registration_success_params', $success_params, self::$valid_form_data, $form_id, $user_id );

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
					'message' => array_unique( self::$response_array ),
				)
			);
		}// End if().
	}

	/**
	 * Get form field data by post_content array passed
	 *
	 * @param array $post_content_array Post Content Array.
	 * @return array
	 */
	public static function get_form_field_data( $post_content_array ) {
		$form_field_data_array = array();
		foreach ( $post_content_array as $row_index => $row ) {
			foreach ( $row as $grid_index => $grid ) {
				foreach ( $grid as $field_index => $field ) {
					if ( 'confirm_user_pass' != $field->general_setting->field_name ) {
						array_push( $form_field_data_array, $field );
					}
				}
			}
		}
		return ( $form_field_data_array );
	}

	/**
	 * Validation from each field's class validation() method.
	 * Sanitization from get_sanitize_value().
	 *
	 * @param  array $form_field_data Form Field Data.
	 * @param  array $form_data  Form data to validate.
	 */
	private static function validate_form_data( $form_field_data = array(), $form_data = array(), $form_id ) {
		$form_data_field     = wp_list_pluck( $form_data, 'field_name' );
		$form_field_data     = apply_filters( 'user_registration_add_form_field_data', $form_field_data, $form_id );
		$form_key_list       = wp_list_pluck( wp_list_pluck( $form_field_data, 'general_setting' ), 'field_name' );
		$duplicate_field_key = array_diff_key( $form_data_field, array_unique( $form_data_field ) );
		if ( count( $duplicate_field_key ) > 0 ) {
			array_push( self::$response_array, __( 'Duplicate field key in form, please contact site administrator.', 'user-registration' ) );
		}

		$contains_search = count( array_intersect( ur_get_required_fields(), $form_data_field ) ) == count( ur_get_required_fields() );

		if ( false === $contains_search ) {
			array_push( self::$response_array, __( 'Required form field not found.', 'user-registration' ) );
		}

		// Check if a required field is missing.
		$missing_item = array_diff( $form_key_list, $form_data_field );

		if ( count( $missing_item ) > 0 ) {

			foreach ( $missing_item as $key => $value ) {

				// Ignoring confirm password and confirm email field, since they are handled separately.
				if ( 'user_confirm_password' !== $value && 'user_confirm_email' !== $value && 'invite_code' !== $value && 'user_pass' !== $value ) {
					self::ur_missing_field_validation( $form_field_data, $key, $value );
				}
			}
		}

		foreach ( $form_data as $data ) {

			if ( in_array( $data->field_name, $form_key_list ) ) {
				$form_data_index                            = array_search( $data->field_name, $form_key_list );
				$single_form_field                          = $form_field_data[ $form_data_index ];
				$general_setting                            = isset( $single_form_field->general_setting ) ? $single_form_field->general_setting : new stdClass();
				$single_field_key                           = $single_form_field->field_key;
				$single_field_label                         = isset( $general_setting->label ) ? $general_setting->label : '';
				$data->extra_params                         = array(
					'field_key' => $single_field_key,
					'label'     => $single_field_label,
				);
				self::$valid_form_data[ $data->field_name ] = self::get_sanitize_value( $data );
				$hook                                       = "user_registration_validate_{$single_form_field->field_key}";
				$filter_hook                                = $hook . '_message';

				if ( 'user_email' === $single_form_field->field_key ) {
					do_action( 'user_registration_validate_email_whitelist', $data->value, $filter_hook );
				}

				if ( 'honeypot' === $single_form_field->field_key ) {
					do_action( 'user_registration_validate_honeypot_container', $data, $filter_hook, $form_id, $form_data );
				}

				do_action( $hook, $single_form_field, $data, $filter_hook, self::$form_id );
				$response = apply_filters( $filter_hook, '' );
				if ( ! empty( $response ) ) {
					array_push( self::$response_array, $response );
				}
			}
		}
	}

	/**
	 * Triger validation method for user fields
	 * Useful for custom fields validation
	 *
	 * @param array $form_field_data Form Field Data.
	 * @param array $form_data Form Data.
	 */
	public static function add_hook( $form_field_data = array(), $form_data = array() ) {
		$form_key_list = wp_list_pluck( wp_list_pluck( $form_field_data, 'general_setting' ), 'field_name' );
		foreach ( $form_data as $data ) {
			if ( in_array( $data->field_name, $form_key_list ) ) {
				$form_data_index   = array_search( $data->field_name, $form_key_list );
				$single_form_field = $form_field_data[ $form_data_index ];
				$class_name        = ur_load_form_field_class( $single_form_field->field_key );
				$hook              = "user_registration_validate_{$single_form_field->field_key}";
				add_action(
					$hook,
					array(
						$class_name::get_instance(),
						'validation',
					),
					10,
					4
				);
			}
		}
	}

	/**
	 * Sanitize form data
	 *
	 * @param  obj $form_data Form data.
	 * @return object
	 */
	public static function get_sanitize_value( &$form_data ) {

		$field_key = isset( $form_data->extra_params['field_key'] ) ? $form_data->extra_params['field_key'] : '';
		$fields    = ur_get_registered_form_fields();

		if ( in_array( $field_key, $fields ) ) {

			switch ( $field_key ) {
				case 'user_email':
				case 'email':
					$form_data->value = sanitize_email( $form_data->value );
					break;
				case 'user_login':
					$form_data->value = sanitize_user( $form_data->value );
					break;
				case 'user_url':
					$form_data->value = esc_url_raw( $form_data->value );
					break;
				case 'textarea':
				case 'description':
					$form_data->value = sanitize_textarea_field( $form_data->value );
					break;
				case 'number':
					$form_data->value = intval( $form_data->value );
					break;
				case 'nickname':
				case 'first_name':
				case 'last_name':
				case 'display_name':
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'privacy_policy':
				case 'mailchimp':
				case 'select':
				case 'country':
				case 'file':
				case 'date':
					$form_data->value = sanitize_text_field( isset( $form_data->value ) ? $form_data->value : '' );
					break;
			}
		}
		return apply_filters( 'user_registration_sanitize_field', $form_data, $field_key );
	}

	/**
	 * Update form data to usermeta table.
	 *
	 * @param  int   $user_id User ID.
	 * @param  array $valid_form_data All valid form data.
	 * @param  int   $form_id Form ID.
	 * @return void
	 */
	public static function ur_update_user_meta( $user_id, $valid_form_data, $form_id ) {

		foreach ( $valid_form_data as $data ) {
			if ( ! in_array( trim( $data->field_name ), ur_get_user_table_fields() ) ) {
				$field_name            = $data->field_name;
				$field_key             = isset( $data->extra_params['field_key'] ) ? $data->extra_params['field_key'] : '';
				$fields_without_prefix = ur_get_fields_without_prefix();

				if ( ! in_array( $field_key, $fields_without_prefix ) ) {
					$field_name = 'user_registration_' . $field_name;
				}

				if ( isset( $data->extra_params['field_key'] ) && $data->extra_params['field_key'] === 'checkbox' ) {
					$data->value = ( json_decode( $data->value ) !== null ) ? json_decode( $data->value ) : $data->value;
				}
				update_user_meta( $user_id, $field_name, $data->value );
			}
			update_user_meta( $user_id, 'ur_form_id', $form_id );
		}
	}

	/**
	 * Match password and confirm password field
	 *
	 * @param  array $form_field_data Form Field Data.
	 * @param  obj   $form_data Form data submitted.
	 * @return obj $form_data
	 */
	private static function match_password( $form_field_data, &$form_data ) {
		$confirm_password     = '';
		$has_confirm_password = false;
		$password             = '';

		$form_data_field = wp_list_pluck( $form_data, 'field_name' );
		$form_key_list   = wp_list_pluck( wp_list_pluck( $form_field_data, 'general_setting' ), 'field_name' );

		// Check if a required field is missing.
		$missing_item = array_diff( $form_key_list, $form_data_field );

		// Check if the missing field is required confirm password field.
		if ( in_array( 'user_confirm_password', $missing_item ) ) {
				$has_confirm_password = true;
		}

		foreach ( $form_data as $index => $single_data ) {
			if ( 'user_confirm_password' == $single_data->field_name ) {
				$confirm_password     = $single_data->value;
				$has_confirm_password = true;
				unset( $form_data[ $index ] );
			}
			if ( 'user_pass' == $single_data->field_name ) {
				$password = $single_data->value;
			}
		}

		if ( $has_confirm_password ) {
			if ( empty( $confirm_password ) ) {
				array_push( self::$response_array, __( 'Empty confirm password', 'user-registration' ) );
			} elseif ( strcmp( $confirm_password, $password ) != 0 ) {
				array_push( self::$response_array, get_option( 'user_registration_form_submission_error_message_confirm_password', __( 'Password and confirm password not matched', 'user-registration' ) ) );
			}
		}
		return $form_data;
	}

	/**
	 * Match email and confirm email field.
	 *
	 * @param  array $form_field_data Form Field Data.
	 * @param  obj   $form_data Form data submitted.
	 * @return obj $form_data
	 */
	private static function match_email( $form_field_data, &$form_data ) {

		$confirm_email_value = '';
		$has_confirm_email   = false;
		$email               = '';

		$form_data_field = wp_list_pluck( $form_data, 'field_name' );
		$form_key_list   = wp_list_pluck( wp_list_pluck( $form_field_data, 'general_setting' ), 'field_name' );

		// Check if a required field is missing.
		$missing_item = array_diff( $form_key_list, $form_data_field );

		// Check if the missing field is required confirm email field.
		if ( in_array( 'user_confirm_email', $missing_item ) ) {
			$has_confirm_email = true;
		}

		foreach ( $form_data as $index => $single_data ) {
			if ( 'user_confirm_email' == $single_data->field_name ) {
				$confirm_email_value = $single_data->value;
				$has_confirm_email   = true;
				unset( $form_data[ $index ] );
			}
			if ( 'user_email' == $single_data->field_name ) {
				$email = $single_data->value;
			}
		}

		if ( $has_confirm_email ) {
			if ( empty( $confirm_email_value ) ) {
				array_push( self::$response_array, __( 'Empty confirm email', 'user-registration' ) );
			} elseif ( strcasecmp( $confirm_email_value, $email ) != 0 ) {
				array_push( self::$response_array, get_option( 'user_registration_form_submission_error_message_confirm_email', __( 'Email and confirm email not matched', 'user-registration' ) ) );
			}
		}
		return $form_data;
	}

	/**
	 * Validate missing required fields.
	 *
	 * @param  array  $form_field_data Form Field Data.
	 * @param int    $key index of missing field in Form Field Data.
	 * @param string $value field name of missing field.
	 * @return obj $form_data
	 */
	private static function ur_missing_field_validation( $form_field_data, $key, $value ) {

		if ( $value == $form_field_data[ $key ]->general_setting->field_name ) {

			if ( isset( $form_field_data[ $key ]->general_setting->required ) && 'yes' === $form_field_data[ $key ]->general_setting->required ) {
				$field_label = $form_field_data[ $key ]->general_setting->label;
				$response    = sprintf( __( '%s is a required field.', 'user-registration' ), $field_label );
				array_push( self::$response_array, $response );
			}
		}

	}
	/**
	 * Validate password to check if match username or email address.
	 *
	 * @param  array $form_field_data Form field data.
	 * @param  array $form_data  Form data to validate.
	 */
	private static function validate_password_data( $form_field_data = array(), $form_data = array() ) {
		$email_value    = '';
		$username_value = '';
		$password_value = '';

		// Find email, username and password value.
		foreach ( $form_data as $data ) {
			if ( isset( $data->extra_params['field_key'] ) ) {
				if ( 'user_email' === $data->extra_params['field_key'] ) {
					$email_value = strtolower( $data->value );
				}
				if ( 'user_login' === $data->extra_params['field_key'] ) {
					$username_value = strtolower( $data->value );
				}
				if ( 'user_pass' === $data->extra_params['field_key'] ) {
					$password_value = strtolower( $data->value );
				}
			}
		}

		if ( $password_value === $email_value || $password_value === $username_value ) {
			array_push( self::$response_array, __( 'Password should not match with Username or Email address.', 'user-registration' ) );
		}
	}
}

return new UR_Frontend_Form_Handler();
