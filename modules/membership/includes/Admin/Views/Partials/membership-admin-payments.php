<?php
/**
 * membership-admin-payments.php
 *
 * membership-admin-payments.php
 *
 * @package  URMembership
 * @date     9/5/2024 : 9:51 AM
 */

?>
<!--								payment gateway container-->
<div id="payment-gateway-container"
	class="ur-mt-3 <?php echo isset( $membership_details['type'] ) && $membership_details['type'] !== 'free' ? '' : 'ur-d-none'; ?>">
	<div class="user-registration-card">
		<div class="user-registration-card__header">
			<h3>Payment Gateway</h3>
		</div>
		<div class="user-registration-card__body ur-d-flex ur-flex-column"
			style="gap: 20px">
			<?php
			render_payment_gateways( $membership_details );
			?>
		</div>
	</div>
</div>

<?php
/**
 * render_payment_gateways
 *
 * @param $membership_details
 *
 * @return void
 */
function render_payment_gateways( $membership_details ) {
	$enabled_features = get_option( 'user_registration_enabled_features', array() );
	if ( in_array( 'user-registration-payments', $enabled_features ) || ! UR_PRO_ACTIVE ) :
		render_paypal_settings( $membership_details );
	endif;
	// render bank settings
	render_bank_settings( $membership_details );
	render_stripe_settings( $membership_details );
}

/**
 * render_paypal_settings
 *
 * @param $membership_details
 *
 * @return void
 */
