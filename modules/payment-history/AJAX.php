<?php
/**
 * URMembership AJAX
 *
 * AJAX Event Handler
 *
 * @class    AJAX
 * @version  1.0.0
 * @package  URMembership/Ajax
 * @category Class
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Payment;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Payment\Admin\OrdersController;
use WPEverest\URMembership\TableList;


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
			'delete_orders'     => false,
			'delete_order'      => false,
			'edit_order'        => false,
			'show_order_detail' => false,
			'approve_payment'   => false,
			'create_order'      => false,
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
	 * delete multiple orders
	 *
	 * @return void
	 */
	public static function delete_orders() {
		if ( current_user_can( 'manage_options' ) ) {
			if ( ! check_ajax_referer( 'ur_member_orders', 'security' ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Nonce error please reload.', 'user-registration' ),
					)
				);
			}

			$order_controller = new OrdersController();
			$deleted          = $order_controller->delete_all( $_POST );
			if ( $deleted ) {
				wp_send_json_success(
					array(
						'message' => esc_html__( 'Orders deleted successfully.', 'user-registration' ),
					)
				);
			}
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Sorry! There was an unexpected error while deleting the orders data.', 'user-registration' ),
				)
			);
		}
	}

	/**
	 * delete single order
	 *
	 * @return void
	 */
	public static function delete_order() {
		if ( current_user_can( 'manage_options' ) ) {
			if ( ! check_ajax_referer( 'ur_member_orders', 'security' ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Nonce error please reload.', 'user-registration' ),
					)
				);
			}

			$order_controller = new OrdersController();
			$deleted          = $order_controller->delete( $_POST );

			if ( $deleted ) {
				wp_send_json_success(
					array(
						'message' => esc_html__( 'Orders deleted successfully.', 'user-registration' ),
					)
				);
			}
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Sorry! There was an unexpected error while deleting the orders data.', 'user-registration' ),
				)
			);
		}
	}

	/**
	 * Show Order Detail
	 *
	 * @return void
	 */
	public static function show_order_detail() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Sorry! You do not have the proper privilege.', 'user-registration' ),
				)
			);
		}
		if ( ! check_ajax_referer( 'ur_member_orders', 'security' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Nonce error please reload.', 'user-registration' ),
				)
			);
		}
		$order_controller = new OrdersController();
		$response         = $order_controller->view( $_POST );

		$message = $response['message'] ?? '';

		if ( ! $response['status'] ) {
			wp_send_json_error(
				array(
					'message' => $message,
				),
				$response['code']
			);
		}

		wp_send_json_success(
			wp_json_encode(
				array(
					$response['template'],
				)
			)
		);
	}

	/**
	 * Create membership orders from backend.
	 */
	public static function create_order() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, you do not have permission to create membership order', 'user-registration' ),
				)
			);
		}

		ur_membership_verify_nonce( 'ur_membership_order' );

		$order_data = isset( $_POST['order_data'] ) ? (array) json_decode( wp_unslash( $_POST['order_data'] ), true ) : array();

		global $wpdb;
		$subscription_table = TableList::subscriptions_table();
		$subscription_id    = $wpdb->get_var(
			$wpdb->prepare(
				"
					SELECT s.ID FROM $subscription_table  AS s
					WHERE s.user_id = %d LIMIT 1
				",
				$order_data['ur_member_id']
			)
		);
		$order_meta_data    = array(
			'meta_key'   => 'payment_date',
			'meta_value' => ( new \DateTime( $order_data['ur_payment_date'] ) )->format( 'Y-m-d H:i:s' ),
		);

		$order_data = array(
			'user_id'         => absint( $order_data['ur_member_id'] ),
			'item_id'         => absint( $order_data['ur_membership_plan'] ),
			'subscription_id' => $subscription_id,
			'total_amount'    => floatval( $order_data['ur_membership_amount'] ),
			'status'          => sanitize_text_field( $order_data['ur_transaction_status'] ),
			'payment_method'  => isset( $order_data['ur_payment_method'] ) ? sanitize_text_field( $order_data['ur_payment_method'] ) : 'Manual',
			'notes'           => isset( $order_data['ur_payment_notes'] ) ? sanitize_text_field( $order_data['ur_payment_notes'] ) : '',
			'created_by'      => get_current_user_id(),
		);

		$members_order_repository = new OrdersRepository();
		$order                    = $members_order_repository->create(
			array(
				'orders_data'      => $order_data,
				'orders_meta_data' => array( $order_meta_data ),
			)
		);

		if ( false === $order ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to create order.', 'user-registration' ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'id'      => $order['ID'],
					'message' => __( 'Order created successfully.', 'user-registration' ),
				)
			);
		}
		wp_die();
	}

	public static function edit_order() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, you do not have permission to edit membership order', 'user-registration' ),
				)
			);
		}

		ur_membership_verify_nonce( 'ur_membership_edit_order' );

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- L:303
		$order_id        = absint( $_POST['order_id'] ?? 0 );
		$is_form_payment = isset( $_POST['is_form_payment'] ) && 'true' === $_POST['is_form_payment'];

		if ( ! $order_id && ! $is_form_payment ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing required fields.', 'user-registration' ),
				)
			);
		}

		if ( $is_form_payment ) {
			$user_id = $order_id;
			$user    = get_user_by( 'ID', $user_id );

			if ( ! $user ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %d: User id */
							__( 'Cannot find user with id %d.', 'user-registration' ),
							$user_id
						),
					)
				);
			}

			$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : false;
			$status = in_array( $status, array( 'completed', 'pending', 'failed', 'refunded' ), true ) ? $status : false;

			$created_at = isset( $_POST['created_at'] ) ? sanitize_text_field( wp_unslash( $_POST['created_at'] ) ) : false;

			if ( $created_at ) {
				$created_timestamp = strtotime( $created_at );
				if ( false === $created_timestamp ) {
					wp_send_json_error(
						array(
							'message' => __( 'Invalid created date format.', 'user-registration' ),
						)
					);
				}

				$payment_invoices = get_user_meta( $user_id, 'ur_payment_invoices', true );
				if ( ! empty( $payment_invoices ) && is_array( $payment_invoices ) && isset( $payment_invoices[0] ) ) {
					$payment_invoices[0]['invoice_date'] = wp_date( 'Y-m-d H:i:s', $created_timestamp );
					update_user_meta( $user_id, 'ur_payment_invoices', $payment_invoices );
				}
			}

			if ( $status ) {
				update_user_meta( $user_id, 'ur_payment_status', $status );
			}

			if ( isset( $_POST['notes'] ) ) {
				update_user_meta( $user_id, 'ur_payment_notes', sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) );
			}

			wp_send_json_success(
				array(
					'message' => __( 'Payment updated successfully.', 'user-registration' ),
				)
			);
		}

		$order = ( new OrdersRepository() )->get_order_detail( $order_id );

		if ( empty( $order ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %d: Order id */
						__( 'Cannot find record of %d order id.', 'user-registration' ),
						$order_id
					),
				)
			);
		}

		$membership = ( new MembershipRepository() )->get_single_membership_by_ID( $order['post_id'] );

		if ( empty( $membership ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Cannot find membership related to the order', 'user-registration' ),
				)
			);
		}

		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : false;

		$created_at = isset( $_POST['created_at'] ) ? sanitize_text_field( wp_unslash( $_POST['created_at'] ) ) : false;

		if ( $created_at ) {
			$created_timestamp = strtotime( $created_at );
			if ( false === $created_timestamp ) {
				wp_send_json_error(
					array(
						'message' => __( 'Invalid created date format.', 'user-registration' ),
					)
				);
			}
		}

		$supports_trial  = false;
		$membership_data = json_decode( get_post_meta( $order['post_id'], 'ur_membership', true ), true );
		if (
			'on' === ( $membership_data['trial_status'] ?? '' ) &&
			isset( $membership_data['trial_data']['value'], $membership_data['trial_data']['duration'] )
		) {
			$supports_trial = true;
		}

		if ( ! $supports_trial && ( isset( $_POST['trial_start_date'] ) || isset( $_POST['trial_start_date'] ) ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Associated membership does not support trial.', 'user-registration' ),
				)
			);
			exit;
		}

		$trial_start_date = isset( $_POST['trial_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['trial_start_date'] ) ) : false;
		$trial_end_date   = isset( $_POST['trial_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['trial_end_date'] ) ) : false;
		$trial_changed    = false;

		if ( $trial_start_date || $trial_end_date ) {
			if ( ! $trial_start_date || ! $trial_end_date ) {
				wp_send_json_error(
					array(
						'message' => __( 'Both trial start and end dates are required.', 'user-registration' ),
					)
				);
			}

			$trial_start_timestamp = strtotime( $trial_start_date );
			$trial_end_timestamp   = strtotime( $trial_end_date );

			if ( false === $trial_start_timestamp || false === $trial_end_timestamp ) {
				wp_send_json_error(
					array(
						'message' => __( 'Invalid trial date format.', 'user-registration' ),
					)
				);
			}

			if ( $trial_end_timestamp <= $trial_start_timestamp ) {
				wp_send_json_error(
					array(
						'message' => __( 'Trial end date must be after trial start date.', 'user-registration' ),
					)
				);
			}

			$trial_duration = $membership_data['trial_data']['value'] . $membership_data['trial_data']['duration'];

			$trial_duration_days = ( $trial_end_timestamp - $trial_start_timestamp ) / DAY_IN_SECONDS;

			if ( $trial_duration_days > $trial_duration ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %s: trial duration */
							__( 'Trial period cannot exceed %s.', 'user-registration' ),
							$trial_duration
						),
					)
				);
			}

			if ( $created_at ) {
				$created_timestamp = strtotime( wp_date( 'Y-m-d', $created_at ) );
				if ( $trial_start_timestamp < $created_timestamp ) {
					wp_send_json_error(
						array(
							'message' => __( 'Trial start date cannot be before order creation date.', 'user-registration' ),
						)
					);
				}
			}

			$original_trial_end = isset( $order['trial_end_date'] ) ? strtotime( $order['trial_end_date'] ) : null;
			if ( $original_trial_end && $trial_end_timestamp !== $original_trial_end ) {
				$trial_changed = true;
			}
		}

		if ( $trial_changed && $trial_end_date ) {
			$trial_end_timestamp = strtotime( $trial_end_date );

			$subscription_start_date = wp_date( 'Y-m-d H:i:s', $trial_end_timestamp );
			$subscription_period     = isset( $membership_data['subscription']['value'] ) ? $membership_data['subscription']['value'] : 1;
			$subscription_duration   = isset( $membership_data['subscription']['duration'] ) ? $membership_data['subscription']['duration'] : 'day';

			$next_billing_timestamp = strtotime( "+{$subscription_period} {$subscription_duration}", $trial_end_timestamp );
			$next_billing_date      = wp_date( 'Y-m-d H:i:s', $next_billing_timestamp );

			$result = ( new SubscriptionRepository() )->update(
				$order['subscription_id'],
				array_filter(
					array(
						'start_date'        => $subscription_start_date,
						'next_billing_date' => $next_billing_date,
						'expiry_date'       => $next_billing_date,
						'trial_start_date'  => $trial_start_date,
						'trial_end_date'    => $trial_end_date,
					)
				)
			);

			if ( false === $result ) {
				wp_send_json_error(
					array(
						'message' => __( 'Failed to update associated subscription.', 'user-registration' ),
					)
				);
			}
		}

		if ( $status === 'completed' ) {
			self::approve_payment( absint( $order_id ) );
		}
		$status = in_array( $status, array( 'completed', 'pending', 'failed', 'refunded' ), true ) ? $status : false;

		$result = ( new OrdersRepository() )->update(
			$order_id,
			array_filter(
				array(
					'status'     => $status,
					'notes'      => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : false,
					'created_at' => $created_at ? wp_date( 'Y-m-d H:i:s', strtotime( $created_at ) ) : false,
				)
			)
		);

		if ( false === $result ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to update order.', 'user-registration' ),
				)
			);
		}

		if ( 'failed' === $status ) {
			do_action( 'ur_membership_order_status_failed', $order_id, $order, $status );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Order updated successfully.', 'user-registration' ),
			)
		);
	}

	public static function approve_payment( $order_id ) {
		$order_controller = new OrdersController();
		$order            = $order_controller->get( $order_id );
		$response         = $order_controller->approve( $order_id );

		$order['member_id']  = $order['user_id'];
		$order['membership'] = $order['post_id'];
		unset( $order['user_id'], $order['post_id'] );

		$email_service = new EmailService();
		$email_service->send_email( $order, 'payment_successful' );

		if ( ! $response['status'] ) {
			return array(
				'status'  => false,
				'message' => $response['message'],
				$response['code'],
			);
		}
		return array(
			'status'  => false,
			'message' => wp_json_encode( $response ),
		);
	}
}
