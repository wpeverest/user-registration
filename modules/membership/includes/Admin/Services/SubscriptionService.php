<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;
use WPEverest\URM\Mollie\Services\PaymentService as MollieService;
class SubscriptionService {

	protected $members_subscription_repository, $members_orders_repository, $membership_repository, $orders_repository;

	public function __construct() {
		$this->members_subscription_repository = new MembersSubscriptionRepository();
		$this->members_orders_repository       = new MembersOrderRepository();
		$this->membership_repository           = new MembershipRepository();
		$this->orders_repository               = new OrdersRepository();
	}

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
			case 'mollie':
				$mollie_service = new MollieService();
				return $mollie_service->cancel_subscription( $order, $subscription );
				break;
			default:
				return apply_filters( 'user_registration_membership_cancel_subscription', array( 'status' =>  false ) , $order, $subscription );
		}
	}

	public function daily_membership_renewal_check() {
		$days_before_value = get_option( 'user_registration_membership_renewal_reminder_days_before', 7 );

		if ( $days_before_value <= 0 ) {
			return;
		}
		$period        = get_option( 'user_registration_membership_renewal_reminder_period', 'weeks' );
		$value_in_days = convert_to_days( $days_before_value, $period );
		$date          = new \DateTime( 'today' );
		$check_date    = $date->modify( "+$value_in_days day" )->format( 'Y-m-d H:i:s' );


		$subscriptions = $this->members_subscription_repository->get_about_to_expire_subscriptions( $check_date );
		if ( empty( $subscriptions ) ) {
			return;
		}
		$email_service = new EmailService();
		foreach ( $subscriptions as $subscription ) {
			$user_id      = $subscription['member_id'];
			$checked_date = get_user_meta( $user_id, 'urm_billing_reminder_sent_for_date', true );
			error_log($checked_date);
			if ( $checked_date === $subscription['next_billing_date'] ) {
				continue;
			}
			$email_service->send_email( $subscription, 'membership_renewal' );
			update_user_meta( $subscription['member_id'], 'urm_billing_reminder_sent_for_date', $subscription['next_billing_date'] );
		}
	}

	/**
	 * send_cancel_emails
	 *
	 * @param $subscription_id
	 *
	 * @return void
	 */
	public function send_cancel_emails( $subscription_id ) {
		$email_service             = new EmailService();
		$current_user_subscription = $this->members_subscription_repository->get_membership_by_subscription_id( $subscription_id, false );

		$member_id = $current_user_subscription['user_id'];

		$latest_order = $this->members_orders_repository->get_member_orders( $member_id );

		$membership = $this->membership_repository->get_single_membership_by_ID( $current_user_subscription['item_id'] );

		$membership_metas          = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$membership_metas['title'] = $membership['post_title'];


		$subscription = $this->members_subscription_repository->get_member_subscription( $member_id );

		$email_data = array(
			'subscription'     => $subscription,
			'order'            => $latest_order,
			'membership_metas' => $membership_metas,
			'member_id'        => $member_id,
		);
		$email_service->send_email( $email_data, 'membership_cancellation_email_user' );
		$email_service->send_email( $email_data, 'membership_cancellation_email_admin' );
	}

	/**
	 * get_membership_plan_details
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function get_membership_plan_details( $data ) {
		$currencies = ur_payment_integration_get_currencies();
		$currency   = get_option( 'user_registration_payment_currency', 'USD' );
		$symbol     = $currencies[ $currency ]['symbol'];

		$subscription                   = $this->members_subscription_repository->get_member_subscription( $data['member_id'] );
		$membership_id 					= isset( $data['membership'] ) ? $data['membership'] : $subscription['item_id'];
		$membership                     = $this->membership_repository->get_single_membership_by_ID( $membership_id );
		$membership_metas               = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$membership_metas['post_title'] = $membership['post_title'];
		$member_order                   = $this->members_orders_repository->get_member_orders( $data['member_id'] );
		$order                          = $this->orders_repository->get_order_detail( $member_order['ID'] );
		$total                          = $order['total_amount'];

		if ( ! empty( $order['coupon'] ) && "bank" !== $order['payment_method'] && isset( $membership_metas ) && ( "paid" === $membership_metas['type'] || ( "subscription" === $membership_metas['type'] && "off" === $order['trial_status'] ) ) ) {
			$discount_amount = ( $order['coupon_discount_type'] === 'fixed' ) ? $order['coupon_discount'] : $order['total_amount'] * $order['coupon_discount'] / 100;
			$total           = $order['total_amount'] - $discount_amount;
		}
		$billing_cycle = ( "subscription" === $membership_metas['type'] ) ? ( 'day' === $membership_metas['subscription']['duration'] ) ? esc_html( 'Daily', 'user-registration' ) : ( esc_html( ucfirst( $membership_metas['subscription']['duration'] . 'ly' ) ) ) : 'N/A';

		return array(
			'membership_plan_name'              => esc_html( ucwords( $membership_metas['post_title'] ) ),
			'membership_plan_type'              => esc_html( ucwords( $membership_metas['type'] ) ),
			'membership_plan_payment_method'    => esc_html( ucwords( isset($data['order']['payment_method']) ? $data['order']['payment_method'] : $data['payment_method'] ) ),
			'membership_plan_trial_status'      => esc_html( ucwords( $order['trial_status'] ) ),
			'membership_plan_trial_start_date'  => esc_html( $subscription['trial_start_date'] ),
			'membership_plan_trial_end_date'    => esc_html( $subscription['trial_end_date'] ),
			'membership_plan_next_billing_date' => esc_html( $subscription['next_billing_date'] ),
			'membership_plan_status'            => esc_html( ucwords( $subscription['status'] ) ),
			'membership_plan_payment_date'      => esc_html( $order['created_at'] ),
			'membership_plan_billing_cycle'     => esc_html( ucwords( $billing_cycle ) ),
			'membership_plan_payment_amount'    => $symbol . number_format( $membership_metas['amount'], 2 ),
			'membership_plan_payment_status'    => esc_html( ucwords( $order['status'] ) ),
			'membership_plan_trial_amount'      => $symbol . number_format( ( 'on' === $order['trial_status'] ) ? $order['total_amount'] : 0, 2 ),
			'membership_plan_coupon_discount'   => isset( $order['coupon_discount'] ) ? ( ( isset( $order['coupon_discount_type'] ) && $order['coupon_discount_type'] == 'percent' ) ? $order['coupon_discount'] . '%' : $symbol . $order['coupon_discount'] ) : '',
			'membership_plan_coupon'            => esc_html( $order['coupon'] ?? '' ),
			'membership_plan_total'             => $symbol . number_format( $total, 2 ),
		);
	}
}
