<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Local_Currency\Admin\CoreFunctions;

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

	public function prepare_orders_data( $data, $member_id, $subscription, $upgrade_details = null, $is_renewal = false ) {

		$current_user = wp_get_current_user();
		$is_admin     = ( isset( $current_user->roles ) && ! empty( $current_user->roles ) && $current_user->roles[0] == 'administrator' );

		$membership = get_post( $data['membership_data']['membership'], ARRAY_A );

		$membership_meta = json_decode( wp_unslash( get_post_meta( $membership['ID'], 'ur_membership', true ) ), true );
		$transaction_id  = '';
		if ( 'bank' == $data['membership_data']['payment_method'] ) {
			$transaction_id = str_replace( '-', '', wp_generate_uuid4() );
		}
		$order_type = '';
		$total      = 0;

		if ( isset( $data['team'] ) && ! empty( $data['team'] ) ) {
			$team_data      = $data['team'];
			$team_plan_type = isset( $team_data['team_plan_type'] ) ? $team_data['team_plan_type'] : null;
			if ( $team_plan_type ){
				if ( 'subscription' === $team_plan_type ) {
					$order_type = 'subscription';
				} else {
					$order_type = 'paid';
				}
			}
			$seat_model = isset( $team_data['seat_model'] ) ? $team_data['seat_model'] : 'fixed';
			if ( 'fixed' === $seat_model ) {
				$total = isset( $team_data['team_price'] ) ? floatval( $team_data['team_price'] ) : 0;
			} else {
				$pricing_model = isset( $team_data['pricing_model'] ) ? $team_data['pricing_model'] : 'per_seat';
				$team_seats    = isset( $data['team_seats'] ) ? absint( $data['team_seats'] ) : 0;

				if ( 'per_seat' === $pricing_model ) {
					// Per seat pricing: team_seats × per_seat_price
					$per_seat_price = isset( $team_data['per_seat_price'] ) ? floatval( $team_data['per_seat_price'] ) : 0;
					$total = $team_seats * $per_seat_price;
				} elseif ( 'tier' === $pricing_model ) {
					// Tier pricing: team_seats × tier_per_seat_price (from selected tier)
					if ( isset( $data['tier'] ) && isset( $data['tier']['tier_per_seat_price'] ) ) {
						$tier_per_seat_price = floatval( $data['tier']['tier_per_seat_price'] );
						$total      = $team_seats * $tier_per_seat_price;
					}
				}
			}
		}else{
			$order_type = sanitize_text_field( $membership_meta['type'] );
			$total = number_format( $membership_meta['amount'], 2, '.', '' );
		}

		if ( isset( $membership_meta['trial_status'] ) && 'on' == $membership_meta['trial_status'] ) {
			$total = 0;
		} else {

			if ( 'bank' === $data['membership_data']['payment_method'] && ur_check_module_activation( 'coupon' ) && ! empty( $data['coupon_data'] ) ) {
				$discount_amount = ( isset( $data['coupon_data']['coupon_discount_type'] ) && 'fixed' === $data['coupon_data']['coupon_discount_type'] ) ? $data['coupon_data']['coupon_discount'] : $total * $data['coupon_data']['coupon_discount'] / 100;
				$total           = $total - $discount_amount;
			}
		}

		$local_currency_converted_amount = 0;

		if ( ! empty( $data['local_currency_details'] ) ) {
			$local_currency  = ! empty( $data['local_currency_details']['switched_currency' ] ) ? $data['local_currency_details']['switched_currency' ] : '';
			$ur_zone_id 	 = ! empty( $data['local_currency_details']['urm_zone_id' ] ) ? $data['local_currency_details']['urm_zone_id' ] : '';

			if ( ! empty( $local_currency ) && ! empty( $ur_zone_id ) && ur_check_module_activation( 'local-currency' ) ) {
				$currency = $local_currency;
				$pricing_data = CoreFunctions::ur_get_pricing_zone_by_id( $ur_zone_id );
				$local_currency_data = ! empty( $membership_meta['local_currency'] ) ? $membership_meta['local_currency'] : array();

				if ( ! empty( $local_currency_data ) && ur_string_to_bool( $local_currency_data[ 'is_enable'] ) ) {
					$total = CoreFunctions::ur_get_amount_after_conversion( $total, $currency, $pricing_data, $local_currency_data, $ur_zone_id );
					$local_currency_converted_amount = CoreFunctions::ur_get_amount_after_conversion( $membership_meta['amount'], $currency, $pricing_data, $local_currency_data, $ur_zone_id );
				}
			}
		}

		$tax_details = isset( $data['tax_data'] ) ? $data['tax_data'] : array();

		if ( ! empty( $data['tax_data']['tax_rate'] ) ) {
			$tax_rate  = floatval( $data['tax_data']['tax_rate'] );
			$tax_amount  = $total * $tax_rate / 100;
			$total     = $total + $tax_amount;

			$tax_details['tax_amount']      = number_format( $tax_amount, 2, '.', '' );
			$tax_details['total_after_tax'] = number_format( $total, 2, '.', '' );
		}

		$creator = $is_admin ? 'admin' : 'member';
		$type = $is_renewal ? 'Renewal' : (!empty($upgrade_details) ? 'Upgrade' : '');
		if ( ! empty( $type ) ) {
			$note = sprintf(__('%s created order for %s of %s', 'user-registration'), $creator , $type , $membership['post_title']);
		} else {
			$note = sprintf(__('%s created order for %s', 'user-registration'), $creator, $membership['post_title'] );
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
			'order_type'      => $order_type,
			'trial_status'    => ( ! empty( $upgrade_details ) && ( "on" === $upgrade_details['trial_status'] ) ) ? 'on' : ( isset( $membership_meta['trial_status'] ) ? sanitize_text_field( $membership_meta['trial_status'] ) : 'off' ),
			'notes'           => $note,
		);

		$orders_meta = array(
			array(
				'meta_key'   => 'is_admin_created',
				'meta_value' => $is_admin,
			)
		);

		if ( ! empty( $tax_details ) ) {
			$orders_meta[] = [
				'meta_key'   => 'tax_data',
				'meta_value' => json_encode( $tax_details )
			];
		}

		if ( ! empty( $currency ) ) {
			$orders_meta[] = [
				'meta_key'   => 'local_currency',
				'meta_value' => $currency,
				];
		}

		if ( ! empty( $local_currency_converted_amount ) ) {
			$orders_meta[] = [
				'meta_key'   => 'local_currency_converted_amount',
				'meta_value' => $local_currency_converted_amount,

			];
		}

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
