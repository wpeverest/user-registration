<?php
/**
 * UserRegistration UR_AJAX
 *
 * AJAX Event Handler
 *
 * @class    UR_AJAX
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_AJAX Class
 */
class UR_AJAX {

	/**
	 * Hooks in ajax handlers
	 */

	private static $field_key_aray = array();

	private static $is_field_key_pass = true;

	private static $failed_key_value = array();

	public static function init() {

		self::add_ajax_events();

	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(

			'user_input_dropped' => true,

			'form_save_action' => true,

			'user_form_submit' => true,

			'rated' => false,


		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_user_registration_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_user_registration_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}


	public static function user_form_submit() {

		check_ajax_referer( 'user_registration_form_data_save_nonce', 'security' );

		$form_id = isset( $_POST['form_id'] ) ? $_POST['form_id'] : 0;

		$nonce = isset( $_POST['ur_frontend_form_nonce'] ) ? $_POST['ur_frontend_form_nonce'] : '';

		$flag = wp_verify_nonce( $nonce, 'ur_frontend_form_id-' . $form_id );

		if ( $flag != true || is_wp_error( $flag ) ) {

			wp_send_json_error( array(
				'message' => __( 'Nonce error, please reload.', 'user-registration' ),
			) );
		}

		$users_can_register = apply_filters( 'ur_register_setting_override', get_option( 'users_can_register' ) );

		if ( ! is_user_logged_in() ) {

			if ( ! $users_can_register ) {

				wp_send_json_error( array(
					'message' => apply_filters( 'ur_register_pre_form_message', __( 'Only an administrator can add new users.', 'user-registration' ) )
				) );

			}
		} else {

			$current_user_capability = apply_filters( 'ur_registration_user_capability', 'create_users' );

			if ( ! current_user_can( $current_user_capability ) ) {

				$user_ID = get_current_user_id();

				$user = get_user_by( 'ID', $user_ID );

				global $wp;

				$current_url = home_url( add_query_arg( array(), $wp->request ) );

				$display_name = ! empty( $user->data->display_name ) ? $user->data->display_name : $user->data->user_email;

				wp_send_json_error( array(

					'message' => apply_filters( 'ur_register_pre_form_message', '<p class="alert" id="ur_register_pre_form_message">' . sprintf( __( "You are currently logged in as %1s. You don't need another account. %2s", 'user-registration' ), '<a href="#" title="' . $display_name . '">' . $display_name . '</a>', '<a href="' . wp_logout_url( $current_url ) . '" title="' . __( 'Log out of this account.', 'user-registration' ) . '">' . __( 'Logout', 'user-registration' ) . '  &raquo;</a>' ) . '</p>', $user_ID )
				) );

			}
		}

		$form_data = array();

		if ( isset( $_POST['form_data'] ) ) {

			$form_data = json_decode( stripslashes( $_POST['form_data'] ) );

		}


		UR_Frontend_Form_Handler::handle_form( $form_data, $form_id );

	}

	/**
	 * user input dropped function
	 */
	public static function user_input_dropped() {

		try {

			check_ajax_referer( 'user_input_dropped_nonce', 'security' );

			$form_field_id = ( isset( $_POST['form_field_id'] ) ) ? $_POST['form_field_id'] : null;

			if ( $form_field_id == null || $form_field_id == '' ) {

				throw  new Exception( 'Empty form data' );
			}

			$class_file_name = str_replace( 'user_registration_', '', $form_field_id );

			$class_name = ur_load_form_field_class( $class_file_name );

			if ( empty( $class_name ) ) {

				throw  new Exception( 'class not exists' );

			}

			$template = $class_name::get_instance()->get_admin_template();

			wp_send_json_success( array(
				'template' => $template,
			) );

		}
		catch ( Exception $e ) {

			wp_send_json_error( array(
				'error' => $e->getMessage(),
			) );

		}
	}


	public static function form_save_action() {

		try {

			check_ajax_referer( 'ur_form_save_nonce', 'security' );

			if ( ! isset( $_POST['data'] ) || ( isset( $_POST['data'] ) && gettype( $_POST['data'] ) != 'array' ) ) {

				throw new Exception( __( 'post data not set', 'user-registration' ) );

			} elseif ( ! isset( $_POST['data']['form_data'] ) || ( isset( $_POST['data']['form_data'] ) && gettype( $_POST['data']['form_data'] ) != 'string' ) ) {

				throw new Exception( __( 'post data not set', 'user-registration' ) );
			}

			$post_data = json_decode( stripslashes( $_POST['data']['form_data'] ) );

			self::sweep_array( $post_data );

			if ( isset( self::$failed_key_value['value'] ) && self::$failed_key_value['value'] != '' ) {

				if ( in_array( self::$failed_key_value['value'], self::$field_key_aray ) ) {

					throw  new Exception( sprintf( "Could not save form. Duplicate field name <span style='color:red'>%s</span>", self::$failed_key_value['value'] ) );

				}
			}

			if ( self::$is_field_key_pass === false ) {

				throw  new Exception( __( 'Could not save form. Invalid field name. Please check all field name', 'user-registration' ) );

			}

			$required_fields = array(

				'user_email',

				'user_password',

			);

			$containsSearch = count( array_intersect( $required_fields, self::$field_key_aray ) ) == count( $required_fields );

			if ( $containsSearch === false ) {

				throw  new Exception( __( 'Could not save form, ' . join( ', ', $required_fields ) . ' fields are required.! ', 'user-registration' ) );

			}
			$form_name = sanitize_text_field( $_POST['data']['form_name'] );

			$form_id = sanitize_text_field( $_POST['data']['form_id'] );

			$post_data = array(

				'post_type' => 'user_registration',

				'post_title' => ur_clean( $form_name ),

				'post_content' => wp_json_encode( $post_data, JSON_UNESCAPED_UNICODE ),

				'post_status' => 'publish',

				'comment_status' => 'closed',   // if you prefer

				'ping_status' => 'closed',      // if you prefer
			);

			if ( $form_id > 0 && is_numeric( $form_id ) ) {

				$post_data['ID'] = $form_id;

			}


			$post_id = wp_insert_post( $post_data );

			if ( $post_id > 0 ) {

				$post_data_setting = isset( $_POST['data']['form_setting_data'] ) ? $_POST['data']['form_setting_data'] : array();

				ur_update_form_settings( $post_data_setting, $post_id );

			}

			wp_send_json_success( array(
				'data'    => $post_data,
				'post_id' => $post_id,
			) );

		}
		catch ( Exception $e ) {

			wp_send_json_error( array(
				'message' => $e->getMessage(),
			) );

		}// End try().

	}

	private static function is_regex_pass( $value ) {

		$field_regex = "/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/";

		if ( preg_match( $field_regex, $value, $match ) ) :

			if ( $match !== null && count( $match ) == 1 && $match[0] === $value ) {

				return true;
			}

		endif;

		return false;

	}

	public static function sweep_array( &$array ) {
		
		foreach ( $array as $key => &$value ) {

			if ( is_array( $value ) || gettype( $value ) == 'object' ) {

				self::sweep_array( $value );

			} else {

				if ( $key == 'field_name' ) {

					$regex_status = self::is_regex_pass( $value );

					if ( ! $regex_status || in_array( $value, self::$field_key_aray ) ) {

						self::$is_field_key_pass = false;

						self::$failed_key_value = array(
							'key'   => $key,
							'value' => $value,
						);

						return;
					}
					array_push( self::$field_key_aray, $value );
				}
				if( $key === 'description' ) {
					$value = str_replace('"', "'", $value); //TODO:: use wp_kses to allow certain html
				}
				else{
					$value = sanitize_text_field( $value );
				}
			}
		}
	}

	/**
	 * @since 1.1.2
	 * Triggered when clicking the rating footer.
	 */
	public static function rated() {
		if ( ! current_user_can( 'manage_user_registration' ) ) {
			wp_die( - 1 );
		}
		update_option( 'user_registration_admin_footer_text_rated', 1 );
		wp_die();
	}

}

UR_AJAX::init();
