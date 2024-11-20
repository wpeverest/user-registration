<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;

class MembershipService {
	public function prepare_membership_data( $memberships ) {
		foreach ( $memberships as $key => $membership ) {
			$membership_post_content = json_decode( wp_unslash( $membership['post_content'] ), true );
			if ( ! $membership_post_content['status'] ) {
				unset( $memberships[ $key ] );
				continue;
			}
			$memberships[ $key ]['post_content'] = $membership_post_content;
			if ( isset( $membership['meta_value'] ) ) {
				$memberships[ $key ]['meta_value'] = json_decode( wp_unslash( $membership['meta_value'] ), true );
			}
		}

		return array_values( $memberships );
	}

	/**
	 * @return array[]
	 */
	public function prepare_membership_post_data( $data ) {
		$membership_id = ! empty( $data['post_data']['ID'] ) ? absint( $data['post_data']['ID'] ) : '';
		$validate_data = $this->validate_membership_data( $data );

		if ( ! $validate_data['status'] ) {
			return $validate_data;
		}
		$post_meta_data = $this->sanitize_membership_meta_data( $data['post_meta_data'] , $membership_id );

		return array(
			'post_data'      => array(
				'ID'             => $membership_id,
				'post_title'     => sanitize_text_field( $data['post_data']['name'] ),
				'post_content'   => wp_json_encode(
					array(
						'description' => sanitize_text_field( $data['post_data']['description'] ),
						'type'        => sanitize_text_field( $data['post_meta_data']['type'] ),
						'status'      => wp_validate_boolean( $data['post_data']['status'] ),
					)
				),
				'post_type'      => 'ur_membership',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			'post_meta_data' => array(
				'meta_key'   => 'ur_membership',
				'meta_value' => wp_json_encode( $post_meta_data ),
			),

		);
	}

	/**
	 * Sanitize membership meta data
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function sanitize_membership_meta_data( $data ,$membership_id ) {

		$product_id = "";
		$price_id = "";
		if(! empty($membership_id)) {
			$membership_meta = get_post_meta($membership_id,'ur_membership');
			$membership_meta = json_decode($membership_meta[0], true);

			if(isset($membership_meta["payment_gateways"]["stripe"]) && "on" == $membership_meta["payment_gateways"]["stripe"]["status"]) {
				$product_id = $membership_meta["payment_gateways"]["stripe"]["product_id"] ?? "";
				$price_id = $membership_meta["payment_gateways"]["stripe"]["price_id"] ?? "";
			}

		}

		// Todo: make this dynamic in future
		$data['type'] = sanitize_text_field( $data['type'] );
		if ( isset( $data['subscription'] ) ) {
			$data['subscription']['value']    = absint( $data['subscription']['value'] );
			$data['subscription']['duration'] = sanitize_text_field( $data['subscription']['duration'] );
			$data['trial_status']             = sanitize_text_field( $data['trial_status'] );
			if ( 'on' === $data['trial_status'] ) {
				$data['trial_data']['value']    = absint( $data['trial_data']['value'] );
				$data['trial_data']['duration'] = sanitize_text_field( $data['trial_data']['duration'] );
			}
		}
		$data['cancel_subscription'] = sanitize_text_field( $data['cancel_subscription'] );

		$data['amount'] = absint( $data['amount'] ?? 0 );
		if ( isset( $data['payment_gateways'] ) ) {
			if ( isset( $data['payment_gateways']['paypal'] ) && 'on' === $data['payment_gateways']['paypal']['status'] ) {
				$data['payment_gateways']['paypal']['status']     = sanitize_text_field( $data['payment_gateways']['paypal']['status'] );
				$data['payment_gateways']['paypal']['email']      = sanitize_email( $data['payment_gateways']['paypal']['email'] );
				$data['payment_gateways']['paypal']['mode']       = sanitize_text_field( $data['payment_gateways']['paypal']['mode'] );
				$data['payment_gateways']['paypal']['cancel_url'] = esc_url( $data['payment_gateways']['paypal']['cancel_url'] );
				$data['payment_gateways']['paypal']['return_url'] = esc_url( $data['payment_gateways']['paypal']['return_url'] );
			}
			if ( isset( $data['payment_gateways']['bank'] ) && 'on' === $data['payment_gateways']['bank']['status'] ) {
				$data['payment_gateways']['bank']['status'] = sanitize_text_field( $data['payment_gateways']['bank']['status'] );
			}
			if ( isset( $data['payment_gateways']['stripe'] ) && 'on' === $data['payment_gateways']['stripe']['status'] ) {
				$data['payment_gateways']['stripe']['status'] = sanitize_text_field( $data['payment_gateways']['stripe']['status'] );
				$data['payment_gateways']['stripe']['product_id'] = sanitize_text_field( $product_id );
				$data['payment_gateways']['stripe']['price_id'] = sanitize_text_field( $price_id );
			}
		}

		return $data;
	}

	/**
	 * Validate Membership Data
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function validate_membership_data( $data ) {
		$result = array(
			'status' => true,
		);
		//		payment gateway validation:stripe
		if ( isset( $data['post_meta_data']['payment_gateways']['stripe'] ) && "on" === $data['post_meta_data']['payment_gateways']['stripe']['status'] ) {
			if ( is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) ) {
				$mode            = get_option( 'user_registration_stripe_test_mode', false ) ? 'test' : 'live';
				$publishable_key = get_option( sprintf( 'user_registration_stripe_%s_publishable_key', $mode ) );
				$secret_key      = get_option( sprintf( 'user_registration_stripe_%s_secret_key', $mode ) );
				$stripe_details  = $membership_details['payment_gateways']['stripe'] ?? '';

				if ( empty( $secret_key ) || empty( $publishable_key ) ) {
					$result['status']  = false;
					$result['message'] = esc_html__( "Incomplete Stripe Gateway setup.", "user-registration" );
				}

			} else {
				$result['status']  = false;
				$result['message'] = esc_html__( "Stripe gateway is not enabled.", "user-registration" );
			}
		}

		return $result;
	}

	public function get_membership_details( $membership_id ) {
		$membership_repository = new MembershipRepository();
		$membership                     = $membership_repository->get_single_membership_by_ID( $membership_id );

		return wp_unslash( json_decode( $membership['meta_value'], true ) );
	}
}
