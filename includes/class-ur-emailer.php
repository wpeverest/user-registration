<?php
/**
 * Emailer class
 *
 * @class    UR_Emailer
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Emailer Class.
 */
class UR_Emailer {

	/**
	 * Query vars to add to wp
	 *
	 * @var public array Query vars to add to wp
	 */
	public $query_vars = array();

	/**
	 * Init function.
	 */
	public static function init() {

		if ( 'yes' === get_option( 'user_registration_email_setting_disable_email' ) ) {
			return;
		}

		add_action( 'user_registration_email_send_before', array( __CLASS__, 'ur_send_email_before' ) );
		add_action( 'user_registration_email_send_after', array( __CLASS__, 'ur_send_email_after' ) );

		add_action(
			'user_registration_after_register_user_action',
			array(
				__CLASS__,
				'ur_after_register_mail',
			),
			10,
			3
		);
		add_action(
			'user_registration_save_profile_details',
			array(
				__CLASS__,
				'ur_profile_details_changed_mail',
			),
			10,
			2
		);
	}

	/**
	 * Apply filters to modify sender's details before email is sent.
	 */
	public static function ur_send_email_before() {
		add_filter( 'wp_mail_from', array( __CLASS__, 'ur_sender_email' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'ur_sender_name' ) );
	}

