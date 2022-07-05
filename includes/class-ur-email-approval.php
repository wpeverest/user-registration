<?php
/**
 * User Registration Email Approval.
 *
 * @class    UR_Email_Approval
 * @since    1.1.5
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UR_Email_Approval
 */
class UR_Email_Approval {

	/**
	 * UR_Email_Approval Constructor.
	 */
	public function __construct() {

    add_action( 'user_registration_after_register_user_action', array( $this, 'set_approval_status' ), 5, 3 );
    add_action( 'admin_init', array( __CLASS__, 'approve_user_after_verification' ) );

	}

	/**
	 * Verify the token and approve the user if the token matches
	 */
    public static function approve_user_after_verification() {
		if ( ! isset( $_GET['ur_approval_token'] ) || empty( $_GET['ur_approval_token'] ) ) {
			return;
		} else {
			if ( current_user_can( 'edit_users' ) ) {

				$ur_approval_token_raw = sanitize_text_field( wp_unslash( $_GET['ur_approval_token'] ) );
				$ur_approval_token     = str_split( $ur_approval_token_raw , 50 );
				$token_string = $ur_approval_token[1];

				if ( 2 < count( $ur_approval_token ) ) {
					unset( $ur_approval_token[0] );
					$token_string = join( '', $ur_approval_token );
				}
				$output     = self::crypt_the_string( $token_string, 'd' );
				$output     = explode( '_', $output );
				$user_id    = absint( $output[0] );
				$form_id 	= ur_get_form_id_by_userid( $user_id );

				$email_approval_enabled = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_email_approval', get_option( 'user_registration_login_option_enable_email_approval', false ) );

				if ( $email_approval_enabled ) {
					$saved_token = get_user_meta( $user_id, 'ur_confirm_approval_token', true );

					if ( $ur_approval_token_raw === $saved_token ) {
						$user_manager = new UR_Admin_User_Manager( $user_id );
						$user_manager->save_status( UR_Admin_User_Manager::APPROVED, true );

						delete_user_meta( $user_id, 'ur_confirm_approval_token' );

						add_action( 'admin_notices', array( __CLASS__, 'approved_success' ) );

						$redirect_url = admin_url() . 'users.php';
						wp_redirect( $redirect_url );
						exit;

					} else {
						add_action( 'admin_notices', array( __CLASS__, 'invalid_approval_token_message' ) );
					}
				} else {
					add_action( 'admin_notices', array( __CLASS__, 'email_approval_disabled_message' ) );
				}
			} else {
				return;
			}
		}
	}

	/**
	 * Message to show when user approved successfully
	 */
	public static function approved_success() {
		echo '<div class="notice notice-success"><p>' . esc_html__( 'User approved successfully.', 'user-registration' );
	}

	/**
	 * Message to show when passed token doesn't match with stored token
	 */
	public static function invalid_approval_token_message() {
		echo "<div class='notice notice-error'><p>" . esc_html__( 'The token is invalid. Please try again.', 'user-registration' ) . "</p></div>";
	}

	/**
	 * Email Approval Disabled Message
	 */
	public static function email_approval_disabled_message() {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Failed to approve user. Email Approval Option is Disabled.', 'user-registration' ) . '</p></div>';
	}


	/**
	 * Encrypt/Decrypt the provided string.
	 * Encrypt while setting token and updating to database, decrypt while comparing the stored token.
	 *
	 * @param  string $string String to encrypt/decrypt.
	 * @param  string $action Encrypt/decrypt action. 'e' for encrypt and 'd' for decrypt.
	 * @return string Encrypted/Decrypted string.
	 */
	public static function crypt_the_string( $string, $action = 'e' ) {

		$secret_key = 'ur_secret_key';
		$secret_iv  = 'ur_secret_iv';

		$output         = false;
		$encrypt_method = 'AES-256-CBC';
		$key            = hash( 'sha256', $secret_key );
		$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( 'e' == $action ) {
			$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
		} elseif ( 'd' == $action ) {
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
		$code_alphabet  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code_alphabet .= 'abcdefghijklmnopqrstuvwxyz';
		$code_alphabet .= '0123456789';
		$max           = strlen( $code_alphabet );

		for ( $i = 0; $i < $length; $i++ ) {
			$token .= $code_alphabet[ random_int( 0, $max - 1 ) ];
		}

		$token .= $this->crypt_the_string( $user_id . '_' . time(), 'e' );

		return $token;

		do_action( 'user_registration_get_token' );
	}

    /**
	 * Set the approval token of the user and update it to usermeta table in database.
	 *
	 * @param array $valid_form_data Form filled data.
	 * @param int   $form_id         Form ID.
	 * @param int   $user_id         User ID.
	 */
	public function set_approval_status( $valid_form_data, $form_id, $user_id ) {
		$form_id = isset( $form_id ) ? $form_id : get_user_meta( $this->user->ID, 'ur_form_id', true );
		$login_option = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );

		$email_approval_enabled = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_email_approval', get_option( 'user_registration_login_option_enable_email_approval', false ) );

		if ( ( 'admin_approval' == $login_option ) && ( $email_approval_enabled ) ) {
			$token = $this->get_token( $user_id );
			update_user_meta( $user_id, 'ur_confirm_approval_token', $token );
		} else {
			return;
		}
	}
}

new UR_Email_Approval();
