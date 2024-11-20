<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/OrdersInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

/**
 * Interface for the URMembership orders.
 *
 * @since 1.0.0
 */
interface OrdersInterface extends BaseInterface {
	/**
	 * Retrieves the orders.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The arguments to filter the orders.
	 *
	 * @return array The orders.
	 */
	public function get_all( array $args );

	/**
	 * Retrieves the details of an order.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return array|object The details of the order.
	 */
	public function get_order_detail( $order_id  );

	/**
	 * get_order_by_subscription
	 *
	 * @param $subscription_id
	 *
	 * @return mixed
	 */
	public function get_order_by_subscription( $subscription_id );
}
