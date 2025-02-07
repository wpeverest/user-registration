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
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Services\CouponService;
use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\Admin\Services\MembershipService;
use WPEverest\URMembership\Admin\Services\MembersService;
use WPEverest\URMembership\Admin\Services\PaymentService;
use WPEverest\URMembership\Admin\Services\Stripe\StripeService;

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
			'create_membership'          => false,
			'update_membership'          => false,
			'delete_memberships'         => false,
			'update_membership_status'   => false,
			'create_member'              => false,
			'update_member'              => false,
			'delete_members'             => false,
			'confirm_payment'            => true,
			'create_stripe_subscription' => true,
			'register_member'            => true,
			'validate_coupon'            => true,
			'cancel_subscription'        => false,
			'get_group_memberships'      => false,
			'create_membership_group'    => false,
			'delete_membership_groups'   => false,
			'verify_pages'               => false,
			'validate_pg'                => false,
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

		$membership_service      = new MembershipService();
		$response                = $membership_service->create_membership_order_and_subscription( $data );
		$member_id               = $response['member_id'];
		$transaction_id          = $response['transaction_id'] ?? 0;
		$data['member_id']       = $member_id;
		$data['subscription_id'] = $response['subscription_id'] ?? 0;
		$data['email']           = $response['member_email'];

		$pg_data = array();
		if ( 'free' !== $data['payment_method'] && $response['status'] ) {
			$payment_service = new PaymentService( $data['payment_method'], $data['membership'], $data['email'] );
			$pg_data         = $payment_service->build_response( $data );
		}

		if ( $response['status'] ) {
			$form_response = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();
			if ( ! empty( $form_response ) && isset( $form_response["auto_login"] ) && $form_response["auto_login"] && 'free' == $data['payment_method'] ) {
				$members_service = new MembersService();
				$members_service->login_member( $member_id );
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
			wp_send_json_success( $response );
		} else {
			$message = $response['message'] ?? esc_html__( 'Sorry! There was an unexpected error while registering the user . ', 'user-registration' );
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


		$data = $membership->prepare_membership_post_data( $data );

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
			add_post_meta( $new_membership_ID, $data['post_meta_data']['meta_key'], $data['post_meta_data']['meta_value'] );
			$meta_data = json_decode( $data["post_meta_data"]["meta_value"], true );


			if ( $is_stripe_enabled && "free" !== $meta_data["type"] ) {
				$stripe_service           = new StripeService();
				$data["membership_id"]    = $new_membership_ID;
				$stripe_price_and_product = $stripe_service->create_stripe_product_and_price( $data["post_data"], $meta_data, false );

				if ( ! empty( $stripe_price_and_product ) ) {
					$meta_data["payment_gateways"]["stripe"]["product_id"] = $stripe_price_and_product->product;
					$meta_data["payment_gateways"]["stripe"]["price_id"]   = $stripe_price_and_product->id;
					update_post_meta( $new_membership_ID, $data['post_meta_data']['meta_key'], wp_json_encode( $meta_data ) );
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
			update_post_meta( $updated_ID, $data['post_meta_data']['meta_key'], $data['post_meta_data']['meta_value'] );
			$meta_data = json_decode( $data["post_meta_data"]["meta_value"], true );

			if ( $is_stripe_enabled && "free" !== $meta_data["type"] ) {

				//check if any significant value has been changed  , trial not included since trial value change does not affect the type of product and price in stripe, instead handled during subscription
				$should_create_new_product = ( $old_membership_data['amount'] !== $meta_data['amount'] || ( isset( $old_membership_data["subscription"] ) && $old_membership_data["subscription"]["value"] !== $meta_data["subscription"]["value"] ) || ( isset( $old_membership_data["subscription"] ) && $old_membership_data["subscription"]["duration"] !== $meta_data["subscription"]["duration"] ) );

				$meta_data = json_decode( $data["post_meta_data"]["meta_value"], true );

				if ( $should_create_new_product || empty( $meta_data["payment_gateways"]["stripe"]["product_id"] ) ) {
					$stripe_service           = new StripeService();
					$data["membership_id"]    = $updated_ID;
					$stripe_price_and_product = $stripe_service->create_stripe_product_and_price( $data["post_data"], $meta_data, $should_create_new_product );

					if ( ! empty( $stripe_price_and_product ) ) {
						$meta_data["payment_gateways"]["stripe"]["product_id"] = $stripe_price_and_product->product;
						$meta_data["payment_gateways"]["stripe"]["price_id"]   = $stripe_price_and_product->id;
						update_post_meta( $updated_ID, $data['post_meta_data']['meta_key'], wp_json_encode( $meta_data ) );
					} else {
						wp_send_json_error(
							array(
								'message' => esc_html__( 'Sorry! Could not create stripe product. ', 'user-registration' ),
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
		$membership_ids        = wp_unslash( $_POST['membership_ids'] );
		$membership_ids        = implode( ",", json_decode( $membership_ids, true ) );
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
			$message = $response['message'] ?? esc_html__( 'Sorry! There was an unexpected error while saving the members data . ', 'user-registration' );
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

		ur_membership_verify_nonce( 'ur_membership_confirm_payment' );
		if ( empty( $_POST['member_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field member_id is required', 'user-registration' ),
				)
			);
		}
		$stripe_service      = new StripeService();
		$payment_status      = sanitize_text_field( $_POST['payment_status'] );
		$update_stripe_order = $stripe_service->update_order( $_POST );
		if ( $update_stripe_order['status'] ) {
			$form_response = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();
			if ( ! empty( $form_response ) && $form_response["auto_login"] && $payment_status !== "failed" ) {
				$members_service = new MembersService();
				$members_service->login_member( $_POST['member_id'] );
			}
			wp_send_json_success(
				array(
					'message' => $update_stripe_order["message"]
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => $update_stripe_order["message"] ?? __( "Something went wrong when updating users payment status" )
			)
		);

	}

	public static function create_stripe_subscription() {
		ur_membership_verify_nonce( 'ur_membership_confirm_payment' );
		$customer_id         = isset( $_POST['customer_id'] ) ? $_POST['customer_id'] : '';
		$payment_method_id   = isset( $_POST['payment_method_id'] ) ? sanitize_text_field( $_POST['payment_method_id'] ) : '';
		$member_id           = absint( wp_unslash( $_POST['member_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$stripe_service      = new StripeService();
		$form_response       = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();
		$stripe_subscription = $stripe_service->create_subscription( $customer_id, $payment_method_id, $member_id );

		if ( $stripe_subscription['status'] ) {
			$form_response = isset( $_POST['form_response'] ) ? (array) json_decode( wp_unslash( $_POST['form_response'] ), true ) : array();
			if ( ! empty( $form_response ) && $form_response["auto_login"] ) {
				$members_service = new MembersService();
				$members_service->login_member( $member_id );
			}
			wp_send_json_success( $stripe_subscription );
		} else {
			wp_delete_user( absint( $member_id ) );
			wp_send_json_error(
				array(
					'message' => $message ?? __( "Something went wrong when updating users payment status" )
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
		$cancel_status           = $subscription_repository->cancel_subscription_by_id( $subscription_id );

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
			$message = $cancel_status['message'] ?? esc_html__( 'Something went wrong while cancelling your subscription. Please contact support', 'user-registration' );
			wp_send_json_error(
				array(
					'message' => $message,
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
}
