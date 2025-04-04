<?php

namespace WPEverest\URMembership\Admin\Services\Paypal;

use Google\Service\Walletobjects\DateTime;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Admin\Services\MembersService;
use WPEverest\URMembership\Admin\Services\OrderService;
use WPEverest\URMembership\Admin\Services\SubscriptionService;

class PaypalService {
	/**
	 * @var MembersOrderRepository
	 */
	protected $members_orders_repository, $members_subscription_repository, $membership_repository, $orders_repository;

	/**
	 * Constructor of this class.
	 */
	public function __construct() {
		$this->members_orders_repository       = new MembersOrderRepository();
		$this->members_subscription_repository = new MembersSubscriptionRepository();
		$this->membership_repository           = new MembershipRepository();
		$this->orders_repository               = new OrdersRepository();
	}

	/**
	 * Build url
	 *
	 * @param $data
	 * @param $membership
	 * @param $member_email
	 * @param $subscription_id
	 * @param $member_id
	 *
	 * @return array|string|string[]
	 */
	public function build_url( $data, $membership, $member_email, $subscription_id, $member_id ) {
		$paypal_options               = $data['payment_gateways']['paypal'];
		$paypal_options['mode']       = get_option( 'user_registration_global_paypal_mode', $paypal_options['mode'] );
		$paypal_options['email']      = get_option( 'user_registration_global_paypal_email_address', $paypal_options['email'] );
		$paypal_options['cancel_url'] = get_option( 'user_registration_global_paypal_cancel_url', home_url() );
		$paypal_options['return_url'] = get_option( 'user_registration_global_paypal_return_url', wp_login_url() );
		$redirect                     = ( 'production' === $paypal_options['mode'] ) ? 'https://www.paypal.com/cgi-bin/webscr/?' : 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
		$post                         = get_post( $membership );
		$membership_amount            = number_format( $data['amount'] );
		$discount_amount              = 0;
		if ( isset( $data['coupon'] ) && ! empty( $data['coupon'] ) && ur_check_module_activation( 'coupon' ) ) {
			$coupon_details  = ur_get_coupon_details( $data['coupon'] );
			$discount_amount = ( 'fixed' === $coupon_details['coupon_discount_type'] ) ? $coupon_details['coupon_discount'] : $membership_amount * $coupon_details['coupon_discount'] / 100;
			$amount          = $membership_amount - $discount_amount;
		}

		if ( 'subscription' === ( $data['type'] ) ) {
			$transaction = '_xclick-subscriptions';
		} else {
			$transaction = '_xclick';
		}
		$query_args = 'membership=' . absint( $membership ) . '&member_id=' . absint( $member_id ) . '&hash=' . wp_hash( $membership . ',' . $member_id );
		$return_url = $paypal_options['return_url'] ?? wp_login_url();
		$return_url = esc_url_raw(
			add_query_arg(
				array(
					'ur-membership-return' => base64_encode( $query_args ),
				),
				apply_filters( 'user_registration_paypal_return_url', $return_url, array() )
			)
		);

		$paypal_args = array(
			'business'      => sanitize_email( $paypal_options['email'] ),
			'cancel_return' => $paypal_options['cancel_url'],
			'notify_url'    => add_query_arg( 'ur-membership-listener', 'IPN', home_url( 'index.php' ) ),
			'cbt'           => $post->post_title,
			'charset'       => get_bloginfo( 'charset' ),
			'cmd'           => $transaction,
			'currency_code' => get_option( 'user_registration_payment_currency', 'USD' ),
			'custom'        => $membership . '-' . $member_id . '-' . $subscription_id,
			'return'        => $return_url,
			'rm'            => '2',
			'tax'           => 0,
			'upload'        => '1',
			'sra'           => '1',
			'src'           => '1',
			'no_note'       => '1',
			'no_shipping'   => '1',
			'shipping'      => '0',
			'item_name'     => $post->post_title,
			'email'         => sanitize_email( $member_email ),
		);

		if ( '_xclick-subscriptions' === $transaction ) {
			$paypal_args['t3'] = ! empty( $data ['subscription'] ) ? strtoupper( substr( $data['subscription']['duration'], 0, 1 ) ) : '';
			$paypal_args['p3'] = ! empty( $data ['subscription']['value'] ) ? $data ['subscription']['value'] : 1;
			$paypal_args['a3'] = floatval( user_registration_sanitize_amount( $membership_amount ) );

			if ( 'on' === $data['trial_status'] ) {
				$paypal_args['t1'] = ! empty( $data ['trial_data'] ) ? strtoupper( substr( $data['trial_data']['duration'], 0, 1 ) ) : '';
				$paypal_args['p1'] = ! empty( $data ['trial_data'] ) ? $data ['trial_data']['value'] : 1;
				$paypal_args['a1'] = '0';
			}
			if ( ! empty( $coupon_details ) ) {
				$amount            = user_registration_sanitize_amount( $membership_amount ) - $discount_amount;
				$paypal_args['t2'] = ! empty( $data ['subscription'] ) ? strtoupper( substr( $data['subscription']['duration'], 0, 1 ) ) : '';
				$paypal_args['p2'] = ! empty( $data ['subscription']['value'] ) ? $data ['subscription']['value'] : 1;
				$paypal_args['a2'] = floatval( $amount );
			}
		} else {
			$paypal_args['amount'] = floatval( user_registration_sanitize_amount( $membership_amount ) - $discount_amount );
		}

		$redirect .= http_build_query( $paypal_args );

		return str_replace( ' & amp;', ' & ', $redirect );
	}

