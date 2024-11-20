<?php

namespace WPEverest\URMembership\Admin\Repositories;

use WPEverest\URMembership\Admin\Interfaces\OrdersInterface;
use WPEverest\URMembership\TableList;

class OrdersRepository extends BaseRepository implements OrdersInterface {
	/**
	 * @var string
	 */
	protected $table, $posts_table, $users_table, $subscriptions_table;

	/**
	 * Constructor of this class
	 */
	public function __construct() {
		$this->table               = TableList::orders_table();
		$this->posts_table         = TableList::posts_table();
		$this->users_table         = TableList::users_table();
		$this->subscriptions_table = TableList::subscriptions_table();
	}

	/**
	 * Get all Orders
	 *
	 * @param $args
	 *
	 * @return array|object|\stdClass[]
	 */
	public function get_all( $args ) {
		$sql = "
					SELECT urmo.ID AS order_id,
						wpp.ID as post_id,
						urmo.transaction_id,
						wpu.display_name,
						wpp.post_title,
						wpp.post_content,
						urmo.payment_method,
						wpu.user_email,
						urmo.status,
						urmo.created_at
					FROM $this->table urmo
					JOIN $this->posts_table wpp ON urmo.item_id = wpp.ID
					JOIN $this->users_table wpu ON urmo.user_id = wpu.ID
					WHERE 1 = 1
				";
		if ( isset( $args['membership_id'] ) ) {
			$sql .= sprintf( " AND wpp.ID = '%d'", $args['membership_id'] );
		}
		if ( isset( $args['s'] ) ) {
			$sql .= sprintf( " AND (wpu.display_name LIKE '%%%s%%' OR wpu.user_email LIKE '%%%s%%' OR urmo.transaction_id LIKE '%%%s%%')", $args['s'], $args['s'], $args['s'] );
		}
		if ( isset( $args['payment_method'] ) ) {
			$sql .= sprintf( " AND urmo.payment_method = '%s'", $args['payment_method'] );
		}
		if ( isset( $args['status'] ) ) {
			$sql .= sprintf( " AND urmo.status = '%s'", $args['status'] );
		}

		$sql .= sprintf( ' ORDER BY %s %s', $args['orderby'], $args['order'] );

		$result = $this->wpdb()->get_results( $sql, ARRAY_A );

		return ! $result ? array() : $result;
	}

	/**
	 * Create a new order
	 *
	 * @param $data
	 *
	 * @return array|false|mixed|object|\stdClass|void
	 */
	public function create( $data ) {
		$result                               = $this->wpdb()->insert(
			$this->table,
			$data['orders_data']
		);
		$order_id                             = $this->wpdb()->insert_id;
		$data['orders_meta_data']['order_id'] = $order_id;
		if ( isset( $data['orders_meta_data'] ) && ! empty( $data['orders_meta_data'] ) ) {
			$this->wpdb()->insert(
				TableList::order_meta_table(),
				$data['orders_meta_data']
			);
		}

		return $this->retrieve( $order_id );
	}

	/**
	 * Get order details
	 *
	 * @param $order_id
	 *
	 * @return array|object|\stdClass|void
	 */
	public function get_order_detail( $order_id ) {
		$result = $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"
				SELECT urmo.ID AS order_id,
					wpp.ID as post_id,
					wpu.ID as user_id,
					urmo.subscription_id as subscription_id,
					urmo.transaction_id,
					wpu.user_nicename,
					wpu.display_name,
					wpu.user_email,
					wpp.post_title,
					wpp.post_content,
					urmo.payment_method,
					urmo.status,
					urmo.notes,
					urmo.trial_status,
					urmo.total_amount,
					urms.status as subscription_status,
					urms.trial_start_date,
					urms.trial_end_date,
					urms.start_date as subscription_start_date,
					urms.expiry_date,
					urms.next_billing_date,
					urms.billing_cycle,
					urms.billing_amount,
					urms.coupon,
					urmo.created_at
				FROM $this->table urmo
					JOIN $this->posts_table wpp ON urmo.item_id = wpp.ID
					JOIN $this->users_table wpu ON urmo.user_id = wpu.ID
					JOIN $this->subscriptions_table urms ON urmo.subscription_id = urms.ID
				WHERE urmo.ID = %d
		",
				$order_id
			),
			ARRAY_A
		);

		return ! $result ? array() : $result;
	}

	/**
	 * Get specific order by their subscription ID
	 *
	 * @param $subscription_id
	 *
	 * @return array|mixed|object|\stdClass|void
	 */
	public function get_order_by_subscription( $subscription_id ) {
		$result = $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"
				SELECT * from $this->table
				WHERE subscription_id = %d
		",
				$subscription_id
			),
			ARRAY_A
		);

		return ! $result ? array() : $result;
	}

}
