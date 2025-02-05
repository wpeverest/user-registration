<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;

class PaymentService {
	/**
	 * @var
	 */
	protected $payment_method, $membership, $member_email;

	/**
	 * @param $payment_method
	 * @param $membership
	 * @param $member_email
	 */
	public function __construct( $payment_method, $membership, $member_email ) {
		$this->payment_method = $payment_method;
		$this->membership     = $membership;
		$this->member_email   = $member_email;
	}

	/**
	 * Get Payment Data
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function get_payment_data( $data ) {
		$member_repo = new MembershipRepository();
		$membership  = $member_repo->get_single_membership_by_ID( $data['membership'] );

		$membership_meta = json_decode( wp_unslash( $membership['meta_value'] ), true );
		if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) ) {
			$membership_meta['coupon'] = $data['coupon'];
		}

		return $membership_meta;

	}

	/**
	 * Build return Response
	 *
	 * @param $response_data
	 *
	 * @return array|void
	 */
	public function build_response( $response_data ) {
		$payment_data = $this->get_payment_data( $response_data );

		switch ( $this->payment_method ) {
			case 'stripe':
				return $this->build_stripe_response( $payment_data, $response_data );
				break;
			case 'paypal':
				return $this->build_paypal_response( $payment_data, $response_data['subscription_id'], $response_data['member_id'] );
			default:
				return $this->build_direct_bank_response( $payment_data, $response_data );
				break;
		}
	}

	/**
	 * Build PayPal Response
	 *
	 * @param $data
	 * @param $subscription_id
	 * @param $member_id
	 *
	 * @return array
	 */
	public function build_paypal_response( $data, $subscription_id, $member_id ) {
		$paypal_service = new PaypalService();

		return array(
			'payment_url' => $paypal_service->build_url( $data, $this->membership, $this->member_email, $subscription_id, $member_id ),
		);

	}

	/**
	 * Build direct bank response
	 *
	 * @param $payment_data
	 * @param $response_data
	 *
	 * @return array
	 */
	public function build_direct_bank_response( $payment_data, $response_data ) {
		$bank_data = get_option( 'user_registration_global_bank_details',  $payment_data['payment_gateways']['bank']['content']);
		return array( 'data' => $bank_data );
	}

	public function build_stripe_response( $payment_data, $response_data ) {
		$stripe_service = new StripeService();

		return $stripe_service->process_stripe_payment($payment_data, $response_data );

	}




}
