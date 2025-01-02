<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;

class MembershipService {
	private $membership_repository, $members_repository, $members_service, $subscription_repository, $orders_repository, $logger;

	public function __construct() {
		$this->membership_repository   = new MembershipRepository();
		$this->members_repository      = new MembersRepository();
		$this->members_service         = new MembersService();
		$this->subscription_repository = new SubscriptionRepository();
		$this->orders_repository       = new OrdersRepository();
		$this->logger                  = ur_get_logger();
	}

	/**
	 * assign_users_to_new_form
	 *
	 * @param $form_id
	 *
	 * @return void
	 */
	public function assign_users_to_new_form( $form_id ) {
		 $this->membership_repository->assign_users_to_new_form($form_id);
	}
	/**
	 * Retrieves and filters the list of all active memberships.
	 *
	 * This function fetches all memberships from the membership repository
	 * and applies the 'build_membership_list_frontend' filter to the result.
	 *
	 * @return array Filtered list of active memberships.
	 */
	public function list_active_memberships() {
		$memberships = $this->membership_repository->get_all_membership();

		return apply_filters( 'build_membership_list_frontend', $memberships );
	}

	/**
	 * Replace the old membership form shortcode with new registration form shortcode.
	 *
	 * @param int $form_id The form ID of the membership form which needs to be updated.
	 *
	 * @return string The updated form shortcode.
	 */
	public function find_and_replace_membership_form_with_registration_form( $form_id ) {
		return $this->membership_repository->replace_old_form_shortcode_with_new($form_id);
	}

	/**
	 * Creates a membership order and subscription.
	 *
	 * This function begins a database transaction to prepare and create a membership order and subscription.
	 * It utilizes member and order services to prepare necessary data and repositories to create the subscription
	 * and order records. If both subscription and order creation are successful, it commits the transaction,
	 * logs the success, and returns an array with the member ID, email, subscription ID, transaction ID, and status.
	 * In case of failure, it rolls back the transaction, logs the error, and returns an error message and status.
	 *
	 * @param array $data The data required to create the membership order and subscription.
	 *
	 * @return array An array containing member ID, email, subscription ID, transaction ID, and status on success,
	 *               or an error message and status on failure.
	 */
	public function create_membership_order_and_subscription( $data ) {
		try {
			$this->members_repository->wpdb()->query( 'START TRANSACTION' ); // Start the transaction.
			$members_data = $this->members_service->prepare_members_data( $data );
			$member       = get_user_by( 'login', $data['username'] );

			//update user source and add membership_role
			$this->members_service->update_user_meta( $members_data, $member->ID );

			$subscription_service = new SubscriptionService();
			$subscription_data    = $subscription_service->prepare_subscription_data( $members_data, $member );
			$subscription         = $this->subscription_repository->create( $subscription_data );
			$order_service        = new OrderService();
			$orders_data          = $order_service->prepare_orders_data( $members_data, $member->ID, $subscription ); // prepare data for orders table.
			$order                = $this->orders_repository->create( $orders_data );
			if ( $subscription && $order ) {
				$this->logger->info( 'Subscription and order created successfully for ' . $data['username'] . '.', array( 'source' => 'urm-registration-logs' ) );
				$this->members_repository->wpdb()->query( 'COMMIT' );

				return array(
					'member_id'       => $member->ID,
					'member_email'    => $member->user_email,
					'subscription_id' => $subscription['ID'],
					'transaction_id'  => $orders_data['orders_data']['transaction_id'],
					'status'          => true,
				);
			}
		} catch ( Exception $e ) {
			// Rollback the transaction if any operation fails.
			$this->members->wpdb()->query( 'ROLLBACK' );

			$data = array(
				'message' => $e->getMessage(),
				'status'  => false,
			);

			return apply_filters( 'urm_create_member_admin_after_error', $data, $e );
		}
	}

	/**
	 * Prepare membership data by unserializing post_content and meta_value,
	 * and removing any memberships with status set to false.
	 *
	 * @param array $memberships Array of membership data.
	 *
	 * @return array Prepared membership data.
	 */
	public function prepare_membership_data(
		$memberships
	) {
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
	 * Prepare membership post data by validating and sanitizing it.
	 *
	 * This function validates the membership data and sanitizes post meta data.
	 * It returns an associative array with two keys: 'post_data' and 'post_meta_data'.
	 * The 'post_data' key contains the data required to create a membership post
	 * and the 'post_meta_data' key contains the sanitized post meta data.
	 *
	 * @param array $data The data required to create a membership post.
	 *
	 * @return array An associative array with 'post_data' and 'post_meta_data' keys.
	 */
	public function prepare_membership_post_data(
		$data
	) {
		$membership_id = ! empty( $data['post_data']['ID'] ) ? absint( $data['post_data']['ID'] ) : '';
		$validate_data = $this->validate_membership_data( $data );

		if ( ! $validate_data['status'] ) {
			return $validate_data;
		}
		$post_meta_data = $this->sanitize_membership_meta_data( $data['post_meta_data'], $membership_id );

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
	public function sanitize_membership_meta_data(
		$data, $membership_id
	) {

		$product_id = "";
		$price_id   = "";
		if ( ! empty( $membership_id ) ) {
			$membership_meta = get_post_meta( $membership_id, 'ur_membership' );
			$membership_meta = json_decode( $membership_meta[0], true );

			if ( isset( $membership_meta["payment_gateways"]["stripe"] ) && "on" == $membership_meta["payment_gateways"]["stripe"]["status"] ) {
				$product_id = $membership_meta["payment_gateways"]["stripe"]["product_id"] ?? "";
				$price_id   = $membership_meta["payment_gateways"]["stripe"]["price_id"] ?? "";
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
				$data['payment_gateways']['stripe']['status']     = sanitize_text_field( $data['payment_gateways']['stripe']['status'] );
				$data['payment_gateways']['stripe']['product_id'] = sanitize_text_field( $product_id );
				$data['payment_gateways']['stripe']['price_id']   = sanitize_text_field( $price_id );
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
	public function validate_membership_data(
		$data
	) {
		$result = array(
			'status' => true,
		);

		if( isset($data['post_meta_data']['type']) && "subscription" === $data['post_meta_data']['type'] && ! (is_plugin_active( 'user-registration-pro/user-registration.php' ))) {
			$result['status']  = false;
			$result['message'] = esc_html__( "Subscription type is a paid feature.", "user-registration" );
			return $result;
		}

		//		payment gateway validation:stripe
		if ( isset( $data['post_meta_data']['payment_gateways']['stripe'] ) && "on" === $data['post_meta_data']['payment_gateways']['stripe']['status'] ) {
			$mode            = get_option( 'user_registration_stripe_test_mode', false ) ? 'test' : 'live';
			$publishable_key = get_option( sprintf( 'user_registration_stripe_%s_publishable_key', $mode ) );
			$secret_key      = get_option( sprintf( 'user_registration_stripe_%s_secret_key', $mode ) );
			$stripe_details  = $membership_details['payment_gateways']['stripe'] ?? '';

			if ( empty( $secret_key ) || empty( $publishable_key ) ) {
				$result['status']  = false;
				$result['message'] = esc_html__( "Incomplete Stripe Gateway setup.", "user-registration" );
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Retrieve Membership Details
	 *
	 * @param int $membership_id
	 *
	 * @return array
	 */
	public function get_membership_details(
		$membership_id
	) {
		$membership_repository = new MembershipRepository();
		$membership            = $membership_repository->get_single_membership_by_ID( $membership_id );

		return wp_unslash( json_decode( $membership['meta_value'], true ) );
	}
}
