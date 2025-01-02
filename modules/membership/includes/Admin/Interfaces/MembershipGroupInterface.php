<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/MembershipGroupInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

/**
 * MembershipInterface
 * This is the interface for Membership.
 */
interface MembershipGroupInterface extends BaseInterface {
	/**
	 * Get all membership
	 *
	 * @return array $result
	 */
	public function get_all_membership_groups();

	/**
	 * Get single membership by ID
	 *
	 * @param int $id membership id.
	 * @return array $result
	 */
	public function get_single_membership_group_by_ID( $id );

	/**
	 * get_group_memberships_by_id
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function get_group_memberships_by_id( $id );

	/**
	 * is_form_related
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function get_group_form_id( $id );
}
