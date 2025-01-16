<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/MembersInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

/**
 * Members Interface
 */
interface MembersInterface extends BaseInterface {
	/**
	 * Get member membership by ID
	 *
	 * @param int $id Member ID.
	 *
	 * @return array
	 */
	public function get_member_membership_by_id( $id );

}