	/**
	 * Handle paypal redirect response.
	 *
	 * @param $params
	 * @param $payer_id
	 *
	 * @return void
	 */
	public function handle_paypal_redirect_response( $params, $payer_id ) {
		parse_str( $params, $url_params );
		$logger = ur_get_logger();

		$membership_id                  = $url_params['membership'];
		$member_id                      = $url_params['member_id'];
		$member_order                   = $this->members_orders_repository->get_member_orders( $member_id );
		$membership                     = $this->membership_repository->get_single_membership_by_ID( $membership_id );
		$membership_metas               = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$membership_metas['post_title'] = $membership['post_title'];
		$member_subscription            = $this->members_subscription_repository->get_member_subscription( $member_id );

		if ( 'completed' === $member_order['status'] ) {
			ur_membership_redirect_to_thank_you_page( $member_id, $member_order );
		}

		$is_order_updated = $this->members_orders_repository->update( $member_order['ID'], array( 'status' => 'completed' ) );

		if ( $is_order_updated && ('paid' === $member_order['order_type'] || 'subscription' === $member_order['order_type'] ) ) {
			$member_subscription = $this->members_subscription_repository->get_member_subscription( $member_id );
			$this->members_subscription_repository->update( $member_subscription['ID'], array( 'status' => 'active' ) );
			$logger->notice( 'Return to merchant log' . $member_subscription['ID'], array( 'source' => 'ur-membership-paypal' ) );
		}
		$email_service = new EmailService();
		$order_detail  = $this->orders_repository->get_order_detail( $member_order['ID'] );
		if ( ! empty( $order_detail['coupon'] ) ) {
			$order_detail['coupon_discount']      = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount', true );
			$order_detail['coupon_discount_type'] = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount_type', true );

		}
		$email_data = array(
			'subscription'     => $member_subscription,
			'order'            => $order_detail,
			'membership_metas' => $membership_metas,
			'member_id'        => $member_id,
		);

		$mail_send = $email_service->send_email( $email_data, 'payment_successful' );

		if ( ! $mail_send ) {
			$logger->notice( 'Email not sent for member: ' . $member_id . 'and subscription: ' . $member_subscription['ID'], array( 'source' => 'ur-membership-paypal' ) );
		}
		$login_option = ur_get_user_login_option( $member_id );
		if ( "auto_login" === $login_option ) {
			$member_service = new MembersService();
			$member_service->login_member( $member_id , true);
		}
		ur_membership_redirect_to_thank_you_page( $member_id, $member_order );
	}