function render_paypal_settings( $membership_details ) {
	?>
	<div id="paypal-section"
		class="ur-p-2 "
		style="background: #f8f8fa; border-radius:5px"
	>
		<div
			id="ur-membership-paypal-toggle-container"
			class="ur-d-flex ur-justify-content-between ur-payment-option-header"
		>
			<h2><?php echo __( 'Paypal', 'user-registration' ); ?></h2>
			<div class="user-registration-switch">

				<input
					data-key-name="Payment Gateway"
					id="ur-membership-pg-paypal" type="checkbox"
					class="user-registration-switch__control hide-show-check enabled pg-switch"
					<?php echo isset( $membership_details['payment_gateways']['paypal'] ) && $membership_details['payment_gateways']['paypal']['status'] == 'on' ? 'checked' : ''; ?>
					name="ur_membership_pg_paypal_status"
				>
				<svg class="ur-pg-arrow
																<?php echo isset( $membership_details['payment_gateways']['paypal'] ) && $membership_details['payment_gateways']['paypal']['status'] == 'on' ? 'expand' : ''; ?>
																" xmlns="http://www.w3.org/2000/svg" fill="none"
					viewBox="0 0 24 24">
					<path stroke="#383838" stroke-linecap="round"
							stroke-linejoin="round" stroke-width="2"
							d="m9 18 6-6-6-6"></path>
				</svg>
			</div>
		</div>
		<div class="payment-option-body"
			data-target-id="ur-membership-paypal-toggle-container"
			style="<?php echo isset( $membership_details['payment_gateways']['paypal'] ) && $membership_details['payment_gateways']['paypal']['status'] == 'on' ? '' : 'display:none'; ?>">
			<!--					paypal email-->
			<div class="ur-membership-input-container ur-d-flex ur-p-1"
				style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label
						for="ur-input-type-paypal-email"><?php esc_html_e( 'Paypal Email', 'user-registration' ); ?>
						<span style="color:red">*</span>
					</label>
				</div>
				<div
					class="ur-input-type-membership-email ur-admin-template"
					style="width: 100%">
					<div class="ur-field" data-field-key="paypal_email">
						<input type="email"
								data-key-name="<?php esc_html_e( 'Paypal Email', 'user-registration' ); ?>"
								id="ur-input-type-paypal-email"
								name="ur_membership_paypal_email"
								style="width: 100%"
								value="<?php echo $membership_details['payment_gateways']['paypal']['email'] ?? ''; ?>"
								required>
					</div>
				</div>

			</div>
			<!--														paypal mode-->
			<div
				class="ur-membership-selection-container ur-d-flex ur-p-1 ur-mt-3"
				style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label
						for="ur-membership-paypal-mode"><?php esc_html_e( 'Mode', 'user-registration' ); ?>
						<span style="color:red">*</span>
					</label>
				</div>
				<div class="ur-field"
					data-field-key="membership_duration"
					style="width: 100%;">
					<select
						id="ur-membership-paypal-mode"
						data-key-name="Duration"
						class=""
						name="ur_membership[duration]_period"
						style="width: 100%">
						<option
							value="sandbox" <?php echo isset( $membership_details['payment_gateways']['paypal']['mode'] ) && $membership_details['payment_gateways']['paypal'] == 'sandbox' ? 'selected="selected"' : ''; ?>
						>
							<?php esc_html_e( 'Sandbox', 'user-registration' ); ?>
						</option>
						<option
							value="production" <?php echo isset( $membership_details['payment_gateways']['paypal']['mode'] ) && $membership_details['payment_gateways']['paypal']['mode'] == 'production' ? 'selected="selected"' : ''; ?>
						>
							<?php esc_html_e( 'Production', 'user-registration' ); ?>
						</option>

					</select>
				</div>
			</div>
			<!--														paypal cancel url-->
			<div
				class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
				style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label
						for="ur-input-type-cancel-url"><?php esc_html_e( 'Cancel Url', 'user-registration' ); ?>
						<span style="color:red">*</span>
					</label>
				</div>
				<div
					class="ur-admin-template"
					style="width: 100%">
					<div class="ur-field"
						data-field-key="paypal_cancel_url">
						<input type="url"
								data-key-name="<?php esc_html_e( 'Cancel Url', 'user-registration' ); ?>"
								id="ur-input-type-cancel-url"
								name="ur_membership_cancel_url"
								style="width: 100%"
								value="<?php echo $membership_details['payment_gateways']['paypal']['cancel_url'] ?? ''; ?>"
								required>
					</div>
				</div>

			</div>
			<!--														paypal return url-->
			<div
				class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
				style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label
						for="ur-input-type-return-url"><?php esc_html_e( 'Return Url', 'user-registration' ); ?>
						<span style="color:red">*</span>
					</label>
				</div>
				<div
					class="ur-input-type-return-url ur-admin-template"
					style="width: 100%">
					<div class="ur-field" data-field-key="return_url">
						<input type="url"
								data-key-name="<?php esc_html_e( 'Return Url', 'user-registration' ); ?>"
								id="ur-input-type-return-url"
								name="ur_membership_return_url"
								style="width: 100%"
								value="<?php echo $membership_details['payment_gateways']['paypal']['return_url'] ?? ''; ?>"
								required>
					</div>
				</div>

			</div>
			<div
				class="ur-membership-subscription-field-container <?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'subscription' ? '' : 'ur-d-none'; ?>"
			>
				<!--														client id-->
				<div
					class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
					style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label
							for="ur-input-type-client-id"><?php esc_html_e( 'Client ID', 'user-registration' ); ?>
							<span style="color:red">*</span>
						</label>
					</div>
					<div
						class="ur-input-type-client-id ur-admin-template"
						style="width: 100%">
						<div class="ur-field" data-field-key="client_id">
							<input type="text"
									autocomplete="off"
									data-key-name="<?php esc_html_e( 'Client ID', 'user-registration' ); ?>"
									id="ur-input-type-client-id"
									name="ur_membership_client_id"
									style="width: 100%"
									value="<?php echo $membership_details['payment_gateways']['paypal']['client_id'] ?? ''; ?>"
									required>
						</div>
					</div>

				</div>
				<!--														client secret-->
				<div
					class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
					style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label
							for="ur-input-type-client-secret"><?php esc_html_e( 'Client Secret', 'user-registration' ); ?>
							<span style="color:red">*</span>
						</label>
					</div>
					<div
						class="ur-input-type-client-secret ur-admin-template"
						style="width: 100%">
						<div class="ur-field"
							data-field-key="client_secret">
							<input type="text"
									autocomplete="off"
									data-key-name="<?php esc_html_e( 'Client Secret', 'user-registration' ); ?>"
									id="ur-input-type-client-secret"
									name="ur_membership_client_secret"
									style="width: 100%"
									value="<?php echo $membership_details['payment_gateways']['paypal']['client_secret'] ?? ''; ?>"
									required>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * render_bank_settings
 *
 * @param $membership_details
 *
 * @return void
 */
function render_bank_settings( $membership_details ) {
	?>
	<div id="bank-section"
		class="ur-p-2 "
		style="background: #f8f8fa; border-radius:5px">
		<?php
		$bank_details = $membership_details['payment_gateways']['bank'] ?? '';
		?>
		<div
			id="ur-membership-bank-toggle-container"
			class="ur-d-flex ur-justify-content-between ur-payment-option-header">
			<h2><?php echo __( 'Bank Transfer', 'user-registration' ); ?></h2>
			<div class="user-registration-switch">
				<input
					data-key-name="Payment Gateway"
					id="ur-membership-pg-bank" type="checkbox"
					class="user-registration-switch__control hide-show-check ur-payment-option-header enabled pg-switch"
					<?php echo isset( $bank_details['status'] ) && $bank_details['status'] == 'on' ? 'checked' : ''; ?>
					name="ur_membership_pg_bank_status"
				>
				<svg class="ur-pg-arrow
																<?php echo isset( $bank_details['status'] ) && $bank_details['status'] == 'on' ? 'expand' : ''; ?>
																" xmlns="http://www.w3.org/2000/svg" fill="none"
					viewBox="0 0 24 24">
					<path stroke="#383838" stroke-linecap="round"
							stroke-linejoin="round" stroke-width="2"
							d="m9 18 6-6-6-6"></path>
				</svg>
			</div>
		</div>
		<div class="payment-option-body"
			data-target-id="ur-membership-bank-toggle-container"
			style="<?php echo isset( $bank_details['status'] ) && $bank_details['status'] == 'on' ? '' : 'display:none'; ?>">
			<?php
			wp_editor(
				$bank_details['content'] ?? '<p>Please transfer the amount to the following bank detail.</p><p>Bank Name: XYZ</p><p>Bank Acc.No: ##############</p>',
				'bank_transfer_field',
				array(
					'textarea_name' => 'bank_transfer_field',
					'textarea_rows' => 50,
				)
			);
			?>
		</div>
	</div>
	<?php
}

/**
 * render_stripe_settings
 *
 * @return void
 */
function render_stripe_settings( $membership_details ) {
	$stripe_settings  = \WPEverest\URMembership\Admin\Services\Stripe\StripeService::get_stripe_settings();
	$setup_incomplete = empty( $stripe_settings['publishable_key'] ) || empty( $stripe_settings['secret_key'] );
	$stripe_details   = $membership_details['payment_gateways']['stripe'] ?? array();

	?>
	<div id="stripe-section"
		class="ur-p-2 "
		style="background: #f8f8fa; border-radius:5px">
		<div
			id="ur-membership-stripe-toggle-container"
			class="ur-d-flex ur-justify-content-between ur-payment-option-header">
			<h2><?php echo __( 'Stripe', 'user-registration' ); ?></h2>
			<div class="user-registration-switch">
				<input
					data-key-name="Payment Gateway"
					id="ur-membership-pg-stripe" type="checkbox"
					class="user-registration-switch__control hide-show-check ur-payment-option-header enabled pg-switch"
					<?php echo isset( $stripe_details['status'] ) && $stripe_details['status'] == 'on' ? 'checked' : ''; ?>
					name="ur_membership_pg_bank_status"
				>
				<svg class="ur-pg-arrow
																<?php echo isset( $stripe_details['status'] ) && $stripe_details['status'] == 'on' ? 'expand' : ''; ?>
																" xmlns="http://www.w3.org/2000/svg" fill="none"
					viewBox="0 0 24 24">
					<path stroke="#383838" stroke-linecap="round"
							stroke-linejoin="round" stroke-width="2"
							d="m9 18 6-6-6-6"></path>
				</svg>
			</div>
		</div>


		<div class="payment-option-body"
			data-target-id="ur-membership-stripe-toggle-container"
			style="<?php echo isset( $stripe_details['status'] ) && $stripe_details['status'] == 'on' ? '' : 'display:none'; ?>">

			<!--													stripe mode-->

			<div
				class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
				style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label
						for="ur-input-type-cancel-url"><?php esc_html_e( 'Mode', 'user-registration' ); ?>
					</label>
				</div>
				<div
					class="ur-admin-template"
					style="width: 100%">
					<div class="ur-field"
						data-field-key="stripe_mode">
						<input type="url"
								data-key-name="<?php esc_html_e( 'Stripe Mode', 'user-registration' ); ?>"
								id="ur-input-type-stripe-mode"
								name="ur_membership_stripe_mode"
								style="width: 100%"
								value="<?php echo esc_html__( ucfirst( $stripe_settings['mode'] ) ); ?>"
								readonly
						>
					</div>
				</div>

			</div>
			<!--stripe publishable key-->
			<div
				class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
				style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label
						for="ur-input-type-cancel-url"><?php esc_html_e( 'Publishable Key', 'user-registration' ); ?>
					</label>
				</div>
				<div
					class="ur-admin-template"
					style="width: 100%">
					<div class="ur-field"
						data-field-key="publishable_key">
						<input type="url"
								data-key-name="<?php esc_html_e( 'Publishable Key', 'user-registration' ); ?>"
								id="ur-input-type-publishable-key"
								name="ur_membership_publishable_key"
								style="width: 100%"
								value="<?php echo esc_html__( $stripe_settings['publishable_key'] ); ?>"
								readonly
						>
					</div>
				</div>

			</div>
			<!--	stripe secret key-->
			<div
				class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
				style="gap:20px;">
				<div class="ur-label" style="width: 30%">
					<label
						for="ur-input-type-cancel-url"><?php esc_html_e( 'Secret Key', 'user-registration' ); ?>
					</label>
				</div>
				<div
					class="ur-admin-template"
					style="width: 100%">
					<div class="ur-field"
						data-field-key="secret_key">
						<input type="url"
								data-key-name="<?php esc_html_e( 'Secret Key', 'user-registration' ); ?>"
								id="ur-input-type-secret-key"
								name="ur_membership_secret_key"
								style="width: 100%"
								value="<?php echo esc_html__( $stripe_settings['secret_key'] ); ?>"
								readonly
						>
					</div>
				</div>

			</div>
			<div class="stripe-settings">
				<?php
				$message      = esc_html__( 'Change your stripe settings from here.' );
				$settings_url = get_admin_url() . 'admin.php?page=user-registration-settings&tab=payment';

				if ( $setup_incomplete ) :
					$message = esc_html__( 'Your Stripe Setup is incomplete. Please complete your Setup to continue.' );
				endif;
				?>
				<p><?php echo "$message"; ?></p>
				<a href="<?php echo esc_url( $settings_url ); ?>"
					target="_blank">
					<?php echo esc_html__( 'Settings', 'user-registration' ); ?>
					>
					<?php echo esc_html__( 'Payments', 'user-registration' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
}

?>
