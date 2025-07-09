<?php

namespace WPEverest\URMembership\Admin\Services;

use DateTime;
use WPEverest\URM\Mollie\Services\PaymentService as MollieService;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;

class SubscriptionService {

	protected $members_subscription_repository, $members_orders_repository, $membership_repository, $orders_repository, $subscription_repository;

	public function __construct() {
		$this->members_subscription_repository = new MembersSubscriptionRepository();
		$this->subscription_repository         = new SubscriptionRepository();
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
	 * @throws \Exception
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
		$billing_cycle = ( "subscription" === $membership_meta['type'] ) ? $membership_meta['subscription']['duration'] :  '';

		$subscription_data = array(
			'user_id'           => $member->ID,
			'item_id'           => $membership['ID'],
			'start_date'        => $data['membership_data']['start_date'],
			'expiry_date'       => $expiry_date ?? '',
			'next_billing_date' => $expiry_date ?? '',
			'billing_amount'    => $membership_meta['amount'] ?? 0,
			'status'            => $status,
			'cancel_sub'        => $membership_meta['cancel_subscription'] ?? 'immediately',
			'billing_cycle'     => $billing_cycle
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
		$logger = ur_get_logger();
		switch ( $order['payment_method'] ) {
			case 'paypal';
				$paypal_service = new PaypalService();
				$logger->notice( 'Paypal cancellation Reached', array( 'source' => 'urm-cancellation-log' ) );

				return $paypal_service->cancel_subscription( $order, $subscription );

			case 'stripe';

				$stripe_service = new StripeService();
				$logger->notice( 'Stripe cancellation Reached', array( 'source' => 'urm-cancellation-log' ) );

				return $stripe_service->cancel_subscription( $order, $subscription );

			case 'mollie':
				$mollie_service = new MollieService();

				return $mollie_service->cancel_subscription( $order, $subscription );

			default:
				$logger->notice( 'Default cancellation Reached', array( 'source' => 'urm-cancellation-log' ) );

				return apply_filters( 'user_registration_membership_cancel_subscription', array( 'status' => false ), $order, $subscription );
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
		$membership_id                  = isset( $data['membership'] ) ? $data['membership'] : $subscription['item_id'];
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
			'membership_plan_payment_method'    => esc_html( ucwords( isset( $data['order']['payment_method'] ) ? $data['order']['payment_method'] : $data['payment_method'] ) ),
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

	/**
	 * Handles the membership upgrade process.
	 *
	 * This function is called when a user chooses to upgrade their membership. It calculates the upgrade cost, cancels the previous subscription,
	 * creates a new order and redirects the user to the payment gateway.
	 *
	 * @param array $data The data passed from the front end.
	 *
	 * @return array The response from the payment gateway.
	 */
	public function upgrade_membership( $data ) {

		ur_get_logger()->notice( __( 'Subscription upgrade initiated', 'user-registration' ), array( 'source' => 'urm-upgrade-subscription' ) );

		$order_service = new OrderService();

		$current_subscription_id                   = $data['current_subscription_id'];
		$subscription                              = $this->subscription_repository->retrieve( $data['current_subscription_id'] );
		$user                                      = get_userdata( $subscription['user_id'] );
		$payment_method                            = $data['selected_pg'];
		$membership                                = $this->membership_repository->get_single_membership_by_ID( $subscription['item_id'] );
		$current_membership_details                = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$current_membership_details['post_title']  = $membership['post_title'];
		$membership                                = $this->membership_repository->get_single_membership_by_ID( $data['selected_membership_id'] );
		$selected_membership_details               = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$selected_membership_details['post_title'] = $membership['post_title'];
		$selected_membership_details['membership'] = $data['selected_membership_id'];

		$selected_membership_details['payment_method'] = $payment_method;

		$upgrade_details = $this->calculate_membership_upgrade_cost( $current_membership_details, $selected_membership_details, $subscription );

		if ( isset( $upgrade_details['status'] ) && ! $upgrade_details['status'] ) {
			ur_get_logger()->notice( __( 'Calculation Failed', 'user-registration-membership' ), array( 'source' => 'urm-upgrade-subscription' ) );

			return array(
				'response' => $upgrade_details
			);
		}

		ur_get_logger()->notice( __( 'Calculation finished', 'user-registration-membership' ), array( 'source' => 'urm-upgrade-subscription' ) );
		ur_get_logger()->notice( print_r( $upgrade_details, true ), array( 'source' => 'urm-upgrade-subscription' ) );


		$members_data = array(
			'membership_data' => $selected_membership_details,
		);

		if ( isset( $data['upgrade'] ) && $data["upgrade"] && "subscription" === $current_membership_details['type'] && "bank" !== $payment_method && "off" === $selected_membership_details['trial_status'] && ! isset( $upgrade_details['delayed_until'] ) ) {

			ur_get_logger()->notice( __( 'Cancelling previous subscription.', 'user-registration-membership' ), array( 'source' => 'urm-upgrade-subscription' ) );

			$cancel_subscription = $this->subscription_repository->cancel_subscription_by_id( $current_subscription_id, false );

			ur_get_logger()->notice( print_r( $cancel_subscription, true ), array( 'source' => 'urm-upgrade-subscription' ) );

			if ( ! $cancel_subscription['status'] ) {

				ur_get_logger()->notice( __( 'Cancelling subscription failed', 'user-registration-membership' ), array( 'source' => 'urm-upgrade-subscription' ) );

				$response["status"] = false;

				return $response;
			} else {
				$this->subscription_repository->cancel_subscription_by_id( $current_subscription_id, false );
			}
		}

		//save previous order
		$latest_order = $this->members_orders_repository->get_member_orders( $user->ID );
		update_user_meta( $user->ID, 'urm_previous_order_data', json_encode( $latest_order ) );

		$orders_data = $order_service->prepare_orders_data( $members_data, $user->ID, $subscription, $upgrade_details ); // prepare data for orders table.

		$order = $this->orders_repository->create( $orders_data );


		ur_get_logger()->notice( __( 'Order created ' . $order['ID'], 'user-registration-membership' ), array( 'source' => 'urm-upgrade-subscription' ) );

		$payment_service = new PaymentService( $payment_method, $data['selected_membership_id'], $user->data->user_email );
		$ur_authorize_net_data = isset( $data[ 'ur_authorize_net' ] ) ? $data[ 'ur_authorize_net' ] : [];
		$data            = array(
			'membership'        => $data['selected_membership_id'],
			'subscription_id'   => $subscription['ID'],
			'member_id'         => $user->ID,
			'email'             => $user->user_email,
			'transaction_id'    => $orders_data['orders_data']['transaction_id'],
			'upgrade'           => true,
			'subscription_data' => $subscription,
			'ur_authorize_net'  => $ur_authorize_net_data,
		);
		$data            = $data + $upgrade_details;


		$response = $payment_service->build_response( $data );

		$response["status"] = false;

		if ( isset( $response["payment_url"] ) || isset( $response["data"] ) || "stripe" === $payment_method || "free" === $payment_method ) {
			$response['status'] = true;

		} else {
			$this->orders_repository->delete( $order['ID'] );
		}

		return array(
			'extra'    => array(
				'member_id'                => $user->ID,
				'username'                 => $user->user_login,
				'transaction_id'           => $orders_data['orders_data']['transaction_id'],
				'updated_membership_title' => $selected_membership_details['post_title']
			),
			'response' => $response
		);

	}

	/**
	 * Calculate upgrade cost and details between memberships.
	 *
	 * Determines the chargeable amount for upgrading from a current to a selected membership.
	 * Considers subscription duration, trial periods, and subscription status.
	 *
	 * @param array $current_membership_details Current membership details.
	 * @param array $selected_membership_details Selected membership details.
	 * @param array $subscription Subscription details.
	 *
	 * @return array Upgrade calculation results.
	 */
	public function calculate_membership_upgrade_cost( $current_membership_details, $selected_membership_details, $subscription ) {
		$upgrade_type               = $current_membership_details['type'] . '->' . $selected_membership_details['type'];
		$upgrade_membership_service = new UpgradeMembershipService();

		$result['status'] = true;

		if ( isset( $selected_membership_details['trial_status'] ) && 'on' === $selected_membership_details['trial_status'] && ! empty( $subscription['trial_end_date'] ) ) {
			$is_trial = $subscription['trial_end_date'] > date( 'Y-m-d H:i:s' );
		} else {
			$is_trial = isset( $selected_membership_details['trial_status'] ) && 'on' === $selected_membership_details['trial_status'];
		}

		switch ( $upgrade_type ) {
			case 'free->free':
			case 'paid->free':
				$result['chargeable_amount'] = 0;
				break;
			case 'subscription->free':
				$result['chargeable_amount'] = 0;
				$result['delayed_until']     = $subscription['expiry_date'];
				break;
			case 'free->paid':
				$result['chargeable_amount'] = $selected_membership_details['amount'];
				break;
			case 'free->subscription':
				$result['chargeable_amount']            = $selected_membership_details['amount'];
				$result['remaining_subscription_value'] = $selected_membership_details['subscription']['value'];
				break;
			case 'paid->paid':
				$result = $upgrade_membership_service->handle_paid_to_paid_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription );
				break;
			case 'paid->subscription':
				$result = $upgrade_membership_service->handle_paid_to_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription );
				break;
			case 'subscription->subscription':
			case 'subscription->paid':
				$result = $upgrade_membership_service->handle_subscription_to_paid_or_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription, $is_trial );
				break;
		}

		if ( ! $result['status'] ) {
			return $result;
		}

		return array(
			'trial_status'                 => $is_trial ? "on" : "off",
			'chargeable_amount'            => ! empty( $result['chargeable_amount'] ) ? $result['chargeable_amount'] : 0,
			'remaining_subscription_value' => ! empty( $result['remaining_subscription_value'] ) ? $result['remaining_subscription_value'] : 0,
			'delayed_until'                => ! empty( $result['delayed_until'] ) ? $result['delayed_until'] : ''
		);

	}

