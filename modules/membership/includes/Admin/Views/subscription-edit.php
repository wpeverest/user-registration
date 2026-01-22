<?php
/**
 * Subscription Edit
 */
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;

defined( 'ABSPATH' ) || exit;

$return_url = admin_url( 'admin.php?page=user-registration-subscriptions' );

if ( ! isset( $subscription ) || empty( $subscription ) ) {
	wp_die( esc_html__( 'Subscription not found.', 'user-registration' ) );
}

$membership_id   = $subscription['item_id'];
$membership_post = get_post( $membership_id );
$membership_meta = ur_get_single_post_meta( $membership_id, 'ur_membership' );
$plan_details    = json_decode( wp_unslash( $membership_meta ), true );

$user              = get_userdata( $subscription['user_id'] );
$first_name        = get_user_meta( $subscription['user_id'], 'first_name', true );
$last_name         = get_user_meta( $subscription['user_id'], 'last_name', true );
$user_display_name = $user->user_login;
if ( $first_name || $last_name ) {
	$user_display_name = trim( $first_name . ' ' . $last_name );
} elseif ( ! empty( $user->display_name ) ) {
	$user_display_name = $user->display_name;
} elseif ( ! empty( $user->nickname ) ) {
	$user_display_name = $user->nickname;
}

$currency   = get_option( 'user_registration_payment_currency', 'USD' );
$currencies = ur_payment_integration_get_currencies();
$symbol     = isset( $currencies[ $currency ]['symbol'] ) ? $currencies[ $currency ]['symbol'] : '$';

$billing_cycle_labels = array(
	'day'   => __( 'Daily', 'user-registration' ),
	'week'  => __( 'Weekly', 'user-registration' ),
	'month' => __( 'Monthly', 'user-registration' ),
	'year'  => __( 'Yearly', 'user-registration' ),
);
$billing_cycle_label  = isset( $billing_cycle_labels[ $subscription['billing_cycle'] ] ) ? $billing_cycle_labels[ $subscription['billing_cycle'] ] : '-';

$status_badge_classes = array(
	'active'   => 'ur-subscription__badge--status-active',
	'pending'  => 'ur-subscription__badge--status-pending',
	'trial'    => 'ur-subscription__badge--trial',
	'canceled' => 'ur-subscription__badge--status-failed',
	'expired'  => 'ur-subscription__badge--status-refunded',
);
$status_badge_class   = isset( $status_badge_classes[ $subscription['status'] ] ) ? $status_badge_classes[ $subscription['status'] ] : '';
$product_amount       = isset( $subscription['billing_amount'] ) ? (float) $subscription['billing_amount'] : 0;

$has_active_trial = false;
$trial_has_ended  = false;
$has_no_trial     = empty( $trial_start_date ) && empty( $trial_end_date );
if ( ! empty( $trial_end_date ) ) {
	$trial_end_timestamp = strtotime( $trial_end_date );
	$current_timestamp   = time();
	$trial_has_ended     = $current_timestamp >= $trial_end_timestamp;
	if ( ! $trial_has_ended ) {
		if ( ! empty( $trial_start_date ) ) {
			$trial_start_timestamp = strtotime( $trial_start_date );
			$has_active_trial      = $current_timestamp >= $trial_start_timestamp;
		} else {
			$has_active_trial = true;
		}
	}
}

$orders_repository  = new \WPEverest\URMembership\Admin\Repositories\OrdersRepository();
$subscription_order = $orders_repository->get_order_by_subscription( $subscription['ID'] );
$order_id           = ! empty( $subscription_order ) && isset( $subscription_order['ID'] ) ? $subscription_order['ID'] : '';

$order_meta_data = $orders_repository->get_order_meta_by_order_id_and_meta_key( $order_id, 'tax_data' );
$tax_data 		 = ! empty( $order_meta_data['meta_value'] ) ? json_decode( $order_meta_data[ 'meta_value' ], true ) : array();

$local_currency   = $orders_repository->get_order_meta_by_order_id_and_meta_key( $order_id, 'local_currency' );

