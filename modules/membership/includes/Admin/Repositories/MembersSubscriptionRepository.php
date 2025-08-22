<?php

namespace WPEverest\URMembership\Admin\Repositories;

use WPEverest\URMembership\Admin\Interfaces\MembersSubscriptionInterface;
use WPEverest\URMembership\TableList;

class MembersSubscriptionRepository extends BaseRepository implements MembersSubscriptionInterface {
	/**
	 * @var string
	 */
	protected $table, $users_table, $posts_table, $posts_meta_table;

	/**
	 * Constructor of this class
	 */
	public function __construct() {
		$this->table            = TableList::subscriptions_table();
		$this->users_table      = TableList::users_table();
		$this->posts_table      = TableList::posts_table();
		$this->posts_meta_table = TableList::posts_meta_table();
	}

	/**
	 * Get members subscription by their ID
	 *
	 * @param $member_id
	 *
	 * @return array|false|mixed|object|\stdClass|void
	 */
	public function get_member_subscription( $member_id ) {
		$result = $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"SELECT wums.* FROM $this->users_table wpu
		         JOIN $this->table wums ON wpu.ID = wums.user_id
		         WHERE wpu.ID = %d",
				$member_id
			),
			ARRAY_A
		);

		return ! $result ? false : $result;
	}

	/**
	 * Get membership by members subscription ID.
	 *
	 * @param $subscription_id
	 *
	 * @return array|false|mixed|object|\stdClass|void
	 */
	public function get_membership_by_subscription_id( $subscription_id , $secondary = false) {
		$compare_id = !$secondary ? 'wpus.ID = %d' : 'wpus.subscription_id = %s';

		$result = $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"SELECT wpp.ID,
                	   wpus.user_id,
                	   wpus.ID as sub_id,
                	   wpus.item_id,
                	   wpus.status,
				       wpp.post_title,
				       wpp.post_content,
				       wpp.post_status,
				       wpp.post_type,
				       wpm.meta_value
				FROM $this->table wpus
				         JOIN $this->posts_table wpp on wpus.item_id = wpp.ID
				         JOIN $this->posts_meta_table wpm on wpm.post_id = wpp.ID
				WHERE wpm.meta_key = 'ur_membership'
				  AND wpp.post_type = 'ur_membership'
				  AND wpp.post_status = 'publish'
				  AND $compare_id",
				$subscription_id
			),
			ARRAY_A
		);

		return ! $result ? false : $result;
	}

	/**
	 * Return all subscription which are about to be billed on the specified date
	 *
	 * @param $check_date
	 *
	 * @return array|object|stdClass[]
	 */
	public function get_about_to_expire_subscriptions( $check_date ) {
		$sql = sprintf( "
						SELECT wu.user_email,
						       wu.user_login as username,
						       wu.ID as member_id,
						       wp.post_title as membership_plan_name,
						       wums.item_id as membership,
						       wums.next_billing_date,
						       wums.expiry_date
						FROM  $this->table wums
					    LEFT JOIN $this->users_table wu ON wums.user_id = wu.ID
					    LEFT JOIN $this->posts_table wp ON wums.item_id = wp.ID
						WHERE NOT wums.status = 'canceled'
						AND wums.next_billing_date = '%s'
						", $check_date );

		$result = $this->wpdb()->get_results( $sql, ARRAY_A );

		return ! $result ? array() : $result;
	}

	/**
	 * Return all subscription which are about to be billed on the specified date
	 *
	 * @param $check_date
	 *
	 * @return array|object|stdClass[]
	 */
	public function get_expired_subscriptions( $check_date ) {
		$sql = sprintf( "
						SELECT wu.user_email,
						       wu.user_login as username,
						       wu.ID as member_id,
						       wp.post_title as membership_plan_name,
						       wums.item_id as membership,
						       wums.next_billing_date,
						       wums.expiry_date
						FROM  $this->table wums
					    LEFT JOIN $this->users_table wu ON wums.user_id = wu.ID
					    LEFT JOIN $this->posts_table wp ON wums.item_id = wp.ID
						WHERE wums.status = 'expired'
						AND wums.expiry_date = '%s'
						", $check_date );

		$result = $this->wpdb()->get_results( $sql, ARRAY_A );

		return ! $result ? array() : $result;
	}
}
