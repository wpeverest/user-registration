<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;

class UpgradeMembershipService {

	protected $members_subscription_repository, $members_orders_repository, $membership_repository, $orders_repository, $subscription_repository;

	public function __construct() {
		$this->members_subscription_repository = new MembersSubscriptionRepository();
		$this->subscription_repository         = new SubscriptionRepository();
		$this->members_orders_repository       = new MembersOrderRepository();
		$this->membership_repository           = new MembershipRepository();
		$this->orders_repository               = new OrdersRepository();
	}

	/**
	 * @throws \Exception
	 */
	public function handle_free_to_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {

		$current_membership_amount  = $current_membership_details['amount'];
		$selected_membership_amount = $selected_membership_details['amount'];
		$upgrade_type               = $current_membership_details['upgrade_settings']['upgrade_type'];
		$member_id                  = $subscription['user_id'];
		$today                      = new \DateTime();

		$total_used_trial_days                = 0;
		$is_trial                             = isset( $selected_membership_details['trial_status'] ) && $selected_membership_details['trial_status'] === 'on';
		$selected_membership_duration_in_days = convert_to_days( $selected_membership_details['subscription']['value'], $selected_membership_details['subscription']['duration'] );

		if ( "full" === $upgrade_type ) {
			$chargeable_amount           = $selected_membership_amount;
			$remaining_subscription_days = $selected_membership_details['subscription']['value'];
		} else {
			$current_membership_duration_in_days        = convert_to_days( $current_membership_details['subscription']['value'], $current_membership_details['subscription']['duration'] );
			$current_membership_trial_duration_in_days  = "on" == $current_membership_details["trial_status"] ? convert_to_days( $current_membership_details['trial_data']['value'], $current_membership_details['trial_data']['duration'] ) : 0;
			$selected_membership_trial_duration_in_days = "on" == $selected_membership_details["trial_status"] ? convert_to_days( $selected_membership_details['trial_data']['value'], $selected_membership_details['trial_data']['duration'] ) : 0;

			$current_membership_cost_per_day = $current_membership_amount / $current_membership_duration_in_days;
			$subscription_start_date         = new \DateTime( $subscription['start_date'] );
			$total_used_subscription_days    = $today->diff( $subscription_start_date )->d;
			$total_used_amount               = $total_used_subscription_days * $current_membership_cost_per_day;
			$chargeable_amount               = $selected_membership_amount - $total_used_amount;
			$remaining_subscription_days     = $selected_membership_duration_in_days - $total_used_subscription_days;

			$total_used_trial_days = ! empty( get_user_meta( $member_id, 'total_trial_days', true ) ) ? get_user_meta( $member_id, 'total_trial_days', true ) : $total_used_trial_days;

		}
		if ( ! empty( $subscription['trial_start_date'] ) ) {
			$trial_start_date      = new \DateTime( $subscription['trial_start_date'] );
			$total_used_trial_days = $today->diff( $trial_start_date )->d;

			update_user_meta( $member_id, 'urm_total_trial_days', $total_used_trial_days );
		}

		return array(
			'status'                      => true,
			'total_used_trial_days'       => $total_used_trial_days,
			'chargeable_amount'           => $chargeable_amount,
			'is_trial'                    => $is_trial,
			'remaining_subscription_days' => $remaining_subscription_days
		);
	}

	/**
	 * Handle Paid to Paid membership Upgrade
	 *
	 * @param $current_membership_details
	 * @param $selected_membership_details
	 * @param $subscription
	 *
	 * @return false[]
	 */
	public function handle_paid_to_paid_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$upgrade_settings = $current_membership_details['upgrade_settings'];
		$response         = array(
			'status' => false
		);
		if ( ! ( $upgrade_settings['upgrade_action'] ) ) {
			$response['status']  = true;
			$response['message'] = __( "Membership upgrade is not enabled for this plan", "user-registration" );
		}
		if ( 'full' === $upgrade_settings['upgrade_type'] ) {
			$response['status']            = true;
			$response['chargeable_amount'] = $selected_membership_details['amount'];
		}

		return $response;
	}

	public function handle_subscription_to_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$selected_membership_amount = $selected_membership_details['amount'];
		$upgrade_type               = $current_membership_details['upgrade_settings']['upgrade_type'];
		$total_used_trial_days                = 0;
		$is_trial                             = isset( $selected_membership_details['trial_status'] ) && $selected_membership_details['trial_status'] === 'on';
		$chargeable_amount = 0;
		$remaining_subscription_days = 0;
		if ( "full" === $upgrade_type ) {
			$chargeable_amount           = $selected_membership_amount;
			$remaining_subscription_days = $selected_membership_details['subscription']['value'];
		}
		return array(
			'status'                      => true,
			'total_used_trial_days'       => $total_used_trial_days,
			'chargeable_amount'           => $chargeable_amount,
			'is_trial'                    => $is_trial,
			'remaining_subscription_days' => $remaining_subscription_days
		);
	}
	public function handle_paid_to_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$selected_membership_amount = $selected_membership_details['amount'];
		$upgrade_type               = $current_membership_details['upgrade_settings']['upgrade_type'];
		$total_used_trial_days                = 0;
		$is_trial                             = isset( $selected_membership_details['trial_status'] ) && $selected_membership_details['trial_status'] === 'on';
		$chargeable_amount = 0;
		$remaining_subscription_days = 0;
		if ( "full" === $upgrade_type ) {
			$chargeable_amount           = $selected_membership_amount;
			$remaining_subscription_days = $selected_membership_details['subscription']['value'];
		}
		return array(
			'status'                      => true,
			'total_used_trial_days'       => $total_used_trial_days,
			'chargeable_amount'           => $chargeable_amount,
			'is_trial'                    => $is_trial,
			'remaining_subscription_days' => $remaining_subscription_days
		);
	}

	public function handle_subscription_to_paid_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$selected_membership_amount = $selected_membership_details['amount'];
		$upgrade_type               = $current_membership_details['upgrade_settings']['upgrade_type'];
		$total_used_trial_days                = 0;
		$is_trial                             = isset( $selected_membership_details['trial_status'] ) && $selected_membership_details['trial_status'] === 'on';
		$chargeable_amount = 0;
		$remaining_subscription_days = 0;
		if ( "full" === $upgrade_type ) {
			$chargeable_amount           = $selected_membership_amount;
			$remaining_subscription_days = $selected_membership_details['subscription']['value'];
		}
		return array(
			'status'                      => true,
			'total_used_trial_days'       => $total_used_trial_days,
			'chargeable_amount'           => $chargeable_amount,
			'is_trial'                    => $is_trial,
			'remaining_subscription_days' => $remaining_subscription_days
		);
	}

}
