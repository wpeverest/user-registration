<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/MembershipInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

/**
 * MembershipInterface
 * This is the interface for Membership.
 */
interface MembershipInterface extends BaseInterface {
	/**
	 * Get all membership
	 *
	 * @return array $result
	 */
	public function get_all_membership();

	/**
	 * Get single membership by ID
	 *
	 * @param int $id membership id.
	 * @return array $result
	 */
	public function get_single_membership_by_ID( $id );
}
