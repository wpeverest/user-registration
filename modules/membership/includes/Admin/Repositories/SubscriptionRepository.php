<?php

namespace WPEverest\URMembership\Admin\Repositories;

use WPEverest\URMembership\Admin\Interfaces\SubscriptionInterface;
use WPEverest\URMembership\Admin\Services\SubscriptionService;
use WPEverest\URMembership\TableList;

class SubscriptionRepository extends BaseRepository implements SubscriptionInterface {

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var
	 */
	protected $orders_repository;

	/**
	 * Constructor of this class.
	 */
	public function __construct() {
		$this->table             = TableList::subscriptions_table();
		$this->orders_repository = new OrdersRepository();
	}

	/**
	 * cancel_subscription_by_id
	 *
	 * @param $subscription_id
	 * @param $send_email
	 * @param $is_upgrade
	 *
	 * @return array|bool[]|mixed|null
	 */
	public function cancel_subscription_by_id( $subscription_id, $send_email = true, $is_upgrade = false ) {
		$subscription = $this->retrieve( $subscription_id );

		$order = $this->orders_repository->get_order_by_subscription( $subscription_id );


		$subscription_service = new SubscriptionService();
		if ( 'canceled' === $subscription['status'] ) {
			return array(
				'status'  => false,
				'message' => esc_html__( 'Subscription is already canceled.', 'user-registration' ),
			);
		}

		if ( ! $is_upgrade && ( 'free' === $order['order_type'] || 'paid' === $order['order_type'] || empty( $order['payment_method'] ) ) ) {
			$this->update(
				$subscription_id,
				array(
					'status' => 'canceled',
				)
			);
			if ( $send_email ) {
				$subscription_service->send_cancel_emails( $subscription_id );
			}
			ur_get_logger()->notice( 'Cancellation successful for free/paid membership.', array( 'source' => 'urm-cancellation-log' ) );

			return array(
				'status'  => true,
				'message' => esc_html__( 'Subscription Cancelled Successfully', 'user-registration' ),
			);
		} else {
			if($is_upgrade) {
				$get_user_old_subscription = json_decode( get_user_meta( $subscription['user_id'], 'urm_previous_subscription_data', true ), true );

				if ( ! empty( $get_user_old_subscription ) ) {
					$subscription = $get_user_old_subscription;
				}

				if ( empty( $subscription['subscription_id'] ) ) {
					return array(
						'status'  => false,
						'message' => esc_html__( 'Subscription id not present.', 'user-registration' ),
					);
				}
				$get_user_old_order = json_decode( get_user_meta( $subscription['user_id'], 'urm_previous_order_data', true ), true );

				if ( ! empty( $get_user_old_order ) ) {
					$order = $get_user_old_order;
				}
			}

			$cancel_sub = $subscription_service->cancel_subscription( $order, $subscription );
			ur_get_logger()->notice( print_r( $cancel_sub, true ), array( 'source' => 'urm-cancellation-log' ) );

			if ( $cancel_sub['status'] ) {

				$this->update( $subscription_id, array( 'status' => 'canceled', ) );
				if ( $send_email ) {
					$subscription_service->send_cancel_emails( $subscription_id );
				}
				ur_get_logger()->notice( 'Cancellation successful for subscription.', array( 'source' => 'urm-cancellation-log' ) );

				return array(
					'status'  => true,
					'message' => esc_html__( 'Subscription Cancelled Successfully', 'user-registration' ),
				);
			} else {
				return $cancel_sub;
			}
		}

	}
	public function reactivate_subscription_by_id( $subscription_id, $send_email = true ) {
		$subscription = $this->retrieve( $subscription_id );

		if( 'active' === $subscription[ 'status' ] ) {
			return array(
				'status' => false,
				'message' => esc_html__( 'Subscription is already active.', 'user-registration' ),
			);
		}

		if( 'expired' !== $subscription[ 'status' ] ) {
			$order = $this->orders_repository->get_order_by_subscription( $subscription_id );

			$subscription_service = new SubscriptionService();
			$subscription_service->reactivate_subscription( $order, $subscription );
			$result = $this->update(
				$subscription_id,
				array(
					'status' => 'active',
				)
			);
			if( ! $result ) {
				return array(
					'status' => false,
					'message' => __( 'Failed to update subscription.', 'user-registration' ),
				);
			}
			return array(
				'status' => true,
			);
		}
		return array(
			'status' => false,
		);
	}
}
