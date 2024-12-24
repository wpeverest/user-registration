<?php

namespace WPEverest\URMembership\Admin\Repositories;

use BlockArt\BlockTypes\Tab;
use WPEverest\URMembership\Admin\Interfaces\MembersInterface;
use WPEverest\URMembership\TableList;

class MembersRepository extends BaseRepository implements MembersInterface {
	/**
	 * @var string
	 */
	protected $table, $subscription_table, $posts_table, $orders_table;

	/**
	 *  Constructor for this class
	 */
	public function __construct() {
		$this->table              = TableList::users_table();
		$this->subscription_table = TableList::subscriptions_table();
		$this->posts_table        = TableList::posts_table();
		$this->orders_table       = TableList::orders_table();
	}

	/**
	 * Create new Membership
	 *
	 * @param $data
	 *
	 * @return false|\WP_User
	 */
	public function create( $data ) {
		$new_user_id = wp_insert_user( $data['user_data'] );
		if ( $new_user_id ) {
			$user = new \WP_User( $new_user_id );
			update_user_meta( $new_user_id, 'ur_registration_source', 'membership' );

			$user->set_role( $data['role'] );

			if ( isset( $data['coupon_data'] ) && ! empty( $data['coupon_data'] ) ) {
				update_user_meta( $new_user_id, 'ur_coupon_discount_type', $data['coupon_data']['coupon_discount_type'] );
				update_user_meta( $new_user_id, 'ur_coupon_discount', $data['coupon_data']['coupon_discount'] );
			}

			return $user;
		}

		return false;
	}

	/**
	 * Get Member by their membership id.
	 *
	 * @param $id
	 *
	 * @return array|object|\stdClass|void|null
	 */
	public function get_member_membership_by_id( $id ) {
		return $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"SELECT wp.id as post_id ,
							urs.id as subscription_id,
							wo.ID as order_id,
							urs.user_id,
							urs.cancel_sub,
							wp.post_title,
							wp.post_content,
							urs.status,
							urs.trial_start_date,
							urs.trial_end_date,
							urs.billing_amount,
							urs.expiry_date,
							urs.start_date,
							urs.next_billing_date,
							urs.billing_cycle
       					FROM $this->subscription_table urs
                        JOIN $this->posts_table wp on wp.ID = urs.item_id
                        JOIN $this->orders_table wo on urs.ID = wo.subscription_id
        				WHERE urs.user_id = %d and wp.post_type = 'ur_membership'
				",
				$id
			),
			ARRAY_A
		);
	}

	public function get_all_members( $args ) {
		global $wpdb;
		$sql = "
				SELECT wpu.ID,
			       wums.ID AS subscription_id,
			       wpp.post_title,
			       wpu.user_login,
			       wpu.user_email,
			       wums.status,
			       wpu.user_registered,
			       wums.expiry_date,
			    	wumo.payment_method
				FROM wp_users wpu
		        JOIN wp_ur_membership_subscriptions wums ON wpu.ID = wums.user_id
				JOIN wp_ur_membership_orders wumo ON wpu.ID = wumo.user_id
		        JOIN wp_posts wpp ON wums.item_id = wpp.ID
				WHERE wpp.post_status = 'publish'
				AND 1 = 1
		";

		if ( isset( $args['membership_id'] ) ) {
			$sql .= sprintf( " AND wpp.ID = '%d'", $args['membership_id'] );
		}
		if ( !empty($args['search']) ) {
			$sql .= sprintf( " AND (wpu.display_name LIKE '%%%s%%' OR wpu.user_email LIKE '%%%s%%')", $args['search'], $args['search'] );
		}
		if ( !empty($args['include']) ) {
			$sql .= " AND wpu.ID IN " . "(".implode("," , $args['include']) . ")";
		}

		if(isset($args['orderby'] )) {
			$sql .= sprintf( ' ORDER BY %s %s', $args['orderby'], $args['order'] );
		}

		$result = $this->wpdb()->get_results( $sql, ARRAY_A );

		return ! $result ? array() : $result;
	}

}
