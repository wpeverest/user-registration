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
	 class="ur-payment-gateway-wrapper <?php echo isset( $membership_details['type'] ) && $membership_details['type'] !== 'free' ? '' : 'ur-d-none'; ?>">
	<div class="user-registration-card">
		<div class="user-registration-card__header">
			<h3><?php echo __( "Payment Gateways", "user-registration" ) ?></h3>
		</div>
		<div class="user-registration-card__body ur-d-flex ur-flex-column"
			 style="gap: 20px">

			<?php render_payment_gateways( $membership_details ); ?>
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
	//render paypal settings
	render_paypal_settings( $membership_details );

	// render bank settings
	render_bank_settings( $membership_details );

	//render stripe settings
	render_stripe_settings( $membership_details );
	/**
	 * Fires when payment gateway settings is rendered on the form settings.
	 *
	 * @param array $membership_details
	 */
	do_action( 'user_registration_membership_render_payment_gateway', $membership_details );
}

/**
 * render_paypal_settings
 *
 * @param $membership_details
 *
 * @return void
 */
function render_paypal_settings( $membership_details ) {
	$global_paypal_settings = array(
		'paypal_mode'   => get_option( 'user_registration_global_paypal_mode', 'test' ),
		'paypal_email'  => get_option( 'user_registration_global_paypal_email_address', '' ),
		'cancel_url'    => get_option( 'user_registration_global_paypal_cancel_url', home_url() ),
		'return_url'    => get_option( 'user_registration_global_paypal_return_url', wp_login_url() ),
		'client_id'     => get_option( 'user_registration_global_paypal_client_id', '' ),
		'client_secret' => get_option( 'user_registration_global_paypal_client_secret', '' ),
	);
	$is_incomplete          = empty( $global_paypal_settings['paypal_email'] );
	?>
	<div id="paypal-section" class="user-registration-payment__items">
		<div
			id="ur-membership-paypal-toggle-container"
			class="ur-d-flex ur-justify-content-between ur-payment-option-header"
		>
			<h2><?php echo __( 'Paypal', 'user-registration' ); ?></h2>
			<div class="ur-toggle-section m1-auto">
				<span class="user-registration-toggle-form">
					<input
						data-key-name="Payment Gateway"
						id="ur-membership-pg-paypal" type="checkbox"
						class="user-registration-switch__control hide-show-check enabled pg-switch"
						<?php echo isset( $membership_details['payment_gateways']['paypal'] ) && $membership_details['payment_gateways']['paypal']['status'] == 'on' && !$is_incomplete ? 'checked' : ''; ?>
						name="ur_membership_pg_paypal_status"
					>
				<span class="slider round"></span>
				</span>
			</div>
		</div>
		<div class="payment-option-body"
			 data-target-id="ur-membership-paypal-toggle-container"
			 style="<?php echo $is_incomplete ? '' : 'display:none'; ?>">
			<?php
			$message      = esc_html__( 'Your Paypal settings is incomplete, please complete your setup from the link below to continue (No need to refresh this page)' );
			$settings_url = get_admin_url() . 'admin.php?page=user-registration-settings&tab=payment&method=paypal';
			?>
			<div id="settings-section" class="user-registration-payment__settings">
				<p><?php echo "$message"; ?></p>
				<a href="<?php echo esc_url( $settings_url ); ?>"
				   target="_blank">
					<?php echo esc_html__( 'Settings', 'user-registration' ); ?>
					>
					<?php echo esc_html__( 'Paypal Settings', 'user-registration' ); ?>
				</a>
			</div>
			<?php
			if ( false ):
				?>
				<!--														paypal mode-->

				<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
					 style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label
							for="ur-input-type-paypal-mode"><?php esc_html_e( 'Mode', 'user-registration' ); ?>
							<span style="color:red">*</span>
						</label>
					</div>
					<div
						class="ur-input-type-membership-email ur-admin-template"
						style="width: 100%">
						<div class="ur-field" data-field-key="paypal_email">
							<input type="email"
								   data-key-name="<?php esc_html_e( 'Paypal Email', 'user-registration' ); ?>"
								   id="ur-input-type-paypal-mode"
								   name="ur_membership_paypal_mode"
								   style="width: 100%"
								   value="<?php echo $global_paypal_settings['paypal_mode']; ?>"
								   required
								   readonly
							>
						</div>
					</div>

				</div>
				<!--					paypal email-->
				<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
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
								   value="<?php echo $global_paypal_settings['paypal_email']; ?>"
								   required
								   readonly
							>
						</div>
					</div>

				</div>

				<!--														paypal cancel url-->
				<div
					class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
					style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label
							for="ur-input-type-cancel-url"><?php esc_html_e( 'Cancel Url', 'user-registration' ); ?>
							<span class="user-registration-help-tip tooltipstered"
								  data-tip="<?php echo __( "Endpoint set for handling paypal cancel api." ); ?>"></span>
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
								   value="<?php echo $global_paypal_settings['cancel_url']; ?>"
								   required
								   readonly
							>
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
							<span class="user-registration-help-tip tooltipstered"
								  data-tip="<?php echo __( "Redirect url after the payment process, also used as notify_url for Paypal IPN." ); ?>"></span>
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
								   value="<?php echo $global_paypal_settings['return_url']; ?>"
								   required
								   readonly>
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
									   value="<?php echo $global_paypal_settings['client_id']; ?>"
								>
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
									   value="<?php echo $global_paypal_settings['client_secret']; ?>"
									   readonly
								>
							</div>
						</div>

					</div>
				</div>
			<?php
			endif;
			?>
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
	$global_bank_details = get_option( 'user_registration_global_bank_details', '' );

	?>
	<div id="bank-section" class="user-registration-payment__items">
		<?php
		$bank_details = $membership_details['payment_gateways']['bank'] ?? '';
		?>
		<div
			id="ur-membership-bank-toggle-container"
			class="ur-d-flex ur-justify-content-between ur-payment-option-header">
			<h2><?php echo __( 'Bank Transfer', 'user-registration' ); ?></h2>
			<div class="ur-toggle-section m1-auto">
				<span class="user-registration-toggle-form">
					<input
						data-key-name="Payment Gateway"
						id="ur-membership-pg-bank" type="checkbox"
						class="user-registration-switch__control hide-show-check ur-payment-option-header enabled pg-switch"
						<?php echo isset( $bank_details['status'] ) && $bank_details['status'] == 'on' && !empty( $global_bank_details ) ? 'checked' : ''; ?>
						name="ur_membership_pg_bank_status"
					>
				<span class="slider round"></span>
				</span>
			</div>
		</div>
		<div class="payment-option-body"
			 data-target-id="ur-membership-bank-toggle-container"
			 style="<?php echo empty( $global_bank_details ) ? '' : 'display:none'; ?>"
		>

			<div class="bank-settings">
				<?php
				$settings_url = get_admin_url() . 'admin.php?page=user-registration-settings&tab=payment&method=bank';
				$message      = esc_html__( 'Your Bank Setup is incomplete, please complete your setup from the link below to continue (No need to refresh this page)' );

				?>
				<p><?php echo "$message"; ?></p>
				<a href="<?php echo esc_url( $settings_url ); ?>"
				   target="_blank">
					<?php echo esc_html__( 'Settings', 'user-registration' ); ?>
					>
					<?php echo esc_html__( 'Bank Transfer Settings', 'user-registration' ); ?>
				</a>
			</div>
			<?php
			if ( false ):
				?>
				<?php
				wp_editor(
					$bank_details['content'] ?? '<p>Please transfer the amount to the following bank detail.</p><p>Bank Name: XYZ</p><p>Bank Acc.No: ##############</p>',
					'bank_transfer_field',
					array(
						'textarea_name' => 'bank_transfer_field',
						'textarea_rows' => 50,
					)
				);
			endif;
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
	<div id="stripe-section" class="user-registration-payment__items">
		<div
			id="ur-membership-stripe-toggle-container"
			class="ur-d-flex ur-justify-content-between ur-payment-option-header">
			<h2><?php echo __( 'Stripe', 'user-registration' ); ?></h2>
			<div class="ur-toggle-section m1-auto">
				<span class="user-registration-toggle-form">
					<input
						data-key-name="Payment Gateway"
						id="ur-membership-pg-stripe" type="checkbox"
						class="user-registration-switch__control hide-show-check ur-payment-option-header enabled pg-switch"
						<?php echo isset( $stripe_details['status'] ) && $stripe_details['status'] == 'on' && !$setup_incomplete ? 'checked' : ''; ?>
						name="ur_membership_pg_bank_status"
					>
				<span class="slider round"></span>
				</span>
			</div>
		</div>
		<div class="payment-option-body"
			 data-target-id="ur-membership-stripe-toggle-container"
			 style="<?php echo $setup_incomplete ? '' : 'display:none'; ?>">

			<div class="stripe-settings">
				<?php
				$settings_url = get_admin_url() . 'admin.php?page=user-registration-settings&tab=payment&method=stripe';
				$message      = esc_html__( 'Your Stripe Setup is incomplete, please complete your setup from the link below to continue (No need to refresh this page)' );

				?>
				<p><?php echo "$message"; ?></p>
				<a href="<?php echo esc_url( $settings_url ); ?>"
				   target="_blank">
					<?php echo esc_html__( 'Settings', 'user-registration' ); ?>
					>
					<?php echo esc_html__( 'Stripe Settings', 'user-registration' ); ?>
				</a>
			</div>
			<?php
			if ( false ):
				?>
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
			<?php
			endif;
			?>


		</div>
	</div>
	<?php
}

?>
