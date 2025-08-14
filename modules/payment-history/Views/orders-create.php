<?php
require __DIR__ . '/header.php';
$return_url = admin_url( 'admin.php?page=member-payment-history' );
?>

<form method="post" id="ur-membership-order-create-form" style="width: 80%">
<div class="user-registration-card">
	<div class="user-registration-card__header ur-d-flex ur-align-items-center" style="gap: 8px;">
		<a style="margin-right: 0; padding-right: 0; border-right: 0; width: 40px; height: 40px; background: #f4f4f4; display: flex; align-items: center; justify-content: center; border-radius: 6px;" class="ur-text-muted ur-d-flex"
			href="<?php echo $return_url ?>">
			<svg viewBox="0 0 24 24" widths="24" height="24" stroke="currentColor" stroke-width="2"
					fill="none"
					stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
				<line x1="19" y1="12" x2="5" y2="12"></line>
				<polyline points="12 19 5 12 12 5"></polyline>
			</svg>
		</a>
		<h3>
			<?php echo esc_html_e( 'Add Manual Payment', 'user-registration' ) ?>
		</h3>
	</div>
	<div class="user_registration-card__body" style="margin: 14px 22px;">
		<div id="ur-membership-orders-create-form" class="user-registration-card">
			<div class="user-registration-card__body">
				<div style="font-size:0.8rem;background-color: #f7fbff; border: 1px solid #475bb2; padding: 12px 16px; border-radius: 4px; line-height: 150%; display: flex; flex-direction: row; gap: 12px; align-items: start;">
					<strong> <?php _e('Important Note:', 'user-registration' ) ?></strong>
					<?php _e('This form is intended only to record missed payments for tracking purposes. Adding a payment here does not renew the next billing cycle or assign any new plan to the user.' , 'user-registration' ) ?>
				</div>

				<!-- Member -->
				<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label for="ur-input-type-membership-plan"><?php esc_html_e( 'Member', 'user-registration' ); ?>
							<span style="color:red">*</span>
							<span class="user-registration-help-tip tooltipstered"
								  data-tip="<?php echo esc_attr__( "Select the user to assign the order to." ) ?>"></span>
						</label>
					</div>
					<div class="ur-input-type-membership-group-name ur-admin-template" style="width: 100%">
						<div class="ur-field">
							<select
								data-key-name="<?php echo esc_html__( 'Membership Plan', 'user-registration' ); ?>"
								id="ur-input-type-member"
								name="ur_member"
								class="user-membership-member-enhanced-select2"
								style="width: 100%"
								required>
								<option value="" disabled selected><?php esc_html_e( 'Search by username or email...', 'user-registration' ); ?></option>
								<?php
								if ( ! empty( $users ) ) {
									foreach ( $users as $user ) :
										?>
										<option
											value="<?php echo esc_attr( $user[ 'user_id' ] ) ?>">
											<?php echo esc_html( $user[ 'user_login' ] ) ?> (<?php echo esc_html( $user[ 'user_email' ] ) ?>)
										</option>
										<?php
									endforeach;
								}
								?>
							</select>
						</div>
					</div>
				</div>


				<!-- Membership Plan -->
				<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label for="ur-input-type-membership-plan"><?php esc_html_e( 'Select Membership Plan', 'user-registration' ); ?>
							<span style="color:red">*</span>
							<span class="user-registration-help-tip tooltipstered"
								  data-tip="<?php echo esc_attr__( "Select the membership plan to assign the user to." ) ?>"></span>
						</label>
					</div>
					<div class="ur-input-type-membership-group-name ur-admin-template" style="width: 100%">
						<div class="ur-field">
							<select
								data-key-name="<?php echo esc_html__( 'Membership Plan', 'user-registration' ); ?>"
								id="ur-input-type-membership-plan"
								name="ur_membership_plan"
								class="user-membership-group-enhanced-select2 urmg-input"
								style="width: 100%"
								required>
								<option value="" disabled selected><?php esc_html_e( 'Select a membership plan', 'user-registration' ); ?></option>
								<?php
								if ( ! empty( $membership_plans ) ) {
									foreach ( $membership_plans as $plan ) :
										?>
										<option
											value="<?php echo esc_attr( $plan['ID'] ); ?>"
											data-amount="<?php echo esc_attr( $plan['amount'] ); ?>">
											<?php echo esc_html( $plan['title'] ); ?>
										</option>
										<?php
									endforeach;
								}
								?>
							</select>
						</div>
					</div>
				</div>

				<!-- Amount -->
				<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label for="ur-input-type-membership-amount"><?php esc_html_e( 'Amount', 'user-registration' ); ?>
							<span style="color:red">*</span>
							<span class="user-registration-help-tip tooltipstered"
								  data-tip="<?php echo esc_attr__( "Payment amount. Automatically fetched from membership plan but can be edited." ) ?>"></span>
						</label>
					</div>
					<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
						<div class="ur-field">
							<input type="text"
								   autocomplete="off"
								   class="ur-membership-amount-input urmg-input"
								   data-key-name="<?php echo esc_html__( 'Amount', 'user-registration' ); ?>"
								   id="ur-input-type-membership-amount"
								   name="ur_membership_amount"
								   style="width: 100%"
								   required>
						</div>
					</div>
				</div>

				<!-- Payment Date -->
				<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label for="ur-input-type-payment-date"><?php esc_html_e( 'Payment Date', 'user-registration' ); ?>
							<span style="color:red">*</span>
							<span class="user-registration-help-tip tooltipstered"
								  data-tip="<?php echo esc_attr__( "Select the payment date." ) ?>"></span>
						</label>
					</div>
					<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
						<div class="ur-field">
							<input type="date"
								   autocomplete="off"
								   class="ur-payment-date-input ur-date urmg-input"
								   data-key-name="<?php echo esc_html__( 'Payment Date', 'user-registration' ); ?>"
								   id="ur-input-type-payment-date"
								   name="ur_payment_date"
								   style="width: 100%"
								   required>
						</div>
					</div>
				</div>

				<!-- Transaction Status -->
				<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label for="ur-input-type-transaction-status"><?php esc_html_e( 'Transaction Status', 'user-registration' ); ?>
							<span style="color:red">*</span>
							<span class="user-registration-help-tip tooltipstered"
								  data-tip="<?php echo esc_attr__( "Select the transaction status." ) ?>"></span>
						</label>
					</div>
					<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
						<div class="ur-field">
							<select
								data-key-name="<?php echo esc_html__( 'Transaction Status', 'user-registration' ); ?>"
								id="ur-input-type-transaction-status"
								name="ur_transaction_status"
								class="urmg-input"
								style="width: 100%"
								required>
								<option value=""><?php esc_html_e( 'Select status', 'user-registration' ); ?></option>
								<option
									value=""><?php echo esc_html__( 'All Status', 'user-registration' ); ?></option>
								<?php
								foreach ( array( 'completed', 'pending', 'failed', 'refunded' ) as $id => $status ) {
									$selected = isset( $_REQUEST['status'] ) && $status == $_REQUEST['status'] ? 'selected=selected' : '';
									?>
									<option
										value='<?php echo esc_attr( $status ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<?php
	$save_btn_class  = 'ur-add-new-payment';
	$create_btn_text = esc_html__( 'Add Payment', 'user-registration' );
	require __DIR__ . '/footer-actions.php';
	?>
	<?php wp_nonce_field( 'ur_membership_order', 'ur_membership_order_nonce' ); ?>
</div>
</form>
