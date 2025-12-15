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
	exit;
}

if ( empty( $orders ) || empty( $orders['items'] ) ) {
	echo esc_html_e( 'You do not have any payment records', 'user-registration' );
	return;
}

$items       = $orders['items'];
$current     = intval( $orders['page'] ?? 1 );
$total_pages = intval( $orders['total_pages'] ?? 1 );
$per_page    = intval( $orders['per_page'] ?? 10 );

$current_url = get_permalink( get_option( 'user_registration_myaccount_page_id' ) ) . 'urm-payments/';
?>

<div class="user-registration-MyAccount-content__body">
	<div class="ur-account-table-container">
		<div class="ur-account-table-wrapper">

			<table class="ur-account-table">
				<thead class="ur-account-table__header">
					<tr class="ur-account-table__row">
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Transaction ID', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Gateway', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Amount', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Status', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Payment Date', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Action', 'user-registration' ); ?></th>
					</tr>
				</thead>

				<tbody class="ur-account-table__body">

					<?php
					foreach ( $items as $user_order ) :
						$total_amount = $user_order['total_amount'] ? number_format( $user_order['total_amount'], 2 ) : '-';

						if ( isset( $user_order['currency'] ) ) {
							$total_amount .= ' ' . $user_order['currency'];
						} else {
							$user_id       = get_current_user_id();
							$currency      = get_user_meta( $user_id, 'ur_payment_currency', true );
							$total_amount .= ' ' . $currency;
						}

						?>
						<tr class="ur-account-table__row">
							<td class="ur-account-table__cell ur-account-table__cell--transaction-id"><?php echo esc_html( $user_order['transaction_id'] ?? '-' ); ?></td>
							<td class="ur-account-table__cell ur-account-table__cell--payment"><?php echo esc_html( $user_order['payment_method'] ?? '-' ); ?></td>
							<td class="ur-account-table__cell ur-account-table__cell--amount"><?php echo esc_html( $total_amount ); ?></td>
							<td class="ur-account-table__cell ur-account-table__cell--status"><span id="ur-membership-status" class="btn-<?php echo esc_attr( $user_order['status'] ?? '-' ); ?>"><?php echo esc_html( $user_order['status'] ?? '-' ); ?></span></td>
							<td class="ur-account-table__cell ur-account-table__cell--date">
								<?php
								echo ! empty( $user_order['created_at'] )
									? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $user_order['created_at'] ) ) )
									: '-';
								?>
							</td>
							<td class="ur-account-table__cell ur-account-table__cell--action">
								<?php
								// Build download link safely using add_query_arg and wp_nonce_url
								$download_args = array(
									'payment_action' => 'invoice_download',
									'transaction_id' => $user_order['transaction_id'] ?? '',
								);
								$download_url  = wp_nonce_url( add_query_arg( $download_args, $current_url ), 'ur_payment_action' );
								?>
								<a class="ur-account-action-link" href="<?php echo esc_url( $download_url ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
										<path d="M11 15V3a1 1 0 1 1 2 0v12a1 1 0 1 1-2 0Z"/>
										<path d="M2 19v-4a1 1 0 1 1 2 0v4l.005.099A1 1 0 0 0 5 20h14a1 1 0 0 0 1-1v-4a1 1 0 1 1 2 0v4a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3Z"/>
										<path d="M16.293 9.293a1 1 0 1 1 1.414 1.414l-5 5a1 1 0 0 1-1.414 0l-5-5a1 1 0 1 1 1.414-1.414L12 13.586l4.293-4.293Z"/>
									</svg>
									<?php esc_html_e( 'Download', 'user-registration' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>

				</tbody>
			</table>

		</div>

		<!--
		<?php
		if ( $total_pages > 1 ) :
			?>
			-->
			<div class="ur-pagination">
				<?php
				echo paginate_links(
					array(
						'base'      => trailingslashit( $current_url ) . '%_%',
						'format'    => 'page/%#%/',
						'current'   => $current,
						'total'     => $total_pages,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'type'      => 'list',
					)
				);
				?>
				</div>

				<?php
				endif;
		?>

	</div>
</div>
