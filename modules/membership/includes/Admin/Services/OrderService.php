<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\OrdersRepository;

class OrderService {
	/**
	 * @var OrdersRepository
	 */
	protected $orders_repository, $response;

	public function __construct() {
		$this->orders_repository = new OrdersRepository();
		$this->response          = array(
			'status' => true,
		);
	}

	public function prepare_orders_data( $data, $member_id, $subscription, $upgrade_details = null ) {

		$current_user = wp_get_current_user();
		$is_admin     = ( isset( $current_user->roles ) && ! empty( $current_user->roles ) && $current_user->roles[0] == 'administrator' );

		$membership = get_post( $data['membership_data']['membership'], ARRAY_A );

		$membership_meta = json_decode( wp_unslash( get_post_meta( $membership['ID'], 'ur_membership', true ) ), true );
		$transaction_id  = '';
		if ( 'bank' == $data['membership_data']['payment_method'] ) {
			$transaction_id = str_replace( '-', '', wp_generate_uuid4() );
		}
		$total = number_format( $membership_meta['amount'], 2, '.', '' );

		if ( isset( $membership_meta['trial_status'] ) && 'on' == $membership_meta['trial_status'] ) {
			$total = 0;
		} else {

			if ( 'bank' === $data['membership_data']['payment_method'] && ur_check_module_activation( 'coupon' ) && ! empty( $data['coupon_data'] ) ) {
				$discount_amount = ( isset( $data['coupon_data']['coupon_discount_type'] ) && 'fixed' === $data['coupon_data']['coupon_discount_type'] ) ? $data['coupon_data']['coupon_discount'] : $total * $data['coupon_data']['coupon_discount'] / 100;
				$total           = $total - $discount_amount;
			}
		}

		$orders_data = array(
			'item_id'         => absint( $data['membership_data']['membership'] ),
			'subscription_id' => absint( $subscription['ID'] ),
			'user_id'         => absint( $member_id ),
			'created_by'      => isset( $current_user ) && $current_user->ID != 0 ? $current_user->ID : absint( $member_id ),
			'transaction_id'  => $transaction_id,
			'payment_method'  => ( $data['membership_data']['payment_method'] ) ? sanitize_text_field( $data['membership_data']['payment_method'] ) : '',
			'total_amount'    => ! empty( $upgrade_details ) ? $upgrade_details['chargeable_amount'] : $total,
			'status'          => ( 'free' === $membership_meta['type'] || $is_admin ) ? 'completed' : 'pending',
			'order_type'      => sanitize_text_field( $membership_meta['type'] ),
			'trial_status'    => ( ! empty( $upgrade_details ) && ( "on" === $upgrade_details['trial_status'] ) ) ? 'on' : ( isset( $membership_meta['trial_status'] ) ? sanitize_text_field( $membership_meta['trial_status'] ) : 'off' ),
			'notes'           => $is_admin ? 'admin created order for ' . $membership['post_title'] : 'subscriber created order for ' . $membership['post_title'],
		);

		$orders_meta = array(
			array(
				'meta_key'   => 'is_admin_created',
				'meta_value' => $is_admin,
			)
		);
		if ( ! empty( $upgrade_details ) && ! empty( $upgrade_details['delayed_until'] ) ) {
			$orders_meta[] = [
				'meta_key'   => 'delayed_until',
				'meta_value' => $upgrade_details['delayed_until'],
			];
		}

		return array(
			'orders_data'      => $orders_data,
			'orders_meta_data' => $orders_meta,
		);
	}

}
