<?php
$user_id = absint( !empty($invoice_details['user_id']) ? $invoice_details['user_id'] : get_current_user_id() );
$currencies = ur_payment_integration_get_currencies();
$currency   = get_user_meta( $user_id, 'ur_payment_currency', true );
$currency   = ! empty( $currency ) ? $currency : 'USD';
$symbol     = $currencies[ $currency ]['symbol'] ?? '$';
$trial_status = isset($invoice_details['membership_plan_trial_status']) ? $invoice_details['membership_plan_trial_status'] : 'off';
$trial_amount = $trial_status === 'On' ? $symbol."0.00" : $invoice_details['membership_plan_trial_amount'];
$total_amount = $trial_status === 'On' ? $symbol."0.00" : $invoice_details['membership_plan_total'];
if ( $invoice_details['is_membership'] ) :

	// Define label–key pairs for membership rows
	$membership_fields = [
		__( 'Membership Name', 'user-registration' )      => ucwords( $invoice_details['membership_plan_name'] ),
		__( 'Trial Status', 'user-registration' )         => ucfirst($trial_status),
		__( 'Trial Start Date', 'user-registration' )     => empty( $invoice_details['membership_plan_trial_start_date'] ) ? __( 'N/A', 'user-registration' ) : date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_trial_start_date'] ) ),
		__( 'Trial End Date', 'user-registration' )       => empty( $invoice_details['membership_plan_trial_end_date'] ) ? __( 'N/A', 'user-registration' ) : date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_trial_end_date'] ) ),
		__( 'Next Billing Date', 'user-registration' )    => empty( $invoice_details['membership_plan_next_billing_date'] ) ? __( 'N/A', 'user-registration' ) : date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_next_billing_date'] ) ),
		__( 'Payment Date', 'user-registration' )         => date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_payment_date'] ) ),
		__( 'Billing Cycle', 'user-registration' )        => $invoice_details['membership_plan_billing_cycle'],
		__( 'Payment Method', 'user-registration' )       => $invoice_details['membership_plan_payment_method'],
		__( 'Amount', 'user-registration' )               => $invoice_details['membership_plan_payment_amount'],
		__( 'Trial Amount', 'user-registration' )         => $trial_amount,
	];

	// Add coupon details if they exist
	if ( ! empty( $invoice_details['membership_plan_coupon'] ) ) {
		$membership_fields[ __( 'Coupon', 'user-registration' ) ] = $invoice_details['membership_plan_coupon'];
		$membership_fields[ __( 'Coupon Discount', 'user-registration' ) ] = $invoice_details['membership_plan_coupon_discount'];
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
			$index++;
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
				$value = get_user_meta( $user_id, $meta_key, true );
				if ( 'ur_payment_total_amount' === $meta_key ) {
					$value = $symbol . $value;
				}
				?>
				<tr style="<?php echo esc_attr( $bg_color ); ?>">
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html( $title ); ?></td>
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html( $value ); ?></td>
				</tr>
				<?php
				$count++;
			endforeach;
			?>
		</table>
	<?php
	endif;
endif;
?>
