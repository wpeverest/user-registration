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

use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Payment\Admin\OrdersController;


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
			'show_order_detail' => false,
			'approve_payment'   => false,
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

	public static function approve_payment() {
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
		if ( empty( $_POST['order_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field order_id is required.', 'user-registration' ),
				)
			);
		}
		$order_id           = absint( $_POST['order_id'] );
		$order_controller   = new OrdersController();
		$response           = $order_controller->approve( $order_id );
		$order              = $order_controller->get( $order_id );
		$order['member_id'] = $order['user_id'];
		$order['membership'] = $order['post_id'];
		unset( $order['user_id'],$order['post_id']  );

		$email_service = new EmailService();
		$email_service->send_email( $order, 'payment_successful' );

		if ( ! $response['status'] ) {
			wp_send_json_error(
				array(
					'message' => $response['message'],
				),
				$response['code']
			);
		}
		wp_send_json_success(
			wp_json_encode( $response )
		);
	}
}
