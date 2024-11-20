<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/MembersOrderInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

/**
 * Interface MembersOrderInterface
 *
 * MembersOrderInterface is an interface for accessing member orders.
 */
interface MembersOrderInterface extends BaseInterface {
	/**
	 * Get the orders for a given member_id.
	 *
	 * @param int $member_id The member's id.
	 * @return array An array of member orders.
	 */
	public function get_member_orders( $member_id );
}
