<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;

class SubscriptionService {

	/**
	 * Prepare data for subscriptions
	 *
	 * @param $data
	 * @param $member
	 *
	 * @return array
	 */
	public function prepare_subscription_data( $data, $member ) {

		$current_user    = wp_get_current_user();
		$membership      = get_post( $data['membership_data']['membership'], ARRAY_A );
		$membership_meta = json_decode( wp_unslash( get_post_meta( $membership['ID'], 'ur_membership', true ) ), true );
		$status          = 'pending';

		if ( 'subscription' == $membership_meta['type'] ) { // TODO: calculate with trail date
			$expiry_date = self::get_expiry_date( $data['membership_data']['start_date'], $membership_meta['subscription']['duration'], $membership_meta['subscription']['value'] );
			$status      = 'on' === $membership_meta['trial_status'] ? 'trial' : 'pending';
		}
		if ( $current_user->ID != 0 || 'free' == $membership_meta['type'] ) {
			$status = 'active';
		}
		$subscription_data = array(
			'user_id'           => $member->ID,
			'item_id'           => $membership['ID'],
			'start_date'        => $data['membership_data']['start_date'],
			'expiry_date'       => $expiry_date ?? '',
			'next_billing_date' => $expiry_date ?? '',
			'billing_amount'    => $membership_meta['amount'] ?? 0,
			'status'            => $status,
			'cancel_sub'        => $membership_meta['cancel_subscription'] ?? 'immediately',
		);

		if ( isset( $data['coupon_data'] ) && ! empty( $data['coupon_data'] ) ) {
			$subscription_data['coupon'] = $data['coupon_data']['coupon_code'];
		}

		if ( isset( $membership_meta['trial_status'] ) && 'on' == $membership_meta['trial_status'] ) {

			$trial_data = array(
				'trial_start_date' => date( 'Y-m-d' ),
				'trial_end_date'   => self::get_expiry_date( date( 'Y-m-d' ), $membership_meta['trial_data']['duration'], $membership_meta['trial_data']['value'] ),
			);

			$subscription_data                      = array_merge( $subscription_data, $trial_data );
			$subscription_data['start_date']        = $trial_data['trial_end_date'];
			$subscription_data['expiry_date']       = self::get_expiry_date( $trial_data['trial_end_date'], $membership_meta['subscription']['duration'], $membership_meta['subscription']['value'] );
			$subscription_data['next_billing_date'] = $subscription_data['expiry_date'];
		}

		return $subscription_data;
	}

	/**
	 * Get Expiry date
	 *
	 * @param $start_date
	 * @param $period
	 * @param $value
	 *
	 * @return false|string
	 * @throws \Exception
	 */
	public static function get_expiry_date( $start_date, $period, $value ) {
		$allowedPeriods = array( 'day', 'week', 'month', 'year' );
		if ( ! in_array( $period, $allowedPeriods ) ) {
			return false;
		}
		$start_date   = new \DateTime( $start_date ?? '' );
		$intervalSpec = 'P' . $value . strtoupper( substr( $period, 0, 1 ) );
		$interval     = new \DateInterval( $intervalSpec );
		$start_date->add( $interval );

		return $start_date->format( 'Y-m-d' );

	}

	/**
	 * Cancel subscription
	 *
	 * @param $order
	 * @param $subscription
	 *
	 * @return array|bool[]|void
	 */
	public function cancel_subscription( $order, $subscription ) {
		switch ( $order['payment_method'] ) {
			case 'paypal';
				$paypal_service = new PaypalService();

				return $paypal_service->cancel_subscription( $order, $subscription );
				break;
			case 'stripe';
				$stripe_service = new StripeService();
				return $stripe_service->cancel_subscription( $order, $subscription );
				break;
			default:
				break;
		}
	}
}