	/**
	 * Prepares subscription data for upgrading a membership.
	 *
	 * This method retrieves and processes the membership data for a given member,
	 * calculates the expiry and trial dates based on the membership type, and
	 * constructs an array of subscription data including user ID, membership ID,
	 * start date, expiry date, next billing date, billing amount, and status.
	 *
	 * @param int $membership_id The ID of the membership to be upgraded.
	 * @param int $member_id The ID of the member for whom the subscription is being upgraded.
	 * @param array $extra_data Additional data required for preparing the subscription, such as
	 *                          remaining subscription days, trial status, and total used trial days.
	 *
	 * @return array The prepared subscription data including trial information if applicable.
	 * @throws \Exception
	 */
	public function prepare_upgrade_subscription_data( $membership_id, $member_id, $extra_data ) {
		$current_subscription         = $extra_data['subscription_data'];
		$remaining_subscription_value = $extra_data['remaining_subscription_value'];
		$membership                   = get_post( $membership_id, ARRAY_A );
		$membership_meta              = json_decode( wp_unslash( get_post_meta( $membership['ID'], 'ur_membership', true ) ), true );
		$expiry_date                  = '';
		if ( 'subscription' == $membership_meta['type'] ) { // TODO: calculate with trial date
			$expiry_date = self::get_expiry_date( date( 'Y-m-d' ), $membership_meta['subscription']['duration'], $remaining_subscription_value );
		}
		$billing_cycle = ( "subscription" === $membership_meta['type'] ) ? $membership_meta['subscription']['duration'] :  '';

		$subscription_data = array(
			'user_id'           => $member_id,
			'item_id'           => $membership['ID'],
			'start_date'        => date( 'Y-m-d' ),
			'expiry_date'       => $expiry_date,
			'next_billing_date' => $expiry_date,
			'billing_amount'    => $membership_meta['amount'] ?? 0,
			'status'            => 'free' === $membership_meta['type'] ? 'active' : 'pending',
			'billing_cycle'     => $billing_cycle
		);

		if ( isset( $membership_meta['trial_status'] ) && "on" === $membership_meta['trial_status'] ) {
			$remaining_trial_days          = $membership_meta['trial_data']['value'];
			$dont_calculate_trial_end_date = ! empty( $current_subscription['trial_end_date'] ) && $current_subscription['trial_end_date'] > date( 'Y-m-d 00:00:00' );
			$trial_data                    = array(
				'trial_start_date' => date( 'Y-m-d' ),
				'trial_end_date'   => ! $dont_calculate_trial_end_date ? self::get_expiry_date( date( 'Y-m-d' ), $membership_meta['trial_data']['duration'], $remaining_trial_days ) : $current_subscription['trial_end_date'],
			);

			$subscription_data                      = array_merge( $subscription_data, $trial_data );
			$subscription_data['start_date']        = $trial_data['trial_end_date'];
			$subscription_data['expiry_date']       = self::get_expiry_date( $trial_data['trial_end_date'], $membership_meta['subscription']['duration'], $remaining_subscription_value );
			$subscription_data['next_billing_date'] = $subscription_data['expiry_date'];
		}


		return $subscription_data;
	}

