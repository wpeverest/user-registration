<?php
/**
 * User Registration Email Confirmation.
 *
 * @class    UR_Email_Confirmation
 * @since    1.1.5
 * @package  UserRegistration/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UR_Email_Confirmation
 */
class UR_Email_Confirmation {

	public function __construct() {

		if ( is_admin() ) {
			add_filter( 'manage_users_columns', array( $this, 'add_column_head' ) );
			// add_filter( 'user_row_actions', array( $this, 'create_quick_links' ), 10, 2 );
			add_action( 'load-users.php', array( $this, 'trigger_query_actions' ) );
		}

		add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ), 10, 2 );
		add_action( 'user_registration_after_register_user_action', array( $this, 'set_email_status' ), 9, 3 );
		add_action( 'template_redirect', array( $this, 'check_token_before_authenticate' ), 30, 2 );
		add_action( 'wp_authenticate', array( $this, 'check_token_before_authenticate' ), 40, 2 );
	}

	/**
	 * Trigger the action query and check if some users have been verified or unverified
	 */
	public function trigger_query_actions() {

		$resend_verification_sent = isset( $_REQUEST['resend_verification_sent'] ) ? sanitize_key( $_REQUEST['resend_verification_sent'] ) : false;
		if($resend_verification_sent){
			add_action( 'admin_notices', array( $this, 'ur_admin_notice_resend_verification_sent' ) );
		}

		$user_id = absint( isset( $_GET['user'] ) ? $_GET['user'] : 0 );

			$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : false;
			$mode   = isset( $_POST['mode'] ) ? $_POST['mode'] : false;

			// If this is a multisite, bulk request, stop now!
		if ( 'list' == $mode ) {
			return;
		}

		if ( ! empty( $action ) && in_array( $action, array( 'resend_verification' ) ) && ! isset( $_GET['new_role'] ) ) {

			check_admin_referer( 'ur_user_change_email_status' );

			$redirect = admin_url( 'users.php' );
			$status   = $action;

			if ( $status == 'resend_verification' ) {
				$user    = get_user_by( 'id', $user_id );
				$form_id = ur_get_form_id_by_userid( $user_id );

				$this->set_email_status( array(), $form_id, $user_id );

				$attachments = apply_filters( 'user_registration_email_attachment_resending_token', array() );
				$name_value  = ur_get_user_extra_fields( $user_id );
					// Get selected email template id for specific form.
				$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template');

				UR_Emailer::send_mail_to_user( $user->user_email, $user->user_login, $user_id, '', $name_value, $attachments, $template_id );
				$redirect = add_query_arg( array( 'resend_verification_sent' => 1 ), $redirect );

			}

			wp_redirect( $redirect );
			exit;
		}

	}

	/**
	 * Admin notice after resend verification email sent.
	 * @since 1.9.4
	 */
	public function ur_admin_notice_resend_verification_sent() {
		echo '<div class="notice-success notice is-dismissible"><p>' . esc_html__( 'Verification Email Sent Successfully !! ', 'user-registration' ) . '</p></div>';
	}

	/**
	 * Add the column header for the email status column
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_column_head( $columns ) {
		if ( ! current_user_can( 'edit_user' ) ) {
			return $columns;
		}

		$the_columns['ur_user_user_status'] = __( 'Status', 'user-registration' );

		$newcol  = array_slice( $columns, 0, -1 );
		$newcol  = array_merge( $newcol, $the_columns );
		$columns = array_merge( $newcol, array_slice( $columns, 1 ) );

		return $columns;
	}

	/**
	 * Enqueque CSS to load notice
	 *
	 * @return void
	 */
	public function ur_enqueue_script() {
		wp_register_style( 'user-registration-css', UR()->plugin_url() . '/assets/css/user-registration.css', array(), UR_VERSION );
		wp_enqueue_style( 'user-registration-css' );
	}

	// Successful registration message.
	public function custom_registration_message() {
		$default = __( 'User successfully registered. Login to continue.', 'user-registration' );
		$message = get_option( 'user_registration_successful_email_verified_message', $default );
		return ur_print_notice( $message );
	}

	/**
	 * Email Successfully verified and waiting for admin approval Message.
	 */
	public function custom_email_confirmed_admin_await_message() {
		$default = __('Email has successfully been verified. Now, please wait until the admin approves you to give access for the login.', 'user-registration' );
		$message = get_option( 'user_registration_pro_email_verified_admin_approval_await_message', $default );
		return ur_print_notice( $message );
	}

	// Token mismatch message.
	public function custom_registration_error_message() {
		return ur_print_notice( __( 'Token Mismatch!', 'user-registration' ), 'error' );
	}

	// Token expired message.
	public function custom_token_expired_message() {
		return ur_print_notice( __( 'Token Expired . Please request for new verification email.', 'user-registration' ), 'error' );
	}

	// Resend verification email message.
	public function custom_resend_email_token_message() {
		return ur_print_notice( __( 'Verification Email Sent!', 'user-registration' ) );
	}

	// Resend verification email error message.
	public function custom_resend_email_token_error_message() {
		return ur_print_notice( __( 'User does not exist!', 'user-registration' ), 'error' );
	}

	/**
	 * Compare user token with token in url
	 *
	 * @return void
	 */
	public function check_token_before_authenticate() {

		$user_reg_successful = false;

		add_action( 'login_enqueue_scripts', array( $this, 'ur_enqueue_script' ), 1 );

		// Condition for resending token.
		if ( isset( $_GET['ur_resend_id'] ) && $_GET['ur_resend_token'] === 'true' ) {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'ur_resend_token' ) ) {
				die( __( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
			}

			$output  = $this->crypt_the_string( $_GET['ur_resend_id'], 'd' );
			$output  = explode( '_', $output );
			$user_id = absint( $output[0] );

			$user    = get_user_by( 'id', $user_id );

			$form_id = ur_get_form_id_by_userid( $user_id );

			$login_option = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );

			if ( $user && ( 'email_confirmation' === $login_option || 'admin_approval_after_email_confirmation' === $login_option ) ) {
				$this->set_email_status( array(), '', $user_id );

				$attachments = apply_filters( 'user_registration_email_attachment_resending_token', array() );
				$name_value  = ur_get_user_extra_fields( $user_id );
					// Get selected email template id for specific form.
				$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template');

				UR_Emailer::send_mail_to_user( $user->user_email, $user->user_login, $user_id, '', $name_value, $attachments, $template_id );

				add_filter( 'login_message', array( $this, 'custom_resend_email_token_message' ) );
				add_filter( 'user_registration_login_form_before_notice', array( $this, 'custom_resend_email_token_message' ) );
			} else {

				add_filter( 'login_message', array( $this, 'custom_resend_email_token_error_message' ) );
				add_filter( 'user_registration_login_form_before_notice', array( $this, 'custom_resend_email_token_error_message' ) );
			}
		}

		if ( ! isset( $_GET['ur_token'] ) ) {
			return;
		} else {

			$ur_token     = str_split( $_GET['ur_token'], 50 );
			$token_string = $ur_token[1];

			if ( 2 < count( $ur_token ) ) {
				unset( $ur_token[0] );
				$token_string = join( '', $ur_token );
			}
			$output     = $this->crypt_the_string( $token_string, 'd' );
			$output     = explode( '_', $output );
			$user_id    = absint( $output[0] );
			$user_token = get_user_meta( $user_id, 'ur_confirm_email_token', true );

			$form_id = ur_get_form_id_by_userid( $user_id );

			// Check if the token matches the token value stored in db.
			$login_option = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );

			if ( $user_token === $_GET['ur_token'] && ( 'email_confirmation' === $login_option || 'admin_approval_after_email_confirmation' === $login_option ) ) {
				if ( isset( $output[1]) && time() > ( $output[1] + 60 * 60 * 24 ) ) {
					add_filter( 'login_message', array( $this, 'custom_token_expired_message' ) );
					add_filter( 'user_registration_login_form_before_notice', array( $this, 'custom_token_expired_message' ) );
				} else {
					$user_reg_successful = true;

					update_user_meta( $user_id, 'ur_confirm_email', 1 );
					delete_user_meta( $user_id, 'ur_confirm_email_token' );

					if ( 'admin_approval_after_email_confirmation' === $login_option ) {
						add_filter( 'login_message', array( $this, 'custom_email_confirmed_admin_await_message' ) );
						add_filter( 'user_registration_login_form_before_notice', array( $this, 'custom_email_confirmed_admin_await_message' ) );
					} else {
						add_filter( 'login_message', array( $this, 'custom_registration_message' ) );
						add_filter( 'user_registration_login_form_before_notice', array( $this, 'custom_registration_message' ) );
					}
				}
			} else {
				add_filter( 'login_message', array( $this, 'custom_registration_error_message' ) );
				add_filter( 'user_registration_login_form_before_notice', array( $this, 'custom_registration_error_message' ) );
			}

			do_action( 'user_registration_check_token_complete', $user_id, $user_reg_successful );
		}

	}

	/**
	 * Encrypt/Decrypt the provided string.
	 * Encrypt while setting token and updating to database, decrypt while comparing the stored token.
	 *
	 * @param  string $string String to encrypt/decrypt
	 * @param  string $action Encrypt/decrypt action. 'e' for encrypt and 'd' for decrypt
	 * @return string Encrypted/Decrypted string.
	 */
	public function crypt_the_string( $string, $action = 'e' ) {

		$secret_key = 'ur_secret_key';
		$secret_iv  = 'ur_secret_iv';

		$output         = false;
		$encrypt_method = 'AES-256-CBC';
		$key            = hash( 'sha256', $secret_key );
		$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( $action == 'e' ) {
			$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
		} elseif ( $action == 'd' ) {
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}

		return $output;
	}

	/**
	 * Generate email token for the user.
	 *
	 * @param  int $user_id User ID.
	 * @return string   Token.
	 */
	public function get_token( $user_id ) {

		$length        = 50;
		$token         = '';
		$codeAlphabet  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$codeAlphabet .= 'abcdefghijklmnopqrstuvwxyz';
		$codeAlphabet .= '0123456789';
		$max           = strlen( $codeAlphabet );

		for ( $i = 0; $i < $length; $i++ ) {
			$token .= $codeAlphabet[ random_int( 0, $max - 1 ) ];
		}

		$token .= $this->crypt_the_string( $user_id . '_' . time(), 'e' );

		return $token;

		do_action( 'user_registration_get_token' );
	}

	/**
	 * Set the token of the user and update it to usermeta table in database.
	 *
	 * @param array $valid_form_data Form filled data.
	 * @param int   $form_id         Form ID.
	 * @param int   $user_id         User ID.
	 */
	public function set_email_status( $valid_form_data, $form_id, $user_id ) {
		$form_id = ( $form_id ) ? $form_id : 0;
		$login_option = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );

		if ( 'email_confirmation' === $login_option || 'admin_approval_after_email_confirmation' === $login_option  ) {
			$token = $this->get_token( $user_id );
			update_user_meta( $user_id, 'ur_confirm_email', 0 );
			update_user_meta( $user_id, 'ur_confirm_email_token', $token );

			if('admin_approval_after_email_confirmation' === $login_option ) {
				update_user_meta( $user_id, 'ur_admin_approval_after_email_confirmation', 'false' );
			}
			//update user status when login using social connect
			if ( get_user_meta( $user_id, 'user_registration_social_connect_bypass_current_password', false ) ) {
				update_user_meta( $user_id, 'ur_confirm_email', 1 );

				if('admin_approval_after_email_confirmation' === $login_option ) {
					update_user_meta( $user_id, 'ur_admin_approval_after_email_confirmation', 'true' );
				}
			}
		}
	}

	/**
	 * Check the email status during authentication
	 *
	 * @param  WP_User $user User instance
	 * @return mixed
	 */
	public function check_email_status( WP_User $user, $password ) {
		$form_id = ur_get_form_id_by_userid( $user->ID );

		$general_login_option = get_option( 'user_registration_general_setting_login_options', 'default' );

		if ( 'email_confirmation' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', $general_login_option ) ) {
			$email_status = get_user_meta( $user->ID, 'ur_confirm_email', true );

			do_action( 'ur_user_before_check_email_status_on_login', $email_status, $user );

			$url = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			$url = substr( $url, 0, strpos( $url, '?' ) );
			$url = wp_nonce_url( $url . '?ur_resend_id=' . $this->crypt_the_string( $user->ID . '_' . time(), 'e' ) . '&ur_resend_token=true', 'ur_resend_token' );

			if ( $email_status === '0' ) {
				$message = '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong> ' . sprintf( __( 'Your account is still pending approval. Verify your email by clicking on the link sent to your email. %s', 'user-registration' ), '<a id="resend-email" href="' . esc_url( $url ) . '">' . __( 'Resend Verification Link', 'user-registration' ) . '</a>' );
				return new WP_Error( 'user_email_not_verified', $message );
			}
			return $user;
		}
		return $user;
	}

	/**
	 * If the user is not approved, disalow to reset the password fom Lost Passwod form and display an error message
	 *
	 * @param $result
	 * @param $user_id
	 *
	 * @return \WP_Error
	 */
	public function allow_password_reset( $result, $user_id ) {
		$form_id = ur_get_form_id_by_userid( $user_id );

		if ( 'email_confirmation' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {

			$email_status = get_user_meta( $user_id, 'ur_confirm_email', true );

			if ( $email_status === '0' ) {
				$error_message = __( 'Email not verified! Verify your email by clicking on the link sent to your email.', 'user-registration' );
				$result        = new WP_Error( 'user_email_not_verified', $error_message );
			}
		}
		return $result;
	}

	/**
	 * @deprecated 1.4.0
	 * @param  string $string the string to encrypt/decrypt
	 * @param  string $action the action encrypt or decrypt
	 * @return void
	 */
	public function my_simple_crypt( $string, $action ) {
		ur_deprecated_function( 'UR_Email_Confirmation::my_simple_crypt', '1.4.0', 'UR_Email_Confirmation::crypt_the_string' );
	}

	/**
	 * @deprecated 1.4.0
	 * @param $user_id User's ID.
	 * @return void
	 */
	public function getToken( $user_id ) {
		ur_deprecated_function( 'UR_Email_Confirmation::getToken', '1.4.0', 'UR_Email_Confirmation::get_token' );
	}
}

new UR_Email_Confirmation();
