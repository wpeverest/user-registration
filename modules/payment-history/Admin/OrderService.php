<?php

namespace WPEverest\URMembership\Payment\Admin;

use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;

class OrderService {
	protected $response, $is_membership_active, $orders_repository, $subscription_repository;

	public function __construct() {
		$this->is_membership_active    = ur_check_module_activation( 'membership' );
		$this->response                = array(
			'status' => true,
		);
		$this->orders_repository       = ( $this->is_membership_active ) ? new OrdersRepository() : '';
		$this->subscription_repository = ( $this->is_membership_active ) ? new SubscriptionRepository() : '';
	}

	public function validation( $method, $data ) {

		switch ( $method ) {
			case 'view':
				return $this->validate_view( $data );
				break;
			case 'approval':
				return $this->validate_payment_approval_request( $data );
			default:
				break;
		}
	}

	/**
	 * extracted
	 *
	 * @param $order
	 *
	 * @return array|void
	 */
	public function validate_payment_approval_request( $order ) {
		if ( empty( $order ) ) {
			return array(
				'status'  => false,
				'message' => __( 'Order not found', 'user-registration' ),
				'code'    => 204,
			);
		}
		if ( 'pending' !== $order['status'] ) {
			return array(
				'status'  => false,
				'message' => __( 'Order is already completed.', 'user-registration' ),
				'code'    => 422,
			);
		}
		if ( 'bank' !== $order['payment_method'] ) {
			return array(
				'status'  => false,
				'message' => __( 'Invalid payment method.', 'user-registration' ),
				'code'    => 422,
			);
		}

		return array(
			'status' => true,
		);
	}

	public function validate_view( $data ) {
		if ( ! isset( $data['order_id'] ) ) {
			$this->response['status']  = false;
			$this->response['code']    = 422;
			$this->response['message'] = __( 'Field Order ID is required.' );

		} elseif ( $data['order_id'] == 0 && ! isset( $data['user_id'] ) ) {
			$this->response['status']  = false;
			$this->response['code']    = 422;
			$this->response['message'] = __( 'Field User ID is required.' );
		}

		return $this->response;
	}

	public function create_view_template( $order_id, $user_id ) {
		if ( $order_id ) {
			$order_detail = $this->orders_repository->get_order_detail( $order_id );

			if ( ! empty( $order_detail['coupon'] ) ) {
				$order_detail['coupon_discount']      = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount', true );
				$order_detail['coupon_discount_type'] = get_user_meta( $order_detail['user_id'], 'ur_coupon_discount_type', true );

			}
		} elseif ( $user_id ) {
			$order_detail = $this->get_user_form_order_detail( $user_id );
		}

		if ( empty( $order_detail ) ) {
			return array(
				'status'  => false,
				'code'    => 404,
				'message' => __( 'Order detail not found', 'user-registration' ),
			);
		}

		include __DIR__ . '/../Views/payment-details.php';
		$html = ob_get_clean();

		return array(
			'status'   => true,
			'template' => $html,
		);
	}

	public function get_user_form_order_detail( $user_id ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! empty( $user ) ) {
			$meta_value = get_user_meta( $user->ID, 'ur_payment_invoices', true );

			$invoice_item         = ( isset( $meta_value ) && ! empty( $meta_value ) ) ? json_decode( $meta_value[0]['invoice_item'][0], true ) : array();
			$total_items          = array(
				'user_id'        => $user->ID,
				'display_name'   => $user->user_login,
				'user_nicename'  => $user->user_nicename,
				'user_email'     => $user->user_email,
				'transaction_id' => $meta_value[0]['invoice_no'] ?? '',
				'post_title'     => $meta_value[0]['invoice_plan'] ?? '',
				'status'         => get_user_meta( $user->ID, 'ur_payment_status', true ),
				'created_at'     => $meta_value[0]['invoice_date'] ?? '',
				'type'           => $invoice_item[0]['label'] ?? '',
				'payment_method' => get_user_meta( $user->ID, 'ur_payment_method', true ),
				'total_amount'   => get_user_meta( $user->ID, 'ur_payment_total_amount', true ),
				'product_amount' => get_user_meta( $user->ID, 'ur_payment_product_amount', true ),
			);
			$coupon               = get_user_meta( $user->ID, 'ur_coupon_code', true );
			$coupon_discount      = get_user_meta( $user->ID, 'ur_coupon_discount', true );
			$coupon_discount_type = get_user_meta( $user->ID, 'ur_coupon_discount_type', true );
			if ( ! empty( $coupon ) ) {
				$total_items['coupon']               = $coupon;
				$total_items['coupon_discount']      = $coupon_discount;
				$total_items['coupon_discount_type'] = $coupon_discount_type;
			}
			return $total_items;
		}

		return array();
	}

	/**
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function delete_user_form_order( $user_id ) {
		$user_meta = get_user_meta( $user_id );
		$status    = true;
		foreach ( $user_meta as $meta_key => $meta_value ) {
			if ( strpos( $meta_key, 'ur_payment_' ) === 0 ) {
				if ( ! delete_user_meta( $user_id, $meta_key ) ) {
					$status = false;
					break;
				}
			}
		}

		return $status;
	}

	/**
	 * @param $user_ids
	 *
	 * @return bool
	 */
	public function delete_multiple_user_form_order( $user_ids ) {
		$status = true;
		foreach ( $user_ids as $user_id ) {
			if ( ! $this->delete_user_form_order( $user_id ) ) {
				$status = false;
				break;
			}
		}

		return $status;
	}

	/**
	 * approve_payment_status
	 *
	 * @param $order_id
	 *
	 * @return bool[]
	 */
	public function approve_payment_status( $order_id, $subscription_id ) {
		try {
			$this->orders_repository->wpdb()->query( 'START TRANSACTION' ); // Start the transaction.
			$approve_order = $this->orders_repository->update( $order_id, array( 'status' => 'completed' ) );

			if ( $approve_order ) {

				$subscription_activated = $this->subscription_repository->update( $subscription_id, array( 'status' => 'active' ) );

				if ( $subscription_activated ) {
					$this->orders_repository->wpdb()->query( 'COMMIT' );
					$this->response['message'] = __( 'Order has been approved successfully.', 'user-registration' );

					return $this->response;
				}
			}
			$this->response['status']  = false;
			$this->response['message'] = __( 'Something went wrong while updating payment status', 'user-registration' );

			return $this->response;
		} catch ( \Exception $e ) {
			// Rollback the transaction if any operation fails.
			$this->orders_repository->wpdb()->query( 'ROLLBACK' );
			$this->response['status']  = false;
			$this->response['message'] = __( 'Something went wrong while updating payment status', 'user-registration' );

			return $this->response;
		}
	}
}
