<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/MembersSubscriptionInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

/**
 * Interface MembersSubscriptionInterface.
 *
 * @package  URMembership\MembersSubscriptionInterface
 * @category Interface
 */
interface MembersSubscriptionInterface extends BaseInterface {

	/**
	 * Get Member Subscription
	 *
	 * @param int $member_id Member Id.
	 *
	 * @return mixed
	 */
	public function get_member_subscription( $member_id );

	/**
	 * Get Membership by Subscription Id
	 *
	 * @param int $subscription_id Subscription Id.
	 *
	 * @return mixed
	 */
	public function get_membership_by_subscription_id( $subscription_id );
}
