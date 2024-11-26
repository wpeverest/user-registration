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
		// validate username
		if ( username_exists( $data['username'] ) ) {
			return array(
				'status'  => false,
				'key'     => 'name',
				'message' => esc_html__( 'Username: ' . $data['username'] . ' is already taken.', 'user-registration' ),
			);
		}
		// validate email
		if ( email_exists( $data['email'] ) ) {
			return array(
				'status'  => false,
				'key'     => 'name',
				'message' => __( 'Email: ' . $data['email'] . ' is already taken.', 'user-registration' ),
			);
		}

		if ( ! preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).*$/', $data['password'] ) ) {
			return array(
				'status'  => false,
				'key'     => 'password',
				'message' => __( 'Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character', 'user-registration' ),
			);
		}
		// validate password
		if ( $data['password'] !== $data['confirm_password'] ) {
			if ( empty( $data['confirm_password'] ) ) {
				return array(
					'status'  => false,
					'message' => __( 'Confirm password field cannot be empty.', 'user-registration' ),
				);
			}

			return array(
				'status'  => false,
				'key'     => 'password',
				'message' => __( 'Password does not match.', 'user-registration' ),
			);
		}

		// validate membership_start date
		if ( $data['start_date'] < date( 'Y-m-d' ) ) {
			return array(
				'status'  => false,
				'key'     => 'start_date',
				'message' => __( 'Password does not match.', 'user-registration' ),
			);
		}
		// validate coupon if applied
		if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) && ur_pro_is_coupons_addon_activated() ) {
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

		if(!isset($data['role'])) {
			$membership_details = $this->membership_repository->get_single_membership_by_ID( absint( $data['membership'] ) );
			$membership_meta    = json_decode( $membership_details['meta_value'], true );
			$data['role'] = $membership_meta['role'] ?? 'subscriber';
		}
		$role           = $data['role'] ?? 'subscriber';

		$coupon_details = array();
		if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) && ur_pro_is_coupons_addon_activated() ) {
			$coupon_details = ur_get_coupon_details( sanitize_text_field( $data['coupon'] ) );
		}

		$user_data       = array(
			'user_login'    => sanitize_text_field( $data['username'] ),
			'user_email'    => sanitize_email( $data['email'] ),
			'user_pass'     => $data['password'],
			'user_nicename' => sanitize_text_field( $data['firstname'] ) . ' ' . sanitize_text_field( $data['lastname'] ),
			'display_name'  => sanitize_text_field( $data['username'] ),
			'first_name'    => sanitize_text_field( $data['firstname'] ),
			'last_name'     => sanitize_text_field( $data['lastname'] ),
			'user_status'   => isset($data['member_status']) ? absint( $data['member_status'] ) : 1,
		);

		$membership_data = array(
			'membership'     => absint( $data['membership'] ),
			'start_date'     => date( 'Y-m-d', strtotime( $data['start_date'] ) ),
			'payment_method' => sanitize_text_field( $data['payment_method'] ?? '' ) ,
		);

		return array(
			'role'            => sanitize_text_field( $role ),
			'user_data'       => $user_data,
			'membership_data' => $membership_data,
			'coupon_data'     => $coupon_details,
		);
	}
}