	/**
	 * validate if a membership can validate from current to another
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function can_upgrade( $data ) {
		$membership_service = new MembershipService();
		$membership_details = $membership_service->get_membership_details( $data['current_membership_id'] );
		$status             = true;
		if ( empty( $membership_details['upgrade_settings']['upgrade_path'] ) ) {
			return array(
				'status'  => false,
				'message' => __( "Sorry, you cannot upgrade to the selected plan.", "user-registration" )
			);
		}
		$upgradable_memberships = explode( ',', $membership_details['upgrade_settings']['upgrade_path'] );

		$status = in_array( $data['selected_membership_id'], $upgradable_memberships );
		if ( ! $status ) {
			return array(
				'status'  => false,
				'message' => __( "Sorry, you cannot upgrade to the selected plan.", "user-registration" )
			);
		}

		$subscription                = $this->subscription_repository->retrieve( $data['current_subscription_id'] );
		$membership                  = $this->membership_repository->get_single_membership_by_ID( $data['selected_membership_id'] );
		$selected_membership_details = wp_unslash( json_decode( $membership['meta_value'], true ) );

		if ( isset( $selected_membership_details['trial_status'] ) && "on" === $selected_membership_details['trial_status'] && ! empty( $subscription['trial_end_date'] ) && $subscription['trial_end_date'] < date( 'Y-m-d H:i:s' ) ) {
			return array(
				'status'  => false,
				'message' => __( "Sorry, You’re not eligible for another trial. Please choose a regular membership plan.", "user-registration" )
			);
		}

		return array(
			'status' => true,
		);

	}

	public function run_daily_delayed_membership_subscriptions() {
		$all_delayed_orders = $this->orders_repository->get_all_delayed_orders( date( 'Y-m-d 00:00:00' ) );

		ur_get_logger()->notice( __( 'Scheduled Subscriptions job started for the date: (' . date( 'd F,Y' ) . ')', 'user-registration' ), array( 'source' => 'urm-membership-crons' ) );

		if ( empty( $all_delayed_orders ) ) {
			ur_get_logger()->notice( __( 'No delayed orders found.', 'user-registration' ), array( 'source' => 'urm-membership-crons' ) );

			return;
		}
		$updated_subscription_for_users = array();

		foreach ( $all_delayed_orders as $data ) {
			$decoded_data = json_decode( $data['sub_data'], true );
			if ( ! isset( $decoded_data['subscription_id'] ) ) {
				continue;
			}
			$subscription_id = $decoded_data['subscription_id'];
			$user            = get_userdata( $decoded_data['member_id'] );
			if ( $user ) {
				$cancel_subscription = $this->subscription_repository->cancel_subscription_by_id( $subscription_id, false, true );
				ur_get_logger()->notice( $cancel_subscription['message'], array( 'source' => 'urm-membership-crons' ) );
				$previous_subscription             = json_decode( get_user_meta( $user->ID, 'urm_previous_subscription_data', true ), true );
				$updated_subscription_for_users[]  = $user->user_login;
				$decoded_data['subscription_data'] = $previous_subscription;
				$subscription_data                 = $this->prepare_upgrade_subscription_data( $decoded_data['membership'], $decoded_data['member_id'], $decoded_data );
				$subscription_data['status']       = 'active';
				$this->subscription_repository->update( $subscription_id, $subscription_data );
				$last_order = $this->members_orders_repository->get_member_orders( $user->ID );
				$this->orders_repository->delete_order_meta( array(
					'order_id' => $last_order['ID'],
					'meta_key' => 'delayed_until'
				) );
				delete_user_meta( $user->ID, 'urm_next_subscription_data' );
				delete_user_meta( $user->ID, 'urm_previous_subscription_data' );
				delete_user_meta( $user->ID, 'urm_previous_order_data' );
			}
		}

		ur_get_logger()->notice( __( 'Subscription updated for ' . implode( ',', $updated_subscription_for_users ), 'user-registration' ), array( 'source' => 'urm-membership-crons' ) );


	}

}
