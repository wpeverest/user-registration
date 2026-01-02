<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/membership.php.
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

if ( empty( $membership_data ) ) {
	echo esc_html_e( 'You do not have any records', 'user-registration' );
	return;
}

$current = $page;

$current_url = get_permalink( get_option( 'user_registration_myaccount_page_id' ) ) . 'ur-membership/';
?>
<div class="user-registration-MyAccount-content__body">
	<div class="ur-account-table-container">
		<div class="ur-account-table-wrapper">
			<table class="ur-account-table">
				<thead class="ur-account-table__header">
					<tr class="ur-account-table__row">
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Membership', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Terms', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Status', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Start Date', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Next Billing Date', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Action', 'user-registration' ); ?></th>
					</tr>
				</thead>
				<tbody class="ur-account-table__body">
					<?php
					foreach ( $membership_data as $data ) :
						$membership      = isset( $data['membership'] ) ? $data['membership'] : array();
						$is_upgraded     = ! empty( $_GET['is_upgraded'] ) ? absint( ur_string_to_bool( $_GET['is_upgraded'] ) ) : false;
						$message         = ! empty( $_GET['message'] ) ? esc_html( $_GET['message'] ) : '';
						$membership_info = ( isset( $_GET['info'] ) && ! empty( $_GET['info'] ) ) ? wp_kses_post_deep( $_GET['info'] ) : ( ! empty( $data['bank_data']['bank_data'] ) ? wp_kses_post_deep( $data['bank_data']['bank_data'] ) : '' );
						$is_delayed      = ! empty( $data['delayed_until'] );
						$user_id         = get_current_user_id();
						$is_renewing     = ur_string_to_bool( get_user_meta( $user_id, 'urm_is_member_renewing', true ) );

						$can_renew     = ! $is_renewing && isset( $membership['post_content']['type'] ) && 'automatic' !== $data['renewal_behaviour'] && 'subscription' == $membership['post_content']['type'];
						$date_to_renew = '';

						if ( 'subscription' == $membership['post_content']['type'] ) {
							$start_date    = $data['subscription_data']['start_date'];
							$expiry_date   = $data['subscription_data']['expiry_date'];
							$date_to_renew = urm_get_date_at_percent_interval( $start_date, $expiry_date, apply_filters( 'urm_show_membership_renewal_btn_in_percent', 80 ) ); // keeping this static for now can be changed to a setting in future
						}

						$current_url = get_permalink( get_option( 'user_registration_myaccount_page_id' ) ) . 'ur-membership/';
						?>
							<tr class="ur-account-table__row">
								<td class="ur-account-table__cell ur-account-table__cell--membership-type"><?php echo isset( $membership['post_title'] ) && ! empty( $membership['post_title'] ) ? esc_html( $membership['post_title'] ) : __( 'N/A', 'user-registration' ); ?></td>
								<td class="ur-account-table__cell ur-account-table__cell--terms"><?php echo esc_html( $data['period'] ?? '-' ); ?></td>
								<td class="ur-account-table__cell ur-account-table__cell--status">
									<?php
									$status = '';
									if ( isset( $membership['status'] ) && ! empty( $membership ) ) {
										$status = 'inactive';
										$status = ( '' != $membership['status'] ) ? $membership['status'] : $status;
										if ( 'inactive' !== $status && 'free' !== $membership['post_content']['type'] && 'paid' !== $membership['post_content']['type'] && ! empty( $membership['billing_cycle'] ) ) {
											$expiry_date = new DateTime( $membership['expiry_date'] );
											if ( date( 'Y-m-d' ) > $expiry_date->format( 'Y-m-d' ) ) {
												$status = 'expired';
											}
										}
									}
									?>
									<?php
									if ( ! empty( $status ) ) :
										$membership_statuses = array(
											'paid'     => __( 'Paid', 'user-registration' ),
											'free'     => __( 'Free', 'user-registration' ),
											'inactive' => __( 'In Active', 'user-registration' ),
											'expired'  => __( 'Expired', 'user-registration' ),
											'active'   => __( 'Active', 'user-registration' ),
										);

										$membership_status = isset( $membership_statuses[ strtolower( $status ) ] )
											? $membership_statuses[ strtolower( $status ) ]
											: ucfirst( $status );
										?>
										<span id="ur-membership-status" class="btn-<?php echo $status; ?>">
												<?php echo esc_html( $membership_status ); ?>
										</span>
										<?php
									else :
										echo __( 'N/A', 'user-registration' );
									endif;
									?>
								</td>
								<td class="ur-account-table__cell ur-account-table__cell--date"><?php echo ! empty( $membership['start_date'] ) ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $membership['start_date'] ) ) ) : __( 'N/A', 'user-registration' ); ?></td>
								<td class="ur-account-table__cell ur-account-table__cell--billing-date"><?php echo ! empty( $membership['next_billing_date'] ) && strtotime( $membership['next_billing_date'] ) > 0 ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $membership['next_billing_date'] ) ) ) : __( 'N/A', 'user-registration' ); ?></td>
								<td class="ur-account-table__cell ur-account-table__cell--action">
									<div class="membership-row-btn-container">
										<?php
										$buttons = array();

										if ( isset( $data['form_type'] ) && 'normal' === $data['form_type'] ) {
											if ( isset( $data['buttons'] ) && ! empty( $data['buttons'] ) ) {
												$buttons = $data['buttons'];
											}
										} else {

											if ( isset( $data['is_upgrading'] ) && ! $data['is_upgrading'] && ! empty( $membership ) ) :
												$membership_checkout_page_id = get_option( 'user_registration_member_registration_page_id', false );
												$redirect_page_url           = get_permalink( $membership_checkout_page_id );
												$thank_you_page_id           = get_option( 'user_registration_thank_you_page_id', false );
												$uuid                        = ur_generate_random_key();
												$subscription_id             = $membership['subscription_id'];
												$redirect_page_url           = $redirect_page_url . '?action=upgrade&current="' . $membership['post_id'] . '"&subscription_id="' . $membership['subscription_id'] . '"&thank_you="' . $thank_you_page_id . '"';
												$upgradable_plans            = $membership_service->get_upgradable_membership( $membership['post_id'] );
												?>
												<?php
												if ( 'canceled' !== $membership['status'] && ! empty( $upgradable_plans ) ) :
													$buttons[] = '<a class="ur-account-action-link membership-tab-btn change-membership-button" href="' . esc_url_raw( $redirect_page_url ) . '" data-id="' . esc_attr( $membership['post_id'] ?? '' ) . '">' . esc_html__( 'Change Plan', 'user-registration' ) . '</a>';
													?>
											<?php endif; ?>
												<?php
												$membership_type = isset( $membership['post_content'] ) && ! empty( $membership['post_content'] ) ? esc_html( ucfirst( wp_unslash( $membership['post_content']['type'] ) ) ) : 'NA';
												if ( 'canceled' === $membership['status'] && ( $membership_type !== 'subscription' || $date_to_renew > date( 'Y-m-d 00:00:00' ) ) ) {
													$buttons[] = '<a class="ur-account-action-link membership-tab-btn reactivate-membership-button" href="#" data-id="' . esc_attr( $membership['subscription_id'] ?? '' ) . '">' . esc_html__( 'Reactivate Membership', 'user-registration' ) . '</a>';
												}
												?>

												<?php
												if ( $can_renew && $date_to_renew <= date( 'Y-m-d 00:00:00' ) && 'canceled' !== $membership['status'] ) {
													$buttons[] = '<a class="ur-account-action-link membership-tab-btn renew-membership-button" href="' . esc_url( $redirect_page_url ) . '" data-pg-gateways="' . ( isset( $membership['active_gateways'] ) ? implode( ',', array_keys( $membership['active_gateways'] ) ) : '' ) . '" data-id="' . esc_attr( $membership['post_id'] ?? '' ) . '">' . esc_html__( 'Renew Membership', 'user-registration' ) . '</a>';
												}
												?>
												<?php
											endif;
											?>
											<?php
											if ( 'canceled' !== $membership['status'] ) {
												$buttons[] = '<a class="ur-account-action-link membership-tab-btn cancel-membership-button" data-id="' . esc_attr( $membership['subscription_id'] ?? '' ) . '"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' . esc_html__( 'Cancel', 'user-registration' ) . '</a>';
											}
										}
										?>
										<div class="btn-div">
											<?php
											if ( ! empty( $buttons ) ) {
												if ( count( $buttons ) > 1 ) {
													?>
													<div class="action-menu">
														<button class="menu-trigger" type="button">
															<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
																<path d="M10 12a2.222 2.222 0 1 1 4.444 0A2.222 2.222 0 0 1 10 12Zm0-7.778a2.222 2.222 0 1 1 4.444 0 2.222 2.222 0 0 1-4.444 0Zm0 15.556a2.222 2.222 0 1 1 4.444 0 2.222 2.222 0 0 1-4.444 0Z"/>
															</svg>
														</button>
														<div class="hidden dropdown">
															<?php foreach ( $buttons as $button ) : ?>
																<?php echo $button; ?>
															<?php endforeach; ?>
														</div>
													</div>
													<?php
												} else {
													echo implode( ' | ', $buttons );
												}
											}
											?>
											<!--
										<?php

										if ( ( isset( $data['is_upgrading'] ) && $data['is_upgrading'] ) || $is_renewing || ( isset( $data['is_purchasing_multiple'] ) && $data['is_purchasing_multiple'] ) ) :

											if ( ! empty( $data['bank_data']['notice_1'] ) ) :
												?>
												<div id="bank-notice" class="btn-success">
													<div class="user-registration-myaccount-notice-box">
														<?php

														if ( $data['is_upgrading'] ) {
															echo isset( $data['bank_data']['notice_1'] ) ? $data['bank_data']['notice_1'] : '';
														} elseif ( $is_renewing ) {
															echo isset( $data['bank_data']['notice_2'] ) ? $data['bank_data']['notice_2'] : '';
														} elseif ( $data['is_purchasing_multiple'] ) {
															echo isset( $data['bank_data']['notice_3'] ) ? $data['bank_data']['notice_3'] : '';
														}
														?>
													</div>
													<span class="view-bank-data">
														<?php
														echo __( 'Bank Info', 'user-registration' );
														?>
													</span>
													</div>
												<?php
											endif;
											?>
											<div class="upgrade-info urm-d-none">
												<?php
												echo $membership_info;
												?>
											</div>
											<?php
										endif;
										?>
										-->
										</div>
									</div>
								</td>
							</tr>
						<?php
					endforeach;
					?>
				</tbody>
			</table>
		</div>
		<?php
		if ( $total_pages > 1 ) :
			?>
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