	/**
	 * Handle membership paypal ipn
	 *
	 * @param $data
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function handle_membership_paypal_ipn( $data ) {

		// Check if $post_data_array has been populated.
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}
		$logger = ur_get_logger();
		$logger->notice( 'Paypal IPN Payload: ' . wp_json_encode( $data ), array( 'source' => 'ur-membership-paypal' ) );

		$txn_type = $data['txn_type'];
		if ( ! isset( $data['custom'] ) ) {
			$logger->notice( esc_html__( 'Custom param not found for subscription.', 'user-registration' ), array( 'source' => 'ur-membership-paypal' ) );

			return;
		}

		$custom           = explode( '-', $data['custom'] );
		$membership_id    = $custom[0];
		$member_id        = $custom[1];
		$subscription_id  = $custom[2];
		$latest_order     = $this->members_orders_repository->get_member_orders( $member_id );
		$membership       = $this->membership_repository->get_single_membership_by_ID( $membership_id );
		$membership_metas = wp_unslash( json_decode( $membership['meta_value'], true ) );
		$paypal_options   = $membership_metas['payment_gateways']['paypal'];

		$paypal_options['mode']       = get_option( 'user_registration_global_paypal_mode', $paypal_options['mode'] );
		$paypal_options['email']      = get_option( 'user_registration_global_paypal_email_address', $paypal_options['email'] );
		$paypal_options['cancel_url'] = get_option( 'user_registration_global_paypal_cancel_url', home_url() );
		$paypal_options['return_url'] = get_option( 'user_registration_global_paypal_return_url', wp_login_url() );

		$membership_metas['post_title'] = $membership['post_title'];
		$receiver_email                 = $paypal_options['email'];
		$amount                         = $membership_metas['amount'];
		$payment_mode                   = $paypal_options['mode'];
		$subscription                   = $this->members_subscription_repository->get_member_subscription( $member_id );

		// initialize email service.
		$email_service = new EmailService();
		$email_data    = array(
			'subscription'     => $subscription,
			'order'            => $latest_order,
			'membership_metas' => $membership_metas,
			'member_id'        => $member_id,
		);

		if ( ! $this->validate_ipn( $payment_mode ) ) {
			$logger->notice( esc_html__( 'Invalid response from paypal IPN for txn: ', 'user-registration' ) . $data['txn_id'], array( 'source' => 'ur-membership-paypal' ) );

			return;
		}
		if ( empty( $subscription ) ) {
			$logger->notice( 'Subscription Not Found for Subscription ID:' . $subscription_id, array( 'source' => 'ur-membership-paypal' ) );

			return;
		}
		if ( 'subscr_cancel' === $txn_type ) { // handle cancel ipn.
			$logger->notice( 'Cancellation request for Subscription ID:' . $subscription_id . 'from Paypal received.', array( 'source' => 'ur-membership-paypal' ) );
			// updating the status to cancel it for sure
			$this->members_subscription_repository->update(
				$subscription_id,
				array(
					'status' => 'canceled',
				)
			);

			$email_service->send_email( $email_data, 'membership_cancellation_email_user' );
			$email_service->send_email( $email_data, 'membership_cancellation_email_admin' );

			return;
		}
		// return if first ipn received, change the status of order and subscription to complete and active respectively.
		if ( 'subscr_signup' === $txn_type || 'web_accept' === $txn_type ) {

			$this->members_orders_repository->update( $latest_order['ID'], array( 'status' => 'completed' ) );

			if ( 'paid' === $membership_metas['type'] ) {
				$this->members_subscription_repository->update( $subscription_id, array( 'status' => 'active' ) );
			} else {
				$this->members_subscription_repository->update( $subscription_id, array( 'subscription_id' => sanitize_text_field( $data['subscr_id'] ) ) );
			}
			$order_detail = $this->orders_repository->get_order_detail( $latest_order['ID'] );
			if ( ! empty( $order_detail['coupon'] ) ) {
				$order_detail['coupon_discount']      = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount', true );
				$order_detail['coupon_discount_type'] = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount_type', true );

			}
			$email_data ['order'] = $order_detail;
			$email_service->send_email( $email_data, 'payment_successful' );

			$logger->notice( 'Subscriber Signup IPN successful for paid membership member ID: ' . $member_id, array( 'source' => 'ur-membership-paypal' ) );

			return;
		}
		$payment_date = \DateTime::createFromFormat( 'H:i:s M d, Y T', $data['payment_date'] ?? date( 'Y-m-d' ), new \DateTimeZone( 'PDT' ) );

		$payment_date = $payment_date->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d' );
		// handle first ipn of the day appart from the signup ipn.
		if ( 'subscr_payment' === $txn_type && $payment_date == date( 'Y-m-d' ) ) {
			$this->members_subscription_repository->update( $subscription_id, array( 'status' => 'active' ) );
			$logger->notice( 'Subscriber First IPN Successful for member: ' . $member_id, array( 'source' => 'ur-membership-paypal' ) );

			return;
		}
		$payment_status = strtolower( $data['payment_status'] );

		// Verify receiver's email address.
		if ( empty( $receiver_email ) || ! is_email( $receiver_email ) || strtolower( $data['business'] ) !== strtolower( trim( $receiver_email ) ) ) {
			$error = esc_html__( 'Payment failed: recipient emails do not match', 'user-registration' );
		} elseif ( empty( $amount ) || number_format( (float) $data['mc_gross'] ) !== number_format( (float) $amount ) ) {
			// Verify amount.
			$error = esc_html__( 'Payment failed: payment amounts do not match ', 'user-registration' );
		}

		if ( ! empty( $error ) ) {
			$logger->notice( $error . ' for Subscription ID:' . $subscription_id, array( 'source' => 'ur-membership-paypal' ) );
			$this->members_orders_repository->update( $latest_order['ID'], array( 'status' => 'failed' ) );

			return;
		}

		if ( 'subscr_payment' == $txn_type && $payment_date > date( 'Y-m-d' ) ) { // only create new order if ipn comes after the payment date since min subscription period is of a day.

			// create new order for ipn
			$order_info                                   = array(
				'membership_data' => array(
					'membership'     => $membership_id,
					'payment_method' => 'paypal',
				),
			);
			$order_service                                = new OrderService();
			$order_repository                             = new OrdersRepository();
			$order_data                                   = $order_service->prepare_orders_data( $order_info, $member_id, $subscription );
			$order_data['orders_data']['status']          = $payment_status;
			$order_data['orders_data']['total_amount']    = $membership_metas['amount'];
			$transaction_id                               = $data['txn_id'];
			$order_data['orders_data']['transaction_id']  = $transaction_id;
			$order_data['orders_data']['subscription_id'] = $subscription_id;

			$order_id = $order_repository->create( $order_data );
			$logger->notice( "New order ID $order_id created: " . json_encode( $order_data ), array( 'source' => 'ur-membership-paypal' ) );

			if ( $order_id ) {
				// update subscription
				if ( isset( $data['next_payment_date'] ) ) {
					$start_date        = new \DateTime( $data['next_payment_date'] );
					$next_billing_date = $start_date->format( 'Y-m-d' );
				} else {
					$next_billing_date = SubscriptionService::get_expiry_date( date( 'Y-m-d' ), $membership_metas['subscription']['duration'], $membership_metas['subscription']['value'] );
				}
				$this->members_subscription_repository->update(
					$subscription_id,
					array(
						'status'            => 'active',
						'next_billing_date' => $next_billing_date,
						'subscription_id'   => sanitize_text_field( $data['subscr_id'] ),
					)
				);
			}
		} elseif ( 'subscr_eot' == $txn_type ) {
			// Verify further if eot is ever received for time specified subscriptions
		}
		if ( 'completed' === $payment_status ) {
			$logger->notice( 'Successfully completed the payment. Payment ID:' . $subscription_id, array( 'source' => 'ur-membership-paypal' ) );
			$this->members_orders_repository->update(
				$latest_order['ID'],
				array(
					'status'         => 'completed',
					'transaction_id' => $transaction_id,
				)
			);

		}
	}

	/**
	 * Login to paypal
	 *
	 * @param $url
	 * @param $client_id
	 * @param $client_secret
	 *
	 * @return array|void
	 */
	public static function login_paypal( $url, $client_id, $client_secret ) {
		$url .= 'v1/oauth2/token';
		try {
			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials' );
			curl_setopt( $ch, CURLOPT_POST, true );

			$response    = curl_exec( $ch );
			$result      = json_decode( $response );
			$status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			curl_close( $ch );

			return array(
				'access_token' => $result->access_token,
				'status_code'  => $status_code,
			);
		} catch ( \Exception $e ) {

			ur_get_logger()->debug( $e->getMessage() );
		}
	}

