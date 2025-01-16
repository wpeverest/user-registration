<?php

namespace WPEverest\URMembership\Admin\Repositories;

use WPEverest\URMembership\Admin\Interfaces\MembershipGroupInterface;
use WPEverest\URMembership\Admin\Interfaces\MembershipInterface;
use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\TableList;

class MembershipGroupRepository extends BaseRepository implements MembershipGroupInterface {
	protected $table, $posts_meta_table;

	public function __construct() {
		$this->table            = TableList::posts_table();
		$this->posts_meta_table = TableList::posts_meta_table();
	}

	/**
	 * @return array
	 */
	public function get_all_membership_groups() {
		// TODO : maybe change this raw queries to wp_Query
		$sql = "
				SELECT wpp.ID,
				       wpp.post_title,
				       wpp.post_content,
				       wpp.post_status,
				       wpp.post_type,
				       wpm.meta_value
				FROM $this->table wpp
				         JOIN $this->posts_meta_table wpm on wpm.post_id = wpp.ID
				WHERE wpm.meta_key = 'urmg_memberships'
				  AND wpp.post_type = 'ur_membership_groups'
				  AND wpp.post_status = 'publish'
				ORDER BY 1 DESC
		";

		$membership_groups = $this->wpdb()->get_results(
			$sql,
			ARRAY_A
		);

		return $membership_groups;


	}

	/**
	 * get_single_membership_by_ID
	 *
	 * @param $id
	 *
	 * @return array|object|\stdClass|void|null
	 */
	public function get_single_membership_group_by_ID( $id ) {
		// TODO : maybe change this raw queries to wp_Query

		return $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"SELECT wpp.ID,
				       wpp.post_title,
				       wpp.post_content,
				       wpp.post_status,
				       wpp.post_type,
				       wpm.meta_value as memberships
				FROM $this->table wpp
				         JOIN $this->posts_meta_table wpm on wpm.post_id = wpp.ID
				WHERE wpm.meta_key = 'urmg_memberships'
				  AND wpp.post_type = 'ur_membership_groups'
				  AND wpp.post_status = 'publish'
				AND wpp.ID = %d
				ORDER BY 1 DESC",
				$id
			),
			ARRAY_A
		);

	}

	/**
	 * get_group_memberships_by_id
	 *
	 * @param $id
	 *
	 * @return array|mixed
	 */
	public function get_group_memberships_by_id( $id ) {

		$memberships = get_post_meta( $id, 'urmg_memberships', true );
		$memberships = str_replace( array( '[', ']' ), '', $memberships );
		if ( empty( $memberships ) ) {
			return array();
		}
		$membership_repository = new MembershipRepository();

		return $membership_repository->get_multiple_membership_by_ID( $memberships );
	}

	/**
	 * get_single_membership_group_by_name
	 *
	 * @param $name
	 *
	 * @return array|object|\stdClass|null
	 */
	public function get_single_membership_group_by_name( $name ) {
		// TODO : maybe change this raw queries to wp_Query

		return $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"SELECT wpp.ID,
				       wpp.post_title,
				       wpp.post_content,
				       wpp.post_status,
				       wpp.post_type,
				       wpm.meta_value as memberships
				FROM $this->table wpp
				         JOIN $this->posts_meta_table wpm on wpm.post_id = wpp.ID
				WHERE wpm.meta_key = 'urmg_memberships'
				  AND wpp.post_type = 'ur_membership_groups'
				AND wpp.post_title = %s
				ORDER BY 1 DESC",
				strtolower($name)
			),
			ARRAY_A
		);

	}

	/**
	 * is_form_related
	 * Just having the meta key means the form consist of a membership group
	 *
	 * @param $group_id
	 *
	 * @return bool
	 */
	public function get_group_form_id( $group_id ) {
		$meta_key_exists = $this->wpdb()->get_var(
			$this->wpdb()->prepare(
				"SELECT post_id FROM $this->posts_meta_table WHERE meta_key = %s LIMIT 1",
				"urm_form_group_" . $group_id
			)
		);

		return $meta_key_exists;
	}

	/**
	 * get_default_group_id
	 *
	 * @return string|null
	 */
	public function get_default_group_id() {
		return $this->wpdb()->get_var(
			$this->wpdb()->prepare(
				"SELECT post_id FROM $this->posts_meta_table WHERE meta_key = %s LIMIT 1",
				"urmg_default_group"
			)
		);
	}
}
