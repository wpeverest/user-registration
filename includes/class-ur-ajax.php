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
	private static $field_key_aray    = array();
	private static $is_field_key_pass = true;
	private static $failed_key_value  = array();

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
			'deactivation_notice'    => false,
			'rated'                  => false,
			'dashboard_widget'       => false,
			'dismiss_review_notice'  => false,
			'import_form_action'     => false,
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

		if ( is_user_logged_in() && ! current_user_can( 'administrator' ) ) {
			wp_send_json_error(
				array(
					'message' => ur_string_translation( null, 'ur_already_logged_in', __( 'You are already logged in.', 'user-registration' ) ),
				)
			);
		}

		if ( ! check_ajax_referer( 'user_registration_form_data_save_nonce', 'security', false ) ) {
			wp_send_json_error(
				array(
					'message' => ur_string_translation( null, 'ur_nonce_error', __( 'Nonce error, please reload.', 'user-registration' ) ),
				)
			);
		}

		$form_id           = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$nonce             = isset( $_POST['ur_frontend_form_nonce'] ) ? $_POST['ur_frontend_form_nonce'] : '';
		$captcha_response  = isset( $_POST['captchaResponse'] ) ? $_POST['captchaResponse'] : '';
		$flag              = wp_verify_nonce( $nonce, 'ur_frontend_form_id-' . $form_id );
		$recaptcha_enabled = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_enable_recaptcha_support', 'no' );
		$recaptcha_version = get_option( 'user_registration_integration_setting_recaptcha_version' );
		$secret_key        = 'v3' === $recaptcha_version ? get_option( 'user_registration_integration_setting_recaptcha_site_secret_v3' ) : get_option( 'user_registration_integration_setting_recaptcha_site_secret' );

		if ( 'yes' == $recaptcha_enabled || '1' == $recaptcha_enabled ) {
			if ( ! empty( $captcha_response ) ) {

				$data = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha_response );
				$data = json_decode( wp_remote_retrieve_body( $data ) );

				if ( empty( $data->success ) || ( isset( $data->score ) && $data->score < apply_filters( 'user_registration_recaptcha_v3_threshold', 0.5 ) ) ) {
					wp_send_json_error(
						array(
							'message' => ur_string_translation( null, 'ur_recatcha_error', __( 'Error on google reCaptcha. Contact your site administrator.', 'user-registration' ) ),
						)
					);
				}
			} else {
				wp_send_json_error(
					array(
						'message' => get_option( 'user_registration_form_submission_error_message_recaptcha', __( 'Captcha code error, please try again.', 'user-registration' ) ),
					)
				);
			}
		}

		if ( $flag != true || is_wp_error( $flag ) ) {
			wp_send_json_error(
				array(
					'message' => ur_string_translation( null, 'ur_nonce_error', __( 'Nonce error, please reload.', 'user-registration' ) ),
				)
			);
		}

		$users_can_register = apply_filters( 'ur_register_setting_override', get_option( 'users_can_register' ) );

		if ( ! is_user_logged_in() ) {

			if ( ! $users_can_register ) {
				wp_send_json_error(
					array(
						'message' => ur_string_translation( null, 'ur_only_administrator_can_register', apply_filters( 'ur_register_pre_form_message', __( 'Only administrators can add new users.', 'user-registration' ) ) ),
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
						'message' => apply_filters( 'ur_register_pre_form_message', '<p class="alert" id="ur_register_pre_form_message">' . sprintf( __( ur_string_translation( null, 'ur_currently_logged_in', 'You are currently logged in as %1$1s. %2$2s' ), 'user-registration' ), '<a href="#" title="' . $display_name . '">' . $display_name . '</a>', '<a href="' . wp_logout_url( $current_url ) . '" title="' . __( 'Log out of this account.', 'user-registration' ) . '">' . ur_string_translation( null, 'ur_logout_menu',__( 'Logout', 'user-registration' ) ) . '  &raquo;</a>' ) . '</p>', $user_ID ),
					)
				);
			}
		}

		$form_data = array();

		if ( isset( $_POST['form_data'] ) ) {
			$form_data = json_decode( stripslashes( $_POST['form_data'] ) );
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
					'message' => ur_string_translation( null, 'ur_nonce_error', __( 'Nonce error, please reload.', 'user-registration' ) ),
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
			$form_data = json_decode( stripslashes( $_POST['form_data'] ) );
			foreach ( $form_data as $data ) {
				$single_field[ $data->field_name ] = isset( $data->value ) ? $data->value : '';
				$data->field_name                  = substr( $data->field_name, 18 );
			}
		}

		if ( isset( $single_field['user_registration_profile_pic_url'] ) && ! empty( $single_field['user_registration_profile_pic_url'] ) ) {
			update_user_meta( $user_id, 'user_registration_profile_pic_url', $single_field['user_registration_profile_pic_url'] );
		}

		$profile = user_registration_form_data( $user_id, $form_id );

		foreach ( $profile as $key => $field ) {

			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}
			// Get Value.
			switch ( $field['type'] ) {
				case 'checkbox':
					if ( isset( $single_field[ $key ] ) ) {
						// Serialize values fo checkbox field.
						$single_field[ $key ] = ( json_decode( $single_field[ $key ] ) !== null ) ? json_decode( $single_field[ $key ] ) : $single_field[ $key ];
					}
					break;
				default:
					$single_field[ $key ] = isset( $single_field[ $key ] ) ? ur_clean( $single_field[ $key ] ) : '';
					break;
			}

			// Hook to allow modification of value.
			$single_field[ $key ] = apply_filters( 'user_registration_process_myaccount_field_' . $key, $single_field[ $key ] );

			if ( 'user_registration_user_email' === $key ) {
				do_action( 'user_registration_validate_email_whitelist', $single_field[ $key ], '' );

				// Check if email already exists before updating user details.
				if ( email_exists( $single_field[ $key ] ) === 1 ) {
					wp_send_json_error(
						array(
							'message' => ur_string_translation( null, 'ur_email_already_exist', __( 'Email already exists.', 'user-registration' ) ),
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
		}// End foreach().

		do_action( 'user_registration_after_save_profile_validation', $user_id, $profile );

		if ( 0 === ur_notice_count( 'error' ) ) {
			$user_data = array();

			foreach ( $profile as $key => $field ) {
				$new_key = str_replace( 'user_registration_', '', $key );

				if ( in_array( $new_key, ur_get_user_table_fields() ) ) {

					if ( $new_key === 'display_name' ) {
						$user_data['display_name'] = $single_field[ $key ];
					} else {
						$user_data[ $new_key ] = $single_field[ $key ];
					}
				} else {
					$update_key = $key;

					if ( in_array( $new_key, ur_get_registered_user_meta_fields() ) ) {
						$update_key = str_replace( 'user_', '', $new_key );
					}
					$disabled = isset( $field['custom_attributes']['disabled'] ) ? $field['custom_attributes']['disabled'] : '';

					if ( $disabled !== 'disabled' ) {

						update_user_meta( $user_id, $update_key, $single_field[ $key ] );
					}
				}
			}

			if ( count( $user_data ) > 0 ) {
				$user_data['ID'] = get_current_user_id();
				wp_update_user( $user_data );
			}

			$message = ur_string_translation( null, 'ur_profile_updated_successfully', __( 'User profile updated successfully.', 'user-registration' ) );
			do_action( 'user_registration_save_profile_details', $user_id, $form_id );

			wp_send_json_success(
				array(
					'message' => $message,
				)
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
					'message' => ur_string_translation( null, 'ur_nonce_error', __( 'Nonce error, please reload.', 'user-registration' ) ),
				)
			);
		}
		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		if ( isset( $_FILES['file'] ) && $_FILES['file']['size'] ) {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$upload = isset( $_FILES['file'] ) ? $_FILES['file'] : array();

			$post_overrides = array(
				'post_status' => 'publish',
				'post_title'  => $upload['name'],
			);
			$attachment_id  = media_handle_sideload( $upload, (int) 0, $post_overrides['post_title'], $post_overrides );

			if ( is_wp_error( $attachment_id ) ) {

				wp_send_json_error(
					array(

						'message' => $attachment_id->get_error_message(),
					)
				);
			}

			$url = wp_get_attachment_thumb_url( $attachment_id );
			if ( empty( $url ) ) {
				$url = home_url() . '/wp-includes/images/media/text.png';
			}

			wp_send_json_success(
				array(
					'url' => $url,
				)
			);

		} elseif ( UPLOAD_ERR_NO_FILE !== $_FILES['file']['error'] ) {

			switch ( $_FILES['file']['error'] ) {
				case UPLOAD_ERR_INI_SIZE:
					wp_send_json_error(
						array(
							'message' => ur_string_translation( null, 'ur_file_size_exceed', __( 'File size exceed, please check your file size.', 'user-registration' ) ),
						)
					);
					break;
				default:
					wp_send_json_error(
						array(
							'message' => ur_string_translation( null, 'ur_uploading_error', __( 'Something went wrong while uploading, please contact your site administrator.', 'user-registration' ) ),
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
	 */
	public static function form_save_action() {

		try {

			check_ajax_referer( 'ur_form_save_nonce', 'security' );

			if ( ! isset( $_POST['data'] ) || ( isset( $_POST['data'] ) && gettype( $_POST['data'] ) != 'array' ) ) {
				throw new Exception( __( 'post data not set', 'user-registration' ) );

			} elseif ( ! isset( $_POST['data']['form_data'] )
			|| ( isset( $_POST['data']['form_data'] )
			&& gettype( $_POST['data']['form_data'] ) != 'string' ) ) {

				throw new Exception( __( 'post data not set', 'user-registration' ) );
			}

			$post_data = json_decode( stripslashes( $_POST['data']['form_data'] ) );

			$post_data = self::ur_add_to_advanced_settings( $post_data ); // Backward compatibility method. Since @1.5.7.

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
				'user_pass',
			);

			$containsSearch = count( array_intersect( $required_fields, self::$field_key_aray ) ) == count( $required_fields );

			if ( $containsSearch === false ) {
				throw  new Exception( __( 'Could not save form, ' . join( ', ', $required_fields ) . ' fields are required.! ', 'user-registration' ) );
			}

			$form_name    = sanitize_text_field( $_POST['data']['form_name'] );
			$form_row_ids = sanitize_text_field( $_POST['data']['form_row_ids'] );
			$form_id      = sanitize_text_field( $_POST['data']['form_id'] );

			$post_data = array(
				'post_type'      => 'user_registration',
				'post_title'     => ur_clean( $form_name ),
				'post_content'   => wp_json_encode( $post_data, JSON_UNESCAPED_UNICODE ),
				'post_status'    => 'publish',
				'comment_status' => 'closed',   // if you prefer
				'ping_status'    => 'closed',      // if you prefer
			);

			if ( $form_id > 0 && is_numeric( $form_id ) ) {
				$post_data['ID'] = $form_id;
			}

			remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );

			$post_id = wp_insert_post( wp_slash( $post_data ) );

			if ( $post_id > 0 ) {
				$_POST['data']['form_id'] = $post_id; // Form id for new form.

				$post_data_setting = isset( $_POST['data']['form_setting_data'] ) ? $_POST['data']['form_setting_data'] : array();
				ur_update_form_settings( $post_data_setting, $post_id );

				// Form row_id save.
				update_post_meta( $form_id, 'user_registration_form_row_ids', $form_row_ids );
			}

			do_action( 'user_registration_after_form_settings_save', $_POST['data'] );

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

		$form_id = isset( $_POST['form_id'] ) ? $_POST['form_id'] : 0;

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
	 * @param  string $value
	 * @return boolean
	 */
	private static function is_regex_pass( $value ) {

		$field_regex = "/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/";

		if ( preg_match( $field_regex, $value, $match ) ) :
			if ( $match !== null && count( $match ) == 1 && $match[0] === $value ) {
				return true;
			}

			endif;

		return false;
	}

	/**
	 * Sanitize values of form field in backend
	 *
	 * @param  array &$array
	 */
	public static function sweep_array( &$array ) {

		foreach ( $array as $key => &$value ) {

			if ( is_array( $value ) || gettype( $value ) == 'object' ) {
				self::sweep_array( $value );

			} else {

				if ( $key === 'field_name' ) {
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
				if ( $key === 'description' ) {
					$value = str_replace( '"', "'", $value );
					$value = wp_kses(
						$value,
						array(
							'a'      => array(
								'href'   => array(),
								'title'  => array(),
								'target' => array(),
							),
							'br'     => array(),
							'em'     => array(),
							'strong' => array(),
						)
					);

				} elseif ( $key == 'html' ) {

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
	 * Dismiss review notice
	 *
	 * @since  1.5.8
	 *
	 * @return void
	 **/
	public static function dismiss_review_notice() {

		check_admin_referer( 'review-nonce', 'security' );

		if ( ! empty( $_POST['dismissed'] ) ) {
			update_option( 'user_registration_review_notice_dismissed', 'yes' );
		}
	}
}

UR_AJAX::init();
