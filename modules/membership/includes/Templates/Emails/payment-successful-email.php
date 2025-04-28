<table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
	<tr>
		<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Details', 'user-registration' ); ?></th>
		<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Information', 'user-registration' ); ?></th>
	</tr>
	<tr style="background-color: #dddddd;">
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Membership Name', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo ucwords( $invoice_details['membership_plan_name'] ); ?></td>
	</tr>
	<tr>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial Status', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo ucfirst( $invoice_details['membership_plan_trial_status'] ); ?></td>
	</tr>
	<tr style="background-color: #dddddd;">
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial Start Date', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo empty( $invoice_details['membership_plan_trial_start_date'] ) ? __( 'N/A', 'user-registration' ) : ( date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_trial_start_date'] ) ) ); ?></td>
	</tr>
	<tr>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial End Date', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo empty( $invoice_details['membership_plan_trial_start_date'] ) ? __( 'N/A', 'user-registration' ) : ( date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_trial_end_date'] ) ) ); ?></td>
	</tr>
	<tr style="background-color: #dddddd;">
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Next Billing Date', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo empty( $invoice_details['membership_plan_next_billing_date'] ) ? __( 'N/A', 'user-registration' ) : ( date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_next_billing_date'] ) ) ); ?></td>
	</tr>
	<tr>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Payment Date', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['membership_plan_payment_date'] ) ); ?></td>
	</tr>
	<tr style="background-color: #dddddd;">
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Billing Cycle', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['membership_plan_billing_cycle']; ?></td>
	</tr>
	<tr>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Payment Method', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['membership_plan_payment_method']; ?></td>
	</tr>
	<tr style="background-color: #dddddd;">
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Amount', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['membership_plan_payment_amount']; ?></td>
	</tr>
	<tr>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial Amount', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['membership_plan_trial_amount']; ?></td>
	</tr>
	<?php if ( isset( $invoice_details['membership_plan_coupon'] ) && ! empty( $invoice_details['membership_plan_coupon'] ) ) : ?>
		<tr style="background-color: #dddddd;">
			<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Coupon', 'user-registration' ); ?></td>
			<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['membership_plan_coupon']; ?></td>
		</tr>
		<tr>
			<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Coupon Discount', 'user-registration' ); ?></td>
			<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['membership_plan_coupon_discount']; ?></td>
		</tr>
	<?php endif; ?>
	<tr style="background-color: #dddddd;">
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Total', 'user-registration' ); ?></td>
		<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['membership_plan_total']; ?></td>
	</tr>
</table>
