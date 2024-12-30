<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/SubscriptionInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

interface SubscriptionInterface extends BaseInterface {

	/**
	 * Cancel subscription by subscription ID
	 *
	 * @param $subscription_id
	 *
	 * @return mixed
	 */
	public function cancel_subscription_by_id( $subscription_id );

}
