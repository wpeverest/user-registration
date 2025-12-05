<?php

namespace WPEverest\URMembership\Admin\Services\Stripe;

use Stripe\Exception\ApiErrorException;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Admin\Services\OrderService;
use WPEverest\URMembership\Admin\Services\SubscriptionService;
use WPEverest\URMembership\Admin\Services\PaymentGatewayLogging;

class StripeService {
	protected $members_orders_repository, $members_subscription_repository, $membership_repository, $orders_repository;

	public function __construct() {

		// initialize necessary repos
		$this->members_orders_repository       = new MembersOrderRepository();
		$this->members_subscription_repository = new MembersSubscriptionRepository();
		$this->membership_repository           = new MembershipRepository();
		$this->orders_repository               = new OrdersRepository();

		if ( ! class_exists( 'Stripe\Stripe' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'lib/stripe-php/init.php';
		}
		$stripe_settings = self::get_stripe_settings();

		// Set your secret key
		\Stripe\Stripe::setApiKey( $stripe_settings['secret_key'] );
	}

	public function create_stripe_product_and_price( $post_data, $meta_data, $should_create_new_price ) {

		$products      = \Stripe\Product::all();
		$membership_id = $post_data['ID'];
		$currency      = get_option( 'user_registration_payment_currency', 'USD' );

		$product_exists = array_filter(
			$products->data,
			function ( $item, $key ) use ( $membership_id ) {
				if ( isset( $item['metadata']['membership_id'] ) ) {
					return $item['metadata']['membership_id'] == $membership_id;
				}
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( count( $product_exists ) > 0 ) { // product already exists, don't create new
			$product = array_values( $product_exists );
			$product = $product[0];
		} else {
			$product = \Stripe\Product::create(
				array(
					'name'        => $post_data['post_title'],
					'description' => ! empty( $meta_data['post_data'] ) && ( json_decode( $meta_data['post_data']['post_content'], true )['description'] ) ?? 'N/A',
					'metadata'    => array(
						'membership_id' => $membership_id, // Your custom ID
					),
				)
			);
		}
		$price = new \stdClass();

		try {

			$prices = \Stripe\Price::all(
				array(
					'product' => $product->id, // Replace with your product ID
				)
			);

			if ( ! empty( $prices->data ) && ! $should_create_new_price ) {
				$price = $prices->data[0];
			} elseif ( empty( $prices->data ) || $should_create_new_price ) {
				if ( 'JPY' === $currency ) {
					$amount = abs( $meta_data['amount'] );
				} else {
					$amount = abs( $meta_data['amount'] ) * 100;
				}
				$price_details = array(
					'unit_amount' => $amount, // New amount in cents
					'currency'    => strtolower( $currency ),
					'product'     => $product->id,
				);
				if ( 'subscription' === $meta_data['type'] ) {
					$price_details['recurring'] = array(
						'interval'       => $meta_data['subscription']['duration'],
						'interval_count' => $meta_data['subscription']['value'],
					);
				}

				$price = \Stripe\Price::create( $price_details );

				return array(
					'success' => true,
					'price'   => $price,
				);
			}
		} catch ( ApiErrorException $e ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Error creating Stripe price',
				array(
					'error_code'    => 'PRICE_CREATION_FAILED',
					'error_message' => $e->getMessage(),
				)
			);

			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}

		return array(
			'success' => true,
			'price'   => $price,
		);
	}

	public function process_stripe_payment( $payment_data, $response_data ) {
		$currency        = get_option( 'user_registration_payment_currency', 'USD' );
		$amount          = $payment_data['amount'];
		$user_email      = $response_data['email'];
		$member_id       = $response_data['member_id'];
		$username        = ! empty( $response_data['username'] ) ? $response_data['username'] : '';
		$membership_type = $payment_data['type'] ?? 'unknown';

		PaymentGatewayLogging::log_transaction_start(
			'stripe',
			'Processing Stripe payment',
			array(
				'member_id'       => $member_id,
				'amount'          => $amount,
				'currency'        => $currency,
				'membership_type' => $membership_type,
				'email'           => $user_email,
			)
		);

		$response = array(
			'type' => $payment_data['type'],
		);

		if ( isset( $payment_data['coupon'] ) && ! empty( $payment_data['coupon'] ) && ur_check_module_activation( 'coupon' ) ) {
			$coupon_details  = ur_get_coupon_details( $payment_data['coupon'] );
			$discount_amount = ( 'fixed' === $coupon_details['coupon_discount_type'] ) ? $coupon_details['coupon_discount'] : $amount * $coupon_details['coupon_discount'] / 100;
			$amount          = $amount - $discount_amount;
		}

		if ( 'JPY' === $currency ) {
			$amount = abs( $amount );
		} else {
			$amount = abs( $amount ) * 100;
		}

		if ( $amount < 1 ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Payment stopped - Amount less than minimum',
				array(
					'error_code' => 'AMOUNT_TOO_LOW',
					'amount'     => $amount,
					'member_id'  => $member_id,
				)
			);
			if ( empty( $payment_data['upgrade'] ) ) {
				wp_delete_user( absint( $member_id ) );
			}
			wp_send_json_error(
				array(
					'message' => __( 'Stripe Payment stopped, Total amount after discount cannot be less than One.', 'user-registration' ),
				)
			);
		}
		// Return if invalid amount.
		if ( ( empty( $amount ) || user_registration_sanitize_amount( 0, $currency ) == $amount ) ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Payment stopped - Invalid or empty amount',
				array(
					'error_code' => 'INVALID_AMOUNT',
					'amount'     => $amount,
					'member_id'  => $member_id,
				)
			);
			if ( empty( $payment_data['upgrade'] ) ) {
				wp_delete_user( absint( $member_id ) );
			}
			wp_send_json_error(
				array(
					'message' => __( 'Stripe Payment stopped, Invalid/Empty amount', 'user-registration' ),
				)
			);
		}

		try {
			PaymentGatewayLogging::log_api_request(
				'stripe',
				'Creating Stripe customer',
				array(
					'endpoint'  => 'Customer::create',
					'email'     => $user_email,
					'member_id' => $member_id,
				)
			);

			$customer                  = \Stripe\Customer::create(
				array(
					'email' => $user_email,
					'name'  => $username,
				)
			);
			$response['stripe_cus_id'] = $customer->id;

			PaymentGatewayLogging::log_api_response(
				'stripe',
				'Stripe customer created successfully',
				array(
					'customer_id' => $customer->id,
					'member_id'   => $member_id,
				)
			);

			if ( ! empty( $customer ) ) {
				update_user_meta( $member_id, 'ur_payment_customer', $customer->id );
			}
			if ( 'paid' === $payment_data['type'] ) {
				PaymentGatewayLogging::log_api_request(
					'stripe',
					'Creating payment intent',
					array(
						'endpoint'    => 'PaymentIntent::create',
						'amount'      => $amount,
						'currency'    => $currency,
						'customer_id' => $customer->id,
					)
				);

				$intent                    = \Stripe\PaymentIntent::create(
					array(
						'amount'               => $amount,
						'currency'             => $currency,
						'payment_method_types' => array( 'card' ),
						'customer'             => $customer->id,
					)
				);
				$response['client_secret'] = $intent->client_secret;

				PaymentGatewayLogging::log_transaction_success(
					'stripe',
					'Payment intent created successfully',
					array(
						'payment_intent_id' => $intent->id,
						'amount'            => $amount / 100,
						'currency'          => $currency,
						'member_id'         => $member_id,
						'membership_type'   => $membership_type,
					)
				);
			}

			return $response;
		} catch ( ApiErrorException $e ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Stripe API error occurred',
				array(
					'error_code'    => 'STRIPE_API_ERROR',
					'error_message' => $e->getMessage(),
					'member_id'     => $member_id,
				)
			);

			if ( empty( $payment_data['upgrade'] ) ) {
				wp_delete_user( absint( $member_id ) );
			}

			wp_send_json_error(
				array(
					'message' => __( 'Stripe Payment stopped, Incomplete Stripe setup.', 'user-registration' ),
				)
			);
		}
	}

	public function update_order( $data ) {
		$transaction_id = $data['payment_result']['paymentIntent']['id'] ?? '';
		$payment_status = sanitize_text_field( $data['payment_status'] );
		$member_id      = absint( $_POST['member_id'] );
		$is_upgrading   = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_upgrading', true ) );

		PaymentGatewayLogging::log_webhook_received(
			'stripe',
			'Stripe payment confirmation callback received',
			array(
				'webhook_type'   => 'payment_confirmation',
				'transaction_id' => $transaction_id,
				'payment_status' => $payment_status,
				'member_id'      => $member_id,
				'is_upgrade'     => $is_upgrading,
			)
		);

		$response = array(
			'status' => true,
		);

		$latest_order = $this->members_orders_repository->get_member_orders( $member_id );

		if ( empty( $latest_order ) ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Order not found for member',
				array(
					'error_code' => 'ORDER_NOT_FOUND',
					'member_id'  => $member_id,
				)
			);
			$response['status']  = false;
			$response['message'] = __( 'Order not found for  ' . $member_id, 'user-registration' );

			return $response;
		}

		$membership       = $this->membership_repository->get_single_membership_by_ID( $latest_order['item_id'] );
		$membership_metas = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$membership_type  = $membership_metas['type'] ?? 'unknown';

		if ( 'failed' === $payment_status ) {
			$is_renewing = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_member_renewing', true ) );
			$error_msg   = __( 'Stripe Payment failed.', 'user-registration' );
			$error_msg   = $data['payment_result']['error']['message'] ?? $error_msg;

			PaymentGatewayLogging::log_transaction_failure(
				'stripe',
				'Stripe payment failed',
				array(
					'error_code'      => 'PAYMENT_FAILED',
					'error_message'   => $error_msg,
					'member_id'       => $member_id,
					'transaction_id'  => $transaction_id,
					'membership_type' => $membership_type,
				)
			);

			if ( ! $is_upgrading && ! $is_renewing ) {
				wp_delete_user( absint( $member_id ) );
				$this->members_orders_repository->delete_member_order( $member_id );
			}
			if ( $is_renewing ) {
				update_user_meta( $member_id, 'urm_is_member_renewing', false );
			}

			$response['message'] = $error_msg;
			$response['status']  = false;

			return $response;
		} elseif ( 'succeeded' === $payment_status ) {
			$member_order = $this->members_orders_repository->get_member_orders( $member_id );

			if ( 'completed' === $member_order['status'] ) {
				$response['message'] = $is_upgrading ? __( 'Membership upgraded successfully.', 'user-registration' ) : __( 'New member has been successfully created with successful stripe payment.', 'user-registration' );
				$response['status']  = true;

				return $response;
			}

			$membership                     = $this->membership_repository->get_single_membership_by_ID( $member_order['item_id'] );
			$membership_metas               = wp_unslash( json_decode( $membership['meta_value'], true ) );
			$membership_metas['post_title'] = $membership['post_title'];
			$member_subscription            = $this->members_subscription_repository->get_member_subscription( $member_id );
			$is_order_updated               = $this->members_orders_repository->update(
				$member_order['ID'],
				array(
					'status'         => 'completed',
					'transaction_id' => $transaction_id,
				)
			);

			if ( $is_order_updated && 'paid' === $member_order['order_type'] ) {
				$this->members_subscription_repository->update(
					$member_subscription['ID'],
					array(
						'status'     => 'active',
						'start_date' => date( 'Y-m-d 00:00:00' ),
					)
				);

				if ( $is_upgrading ) {
					PaymentGatewayLogging::log_general(
						'stripe',
						'Upgrade payment completed - Order status changed to completed',
						'success',
						array(
							'event_type'      => 'upgrade_payment_completed',
							'old_status'      => $member_order['status'] ?? 'unknown',
							'new_status'      => 'completed',
							'order_id'        => $member_order['ID'],
							'member_id'       => $member_id,
							'transaction_id'  => $transaction_id,
							'membership_type' => $membership_type,
						)
					);
				} else {
					PaymentGatewayLogging::log_general(
						'stripe',
						'Order status changed to completed',
						'success',
						array(
							'event_type'      => 'status_change',
							'old_status'      => $member_order['status'] ?? 'unknown',
							'new_status'      => 'completed',
							'order_id'        => $member_order['ID'],
							'member_id'       => $member_id,
							'transaction_id'  => $transaction_id,
							'membership_type' => $membership_type,
						)
					);
				}

				PaymentGatewayLogging::log_general(
					'stripe',
					'Subscription status changed to active',
					'success',
					array(
						'event_type'      => $is_upgrading ? 'upgrade_subscription_activated' : 'status_change',
						'old_status'      => $member_subscription['status'] ?? 'unknown',
						'new_status'      => 'active',
						'subscription_id' => $member_subscription['ID'],
						'member_id'       => $member_id,
						'membership_type' => $membership_type,
						'is_upgrade'      => $is_upgrading,
					)
				);
			}
			$response = $this->sendEmail( $member_order['ID'], $member_subscription, $membership_metas, $member_id, $response );
		}

		return $response;
	}

	public function create_subscription( $customer_id, $payment_method_id, $member_id, $is_upgrading ) {

		$member_order     = $this->members_orders_repository->get_member_orders( $member_id );
		$membership       = $this->membership_repository->get_single_membership_by_ID( $member_order['item_id'] );
		$membership_metas = wp_unslash( json_decode( $membership['meta_value'], true ) );

		$membership_metas['post_title'] = $membership['post_title'];
		$member_subscription            = $this->members_subscription_repository->get_member_subscription( $member_id );
		$is_automatic                   = 'automatic' === get_option( 'user_registration_renewal_behaviour', 'automatic' );
		$is_renewing                    = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_member_renewing', true ) );
		$membership_type                = $membership_metas['type'] ?? 'unknown';

		PaymentGatewayLogging::log_api_request(
			'stripe',
			'Creating Stripe subscription',
			array(
				'customer_id'       => $customer_id,
				'payment_method_id' => $payment_method_id,
				'member_id'         => $member_id,
				'is_upgrading'      => $is_upgrading,
				'membership_type'   => $membership_type,
			)
		);

		$response = array(
			'status' => false,
		);

		if ( empty( $member_subscription ) ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Subscription not found for member',
				array(
					'error_code' => 'SUBSCRIPTION_NOT_FOUND',
					'member_id'  => $member_id,
				)
			);

			return $response;
		}

		$stripe_product_details = $membership_metas['payment_gateways']['stripe'] ?? array();

		if ( ! isset( $stripe_product_details['price_id'] ) || ! isset( $stripe_product_details['product_id'] ) ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Stripe subscription failed - Price or product not configured',
				array(
					'error_code'      => 'MISSING_STRIPE_CONFIG',
					'member_id'       => $member_id,
					'membership_type' => $membership_type,
				)
			);

			$response['status']  = false;
			$response['message'] = __( 'Stripe subscription failed, price or product not found', 'user-registration' );

			return $response;
		}

		try {
			$customer       = \Stripe\Customer::retrieve( $customer_id );
			$payment_method = \Stripe\PaymentMethod::retrieve( $payment_method_id );
			$payment_method->attach(
				array(
					'customer' => $customer->id,
				)
			);

			\Stripe\Customer::update(
				$customer->id,
				array(
					'invoice_settings' => array(
						'default_payment_method' => $payment_method->id,
					),
				)
			);
			$subscription_details = array(
				'customer' => $customer->id,
				'items'    => array(
					array(
						'price' => $stripe_product_details['price_id'],
					),
				),
			);
			// handle trial period

			if ( isset( $membership_metas['trial_status'] ) && 'on' === $membership_metas['trial_status'] ) {
				$trail_period                      = strtotime( date( 'Y-m-d H:i:s', strtotime( '+' . $membership_metas['trial_data']['value'] . ' ' . $membership_metas['trial_data']['duration'] ) ) );
				$subscription_details['trial_end'] = $trail_period;
			} else {
				$subscription_details['expand'] = array( 'latest_invoice.payment_intent' );
			}

			// handle coupon section
			$order_detail = $this->orders_repository->get_order_detail( $member_order['ID'] );
			if ( ! empty( $order_detail['coupon'] ) ) {
				$coupon_details = ur_get_coupon_details( $order_detail['coupon'] );
				if ( ! empty( $coupon_details['stripe_coupon_id'] ) ) {
					$subscription_details['coupon'] = $coupon_details['stripe_coupon_id'];
				}
			}

			if ( $is_upgrading ) {
				PaymentGatewayLogging::log_general(
					'stripe',
					'Processing Stripe membership upgrade',
					'notice',
					array(
						'event_type'      => 'upgrade_processing',
						'member_id'       => $member_id,
						'membership_type' => $membership_type,
					)
				);

				$previous_subscription = json_decode( get_user_meta( $member_id, 'urm_previous_subscription_data', true ), true );

				if ( $previous_subscription ) {
					$previous_membership = $this->membership_repository->get_single_membership_by_ID( $previous_subscription['item_id'] );

					if ( ! empty( $previous_membership['meta_value'] ) ) {
						$previous_membership_metas = json_decode( wp_unslash( $previous_membership['meta_value'] ), true );

						if ( isset( $previous_membership_metas['type'], $previous_membership_metas['amount'] ) && $previous_membership_metas['type'] !== 'free' ) {
							$new_price         = isset( $membership_metas['amount'] ) ? $membership_metas['amount'] : 0;
							$current_price     = $previous_membership_metas['amount'];
							$first_month_price = $new_price - $current_price;

							if ( $new_price > $current_price ) {
								$discount_amount = $new_price - $first_month_price;
								$currency        = get_option( 'user_registration_payment_currency', 'USD' );
								$amount          = ( 'JPY' === $currency ) ? $discount_amount : $discount_amount * 100;

								PaymentGatewayLogging::log_general(
									'stripe',
									'Creating upgrade discount coupon',
									'debug',
									array(
										'event_type'      => 'upgrade_coupon_created',
										'member_id'       => $member_id,
										'old_price'       => $current_price,
										'new_price'       => $new_price,
										'discount_amount' => $discount_amount,
									)
								);

								$coupon = \Stripe\Coupon::create(
									array(
										'amount_off' => $amount,
										'currency'   => $currency,
										'duration'   => 'once',
										'name'       => 'UpgradeCoupon',
									)
								);

								$subscription_details['coupon'] = $coupon->id;
							}
						}
					}
				}

				$next_subscription = json_decode( get_user_meta( $member_id, 'urm_next_subscription_data', true ), true );
				if ( ! empty( $next_subscription['delayed_until'] ) ) {
					$subscription_details['trial_end'] = strtotime( $next_subscription['delayed_until'] );

					PaymentGatewayLogging::log_general(
						'stripe',
						'Upgrade scheduled with delayed start',
						'notice',
						array(
							'event_type'    => 'upgrade_delayed_start',
							'member_id'     => $member_id,
							'delayed_until' => $next_subscription['delayed_until'],
						)
					);
				} elseif (
					! empty( $previous_subscription['trial_end_date'] ) &&
					isset( $membership_metas['type'], $membership_metas['trial_status'] ) &&
					$membership_metas['type'] === 'subscription' &&
					$membership_metas['trial_status'] === 'on'
				) {
					$subscription_details['trial_end'] = strtotime( $previous_subscription['trial_end_date'] );

					PaymentGatewayLogging::log_general(
						'stripe',
						'Upgrade preserving trial period',
						'notice',
						array(
							'event_type'     => 'upgrade_trial_preserved',
							'member_id'      => $member_id,
							'trial_end_date' => $previous_subscription['trial_end_date'],
						)
					);
				}
			}

			if ( ( ! $is_automatic && ! $is_upgrading ) || $is_renewing ) {
				$value    = $membership_metas['subscription']['value'];
				$duration = $membership_metas['subscription']['duration'];

				$subscription_details['cancel_at'] = ( new \DateTime( "+ $value $duration" ) )->getTimestamp();
				if ( $is_renewing ) {
					$next_billing_date = new \DateTime( $member_subscription['next_billing_date'] );

					// reset next billing date to today if it is in the past.
					$today = new \DateTime( 'today' );
					if ( $next_billing_date < $today ) {
						$next_billing_date = $today;
					}

					$next_billing_date                 = $next_billing_date->modify( "+ $value $duration" )->getTimestamp();
					$subscription_details['cancel_at'] = $next_billing_date;
				}
			}

			PaymentGatewayLogging::log_debug(
				'stripe',
				'Creating subscription with details',
				array(
					'price_id'    => $stripe_product_details['price_id'],
					'customer_id' => $customer_id,
					'member_id'   => $member_id,
					'has_trial'   => isset( $subscription_details['trial_end'] ),
					'has_coupon'  => isset( $subscription_details['coupon'] ),
				)
			);

			$subscription        = \Stripe\Subscription::create( $subscription_details );
			$subscription_status = $subscription->status ?? '';

			PaymentGatewayLogging::log_api_response(
				'stripe',
				'Stripe subscription created',
				array(
					'subscription_id'     => $subscription->id,
					'subscription_status' => $subscription_status,
					'member_id'           => $member_id,
					'membership_type'     => $membership_type,
				)
			);

			if ( 'active' === $subscription_status || 'trialing' === $subscription_status ) {
				$this->members_orders_repository->update(
					$member_order['ID'],
					array(
						'status'         => 'completed',
						'transaction_id' => $subscription->id,
					)
				);
				switch ( $subscription_status ) {
					case 'trialing':
						$subscription_status = ( $is_upgrading && ! empty( $next_subscription['delayed_until'] ) ) ? 'active' : 'trial';
						break;
					default:
						break;
				}

				$this->members_subscription_repository->update(
					$member_order['subscription_id'],
					array(
						'subscription_id' => sanitize_text_field( $subscription->id ),
						'status'          => $subscription_status,
					)
				);

				PaymentGatewayLogging::log_transaction_success(
					'stripe',
					'Stripe subscription activated successfully',
					array(
						'subscription_id'     => $subscription->id,
						'subscription_status' => $subscription_status,
						'order_id'            => $member_order['ID'],
						'member_id'           => $member_id,
						'membership_type'     => $membership_type,
					)
				);

				$this->sendEmail( $member_order['ID'], $member_subscription, $membership_metas, $member_id, $response );
				$response['subscription'] = $subscription;
				$response['message']      = __( 'New member has been successfully created with successful stripe subscription.' );
				$response['status']       = true;
			}

			return $response;
		} catch ( \Exception $e ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Stripe subscription creation failed',
				array(
					'error_code'      => 'STRIPE_SUBSCRIPTION_ERROR',
					'error_message'   => $e->getMessage(),
					'member_id'       => $member_id,
					'membership_type' => $membership_type,
				)
			);

			if ( ! $is_upgrading && ! $is_renewing ) {
				wp_delete_user( absint( $member_id ) );
				$this->members_orders_repository->delete_member_order( $member_id );
				$customer = \Stripe\Customer::retrieve( $customer_id );
				$customer->delete();
			}

			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * get_stripe_settings
	 *
	 * @return array
	 */
	public static function get_stripe_settings() {
		$mode            = get_option( 'user_registration_stripe_test_mode', false ) ? 'test' : 'live';
		$publishable_key = get_option( sprintf( 'user_registration_stripe_%s_publishable_key', $mode ) );
		$secret_key      = get_option( sprintf( 'user_registration_stripe_%s_secret_key', $mode ) );

		return array(
			'mode'            => $mode,
			'publishable_key' => $publishable_key,
			'secret_key'      => $secret_key,
		);
	}

	/**
	 * Sends an email.
	 *
	 * @param int   $ID The ID of the email.
	 * @param mixed $member_subscription The subscription details of the member.
	 * @param array $membership_metas Metadata related to the membership.
	 * @param int   $member_id The ID of the member.
	 * @param array $response The response data.
	 *
	 * @return array The result of the email operation.
	 */
	public function sendEmail( int $ID, $member_subscription, array $membership_metas, int $member_id, array $response, $is_upgrading = false ): array {
		$email_service = new EmailService();
		$order_detail  = $this->orders_repository->get_order_detail( $ID );

		if ( ! empty( $order_detail['coupon'] ) ) {
			$order_detail['coupon_discount']      = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount', true );
			$order_detail['coupon_discount_type'] = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount_type', true );

		}
		$email_data = array(
			'subscription'     => $member_subscription,
			'order'            => $order_detail,
			'membership_metas' => $membership_metas,
			'member_id'        => $member_id,
			'membership'       => $member_subscription['item_id'],
		);

		$mail_send = $email_service->send_email( $email_data, 'payment_successful' );

		if ( ! $mail_send ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Payment email could not be sent',
				array(
					'error_code' => 'EMAIL_SEND_FAILED',
					'member_id'  => $member_id,
					'order_id'   => $ID,
				)
			);
		}

		return array(
			'message' => $is_upgrading ? __( 'Membership upgraded successfully.', 'user-registration' ) : __( 'New member has been successfully created with successful stripe payment.', 'user-registration' ),
			'status'  => true,
		);
	}

	/**
	 * sync_coupon
	 *
	 * @param $data
	 *
	 * @return array
	 * @throws ApiErrorException
	 */
	public function sync_coupon( $data, $old_coupon_data ) {

		$currency         = get_option( 'user_registration_payment_currency', 'USD' );
		$all_coupon       = \Stripe\Coupon::all();
		$coupon_exists    = false;
		$stripe_coupon_id = '';
		foreach ( $all_coupon->data as $key => $coupon ) {
			if ( $coupon->metadata->coupon === strtolower( $data['post_data']['post_content'] ) ) {
				$coupon_exists    = true;
				$stripe_coupon_id = $coupon->id;
			}
		}
		// delete coupon if new changes introduced
		if ( $coupon_exists && ( $old_coupon_data['coupon_discount'] !== $data['post_meta_data']['coupon_discount'] || $old_coupon_data['coupon_discount_type'] !== $data['post_meta_data']['post_meta_data'] ) ) {
			$coupon = \Stripe\Coupon::retrieve( $stripe_coupon_id );
			$coupon->delete();
		}
		$amount = $data['post_meta_data']['coupon_discount'];
		if ( 'JPY' === $currency ) {
			$amount = abs( $amount );
		} else {
			$amount = abs( $amount ) * 100;
		}

		$coupon_details = array(
			'currency' => strtolower( $currency ),
			'duration' => 'once',
			'metadata' => array(
				'coupon' => strtolower( $data['post_data']['post_content'] ),
			),
		);
		if ( 'fixed' === $data['post_meta_data']['coupon_discount_type'] ) {
			$coupon_details['amount_off'] = $amount;
		} elseif ( 'percent' === $data['post_meta_data']['coupon_discount_type'] ) {
			$coupon_details['percent_off'] = $data['post_meta_data']['coupon_discount'];
		}
		$coupon           = \Stripe\Coupon::create( $coupon_details );
		$stripe_coupon_id = $coupon->id;

		$data['post_meta_data']['stripe_coupon_id'] = $stripe_coupon_id;

		return $data;
	}

	public function cancel_subscription( $order, $subscription ) {

		$response = array(
			'status' => false,
		);

		if ( empty( $subscription['subscription_id'] ) ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Stripe subscription ID not found for cancellation',
				array(
					'error_code' => 'MISSING_SUBSCRIPTION_ID',
					'order_id'   => $order['ID'] ?? 'unknown',
				)
			);

			return $response;
		}

		PaymentGatewayLogging::log_general(
			'stripe',
			'Cancelling Stripe subscription',
			'notice',
			array(
				'event_type'      => 'cancellation_initiated',
				'subscription_id' => $subscription['subscription_id'],
				'order_id'        => $order['ID'] ?? 'unknown',
			)
		);

		$stripe_subscription = \Stripe\Subscription::retrieve( $subscription['subscription_id'] );
		if ( $stripe_subscription ) {
			$deleted_sub = \Stripe\Subscription::update(
				$subscription['subscription_id'],
				array( 'cancel_at_period_end' => true ),
			);
		}
		if ( isset( $deleted_sub['canceled_at'] ) && '' !== $deleted_sub['canceled_at'] ) {
			$response['status'] = true;

			PaymentGatewayLogging::log_general(
				'stripe',
				'Subscription cancelled successfully',
				'success',
				array(
					'event_type'           => 'cancellation_success',
					'subscription_id'      => $subscription['subscription_id'],
					'canceled_at'          => date( 'Y-m-d H:i:s', $deleted_sub['canceled_at'] ),
					'cancel_at_period_end' => $deleted_sub['cancel_at_period_end'] ?? false,
				)
			);
		}

		return $response;
	}
	/**
	 * Reactivates stripe subscription if it has been soft cancelled.
	 *
	 * @param $subscription_id Stripe's Subscription Id.
	 *
	 * @return $response array Response with status flag and message.
	 */
	public function reactivate_subscription( $subscription_id ) {
		$response     = array(
			'status' => false,
		);
		$subscription = \Stripe\Subscription::retrieve( $subscription_id );
		if ( isset( $subscription->id ) ) {
			if ( 'active' === $subscription->status ) {
				return array(
					'status'  => true,
					'message' => __( 'Subscription reactivated successfully.', 'user-registration' ),
				);
			} elseif ( 'canceled' !== $subscription->status && true === $subscription->cancel_at_period_end ) {
				$subscription = \Stripe\Subscription::update(
					$subscription_id,
					array( 'cancel_at_period_end' => false )
				);
				return array(
					'status'  => true,
					'message' => __( 'Subscription reactivated successfully.', 'user-registration' ),
				);
			} else {
				PaymentGatewayLogging::log_general(
					'stripe',
					'Subscription not reactivable',
					'info',
					array(
						'subscription_id'     => $subscription_id,
						'subscription_status' => $subscription->status,
						'event_type'          => 'reactivation_failed',
					)
				);
			}
		} else {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Subscription not found in Stripe',
				array(
					'error_code'      => 'SUBSCRIPTION_NOT_FOUND_IN_STRIPE',
					'subscription_id' => $subscription_id,
				)
			);
			wp_send_json_error(
				array(
					'message' => __( 'Error reactivating the stripe subscription.', 'user-registration' ),
				)
			);
		}

		return $response;
	}

	public function handle_webhook( $event, $subscription_id ) {
        // Verify that the event was sent by Stripe
		if( isset( $event[ 'id' ] ) ) {
            try {
				$event_id = sanitize_text_field( $event[ 'id' ] );
                $event = (array)\Stripe\Event::retrieve( $event_id );
            } catch( \Exception $e ) {
				die();
            }
        } else {
			die();
		}
		switch ( $event['type'] ) {
			case 'invoice.payment_succeeded':
				$this->handle_succeeded_invoice( $event, $subscription_id );
				break;
			default:
				break;
		}
	}

	public function handle_succeeded_invoice( $event, $subscription_id ) {
		PaymentGatewayLogging::log_webhook_received(
			'stripe',
			'Invoice payment succeeded webhook received',
			array(
				'webhook_type'    => 'invoice.payment_succeeded',
				'subscription_id' => $subscription_id,
				'event_id'        => $event['id'] ?? 'unknown',
			)
		);

		if ( null === $subscription_id ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Subscription ID is null in webhook',
				array(
					'error_code'   => 'NULL_SUBSCRIPTION_ID',
					'webhook_type' => 'invoice.payment_succeeded',
				)
			);

			return;
		}

		$current_subscription = $this->members_subscription_repository->get_membership_by_subscription_id( $subscription_id, true );

		if ( empty( $current_subscription ) ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Subscription not found for subscription ID',
				array(
					'error_code'      => 'SUBSCRIPTION_NOT_FOUND',
					'subscription_id' => $subscription_id,
				)
			);

			return;
		}
		$subscription_status = ( 'trial' == $current_subscription['status'] ) ? 'active' : $current_subscription['status'];

		$member_id     = $current_subscription['user_id'];
		$membership_id = $current_subscription['item_id'];
		$invoice_id    = $event['data']['object']['id'];

		$invoice_amount = $event['data']['object']['amount_due'];

		// create new order
		$order_info = array(
			'membership_data' => array(
				'membership'     => $membership_id,
				'payment_method' => 'stripe',
			),
		);

		$membership       = $this->membership_repository->get_single_membership_by_ID( $membership_id );
		$membership_metas = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$membership_type  = $membership_metas['type'] ?? 'unknown';

		$order_service                                = new OrderService();
		$order_repository                             = new OrdersRepository();
		$order_data                                   = $order_service->prepare_orders_data( $order_info, $member_id, $current_subscription );
		$order_data['orders_data']['status']          = 'completed';
		$order_data['orders_data']['user_id']         = $member_id;
		$order_data['orders_data']['created_by']      = $member_id;
		$order_data['orders_data']['trial_status']    = 'off';
		$order_data['orders_data']['notes']           = sanitize_text_field( esc_html__( 'Generated with stripe webhook', 'user-registration' ) );
		$order_data['orders_data']['total_amount']    = $membership_metas['amount'];
		$order_data['orders_data']['transaction_id']  = $invoice_id;
		$order_data['orders_data']['subscription_id'] = $current_subscription['sub_id'];

		$order_id = $order_repository->create( $order_data );

		PaymentGatewayLogging::log_general(
			'stripe',
			'New renewal order created via webhook',
			'notice',
			array(
				'event_type'      => 'renewal_order_created',
				'order_id'        => $order_id,
				'subscription_id' => $subscription_id,
				'invoice_id'      => $invoice_id,
				'amount'          => $membership_metas['amount'],
				'member_id'       => $member_id,
				'membership_type' => $membership_type,
			)
		);

		$next_billing_date = SubscriptionService::get_expiry_date( date( 'Y-m-d' ), $membership_metas['subscription']['duration'], $membership_metas['subscription']['value'] );

		$this->members_subscription_repository->update(
			$current_subscription['sub_id'],
			array(
				'start_date'        => date( 'Y-m-d 00:00:00' ),
				'next_billing_date' => $next_billing_date,
				'expiry_date'       => $next_billing_date,
				'status'            => $subscription_status,
			)
		);
		$is_renewing = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_member_renewing', true ) );

		if ( $is_renewing ) {
			$subscription_service = new SubscriptionService();
			$subscription_service->update_subscription_data_for_renewal( $current_subscription, $membership_metas );
		}

		PaymentGatewayLogging::log_webhook_processed(
			'stripe',
			'Subscription renewed successfully',
			array(
				'subscription_id'     => $subscription_id,
				'new_billing_date'    => $next_billing_date,
				'subscription_status' => $subscription_status,
				'member_id'           => $member_id,
				'membership_type'     => $membership_type,
			)
		);
	}

	public function validate_setup() {
		$stripe_settings = self::get_stripe_settings();

		return ( empty( $stripe_settings['publishable_key'] ) || empty( $stripe_settings['secret_key'] ) );
	}

	public function refund_subscription( $subscription, $refund_amount = null, $cancel_subscription = false, $cancel_at_end_period = false ) {
		try {
			$response        = array(
				'status' => false,
			);
			$subscription_id = $subscription['subscription_id'];
			if ( empty( $subscription_id ) ) {
				return $response;
			}

			$subscription = \Stripe\Subscription::retrieve( $subscription_id );

			$invoice_id = $subscription->latest_invoice;
			if ( ! $invoice_id ) {
				return $response;
			}
			$invoice = \Stripe\Invoice::retrieve( $invoice_id );

			$charge_id = $invoice->charge;
			if ( ! $charge_id ) {
				return $response;
			}

			$refundParams = array( 'charge' => $charge_id );
			if ( ! is_null( $refund_amount ) ) {
				$refundParams['amount'] = $refund_amount;
			}

			$refund = \Stripe\Refund::create( $refundParams );

			$cancel_result = null;
			if ( $cancel_subscription ) {
				\Stripe\Subscription::update(
					$subscription_id,
					array(
						'cancel_at_period_end' => $cancel_at_end_period,
					)
				);

				if ( ! $cancel_at_end_period ) {
					$subscription->cancel();
				}
			}

			return array(
				'success'      => true,
				'refund'       => $refund,
				'cancellation' => $cancel_result,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'error'   => $e->getMessage(),
			);
		}
	}

	public function refund_transaction( $payment_intent_id ) {
		try {
			// Full refund using payment intent
			$refund = \Stripe\Refund::create(
				array(
					'payment_intent' => $payment_intent_id,
				)
			);

			return array(
				'success' => true,
				'refund'  => $refund,
			);

		} catch ( \Stripe\Exception\ApiErrorException $e ) {
			return array(
				'success' => false,
				'error'   => $e->getMessage(),
			);
		}
	}

	public function refund( $order, $subscription ) {
		$response = array(
			'status' => false,
		);
		if ( 'stripe' !== $order['payment_method'] || 0 === absint( $order['total_amount'] ) ) {
			return $response;
		}
		if ( 'subscription' === $order['order_type'] ) {

			return $this->refund_subscription( $subscription, null, true );

		} else {
			$this->refund_transaction( $order['transaction_id'] );
		}
	}

	/**
	 * extracted
	 *
	 * @param int $member_id
	 *
	 * @return void
	 */
	public function revert_subscription( int $member_id ): void {
		$last_subscription = json_decode( get_user_meta( $member_id, 'urm_previous_subscription_data', true ), true );
		$subscription_id   = $last_subscription['ID'];
		unset( $last_subscription['ID'] );
		$last_order   = $this->members_orders_repository->get_member_orders( $member_id );
		$subscription = $this->members_subscription_repository->retrieve( $subscription_id );

		$this->members_subscription_repository->update( $subscription_id, $last_subscription );
		$this->members_orders_repository->delete_member_order( $member_id, false );
		$refund_response = $this->refund( $last_order, $subscription );

		delete_user_meta( $member_id, 'urm_previous_subscription_data' );
		delete_user_meta( $member_id, 'urm_is_upgrading' );
		delete_user_meta( $member_id, 'urm_is_upgrading_to' );
	}

	/**
	 * Checks if the product exists or not in stripe.
	 *
	 * @since 4.4.2
	 *
	 * @param  string $product_id
	 */
	public function check_exists_product_in_stripe( $product_id ) {
		try {
			$product = \Stripe\Product::retrieve( $product_id );

			return array(
				'success' => true,
				'product' => $product,
			);
		} catch ( \Stripe\Exception\ApiErrorException $ex ) {
			return array(
				'success' => false,
				'message' => $ex->getMessage(),
			);
		} catch ( Exception $ex ) {
			return array(
				'success' => false,
				'message' => $ex->getMessage(),
			);
		}
	}

	/**
	 * Checks if price id exists or not in stripe.
	 *
	 * @since 4.4.2
	 *
	 * @param  string $price_id
	 */
	public function check_price_exists_in_stripe( $price_id ) {
		try {
			$price = \Stripe\Price::retrieve( $price_id );

			return array(
				'success' => true,
				'price'   => $price,
			);
		} catch ( \Stripe\Exception\ApiErrorException $ex ) {
			return array(
				'success' => false,
				'message' => $ex->getMessage(),
			);
		}
	}

	/**
	 * Creates price for existing product if price_id not found.
	 *
	 * @since 4.4.2
	 *
	 * @param  string $product_id
	 * @param  array  $meta_data
	 */
	public function create_stripe_price_for_existing_product( $product_id, $meta_data ) {
		$currency = get_option( 'user_registration_payment_currency', 'USD' );

		try {
			// Calculate amount in cents (or leave as-is for JPY)
			$amount = ( 'JPY' === $currency ) ? abs( $meta_data['amount'] ) : abs( $meta_data['amount'] ) * 100;

			// Prepare price data
			$price_details = array(
				'unit_amount' => $amount,
				'currency'    => strtolower( $currency ),
				'product'     => $product_id,
			);

			// Add recurring details if it's a subscription
			if ( 'subscription' === $meta_data['type'] ) {
				$price_details['recurring'] = array(
					'interval'       => $meta_data['subscription']['duration'],
					'interval_count' => $meta_data['subscription']['value'],
				);
			}

			// Create new price in Stripe
			$price = \Stripe\Price::create( $price_details );

			return array(
				'success' => true,
				'price'   => $price,
			);

		} catch ( \Stripe\Exception\ApiErrorException $e ) {
			PaymentGatewayLogging::log_error(
				'stripe',
				'Error creating Stripe price',
				array(
					'error_code'    => 'PRICE_CREATION_FAILED',
					'error_message' => $e->getMessage(),
				)
			);
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}
}
