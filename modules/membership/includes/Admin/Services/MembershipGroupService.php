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

use WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository;

class MembershipGroupService {
	protected $logger, $membership_group_repository;

	public function __construct() {
		$this->logger                      = ur_get_logger();
		$this->membership_group_repository = new MembershipGroupRepository();
	}

	public function get_membership_groups() {
		return array(
			101 => __( 'Default Group', 'user-registration' ),
			102 => __( 'New Group', 'user-registration' )
		);
	}

	/**
	 * get_membership_group_by_id
	 *
	 * @param $group_id
	 *
	 * @return array|object|\stdClass|null
	 */
	public function get_membership_group_by_id( $group_id ) {
		return $this->membership_group_repository->get_single_membership_group_by_ID( $group_id );
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

	public function validate_membership_group_data( $data ) {
		$response = array(
			'status' => true
		);
		if ( isset( $data['post_data']['name'] ) && empty( $data['post_data']['name'] ) ) {
			$response['status']  = false;
			$response['message'] = __( 'Field name is required', 'user-registration' );
		}
		if ( isset( $data['post_meta_data']['name'] ) && empty( $data['post_meta_data']['memberships'] ) ) {
			$response['status']  = false;
			$response['message'] = __( 'Field memberships is required', 'user-registration' );
		}

		return $response;
	}

	public function prepare_membership_data( $post_data ) {
		$membership_group_id = ! empty( $post_data['post_data']['ID'] ) ? absint( $post_data['post_data']['ID'] ) : '';
		$post_meta_data      = array_map( 'sanitize_text_field', $post_data['post_meta_data']['memberships'] );

		return array(
			'post_data'      => array(
				'ID'             => $membership_group_id,
				'post_title'     => sanitize_text_field( $post_data['post_data']['name'] ),
				'post_content'   => wp_json_encode( array(
					'description' => sanitize_text_field( $post_data['post_data']['description'] ),
					'status'      => wp_validate_boolean( $post_data['post_data']['status'] ),
				) ),
				'post_type'      => 'ur_membership_groups',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			'post_meta_data' => array(
				'meta_key'   => 'urmg_memberships',
				'meta_value' => wp_json_encode( $post_meta_data ),
			)
		);
	}

	/**
	 * create_membership_groups
	 *
	 * @param $post_data
	 *
	 * @return int|true[]|\WP_Error
	 */
	public function create_membership_groups( $post_data ) {
		$post_data = json_decode( wp_unslash( $post_data ), true );
		$data      = $this->validate_membership_group_data( $post_data );
		if ( $data['status'] ) {
			$data                = $this->prepare_membership_data( $post_data );
			$data                = apply_filters( 'ur_membership_after_create_membership_groups_data_before_save', $data );
			$membership_group_id = wp_insert_post( $data['post_data'] );
			if ( $membership_group_id ) {
				if ( ! empty( $data['post_data']['ID'] ) ) {
					update_post_meta( $membership_group_id, $data['post_meta_data']['meta_key'], $data['post_meta_data']['meta_value'] );
				} else {
					add_post_meta( $membership_group_id, $data['post_meta_data']['meta_key'], $data['post_meta_data']['meta_value'] );

				}

				return array(
					'status'              => true,
					'membership_group_id' => $membership_group_id
				);
			}

		}

		return $data;
	}
}
