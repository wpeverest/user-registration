<?php

namespace WPEverest\URMembership\Payment\Admin;

use WPEverest\URMembership\Admin\Repositories\OrdersRepository;

class OrdersController {
	protected $order_service, $is_membership_active, $orders;

	public function __construct() {
		$this->order_service        = new OrderService();
		$this->is_membership_active = ur_check_module_activation( 'membership' );
		$this->orders               = ( $this->is_membership_active ) ? new OrdersRepository() : '';
	}

	public function get( $id ) {
		return $this->orders->get_order_detail( $id );
	}
	public function view( $data ) {

		$validation = $this->order_service->validation( __FUNCTION__, $data );
		if ( ! $validation['status'] ) {
			return $validation;
		}

		return $this->order_service->create_view_template( $data['order_id'], $data['user_id'] );
	}

	public function delete_all( $data ) {
		$order_ids = json_decode( wp_unslash( $data['order_ids'] ), true );
		$user_ids  = json_decode( wp_unslash( $data['user_ids'] ), true );

		if ( ! empty( $order_ids ) ) {
			$order_ids = implode( ',', $order_ids );

			return $this->orders->delete_multiple( $order_ids );
		}

		return $this->order_service->delete_multiple_user_form_order( $user_ids );
	}

	public function delete( $data ) {
		$order_id = absint( $data['order_id'] );
		$user_id  = absint( $data['user_id'] );

		if ( $order_id ) {
			return $this->orders->delete( $order_id );
		}

		return $this->order_service->delete_user_form_order( $user_id );
	}

	public function approve( $order_id ) {
		$order                     = $this->orders->get_order_detail( $order_id );
		$validate_approval_request = $this->order_service->validation( 'approval', $order );
		if ( false === $validate_approval_request['status'] ) {
			return $validate_approval_request;
		}

		return $this->order_service->approve_payment_status( $order_id, $order['subscription_id'] );
	}
}