$currency = ! empty( $local_currency['meta_value'] ) ? $local_currency['meta_value'] : $currency;
$symbol = ur_get_currency_symbol( $currency );

$local_currency_converted_amount = $orders_repository->get_order_meta_by_order_id_and_meta_key( $order_id, 'local_currency_converted_amount' );

$product_amount = ! empty( $local_currency_converted_amount['meta_value'] ) ? $local_currency_converted_amount['meta_value'] : $product_amount;

$team_data      = null;
$team_name     = '';
$team_size     = '';
$per_seat_price = '';
$team_seats     = '';
$pricing_model  = '';
$tier_info      = null;
$tier_range     = '';
if ( ! empty( $order_id ) ) {
	$ordermeta_table = $orders_repository->wpdb()->prefix . 'ur_membership_ordermeta';
	$team_id         = $orders_repository->wpdb()->get_var(
		$orders_repository->wpdb()->prepare(
			"SELECT meta_value FROM {$ordermeta_table} WHERE meta_key=%s AND order_id=%d LIMIT 1",
			'urm_team_id',
			$order_id
		)
	);

	if ( ! empty( $team_id ) ) {
		$team_data = get_post_meta( $team_id, 'urm_team_data', true );
		if ( ! empty( $team_data ) ) {
			$team_name     = isset( $team_data['team_name'] ) ? $team_data['team_name'] : '';
			$team_size     = isset( $team_data['team_size'] ) ? $team_data['team_size'] : '';
			$pricing_model  = isset( $team_data['pricing_model'] ) ? $team_data['pricing_model'] : '';
			$per_seat_price = isset( $team_data['per_seat_price'] ) ? $team_data['per_seat_price'] : '';
		}
		$team_seats = get_post_meta( $team_id, 'urm_team_seats', true );
		$tier_info  = get_post_meta( $team_id, 'urm_tier_info', true );

		if ( 'tier' === $pricing_model && ! empty( $tier_info ) && isset( $tier_info['tier_per_seat_price'] ) ) {
			$per_seat_price = $tier_info['tier_per_seat_price'];
		}

		$tier_range = '';
		if ( 'tier' === $pricing_model && ! empty( $tier_info ) ) {
			$tier_from = isset( $tier_info['tier_from'] ) ? $tier_info['tier_from'] : '';
			$tier_to   = isset( $tier_info['tier_to'] ) ? $tier_info['tier_to'] : '';
			if ( ! empty( $tier_from ) && ! empty( $tier_to ) ) {
				$tier_range = $tier_from . '-' . $tier_to;
			} elseif ( ! empty( $tier_from ) ) {
				$tier_range = $tier_from . '+';
			}
		}
	}
}