	/**
	 * Remove filters after the email is sent.
	 *
	 * @since 1.4.6
	 */
	public static function ur_send_email_after() {
		remove_filter( 'wp_mail_from', array( __CLASS__, 'ur_sender_email' ) );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, 'ur_sender_name' ) );
	}


	/**
	 * Sender's Email address
	 *
	 * @return string sender's email
	 */
	public static function ur_sender_email() {
		$sender_email = get_option( 'user_registration_email_from_address', get_option( 'admin_email' ) );
		return $sender_email;
	}

	/**
	 * Sender's name.
	 *
	 * @return string sender's name
	 */
	public static function ur_sender_name() {
		$sender_name = get_option( 'user_registration_email_from_name', esc_attr( get_bloginfo( 'name', 'display' ) ) );
		return $sender_name;
	}

	/**
	 * Emails Headers.
	 *
	 * @return string email header
	 */
	public static function ur_get_header() {
		$header  = 'From: ' . self::ur_sender_name() . ' <' . self::ur_sender_email() . ">\r\n";
		$header .= 'Reply-To: ' . self::ur_sender_email() . "\r\n";
		$header .= "Content-Type: text/html; charset=UTF-8\r\n";

		return $header;
	}

	/**
	 * Email sending process after registration hook.
	 *
	 * @param  array $valid_form_data Form filled data.
	 * @param  int   $form_id         Form ID.
	 * @param  int   $user_id         User ID.
	 * @return void
	 */
	public static function ur_after_register_mail( $valid_form_data, $form_id, $user_id ) {

		$attachments     = apply_filters( 'user_registration_email_attachment', array(), $valid_form_data, $form_id, $user_id );
		$valid_form_data = isset( $valid_form_data ) ? $valid_form_data : array();
		$name_value      = array();
		$data_html       = '';

		// Generate $data_html string to replace for {{all_fields}} smart tag.
		foreach ( $valid_form_data as $field_meta => $form_data ) {
			if ( 'user_confirm_password' === $field_meta ) {
				continue;
			}

			// Donot include privacy policy value.
			if ( isset( $form_data->extra_params['field_key'] ) && 'privacy_policy' === $form_data->extra_params['field_key'] ) {
				continue;
			}

			// Process for file upload.
			if ( isset( $form_data->extra_params['field_key'] ) && 'file' === $form_data->extra_params['field_key'] ) {
				$form_data->value = isset( $form_data->value ) ? wp_get_attachment_url( $form_data->value ) : '';
			}

			$label      = isset( $form_data->extra_params['label'] ) ? $form_data->extra_params['label'] : '';
			$field_name = isset( $form_data->field_name ) ? $form_data->field_name : '';
			$value      = isset( $form_data->value ) ? $form_data->value : '';

			if ( 'user_pass' === $field_meta ) {
				$value = __( 'Chosen Password', 'user-registration' );
			}

			// Check if value contains array.
			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}

			$data_html .= $label . ' : ' . $value . '<br/>';

			$name_value[ $field_name ] = $value;
		}

		// Smart tag process for extra fields.
		$name_value = apply_filters( 'user_registration_process_smart_tag', $name_value, $form_data, $form_id, $user_id );

		$email_object      = isset( $valid_form_data['user_email'] ) ? $valid_form_data['user_email'] : array();
		$user_login_object = isset( $valid_form_data['user_login'] ) ? $valid_form_data['user_login'] : array();
		$email             = isset( $email_object->value ) && ! empty( $email_object->value ) ? $email_object->value : '';
		$username          = isset( $user_login_object->value ) && ! empty( $user_login_object->value ) ? $user_login_object->value : '';

		if ( ! empty( $email ) && ! empty( $user_id ) ) {

			do_action( 'user_registration_email_send_before' );

			self::send_mail_to_user( $email, $username, $user_id, $data_html, $name_value, $attachments );
			self::send_mail_to_admin( $email, $username, $user_id, $data_html, $name_value, $attachments );

			do_action( 'user_registration_email_send_after' );
		}
	}

	/**
	 * Email sending process after profile details changed by user.
	 *
	 * @param  int $user_id         User ID.
	 * @param  int $form_id         Form ID.
	 * @return void
	 * @since 1.6.3
	 */
	public static function ur_profile_details_changed_mail( $user_id, $form_id ) {
		$profile      = user_registration_form_data( $user_id, $form_id );
		$name_value   = array();
		$data_html    = '';
		$smart_data   = array();
		$email        = '';
		$user_data    = get_userdata( $user_id );
		$username     = $user_data->user_login;
		$single_field = array();

		if ( 'yes' === get_option( 'user_registration_ajax_form_submission_on_edit_profile', 'no' ) ) {

			if ( isset( $_POST['form_data'] ) ) {
				$form_data = json_decode( stripslashes( $_POST['form_data'] ) );
				foreach ( $form_data as $data ) {
					$single_field[ $data->field_name ] = isset( $data->value ) ? $data->value : '';
					$data->field_name                  = substr( $data->field_name, 18 );
				}
			}
		} else {
			$single_field = $_POST;
		}

		// Generate $data_html string to replace for {{all_fields}} smart tag.
		foreach ( $profile as $key => $form_data ) {
			$field_name = str_replace( 'user_registration_', '', $key );

			// Check if value contains array.
			// @codingStandardsIgnoreStart
			error_log( print_r( $single_field, true ) );
			$value = ur_clean( $single_field[ $key ] );
			if ( is_array( $single_field[ $key ] ) ) {
				$value = implode( ',', $single_field[ $key ] );
			}
			// @codingStandardsIgnoreEnd

			$data_html                .= $form_data['label'] . ' : ' . $value . '<br/>';
			$field_key                 = isset( $form_data['field_key'] ) ? $form_data['field_key'] : '';
			$name_value[ $field_name ] = $value;

			if ( 'user_email' === $field_name ) {
				$email = $value;
			}
			$tmp_data['value']        = $value;
			$tmp_data['field_type']   = $form_data['type'];
			$tmp_data['label']        = $form_data['label'];
			$tmp_data['field_name']   = $field_name;
			$tmp_data['extra_params'] = array(
				'field_key' => $field_key,
				'label'     => $form_data['label'],
			);

			array_push( $smart_data, $tmp_data );
		}

		// Smart tag process for extra fields.
		$attachments = apply_filters( 'user_registration_email_attachment', array(), $smart_data, $form_id, $user_id );
		$name_value  = apply_filters( 'user_registration_process_smart_tag', $name_value, $smart_data, $form_id, $user_id );

		if ( ! empty( $email ) && ! empty( $user_id ) ) {

			do_action( 'user_registration_email_send_before' );

			self::send_profile_changed_email_to_admin( $email, $username, $user_id, $data_html, $name_value, $attachments );

			do_action( 'user_registration_email_send_after' );
		}
	}


	/**
	 * Trigger the user email.
	 *
	 * @param  string $email Email of the user.
	 * @param  string $username   Username of the user.
	 * @param  int    $user_id       User id.
	 * @param  string $data_html  String replaced with {{all_fields}} smart tag.
	 * @param  array  $name_value Array to replace with extra fields smart tag.
	 * @param  array  $attachments Email Attachment.
	 * @return void
	 */
	public static function send_mail_to_user( $email, $username, $user_id, $data_html, $name_value, $attachments ) {

		$form_id      = ur_get_form_id_by_userid( $user_id );
		$login_option = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );
		$attachment   = isset( $attachments['user'] ) ? $attachments['user'] : '';
		$status       = ur_get_user_approval_status( $user_id );
		$email_status = get_user_meta( $user_id, 'ur_confirm_email', true );
		$email_token  = get_user_meta( $user_id, 'ur_confirm_email_token', true );
		$values       = array(
			'username'    => $username,
			'email'       => $email,
			'all_fields'  => $data_html,
			'email_token' => $email_token,
		);

		if ( '0' === $email_status ) {
			$subject = get_option( 'user_registration_email_confirmation_subject', __( 'Please confirm your registration on {{blog_info}}', 'user-registration' ) );
			$message = new UR_Settings_Email_Confirmation();
			$message = $message->ur_get_email_confirmation();
			$message = get_option( 'user_registration_email_confirmation', $message );
			$message = self::parse_smart_tags( $message, $values, $name_value );
			$subject = self::parse_smart_tags( $subject, $values, $name_value );

			wp_mail( $email, $subject, $message, self::ur_get_header(), $attachment );
		} elseif ( 0 === intval( $status ) ) {
			$subject = get_option( 'user_registration_awaiting_admin_approval_email_subject', __( 'Thank you for registration on {{blog_info}}', 'user-registration' ) );
			$message = new UR_Settings_Awaiting_Admin_Approval_Email();
			$message = $message->ur_get_awaiting_admin_approval_email();
			$message = get_option( 'user_registration_awaiting_admin_approval_email', $message );
			$message = self::parse_smart_tags( $message, $values, $name_value );
			$subject = self::parse_smart_tags( $subject, $values, $name_value );

			if ( 'yes' === get_option( 'user_registration_enable_awaiting_admin_approval_email', 'yes' ) ) {
				wp_mail( $email, $subject, $message, self::ur_get_header(), $attachment );
			}
		} elseif ( -1 === intval( $status ) ) {
			$subject = get_option( 'user_registration_registration_denied_email_subject', __( 'Sorry! Registration denied on {{blog_info}}', 'user-registration' ) );
			$message = new UR_Settings_Registration_Denied_Email();
			$message = $message->ur_get_registration_denied_email();
			$message = get_option( 'user_registration_registration_denied_email', $message );
			$message = self::parse_smart_tags( $message, $values, $name_value );
			$subject = self::parse_smart_tags( $subject, $values, $name_value );

			if ( 'yes' === get_option( 'user_registration_enable_registration_denied_email', 'yes' ) ) {
				wp_mail( $email, $subject, $message, self::ur_get_header(), $attachment );
			}
		} elseif ( 'default' === $login_option || 'auto_login' === $login_option ) {
			$subject = get_option( 'user_registration_successfully_registered_email_subject', __( 'Congratulations! Registration Complete on {{blog_info}}', 'user-registration' ) );
			$message = new UR_Settings_Successfully_Registered_Email();
			$message = $message->ur_get_successfully_registered_email();
			$message = get_option( 'user_registration_successfully_registered_email', $message );
			$message = self::parse_smart_tags( $message, $values, $name_value );
			$subject = self::parse_smart_tags( $subject, $values, $name_value );

			if ( 'yes' === get_option( 'user_registration_enable_successfully_registered_email', 'yes' ) ) {
				wp_mail( $email, $subject, $message, self::ur_get_header(), $attachment );
			}
		}
	}

	/**
	 * Trigger the admin email.
	 *
	 * @param  string $user_email Email of the user.
	 * @param  string $username   Username of the user.
	 * @param  int    $user_id       User id.
	 * @param  string $data_html  String replaced with {{all_fields}} smart tag.
	 * @param  array  $name_value Array to replace with extra fields smart tag.
	 * @param  array  $attachments Email Attachement.
	 * @return void
	 */
	public static function send_mail_to_admin( $user_email, $username, $user_id, $data_html, $name_value, $attachments ) {

		$header  = "Reply-To: {{email}} \r\n";
		$header .= 'Content-Type: text/html; charset=UTF-8';

		$attachment  = isset( $attachments['admin'] ) ? $attachments['admin'] : '';
		$admin_email = get_option( 'user_registration_admin_email_receipents', get_option( 'admin_email' ) );
		$admin_email = explode( ',', $admin_email );
		$admin_email = array_map( 'trim', $admin_email );

		$subject = get_option( 'user_registration_admin_email_subject', __( 'A New User Registered', 'user-registration' ) );
		$message = new UR_Settings_Admin_Email();
		$message = $message->ur_get_admin_email();
		$message = get_option( 'user_registration_admin_email', $message );

		$values  = array(
			'username'   => $username,
			'email'      => $user_email,
			'all_fields' => $data_html,
		);
		$message = self::parse_smart_tags( $message, $values, $name_value );
		$subject = self::parse_smart_tags( $subject, $values, $name_value );
		$header  = self::parse_smart_tags( $header, $values, $name_value );

		if ( 'yes' === get_option( 'user_registration_enable_admin_email', 'yes' ) ) {
			foreach ( $admin_email as $email ) {
				wp_mail( $email, $subject, $message, $header, $attachment );
			}
		}
	}

	/**
	 * Trigger status change email while admin changes users status on admin approval.
	 *
	 * @param  string $email    Email address of the user.
	 * @param  string $username Username of the user.
	 * @param  int    $status   Stautus of the user.
	 * @return void
	 */
	public static function status_change_email( $email, $username, $status ) {

		// Get name value pair to replace smart tag.
		$name_value = self::user_data_smart_tags( $email );

		$values = array(
			'username' => $username,
			'email'    => $email,
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		if ( 0 === intval( $status ) ) {

			$subject = get_option( 'user_registration_registration_pending_email_subject', __( 'Sorry! Registration changed to pending on {{blog_info}}', 'user-registration' ) );
			$message = new UR_Settings_Registration_Pending_Email();
			$message = $message->ur_get_registration_pending_email();
			$message = get_option( 'user_registration_registration_pending_email', $message );
			$message = self::parse_smart_tags( $message, $values, $name_value );
			$subject = self::parse_smart_tags( $subject, $values, $name_value );

			if ( 'yes' === get_option( 'user_registration_enable_registration_pending_email', 'yes' ) ) {
				wp_mail( $email, $subject, $message, self::ur_get_header() );
			}
		} elseif ( -1 === intval( $status ) ) {

			$subject = get_option( 'user_registration_registration_denied_email_subject', __( 'Sorry! Registration denied on {{blog_info}}', 'user-registration' ) );
			$message = new UR_Settings_Registration_Denied_Email();
			$message = $message->ur_get_registration_denied_email();
			$message = get_option( 'user_registration_registration_denied_email', $message );
			$message = self::parse_smart_tags( $message, $values, $name_value );
			$subject = self::parse_smart_tags( $subject, $values, $name_value );

			if ( 'yes' === get_option( 'user_registration_enable_registration_denied_email', 'yes' ) ) {
				wp_mail( $email, $subject, $message, self::ur_get_header() );
			}
		} else {

			$subject = get_option( 'user_registration_registration_approved_email_subject', __( 'Congratulations! Registration approved on {{blog_info}}', 'user-registration' ) );
			$message = new UR_Settings_Registration_Approved_Email();
			$message = $message->ur_get_registration_approved_email();
			$message = get_option( 'user_registration_registration_approved_email', $message );
			$message = self::parse_smart_tags( $message, $values, $name_value );
			$subject = self::parse_smart_tags( $subject, $values, $name_value );

			if ( 'yes' === get_option( 'user_registration_enable_registration_approved_email', 'yes' ) ) {
				wp_mail( $email, $subject, $message, self::ur_get_header() );
			}
		}
	}

	/**
	 * Lost Password Email Trigger
	 *
	 * @param  string $user_login username.
	 * @param  obj    $user_data user object.
	 * @param  string $key password reset key.
	 * @return bool
	 */
	public static function lost_password_email( $user_login, $user_data, $key ) {

		$user     = get_user_by( 'login', $user_login );
		$email    = isset( $user->data->user_email ) ? $user->data->user_email : '';
		$username = isset( $user->data->user_login ) ? $user->data->user_login : '';

		if ( empty( $email ) || empty( $username ) ) {
			return false;
		}

		$subject = get_option( 'user_registration_reset_password_email_subject', __( 'Password Reset Email: {{blog_info}}', 'user-registration' ) );
		$message = new UR_Settings_Reset_Password_Email();
		$message = $message->ur_get_reset_password_email();
		$message = get_option( 'user_registration_reset_password_email', $message );

		$values = array(
			'username' => $username,
			'email'    => $email,
			'key'      => $key,
		);

		$message = self::parse_smart_tags( $message, $values );
		$subject = self::parse_smart_tags( $subject, $values );

		if ( 'yes' === get_option( 'user_registration_enable_reset_password_email', 'yes' ) ) {
			wp_mail( $email, $subject, $message, self::ur_get_header() );
			return true;
		}

		return false;
	}

	/**
	 * Trigger the admin email after profile details changed by user.
	 *
	 * @param  string $user_email Email of the user.
	 * @param  string $username   Username of the user.
	 * @param  int    $user_id       User id.
	 * @param  string $data_html  String replaced with {{all_fields}} smart tag.
	 * @param  array  $name_value Array to replace with extra fields smart tag.
	 * @param  array  $attachments Email Attachement.
	 * @return void
	 * @since 1.6.3
	 */
	public static function send_profile_changed_email_to_admin( $user_email, $username, $user_id, $data_html, $name_value, $attachments ) {

		$header  = "Reply-To: {{email}} \r\n";
		$header .= 'Content-Type: text/html; charset=UTF-8';

		$attachment  = isset( $attachments['admin'] ) ? $attachments['admin'] : '';
		$admin_email = get_option( 'user_registration_edit_profile_email_receipents', get_option( 'admin_email' ) );
		$admin_email = explode( ',', $admin_email );
		$admin_email = array_map( 'trim', $admin_email );

		$subject = get_option( 'user_registration_profile_details_changed_email_subject', __( 'Profile Details Changed Email: {{blog_info}}', 'user-registration' ) );
		$message = new UR_Settings_Profile_Details_Changed_Email();
		$message = $message->ur_get_profile_details_changed_email();
		$message = get_option( 'user_registration_profile_details_changed_email', $message );

		$values  = array(
			'username'   => $username,
			'email'      => $user_email,
			'all_fields' => $data_html,
		);
		$message = self::parse_smart_tags( $message, $values, $name_value );
		$subject = self::parse_smart_tags( $subject, $values, $name_value );
		$header  = self::parse_smart_tags( $header, $values, $name_value );

		if ( 'yes' === get_option( 'user_registration_enable_profile_details_changed_email', 'yes' ) ) {
			foreach ( $admin_email as $email ) {
				wp_mail( $email, $subject, $message, $header, $attachment );
			}
		}
	}

	/**
	 * Process smart tags for status change emails.
	 *
	 * @param  string $email User Email.
	 * @since  1.5.0
	 * @return array smart tag key value pair.
	 */
	public static function user_data_smart_tags( $email ) {
		$name_value = array();
		$user       = get_user_by( 'email', $email );
		$user_id    = isset( $user->ID ) ? absint( $user->ID ) : 0;

		$user_fields = ur_get_user_table_fields();

		foreach ( $user_fields as $field ) {
			$name_value[ $field ] = isset( $user->data->$field ) ? $user->data->$field : '';
		}

		$user_meta_fields = ur_get_registered_user_meta_fields();

		// Use name_value for smart tag to replace.
		foreach ( $user_meta_fields as $field ) {
			$name_value[ $field ] = get_user_meta( $user_id, $field, true );
		}

		$user_extra_fields = ur_get_user_extra_fields( $user_id );
		$name_value        = array_merge( $name_value, $user_extra_fields );

		return apply_filters( 'user_registration_process_smart_tag_for_status_change_emails', $name_value, $email );
	}

	/**
	 * Parse Smart tags for emails.
	 *
	 * @param string $content Contents.
	 * @param array  $values Data values.
	 * @param array  $name_value  Extra values.
	 */
	private static function parse_smart_tags( $content = '', $values = array(), $name_value = array() ) {
		$smart_tags = array(
			'{{username}}',
			'{{email}}',
			'{{email_token}}',
			'{{blog_info}}',
			'{{home_url}}',
			'{{ur_login}}',
			'{{key}}',
			'{{all_fields}}',
			'{{auto_pass}}',
		);

		$ur_login = ( ur_get_page_permalink( 'myaccount' ) !== get_home_url() ) ? ur_get_page_permalink( 'myaccount' ) : wp_login_url();
		$ur_login = str_replace( get_home_url() . '/', '', $ur_login );

		$default_values = array(
			'username'    => '',
			'email'       => '',
			'email_token' => '',
			'blog_info'   => get_bloginfo(),
			'home_url'    => get_home_url(),
			'ur_login'    => $ur_login,
			'key'         => '',
			'all_fields'  => '',
			'auto_pass'   => '',
		);

		if ( has_filter( 'user_registration_extras_auto_generated_password' ) ) {
			$user_pass                   = apply_filters( 'user_registration_extras_auto_generated_password', 'user_pass' );
			$default_values['auto_pass'] = $user_pass;
		}
		$values = wp_parse_args( $values, $default_values );

		if ( ! empty( $values['email'] ) ) {
			$user_data = self::user_data_smart_tags( $values['email'] );
			if ( is_array( $name_value ) && ! empty( $name_value ) ) {
				$user_data = array_merge( $user_data, $name_value );
			}
			$values = array_merge( $values, $user_data );
			array_walk(
				$values,
				function( &$value, $key ) {
					if ( 'user_pass' === $key ) {
						$value = esc_html__( 'Chosen Password', 'user-registration' );
					}
				}
			);

			$user_smart_tags = array_keys( $user_data );
			array_walk(
				$user_smart_tags,
				function( &$value ) {
					$value = '{{' . trim( $value, '{}' ) . '}}';
				}
			);
			$smart_tags = array_merge( $smart_tags, $user_smart_tags );
		}
		$smart_tags = apply_filters( 'user_registration_smart_tags', $smart_tags );
		$content    = str_replace( $smart_tags, array_values( $values ), $content );

		return $content;
	}
}

UR_Emailer::init();
