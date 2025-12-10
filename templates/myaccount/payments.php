<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/payment-history.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $orders ) ) {
	return esc_html_e( 'You do not have any payment records', 'user-registration' );
}

?>
<div class="user-registration-MyAccount-content__body">

	<div class="ur-account-table-container">
		<div class="ur-account-table-wrapper">

			<table class="ur-account-table">
				<thead class="ur-account-table__header">
					<tr class="ur-account-table__row">
						<th class="ur-account-table__cell ur-account-table__header-cell">
							<?php esc_html_e( 'Transaction ID', 'user-registration' ); ?>
						</th>
						<th class="ur-account-table__cell ur-account-table__header-cell">
							<?php esc_html_e( 'Gateway', 'user-registration' ); ?>
						</th>
						<th class="ur-account-table__cell ur-account-table__header-cell">
							<?php esc_html_e( 'Amount', 'user-registration' ); ?>
						</th>
						<th class="ur-account-table__cell ur-account-table__header-cell">
							<?php esc_html_e( 'Status', 'user-registration' ); ?>
						</th>
						<th class="ur-account-table__cell ur-account-table__header-cell">
							<?php esc_html_e( 'Payment Date', 'user-registration' ); ?>
						</th>
						<th class="ur-account-table__cell ur-account-table__header-cell">
							<?php esc_html_e( 'Action', 'user-registration' ); ?>
						</th>
					</tr>
				</thead>

				<tbody class="ur-account-table__body">
					<?php foreach ( $orders as $user_order ) : ?>
						<tr class="ur-account-table__row">
							<td class="ur-account-table__cell">
								<?php echo esc_html( $user_order['transaction_id'] ?? '-' ); ?>
							</td>

							<td class="ur-account-table__cell">
								<?php echo esc_html( $user_order['payment_method'] ?? '-' ); ?>
							</td>

							<td class="ur-account-table__cell">
								<?php echo esc_html( $user_order['total_amount'] ?? '-' ); ?>
							</td>

							<td class="ur-account-table__cell">
								<?php echo esc_html( $user_order['status'] ?? '-' ); ?>
							</td>

							<td class="ur-account-table__cell">
								<?php
								echo ! empty( $user_order['created_at'] )
									? esc_html( gmdate( 'Y-m-d H:i:s', strtotime( $user_order['created_at'] ) ) )
									: '-';
								?>
							</td>

							<td class="ur-account-table__cell ur-account-table__cell--action">
								<?php
								$url          = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
								$url          = substr( $url, 0, strpos( $url, '?' ) );
								$download_url = wp_nonce_url( $url . '?payment_action=invoice_download&transaction_id=' . $user_order['transaction_id'], 'ur_payment_action' );
								?>

								<a class="ur-account-action-link" href="<?php echo esc_url( $download_url ); ?>">
									<?php esc_html_e( 'Download', 'user-registration' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>

			</table>

		</div>
	</div>

</div>

<?php
