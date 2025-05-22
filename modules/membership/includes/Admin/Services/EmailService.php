<?php

/**
 * EmailService.php
 *
 * EmailService.php
 *
 * @class    EmailService.php
 * @package  Coupons
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Services;

use UR_Settings_Admin_Email;
use WPEverest\URMembership\Emails\User\UR_Settings_Membership_Cancellation_User_Email;
use WPEverest\URMembership\Emails\Admin\UR_Settings_Membership_Cancellation_Admin_Email;
use WPEverest\URMembership\Emails\User\UR_Settings_Membership_Renewal_Reminder_User_Email;

class EmailService
{
	protected $email_type, $logger;

	public function __construct() {
		$this->logger = ur_get_logger();
	}

	/**
	 * Send email
	 *
	 * @param $data
	 * @param $type
	 *
	 * @return bool|mixed|void
	 */
	public function send_email( $data, $type ) {
		if ( ! isset( $data['member_id'] ) ) {
			$this->logger->notice( 'Send Email:Registration: Member Id not Present.', array( 'source' => 'ur-membership-email-logs' ) );

			return false;
		}

		switch ( $type ) {
			case 'user_register_user':  // public registration email to member.
				return $this->send_user_register_email( $data );
			case 'user_register_admin': // public registration email to admin.
				return $this->send_user_register_admin_email( $data );
			case 'user_register_backend_user':  // public registration email to member.
				return $this->send_user_register_admin_email( $data );
			case 'payment_successful': // payment successful message to member.
				return self::send_payment_successful_email( $data );
			case 'payment_approval': // payment approval message to member.
				return self::send_payment_approval_email( $data );
			case 'membership_cancellation_email_user': // membership cancellation email to member.
				return self::send_membership_cancellation_email_user( $data );
			case 'membership_cancellation_email_admin': // membership cancellation email to admin.
				return self::send_membership_cancellation_email_admin( $data );
			case 'membership_renewal': // membership renewal
				return self::send_membership_renewal_email($data);

			default:
				break;
		}
	}

	/**
	 * Send user register email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_user_backend_register_email( $data ) {

		$membership_title = get_the_title( $data['membership'] );
		$subject          = __( 'Congratulations! Registration completed', 'user-registration' );
		$message          = sprintf( __( 'Hi, %s', 'user-registration' ), $data['username'] ) . "\n\n";
		$message          .= sprintf( __( 'Account created successfully under membership <b><i>%s</i></b>.', 'user-registration' ), $membership_title ) . "\n\n";
		$extra_message    = sprintf( __( '<b>Username: </b><i>%1$s</i> <b>Password: </b><i>%2$s</i> ', 'user-registration' ), $data['username'], $data['password'] ) . "\n\n";
		$final_greeting   = __( 'Thank You.', 'user-registration' );

		$template_file = locate_template( 'membership-registration-email.php' );
		if ( ! $template_file ) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/membership-registration-email.php';
		}
		ob_start();
		require $template_file;

		$message = ob_get_clean();
		$message = apply_filters( 'ur_membership_registration_email_custom_template', $message, $subject );
		$headers = \UR_Emailer::ur_get_header();

		return wp_mail( $data['email'], $subject, $message, $headers );
	}

	/**
	 * Send user register email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_user_register_email( $data ) {
		$user_id  = absint( $data['member_id'] );
		$subject  = get_option( 'user_registration_successfully_registered_email_subject', __( 'Registration Successful – Welcome to {{blog_info}}!', 'user-registration' ) );
		$settings = new \UR_Settings_Successfully_Registered_Email();
		$message  = $settings->ur_get_successfully_registered_email();
		$message  = get_option( 'user_registration_successfully_registered_email', $message );

		$form_id          = ur_get_form_id_by_userid( $user_id );
		$current_language = get_user_meta( $user_id, 'ur_registered_language' );

		list( $message, $subject ) = user_registration_email_content_overrider( $form_id, $settings, $message, $subject );
		$message              = ur_get_translated_string( 'admin_texts_user_registration_successfully_registered_email', $message, $current_language, 'user_registration_successfully_registered_email' );
		$subject              = ur_get_translated_string( 'admin_texts_user_registration_successfully_registered_email_subject', $subject, $current_language, 'user_registration_successfully_registered_email_subject' );
		$subscription_service = new SubscriptionService();

		$values      = array(
			'membership_tags' => $subscription_service->get_membership_plan_details( $data )
		);
		$message     = \UR_Emailer::parse_smart_tags( $message, $values );
		$subject     = \UR_Emailer::parse_smart_tags( $subject, $values );
		$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );

		$headers = \UR_Emailer::ur_get_header();

		return \UR_Emailer::user_registration_process_and_send_email( sanitize_email( $data['email'] ), $subject, $message, $headers, array(), $template_id );
	}

	/**
	 * Send user register email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_user_register_admin_email( $data ) {
		if ( ! ur_option_checked( 'user_registration_enable_admin_email', true ) ) {
			return;
		}
		$user_id = absint( $data['member_id'] );
		$subject = get_option( 'user_registration_admin_email_subject', __( 'A New Member Registered', 'user-registration' ) );
		$form_id = ur_get_form_id_by_userid( $user_id );

		$settings         = new UR_Settings_Admin_Email();
		$message          = $settings->ur_get_admin_email();
		$message          = get_option( 'user_registration_admin_email', $message );
		$current_language = get_user_meta( $user_id, 'ur_registered_language' );

		list( $message, $subject ) = user_registration_email_content_overrider( $form_id, $settings, $message, $subject );
		$message              = ur_get_translated_string( 'admin_texts_user_registration_successfully_registered_email', $message, $current_language, 'user_registration_successfully_registered_email' );
		$subject              = ur_get_translated_string( 'admin_texts_user_registration_successfully_registered_email_subject', $subject, $current_language, 'user_registration_successfully_registered_email_subject' );
		$subscription_service = new SubscriptionService();
		$values               = array(
			'membership_tags' => $subscription_service->get_membership_plan_details( $data )
		);
		$values               = $data + $values;

		$message     = \UR_Emailer::parse_smart_tags( $message, $values );
		$subject     = \UR_Emailer::parse_smart_tags( $subject, $values );
		$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );

		$headers     = \UR_Emailer::ur_get_header();
		$admin_email = get_option( 'user_registration_admin_email_receipents', get_option( 'admin_email' ) );
		$admin_email = explode( ',', $admin_email );
		$admin_email = array_map( 'trim', $admin_email );

		foreach ( $admin_email as $email ) {
			\UR_Emailer::user_registration_process_and_send_email( $email, $subject, $message, $headers, array(), $template_id );
		}

	}

	/**
	 * Send payment successful email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_payment_successful_email( $data ) {
		$user_id              = absint( $data['member_id'] );
		$user                 = get_user_by( 'ID', $user_id );
		$username             = $user->data->user_login;
		$email                = $user->data->user_email;
		$form_id              = ur_get_form_id_by_userid( $user_id );
		$data['username']     = $user->user_login;
		$data['user_email']   = $user->user_email;
		$subscription_service = new SubscriptionService();
		$values               = array(
			'membership_tags' => $subscription_service->get_membership_plan_details( $data )
		);
		$values               = $data + $values;


		$subject = get_option( 'user_registration_payment_success_email_subject', __( 'Congratulations! Payment Complete on {{blog_info}}', 'user-registration' ) );

		$settings = new \UR_Settings_Payment_Success_Email();
		$message  = $settings->ur_get_payment_success_email();
		$message  = get_option( 'user_registration_payment_success_email', $message );
		list( $message, $subject ) = user_registration_email_content_overrider( $form_id, $settings, $message, $subject );

		// Get selected email template id for specific form.
		$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );

		$message = \UR_Emailer::parse_smart_tags( $message, $values );

		$subject = \UR_Emailer::parse_smart_tags( $subject, $values );
		$headers = \UR_Emailer::ur_get_header();

		if ( ur_string_to_bool( get_option( 'user_registration_enable_payment_success_email', true ) ) ) {
			\UR_Emailer::user_registration_process_and_send_email( $email, $subject, $message, $headers, array(), $template_id );
		}
	}
//	/**
//	 * Send payment successful email
//	 *
//	 * @param $data
//	 *
//	 * @return bool|mixed|void
//	 */
//	public function send_payment_successful_email( $data ) {
//		if ( ! $this->validate_email_fields( $data ) ) {
//			return false;
//		}
//		$subject          = __( 'Payment Received!', 'user-registration' );
//		$user             = get_userdata( $data['member_id'] );
//		$order            = $data['order'];
//		$subscription     = $data['subscription'];
//		$membership_metas = $data['membership_metas'];
//		$extra_message    = $data['extra_message'] ?? '';
//		$currency         = get_option( 'user_registration_payment_currency', 'USD' );
//		$currencies       = ur_payment_integration_get_currencies();
//		$symbol           = $currencies[ $currency ]['symbol'];
//
//		$total = $order['total_amount'];
//
//		if ( isset( $order['coupon'] ) && ! empty( $order['coupon'] ) && "bank" !== $order['payment_method'] && isset( $membership_metas ) && ( "paid" === $membership_metas['type'] || ( "subscription" === $membership_metas['type'] && "off" === $order['trial_status'] ) ) ) {
//			$discount_amount = ( $order['coupon_discount_type'] === 'fixed' ) ? $order['coupon_discount'] : $order['total_amount'] * $order['coupon_discount'] / 100;
//			$total           = $order['total_amount'] - $discount_amount;
//		}
//		$billing_cycle = ( "subscription" === $membership_metas['type'] ) ? ( 'day' === $membership_metas['subscription']['duration'] ) ? esc_html( 'Daily', 'user-registration' ) : ( esc_html( ucfirst( $membership_metas['subscription']['duration'] . 'ly' ) ) ) : 'N/A';
//
//		$invoice_details = array(
//			'membership_name'   => esc_html( $membership_metas['post_title'] ),
//			'trial_status'      => esc_html( $order['trial_status'] ),
//			'trial_start_date'  => esc_html( $subscription['trial_start_date'] ),
//			'trial_end_date'    => esc_html( $subscription['trial_end_date'] ),
//			'next_billing_date' => esc_html( $subscription['next_billing_date'] ),
//			'payment_date'      => esc_html( $order['created_at'] ),
//			'billing_cycle'     => esc_html( $billing_cycle ),
//			'amount'            => $symbol . number_format( $membership_metas['amount'], 2 ),
//			'trial_amount'      => $symbol . number_format( ( 'on' === $order['trial_status'] ) ? $order['total_amount'] : 0, 2 ),
//			'coupon_discount'   => isset( $order['coupon_discount'] ) ? ( ( isset( $order['coupon_discount_type'] ) && $order['coupon_discount_type'] == 'percent' ) ? $order['coupon_discount'] . '%' : $symbol . $order['coupon_discount'] ) : '',
//			'coupon'            => esc_html( $order['coupon'] ?? '' ),
//			'total'             => $symbol . number_format( $total, 2 ),
//		);
//
//		$template_file = locate_template( 'payment-successful-email.php' );
//
//		if ( ! $template_file ) {
//			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/payment-successful-email.php';
//		}
//		ob_start();
//		require $template_file;
//
//		$message = ob_get_clean();
//		$message = apply_filters( 'ur_membership_payment_successful_email_custom_template', $message, $subject );
//		$headers = \UR_Emailer::ur_get_header();
//
//		return wp_mail( $user->user_email, $subject, $message, $headers );
//	}

	/**
	 * Send payment successful email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_payment_approval_email( $data ) {

		$subject        = __( 'Payment Approved!', 'user-registration' );
		$currency       = get_option( 'user_registration_payment_currency', 'USD' );
		$currencies     = ur_payment_integration_get_currencies();
		$symbol         = $currencies[ $currency ]['symbol'];
		$message        = sprintf( __( 'Hi <b><i>%s</i></b>, Your payment of amount %s for the membership: <b>%s</b> has been approved by admin.', 'user-registration' ), $data['display_name'] ?? '', number_format( $data['total_amount'], 2 ) . $symbol, $data['post_title'] ?? '' ) . "\n\n";
		$extra_message  = __( "You can now login as a member." );
		$final_greeting = __( 'Thank You.', 'user-registration' );


		$template_file = locate_template( 'payment-approval-email.php' );

		if ( ! $template_file ) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/payment-approval-email.php';
		}
		ob_start();
		require $template_file;

		$message = ob_get_clean();
		$message = apply_filters( 'ur_membership_payment_successful_email_custom_template', $message, $subject );
		$headers = \UR_Emailer::ur_get_header();

		return wp_mail( $data["user_email"], $subject, $message, $headers );
	}

	/**
	 * Send membership cancellation email user
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_membership_cancellation_email_user( $data ) {
		if ( ! $this->validate_email_fields( $data ) || ! self::is_membership_email_enabled( 'user_registration_enable_membership_cancellation_user_email' ) ) {
			return false;
		}
		$subject  = get_option( 'user_registration_membership_cancellation_user_email_subject', esc_html__( 'Membership Cancellation Confirmed – {{membership_plan}}', 'user-registration' ) );
		$user     = get_userdata( $data['member_id'] );
		$form_id  = ur_get_form_id_by_userid( $data['member_id'] );
		$settings = new UR_Settings_Membership_Cancellation_User_Email();
		$values   = array(
			'membership_plan_name' => esc_html__( $data['membership_metas']['title'] )
		);
		$message  = apply_filters( 'user_registration_process_smart_tags', get_option( 'user_registration_membership_cancellation_admin_email_message', $settings->user_registration_get_membership_cancellation_user_email() ), $values, $form_id );;
		$message     = apply_filters( 'ur_membership_membership_cancellation_email_custom_template', $message, $subject );
		$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );
		$subject     = \UR_Emailer::parse_smart_tags( $subject, $values );
		$headers     = \UR_Emailer::ur_get_header();

		return \UR_Emailer::user_registration_process_and_send_email( $user->user_email, $subject, $message, $headers, array(), $template_id );
	}

	/**
	 * Send membership cancellation email admin
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_membership_cancellation_email_admin( $data ) {
		if ( ! $this->validate_email_fields( $data ) || ! self::is_membership_email_enabled( 'user_registration_enable_membership_cancellation_admin_email' ) ) {
			return false;
		}
		$subject = get_option( 'user_registration_membership_cancellation_admin_email_subject', esc_html__( 'Membership Cancelled!', 'user-registration' ) );
		$user    = get_userdata( $data['member_id'] );

		$form_id  = ur_get_form_id_by_userid( $data['member_id'] );
		$settings = new UR_Settings_Membership_Cancellation_Admin_Email();

		$message = apply_filters( 'user_registration_process_smart_tags', get_option( 'user_registration_membership_cancellation_admin_email_message', $settings->user_registration_get_membership_cancellation_admin_email() ), $data, $form_id );;

		$message     = apply_filters( 'ur_membership_membership_cancellation_email_custom_template', $message, $subject );
		$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );

		$headers = \UR_Emailer::ur_get_header();

		return \UR_Emailer::user_registration_process_and_send_email( get_option( 'admin_email' ), $subject, $message, $headers, array(), $template_id );

	}

	public function send_membership_renewal_email( $data ) {
		$subject = get_option( 'user_registration_membership_renewal_reminder_user_email_subject', esc_html__( 'Reminder: Automatic Renewal for Your Membership is Coming Up', 'user-registration' ) );
		$user    = get_userdata( $data['member_id'] );

		$form_id  = ur_get_form_id_by_userid( $data['member_id'] );
		$settings = new UR_Settings_Membership_Renewal_Reminder_User_Email();

		$message = apply_filters( 'user_registration_process_smart_tags', get_option( 'user_registration_membership_renewal_reminder_user_email_message', $settings->user_registration_get_membership_renewal_reminder_user_email() ), $data, $form_id );;

		$message     = apply_filters( 'ur_membership_membership_cancellation_email_custom_template', $message, $subject );

		$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );

		$headers = \UR_Emailer::ur_get_header();

		return \UR_Emailer::user_registration_process_and_send_email( $data['user_email'], $subject, $message, $headers, array(), $template_id );
	}
	/**
	 * Validate email fields
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function validate_email_fields( $data ) {
		if ( ! isset( $data['order'] ) ) {
			$this->logger->notice( 'Send Email:Registration:Payment Order not present.', array( 'source' => 'ur-membership-email-logs' ) );

			return false;
		}
		if ( ! isset( $data['subscription'] ) ) {
			$this->logger->notice( 'Send Email:Registration:Payment Subscription not present.', array( 'source' => 'ur-membership-email-logs' ) );

			return false;
		}
		if ( ! isset( $data['membership_metas'] ) ) {
			$this->logger->notice( 'Send Email:Registration:Payment Membership Data not found.', array( 'source' => 'ur-membership-email-logs' ) );

			return false;
		}

		return true;
	}
	/**
	 * Checks if the 'is_membership_email_enabled' option is set to true or false.
	 *
	 * @return bool Returns true if the option is set to true, false otherwise.
	 * @since 1.0.0
	 */
	public static function is_membership_email_enabled( $option ) {
		if ( ! ur_string_to_bool( get_option( 'user_registration_enable_membership_cancellation_admin_email', true ) ) ) {
			return false;
		}

		return true;
	}
}