	/**
	 * Cancel subscription
	 *
	 * @param $order
	 * @param $subscription
	 *
	 * @return array|bool[]
	 */
	public function cancel_subscription( $order, $subscription ) {
		$membership      = $this->membership_repository->get_single_membership_by_ID( $order['item_id'] );
		$membership_meta = json_decode( $membership['meta_value'], true );
		$paypal_options  = $membership_meta['payment_gateways']['paypal'];

		$paypal_options['mode']          = get_option( 'user_registration_global_paypal_mode', $paypal_options['mode'] );
		$paypal_options['email']         = get_option( 'user_registration_global_paypal_email_address', $paypal_options['email'] );
		$paypal_options['cancel_url']    = get_option( 'user_registration_global_paypal_cancel_url', home_url() );
		$paypal_options['return_url']    = get_option( 'user_registration_global_paypal_return_url', wp_login_url() );
		$paypal_options['client_id']     = get_option( 'user_registration_global_paypal_client_id', $paypal_options['client_id'] );
		$paypal_options['client_secret'] = get_option( 'user_registration_global_paypal_client_secret', $paypal_options['client_secret'] );

		$client_id     = $paypal_options['client_id'];
		$client_secret = $paypal_options['client_secret'];
		$url           = ( 'production' === $paypal_options['mode'] ) ? 'https://api-m.paypal.com/' : 'https://api-m.sandbox.paypal.com/';
		$login_request = self::login_paypal( $url, $client_id, $client_secret );
		if ( 200 !== $login_request['status_code'] ) {
			$message = esc_html__( 'Invalid response from paypal, check Client ID or Secret.', 'user-registration' );
			ur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );

			return array(
				'status'  => false,
				'message' => $message,
			);
		}
		if ( empty( $subscription['subscription_id'] ) ) {
			$message = esc_html__( 'Paypal Subscription ID not present, please contact your administrator.', 'user-registration' );
			ur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );

