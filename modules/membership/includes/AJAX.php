<?php
/**
 * URMembership AJAX
 *
 * AJAX Event Handler
 *
 * @class    AJAX
 * @package  URMembership/Ajax
 * @category Class
 * @author   WPEverest
 */

namespace WPEverest\URMembership;

use WPEverest\URMembership\Admin\Controllers\MembersController;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Services\CouponService;
use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\Admin\Services\MembersService;
use WPEverest\URMembership\Admin\Services\PaymentService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;
use WPEverest\URMembership\Admin\Services\SubscriptionService;
use WPEverest\URMembership\Admin\Services\OrderService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Class
 */
class AJAX {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'create_membership'            => false,
			'update_membership'            => false,
			'delete_memberships'           => false,
			'delete_membership'            => false,
			'update_membership_status'     => false,
			'create_member'                => false,
			'update_member'                => false,
			'delete_members'               => false,
			'confirm_payment'              => true,
			'create_stripe_subscription'   => true,
			'register_member'              => true,
			'validate_coupon'              => true,
			'cancel_subscription'          => false,
			'reactivate_membership'		   => false,
			'renew_membership'             => false,
			'cancel_upcoming_subscription' => false,
			'fetch_upgradable_memberships' => false,
			'get_group_memberships'        => false,
			'create_membership_group'      => false,
			'delete_membership_groups'     => false,
			'verify_pages'                 => false,
			'validate_pg'                  => false,
			'upgrade_membership'           => false,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_user_registration_membership_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_user_registration_membership_' . $ajax_event,
					array(
						__CLASS__,
						$ajax_event,
					)
				);
			}
		}
	}

	/**
	 * Register user from frontend
	 *
	 * @return void
	 */
	public static function register_member() {

		ur_membership_verify_nonce( 'ur_members_frontend' ); // nonce verification.
		if ( ! isset( $_POST['members_data'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field members data is required.', 'user-registration' ),
				)
			);
		}

		$data = apply_filters( 'user_registration_membership_before_register_member', isset( $_POST['members_data'] ) ? (array) json_decode( wp_unslash( $_POST['members_data'] ), true ) : array() );


		if ( ! isset( $data['username'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field username is required.', 'user-registration' ),
				)
			);
		}
		$member          = get_user_by( 'login', $data['username'] );
		$member_id       = $member->ID;
		$is_just_created = get_user_meta( $member_id, 'urm_user_just_created', true );

		if ( ! $is_just_created ) {
			wp_send_json_error(
				array(
					'message' => __( "User already exists.", "user-registration" ),
				)
			);
		}
		if ( empty( $data['payment_method'] ) ) {
			wp_delete_user( $member_id );
			wp_send_json_error(
				array(
					'message' => __( "Payment method is required.", "user-registration" ),
				)
			);
		}
		$membership_service = new MembershipService();

		$response = $membership_service->create_membership_order_and_subscription( $data );

		$transaction_id          = isset( $response['transaction_id'] ) ? $response['transaction_id'] : 0;
		$data['member_id']       = $member_id;
		$data['subscription_id'] = isset( $response['subscription_id'] ) ? $response['subscription_id'] : 0;
		$data['email']           = $response['member_email'];
		$pg_data                 = array();
		if ( 'free' !== $data['payment_method'] && $response['status'] ) {
			$payment_service = new PaymentService( $data['payment_method'], $data['membership'], $data['email'] );

			$form_response    = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();
			$ur_authorize_net = array( 'ur_authorize_net' => ! empty ( $form_response['ur_authorize_net'] ) ? $form_response['ur_authorize_net'] : [] );
			$data             = array_merge( $data, $ur_authorize_net );
			$pg_data          = $payment_service->build_response( $data );
		}

		if ( $response['status'] ) {
			$form_response = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();

			if ( ! empty( $form_response ) && isset( $form_response["auto_login"] ) && $form_response["auto_login"] && 'free' == $data['payment_method'] ) {
				$members_service = new MembersService();
				$logged_in       = $members_service->login_member( $member_id, true );
				if ( ! $logged_in ) {
					wp_send_json_error(
						array(
							'message' => __( "Invalid User", "user-registration" ),
						)
					);
				}
			}
			$email_service = new EmailService();
			$email_service->send_email( $data, 'user_register_user' );
			$email_service->send_email( $data, 'user_register_admin' );

			$response = apply_filters(
				'user_registration_membership_after_register_member',
				array(
					'member_id'      => absint( $member_id ),
					'transaction_id' => esc_html( $transaction_id ),
					'message'        => esc_html__( 'New member has been successfully created.', 'user-registration' ),
				)
			);
			if ( 'free' !== $data['payment_method'] ) {
				$response['pg_data'] = $pg_data;
			}
			if ( in_array( $data['payment_method'], array( 'free', 'bank' ) ) ) {
				delete_user_meta( $member_id, 'urm_user_just_created' );
			}
			wp_send_json_success( $response );
		} else {
			$message = isset( $response['message'] ) ? $response['message'] : esc_html__( 'Sorry! There was an unexpected error while registering the user . ', 'user-registration' );
			wp_send_json_error(
				array(
					'message' => $message,
				)
			);
		}
	}

	/**
	 * Create membership from backend
	 *
	 * @return void
	 */
	public static function create_membership() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to create membership', 'user-registration' ),
				)
			);
		}
		// Developers can override the verify_nonce function.
		ur_membership_verify_nonce( 'ur_membership' );
		$membership        = new MembershipService();
		$data              = isset( $_POST['membership_data'] ) ? (array) json_decode( wp_unslash( $_POST['membership_data'] ), true ) : array();
		$is_stripe_enabled = isset( $data['post_meta_data']['payment_gateways']['stripe'] ) && "on" === $data['post_meta_data']['payment_gateways']['stripe']["status"];
		$data              = $membership->prepare_membership_post_data( $data );

		if ( isset( $data['status'] ) && ! $data['status'] ) {
			wp_send_json_error(
				array(
					'message' => $data['message'],
				)
			);
		}

		$data = apply_filters( 'ur_membership_after_create_membership_data_prepare', $data );

		$new_membership_ID = wp_insert_post( $data['post_data'] );

		if ( $new_membership_ID ) {
			if ( ! empty( $data['post_meta_data'] ) ) {
				foreach ( $data['post_meta_data'] as $datum ) {
					add_post_meta( $new_membership_ID, $datum['meta_key'], $datum['meta_value'] );
				}
			}
			$meta_data = json_decode( $data["post_meta_data"]['ur_membership']["meta_value"], true );

			if ( $is_stripe_enabled && "free" !== $meta_data["type"] ) {
				$stripe_service           = new StripeService();
				$data["membership_id"]    = $new_membership_ID;
				$stripe_price_and_product = $stripe_service->create_stripe_product_and_price( $data["post_data"], $meta_data, false );

				if ( $stripe_price_and_product['success'] ) {
					$meta_data["payment_gateways"]["stripe"]["product_id"] = $stripe_price_and_product['price']->product;
					$meta_data["payment_gateways"]["stripe"]["price_id"]   = $stripe_price_and_product['price']->id;
					update_post_meta( $new_membership_ID, $data['post_meta_data']['ur_membership']['meta_key'], wp_json_encode( $meta_data ) );
				}
			}

			$response = array(
				'membership_id' => $new_membership_ID,
				'message'       => esc_html__( 'Successfully created the membership . ', 'user-registration' ),
			);

			$response = apply_filters( 'ur_membership_before_create_membership_response', $response );
			wp_send_json_success( $response );
		} else {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Sorry! There was an unexpected error while saving the membership data . ', 'user-registration' ),
				)
			);
		}
	}

	/**
	 * Update membership from backend
	 *
	 * @return void
	 */
	public static function update_membership() {
		if ( empty( $_POST['membership_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field membership_id is required.', 'user-registration' ),
				)
			);
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to edit membership', 'user-registration' ),
				)
			);
		}
		ur_membership_verify_nonce( 'ur_membership' );

		$membership        = new MembershipService();
		$data              = isset( $_POST['membership_data'] ) ? (array) json_decode( wp_unslash( $_POST['membership_data'] ), true ) : array();
		$is_stripe_enabled = isset( $data['post_meta_data']['payment_gateways']['stripe'] ) && "on" === $data['post_meta_data']['payment_gateways']['stripe']["status"];
		$is_mollie_enabled = isset( $data['post_meta_data']['payment_gateways']['mollie'] ) && "on" === $data['post_meta_data']['payment_gateways']['mollie']['status'];


		$data = $membership->prepare_membership_post_data( $data );

		if ( isset( $data['status'] ) && ! $data['status'] ) {
			wp_send_json_error(
				array(
					'message' => $data['message'],
				)
			);
		}

		$data = apply_filters( 'ur_membership_after_create_membership_data_prepare', $data );

		$old_membership_data = $membership->get_membership_details( $_POST['membership_id'] );

		$updated_ID = wp_insert_post( $data['post_data'] );

		if ( $updated_ID ) {
			if ( ! empty( $data['post_meta_data'] ) ) {
				foreach ( $data['post_meta_data'] as $datum ) {
					update_post_meta( $updated_ID, $datum['meta_key'], $datum['meta_value'] );
				}
			}

			$meta_data = json_decode( $data["post_meta_data"]['ur_membership']["meta_value"], true );
			if ( ! empty( $meta_data['upgrade_settings'] ) && ! empty( $old_membership_data['upgrade_settings'] ) && $meta_data['upgrade_settings']['upgrade_path'] !== $old_membership_data['upgrade_settings']['upgrade_path'] ) {
				$transient_key          = 'urm_upgradable_memberships_for_' . $updated_ID;
				$upgradable_memberships = $membership->get_upgradable_membership( $updated_ID );
				set_transient( $transient_key, $upgradable_memberships, 5 * MINUTE_IN_SECONDS );
			}

			if ( $is_stripe_enabled && "free" !== $meta_data["type"] ) {

				//check if any significant value has been changed  , trial not included since trial value change does not affect the type of product and price in stripe, instead handled during subscription
				$should_create_new_product = ( $old_membership_data['amount'] !== $meta_data['amount'] || ( isset( $old_membership_data["subscription"] ) && $old_membership_data["subscription"]["value"] !== $meta_data["subscription"]["value"] ) || ( isset( $old_membership_data["subscription"] ) && $old_membership_data["subscription"]["duration"] !== $meta_data["subscription"]["duration"] ) );

				$meta_data = json_decode( $data["post_meta_data"]['ur_membership']["meta_value"], true );

				if ( $should_create_new_product || empty( $meta_data["payment_gateways"]["stripe"]["product_id"] ) ) {
					$stripe_service           = new StripeService();
					$data["membership_id"]    = $updated_ID;
					$stripe_price_and_product = $stripe_service->create_stripe_product_and_price( $data["post_data"], $meta_data, $should_create_new_product );

					if ( ur_string_to_bool( $stripe_price_and_product['success'] ) ) {
						$meta_data["payment_gateways"]["stripe"]["product_id"] = $stripe_price_and_product['price']->product;
						$meta_data["payment_gateways"]["stripe"]["price_id"]   = $stripe_price_and_product['price']->id;
						update_post_meta( $updated_ID, $data['post_meta_data']['ur_membership']['meta_key'], wp_json_encode( $meta_data ) );
					} else {
						wp_send_json_error(
							array(
								'message' => $stripe_price_and_product['message'],
							)
						);
					}
				}

			}
			$response = array(
				'membership_id' => $updated_ID,
				'message'       => esc_html__( 'Successfully updated the membership data.', 'user-registration' ),
			);

			$response = apply_filters( 'ur_membership_before_create_membership_response', $response );
			wp_send_json_success( $response );
		} else {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Sorry! There was an unexpected error while saving the membership data . ', 'user-registration' ),
				)
			);
		}

	}

	/**
	 * Delete multiple Memberships
	 *
	 * @return void
	 */
	public static function delete_membership() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission not allowed.', 'user-registration' ),
				),
				403
			);
		}

		ur_membership_verify_nonce( 'ur_membership' );
		if ( empty( $_POST['membership_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field membership_id is required.', 'user-registration' ),
				),
				422
			);
		}
		$membership_id = absint( $_POST['membership_id'] );

		$membership_service = new MembershipService();
		$deleted               = $membership_service->delete_membership( $membership_id );
		if ( $deleted["status"] ) {
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Membership deleted successfully.', 'user-registration' ),
				)
			);
		}
		wp_send_json_error(
			array(
				'message' =>  $deleted["message"] ,
			)
		);
	}

	/**
	 * Delete multiple Memberships
	 *
	 * @return void
	 */
	public static function delete_memberships() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission not allowed.', 'user-registration' ),
				),
				403
			);
		}
		ur_membership_verify_nonce( 'ur_membership' );
		if ( ! isset( $_POST ) && ! isset( $_POST['membership_ids'] ) && ! empty( $_POST['membership_ids'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field membership_ids is required.', 'user-registration' ),
				),
				422
			);
		}
		$membership_ids = wp_unslash( $_POST['membership_ids'] );
		$membership_ids = implode( ",", json_decode( $membership_ids, true ) );

		$membership_repository = new MembershipRepository();
		$deleted               = $membership_repository->delete_multiple( $membership_ids );
		if ( $deleted ) {
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Memberships deleted successfully.', 'user-registration' ),
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => esc_html__( 'Sorry! There was an unexpected error while deleting the memberships.', 'user-registration' ),
			)
		);
	}

	/**
	 * Delete multiple Memberships
	 *
	 * @return void
	 */
	public static function delete_membership_groups() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission not allowed.', 'user-registration' ),
				),
				403
			);
		}
		ur_membership_verify_nonce( 'ur_membership_group' );
		if ( ! isset( $_POST ) || empty( $_POST['membership_group_ids'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field membership_group_ids is required.', 'user-registration' ),
				),
				422
			);
		}
		$membership_group_ids = wp_unslash( $_POST['membership_group_ids'] );
		$membership_service   = new MembershipGroupService();
		$membership_group_ids = $membership_service->remove_form_related_groups( json_decode( $membership_group_ids, true ) );

		$membership_group_ids = implode( ",", $membership_group_ids );

		$membership_repository = new MembershipRepository();

		$deleted = $membership_repository->delete_multiple( $membership_group_ids );
		if ( $deleted ) {
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Memberships Groups deleted successfully.', 'user-registration' ),
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => esc_html__( 'Sorry! There was an unexpected error while deleting the membership groups.', 'user-registration' ),
			)
		);
	}

	/**
	 * Delete multiple Members
	 *
	 * @return void
	 */
	public static function delete_members() {
		if ( ! current_user_can( 'delete_users' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission not allowed.', 'user-registration' ),
				),
				403
			);
		}
		ur_membership_verify_nonce( 'ur_members' );
		if ( ! isset( $_POST ) && ! isset( $_POST['members_ids'] ) && ! empty( $_POST['members_ids'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field members_ids is required.', 'user-registration' ),
				),
				422
			);
		}
		$members_ids        = wp_unslash( $_POST['members_ids'] );
		$members_ids        = implode( ",", json_decode( $members_ids, true ) );
		$members_repository = new MembersRepository();
		$deleted            = $members_repository->delete_multiple( $members_ids );
		if ( $deleted ) {
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Members deleted successfully.', 'user-registration' ),
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => esc_html__( 'Sorry! There was an unexpected error while deleting the selected members.', 'user-registration' ),
			)
		);
	}

	/**
	 * Update just the membership status from list-table.
	 *
	 * @return void
	 */
	public static function update_membership_status() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to edit membership', 'user-registration' ),
				)
			);
		}
		ur_membership_verify_nonce( 'ur_membership' );

		$data                   = json_decode( wp_unslash( $_POST['membership_data'] ), true );
		$post                   = get_post( $data['ID'] );
		$post_content           = json_decode( wp_unslash( $post->post_content ), true );
		$post_content['status'] = $data['status'];
		unset( $data['status'] );
		$data['post_content'] = wp_json_encode( $post_content );
		$updated_ID           = wp_update_post( $data );
		if ( $updated_ID ) {
			wp_send_json_success(
				array(
					'membership_id' => $updated_ID,
					'message'       => esc_html__( 'Successfully updated the membership status . ', 'user-registration' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Sorry! There was an unexpected error while saving the membership data . ', 'user-registration' ),
				)
			);
		}
	}

	/**
	 * Create member from backend.
	 *
	 * @return void
	 */
	public static function create_member() {

		if ( ! current_user_can( 'add_users' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to create users', 'user-registration' ),
				)
			);
		}
		ur_membership_verify_nonce( 'ur_members' );
		$data               = isset( $_POST['members_data'] ) ? (array) json_decode( wp_unslash( $_POST['members_data'] ), true ) : array();
		$members_controller = new MembersController( new MembersRepository(), new OrdersRepository(), new SubscriptionRepository() );

		$response = $members_controller->create_members_admin( $data );

		if ( $response['status'] ) {
			wp_send_json_success(
				array(
					'member_id' => $response['member_id'],
					'message'   => esc_html__( 'New member has been successfully created. ', 'user-registration' ),
				)
			);
		} else {
			$message = isset( $response['message'] ) ? $response['message'] : esc_html__( 'Sorry! There was an unexpected error while saving the members data . ', 'user-registration' );
			wp_send_json_error(
				array(
					'message' => $message,
				)
			);
		}

	}

	/**
	 * Get coupon detail.
	 *
	 * @return void
	 */
	public static function validate_coupon() {
		ur_membership_verify_nonce( 'ur_members_frontend' );
		$data           = isset( $_POST['coupon_data'] ) ? (array) wp_unslash( $_POST['coupon_data'] ) : array();
		$coupon_service = new CouponService();

		$response = $coupon_service->validate( $data );

		if ( $response['status'] ) {
			wp_send_json_success(
				array(
					'message' => $response['message'],
					'data'    => $response['data'],
				),
				$response['code']
			);
		}
		wp_send_json_error(
			array(
				'message' => $response['message'],
			),
			$response['code']
		);
	}

	public static function confirm_payment() {

		ur_membership_verify_nonce( 'urm_confirm_payment' );

		if ( empty( $_POST['member_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field member_id is required', 'user-registration' ),
				)
			);
		}
		if ( empty( $_POST['form_response'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field form_response is required', 'user-registration' ),
				)
			);
		}

		$member_id       = absint( $_POST['member_id'] );
		$is_user_created = get_user_meta( $member_id, 'urm_user_just_created' );
		$is_upgrading    = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_upgrading', true ) );
		$is_renewing     = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_member_renewing', true ) );
		if ( ! $is_user_created && ! $is_upgrading && ! $is_renewing ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid Request.', 'user-registration' ),
				)
			);
		}
		if ( is_user_logged_in() && ! current_user_can( 'edit_user', $member_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to edit this user.', 'user-registration' ),
				)
			);
		}
		$stripe_service      = new StripeService();
		$payment_status      = sanitize_text_field( $_POST['payment_status'] );
		$is_renewing         = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_member_renewing', true ) );

		$update_stripe_order = $stripe_service->update_order( $_POST );

		if ( $update_stripe_order['status'] ) {

			if ( $is_upgrading ) {
				$next_subscription     = json_decode( get_user_meta( $member_id, 'urm_next_subscription_data', true ), true );
				$previous_subscription = get_user_meta( $member_id, 'urm_previous_subscription_data', true );
				$is_delayed            = ! empty( $next_subscription['delayed_until'] );
				if ( ! empty( $previous_subscription ) && ! $is_delayed ) {
					$previous_subscription = json_decode( $previous_subscription, true );
					$stripe_service        = new StripeService();
					$stripe_service->cancel_subscription( array(), $previous_subscription );
					delete_user_meta( $member_id, 'urm_next_subscription_data' );
					delete_user_meta( $member_id, 'urm_previous_subscription_data' );
					delete_user_meta( $member_id, 'urm_previous_order_data' );
				}
			}

			$form_response = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();
			if ( ! empty( $form_response ) && isset( $form_response["auto_login"] ) && $payment_status !== "failed" ) {
				$members_service = new MembersService();
				$logged_in       = $members_service->login_member( $member_id, true );
				if ( ! $logged_in ) {
					wp_send_json_error(
						array(
							'message' => isset( $update_stripe_order["message"] ) ? $update_stripe_order["message"] : __( "Something went wrong when updating users payment status" )
						),
						500
					);
				}
			}

			delete_user_meta( $member_id, 'urm_user_just_created' );
			$response = array(
				'message'      => $update_stripe_order["message"],
				'is_upgrading' => ur_string_to_bool( $is_upgrading ),
				'is_renewing'  => ur_string_to_bool( $is_renewing )
			);
			if ( $is_upgrading ) {
				$response['message'] = __( "Membership upgraded successfully", "user-registration" );
				delete_user_meta( $member_id, 'urm_is_upgrading' );
				delete_user_meta( $member_id, 'urm_is_upgrading_to' );
				update_user_meta( $member_id, 'urm_is_user_upgraded', 1 );
			}
			if ( $is_renewing ) {
				$response['message']            = __( "Membership has been successfully renewed.", "user-registration" );
				$subscription_service           = new SubscriptionService();
				$members_subscription_repo      = new MembersSubscriptionRepository();
				$membership_repository          = new MembershipRepository();
				$member_subscription            = $members_subscription_repo->get_member_subscription( $member_id );
				$membership                     = $membership_repository->get_single_membership_by_ID( $member_subscription['item_id'] );
				$membership_metas               = wp_unslash( json_decode( $membership['meta_value'], true ) );
				$membership_metas['post_title'] = $membership['post_title'];
				$subscription_service->update_subscription_data_for_renewal( $member_subscription, $membership_metas );
			}
			wp_send_json_success(
				$response
			);
		}
		if ( $is_upgrading ) {
			$stripe_service->revert_subscription( $member_id );
		}
		wp_send_json_error(
			array(
				'message' => isset( $update_stripe_order["message"] ) ? $update_stripe_order["message"] : __( "Something went wrong when updating users payment status" )
			),
			500
		);

	}

	public static function create_stripe_subscription() {
		ur_membership_verify_nonce( 'urm_confirm_payment' );
		$customer_id       = isset( $_POST['customer_id'] ) ? $_POST['customer_id'] : '';
		$payment_method_id = isset( $_POST['payment_method_id'] ) ? sanitize_text_field( $_POST['payment_method_id'] ) : '';
		$member_id         = absint( wp_unslash( $_POST['member_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$is_upgrading      = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_upgrading', true ) );
		$is_renewing       = ur_string_to_bool( get_user_meta( $member_id, 'urm_is_member_renewing', true ) );
		$is_user_created   = get_user_meta( $member_id, 'urm_user_just_created' );
		if ( ! $is_user_created && ! $is_upgrading && ! $is_renewing ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid Request.', 'user-registration' ),
				)
			);
		}
		if ( is_user_logged_in() && ! current_user_can( 'edit_user', $member_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to edit this user.', 'user-registration' ),
				)
			);
		}
		$stripe_service      = new StripeService();
		$form_response       = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();
		$stripe_subscription = $stripe_service->create_subscription( $customer_id, $payment_method_id, $member_id, $is_upgrading );

		if ( $stripe_subscription['status'] ) {
			if ( ! empty( $form_response ) && isset( $form_response['auto_login'] ) && $form_response['auto_login'] ) {
				$members_service = new MembersService();
				$logged_in       = $members_service->login_member( $member_id, true );
				if ( ! $logged_in ) {
					wp_send_json_error(
						array(
							'message' => __( 'Invalid User', 'user-registration' ),
						)
					);
				}
			}
			wp_send_json_success( $stripe_subscription );
		} else {
			wp_delete_user( absint( $member_id ) );
			wp_send_json_error(
				array(
					'message' => __( "Something went wrong when updating users payment status" )
				)
			);
		}


	}

	/**
	 * cancel_subscription
	 *
	 */
	public static function cancel_subscription() {
		$security = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		if ( '' === $security || ! wp_verify_nonce( $security, 'ur_members_frontend' ) ) {
			wp_send_json_error( 'Nonce verification failed' );

			return;
		}

		if ( ! isset( $_POST['subscription_id'] ) ) {
			wp_send_json_error( __( 'Wrong request.', 'user-registration' ) );
		}

		$subscription_id = absint( $_POST['subscription_id'] );

		$subscription_repository = new SubscriptionRepository();
		$user_subscription       = $subscription_repository->retrieve( $subscription_id );
		if ( empty( $user_subscription ) ) {
			wp_send_json_error(
				array(
					'message' => __( "User's subscription not found.", "user-registration" ),
				)
			);
		}
		$user_id = ! empty( $user_subscription['user_id'] ) ? $user_subscription['user_id'] : '';

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to edit this user.', 'user-registration' ),
				)
			);
		}

		$cancel_status = $subscription_repository->cancel_subscription_by_id( $subscription_id );

		if ( $cancel_status['status'] ) {
			wp_destroy_current_session();
			wp_clear_auth_cookie();
			wp_set_current_user( 0 );
			wp_send_json_success(
				array(
					'message' => $cancel_status['message'],
				)
			);
		} else {
			$message = isset( $cancel_status['message'] ) ? $cancel_status['message'] : esc_html__( 'Something went wrong while cancelling your subscription. Please contact support', 'user-registration' );
			wp_send_json_error(
				array(
					'message' => $message,
				)
			);

		}


	}
	/**
	 * Reactivate membership.
	 */
	public static function reactivate_membership() {
		$security = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		if ( '' === $security || ! wp_verify_nonce( $security, 'ur_members_frontend' ) ) {
			wp_send_json_error( 'Nonce verification failed' );

			return;
		}

		if ( ! isset( $_POST['subscription_id'] ) ) {
			wp_send_json_error( __( 'Wrong request.', 'user-registration' ) );
		}
		$subscription_id = absint( $_POST['subscription_id'] );

		$subscription_repository = new SubscriptionRepository();
		$user_subscription       = $subscription_repository->retrieve( $subscription_id );

		$user_id = ! empty( $user_subscription['user_id'] ) ? $user_subscription['user_id'] : '';

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to edit this user.', 'user-registration' ),
				)
			);
		}

		$reactivation_status = $subscription_repository->reactivate_subscription_by_id( $subscription_id );
		if( $reactivation_status[ 'status' ] ) {
			wp_send_json_success(
				array(
					'message' => __( 'Membership reactivated successfully.', 'user-registration' ),
				)
			);
		} else {
			$message = ! empty( $reactivation_status[ 'message' ] ) ? $reactivation_status[ 'message' ] : __( 'Failed to reactivate membership.', 'user-registration' );
			wp_send_json_error(
				array(
					'message' => $message
				)
				);
		}
	}

	/**
	 * get_group_memberships
	 *
	 * @return void
	 */
	public static function get_group_memberships() {
		ur_membership_verify_nonce( 'ur_membership_group' );
		if ( ! isset( $_POST['group_id'] ) ) {
			wp_send_json_error( __( 'Wrong request.', 'user-registration' ) );
		}
		if ( ! isset( $_POST['list_type'] ) ) {
			wp_send_json_error( __( 'Field list type is required.', 'user-registration' ) );
		}
		$list_type = sanitize_text_field( $_POST['list_type'] );
		$group_id  = absint( $_POST['group_id'] );
		if ( "group" == $list_type ) {
			$membership_group_service = new MembershipGroupService();
			$membership_plans         = $membership_group_service->get_group_memberships( $group_id );
		} else {
			$membership_service = new MembershipService();
			$membership_plans   = $membership_service->list_active_memberships();
		}

		if ( empty( $membership_plans ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No membership is available for the selected group.', 'user-registration' ),
				)
			);
		}
		wp_send_json_success(
			array(
				'plans' => $membership_plans,
			)
		);
	}

	/**
	 * create_membership_group
	 *
	 * @return void
	 */
	public static function create_membership_group() {
		ur_membership_verify_nonce( 'ur_membership_group' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to create membership groups.', 'user-registration' ),
				)
			);
		}
		if ( ! isset( $_POST['membership_groups_data'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field membership_groups_data is required', 'user-registration' ),
				)
			);
		}
		$data                     = $_POST['membership_groups_data'];
		$membership_group_service = new MembershipGroupService();
		$create_groups            = $membership_group_service->create_membership_groups( $data );

		if ( ! $create_groups['status'] ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( $create_groups['message'], 'user-registration' ),
				)
			);
		}
		wp_send_json_success(
			array(
				'membership_group_id' => $create_groups['membership_group_id'],
				'message'             => __( 'Membership Groups saved successfully.', 'user-registration' ),
			)
		);

	}

	/**
	 * Verify if pages selected in membership global settings contain respective content(shortcodes/fields etc.)
	 * verify_pages
	 *
	 * @return void
	 */
	public static function verify_pages() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to create membership groups.', 'user-registration' ),
				)
			);
		}
		ur_membership_verify_nonce( 'user_registration_validate_page_none' );
		if ( ! isset( $_POST['value'] ) ) {
			wp_send_json_error( __( 'Wrong request.', 'user-registration' ) );
		}
		if ( ! isset( $_POST['type'] ) ) {
			wp_send_json_error( __( 'Wrong request.', 'user-registration' ) );
		}
		if ( ! in_array( $_POST['type'], array(
			'user_registration_member_registration_page_id',
			'user_registration_thank_you_page_id'
		) ) ) {
			wp_send_json_error( __( 'Invalid post type', 'user-registration' ) );
		}
		$post_id = absint( $_POST['value'] );
		$type    = sanitize_text_field( $_POST['type'] );

		$membership_service = new MembershipService();
		$response           = $membership_service->verify_page_content( $type, $post_id );
		wp_send_json( $response );
	}

	/**
	 * validate individual payment gateway on click
	 *
	 * @return void
	 */
	public static function validate_pg() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to create membership groups.', 'user-registration' ),
				)
			);
		}
		ur_membership_verify_nonce( 'ur_membership' );
		if ( ! isset( $_POST['pg'] ) || ! isset( $_POST['membership_type'] ) ) {
			wp_send_json_error( __( 'Wrong request.', 'user-registration' ) );
		}
		$pg                 = sanitize_text_field( $_POST['pg'] );
		$subscription_type  = sanitize_text_field( $_POST['membership_type'] );
		$membership_service = new MembershipService();
		$result             = $membership_service->validate_payment_gateway( array( $pg, $subscription_type ) );

		wp_send_json( $result );
	}

	/**
	 * fetch_upgradable_membership
	 *
	 * @return void
	 */
	public static function fetch_upgradable_memberships() {

		$security = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		if ( '' === $security || ! wp_verify_nonce( $security, 'ur_members_frontend' ) ) {
			wp_send_json_error( 'Nonce verification failed' );
		}
		if ( ! isset( $_POST['membership_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( "Membership does not exist", "user-registration" ),
				)
			);
		}
		$membership_id            = absint( $_POST['membership_id'] );
		$member_id                = get_current_user_id();
		$members_order_repository = new MembersOrderRepository();
		$orders_repository        = new OrdersRepository();
		$last_order               = $members_order_repository->get_member_orders( $member_id );

		if ( ! empty( $last_order ) ) {
			$order_meta = $orders_repository->get_order_metas( $last_order['ID'] );
			if ( ! empty( $order_meta ) ) {
				$upcoming_subscription = json_decode( get_user_meta( $member_id, 'urm_next_subscription_data', true ), true );
				$membership            = get_post( $upcoming_subscription['membership'] );
				wp_send_json_error(
					array(
						'message' => apply_filters( 'urm_delayed_plan_exist_notice', __( sprintf( 'You already have a scheduled upgrade to the <b>%s</b> plan at the end of your current subscription cycle (<i><b>%s</b></i>) <br> If you\'d like to cancel this upcoming change, click the <b>Cancel Membership</b> button to proceed.', $membership->post_title, date( 'M d, Y', strtotime( $order_meta['meta_value'] ) ) ), "user-registration" ), $membership->post_title, $order_meta['meta_value'] ),
					)
				);
			}
		}
		$transient_key = 'urm_upgradable_memberships_for_' . $membership_id;
		$cached_data   = get_transient( $transient_key );

		if ( false !== $cached_data ) {
			wp_send_json_success( $cached_data );
		}

		$membership_service     = new MembershipService();
		$upgradable_memberships = $membership_service->get_upgradable_membership( $membership_id );

		if ( empty( $upgradable_memberships ) ) {
			wp_send_json_error(
				array(
					'message' => __( "No upgradable Memberships.", "user-registration" ),
				),
				404
			);
		}
		set_transient( $transient_key, $upgradable_memberships, 5 * MINUTE_IN_SECONDS );
		wp_send_json_success(
			$upgradable_memberships
		);
	}

	/**
	 * Upgrade membership ajax request
	 *
	 * @return void
	 */
	public static function upgrade_membership() {

		ur_membership_verify_nonce( 'urm_upgrade_membership' );

		if ( empty( $_POST['current_subscription_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field current_subscription_id is required', 'user-registration' ),
				)
			);
		}
		if ( empty( $_POST['selected_membership_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( "Field selected_membership_id is required", "user-registration" ),
				)
			);
		}
		$ur_authorize_data = isset( $_POST['ur_authorize_data'] ) ? $_POST['ur_authorize_data'] : [];
		$data              = array(
			'current_subscription_id' => absint( $_POST['current_subscription_id'] ),
			'selected_membership_id'  => absint( $_POST['selected_membership_id'] ),
			'current_membership_id'   => absint( $_POST['current_membership_id'] ),
			'selected_pg'             => sanitize_text_field( $_POST['selected_pg'] ),
			'ur_authorize_net'        => $ur_authorize_data,
		);

		$subscription_service = new SubscriptionService();
		$status               = $subscription_service->can_upgrade( $data );

		if ( ! $status['status'] ) {
			wp_send_json_error(
				array(
					'message' => $status['message'],
				)
			);
		}

		$upgrade_membership_response = $subscription_service->upgrade_membership( $data );
		$response                    = $upgrade_membership_response['response'];

		if ( $response['status'] ) {
			$selected_pg = $data['selected_pg'];
			if ( $selected_pg !== 'free' ) {
				update_user_meta( $upgrade_membership_response['extra']['member_id'], 'urm_is_upgrading', true );
				update_user_meta( $upgrade_membership_response['extra']['member_id'], 'urm_is_upgrading_to', $data['selected_membership_id'] );
			}
			$message = "free" === $selected_pg ? __( "Membership upgraded successfully.", "user-registration-membership" ) : __( "New Order created, initializing payment...", "user-registration-membership" );
			wp_send_json_success(
				array(
					'is_upgrading'             => true,
					'pg_data'                  => $response,
					'member_id'                => $upgrade_membership_response['extra']['member_id'],
					'username'                 => $upgrade_membership_response['extra']['username'],
					'transaction_id'           => $upgrade_membership_response['extra']['transaction_id'],
					'updated_membership_title' => $upgrade_membership_response['extra']['updated_membership_title'],
					'message'                  => $message,
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => __( "Something went wrong while upgrading membership.", "user-registration" ),
			)
		);
	}

	/**
	 * cancel_upcoming_subscription
	 *
	 * @return void
	 */
	public static function cancel_upcoming_subscription() {
		ur_membership_verify_nonce( 'urm_upgrade_membership' );
		$member_id = get_current_user_id();
		$user      = get_userdata( $member_id );
		ur_get_logger()->notice( __( 'Cancel Upcoming Membership Triggered for :' . $user->user_login ), array( 'source' => 'urm-upgrade-subscription' ) );
		$members_order_repository = new MembersOrderRepository();
		$order_repository         = new OrdersRepository();
		$last_order               = $members_order_repository->get_member_orders( $member_id );
		$order_detail             = $order_repository->get_order_metas( $last_order['ID'] );
		if ( ! empty( $order_detail ) ) {
			$order_repository->delete( $last_order['ID'] );
			delete_user_meta( $member_id, 'urm_next_subscription_data' );
			delete_user_meta( $member_id, 'urm_previous_subscription_data' );
			delete_user_meta( $member_id, 'urm_next_subscription_data' );

			wp_send_json_success(
				array(
					'message' => __( "Scheduled membership has been cancelled successfully.", "user-registration" ),
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => __( "Something went wrong while cancelling membership.", "user-registration" ),
			)
		);
	}

	/**
	 * renew_subscription
	 *
	 * @return void
	 */
	public static function renew_membership() {
		ur_membership_verify_nonce( 'urm_renew_membership' );
		$member_id = get_current_user_id();
		$user      = get_userdata( $member_id );
		ur_get_logger()->notice( __( 'Renew Membership Triggered for :' . $user->user_login ), array( 'source' => 'urm-renew-membership' ) );
		$subscription_service = new SubscriptionService();
		$selected_pg          = $_POST["selected_pg"];
		$renew_membership     = $subscription_service->renew_membership( $user, $selected_pg );

		$response = $renew_membership['response'];
		if ( $response['status'] ) {
			$message = __( "New Order created, initializing payment...", "user-registration-membership" );
			wp_send_json_success(
				array(
					'pg_data'        => $response,
					'member_id'      => $renew_membership['extra']['member_id'],
					'username'       => $renew_membership['extra']['username'],
					'transaction_id' => $renew_membership['extra']['transaction_id'],
					'message'        => $message,
					'is_renewing'    => true
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => __( "Something went wrong while cancelling membership.", "user-registration" ),
			)
		);
	}
}