$delete_url = wp_nonce_url(
	admin_url( 'admin.php?page=user-registration-subscriptions&action=delete&id=' . $subscription['ID'] ),
	'ur_subscription_delete'
);
?>
<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
	<div class="ur-page-title__wrapper">
		<div class="ur-page-title__wrapper--left">
			<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2"
				href="<?php echo esc_url( $return_url ); ?>">
				<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
					stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
					<line x1="19" y1="12" x2="5" y2="12"></line>
					<polyline points="12 19 5 12 12 5"></polyline>
				</svg>
			</a>
			<div class="ur-page-title__wrapper--left-menu">
				<div class="ur-page-title__wrapper--left-menu__items">
					<p>
						<?php
						printf(
							/* translators: %d Subscription id */
							esc_html__( 'Edit Subscription #%d', 'user-registration' ),
							esc_html( $subscription['ID'] )
						);
						?>
					</p>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<button type="button" form="ur-membership-subscription-edit-form"
				class="button-primary ur-subscription-update-btn">
				<?php esc_html_e( 'Update', 'user-registration' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="ur-membership">
	<div class="ur-membership-tab-contents-wrapper ur-subscription">
		<form id="ur-membership-subscription-edit-form" method="post" class="ur-subscription__form">
			<?php do_action( 'ur_membership_subscription_edit_form_start', $subscription ); ?>
			<?php wp_nonce_field( 'ur_membership_subscription', 'security' ); ?>
			<input type="hidden" name="id" value="<?php echo esc_attr( $subscription['ID'] ); ?>">
			<input type="hidden" name="start_date" value="<?php echo esc_attr( $subscription['start_date'] ); ?>">
			<div class="ur-subscription__form--left">
				<div class="ur-subscription__main-content">
					<?php if ( $has_active_trial ) : ?>
					<div class="ur-subscription__main-content-wrapper ur-subscription__trial-notice">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
							style="color: rgb(71, 91, 178);">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" x2="12" y1="8" y2="12"></line>
							<line x1="12" x2="12.01" y1="16" y2="16"></line>
						</svg>
						<div>
							<div class="ur-subscription__trial-notice__title">
								<?php esc_html_e( 'Trial period active', 'user-registration' ); ?>
							</div>
							<div class="ur-subscription__trial-notice__content">
								<?php
								if ( ! empty( $trial_end_date ) ) {
									$trial_end_timestamp = strtotime( $trial_end_date );
									$current_timestamp   = time();
									$days_remaining      = max( 0, ceil( ( $trial_end_timestamp - $current_timestamp ) / DAY_IN_SECONDS ) );
									$formatted_date      = date_i18n( get_option( 'date_format' ), $trial_end_timestamp );

									if ( $days_remaining > 0 ) {
										printf(
											/* translators: %1$d: number of days, %2$s: formatted date */
											esc_html__( '%1$d days remaining. Converts to paid on %2$s', 'user-registration' ),
											esc_html( $days_remaining ),
											esc_html( $formatted_date )
										);
									} else {
										printf(
											/* translators: %s: formatted date */
											esc_html__( 'Trial ended. Converts to paid on %s', 'user-registration' ),
											esc_html( $formatted_date )
										);
									}
								} else {
									esc_html_e( 'Trial period active', 'user-registration' );
								}
								?>
							</div>
						</div>
					</div>
					<?php endif ?>
					<div class="ur-subscription__main-content-wrapper">
						<div class="ur-subscription__section-header">
							<h3 class="ur-subscription__section-title">
								<?php
								printf(
									/* translators: %d Subscription id */
									esc_html__( 'Subscription #%d', 'user-registration' ),
									esc_html( $subscription['ID'] )
								);
								?>
							</h3>
							<div class="ur-subscription__badges">
								<span
									class="ur-subscription__badge ur-subscription__badge--status <?php echo esc_attr( $status_badge_class ); ?>">
									<?php echo esc_html( ucfirst( $subscription['status'] ) ); ?>
								</span>
							</div>
						</div>
						<div class="ur-subscription__section-content">
							<div class="ur-subscription__table">
								<div class="ur-subscription__table-head">
									<div><?php esc_html_e( 'Item', 'user-registration' ); ?></div>
									<div><?php esc_html_e( 'Recurring', 'user-registration' ); ?></div>
									<?php if ( ! empty( $tax_data ) ) : ?>
									<div><?php esc_html_e( 'Tax Rate', 'user-registration' ); ?></div>
									<?php endif; ?>
									<div><?php esc_html_e( 'Price', 'user-registration' ); ?></div>
									<?php if ( ! empty( $tax_data ) ) : ?>
									<div><?php esc_html_e( 'Total', 'user-registration' ); ?></div>
									<?php endif; ?>
								</div>
								<div class="ur-subscription__table-body">
									<div>
										<?php if ( $membership_post ) : ?>
										<a href="<?php echo esc_url( admin_url( "admin.php?post_id={$membership_id}&action=add_new_membership&page=user-registration-membership" ) ); ?>"
											class="ur-subscription__table-item-link">
											<?php echo esc_html( $membership_post->post_title ); ?>
										</a>
										<?php else : ?>
											<?php esc_html_e( 'N/A', 'user-registration' ); ?>
										<?php endif; ?>
									</div>
									<div><?php echo esc_html( $billing_cycle_label ); ?></div>
									<?php if ( ! empty( $tax_data ) ) : ?>
										<div class="ur-subscription__table-tax_rate">
											<?php echo esc_html( $tax_data['tax_rate'] . '%' ); ?>
										</div>
									<?php endif; ?>
									<div class="ur-subscription__table-price">
										<?php echo esc_html( $symbol . number_format( $product_amount, 2 ) ); ?>
									</div>
									<?php if ( ! empty( $tax_data ) ) : ?>
										<div class="ur-subscription__table-total">
											<?php echo esc_html( $symbol . number_format( $tax_data['total_after_tax'], 2 ) ); ?>
										</div>
									<?php endif; ?>
								</div>
								<?php
								$number_of_seats = ! empty( $team_seats ) ? (int) $team_seats : ( ! empty( $team_size ) ? (int) $team_size : 1 );
								$items_subtotal  = 0;
								$order_total     = 0;
								$paid_amount     = $product_amount;

								if ( ! empty( $per_seat_price ) && $number_of_seats > 0 ) {
									$items_subtotal = (float) $per_seat_price * $number_of_seats;
									$order_total    = $items_subtotal;
									$paid_amount    = $order_total;
								} else {
									$items_subtotal = $product_amount;
									$order_total    = $product_amount;
								}

								$payment_date = '';
								if ( ! empty( $subscription['start_date'] ) ) {
									$date_format  = get_option( 'date_format' );
									$payment_date = date_i18n( $date_format, strtotime( $subscription['start_date'] ) );
								}
								?>
								<?php if ( ! empty( $team_name ) || ! empty( $per_seat_price ) ) : ?>
								<div class="ur-subscription__fields" style="padding: 20px 14px;">
									<div class="ur-subscription__fields-content">
										<div class="ur-payments__data" style="display: flex; justify-content: space-between;">
											<!-- team Name Section -->
											<?php if ( ! empty( $team_name ) ) : ?>
											<div class="ur-payments__coupon-section">
												<div class="ur-payments__coupon-title" style="font-weight: 600; margin-bottom: 10px;">
													<?php esc_html_e( 'Team Name', 'user-registration' ); ?>
												</div>
												<a href="<?php echo esc_url( admin_url( "admin.php?post_id={$team_id}&action=edit&page=user-registration-team" ) ); ?>">
													<?php echo esc_html( $team_name ); ?>
												</a>
											</div>
											<?php endif; ?>

											<!-- Summary and Payment Tables -->
											<div class="ur-payments__summary-payment-wrapper">
												<!-- Order Summary Table -->
												<table class="ur-payments__summary-table">
													<tbody>
														<tr>
															<td class="ur-payments__summary-label">
																<?php esc_html_e( 'Items Subtotal:', 'user-registration' ); ?>
															</td>
															<td width="1%"></td>
															<td class="ur-payments__summary-total">
																<?php echo esc_html( $symbol . number_format( $items_subtotal, 2 ) ); ?>
															</td>
														</tr>
														<?php if ( ! empty( $number_of_seats ) && $number_of_seats > 0 ) : ?>
														<tr>
															<td class="ur-payments__summary-label">
																<?php esc_html_e( 'Seat', 'user-registration' ); ?>:
															</td>
															<td width="1%"></td>
															<td class="ur-payments__summary-total">
																<?php echo esc_html( $number_of_seats ); ?>
															</td>
														</tr>
														<?php endif; ?>
														<?php if ( ! empty( $per_seat_price ) ) : ?>
														<tr>
															<td class="ur-payments__summary-label">
																<?php
																if ( 'tier' === $pricing_model && ! empty( $tier_range ) ) {
																	printf(
																		/* translators: %1$s: tier range */
																		esc_html__( 'Per seat (tier %1$s)', 'user-registration' ),
																		esc_html( $tier_range )
																	);
																} elseif ( 'tier' === $pricing_model ) {
																	esc_html_e( 'Per seat (tier pricing)', 'user-registration' );
																} else {
																	esc_html_e( 'Per seat', 'user-registration' );
																}
																?>
																:
															</td>
															<td width="1%"></td>
															<td class="ur-payments__summary-total">
																<?php echo esc_html( $symbol . number_format( (float) $per_seat_price, 2 ) ); ?>
															</td>
														</tr>
														<?php endif; ?>
														<tr>
															<td class="ur-payments__payment-label ur-payments__payment-label-highlight">
																<?php esc_html_e( 'Paid:', 'user-registration' ); ?><br>
															</td>
															<td width="1%"></td>
															<td class="ur-payments__payment-total">
																<?php echo esc_html( $symbol . number_format( $paid_amount, 2 ) ); ?>
															</td>
														</tr>
														<?php if ( ! empty( $payment_date ) ) : ?>
														<tr>
															<td>
																<span class="ur-payments__payment-description">
																	<?php echo esc_html( $payment_date ); ?>
																</span>
															</td>
															<td colspan="2"></td>
														</tr>
														<?php endif; ?>
													</tbody>
												</table>

											</div>
										</div>
									</div>
								</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="ur-subscription__main-content-wrapper">
						<div class="ur-subscription__section-header">
							<h3 class="ur-subscription__section-title">
								<?php esc_html_e( 'Payment Details', 'user-registration' ); ?>
							</h3>
						</div>
						<div class="ur-subscription__section-content">
							<div class="ur-subscription__section-column">
								<div class="ur-subscription__section-item">
									<div class="ur-subscription__section-label">
										<?php esc_html_e( 'Amount', 'user-registration' ); ?>
									</div>
									<?php
									$product_amount = ! empty( $tax_data['total_after_tax'] ) ? $tax_data['total_after_tax'] : $product_amount;
									?>
									<div class="ur-subscription__section-value">
										<?php echo esc_html( $symbol . number_format( $product_amount, 2 ) ); ?>
									</div>
								</div>
								<div class="ur-subscription__section-item">
									<div class="ur-subscription__section-label">
										<?php esc_html_e( 'Billing Cycle', 'user-registration' ); ?>
									</div>
									<div class="ur-subscription__section-value">
										<?php echo esc_html( $billing_cycle_label ); ?>
									</div>
								</div>
								<div class="ur-subscription__section-item">
									<div class="ur-subscription__section-label">
										<?php esc_html_e( 'Payment ID', 'user-registration' ); ?>
									</div>
									<div class="ur-subscription__section-value">
										<?php if ( ! empty( $order_id ) ) : ?>
										<a
											href="<?php echo esc_url( admin_url( "admin.php?page=member-payment-history&action=view&id={$order_id}" ) ); ?>">
											#<?php echo esc_html( $order_id ); ?>
										</a>
										<?php else : ?>
											<?php esc_html_e( 'N/A', 'user-registration' ); ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<div class="ur-subscription__section-column ur-subscription__field">
								<div class="ur-subscription__field-row">
									<label class="ur-subscription__section-label" for="ur-subscription-id-field">
										<?php esc_html_e( 'Subscription / Profile ID', 'user-registration' ); ?>
									</label>
									<div class="ur-subscription__section-value">
										<input type="text" name="subscription_id" id="ur-subscription-id-field"
											value="<?php echo esc_attr( $subscription['subscription_id'] ?? '' ); ?>"
											placeholder="<?php esc_attr_e( 'External subscription/transaction ID', 'user-registration' ); ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
					if ( UR_PRO_ACTIVE ) {
						$subscription_events_service = new WPEverest\URMembership\Admin\Services\SubscriptionEventsService();
						$limit                       = 10;
						$events                      = $subscription_events_service->get_events( $subscription['ID'], $limit );
						$total_events                = $subscription_events_service->get_total_events( $subscription['ID'] );

						if ( ! empty( $events ) ) {
							?>
						<div class="ur-subscription__main-content-wrapper"
						data-subscription-id="<?php echo esc_attr( $subscription['ID'] ); ?>"
						data-limit="<?php echo esc_attr( $limit ); ?>"
						data-offset="<?php echo esc_attr( $limit ); ?>"
						data-total="<?php echo esc_attr( $total_events ); ?>">
							<div class="ur-subscription__section-header">
								<h3 class="ur-subscription__section-title">
									<?php esc_html_e( 'Activity Log (Subscription Events)', 'user-registration' ); ?>
								</h3>
							</div>
							<div class="ur-subscription__section-content">
								<?php
									ob_start();
									$subscription_events_service->ur_render_subscription_events_section( $events );
									echo ob_get_clean();
								?>
							</div>
							<div class="ur-subscription__section-footer">
								<?php if ( $total_events > $limit ) : ?>
									<button
										type="button"
										class="button action urm-load-more-events">
										<?php esc_html_e( 'View more', 'user-registration' ); ?>
									</button>
								<?php endif; ?>
							</div>
						</div>
							<?php
						}
					}
					?>
				</div>
			</div>
			<div class="ur-subscription__form--right">
				<div class="ur-subscription__fields">
					<div class="ur-subscription__fields-content">
						<div class="ur-subscription__field-row">
							<div class="ur-subscription__user">
								<?php echo get_avatar( $subscription['user_id'], 122, '', '', array( 'class' => 'ur-subscription__user-avatar' ) ); ?>
								<div class="ur-subscription__user-name">
									<?php echo esc_html( $user_display_name ); ?>
								</div>
								<div class="ur-subscription__user-email"><?php echo esc_html( $user->user_email ); ?>
								</div>
							</div>
						</div>
						<div class="ur-subscription__subscription-actions">
							<?php
							$member_edit_url = add_query_arg(
								array(
									'action'   => 'edit',
									'user_id'  => $subscription['user_id'],
									'_wpnonce' => wp_create_nonce( 'bulk-users' ),
								),
								admin_url( 'admin.php?page=user-registration-users&view_user' ),
							);
							?>
							<a class="button action" href="<?php echo esc_url( $member_edit_url ); ?>">
								<?php esc_html_e( 'Edit Member', 'user-registration' ); ?>
							</a>
							<a class="button action delete single-delete-subscription"
								href="<?php echo esc_url( $delete_url ); ?>">
								<?php esc_html_e( 'Delete Subscription', 'user-registration' ); ?>
							</a>
						</div>
					</div>
				</div>
				<?php do_action( 'ur_membership_subscription_edit_form_before_fields', $subscription ); ?>
				<div class="ur-subscription__fields">
					<div class="ur-subscription__fields-header">
						<?php esc_html_e( 'Edit Subscription', 'user-registration' ); ?>
					</div>
					<div class="ur-subscription__fields-content">
						<div class="ur-subscription__field-row">
							<label class="ur-subscription__field-label" for="ur-subscription-status">
								<?php esc_html_e( 'Status', 'user-registration' ); ?>
								<span style="color:red">*</span>
							</label>
							<div class="ur-subscription__field-input">
								<?php
								$status_options = array(
									'active'   => __( 'Active', 'user-registration' ),
									'pending'  => __( 'Pending', 'user-registration' ),
									'canceled' => __( 'Canceled', 'user-registration' ),
									'expired'  => __( 'Expired', 'user-registration' ),
								);
								$status_options = apply_filters( 'ur_membership_subscription_edit_status_options', $status_options, $subscription );
								?>
								<select name="status" id="ur-subscription-status" class="ur-enhanced-select" required>
									<?php foreach ( $status_options as $status_value => $status_label ) : ?>
									<option value="<?php echo esc_attr( $status_value ); ?>"
										<?php selected( $subscription['status'], $status_value ); ?>>
										<?php echo esc_html( $status_label ); ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<?php do_action( 'ur_membership_subscription_edit_form_fields', $subscription ); ?>
					</div>
				</div>
			</div>
			<?php do_action( 'ur_membership_subscription_edit_form_end', $subscription ); ?>
		</form>
	</div>
</div>
