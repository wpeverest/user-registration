<?php

namespace WPEverest\URMembership\Emails;

use WPEverest\URMembership\Admin\Services\SubscriptionService;
use WPEverest\URMembership\Emails\Admin\UR_Settings_Membership_Cancellation_Admin_Email;
use WPEverest\URMembership\Emails\User\UR_Settings_Membership_Cancellation_User_Email;
use WPEverest\URMembership\Emails\User\UR_Settings_Membership_Renewal_Reminder_User_Email;

/**
 * EmailSettings.php
 *
 * @class    EmailSettings.php
 * @date     4/24/2025 : 2:01 PM
 */
class EmailSettings {
	public function __construct() {
		add_filter( 'user_registration_email_classes', array( $this, 'add_email_settings' ), 10, 1 );
		add_action( 'urm_daily_membership_renewal_check', array( $this, 'membership_renewal_check' ), 10, 1 );
	}

	/**
	 * Add email settings
	 *
	 * @param $emails
	 *
	 * @return array
	 */
	public function add_email_settings( $emails ) {
		$new_emails = array(
			'UR_Settings_Membership_Cancellation_Admin_Email'    => new UR_Settings_Membership_Cancellation_Admin_Email(),
			'UR_Settings_Membership_Cancellation_User_Email'     => new UR_Settings_Membership_Cancellation_User_Email(),
		);

		if ( UR_PRO_ACTIVE ) {
			$new_emails = array(
				'UR_Settings_Membership_Renewal_Reminder_User_Email' => new UR_Settings_Membership_Renewal_Reminder_User_Email(),
			);
		}

		return array_merge( $emails, $new_emails );
	}

	/**
	 * Send membership renewal email
	 *
	 * @return void
	 */
	public function membership_renewal_check() {
		if ( ! ur_option_checked( 'user_registration_membership_renewal_reminder_user_email', false ) ) {
			return;
		}
		$subscription_service = new SubscriptionService();
		$subscription_service->daily_membership_renewal_check();
	}
}
