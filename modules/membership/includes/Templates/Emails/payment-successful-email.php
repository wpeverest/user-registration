<?php

use WPEverest\URMembership\Admin\Repositories\OrdersRepository;

$user_id    = absint( ! empty( $invoice_details['user_id'] ) ? $invoice_details['user_id'] : get_current_user_id() );
$currencies = ur_payment_integration_get_currencies();
$currency   = get_user_meta( $user_id, 'ur_payment_currency', true );
$currency   = ! empty( $currency ) ? $currency : 'USD';
$symbol     = $currencies[ $currency ]['symbol'] ?? '$';

$trial_status = isset( $invoice_details['membership_plan_trial_status'] ) ? $invoice_details['membership_plan_trial_status'] : 'off';
$trial_amount = $trial_status === 'On' ? ( isset( $invoice_details['membership_plan_trial_amount'] ) ? $invoice_details['membership_plan_trial_amount'] : 'N/A' ) : $symbol . '0.00';
$total_amount = $trial_status === 'On' ? $symbol . '0.00' : ( isset( $invoice_details['membership_plan_total'] ) ? $invoice_details['membership_plan_total'] : 'N/A' );

$team_data       = isset( $invoice_details['team'] ) && is_array( $invoice_details['team'] ) ? $invoice_details['team'] : null;
$number_of_seats = 0;
if ( ! empty( $invoice_details['team_seats'] ) ) {
	$number_of_seats = (int) $invoice_details['team_seats'];
} elseif ( ! empty( $team_data ) && isset( $team_data['team_size'] ) ) {
	$number_of_seats = (int) $team_data['team_size'];
}
$pricing_model = isset( $team_data['pricing_model'] ) ? $team_data['pricing_model'] : '';
$tier_info     = isset( $invoice_details['tier'] ) && is_array( $invoice_details['tier'] ) && ! empty( $invoice_details['tier'] ) ? $invoice_details['tier'] : null;

$payment_amount = isset( $invoice_details['membership_plan_payment_amount'] ) ? $invoice_details['membership_plan_payment_amount'] : '';
if ( ! empty( $team_data ) ) {
	if ( isset( $team_data['team_price'] ) && ! empty( $team_data['team_price'] ) ) {
		$payment_amount = $symbol . number_format( (float) $team_data['team_price'], 2 );
		if ( 'On' !== $trial_status ) {
			$total_amount = $symbol . number_format( (float) $team_data['team_price'], 2 );
		}
	} elseif ( isset( $team_data['per_seat_price'] ) && ! empty( $team_data['per_seat_price'] ) && $number_of_seats > 0 ) {
		$calculated_total = (float) $team_data['per_seat_price'] * $number_of_seats;
		$payment_amount   = $symbol . number_format( $calculated_total, 2 );
		if ( 'On' !== $trial_status ) {
			$total_amount = $symbol . number_format( $calculated_total, 2 );
		}
	} elseif ( 'tier' === $pricing_model && ! empty( $tier_info ) && isset( $tier_info['tier_per_seat_price'] ) && $number_of_seats > 0 ) {
		$calculated_total = (float) $tier_info['tier_per_seat_price'] * $number_of_seats;
		$payment_amount   = $symbol . number_format( $calculated_total, 2 );
		if ( 'On' !== $trial_status ) {
			$total_amount = $symbol . number_format( $calculated_total, 2 );
		}
	}
}

$order_id = ! empty( $values['order']['order_id'] ) ? $values['order']['order_id'] : '';

$order_repository = new OrdersRepository();
$order_meta_data  = $order_repository->get_order_meta_by_order_id_and_meta_key( $order_id, 'tax_data' );
$tax_data 		  = ! empty( $order_meta_data['meta_value'] ) ? json_decode( $order_meta_data[ 'meta_value' ], true ) : array();
$tax_amount       = ! empty( $tax_data['tax_amount'] ) ? $symbol . $tax_data['tax_amount'] : 0;

