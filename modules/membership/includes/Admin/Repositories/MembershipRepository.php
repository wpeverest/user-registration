<?php

namespace WPEverest\URMembership\Admin\Repositories;

use WPEverest\URMembership\Admin\Interfaces\MembershipInterface;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\TableList;

class MembershipRepository extends BaseRepository implements MembershipInterface {
	protected $table, $posts_meta_table;

	public function __construct() {
		$this->table            = TableList::posts_table();
		$this->posts_meta_table = TableList::posts_meta_table();
	}

	/**
	 * @return array
	 */
	public function get_all_membership() {
		// TODO : maybe change this raw queries to wp_Query
		global $wpdb;
		$sql = "
				SELECT wpp.ID,
				       wpp.post_title,
				       wpp.post_content,
				       wpp.post_status,
				       wpp.post_type,
				       wpm.meta_value
				FROM $this->table wpp
				         JOIN $this->posts_meta_table wpm on wpm.post_id = wpp.ID
				WHERE wpm.meta_key = 'ur_membership'
				  AND wpp.post_type = 'ur_membership'
				  AND wpp.post_status = 'publish'
				ORDER BY 1 DESC
		";

		$memberships        = $wpdb->get_results(
			$sql,
			ARRAY_A
		);
		$membership_service = new MembershipService();
		return $membership_service->prepare_membership_data( $memberships );

	}

	/**
	 * get_single_membership_by_ID
	 *
	 * @param $id
	 *
	 * @return array|object|\stdClass|void|null
	 */
	public function get_single_membership_by_ID( $id ) {
		// TODO : maybe change this raw queries to wp_Query
		global $wpdb;

		return $wpdb->get_row(
			$this->wpdb()->prepare(
				"SELECT wpp.ID,
				       wpp.post_title,
				       wpp.post_content,
				       wpp.post_status,
				       wpp.post_type,
				       wpm.meta_value
				FROM $this->table wpp
				         JOIN $this->posts_meta_table wpm on wpm.post_id = wpp.ID
				WHERE wpm.meta_key = 'ur_membership'
				  AND wpp.post_type = 'ur_membership'
				  AND wpp.post_status = 'publish'
				AND wpp.ID = %d
				ORDER BY 1 DESC",
				$id
			),
			ARRAY_A
		);

	}


	public function get_multiple_membership_by_ID( $ids ) {
		global $wpdb;
		$sql = "
				SELECT wpp.ID,
				       wpp.post_title,
				       wpp.post_content,
				       wpp.post_status,
				       wpp.post_type,
				       wpm.meta_value
				FROM $this->table wpp
				         JOIN $this->posts_meta_table wpm on wpm.post_id = wpp.ID
				WHERE wpm.meta_key = 'ur_membership'
				  AND wpp.post_type = 'ur_membership'
				  AND wpp.post_status = 'publish'
				AND wpp.ID IN ($ids)
				ORDER BY 1 DESC
		";

		$memberships        = $wpdb->get_results(
			$sql,
			ARRAY_A
		);
		$membership_service = new MembershipService();
		return $membership_service->prepare_membership_data( $memberships );
	}

}
