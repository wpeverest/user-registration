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
	public function validate_user_data( $data, $is_edit = false ) {

		// validate membership_start date
		if ( $data['start_date'] < date( 'Y-m-d' ) && ! $is_edit ) {
			return array(
				'status'  => false,
				'key'     => 'start_date',
				'message' => __( 'Please select a start date that is today or later', 'user-registration' ),
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
		$response         = array();
		$response['role'] = isset( $data['role'] ) ? sanitize_text_field( $data['role'] ) : 'subscriber';

		if ( isset( $data['tax_rate'] ) && ! empty( $data['tax_rate'] ) ) {
			$tax_details = array(
				'tax_rate'       		 => floatval( $data['tax_rate'] ),
				'tax_calculation_method' =>  ur_string_to_bool( sanitize_text_field( $data['tax_calculation_method'] ) ),
				);
			$response['tax_data'] = $tax_details;
		}

		if ( isset( $data['switched_currency'] ) && ! empty( $data['switched_currency'] ) ) {
			$local_currency_details             = array(
				'switched_currency' => sanitize_text_field( $data['switched_currency'] ),
				'urm_zone_id'       => ! empty( $data['urm_zone_id'] ) ? $data['urm_zone_id'] : '',
			);
			$response['local_currency_details'] = $local_currency_details;
		}

		if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) && ur_check_module_activation( 'coupon' ) ) {
			$response['coupon_data'] = ur_get_coupon_details( sanitize_text_field( $data['coupon'] ) );
		}

		$response['user_data'] = array(
			'user_login'    => ! empty( $data['username'] ) ? sanitize_text_field( $data['username'] ) : '',
			'user_email'    => ! empty( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'user_pass'     => ! empty( $data['password'] ) ? $data['password'] : '',
			'user_nicename' => ( ! empty( $data['firstname'] ) && ! empty( $data['lastname'] ) ) ? sanitize_text_field( $data['firstname'] ) . ' ' . sanitize_text_field( $data['lastname'] ) : '',
			'display_name'  => ! empty( $data['username'] ) ? sanitize_text_field( $data['username'] ) : '',
			'first_name'    => ! empty( $data['firstname'] ) ? sanitize_text_field( $data['firstname'] ) : '',
			'last_name'     => ! empty( $data['lastname'] ) ? sanitize_text_field( $data['lastname'] ) : '',
			'user_status'   => isset( $data['member_status'] ) ? absint( $data['member_status'] ) : 1,
		);

		if ( isset( $data['membership'] ) ) {
			$membership_details          = $this->membership_repository->get_single_membership_by_ID( absint( $data['membership'] ) );
			$membership_meta             = json_decode( $membership_details['meta_value'], true );
			$response['role']            = isset( $membership_meta['role'] ) ? sanitize_text_field( $membership_meta['role'] ) : $response['role'];
			$response['membership_data'] = array(
				'membership'     => absint( $data['membership'] ),
				'start_date'     => date( 'Y-m-d', strtotime( $data['start_date'] ) ),
				'payment_method' => sanitize_text_field( $data['payment_method'] ?? '' ),
			);

			if ( isset( $data['is_purchasing_multiple'] ) ) {
				$response['is_purchasing_multiple'] = $data['is_purchasing_multiple'];
			}
		}

		$team       = array();
		$tier       = array();
		$team_seats = '';
		if ( isset( $data['team'] ) ) {
			if ( ! isset( $membership_meta ) ) {
				if ( ! isset( $data['membership'] ) ) {
					throw new \Exception( __( 'Membership is required for team pricing.', 'user-registration' ) );
				}
				$membership_details = $this->membership_repository->get_single_membership_by_ID( absint( $data['membership'] ) );
				$membership_meta    = json_decode( $membership_details['meta_value'], true );
			}
			$team_index = absint( $data['team'] );
			if ( ! isset( $membership_meta['team_pricing'][ $team_index ] ) ) {
				throw new \Exception( __( 'Invalid team pricing selection.', 'user-registration' ) );
			}

			$team = $membership_meta['team_pricing'][ $team_index ];
			if ( isset( $data['tier'] ) ) {
				$tier_index = absint( $data['tier'] );
				if ( ! isset( $team['tiers'] ) || ! is_array( $team['tiers'] ) || ! isset( $team['tiers'][ $tier_index ] ) ) {
					throw new \Exception( __( 'Invalid tier selection.', 'user-registration' ) );
				}

				$tier = $team['tiers'][ $tier_index ];

				if ( isset( $data['no_of_seats'] ) && ! empty( $data['no_of_seats'] ) ) {
					$no_of_seats = absint( $data['no_of_seats'] );
					$tier_from   = isset( $tier['tier_from'] ) ? absint( $tier['tier_from'] ) : 0;
					$tier_to     = isset( $tier['tier_to'] ) ? absint( $tier['tier_to'] ) : 0;

					if ( $no_of_seats < $tier_from || $no_of_seats > $tier_to ) {
						throw new \Exception(
							sprintf(
								__( 'Number of seats must be between %1$d and %2$d for the selected tier.', 'user-registration' ),
								esc_html( $tier_from ),
								esc_html( $tier_to )
							)
						);
					}
				}
			}
			if ( 'fixed' === $team['seat_model'] ) {
				$team_seats = isset( $team['team_size'] ) ? $team['team_size'] : '';
			} else {
				$no_of_seats = isset( $data['no_of_seats'] ) ? absint( $data['no_of_seats'] ) : '';
				if ( $no_of_seats >= 1 ) {
					$team_seats = $no_of_seats;
				}
			}
		}

		$response['team']       = $team;
		$response['tier']       = $tier;
		$response['team_seats'] = $team_seats;

		return $response;
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

		if ( 'yes' === $is_just_created ) {
			delete_user_meta( $user_id, 'urm_user_just_created' );
			wp_clear_auth_cookie();
			$remember = apply_filters( 'user_registration_autologin_remember_user', false );
			wp_set_auth_cookie( $user_id, $remember );

			return true;
		} else {
			return false;
		}
	}
}
