<?php
/**
 * User_Registration_Membership Crons
 *
 * @package User_Registration_Membership
 * @since  1.0.0
 */

namespace WPEverest\URMembership;


use WPEverest\URMembership\Admin\Services\SubscriptionService;

class Crons {

	public function __construct() {
		$this->init();
	}

	public function init() {
		if ( ur_check_module_activation( 'membership' ) ) {
			add_action( 'urm_run_delayed_subscription', array( $this, 'run_daily_delayed_membership_subscriptions' ) );
			add_action( 'urm_daily_membership_renewal_check', array( $this, 'membership_renewal_check' ), 10, 1 );
		}
	}

	/**
	 * Run daily subscription updated for all delayed subscriptions.
	 *
	 * @return void
	 */
	public function run_daily_delayed_membership_subscriptions() {
		$subscription_service = new SubscriptionService();
		$subscription_service->run_daily_delayed_membership_subscriptions();
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
