<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Frontend_Form_Handler
 * @version  1.0.0
 * @package  UserRegistration/Frontend
 * @category Admin
 * @author   WPEverest
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * UR_Frontend_Form_Handler Class
 */
class UR_Frontend_Form_Handler {
	public static $form_id = 0;
	public static $response_array = array();
	private static $valid_form_data = array();

	public static function handle_form( $form_data, $form_id ) {

		self::$form_id = $form_id;
		$post_content = self::get_post_content( $form_id );
		$post_content_array = array();
		if ( ! empty( $post_content ) ) {
			$post_content_array = json_decode( $post_content );
		}
		if ( gettype( $form_data ) != 'array' && gettype( $form_data ) != 'object' ) {
			$form_data = array();
		}
		self::match_password( $form_data );
		$form_field_data = self::get_form_field_data( $post_content_array );

		self::add_hook( $form_field_data, $form_data );
		self::validate_form_data( $form_field_data, $form_data );
		if ( count( self::$response_array ) == 0 ) {
			$user_role = ! in_array( ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_default_user_role' ), array_keys( ur_get_default_admin_roles() ) ) ? 'subscriber' : ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_default_user_role' );
			$userdata = array(
				'user_login' => isset( self::$valid_form_data['user_username'] ) ? self::$valid_form_data['user_username']->value : '',
				'user_pass' => self::$valid_form_data['user_password']->value,
				'user_email' => self::$valid_form_data['user_email']->value,
				'display_name' => isset( self::$valid_form_data['user_display_name']->value ) ? self::$valid_form_data['user_display_name']->value : '',
				'user_url' => isset( self::$valid_form_data['user_url']->value ) ? self::$valid_form_data['user_url']->value : '',
				// When creating an user, `user_pass` is expected.
				'role'     => $user_role,
			);

			self::$valid_form_data = apply_filters( 'user_registration_before_register_user_filter', self::$valid_form_data, $form_id );

			do_action( 'user_registration_before_register_user_action', self::$valid_form_data, $form_id );

			if( empty( $userdata['user_login'] ) ) {

				$part_of_email = explode( "@", $userdata['user_email'] );

				$username = check_username( $part_of_email[0] );
				
				$userdata['user_login'] = $username;
				
			}

			$user_id = wp_insert_user( $userdata );


			self::ur_update_user_meta( $user_id, self::$valid_form_data, $form_id );
			do_action( 'user_registration_after_register_user_action', self::$valid_form_data, $form_id, $user_id );
			if ( $user_id > 0 ) {
				$login_option = get_option( 'user_registration_general_setting_login_options', 'default' );
				$success_params = array(
					'username' => isset( self::$valid_form_data['user_username'] ) ? self::$valid_form_data['user_username']->value : '',
				);
				if ( 'auto_login' === $login_option ) {
					wp_clear_auth_cookie();
					wp_set_auth_cookie( $user_id );
					$success_params['auto_login'] = true;
				}

				wp_send_json_success( $success_params );
			}
			wp_send_json_error( array(
				'message' => __( 'Someting error! please try again', 'user-registration' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => array_unique(self::$response_array),
			) );
		}// End if().
	}
	private static function get_form_field_data( $post_content_array ) {
		$form_field_data_array = array();
		foreach ( $post_content_array as $row_index => $row ) {
			foreach ( $row as $grid_index => $grid ) {
				foreach ( $grid as $field_index => $field ) {
					if ( 'confirm_user_password' != $field->general_setting->field_name ) {
						array_push( $form_field_data_array, $field );
					}
				}
			}
		}
		return ( $form_field_data_array );
	}
	private static function get_post_content( $form_id ) {
		$args      = array(
			'post_type' => 'user_registration',
			'post_status' => 'publish',
			'post__in' => array( $form_id ),
		);
		$post_data = get_posts( $args );
		if ( isset( $post_data[0]->post_content ) ) {
			return $post_data[0]->post_content;
		} else {
			return '';
		}
	}
	private static function validate_form_data( $form_field_data = array(), $form_data = array() ) {
		$form_data_field = wp_list_pluck( $form_data, 'field_name' );

		$form_key_list = wp_list_pluck( wp_list_pluck( $form_field_data, 'general_setting' ), 'field_name' );
		$duplicate_field_key = array_diff_key( $form_data_field, array_unique( $form_data_field ) );
		if ( count( $duplicate_field_key ) > 0 ) {
			array_push( self::$response_array, __( 'Duplicate field key in form, please contact site administrator.', 'user-registration' ) );
		}

		$containsSearch = count( array_intersect( ur_get_required_fields(), $form_data_field ) ) == count( ur_get_required_fields() );

		if ( false === $containsSearch ) {
			array_push( self::$response_array, __( 'Required form field not found.', 'user-registration' ) );
		}

		foreach ( $form_data as $data ) {

			if ( in_array( $data->field_name, $form_key_list ) ) {
				self::$valid_form_data[ $data->field_name ] = self::get_sanitize_value( $data );
				$form_data_index = array_search( $data->field_name, $form_key_list );
				$single_form_field = $form_field_data[ $form_data_index ];
				$general_setting = isset( $single_form_field->general_setting ) ? $single_form_field->general_setting : new stdClass();
				$single_field_key = $single_form_field->field_key;
				$single_field_label = isset( $general_setting->label ) ? $general_setting->label : '';
				self::$valid_form_data[ $data->field_name ]->extra_params = array(
					'field_key' => $single_field_key,
					'label'     => $single_field_label
				);
				$hook = "user_registration_validate_{$single_form_field->field_key}";
				$filter_hook = $hook . '_message';
				do_action( $hook, $single_form_field, $data, $filter_hook, self::$form_id );
				$response = apply_filters( $filter_hook, '' );
				if ( ! empty( $response ) ) {
					array_push( self::$response_array, $response );
				}
			}
		}
	}
	public static function add_hook( $form_field_data = array(), $form_data = array() ) {
		$form_key_list = wp_list_pluck( wp_list_pluck( $form_field_data, 'general_setting' ), 'field_name' );
		foreach ( $form_data as $data ) {
			if ( in_array( $data->field_name, $form_key_list ) ) {
				$form_data_index = array_search( $data->field_name, $form_key_list );
				$single_form_field = $form_field_data[ $form_data_index ];
				$class_name = ur_load_form_field_class( $single_form_field->field_key );
				$hook = "user_registration_validate_{$single_form_field->field_key}";
				add_action( $hook, array(
					$class_name::get_instance(),
					'validation',
				), 10, 4 );
			}
		}
	}
	private static function get_sanitize_value( &$form_data ) {
		$field_name = isset( $form_data->field_name ) ? $form_data->field_name : '';
		switch ( $field_name ) {
			case 'user_email':
				$form_data->value = sanitize_email( $form_data->value );
				break;
			case 'user_username':
				$form_data->value = sanitize_user( $form_data->value );
				break;
			case 'user_password':
				break;
			default:
				$form_data->value = sanitize_text_field( $form_data->value );
		}
		return $form_data;
	}

	private static function ur_update_user_meta( $user_id, $valid_form_data, $form_id ) {

		foreach ( $valid_form_data as $data ) {
			if ( ! in_array( trim( $data->field_name ), ur_get_user_table_fields() ) ) {
				$field_key           = $data->field_name;
				$field_key_for_param = $data->field_name;

				$default_meta = array( 'user_description', 'user_nickname', 'user_first_name', 'user_last_name' );
				
				$woocommerce_fields = function_exists( 'ur_get_all_woocommerce_fields' ) ? ur_get_all_woocommerce_fields() : array(); 
				
				if( in_array( $field_key, $default_meta ) ) {
					$field_key = trim( str_replace( 'user_', '', $field_key ) );				
				} 
				elseif( in_array( $field_key, $woocommerce_fields) ) {
					// do nothing 
				}
				else {
					$field_key = 'user_registration_' . $field_key;
				}

				if( isset( $data->extra_params['field_key'] ) && $data->extra_params['field_key'] === 'checkbox' ) {
					$data->value = json_decode( $data->value );	
				}
				update_user_meta( $user_id, $field_key, $data->value );
			}
		update_user_meta( $user_id, 'ur_form_id', $form_id );
		}
	}
	private static function match_password( &$form_data ) {
		$confirm_password     = '';
		$has_confirm_password = false;
		$password             = '';
		foreach ( $form_data as $index => $single_data ) {
			if ( 'user_confirm_password' == $single_data->field_name ) {
				$confirm_password = $single_data->value;
				$has_confirm_password = true;
				unset( $form_data[ $index ] );
			}
			if ( 'user_password' == $single_data->field_name ) {
				$password = $single_data->value;
			}
		}
		if ( $has_confirm_password ) {
			if ( empty( $confirm_password ) ) {
				array_push( self::$response_array, __( 'Empty confirm password', 'user-registration' ) );
			} elseif ( strcasecmp( $confirm_password, $password ) != 0 ) {
				array_push( self::$response_array, __( 'Password and confirm password not matched', 'user-registration' ) );
			}
		}
		return $form_data;
	}
}
return new UR_Frontend_Form_Handler();
