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
			$product = array_values($product_exists);
			$product = $product_exists[0];
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
					'price'   => $price
				);
			}
		} catch ( ApiErrorException $e ) {
			ur_get_logger()->debug( 'Error creating price: ' . $e->getMessage(), array( 'source' => 'user-registration-membership-stripe' ) );

			return array(
				'success' => false,
				'message' => $e->getMessage()
			);
		}

		return array(
			'success' => true,
			'price'   => $price
		);
	}

	public function process_stripe_payment( $payment_data, $response_data ) {
		$currency   = get_option( 'user_registration_payment_currency', 'USD' );
		$amount     = $payment_data['amount'];
		$user_email = $response_data['email'];
		$member_id  = $response_data['member_id'];
		$response   = array(
			'type' => $payment_data['type'],
		);

		if ( isset( $payment_data['coupon'] ) && ! empty( $payment_data['coupon'] ) && ur_pro_is_coupons_addon_activated() ) {
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
			$customer                  = \Stripe\Customer::create(
				array(
					'email' => $user_email,
				)
			);
			$response['stripe_cus_id'] = $customer->id;
			if ( ! empty( $customer ) ) {
				update_user_meta( $member_id, 'ur_payment_customer', $customer->id );
			}
			if ( 'paid' === $payment_data['type'] ) {
				$intent                    = \Stripe\PaymentIntent::create(
					array(
						'amount'               => $amount,
						'currency'             => $currency,
						'payment_method_types' => array( 'card' ),
						'customer'             => $customer->id,
					)
				);
				$response['client_secret'] = $intent->client_secret;
			}

			return $response;
		} catch ( ApiErrorException $e ) {
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
		$logger         = ur_get_logger();
		$is_upgrading   = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_upgrading', true ) );


		$response = array(
			'status' => true,
		);

		$latest_order = $this->members_orders_repository->get_member_orders( $member_id );

		if ( empty( $latest_order ) ) {
			$logger->notice( '-------------------------------------------- Order not found for  ' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );
			$response['status']  = false;
			$response['message'] = __( 'Order not found for  ' . $member_id, 'user-registration' );

			return $response;
		}
		$logger->notice( '-------------------------------------------- Stripe Payment Confirmation started for ' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );

		if ( 'failed' === $payment_status ) {
			if ( ! $is_upgrading ) {
				wp_delete_user( absint( $member_id ) );
				$this->members_orders_repository->delete_member_order( $member_id );

			}
			$error_msg = __( 'Stripe Payment failed.', 'user-registration' );
			$error_msg = $data['payment_result']['error']['message'] ?? $error_msg;


			$logger->notice( $error_msg, array( 'source' => 'ur-membership-paypal' ) );
			$response['message'] = $error_msg;
			$response['status']  = false;
			$logger->notice( '-------------------------------------------- Stripe Subscription process failed for ' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );

			return $response;
		} elseif ( 'succeeded' === $payment_status ) {
			$member_order = $this->members_orders_repository->get_member_orders( $member_id );

			if ( 'completed' === $member_order['status'] ) {
				$logger->notice( '-------------------------------------------- Stripe Subscription process: Order status is already completed.' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );
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
				$this->members_subscription_repository->update( $member_subscription['ID'], array( 'status' => 'active' ) );
				$logger->notice( 'Order and subscription status updated ', array( 'source' => 'ur-membership-stripe' ) );
			}
			$response = $this->sendEmail( $member_order['ID'], $member_subscription, $membership_metas, $member_id, $response );
		}
		$logger->notice( '-------------------------------------------- Stripe Subscription process ended for ' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );

		return $response;
	}

	public function create_subscription( $customer_id, $payment_method_id, $member_id, $is_upgrading ) {

		$member_order                   = $this->members_orders_repository->get_member_orders( $member_id );
		$membership                     = $this->membership_repository->get_single_membership_by_ID( $member_order['item_id'] );
		$membership_metas               = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$membership_metas['post_title'] = $membership['post_title'];
		$member_subscription            = $this->members_subscription_repository->get_member_subscription( $member_id );
		$response                       = array(
			'status' => false,
		);
		$logger                         = ur_get_logger();
		if ( empty( $member_subscription ) ) {
			$logger->notice( '-------------------------------------------- Stripe Subscription not found for ' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );

			return $response;
		}

		$logger->notice( '-------------------------------------------- Stripe Subscription started for ' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );

		$stripe_product_details = $membership_metas['payment_gateways']['stripe'] ?? array();

		if ( ! isset( $stripe_product_details['price_id'] ) || ! isset( $stripe_product_details['product_id'] ) ) {
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

								$coupon = \Stripe\Coupon::create( [
									'amount_off' => $amount,
									'currency'   => $currency,
									'duration'   => 'once',
									'name'       => 'UpgradeCoupon',
								] );

								$subscription_details['coupon'] = $coupon->id;
							}
						}
					}
				}

				$next_subscription = json_decode( get_user_meta( $member_id, 'urm_next_subscription_data', true ), true );
				if ( ! empty( $next_subscription['delayed_until'] ) ) {
					$subscription_details['trial_end'] = strtotime( $next_subscription['delayed_until'] );
				} elseif (
					! empty( $previous_subscription['trial_end_date'] ) &&
					isset( $membership_metas['type'], $membership_metas['trial_status'] ) &&
					$membership_metas['type'] === 'subscription' &&
					$membership_metas['trial_status'] === 'on'
				) {
					$subscription_details['trial_end'] = strtotime( $previous_subscription['trial_end_date'] );
				}
			}
			$logger->notice( print_r( $subscription_details, true ), array( 'source' => 'ur-membership-stripe' ) );

			$subscription        = \Stripe\Subscription::create( $subscription_details );
			$subscription_status = $subscription->status ?? '';

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
						$subscription_status = ($is_upgrading && ! empty( $next_subscription['delayed_until'] )) ? 'active' : 'trial';
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

				$this->sendEmail( $member_order['ID'], $member_subscription, $membership_metas, $member_id, $response );
				$response['subscription'] = $subscription;
				$response['message']      = __( 'New member has been successfully created with successful stripe subscription.' );
				$response['status']       = true;
			}
			$logger->notice( '-------------------------------------------- Stripe Subscription process ended for ' . $member_id . ' --------------------------------------------', array( 'source' => 'ur-membership-stripe' ) );

			return $response;
		} catch
		( \Exception $e ) {
			if ( ! $is_upgrading ) {
				wp_delete_user( absint( $member_id ) );
				$this->members_orders_repository->delete_member_order( $member_id );
				$customer = \Stripe\Customer::retrieve( $customer_id );
				$customer->delete();
			}
			$logger->notice( print_r( $e, true ), array( 'source' => 'ur-membership-stripe' ) );

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
	 * @param int $ID The ID of the email.
	 * @param mixed $member_subscription The subscription details of the member.
	 * @param array $membership_metas Metadata related to the membership.
	 * @param int $member_id The ID of the member.
	 * @param array $response The response data.
	 *
	 * @return array The result of the email operation.
	 */
	public function sendEmail( int $ID, $member_subscription, array $membership_metas, int $member_id, array $response, $is_upgrading = false ): array {
		$email_service = new EmailService();
		$order_detail  = $this->orders_repository->get_order_detail( $ID );
		$logger        = ur_get_logger();
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
			$logger->notice( __( 'Payment Mail could not be sent after successful stripe payment ', '"user-registration' ), array( 'source' => 'ur-membership-stripe' ) );
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

		if ( ! isset( $subscription['subscription_id'] ) ) {
			ur_get_logger()->notice( 'Stripe subscription_id not found.', array( 'source' => 'urm-cancellation-log' ) );

			return $response;
		}

		$subscription = \Stripe\Subscription::retrieve( $subscription['subscription_id'] );
		$deleted_sub  = $subscription->cancel();
		if ( '' !== $deleted_sub['canceled_at'] ) {
			$response['status'] = true;
		}
		ur_get_logger()->notice( '----------------- Stripe cancellation response for subscription id ' . $subscription['subscription_id'] . ' -----------------', array( 'source' => 'urm-cancellation-log' ) );
		ur_get_logger()->notice( print_r( $deleted_sub, true ), array( 'source' => 'urm-cancellation-log' ) );

		return $response;
	}

	public function handle_webhook( $event, $subscription_id ) {
		switch ( $event['type'] ) {
			case 'invoice.payment_succeeded':
				$this->handle_succeeded_invoice( $event, $subscription_id );
				break;
			default:
				break;
		}
	}

	public function handle_succeeded_invoice( $event, $subscription_id ) {
		$logger = ur_get_logger();
		$logger->notice( 'triggered succeeded invoice webhook.', array( 'source' => 'user-registration-membership-stripe' ) );

		if ( null === $subscription_id ) {
			$logger->error( 'Subscription ID is null', array( 'source' => 'user-registration-membership-stripe' ) );

			return;
		}

		$current_subscription = $this->members_subscription_repository->get_membership_by_subscription_id( $subscription_id, true );

		if ( null === $current_subscription ) {
			$logger->error( 'Subscription not found for subscription id ' . $subscription_id, array( 'source' => 'user-registration-membership-stripe' ) );

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

		$logger->notice( "New order ID $order_id created: " . json_encode( $order_data ), array( 'source' => 'user-registration-membership-stripe' ) );

		$next_billing_date = SubscriptionService::get_expiry_date( date( 'Y-m-d' ), $membership_metas['subscription']['duration'], $membership_metas['subscription']['value'] );
		$this->members_subscription_repository->update(
			$current_subscription['sub_id'],
			array(
				'next_billing_date' => $next_billing_date,
				'expiry_date'       => $next_billing_date,
				'status'            => $subscription_status,
			)
		);
		$logger->notice( 'Subscription updated with new billing date', array( 'source' => 'user-registration-membership-stripe' ) );
	}

	public function validate_setup() {
		$stripe_settings = self::get_stripe_settings();

		return ( empty( $stripe_settings['publishable_key'] ) || empty( $stripe_settings['secret_key'] ) );
	}

	public function refund_subscription( $subscription, $refund_amount = null, $cancel_subscription = false, $cancel_at_end_period = false ) {
		try {
			$response        = array(
				'status' => false
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


			$refundParams = [ 'charge' => $charge_id ];
			if ( ! is_null( $refund_amount ) ) {
				$refundParams['amount'] = $refund_amount;
			}

			$refund = \Stripe\Refund::create( $refundParams );


			$cancel_result = null;
			if ( $cancel_subscription ) {
				\Stripe\Subscription::update( $subscription_id, [
					'cancel_at_period_end' => $cancel_at_end_period,
				] );


				if ( ! $cancel_at_end_period ) {
					$subscription->cancel();
				}
			}

			return array(
				'success'      => true,
				'refund'       => $refund,
				'cancellation' => $cancel_result
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
			$refund = \Stripe\Refund::create( [
				'payment_intent' => $payment_intent_id,
			] );

			return array(
				'success' => true,
				'refund'  => $refund
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
			'status' => false
		);
		if ( "stripe" !== $order['payment_method'] || 0 === absint( $order["total_amount"] ) ) {
			return $response;
		}
		if ( "subscription" === $order['order_type'] ) {

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
}
