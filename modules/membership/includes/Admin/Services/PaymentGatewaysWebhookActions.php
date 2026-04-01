<?php

namespace WPEverest\URMembership\Admin\Services;

use WP_REST_Request;
use WP_REST_Response;
use WPEverest\URMembership\Admin\Services\Paypal\NewPaypalService;
use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;

/**
 * PaymentGatewaysWebhookActions
 */
class PaymentGatewaysWebhookActions {

	/**
	 * @var NewPaypalService
	 */
	protected $paypal_service;

	/**
	 * @var PaypalService
	 */
	protected $legacy_paypal_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->paypal_service        = new NewPaypalService();
		$this->legacy_paypal_service = new PaypalService();

		$this->init();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'handle_paypal_redirect_response' ), 1 );
		add_action( 'init', array( $this, 'handle_membership_paypal_ipn' ), 1 );

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'user-registration',
			'/stripe-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_stripe_webhook' ),
				'permission_callback' => array( $this, 'verify_stripe_webhook_signature' ),
			)
		);

		register_rest_route(
			'user-registration',
			'/paypal-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_paypal_webhook' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Handle PayPal buyer return redirect.
	 *
	 * Supports:
	 * - REST one-time order return (token)
	 * - REST subscription return (subscription_id)
	 * - legacy PayerID param if present
	 *
	 * @return void
	 */
	public function handle_paypal_redirect_response() {
		if ( ! isset( $_GET['ur-membership-return'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$get_params = base64_decode( sanitize_text_field( wp_unslash( $_GET['ur-membership-return'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$payer_id   = isset( $_GET['PayerID'] ) ? sanitize_text_field( wp_unslash( $_GET['PayerID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		PaymentGatewayLogging::log_webhook_received(
			'paypal',
			'PayPal redirect received in PaymentGatewaysWebhookActions'
			. "\n" . wp_json_encode(
				array(
					'has_payer_id'    => ! empty( $payer_id ),
					'has_token'       => isset( $_GET['token'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'has_subscription_id' => isset( $_GET['subscription_id'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				),
				JSON_PRETTY_PRINT
			)
		);

		$this->paypal_service->handle_paypal_redirect_response( $get_params, $payer_id );
	}

	/**
	 * Handle legacy PayPal IPN callback.
	 *
	 * This is kept only for backward compatibility with old PayPal Standard/IPN flow.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function handle_membership_paypal_ipn() {
		if (
			! isset( $_GET['ur-membership-listener'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'IPN' !== sanitize_text_field( wp_unslash( $_GET['ur-membership-listener'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			return;
		}

		$data = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		PaymentGatewayLogging::log_webhook_received(
			'paypal',
			'Legacy PayPal IPN listener triggered',
			array(
				'listener' => 'IPN',
			)
		);

		$this->legacy_paypal_service->handle_membership_paypal_ipn( $data );
	}

	/**
	 * Verify Stripe webhook signature.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function verify_stripe_webhook_signature( WP_REST_Request $request ) {
		PaymentGatewayLogging::log_webhook_received(
			'stripe',
			'Stripe webhook received, starting signature verification.',
			array()
		);

		$body = $request->get_body();

		$stripe_signature = $request->get_header( 'stripe-signature' );
		if ( empty( $stripe_signature ) ) {
			$stripe_signature = $request->get_header( 'stripe_signature' );
		}

		$legacy      = apply_filters( 'user_registration_stripe_webhook_secret', get_option( 'user_registration_stripe_webhook_secret', '' ) );
		$secret_test = get_option( 'user_registration_stripe_webhook_secret_test', '' );
		$secret_live = get_option( 'user_registration_stripe_webhook_secret_live', '' );
		$candidates  = array_filter( array( $legacy, $secret_test, $secret_live ) );

		if ( empty( $candidates ) ) {
			PaymentGatewayLogging::log_general(
				'stripe',
				'Missing Stripe webhook secret, skipping verification.',
				'notice'
			);
			return true;
		}

		if ( empty( $stripe_signature ) ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Stripe webhook verification failed.',
				array(
					'error_code'    => 'MISSING_STRIPE_SIGNATURE',
					'error_message' => 'Stripe-Signature header not found',
				)
			);
			return false;
		}

		new StripeService();

		foreach ( $candidates as $secret ) {
			try {
				\Stripe\Webhook::constructEvent( $body, $stripe_signature, $secret );

				PaymentGatewayLogging::log_general(
					'stripe',
					'Stripe webhook signature verification successful.',
					'success'
				);

				return true;
			} catch ( \Exception $e ) {
				continue;
			}
		}

		PaymentGatewayLogging::log_error(
			'stripe',
			'Stripe webhook verification failed.',
			array(
				'error_code'    => 'SIGNATURE_VERIFICATION_FAILED',
				'error_message' => 'No matching signing secret',
			)
		);

		return false;
	}

	/**
	 * Handle Stripe webhook.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function handle_stripe_webhook( WP_REST_Request $request ) {
		$body = $request->get_body();

		if ( empty( $body ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Empty Stripe webhook body.',
				),
				400
			);
		}

		$event = json_decode( $body, true );

		if ( empty( $event ) || ! is_array( $event ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Invalid Stripe webhook payload.',
				),
				400
			);
		}

		$subscription_id = isset( $event['data']['object']['subscription'] ) ? $event['data']['object']['subscription'] : null;

		PaymentGatewayLogging::log_webhook_received(
			'stripe',
			'Stripe webhook payload parsed successfully.',
			array(
				'event_type'      => isset( $event['type'] ) ? $event['type'] : '',
				'subscription_id' => $subscription_id,
			)
		);

		$stripe_service = new StripeService();
		$stripe_service->handle_webhook( $event, $subscription_id );

		return new WP_REST_Response(
			array(
				'success' => true,
			),
			200
		);
	}

	/**
	 * Handle PayPal REST webhook.
	 *
	 * Webhook verification is intentionally not required here so that
	 * PayPal REST setup stays simple. This route acts as an optional sync layer.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function handle_paypal_webhook( WP_REST_Request $request ) {
		$body = $request->get_body();

		if ( empty( $body ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Empty PayPal webhook body.',
				),
				400
			);
		}

		$event = json_decode( $body, true );

		if ( empty( $event ) || ! is_array( $event ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Invalid PayPal webhook payload.',
				),
				400
			);
		}

		PaymentGatewayLogging::log_webhook_received(
			'paypal',
			'PayPal REST webhook payload parsed successfully.',
			array(
				'event_type'   => isset( $event['event_type'] ) ? $event['event_type'] : '',
				'resource_id'  => isset( $event['resource']['id'] ) ? $event['resource']['id'] : '',
				'verification' => 'not_required',
			)
		);

		$result = $this->paypal_service->handle_webhook_event( $event );

		if ( false === $result ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'PayPal webhook could not be processed.',
				),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
			),
			200
		);
	}
}
