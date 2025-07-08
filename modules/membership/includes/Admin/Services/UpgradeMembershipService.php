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
	 * Handle Paid to Paid membership Upgrade
	 *
	 * @param $current_membership_details
	 * @param $selected_membership_details
	 * @param $subscription
	 *
	 * @return false[]
	 */
	protected function calculate_chargeable_amount( $selected_amount, $current_amount, $upgrade_type ) {
		if ( 'full' === $upgrade_type ) {
			return $selected_amount;
		}
		if ( $selected_amount > $current_amount ) {
			return $selected_amount - $current_amount;
		}

		return $selected_amount;
	}

	public function handle_paid_to_paid_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$upgrade_settings = $current_membership_details['upgrade_settings'];
		$response         = array(
			'status' => false
		);
		if ( ! ( $upgrade_settings['upgrade_action'] ) ) {
			$response['status']  = true;
			$response['message'] = __( "Membership upgrade is not enabled for this plan", "user-registration" );
		}
		$response['status']            = true;
		$response['chargeable_amount'] = $this->calculate_chargeable_amount(
			$selected_membership_details['amount'],
			$current_membership_details['amount'],
			$upgrade_settings['upgrade_type']
		);

		return $response;
	}

	public function handle_paid_to_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$selected_membership_amount   = $selected_membership_details['amount'];
		$current_membership_amount    = $current_membership_details['amount'];
		$upgrade_type                 = $current_membership_details['upgrade_settings']['upgrade_type'];
		$remaining_subscription_value = $selected_membership_details['subscription']['value'];
		$delayed_until                = '';

		$chargeable_amount = $this->calculate_chargeable_amount(
			$selected_membership_amount,
			$current_membership_amount,
			$upgrade_type
		);

		if ( 'partial' === $upgrade_type && $selected_membership_amount < $current_membership_amount ) {
			$delayed_until = $subscription['expiry_date'];
		}

		return array(
			'status'                       => true,
			'chargeable_amount'            => $chargeable_amount,
			'remaining_subscription_value' => $remaining_subscription_value,
			'delayed_until'                => $delayed_until
		);
	}

	public function handle_subscription_to_paid_or_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription, $is_trial ) {
		$selected_membership_amount   = $selected_membership_details['amount'];
		$current_membership_amount    = $current_membership_details['amount'];
		$upgrade_type                 = $current_membership_details['upgrade_settings']['upgrade_type'];
		$chargeable_amount            = 0;
		$remaining_subscription_value = $selected_membership_details['subscription']['value'];
		$delayed_until                = '';
		$timezone = get_option( 'timezone_string' );
		if ( ! $timezone ) {
			$timezone = 'UTC';
		}
		$tz                                  = new \DateTimeZone( $timezone );
		$dateTime                            = \DateTime::createFromFormat( 'Y-m-d', date( 'Y-m-d' ), $tz );

		if ( "full" === $upgrade_type ) {
			$chargeable_amount = $selected_membership_amount;
		} else {
			if ( $selected_membership_amount > $current_membership_amount ) {
				$start_date                          = new \DateTime( $subscription['start_date'], $tz );
				$days_passed                         = $dateTime->diff( $start_date )->format( '%a' );
				$current_membership_duration_in_days = convert_to_days( $current_membership_details['subscription']['value'], $current_membership_details['subscription']['duration'] );
				$price_per_day                       = $current_membership_amount / $current_membership_duration_in_days;
				$prorate_discount                    = $current_membership_amount - ( $price_per_day * $days_passed );
				$chargeable_amount                   = ( $is_trial ) ? $selected_membership_amount : ( $selected_membership_amount - $prorate_discount );
			} else {
				$chargeable_amount = $selected_membership_amount;
				$delayed_until = $subscription['expiry_date'];
				if ( $is_trial ) {
					$expiry_date = new \DateTime($subscription['expiry_date'], $tz);
					$trial_in_days = convert_to_days($selected_membership_details['trial_data']['value'],$selected_membership_details['trial_data']['duration'] );
					$trial_end_date = !empty($subscription['trial_end_date']) ? $subscription['trial_end_date'] : date('Y-m-d 00:00:00', strtotime("+$trial_in_days days"));
					$trial_end_date_obj = new \DateTime($trial_end_date, $tz);
					$remaining_trial_days = $dateTime->diff( $trial_end_date_obj )->format( '%a' );
					$delayed_until = $expiry_date->modify("+$remaining_trial_days days")->format('Y-m-d');
				}

			}
		}

		return array(
			'status'                       => true,
			'chargeable_amount'            => $chargeable_amount,
			'remaining_subscription_value' => $remaining_subscription_value,
			'delayed_until'                => $delayed_until
		);
	}

}