			return array(
				'status'  => false,
				'message' => $message,
			);
		}
		$url .= sprintf( 'v1/billing/subscriptions/%s/cancel', $subscription['subscription_id'] );

		$bearerToken = $login_request['access_token']; // Replace with your actual Bearer token

		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Bearer ' . $bearerToken,
		);
		$ch      = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$response = curl_exec( $ch );
		$result   = json_decode( $response );

		$status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		if ( curl_errno( $ch ) ) {
			ur_get_logger()->notice( curl_error( $ch ), array( 'source' => 'ur-membership-paypal' ) );
		}
		curl_close( $ch );
		ur_get_logger()->notice( 'Paypal Response Status Code: ' . $status_code, array( 'source' => 'ur-membership-paypal' ) );

		if ( 204 === $status_code ) {
			$message = esc_html__( 'Subscription successfully canceled from paypal.', 'user-registration' );
			ur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );

			return array(
				'status' => true,
			);
		}
		$message = esc_html__( 'Subscription cancellation failed from Paypal.', 'user-registration' );
		ur_get_logger()->notice( $response, array( 'source' => 'ur-membership-paypal' ) );

		return array(
			'status'  => false,
			'message' => $message,
		);
	}

	/**
	 * validate_ipn
	 *
	 * @param $payment_mode
	 *
	 * @return bool
	 */
	public function validate_ipn( $payment_mode ) {
		$logger = ur_get_logger();
		$logger->notice( 'Checking IPN response is valid' );
		// Get received values from post data.
		$validate_ipn        = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$validate_ipn['cmd'] = '_notify-validate';
		// Send back post vars to paypal.
		$params = array(
			'body'        => $validate_ipn,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => 'User Registration IPN Verification',
		);

		$remote_post_url = ( ! empty( $payment_mode ) && 'production' === $payment_mode ) ? 'https://ipnpb.paypal.com/cgi-bin/webscr' : 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
		// Post back to get a response.
		$response = wp_safe_remote_post( $remote_post_url, $params );
		$logger   = ur_get_logger();
		$logger->notice( 'IPN Response: ' . print_r( $response, true ) );
		// Check to see if the request was valid.
		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
			$logger = ur_get_logger();
			$logger->notice( 'Received valid response from PayPal IPN' );

			return true;
		}
		$logger = ur_get_logger();
		$logger->notice( 'Received invalid response from PayPal IPN' );
		if ( is_wp_error( $response ) ) {
			$logger = ur_get_logger();
			$logger->notice( 'Error response: ' . $response->get_error_message() );
		}

		return false;
	}

	public function validate_setup( $membership_type ) {
		$paypal_options['email'] = get_option( 'user_registration_global_paypal_email_address' );
		if ( "subscription" === $membership_type ) {
			$paypal_options['client_id']     = get_option( 'user_registration_global_paypal_client_id' );
			$paypal_options['client_secret'] = get_option( 'user_registration_global_paypal_client_secret' );
		}

		$is_incomplete = false;
		foreach ( $paypal_options as $k => $option ) {
			if ( empty( $option ) ) {
				$is_incomplete = true;
			}
		}
		return $is_incomplete;
	}
}
