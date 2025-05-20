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
	 * Cancel Subscription By ID.
	 *
	 * @param $subscription_id
	 *
	 * @return array|bool[]|mixed|void
	 */
	public function cancel_subscription_by_id( $subscription_id ) {
		$subscription = $this->retrieve( $subscription_id );
		$order        = $this->orders_repository->get_order_by_subscription( $subscription_id );
		$subscription_service = new SubscriptionService();
		if ( 'canceled' === $subscription['status'] ) {
			return array(
				'status'  => false,
				'message' => esc_html__( 'Subscription is already canceled.', 'user-registration' ),
			);
		}
		if ( 'free' === $order['order_type'] || 'paid' === $order['order_type'] || empty( $order['payment_method'] ) ) {

			$this->update(
				$subscription_id,
				array(
					'status' => 'canceled',
				)
			);
			$subscription_service->send_cancel_emails($subscription_id);
			return array(
				'status'  => true,
				'message' => esc_html__( 'Subscription Cancelled Successfully', 'user-registration' ),
			);
		} else {
			$cancel_sub           = $subscription_service->cancel_subscription( $order, $subscription );

			if ( $cancel_sub['status'] ) {
				$this->update( $subscription_id, array( 'status' => 'canceled' ) );
				$subscription_service->send_cancel_emails($subscription_id);
				return array(
					'status'  => true,
					'message' => esc_html__( 'Subscription Cancelled Successfully', 'user-registration' ),
				);
			} else {
				return $cancel_sub;
			}
		}

	}

}
