<?php
/**
 * payment-successful.php
 *
 * @class    payment-successful.php
 * @package  Membership
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo esc_html__( 'Payment Successful Email', 'user-registration' ); ?></title>
</head>
<body style="background-color: #ebebeb;">
<div style="padding: 100px 0;">
	<div
		style="width: 80%; margin: 0 auto; background: #ffffff; padding: 30px 30px 26px; border: 0.4px solid #d3d3d3; border-radius: 11px; font-family: 'Segoe UI', sans-serif;">
		<p><?php echo wp_kses_post( sprintf( 'Hi, <b><i>%s</i></b>, Your Payment has been successfully received.', $user->user_login ), 'user-registration' ); ?></p>
		<p><?php echo wp_kses_post( $extra_message ); ?></p>
		<table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
			<tr>
				<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Field', 'user-registration' ); ?></th>
				<th style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Details', 'user-registration' ); ?></th>
			</tr>
			<tr style="background-color: #dddddd;">
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Membership Name', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo ucwords( $invoice_details['membership_name'] ); ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial Status', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo ucfirst( $invoice_details['trial_status'] ); ?></td>
			</tr>
			<tr style="background-color: #dddddd;">
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial Start Date', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo empty( $invoice_details['trial_start_date'] ) ? __( 'N/A', 'user-registration' ) : ( date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['trial_start_date'] ) ) ); ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial End Date', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo empty( $invoice_details['trial_start_date'] ) ? __( 'N/A', 'user-registration' ) : ( date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['trial_end_date'] ) ) ); ?></td>
			</tr>
			<tr style="background-color: #dddddd;">
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Next Billing Date', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo empty( $invoice_details['next_billing_date'] ) ? __( 'N/A', 'user-registration' ) : ( date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['next_billing_date'] ) ) ); ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Payment Date', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $invoice_details['payment_date'] ) ); ?></td>
			</tr>
			<tr style="background-color: #dddddd;">
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Billing Cycle', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['billing_cycle']; ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Amount', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['amount']; ?></td>
			</tr>
			<tr style="background-color: #dddddd;">
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Trial Amount', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['trial_amount']; ?></td>
			</tr>
			<?php if ( isset( $invoice_details['coupon'] ) && ! empty( $invoice_details['coupon'] ) ) : ?>
				<tr>
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Coupon', 'user-registration' ); ?></td>
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['coupon']; ?></td>
				</tr>
				<tr style="background-color: #dddddd;">
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Coupon Discount', 'user-registration' ); ?></td>
					<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['coupon_discount']; ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo esc_html__( 'Total', 'user-registration' ); ?></td>
				<td style="border: 1px solid #dddddd; text-align: left; padding: 8px;"><?php echo $invoice_details['total']; ?></td>
			</tr>
		</table>
	</div>
</div>
</body>
</html>
