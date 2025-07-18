<?php

namespace WPEverest\URMembership\Admin\Repositories;

use WPEverest\URMembership\Admin\Interfaces\MembersInterface;
use WPEverest\URMembership\Admin\Interfaces\MembersOrderInterface;
use WPEverest\URMembership\TableList;

class MembersOrderRepository extends BaseRepository implements MembersOrderInterface {
	/**
	 * @var string
	 */
	protected $table, $users_table;

	/**
	 *
	 */
	public function __construct() {
		$this->table       = TableList::orders_table();
		$this->users_table = TableList::users_table();
	}

	/**
	 * get_member_orders
	 *
	 * @param $member_id
	 *
	 * @return array|false|object|\stdClass|void
	 */
	public function get_member_orders( $member_id ) {
		$result = $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"SELECT wumo.* FROM $this->users_table wpu
		         JOIN $this->table  wumo ON wpu.ID = wumo.user_id
		         WHERE wpu.ID = %d  ORDER BY wumo.created_at DESC",
				$member_id
			),
			ARRAY_A
		);

		return ! $result ? false : $result;
	}

	public function delete_member_order( $member_id, $delete_all = true ) {
		if ( $delete_all ) {
			// Delete all orders for the member
			$deleted = $this->wpdb()->query(
				$this->wpdb()->prepare(
					"DELETE FROM $this->table WHERE user_id = %d",
					$member_id
				)
			);
		} else {
			// Delete only the latest order for the member

			$deleted = $this->wpdb()->query(
				$this->wpdb()->prepare(
					"DELETE FROM $this->table
                 WHERE id = (
                     SELECT id FROM (
                         SELECT id FROM $this->table
                         WHERE user_id = %d
                         ORDER BY id DESC
                         LIMIT 1
                     ) AS latest
                 )",
					$member_id
				)
			);
		}

		return $deleted !== false && $deleted > 0;
	}

}
