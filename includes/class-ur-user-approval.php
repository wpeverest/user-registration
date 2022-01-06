<?php
/**
 * User Registration User Approval.
 *
 * @class    UR_User_Approval
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UR_User_Approval
 */
class UR_User_Approval {

	/**
	 * UR_User_Approval constructor.
	 */
	public function __construct() {

		// -------------------- ACTIONS & FILTERS --------------------
		// Additional checks
		add_action( 'after_setup_theme', array( $this, 'check_status_on_page' ) );

		// Handle user Sign in.
		add_action( 'user_registration_after_register_user_action', array( $this, 'set_user_status' ), 10, 3 );
		add_action( 'user_register', array( $this, 'send_request_notification_to_admin' ), 10, 1 );
		add_filter( 'wp_login_errors', array( $this, 'registration_completed_message' ) );

		// Handle user Sign on.
		add_action( 'wp_login', array( $this, 'track_first_login' ), 10, 2 );
		add_filter( 'wp_authenticate_user', array( $this, 'check_status_on_login' ), 10, 2 );

		// Handle Lost Password Page.
		add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ), 10, 2 );

		// When the approval status of an user change.
		add_action(
			'ur_user_status_updated',
			array(
				$this,
				'send_notification_to_user_about_status_changing',
			),
			10,
			3
		);
		add_action( 'ur_user_user_denied', array( $this, 'disconnect_user_session' ) );

		do_action( 'ur_user_construct' );

	}

	/**
	 * Display a message the provide instruction after the use regsitration and remove the login form from there
	 *
	 * @param array $errors Errors.
	 * @return mixed
	 */
	public function registration_completed_message( $errors ) {

		if ( ! ( isset( $_GET['checkemail'] ) && 'registered' === $_GET['checkemail'] ) ) {
			return $errors;
		}

		return '';
	}

	/**
	 * Save a flag that ensure if an user has ever loggedin while the plugin is activated
	 *
	 * @param  mixed $user_login Username.
	 * @param  mixed $user Users Object.
	 */
	public function track_first_login( $user_login, $user ) {

		$form_id = ur_get_form_id_by_userid( $user->ID );

		if ( 'admin_approval' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {
			$user_manager = new UR_Admin_User_Manager( $user );
			$user_manager->save_first_access_flag();
		}
	}

	/**
	 * Send the email to the user that alert if the approvalrequest has been approved or rejected.
	 * If the request is approved and the user needs to receive the password, a new password will be generated and sent
	 *
	 * @param mixed $status Status.
	 * @param int   $user_id User ID.
	 * @param mixed $alert_user Alert User.
	 */
	public function send_notification_to_user_about_status_changing( $status, $user_id, $alert_user ) {

		$form_id = ur_get_form_id_by_userid( $user_id );

		if ( ! $alert_user && 'admin_approval' !== ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {
			return;
		}

		$user_manager = new UR_Admin_User_Manager( $user_id );

		$user_status = $user_manager->get_user_status();

		// Avoid to send multiple times the same email.
		if ( $status === $user_status['user_status'] ) {
			return;
		}

		$user      = get_userdata( $user_id );
		$user_data = isset( $user->data ) ? $user->data : array();
		$username  = isset( $user_data->user_login ) ? $user_data->user_login : '';
		$email     = isset( $user_data->user_email ) ? $user_data->user_email : '';

		UR_Emailer::status_change_email( $email, $username, $status, $form_id );
	}

	/**
	 * Send an email to the admin in order to alert the a new user requests to be approved
	 *
	 * @param int $user_id User ID.
	 */
	public function send_request_notification_to_admin( $user_id ) {

		// If the user is created by admin or if the admin alert is disabled, doesn't send the email to the admin.
		if ( $this->is_admin_creation_process() ) {
			return;
		}

	}

	/**
	 * Set the status of the user right after the registration.
	 *
	 * @param mixed $form_data Form Data.
	 * @param int   $form_id Form ID.
	 * @param int   $user_id User ID.
	 */
	public function set_user_status( $form_data, $form_id, $user_id ) {

		if ( 'admin_approval' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {

			$status = UR_Admin_User_Manager::PENDING;
			// If the user is created by admin in the backend, than automatically approve him.
			if ( $this->is_admin_creation_process() ) {
				$status = UR_Admin_User_Manager::APPROVED;
			}
			// update user status when login using social connect
			if ( get_user_meta( $user_id, 'user_registration_social_connect_bypass_current_password', false ) ) {
				$status = UR_Admin_User_Manager::APPROVED;
			}

			$user_manager = new UR_Admin_User_Manager( $user_id );

			// The user have to be not alerted on status creation, it will be always pending or approved.
			$alert_user = false;

			$user_manager->save_status( $status, $alert_user );
		}
	}

	/**
	 * Check the status of an user on login.
	 *
	 * @param mixed  $user Users.
	 * @param string $password Password.
	 *
	 * @return \WP_Error
	 */
	public function check_status_on_login( $user, $password ) {

		if ( ! $user instanceof WP_User ) {
			return $user;
		}

		$form_id = ur_get_form_id_by_userid( $user->ID );

		$user_manager = new UR_Admin_User_Manager( $user );

		$status = $user_manager->get_user_status();

		if ( 'admin_approval' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) || 'admin_approval' === $status['login_option'] ) {

			do_action( 'ur_user_before_check_status_on_login', $status['user_status'], $user );

			switch ( $status['user_status'] ) {
				case UR_Admin_User_Manager::APPROVED:
					return $user;
					break;
				case UR_Admin_User_Manager::PENDING:
					$message = '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong> ' . __( 'Your account is still pending approval.', 'user-registration' );

					return new WP_Error( 'pending_approval', $message );
					break;
				case UR_Admin_User_Manager::DENIED:
					$message = '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong> ' . __( 'Your account has been denied.', 'user-registration' );

					return new WP_Error( 'denied_access', $message );
					break;
			}
		} elseif ( 'email_confirmation' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) || 'email_confirmation' === $status['login_option'] ) {

			do_action( 'ur_user_before_check_email_status_on_login', $status['user_status'], $user );

			$url      = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https://' . $_SERVER['SERVER_NAME'] : 'http://' . $_SERVER['SERVER_NAME'];

			if ( get_option( 'ur_login_ajax_submission' ) ) {
				$url .= $_SERVER['HTTP_REFERER'];
			} else {
				$url .= $_SERVER['REQUEST_URI'];
			}

			$url      = substr( $url, 0, strpos( $url, '?' ) );
			$instance = new UR_Email_Confirmation();
			$url      = wp_nonce_url( $url . '?ur_resend_id=' . $instance->crypt_the_string( $user->ID . '_' . time(), 'e' ) . '&ur_resend_token=true', 'ur_resend_token' );

			if ( '0' === $status['user_status'] ) {
				$message = '<strong>' . esc_html__( 'ERROR:', 'user-registration' ) . '</strong> ' . sprintf( __( 'Your account is still pending approval. Verify your email by clicking on the link sent to your email. %s', 'user-registration' ), '<a id="resend-email" href="' . esc_url( $url ) . '">' . __( 'Resend Verification Link', 'user-registration' ) . '</a>' );
				return new WP_Error( 'user_email_not_verified', $message );
			}
			return $user;
		} elseif ( 'payment' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {
			$payment_status = get_user_meta( $user->ID, 'ur_payment_status', true );

			do_action( 'ur_user_before_check_payment_status_on_login', $payment_status, $user );

			if ( ! empty( $payment_status ) && 'completed' !== $payment_status ) {

				$user_id      = $user->ID;
				$instance     = new User_Registration_Payments_Process();
				$redirect_url = $instance->generate_redirect_url( $user_id );
				$message      = '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong> ' . sprintf( __( 'Your account is still pending payment. Process the payment by clicking on this: %s', 'user-registration' ), '<a id="payment-link" href="' . esc_url( $redirect_url ) . '">' . __( 'link', 'user-registration' ) . '</a>' );

				return new WP_Error( 'user_payment_pending', $message );
			}

			return $user;
		}
		return $user;
	}

	/**
	 * Check on every page if the current user is actual approved, otherwise logout him
	 * This is an additional protection against that themes or plugins that login users automatically after sign up
	 */
	public function check_status_on_page() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$form_id = ur_get_form_id_by_userid( get_current_user_id() );

		if ( 'admin_approval' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {

			// Try to hide the not approved users from any theme or plugin request in frontend.
			$disable_pre_get = apply_filters( 'user_registration_disable_pre_get_users', 'no' );

			if ( 'no' === $disable_pre_get ) {
				add_action( 'pre_get_users', array( $this, 'hide_not_approved_users_in_frontend' ) );
			}

			$status = ur_get_user_approval_status( get_current_user_id() );

			$user_manager = new UR_Admin_User_Manager();

			if ( ! $user_manager->can_status_be_changed_by( get_current_user_id() ) ) {
				return;
			}

			do_action( 'ur_user_before_check_status_on_page', $status, $user_manager );

			if ( UR_Admin_User_Manager::APPROVED === $status ) {
				return;
			}

			wp_logout();
		}
	}

	/**
	 * Check the $_REQUEST variable to understand if the user currently created is created by admin in the backend or noth
	 *
	 * @return bool
	 */
	protected function is_admin_creation_process() {
		return ( isset( $_REQUEST['action'] ) && 'createuser' == $_REQUEST['action'] );
	}

	/**
	 * Disconnect an user selected by id.
	 *
	 * @param int $user_id User Id.
	 */
	public function disconnect_user_session( $user_id ) {
		$form_id = ur_get_form_id_by_userid( $user_id );

		if ( 'admin_approval' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {
			// get all sessions for user with ID $user_id.
			$sessions = WP_Session_Tokens::get_instance( $user_id );

			// we have got the sessions, destroy them all!
			$sessions->destroy_all();
		}
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

		// Check if the form is our form and the login option is admin approval.
		if ( 0 !== $form_id && 'admin_approval' === ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {
			$user_manager = new UR_Admin_User_Manager( $user_id );

			if ( ! $user_manager->is_approved() ) {
				$error_message = __( 'Your account is still awaiting admin approval. Reset Password is not allowed.', 'user-registration' );
				$result        = new WP_Error( 'user_not_approved', $error_message );
			}
		}

		return $result;
	}

	/**
	 * Function called on action pre_get_users, it remove all users not approved when the request is don by frontend,
	 * in this way it ensure a compatibility with all other plugin and themes, avoiding to show unapproved users
	 * (for instance in members page of buddypress or Extrafooter of Woffice)
	 *
	 * @param \WP_Query $query Query.
	 */
	public function hide_not_approved_users_in_frontend( $query ) {

		// If this is not a frontend page, then do nothing.
		if ( is_admin() ) {
			return;
		}

		if ( isset( $query->query_vars['ur_user_ignore_users_hiding'] ) && $query->query_vars['ur_user_ignore_users_hiding'] ) {
			return;
		}

		// Otherwise display only approved users.
		$meta_query = array(
			'relation' => 'OR',
			array(
				'key'     => 'ur_user_status',
				'compare' => 'NOT EXISTS', // works!
				'value'   => '', // This is ignored, but is necessary...
			),
			array(
				'key'   => 'ur_user_status',
				'value' => UR_Admin_User_Manager::APPROVED,
			),
		);

		$meta_query = apply_filters( 'ur_user_hide_not_approved_users_in_frontend', $meta_query, $query );

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}
}

new UR_User_Approval();
