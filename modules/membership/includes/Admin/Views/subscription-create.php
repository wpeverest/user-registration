<?php
/**
 *
 */

defined( 'ABSPATH' ) || exit;

$return_url   = admin_url( 'admin.php?page=user-registration-subscriptions' );
$all_gateways = get_option( 'ur_membership_payment_gateways', array() );
?>
<script>
var ur_membership_plans = <?php echo wp_json_encode( $membership_plans ); ?>;
</script>
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
					<p><?php esc_html_e( 'Create Subscription', 'user-registration' ); ?></p>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<button type="submit" form="ur-membership-subscription-create-form"
				class="button-primary ur-subscription-create-btn">
				<?php esc_html_e( 'Create', 'user-registration' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="ur-membership">
	<div class="ur-membership-tab-contents-wrapper ur-subscription">
		<form id="ur-membership-subscription-create-form" method="post"
			class="ur-membership-subscription-create-form ur-subscription__form">
			<?php do_action( 'ur_membership_subscription_create_form_start' ); ?>
			<div class="user-registration-card">
				<div class="user_registration-card__body">
					<div class="ur-membership-main-fields">
						<?php do_action( 'ur_membership_subscription_create_form_before_fields' ); ?>
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label">
								<label for="ur-subscription-member">
									<?php esc_html_e( 'User', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-member-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<select name="member" id="ur-subscription-member"
										class="user-membership-enhanced-select2" style="width: 100%" required>
										<option value="" disabled selected>
											<?php esc_html_e( 'Search by username or email...', 'user-registration' ); ?>
										</option>
										<?php if ( ! empty( $users ) ) : ?>
											<?php foreach ( $users as $user ) : ?>
										<option value="<?php echo esc_attr( $user->ID ); ?>">
												<?php printf( '%s (%s)', esc_html( $user->data->user_login ), esc_html( $user->data->user_email ) ); ?>
										</option>
										<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
							</div>
						</div>

						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label">
								<label for="ur-subscription-plan">
									<?php esc_html_e( 'Membership Plan', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<select name="plan" id="ur-subscription-plan"
										class="user-membership-enhanced-select2" style="width: 100%" required>
										<option value="" disabled selected>
											<?php esc_html_e( 'Select a membership plan', 'user-registration' ); ?>
										</option>
										<?php if ( ! empty( $membership_plans ) ) : ?>
											<?php foreach ( $membership_plans as $plan ) : ?>
										<option value="<?php echo esc_attr( $plan['ID'] ); ?>">
												<?php echo esc_html( $plan['post_title'] ?? '' ); ?>
										</option>
										<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
							</div>
						</div>

						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label">
								<label for="ur-subscription-billing-amount">
									<?php esc_html_e( 'Billing Amount', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<input type="number" name="billing_amount" id="ur-subscription-billing-amount"
										class="urmg-input" step="0.01" min="0"
										style="width: 100%; padding: 8px; border: 1px solid #e1e1e1; border-radius: 4px; height: 38px;"
										required>
								</div>
							</div>
						</div>

						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px; display: none;"
							id="ur-payment-gateway-container">
							<div class="ur-label">
								<label for="ur-subscription-payment-gateway">
									<?php esc_html_e( 'Payment Gateway', 'user-registration' ); ?>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<select name="payment_gateway" id="ur-subscription-payment-gateway"
										style="width: 100%" class="user-membership-enhanced-select2">
										<option value="">
											<?php esc_html_e( 'Select Payment Gateway', 'user-registration' ); ?>
										</option>
										<?php if ( ! empty( $all_gateways ) ) : ?>
											<?php foreach ( $all_gateways as $gateway_key => $gateway_label ) : ?>
										<option value="<?php echo esc_attr( $gateway_key ); ?>"
											data-gateway-key="<?php echo esc_attr( $gateway_key ); ?>">
												<?php echo esc_html( $gateway_label ); ?>
										</option>
										<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
							</div>
						</div>

						<?php do_action( 'ur_membership_subscription_create_form_after_fields' ); ?>
					</div>
				</div>
				<?php wp_nonce_field( 'ur_membership_subscription', 'security' ); ?>
			</div>
			<?php do_action( 'ur_membership_subscription_create_form_end' ); ?>
		</form>
	</div>
</div>
