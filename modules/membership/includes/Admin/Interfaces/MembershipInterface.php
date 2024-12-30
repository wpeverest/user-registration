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

	/**
	 * get_multiple_membership_by_ID
	 *
	 * @param $ids
	 *
	 * @return mixed
	 */
	public function get_multiple_membership_by_ID( $ids );

	/**
	 * get_all_page_with_membership_form
	 *
	 * @return mixed
	 */
	public function replace_old_form_shortcode_with_new( $form_id );

	/**
	 * get_membership_forms
	 *
	 * @return mixed
	 */
	public function get_membership_forms(  );

	/**
	 * assign_users_to_new_form
	 *
	 * @param $form_id
	 *
	 * @return mixed
	 */
	public function assign_users_to_new_form( $form_id );
}
