<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;

class MembersService {
	/**
	 * @var MembershipRepository
	 */
	protected $membership_repository;

	/**
	 * Constructor of this class
	 */
	public function __construct() {
		$this->membership_repository = new MembershipRepository();
	}

	/**
	 * Validate user data
	 *
	 * @param $data
	 *
	 * @return array|bool[]
	 */
	public function validate_user_data( $data ) {

		// validate membership_start date
		if ( $data['start_date'] < date( 'Y-m-d' ) ) {
			return array(
				'status'  => false,
				'key'     => 'start_date',
				'message' => __( 'Password does not match.', 'user-registration' ),
			);
		}
		// validate coupon if applied
		if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) && ur_check_module_activation( 'coupon' ) ) {
			$coupon_details = ur_get_coupon_details( $data['coupon'] );
			if ( empty( $coupon_details ) ) {
				return array(
					'status'  => false,
					'key'     => 'coupon',
					'message' => __( 'Coupon does not exist.', 'user-registration' ),
				);
			}
			if ( isset( $coupon_details['coupon_status'] ) && ! $coupon_details['coupon_status'] ) {
				return array(
					'status'  => false,
					'key'     => 'coupon',
					'message' => __( 'Coupon is Inactive.', 'user-registration' ),
				);
			}
			if ( isset( $coupon_details['coupon_end_date'] ) && $coupon_details['coupon_end_date'] < date( 'Y-m-d' ) ) {
				return array(
					'status'  => false,
					'key'     => 'coupon',
					'message' => __( 'Coupon expired.', 'user-registration' ),
				);
			}
			if ( 'membership' !== $coupon_details['coupon_for'] ) {
				return array(
					'status'  => false,
					'key'     => 'coupon',
					'message' => __( 'Invalid coupon type.', 'user-registration' ),
				);
			}
			$coupon_membership = json_decode( $coupon_details['coupon_membership'], true );
			if ( ! in_array( $data['membership'], $coupon_membership ) ) {
				return array(
					'status'  => false,
					'key'     => 'coupon',
					'message' => __( 'Coupon cannot be applied for the selected membership.', 'user-registration' ),
				);
			}
			if ( $coupon_details['coupon_start_date'] > date( 'Y-m-d' ) ) {
				return array(
					'status'  => false,
					'key'     => 'coupon',
					'message' => __( 'Coupon is not valid until ' . date_i18n( get_option( 'date_format' ), strtotime( $coupon_details['coupon_start_date'] ) ) . '.', 'user-registration' ),
				);
			}
			$membership_details = $this->membership_repository->get_single_membership_by_ID( absint( $data['membership'] ) );
			$membership_meta    = json_decode( $membership_details['meta_value'], true );
			if ( 'free' === $membership_meta['type'] ) {
				return array(
					'status'  => false,
					'key'     => 'coupon',
					'message' => __( 'Invalid membership type (Free).', 'user-registration' ),
				);
			}
		}

		return array(
			'status' => true,
		);
	}

	/**
	 * prepare members data and sanitize at the same time
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function prepare_members_data( $data ) {
		$membership_details = $this->membership_repository->get_single_membership_by_ID( absint( $data['membership'] ) );
		$membership_meta    = json_decode( $membership_details['meta_value'], true );
		$role               = isset( $membership_meta['role'] ) ? $membership_meta['role'] : 'subscriber';


		$coupon_details = array();
		if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) && ur_check_module_activation( 'coupon' ) ) {
			$coupon_details = ur_get_coupon_details( sanitize_text_field( $data['coupon'] ) );
		}

		$user_data = array(
			'user_login'    => ! empty( $data['username'] ) ? sanitize_text_field( $data['username'] ) : '',
			'user_email'    => ! empty( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'user_pass'     => ! empty( $data['password'] ) ? $data['password'] : '',
			'user_nicename' => ( ! empty( $data['firstname'] ) && ! empty( $data['lastname'] ) ) ? sanitize_text_field( $data['firstname'] ) . ' ' . sanitize_text_field( $data['lastname'] ) : '',
			'display_name'  => ! empty( $data['username'] ) ? sanitize_text_field( $data['username'] ) : '',
			'first_name'    => ! empty( $data['firstname'] ) ? sanitize_text_field( $data['firstname'] ) : '',
			'last_name'     => ! empty( $data['lastname'] ) ? sanitize_text_field( $data['lastname'] ) : '',
			'user_status'   => isset( $data['member_status'] ) ? absint( $data['member_status'] ) : 1,
		);

		$membership_data = array(
			'membership'     => absint( $data['membership'] ),
			'start_date'     => date( 'Y-m-d', strtotime( $data['start_date'] ) ),
			'payment_method' => sanitize_text_field( $data['payment_method'] ?? '' ),
		);

		return array(
			'role'            => sanitize_text_field( $role ),
			'membership_data' => $membership_data,
			'coupon_data'     => $coupon_details,
			'user_data'       => $user_data
		);
	}

	public function update_user_meta( $data, $new_user_id ) {
		$user = new \WP_User( $new_user_id );
		update_user_meta( $new_user_id, 'ur_registration_source', 'membership' );
		$user->set_role( $data['role'] );
		if ( ! empty( $data['coupon_data'] ) ) {
			update_user_meta( $new_user_id, 'ur_coupon_discount_type', $data['coupon_data']['coupon_discount_type'] );
			update_user_meta( $new_user_id, 'ur_coupon_discount', $data['coupon_data']['coupon_discount'] );
		}

		return $user;

	}

	/**
	 * login_member
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function login_member( $user_id, $check_just_created ) {
		$is_just_created = 'no';
		if ( $check_just_created ) {
			$is_just_created = get_user_meta( $user_id, 'urm_user_just_created', true );
		}

		if ( "yes" === $is_just_created ) {
			delete_user_meta( $user_id, 'urm_user_just_created');
			wp_clear_auth_cookie();
			$remember = apply_filters( 'user_registration_autologin_remember_user', false );
			wp_set_auth_cookie( $user_id, $remember );

			return true;
		} else {
			return false;
		}

	}

}
