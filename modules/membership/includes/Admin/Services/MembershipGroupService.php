<?php

/**
 * MembershipGroupService.php
 *
 * MembershipService.php
 *
 * @class    MembershipGroupService.php
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Services;

class MembershipGroupService {
	protected $logger;

	public function __construct() {
		$this->logger = ur_get_logger();
	}

	public function get_membership_groups() {
		return array(
			101 => __( 'Default Group', 'user-registration' ),
			102 => __( 'New Group', 'user-registration' )
		);
	}


	public function get_group_memberships( $group_id ) {
//		$membership_repository = new MembershipRepository();
//		$memberships           = $membership_repository->get_all_membership();
//		$memberships           = apply_filters( 'build_membership_list_frontend', $memberships );
		$data = array(
			array(
				array(
					'ID'                      => 15,
					'title'                   => 'Paypal Membership',
					'type'                    => 'subscription',
					'amount'                  => 10,
					'currency_symbol'         => '$',
					'calculated_amount'       => 10,
					'period'                  => '$10 / 1 Month',
					'active_payment_gateways' => json_encode( array( 'paypal' => 'on' ) ),
				),
				array(
					'ID'                => 8,
					'title'             => 'My Membership',
					'type'              => 'free',
					'amount'            => 0,
					'currency_symbol'   => '$',
					'calculated_amount' => 0,
					'period'            => 'Free',
				),
			),
			array(
				array(
					'ID'                      => 22,
					'title'                   => 'Pro Membership',
					'type'                    => 'subscription',
					'amount'                  => 25,
					'currency_symbol'         => '$',
					'calculated_amount'       => 25,
					'period'                  => '$25 / 1 Month',
					'active_payment_gateways' => json_encode( array( 'stripe' => 'on' ) ),
				),
				array(
					'ID'                      => 33,
					'title'                   => 'Lifetime Membership',
					'type'                    => 'lifetime',
					'amount'                  => 299,
					'currency_symbol'         => '$',
					'calculated_amount'       => 299,
					'period'                  => '$299 One-Time',
					'active_payment_gateways' => json_encode( array( 'paypal' => 'on', 'stripe' => 'on' ) ),
				),
			),
			array(
				array(
					'ID'                      => 42,
					'title'                   => 'Student Membership',
					'type'                    => 'subscription',
					'amount'                  => 5,
					'currency_symbol'         => '$',
					'calculated_amount'       => 5,
					'period'                  => '$5 / 1 Month',
					'active_payment_gateways' => json_encode( array( 'stripe' => 'on' ) ),
				),
				array(
					'ID'                      => 51,
					'title'                   => 'Business Membership',
					'type'                    => 'subscription',
					'amount'                  => 50,
					'currency_symbol'         => '$',
					'calculated_amount'       => 50,
					'period'                  => '$50 / 1 Month',
					'active_payment_gateways' => json_encode( array( 'paypal' => 'on', 'stripe' => 'on' ) ),
				),
			),
			array(
				array(
					'ID'                      => 63,
					'title'                   => 'Trial Membership',
					'type'                    => 'trial',
					'amount'                  => 0,
					'currency_symbol'         => '$',
					'calculated_amount'       => 0,
					'period'                  => 'Free for 7 Days',
					'active_payment_gateways' => json_encode( array( 'stripe' => 'on' ) ),
				),
			)
		);

		return $data[ rand( 0, 3 ) ];
	}
}
