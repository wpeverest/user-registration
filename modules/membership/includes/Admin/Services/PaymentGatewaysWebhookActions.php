<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;

/**
 * PaypalActions
 */
class PaymentGatewaysWebhookActions {
	protected $paypal_service;

	/**
	 *
	 */
	public function __construct() {
		$this->init();
		$this->paypal_service = new PaypalService();
	}

	/**
	 * init
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'handle_paypal_redirect_response' ), 1 );
		add_action( 'init', array( $this, 'handle_membership_paypal_ipn' ) );

		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'user-registration',
					'/stripe-webhook',
					array(
						'methods'             => 'POST',
						'callback'            => array( $this, 'handle_stripe_webhook' ),
						'permission_callback' => array( $this, 'verify_stripe_webhook_signature' ),
					)
				);
			}
		);
	}

	/**
	 * Handle paypal redirect response
	 *
	 * @return void
	 */
	public function handle_paypal_redirect_response() {
		if ( ! isset( $_GET['ur-membership-return'] ) ) {
			return;
		}
		$get_params = base64_decode( $_GET['ur-membership-return'] );

		$payer_id = $_GET['PayerID'] ?? '';

		$this->paypal_service->handle_paypal_redirect_response( $get_params, $payer_id );
	}
	/**
	 * Handle Membership PayPal ipn
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function handle_membership_paypal_ipn() {

		if ( ! isset( $_GET['ur-membership-listener'] ) || 'IPN' !== $_GET['ur-membership-listener'] ) {
			return;
		}

		$data = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$this->paypal_service->handle_membership_paypal_ipn( $data );
	}

	public function verify_stripe_webhook_signature( \WP_REST_Request $request ) {

		PaymentGatewayLogging::log_webhook_received(
			'stripe',
			'Stripe webhook received, starting signature verification.',
			array()
		);

		$stripe_signature = $request->get_header( 'stripe_signature' );

		$body = $request->get_body();

		new StripeService();
		$webhook_secret = apply_filters( 'user_registration_stripe_webhook_secret', get_option( 'user_registration_stripe_webhook_secret', false ) );

		if ( empty( $webhook_secret ) ) {
			PaymentGatewayLogging::log_general(
				'stripe',
				'Missing webhook secret, skipping verification.',
				'notice'
			);
			return true;
		}
		try {
			\Stripe\Webhook::constructEvent(
				$body,
				$stripe_signature,
				$webhook_secret,
			);
		} catch ( \Exception $e ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Stripe webhook verification failed.',
				array(
					'error_code'    => 'SIGNATURE_VERIFICATION_FAILED',
					'error_message' => $e->getMessage(),
				)
			);
		}
		PaymentGatewayLogging::log_general(
			'stripe',
			'Webhook signature verification successful.',
			'success'
		);
		return true;
	}
	/**
	 * handle_stripe_webhook
	 *
	 * @return void
	 */
	public function handle_stripe_webhook( \WP_REST_Request $request ) {

		$body = $request->get_body();

		$stripe_service = new StripeService();

		$event = json_decode( $body, true );

		$subscription_id = $event['data']['object']['subscription'];

		if ( empty( $body ) && '' == $subscription_id ) {
			return;
		}

		$stripe_service->handle_webhook( $event, $subscription_id );
	}
}
