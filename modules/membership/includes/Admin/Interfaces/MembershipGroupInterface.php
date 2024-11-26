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
}
