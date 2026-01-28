<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository;
use WPEverest\URMembership\Admin\Services\UpgradeMembershipService;
use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\Admin\Services\Paypal\PaypalService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;
use WPEverest\URMembership\TableList;

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

			// update user source and add membership_role
			$this->members_service->update_user_meta( $members_data, $member->ID );

			$subscription_service = new SubscriptionService();
			$subscription_data    = $subscription_service->prepare_subscription_data( $members_data, $member );
			$subscription         = $this->subscription_repository->create( $subscription_data );
			$order_service        = new OrderService();
			$orders_data          = $order_service->prepare_orders_data( $members_data, $member->ID, $subscription ); // prepare data for orders table.

			$order   = $this->orders_repository->create( $orders_data );
			$team_id = '';
			if ( $subscription && $order ) {
				$this->logger->info( 'Subscription and order created successfully for ' . $data['username'] . '.', array( 'source' => 'urm-registration-logs' ) );
				if ( ! empty( $members_data['team'] ) && ur_check_module_activation( 'team' ) ) {
					$first_name      = get_user_meta( $member->ID, 'first_name', true );
					$team_post_count = wp_count_posts( 'ur_membership_team' );
					$team_index      = (int) ( $team_post_count->publish ?? 0 ) + 1;
					if ( $first_name ) {
						$team_name = $first_name . '-Team-#' . $team_index;
					} else {
						$team_name = 'Team-#' . $team_index;
					}
					$team_id = wp_insert_post(
						[
							'post_type'   => 'ur_membership_team',
							'post_title'  => $team_name,
							'post_status' => 'publish',
						]
					);

					if ( 0 === $team_id ) {
						throw new \Exception( 'Failed to create team post' );
					}
					update_post_meta( $team_id, 'urm_team_data', $members_data['team'] );
					if ( ! empty( $members_data['tier'] ) ) {
						update_post_meta(
							$team_id,
							'urm_tier_info',
							$members_data['tier']
						);
					}
					update_post_meta( $team_id, 'urm_team_seats', $members_data['team_seats'] );
					update_post_meta( $team_id, 'urm_used_seats', 1 );
					update_post_meta( $team_id, 'urm_order_id', $order['ID'] );
					update_post_meta( $team_id, 'urm_subscription_id', $subscription['ID'] );
					update_post_meta( $team_id, 'urm_team_leader_id', $member->ID );
					update_post_meta( $team_id, 'urm_member_emails', array( $member->user_email ) );
					update_post_meta( $team_id, 'urm_member_ids', array( $member->ID ) );
					update_post_meta( $team_id, 'urm_membership_id', $subscription_data['item_id'] );
					$team_ids = get_user_meta( $member->ID, 'urm_team_ids', true );

					if ( ! is_array( $team_ids ) ) {
						$team_ids = empty( $team_ids ) ? array() : array( $team_ids );
					}

					$team_ids[] = $team_id;

					update_user_meta( $member->ID, 'urm_team_ids', $team_ids );
					$this->orders_repository->update_order_meta(
						array(
							'order_id'   => $order['ID'],
							'meta_key'   => 'urm_team_id',
							'meta_value' => $team_id,
						)
					);
				}
				$this->members_repository->wpdb()->query( 'COMMIT' );

				$payload = array(
					'subscription_id' => $subscription['ID'],
					'member_id'       => $member->ID,
					'event_type'      => '',
					'meta'            => array(),
				);

				if ( isset( $subscription_data['status'] ) && 'trial' === $subscription_data['status'] ) {
					// Register subscription trial started event.
					$payload['event_type'] = 'trial_started';
					$payload['meta']       = array(
						'trial_end_date'    => $subscription_data['trial_end_date'],
						'next_billing_date' => $subscription_data['next_billing_date'],
					);

					do_action( 'ur_membership_subscription_event_triggered', $payload );
				} else {

					// Register subscription created event.
					$payload['event_type'] = 'created';
					$payload['meta']       = array(
						'order_id'          => $order['ID'],
						'transaction_id'    => $order['transaction_id'],
						'payment_method'    => $order['payment_method'],
						'next_billing_date' => $subscription_data['next_billing_date'],
					);
					do_action( 'ur_membership_subscription_event_triggered', $payload );
				}

				return array(
					'member_id'       => $member->ID,
					'member_email'    => $member->user_email,
					'subscription_id' => $subscription['ID'],
					'transaction_id'  => $orders_data['orders_data']['transaction_id'],
					'status'          => true,
					'team_id'         => $team_id,
				);
			}
		} catch ( Exception $e ) {
			// Rollback the transaction if any operation fails.
			$this->members_repository->wpdb()->query( 'ROLLBACK' );
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
	 * Prepare membership data without filtering by status
	 * Used for content restriction where all memberships should be available
	 *
	 * @param array $memberships Raw membership data from database
	 * @return array Prepared membership data
	 */
	public function prepare_membership_data_without_status_filter(
		$memberships
	) {
		foreach ( $memberships as $key => $membership ) {
			$membership_post_content = json_decode( wp_unslash( $membership['post_content'] ), true );
			// Don't filter by status - include all memberships
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
		// if( !empty($data['post_meta_data']['payment_gateways']) ) {
		// foreach ($data['post_meta_data']['payment_gateways'] as $pg => $pg_data) {
		// if("on" == $pg_data['status']) {
		// $validate_pg = $this->validate_payment_gateway( array($pg, $data['post_meta_data']['type']));
		// if(!$validate_pg['status']) {
		// $validate_data = $validate_pg;
		// }
		// }
		// }
		// }

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
				),
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
		$data,
		$membership_id
	) {

		$product_id = '';
		$price_id   = '';
		if ( ! empty( $membership_id ) ) {
			$membership_meta = get_post_meta( $membership_id, 'ur_membership' );
			$membership_meta = json_decode( $membership_meta[0], true );

			if ( isset( $membership_meta['payment_gateways']['stripe'] ) ) {
				$product_id = $membership_meta['payment_gateways']['stripe']['product_id'] ?? '';
				$price_id   = $membership_meta['payment_gateways']['stripe']['price_id'] ?? '';
			}
		}

		// Todo: make this dynamic in future
		$data['type'] = sanitize_text_field( $data['type'] );
		if ( isset( $data['subscription'] ) && is_array( $data['subscription'] ) ) {
			$data['subscription']['value']    = isset( $data['subscription']['value'] ) ? absint( $data['subscription']['value'] ) : '';
			$data['subscription']['duration'] = isset( $data['subscription']['duration'] ) ? sanitize_text_field( $data['subscription']['duration'] ) : '';
			$data['trial_status']             = isset( $data['trial_status'] ) ? sanitize_text_field( $data['trial_status'] ) : '';
			if ( 'on' === $data['trial_status'] && isset( $data['trial_data'] ) && is_array( $data['trial_data'] ) ) {
				$data['trial_data']['value']    = absint( $data['trial_data']['value'] );
				$data['trial_data']['duration'] = sanitize_text_field( $data['trial_data']['duration'] );
			}
		}
		$data['cancel_subscription'] = sanitize_text_field( ! empty( $data['cancel_subscription'] ) ? $data['cancel_subscription'] : '' );

		$data['amount'] = $data['amount'] ?? 0;

		if ( isset( $data['payment_gateways'] ) ) {
			if ( isset( $data['payment_gateways']['paypal'] ) && is_array( $data['payment_gateways']['paypal'] ) ) {
				$data['payment_gateways']['paypal']['status']     = sanitize_text_field( $data['payment_gateways']['paypal']['status'] );
				$data['payment_gateways']['paypal']['email']      = sanitize_email( ! empty( $data['payment_gateways']['paypal']['email'] ) ? $data['payment_gateways']['paypal']['email'] : '' );
				$data['payment_gateways']['paypal']['mode']       = sanitize_text_field( ! empty( $data['payment_gateways']['paypal']['mode'] ) ? $data['payment_gateways']['paypal']['mode'] : 'sandbox' );
				$data['payment_gateways']['paypal']['cancel_url'] = esc_url( ! empty( $data['payment_gateways']['paypal']['cancel_url'] ) ? $data['payment_gateways']['paypal']['cancel_url'] : '' );
				$data['payment_gateways']['paypal']['return_url'] = esc_url( ! empty( $data['payment_gateways']['paypal']['return_url'] ) ? $data['payment_gateways']['paypal']['return_url'] : '' );
			}
			if ( isset( $data['payment_gateways']['bank'] ) && is_array( $data['payment_gateways']['bank'] ) ) {
				$data['payment_gateways']['bank']['status'] = sanitize_text_field( $data['payment_gateways']['bank']['status'] );
			}
			if ( isset( $data['payment_gateways']['stripe'] ) && is_array( $data['payment_gateways']['stripe'] ) ) {
				$data['payment_gateways']['stripe']['status']     = sanitize_text_field( $data['payment_gateways']['stripe']['status'] );
				$data['payment_gateways']['stripe']['product_id'] = sanitize_text_field( $product_id );
				$data['payment_gateways']['stripe']['price_id']   = sanitize_text_field( $price_id );
			}
		}

		if ( isset( $data['upgrade_settings'] ) && ! empty( $data['upgrade_settings']['upgrade_action'] ) ) {
			$data['upgrade_settings']['upgrade_action'] = absint( $data['upgrade_settings']['upgrade_action'] );
			if ( isset( $data['upgrade_settings']['upgrade_path'] ) && is_array( $data['upgrade_settings']['upgrade_path'] ) ) {
				$data['upgrade_settings']['upgrade_path'] = sanitize_text_field( implode( ',', $data['upgrade_settings']['upgrade_path'] ) );
			} elseif ( isset( $data['upgrade_settings']['upgrade_path'] ) ) {
				$data['upgrade_settings']['upgrade_path'] = sanitize_text_field( $data['upgrade_settings']['upgrade_path'] );
			}
			$data['upgrade_settings']['upgrade_type'] = ! empty( $data['upgrade_settings']['upgrade_type'] ) ? sanitize_text_field( $data['upgrade_settings']['upgrade_type'] ) : 'full';
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

		if ( isset( $data['post_meta_data']['type'] ) && 'subscription' === $data['post_meta_data']['type'] && ! ( UR_PRO_ACTIVE ) ) {
			$result['status']  = false;
			$result['message'] = esc_html__( 'Subscription type is a paid feature.', 'user-registration' );

			return $result;
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

		return ( is_array( $membership ) && ! empty( $membership['meta_value'] ) ) ? wp_unslash( json_decode( $membership['meta_value'], true ) ) : array();
	}

	public function verify_page_content( $type, $post_id ) {
		$response = array(
			'status' => true,
		);
		$post     = get_post( $post_id );
		if ( empty( $post ) ) {
			$response['status']  = false;
			$response['message'] = __( 'No page selected.', 'user-registration' );

			return $response; // return since the post does not exist;
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
		$form_id                 = 0;

		if ( preg_match_all( '/\[user_registration_form\s+id="(\d+)"\]/', $post->post_content, $matches ) ) {
			$form_id = ! empty( $matches[1][0] ) ? absint( $matches[1][0] ) : 0;
		}

		if ( ! $form_id && preg_match( '/<!--\s*\/wp:user-registration\/registration-form\s*-->/', $post->post_content ) ) {
			$form_id = ! empty( $matches[1][0] ) ? absint( $matches[1][0] ) : 0;
			return $response;
		}

		if ( ! $form_id ) {
			$response['status']  = false;
			$response['message'] = __( 'The selected page does not consist any User Registration & Membership Form.' );
			return $response;
		}

		$fields = ur_get_form_fields( $matches[1][0] );
		foreach ( $fields as $k => $field ) {
			if ( 'membership' === $field->field_key ) {
				$membership_field_exists = true;
			}
		}
		$response['status']  = $membership_field_exists;
		$response['message'] = ! $membership_field_exists ? __( 'The selected page consist a User Registration & Membership Form but no membership field.' ) : '';
		$response['disable_save_btn'] = 'no';

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
			'status' => 'true',
		);
		switch ( $data[0] ) {
			case 'paypal':
				$paypal_service = new PaypalService();
				$result         = $paypal_service->validate_setup( $data[1] );
				break;
			case 'stripe':
				$stripe_service = new StripeService();
				$result         = $stripe_service->validate_setup();
				break;
			default:
				$bank_enabled     = get_option( 'user_registration_bank_enabled', '' );
				$bank_default     = ur_string_to_bool( get_option( 'urm_is_new_installation', false ) );
				$has_user_changed = ur_string_to_bool( get_option( 'urm_bank_updated_connection_status', false ) );

				$is_bank_enabled = $bank_enabled ? $bank_enabled : ( $has_user_changed ? $bank_enabled : ! $bank_default );

				$result = empty( get_option( 'user_registration_global_bank_details' ) ) || ! $is_bank_enabled;
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
			$response['message'] = __( 'Incomplete ' . ucfirst( $data[0] ) . ' setup.', 'user-registration' );
		}

		return $response;
	}

	public function get_upgradable_membership( $membership_id ) {
		$membership_group_repository = new MembershipGroupRepository();
		$membership_details          = $this->get_membership_details( $membership_id );
		$group_details               = $membership_group_repository->get_membership_group_by_membership_id( $membership_id );
		$memberships                 = array();
		$group_mode                  = '';

		if ( ! empty( $group_details ) ) {
			if ( isset( $group_details['mode'] ) && 'upgrade' === $group_details['mode'] ) {
				$group_mode = 'upgrade';
				if ( isset( $group_details['upgrade_path'] ) && '' !== $group_details['upgrade_path'] ) {
					$upgrade_paths = json_decode( $group_details['upgrade_path'], true );

					if ( isset( $upgrade_paths[ $membership_id ] ) && ! empty( $upgrade_paths[ $membership_id ] ) ) {
						$upgradeable_memberships    = $upgrade_paths[ $membership_id ];
						$upgradeable_membership_ids = array_map(
							function ( $upgradeable_memberships ) {
								return $upgradeable_memberships['membership_id'];
							},
							$upgradeable_memberships
						);

						$memberships = $this->membership_repository->get_multiple_membership_by_ID( implode( ',', $upgradeable_membership_ids ) );
					}
				}
			} elseif ( isset( $group_details['mode'] ) && 'multiple' === $group_details['mode'] ) {
				$group_mode = 'multiple';
				return array();
			}
		}

		if ( empty( $memberships ) && empty( $group_mode ) ) {
			if ( ! empty( $membership_details['upgrade_settings'] ) && $membership_details['upgrade_settings']['upgrade_action'] ) {
				$memberships = $this->membership_repository->get_multiple_membership_by_ID( $membership_details['upgrade_settings']['upgrade_path'] );
			}
		}

		if ( ! empty( $memberships ) ) {
			return apply_filters( 'build_membership_list_frontend', $memberships );
		}

		return array();
	}

	/**
	 * create or update a membership using prepared post data.
	 *
	 * @since x.x.x
	 *
	 * @param array      $membership_data Membership data (same structure expected by prepare_membership_post_data).
	 * @param array|null $rule_data       Optional access rule data for content restriction.
	 *
	 * @return int|\WP_Error Membership post ID on success, WP_Error on failure.
	 */
	public function create_membership( $membership_data, $rule_data = null ) {
		$data = $this->prepare_membership_post_data( $membership_data );

		if ( isset( $data['status'] ) && ! $data['status'] ) {
			$message = isset( $data['message'] ) ? $data['message'] : __( 'Invalid membership data.', 'user-registration' );
			return new \WP_Error( 'ur_membership_invalid_data', $message );
		}

		$data             = apply_filters( 'ur_membership_after_create_membership_data_prepare', $data );
		$is_stripe_active = isset( $data['post_meta_data']['payment_gateways']['stripe'] ) && 'on' === $data['post_meta_data']['payment_gateways']['stripe']['status'];

		$new_membership_id = wp_insert_post( $data['post_data'], true );
		if ( is_wp_error( $new_membership_id ) ) {
			return $new_membership_id;
		}
		if ( ! $new_membership_id ) {
			return new \WP_Error( 'ur_membership_insert_failed', __( 'Failed to create membership.', 'user-registration' ) );
		}

		if ( ! empty( $data['post_meta_data'] ) ) {
			foreach ( $data['post_meta_data'] as $datum ) {
				add_post_meta( $new_membership_id, $datum['meta_key'], $datum['meta_value'] );
			}
		}

		if ( $is_stripe_active && ! empty( $data['post_meta_data']['ur_membership']['meta_value'] ) ) {
			$meta_data = json_decode( $data['post_meta_data']['ur_membership']['meta_value'], true );
			if ( isset( $meta_data['type'] ) && 'free' !== $meta_data['type'] ) {
				$stripe_service           = new StripeService();
				$args                     = $data;
				$args['membership_id']    = $new_membership_id;
				$stripe_price_and_product = $stripe_service->create_stripe_product_and_price( $args['post_data'], $meta_data, false );

				if ( ! empty( $stripe_price_and_product['success'] ) && $stripe_price_and_product['success'] ) {
					$meta_data['payment_gateways']['stripe']['product_id'] = $stripe_price_and_product['price']->product;
					$meta_data['payment_gateways']['stripe']['price_id']   = $stripe_price_and_product['price']->id;
					update_post_meta( $new_membership_id, $data['post_meta_data']['ur_membership']['meta_key'], wp_json_encode( $meta_data ) );
				}
			}
		}

		if ( $rule_data && function_exists( 'urcr_create_or_update_membership_rule' ) ) {
			urcr_create_or_update_membership_rule( $new_membership_id, $rule_data );
		}

		return $new_membership_id;
	}


	public function delete_membership( $membership_id ) {
		$response                  = array(
			'status' => true,
		);
		$valid_membership_statuses = apply_filters(
			'urm_valid_membership_statuses',
			array(
				__( 'active', 'user-registration' ),
				__( 'pending', 'user-registration' ),
				__( 'trial', 'user-registration' ),
			)
		);
		$active_members            = $this->membership_repository->check_deletable_membership( $membership_id, $valid_membership_statuses );
		if ( $active_members['total'] > 0 ) {
			$html  = '<p>' . __( 'You cannot delete a membership plan if it has active users assigned to it with any of the following statuses:', 'user-registration' ) . '</p>';
			$html .= '<ul>';
			foreach ( $valid_membership_statuses as $status ) {
				$html .= '<li>' . ucfirst( $status ) . '</li>';
			}
			$html .= '</ul >';
			$html .= '<p >' . __( 'To proceed, update all memberships to Expired or Cancelled, or assign users to a different plan . Once no active users remain, the plan can be deleted', 'user-registration' ) . '</p > ';

			$response = array(
				'status'  => false,
				'message' => $html,
			);
		} else {
			$this->membership_repository->delete( $membership_id );
		}

		return $response;
	}

	public function prepare_single_membership_data(
		$membership
	) {
		$membership_post_content = json_decode( wp_unslash( $membership['post_content'] ), true );
		if ( ! $membership_post_content['status'] ) {
			return array();
		}
		$membership['post_content'] = $membership_post_content;
		if ( isset( $membership['meta_value'] ) ) {
			$membership['meta_value'] = json_decode( wp_unslash( $membership['meta_value'] ), true );
		}

		return $membership;
	}

	/**
	 * Fetch Membership details from intended actions like upgrade or purchase multiple.
	 *
	 * @param array $data Intended actions data.
	 */
	public function fetch_membership_details_from_intended_actions( $data ) {
		$subscription_repository     = new SubscriptionRepository();
		$upgrade_service             = new UpgradeMembershipService();
		$members_order_repository    = new MembersOrderRepository();
		$orders_repository           = new OrdersRepository();
		$members_repository          = new MembersRepository();
		$membership_repository       = new MembershipRepository();
		$members_subscription_repo   = new MembersSubscriptionRepository();
		$subscription_service        = new SubscriptionService();
		$membership_group_repository = new MembershipGroupRepository();
		$membership_group_service    = new MembershipGroupService();

		$subscription_id       = absint( $data['subscription_id'] ?? 0 );
		$memberships           = array();
		$current_membership_id = absint( $data['current'] ?? 0 );

		$current_user_id     = get_current_user_id();
		$user_memberships    = array();
		$user_membership_ids = array();

		if ( $current_user_id ) {

			$user_memberships = $members_repository->get_member_membership_by_id( $current_user_id );

			$user_membership_ids = array_filter(
				array_map(
					function ( $user_memberships ) {
						return $user_memberships['post_id'];
					},
					$user_memberships
				)
			);
		}

		if ( isset( $data['action'] ) && 'upgrade' === $data['action'] ) {

			$member_id  = get_current_user_id();
			$last_order = $members_order_repository->get_member_orders( $member_id );

			if ( ! empty( $last_order ) ) {
				$order_meta = $orders_repository->get_order_metas( $last_order['ID'] );
				if ( ! empty( $order_meta ) ) {
					$upcoming_subscription = json_decode( get_user_meta( $member_id, 'urm_next_subscription_data', true ), true );
					$membership            = get_post( $upcoming_subscription['membership'] );
					$message               = apply_filters( 'urm_delayed_plan_exist_notice', __( sprintf( 'You already have a scheduled upgrade to the <b>%s</b> plan at the end of your current subscription cycle (<i><b>%s</b></i>) <br> If you\'d like to cancel this upcoming change, please proceed from my account page.', $membership->post_title, date( 'M d, Y', strtotime( $order_meta['meta_value'] ) ) ), 'user-registration' ), $membership->post_title, $order_meta['meta_value'] );

					return array(
						'status'  => false,
						'message' => $message,
					);
				}
			}

			// Checkout page for logged in user to upgrade membership.
			if ( isset( $data['current'] ) && '' !== $data['current'] ) {

				$membership_process = urm_get_membership_process( $member_id );
				if ( $membership_process && isset( $membership_process['upgrade'][ $current_membership_id ] ) ) {
					$current_membership       = $membership_repository->get_single_membership_by_ID( $current_membership_id );
						$current_plan_name    = $current_membership['post_title'] ?? '';
						$initiated_membership = $membership_repository->get_single_membership_by_ID( $membership_process['upgrade'][ $current_membership_id ]['to'] ?? 0 );
						$initiated_plan_name  = $initiated_membership['post_title'] ?? '';

						return array(
							'status'  => false,
							'message' => sprintf( esc_html__( 'You already have a membership plan upgrade initiated from %1$s to %2$s. Please complete the process and try again.', 'user-registration' ), $current_plan_name, $initiated_plan_name ),
						);
				}

				$memberships = $this->get_upgradable_membership( $current_membership_id );
				$memberships = array_filter(
					$memberships,
					function ( $membership ) use ( $user_membership_ids ) {
						return ! in_array( $membership['ID'], $user_membership_ids );
					}
				);

				if ( empty( $memberships ) ) {
					return array(
						'status'  => false,
						'message' => esc_html__( 'You aren’t eligible to upgrade to this membership tier. Please contact site administrator', 'user-registration' ),
					);
				}
			} else {
				$intended_membership_id = isset( $data['membership_id'] ) ? absint( $data['membership_id'] ) : 0;
				$user_membership_id     = 0;

				foreach ( $user_memberships as $membership ) {
					$current_upgradable_memberships     = $this->get_upgradable_membership( $membership['post_id'] );
					$current_upgradable_memberships_ids = array_filter(
						array_map(
							function ( $current_upgradable_memberships ) {
								return $current_upgradable_memberships['ID'];
							},
							$current_upgradable_memberships
						)
					);

					if ( in_array( $intended_membership_id, $user_membership_ids ) ) {
						return array(
							'status'  => false,
							'message' => esc_html__( 'You already have purchased this membership plan.', 'user-registration' ),
						);
					}

					if ( in_array( $intended_membership_id, $current_upgradable_memberships_ids ) ) {
						$user_membership_id  = $membership['post_id'];
						$member_subscription = $members_subscription_repo->get_subscription_data_by_member_and_membership_id( $current_user_id, $user_membership_id );
						$subscription_id     = $member_subscription['ID'] ?? 0;
						break;
					}
				}

				if ( $user_membership_id ) {
					$current_membership_id = $user_membership_id;

					$membership_process = urm_get_membership_process( $member_id );
					if ( $membership_process && isset( $membership_process['upgrade'][ $current_membership_id ] ) ) {

						$current_membership   = $membership_repository->get_single_membership_by_ID( $current_membership_id );
						$current_plan_name    = $current_membership['post_title'] ?? '';
						$initiated_membership = $membership_repository->get_single_membership_by_ID( $membership_process['upgrade'][ $current_membership_id ]['to'] ?? 0 );
						$initiated_plan_name  = $initiated_membership['post_title'] ?? '';

						return array(
							'status'  => false,
							'message' => sprintf( esc_html__( 'You already have a membership plan upgrade initiated from %1$s to %2$s. Please complete the process and try again.', 'user-registration' ), $current_plan_name, $initiated_plan_name ),
						);
					}

					$memberships = $membership_repository->get_multiple_membership_by_ID( $intended_membership_id );
					$memberships = apply_filters( 'build_membership_list_frontend', $memberships );
				} else {
					return array(
						'status'  => false,
						'message' => esc_html__( 'You aren’t eligible to upgrade to this membership tier. Please contact site administrator.', 'user-registration' ),
					);
				}
			}

			$current_membership_details       = $this->get_membership_details( $current_membership_id );
			$current_membership_details['ID'] = $current_membership_id;
			$subscription                     = $subscription_repository->retrieve( $subscription_id );

			foreach ( $memberships as $key => &$membership ) {
				$membership_group = $membership_group_repository->get_membership_group_by_membership_id( $membership['ID'] );
				if ( ! empty( $membership_group ) && isset( $membership_group['ID'] ) ) {
					$multiple_allowed = $membership_group_service->check_if_multiple_memberships_allowed( $membership_group['ID'] );

					if ( $multiple_allowed ) {
						unset( $memberships[ $key ] );
						continue;
					}
				}

				$selected_membership_details       = $this->get_membership_details( $membership['ID'] );
				$selected_membership_details['ID'] = $membership['ID'];

				$upgrade_details = $subscription_service->calculate_membership_upgrade_cost( $current_membership_details, $selected_membership_details, $subscription );

				$selected_membership_amount = $selected_membership_details['amount'];
				$current_membership_amount  = $current_membership_details['amount'];

				$upgrade_service = new UpgradeMembershipService();

				$upgrade_data = $upgrade_service->get_upgrade_details( $current_membership_details );
				$upgrade_type = ! empty( $upgrade_data['upgrade_type'] ) ? $upgrade_data['upgrade_type'] : '';

				if ( empty( $upgrade_type ) || empty( $upgrade_data['upgrade_path'] ) ) {
					return array(
						'status'  => false,
						'message' => esc_html__( 'Membership upgrade is not enabled for this plan.', 'user-registration' ),
					);
				}

				$remaining_subscription_value = isset( $selected_membership_details['subscription']['value'] ) ? $selected_membership_details['subscription']['value'] : '';
				$delayed_until                = '';

				if ( $subscription_service->is_user_membership_expired( $current_user_id, $current_membership_id ) ) {
					$chargeable_amount    = $upgrade_service->calculate_chargeable_amount(
						$selected_membership_amount,
						$current_membership_amount,
						$upgrade_type
					);
					$membership['amount'] = $chargeable_amount;
				}
			}
			unset( $membership );
		} elseif ( isset( $data['action'] ) && 'multiple' === $data['action'] ) {
			if ( UR_PRO_ACTIVE && urm_check_if_plus_and_above_plan() && ur_check_module_activation( 'membership-groups' ) ) {
				$membership_id    = isset( $data['membership_id'] ) ? absint( $data['membership_id'] ) : 0;
				$membership_group = $membership_group_repository->get_membership_group_by_membership_id( $membership_id );

				// If current membership is associated with a group then check if multiple can be purchased.
				if ( ! empty( $membership_group ) ) {
					$multiple_allowed = false;

					if ( $current_user_id ) {

						if ( in_array( $membership_id, $user_membership_ids ) ) {
							return array(
								'status'  => false,
								'message' => esc_html__( 'You already have purchased this membership plan.', 'user-registration' ),
							);
						} else {

							$group_diff = array_diff( $user_membership_ids, json_decode( $membership_group['memberships'] ) );
							$overlap    = array_intersect( $user_membership_ids, json_decode( $membership_group['memberships'] ) );
							if ( ! $overlap ) {
								$multiple_allowed = true;
							} else {

								// Check if current user membership
								if ( count( $group_diff ) < $user_membership_ids ) {
									$multiple_allowed = $membership_group_service->check_if_multiple_memberships_allowed( $membership_group['ID'] );
								} else {
									$multiple_allowed = true;
								}
							}
						}
					}

					if ( $multiple_allowed ) {
						$memberships = $membership_repository->get_single_membership_by_ID( $membership_id );
						$memberships = $this->prepare_single_membership_data( $memberships );
						$memberships = apply_filters( 'build_membership_list_frontend', array( (array) $memberships ) )[0];
						$memberships = array( $memberships );
					} else {
						return array(
							'status'  => false,
							'message' => esc_html__( 'You cannot purchase this membership.', 'user-registration' ),
						);
					}
				} else {
					// If current membership is not associated with any group then users are not allowed to buy membership.
					return array(
						'status'  => false,
						'message' => esc_html__( 'You cannot purchase this membership.', 'user-registration' ),
					);
				}
			} else {
				// If pro version and multiple membership module is not active then users are not allowed to buy membership.
				return array(
					'status'  => false,
					'message' => esc_html__( 'You cannot purchase this membership.', 'user-registration' ),
				);
			}
		} elseif ( isset( $data['action'] ) && 'register' === $data['action'] ) {
			$membership_id = isset( $data['membership_id'] ) ? absint( $data['membership_id'] ) : 0;
			$memberships   = $membership_repository->get_single_membership_by_ID( $membership_id );
			$memberships   = $this->prepare_single_membership_data( $memberships );
			$memberships   = apply_filters( 'build_membership_list_frontend', array( (array) $memberships ) )[0];
			$memberships   = array( $memberships );
		} elseif ( isset( $data['action'] ) && 'renew' === $data['action'] ) {
			$membership_id = isset( $data['current'] ) ? absint( $data['current'] ) : 0;
			$memberships   = $membership_repository->get_single_membership_by_ID( $membership_id );
			$memberships   = $this->prepare_single_membership_data( $memberships );
			$memberships   = apply_filters( 'build_membership_list_frontend', array( (array) $memberships ) )[0];
			$memberships   = array( $memberships );
		}

		if ( empty( $memberships ) ) {
			return array(
				'status'  => false,
				'message' => esc_html__( 'Selected membership details not found. Please contact your site administrator.', 'user-registration' ),
			);
		} else {
			return array(
				'status'                  => true,
				'memberships'             => $memberships,
				'current_subscription_id' => $subscription_id,
				'current_membership_id'   => $current_membership_id,
			);
		}
	}

	/**
	 * Fetch intended action from details.
	 *
	 * @param string $intended_action Intended action.
	 * @param array  $membership Membership Details.
	 * @param array  $user_membership_ids User Membership ID.
	 */
	public function fetch_intended_action( $intended_action, $membership, $user_membership_ids ) {

		$membership_group_repository = new MembershipGroupRepository();
		$membership_group_service    = new MembershipGroupService();
		$current_membership_group    = $membership_group_repository->get_membership_group_by_membership_id( $membership['ID'] );
		$user_membership_group_ids   = array();

		if ( empty( $user_membership_ids ) ) {
			return 'register';
		}

		foreach ( $user_membership_ids as $user_membership_id ) {
			$user_membership_group_id = $membership_group_repository->get_membership_group_by_membership_id( $user_membership_id );

			if ( isset( $user_membership_group_id['ID'] ) ) {
				$user_membership_group_ids[] = $user_membership_group_id['ID'];
			}
		}

		$user_membership_group_ids = array_values( array_unique( $user_membership_group_ids ) );

		if ( is_user_logged_in() ) {

			if ( ! empty( $current_membership_group ) ) {

				if ( in_array( $current_membership_group['ID'], $user_membership_group_ids ) ) {
					foreach ( $user_membership_group_ids as $group_id ) {
						if ( $current_membership_group['ID'] === $group_id ) {
							$multiple_memberships_allowed = $membership_group_service->check_if_multiple_memberships_allowed( $current_membership_group['ID'] );
							$upgrade_allowed              = $membership_group_service->check_if_upgrade_allowed( $current_membership_group['ID'] );

							if ( $multiple_memberships_allowed ) {
								$intended_action = 'multiple';
							} elseif ( $upgrade_allowed ) {
								$intended_action = 'upgrade';
							}
						}
					}
				} else {
					$intended_action = 'multiple';
				}
			} else {
				$intended_action = 'upgrade';
			}
		} else {
			$intended_action = 'register';
		}

		return $intended_action;
	}


	/**
	 * Retrieve the membership title and description.
	 *
	 * Fetches the post title and the `ur_membership_description` post meta
	 * for the given membership post ID.
	 *
	 * @param int $membership_id The membership post ID.
	 *
	 * @return array {
	 *     An associative array containing the membership details.
	 *
	 *     @type string $item_title       The membership post title.
	 *     @type string $item_description The membership description meta value.
	 * }
	 */
	public function get_membership_title_and_description( $membership_id ) {
		$membership             = get_post( $membership_id );
		$membership_title       = $membership->post_title;
		$membership_description = get_post_meta( $membership_id, 'ur_membership_description', true );
		return array(
			'item_title'       => $membership_title,
			'item_description' => $membership_description,
		);
	}
}
