<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;

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
		$this->membership_repository->assign_users_to_new_form( $form_id );
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
		return $this->membership_repository->replace_old_form_shortcode_with_new( $form_id );
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
						'type'   => sanitize_text_field( $data['post_meta_data']['type'] ),
						'status' => wp_validate_boolean( $data['post_data']['status'] ),
					)
				),
				'post_type'      => 'ur_membership',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			'post_meta_data' => array(
				'ur_membership'             => array(
					'meta_key'   => 'ur_membership',
					'meta_value' => wp_json_encode( $post_meta_data ),
				),
				'ur_membership_description' => array(
					'meta_key'   => 'ur_membership_description',
					'meta_value' => wp_kses_post( $data['post_data']['description'] ),
				)
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
		$data['cancel_subscription'] = sanitize_text_field( ! empty( $data['cancel_subscription'] ) ? $data['cancel_subscription'] : '' );

		$data['amount'] = absint( $data['amount'] ?? 0 );
		if ( isset( $data['payment_gateways'] ) ) {
			if ( isset( $data['payment_gateways']['paypal'] ) && 'on' === $data['payment_gateways']['paypal']['status'] ) {
				$data['payment_gateways']['paypal']['status']     = sanitize_text_field( $data['payment_gateways']['paypal']['status'] );
				$data['payment_gateways']['paypal']['email']      = sanitize_email( ! empty( $data['payment_gateways']['paypal']['email'] ) ? $data['payment_gateways']['paypal']['email'] : '' );
				$data['payment_gateways']['paypal']['mode']       = sanitize_text_field( ! empty( $data['payment_gateways']['paypal']['mode'] ) ? $data['payment_gateways']['paypal']['mode'] : 'sandbox' );
				$data['payment_gateways']['paypal']['cancel_url'] = esc_url( ! empty( $data['payment_gateways']['paypal']['cancel_url'] ) ? $data['payment_gateways']['paypal']['cancel_url'] : '' );
				$data['payment_gateways']['paypal']['return_url'] = esc_url( ! empty( $data['payment_gateways']['paypal']['return_url'] ) ? $data['payment_gateways']['paypal']['return_url'] : '' );
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
		if ( isset( $data['upgrade_settings'] ) ) {
			$data['upgrade_settings']['upgrade_action'] = absint( $data['upgrade_settings']['upgrade_action'] );
			$data['upgrade_settings']['upgrade_path']   = sanitize_text_field( implode( ',', $data['upgrade_settings']['upgrade_path'] ) );
			$data['upgrade_settings']['upgrade_type']   = ! empty( $data['upgrade_settings']['upgrade_type'] ) ? sanitize_text_field( $data['upgrade_settings']['upgrade_type'] ) : 'full';
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

		if ( isset( $data['post_meta_data']['type'] ) && "subscription" === $data['post_meta_data']['type'] && ! ( UR_PRO_ACTIVE ) ) {
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
				$result['message'] = esc_html__( "Incomplete Stripe setup, please update stripe payment settings before continuing.", "user-registration" );

				return $result;
			}
		}

		//		payment gateway validation:paypal
		if ( isset( $data['post_meta_data']['payment_gateways']['paypal'] ) && "on" === $data['post_meta_data']['payment_gateways']['paypal']['status'] ) {
			$paypal_email = get_option( 'user_registration_global_paypal_email_address' );

			if ( empty( $paypal_email ) ) {
				$result['status']  = false;
				$result['message'] = esc_html__( "Incomplete Paypal setup, please update paypal payment settings before continuing.", "user-registration" );

				return $result;
			}
		}

		/**
		 * Filters the membership data validation result
		 *
		 * This hook should be used by new payment gateway integrations add-on to validate the membership data.
		 *
		 * @param array $result Membership validation result data
		 * @param array $data Membership data.
		 *
		 * @since 4.2.3
		 *
		 */
		return apply_filters( 'user_registration_membership_validate_membership_data', $result, $data );
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

	public function verify_page_content( $type, $post_id ) {
		$response = array(
			'status' => true
		);
		$post     = get_post( $post_id );
		if ( empty( $post ) ) {
			$response['status']  = false;
			$response['message'] =  __( 'No page selected.', 'user-registration' );

			return $response; //return since the post does not exist;
		}
		switch ( $type ) {
			case 'user_registration_member_registration_page_id':
				$response = self::verify_membership_registration_form_shortcode( $post, $response );
				break;
			default:
				$response = self::verify_thank_you_shortcode( $post, $response );
		}

		return $response;
	}

	/**
	 * verify_membership_registration_form_shortcode
	 *
	 * @param $post
	 * @param $response
	 *
	 * @return mixed
	 */
	private static function verify_membership_registration_form_shortcode( $post, $response ) {
		$membership_field_exists = false;
		$match                   = preg_match_all( '/\[user_registration_form\s+id="(\d+)"\]/', $post->post_content, $matches );
		if ( ! $match ) {
			$match = preg_match_all( '<!-- /wp:user-registration/membership-listing -->', $post->post_content, $matches );
			if ( ! $match ) {
				$response['status']  = false;
				$response['message'] = __( 'The selected page does not consist any User Registration & Membership Form.' );

				return $response;
			}
		}
		$fields = ur_get_form_fields( $matches[1][0] );
		foreach ( $fields as $k => $field ) {
			if ( 'membership' === $field->field_key ) {
				$membership_field_exists = true;
			}
		}
		$response['status']  = $membership_field_exists;
		$response['message'] = ! $membership_field_exists ? __( 'The selected page consist a User Registration & Membership Form but no membership field.' ) : '';

		return $response;
	}

	/**
	 * verify_thank_you_shortcode
	 *
	 * @param $post
	 * @param $response
	 *
	 * @return mixed
	 */
	private static function verify_thank_you_shortcode( $post, $response ) {

		$content = $post->post_content;
		$match   = preg_match( '/\[user_registration_membership_thank_you\]/', $content );
		if ( ! $match ) {
			$match = preg_match( '<!-- /wp:user-registration/thank-you -->', $content );
			if ( ! $match ) {
				$response['status']  = false;
				$response['message'] = __( 'The selected page does not consist the User Registration & Membership Thank you page Shortcode.' );

				return $response;
			}
		}

		return $response;
	}

	/**
	 * validate_payment_gateway
	 *
	 * @param $data
	 *
	 * @return string[]
	 */
	public function validate_payment_gateway( $data ) {
		$response = array(
			'status' => 'true'
		);

		switch ( $data[0] ) {
			case 'paypal';
				$paypal_service = new PaypalService();
				$result         = $paypal_service->validate_setup( $data[1] );
				break;
			case 'stripe';
				$stripe_service = new StripeService();
				$result         = $stripe_service->validate_setup();
				break;
			default:
				$result = empty( get_option( 'user_registration_global_bank_details' ) );
				break;
		}
		/**
		 * Filters whether the payment gateway setup is valid.
		 *
		 * @param bool $result Payment setup validation check, yield true for invalid setup.
		 * @param array $data Payment data.
		 *
		 * @return bool $result
		 */
		$result = apply_filters( 'user_registration_membership_validate_payment_gateway', $result, $data );

		if ( $result ) {
			$response['status']  = false;
			$response['message'] = __( 'Incomplete ' . ucfirst( $data[0] ) . ' setup.', "user-registration" );
		}

		return $response;
	}

	public function get_upgradable_membership( $membership_id ) {
		$membership_details = $this->get_membership_details( $membership_id );
		if ( ! empty( $membership_details['upgrade_settings'] ) && $membership_details['upgrade_settings']['upgrade_action'] ) {
			$memberships = $this->membership_repository->get_multiple_membership_by_ID( $membership_details['upgrade_settings']['upgrade_path'] );

			return apply_filters( 'build_membership_list_frontend', $memberships );
		}

		return array();
	}

	public function delete_membership( $membership_id ) {
		$response                  = array(
			'status' => true,
		);
		$valid_membership_statuses = apply_filters( "urm_valid_membership_statuses", array(
				__( "active", "user-registration" ),
				__( "pending", "user-registration" ),
				__( "trial", "user-registration" )
			)
		);
		$active_members            = $this->membership_repository->check_deletable_membership( $membership_id, $valid_membership_statuses );
		if ( $active_members['total'] > 0 ) {
			$html = "<p>" . __( "You cannot delete a membership plan if it has active users assigned to it with any of the following statuses:", "user-registration" ) . "</p>";
			$html .= "<ul>";
			foreach ( $valid_membership_statuses as $status ) {
				$html .= "<li>" . ucfirst($status). "</li>";
			}
			$html .= "</ul >";
			$html .= "<p >" . __( "To proceed, update all memberships to Expired or Cancelled, or assign users to a different plan . Once no active users remain, the plan can be deleted", "user-registration" ) . "</p > ";

			$response = array(
				'status'  => false,
				'message' => $html
			);
		} else {
			$this->membership_repository->delete( $membership_id );
		}

		return $response;
	}
}