if ( $invoice_details['is_membership'] ) :

	// Define label–key pairs for membership rows
	$membership_fields = [
		__( 'Membership Name', 'user-registration' )   => ucwords( $invoice_details['membership_plan_name'] ),
		__( 'Trial Status', 'user-registration' )      => ucfirst( $trial_status ),
		__( 'Trial Start Date', 'user-registration' )  => ! empty( $invoice_details['membership_plan_trial_start_date'] ) && 'N/A' !== $invoice_details['membership_plan_trial_start_date'] ? date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_trial_start_date'] ) ) : __( 'N/A', 'user-registration' ),
		__( 'Trial End Date', 'user-registration' )    => ! empty( $invoice_details['membership_plan_trial_end_date'] ) && 'N/A' !== $invoice_details['membership_plan_trial_end_date'] ? date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_trial_end_date'] ) ) : __( 'N/A', 'user-registration' ),
		__( 'Next Billing Date', 'user-registration' ) => ! empty( $invoice_details['membership_plan_next_billing_date'] ) && 'N/A' !== $invoice_details['membership_plan_next_billing_date'] ? date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_next_billing_date'] ) ) : __( 'N/A', 'user-registration' ),
		__( 'Payment Date', 'user-registration' )      => date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_payment_date'] ) ),
		__( 'Billing Cycle', 'user-registration' )     => $invoice_details['membership_plan_billing_cycle'],
		__( 'Payment Method', 'user-registration' )    => $invoice_details['membership_plan_payment_method'],
		__( 'Amount', 'user-registration' )            => $payment_amount,
		__( 'Trial Amount', 'user-registration' )      => $trial_amount,
		__( 'Tax Amount', 'user-registration' )		   => $tax_amount,
	];

	// Add coupon details if they exist
	if ( ! empty( $invoice_details['membership_plan_coupon'] ) ) {
		$membership_fields[ __( 'Coupon', 'user-registration' ) ]          = $invoice_details['membership_plan_coupon'];
		$membership_fields[ __( 'Coupon Discount', 'user-registration' ) ] = $invoice_details['membership_plan_coupon_discount'];
	}

		if ( ! empty( $team_data ) && is_array( $team_data ) ) {
		$per_seat_price = '';
		$tier_range     = '';

		if ( 'tier' === $pricing_model && ! empty( $tier_info ) && isset( $tier_info['tier_per_seat_price'] ) ) {
			$per_seat_price = $tier_info['tier_per_seat_price'];
			if ( isset( $tier_info['tier_range'] ) ) {
				$tier_range = $tier_info['tier_range'];
			}
		} elseif ( isset( $team_data['per_seat_price'] ) && ! empty( $team_data['per_seat_price'] ) ) {
			$per_seat_price = $team_data['per_seat_price'];
		} elseif ( isset( $team_data['team_price'] ) && ! empty( $team_data['team_price'] ) && $number_of_seats > 0 ) {
			$per_seat_price = (float) $team_data['team_price'] / $number_of_seats;
		}

		if ( $number_of_seats > 0 ) {
			$membership_fields[ __( 'Seat', 'user-registration' ) ] = $number_of_seats;
		}

		if ( empty( $per_seat_price ) && isset( $team_data['team_price'] ) && ! empty( $team_data['team_price'] ) && $number_of_seats > 0 ) {
			$per_seat_price = (float) $team_data['team_price'] / $number_of_seats;
		}

		$seat_model = isset( $team_data['seat_model'] ) ? $team_data['seat_model'] : '';
		if ( ! empty( $per_seat_price ) && 'variable' === $seat_model ) {
			$per_seat_label = __( 'Per seat', 'user-registration' );
			if ( 'tier' === $pricing_model && ! empty( $tier_range ) ) {
				$per_seat_label = sprintf( __( 'Per seat (tier %s)', 'user-registration' ), $tier_range );
			} elseif ( 'tier' === $pricing_model ) {
				$per_seat_label = __( 'Per seat (tier pricing)', 'user-registration' );
			}
			$membership_fields[ $per_seat_label ] = $symbol . number_format( (float) $per_seat_price, 2 );
		}
	}

	// Add total (after coupon logic)
	$membership_fields[ __( 'Total', 'user-registration' ) ] = $total_amount;
	// Render table
	?>
	<table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
		<tr>
			<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php esc_html_e( 'Details', 'user-registration' ); ?></th>
			<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php esc_html_e( 'Information', 'user-registration' ); ?></th>
		</tr>
		<?php
		$index = 0;
		foreach ( $membership_fields as $label => $value ) :
			$bg_color = $index % 2 === 0 ? 'background-color: #dddddd;' : '';
			?>
			<tr style="<?php echo esc_attr( $bg_color ); ?>">
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html( $label ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html( $value ); ?></td>
			</tr>
			<?php
			++$index;
		endforeach;
		?>
	</table>

	<?php
else :

	$invoice_details = apply_filters( 'user_registration_get_payment_details', $user_id );



	if ( is_array( $invoice_details ) ) :
		?>
		<table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
			<tr>
				<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php esc_html_e( 'Details', 'user-registration' ); ?></th>
				<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php esc_html_e( 'Information', 'user-registration' ); ?></th>
			</tr>
			<?php
			$count = 0;
			foreach ( $invoice_details as $meta_key => $title ) :
				$bg_color = $count % 2 === 0 ? 'background-color: #dddddd;' : '';
				$value    = get_user_meta( $user_id, $meta_key, true );
				if ( 'ur_payment_total_amount' === $meta_key ) {
					$value = $symbol . $value;
				}
				?>
				<tr style="<?php echo esc_attr( $bg_color ); ?>">
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html( $title ); ?></td>
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html( $value ); ?></td>
				</tr>
				<?php
				++$count;
			endforeach;
			?>
		</table>
		<?php
	endif;
endif;
?>
