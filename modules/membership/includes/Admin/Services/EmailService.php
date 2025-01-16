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

class EmailService
{
	protected $email_type, $logger;

	public function __construct()
	{
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
	public function send_email($data, $type)
	{
		if (!isset($data['member_id'])) {
			$this->logger->notice('Send Email:Registration: Member Id not Present.', array('source' => 'ur-membership-email-logs'));

			return false;
		}
		switch ($type) {
			case 'user_register_user':  // public registration email to member.
				return $this->send_user_register_email($data);
			case 'user_register_admin': // public registration email to admin.
				return $this->send_user_register_admin_email($data);
			case 'user_register_backend_user':  // public registration email to member.
				return $this->send_user_backend_register_email($data);
			case 'payment_successful': // payment successful message to member.
				return self::send_payment_successful_email($data);
			case 'payment_approval': // payment approval message to member.
				return self::send_payment_approval_email($data);
			case 'membership_cancellation_email_user': // membership cancellation email to member.
				return self::send_membership_cancellation_email_user($data);
			case 'membership_cancellation_email_admin': // membership cancellation email to admin.
				return self::send_membership_cancellation_email_admin($data);
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
	public function send_user_backend_register_email($data)
	{

		$membership_title = get_the_title($data['membership']);
		$subject          = __('Congratulations! Registration completed', 'user-registration');
		$message          = sprintf(__('Hi, %s', 'user-registration'), $data['username']) . "\n\n";
		$message         .= sprintf(__('Account created successfully under membership <b><i>%s</i></b>.', 'user-registration'), $membership_title) . "\n\n";
		$extra_message    = sprintf(__('<b>Username: </b><i>%1$s</i> <b>Password: </b><i>%2$s</i> ', 'user-registration'), $data['username'], $data['password']) . "\n\n";
		$final_greeting   = __('Thank You.', 'user-registration');

		$template_file = locate_template('membership-registration-email.php');
		if (!$template_file) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/membership-registration-email.php';
		}
		ob_start();
		require $template_file;

		$message = ob_get_clean();
		$message = apply_filters('ur_membership_registration_email_custom_template', $message, $subject);
		$headers = \UR_Emailer::ur_get_header();

		return wp_mail($data['email'], $subject, $message, $headers);
	}

	/**
	 * Send user register email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_user_register_email($data)
	{

		$membership_title = get_the_title($data['membership']);
		$subject          = __('Congratulations! Registration completed', 'user-registration');
		$message          = sprintf(__('Hi, %s', 'user-registration'), $data['username']) . "\n\n";
		$message         .= sprintf(__('We have successfully received your request for registration under membership <b><i>%s</i></b>.', 'user-registration'), $membership_title) . "\n\n";
		$extra_message    = '';
		$final_greeting   = __('Thank You.', 'user-registration');

		if ('free' !== $data['payment_method']) {
			$message .= sprintf(__('Please wait for your payment to be verified. ', 'user-registration'), $membership_title) . "\n\n";
		}
		$template_file = locate_template('membership-registration-email.php');
		if (!$template_file) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/membership-registration-email.php';
		}
		ob_start();
		require $template_file;

		$message = ob_get_clean();
		$message = apply_filters('ur_membership_registration_email_custom_template', $message, $subject);
		$headers = \UR_Emailer::ur_get_header();

		return wp_mail($data['email'], $subject, $message, $headers);
	}

	/**
	 * Send user register email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_user_register_admin_email($data)
	{
		$membership_title = get_the_title($data['membership']);
		$subject          = __('New Member Registration!', 'user-registration');
		$message          = sprintf(__('A new user <b><i>%1$s</i></b> has been registered under membership <b><i>%2$s</i></b>,', 'user-registration'), $data['username'], $membership_title) . "\n\n";
		$extra_message    = '';
		$final_greeting   = __('Thank You.', 'user-registration');

		if ('free' !== $data['payment_method']) {
			$message .= sprintf(__('paid with <b><i>%s</i></b>.', 'user-registration'), ucfirst($data['payment_method'])) . "\n\n";
		}
		$template_file = locate_template('membership-registration-email.php');
		if (!$template_file) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/membership-registration-email.php';
		}
		ob_start();
		require $template_file;

		$message = ob_get_clean();
		$message = apply_filters('ur_membership_registration_email_admin_custom_template', $message, $subject);
		$headers = \UR_Emailer::ur_get_header();

		return wp_mail(get_option('admin_email'), $subject, $message, $headers);
	}

	/**
	 * Send payment successful email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_payment_successful_email($data)
	{
		if (!$this->validate_email_fields($data)) {
			return false;
		}
		$subject          = __('Payment Received!', 'user-registration');
		$user             = get_userdata($data['member_id']);
		$order            = $data['order'];
		$subscription     = $data['subscription'];
		$membership_metas = $data['membership_metas'];
		$extra_message    = $data['extra_message'] ?? '';
		$currency         = get_option('user_registration_payment_currency', 'USD');
		$currencies       = ur_payment_integration_get_currencies();
		$symbol           = $currencies[$currency]['symbol'];

		$total = $order['total_amount'];

		if (isset($order['coupon']) && !empty($order['coupon']) && "bank" !== $order['payment_method'] && isset( $membership_metas ) && ( "paid" === $membership_metas['type'] || ("subscription" === $membership_metas['type'] && "off" === $order['trial_status'] ) )) {
			$discount_amount = ( $order['coupon_discount_type'] === 'fixed' ) ? $order['coupon_discount'] : $order['total_amount'] * $order['coupon_discount'] / 100;
			$total           = $order['total_amount'] - $discount_amount;
		}
		$invoice_details  = array(
			'membership_name'   => esc_html($membership_metas['post_title']),
			'trial_status'      => esc_html($order['trial_status']),
			'trial_start_date'  => esc_html($subscription['trial_start_date']),
			'trial_end_date'    => esc_html($subscription['trial_end_date']),
			'next_billing_date' => esc_html($subscription['next_billing_date']),
			'payment_date'      => esc_html($order['created_at']),
			'billing_cycle'     => ('day' === $subscription['billing_cycle']) ? esc_html('Daily', 'user-registration') : (esc_html(ucfirst($subscription['billing_cycle'] . 'ly'))),
			'amount'            => $symbol . number_format($membership_metas['amount'], 2),
			'trial_amount'      => $symbol . number_format(('on' === $order['trial_status']) ? $order['total_amount'] : 0, 2),
			'coupon_discount'   => isset( $order['coupon_discount'] ) ? ((isset( $order['coupon_discount_type'] ) && $order['coupon_discount_type'] == 'percent' ) ? $order['coupon_discount'] . '%' : $symbol . $order['coupon_discount']) : '',
			'coupon'   			=> esc_html($order['coupon'] ?? ''),
			'total'             => $symbol . number_format($total, 2),
		);

		$template_file    = locate_template('payment-successful-email.php');

		if (!$template_file) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/payment-successful-email.php';
		}
		ob_start();
		require $template_file;

		$message = ob_get_clean();
		$message = apply_filters('ur_membership_payment_successful_email_custom_template', $message, $subject);
		$headers = \UR_Emailer::ur_get_header();

		return wp_mail($user->user_email, $subject, $message, $headers);
	}
	/**
	 * Send payment successful email
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_payment_approval_email($data)
	{

		$subject          = __('Payment Approved!', 'user-registration');
		$currency         = get_option('user_registration_payment_currency', 'USD');
		$currencies       = ur_payment_integration_get_currencies();
		$symbol           = $currencies[$currency]['symbol'];
		$message          = sprintf(__('Hi <b><i>%s</i></b>, Your payment of amount %s for the membership: <b>%s</b> has been approved by admin.', 'user-registration'), $data['display_name'] ?? '', number_format($data['total_amount'], 2) . $symbol, $data['post_title'] ?? '') . "\n\n";
		$extra_message    = __("You can now login as a member.");
		$final_greeting   = __('Thank You.', 'user-registration');


		$template_file    = locate_template('payment-approval-email.php');

		if (!$template_file) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/payment-approval-email.php';
		}
		ob_start();
		require $template_file;

		$message = ob_get_clean();
		$message = apply_filters('ur_membership_payment_successful_email_custom_template', $message, $subject);
		$headers = \UR_Emailer::ur_get_header();

		return wp_mail($data["user_email"], $subject, $message, $headers);
	}
	/**
	 * Send membership cancellation email user
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_membership_cancellation_email_user($data)
	{

		if (!$this->validate_email_fields($data)) {
			return false;
		}
		$subject = __('Membership Cancelled Successfully!', 'user-registration');
		$user    = get_userdata($data['member_id']);

		$message        = sprintf(__('Hi <b><i>%1$s</i></b>, Your request for cancellation of current membership  <b><i>%2$s</i></b> was successful.', 'user-registration'), esc_html($user->user_login), esc_html($data['membership_metas']['post_title'])) . "\n\n";
		$extra_message  = sprintf(__('We are sorry to see you leave.', 'user-registration'), esc_html($user->user_login), esc_html($data['membership_metas']['post_title'])) . "\n\n";
		$final_greeting = __('Good Bye.', 'user-registration');
		$template_file  = locate_template('membership-registration-email.php');
		if (!$template_file) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/membership-registration-email.php';
		}
		ob_start();
		require $template_file;
		$message = ob_get_clean();
		$message = apply_filters('ur_membership_membership_cancellation_email_custom_template', $message, $subject);

		$headers = \UR_Emailer::ur_get_header();

		return wp_mail($user->user_email, $subject, $message, $headers);
	}

	/**
	 * Send membership cancellation email admin
	 *
	 * @param $data
	 *
	 * @return bool|mixed|void
	 */
	public function send_membership_cancellation_email_admin($data)
	{

		if (!$this->validate_email_fields($data)) {
			return false;
		}
		$subject = __('Membership Cancelled!', 'user-registration');
		$user    = get_userdata($data['member_id']);

		$message        = sprintf(__('User <b><i>%1$s</i></b>, has cancelled their subscription under membership  <b><i>%2$s</i></b>.', 'user-registration'), esc_html($user->user_login), esc_html($data['membership_metas']['post_title'])) . "\n\n";
		$extra_message  = '';
		$final_greeting = '';
		$template_file  = locate_template('membership-registration-email.php');
		if (!$template_file) {
			$template_file = UR_MEMBERSHIP_DIR . 'includes/Templates/Emails/membership-registration-email.php';
		}
		ob_start();
		require $template_file;
		$message = ob_get_clean();
		$message = apply_filters('ur_membership_membership_cancellation_email_custom_template', $message, $subject);

		$headers = \UR_Emailer::ur_get_header();

		return wp_mail(get_option('admin_email'), $subject, $message, $headers);
	}

	/**
	 * Validate email fields
	 *
	 * @param $data
	 *
	 * @return false|void
	 */
	public function validate_email_fields($data)
	{
		if (!isset($data['order'])) {
			$this->logger->notice('Send Email:Registration:Payment Order not present.', array('source' => 'ur-membership-email-logs'));

			return false;
		}
		if (!isset($data['subscription'])) {
			$this->logger->notice('Send Email:Registration:Payment Subscription not present.', array('source' => 'ur-membership-email-logs'));

			return false;
		}
		if (!isset($data['membership_metas'])) {
			$this->logger->notice('Send Email:Registration:Payment Membership Data not found.', array('source' => 'ur-membership-email-logs'));

			return false;
		}

		return true;
	}
}
