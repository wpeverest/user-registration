<?php
/**
 * Template for Membership Checkout Form.
 */
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Services\MembershipService;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$member_id                     = get_current_user_id();
$actionable_membership_details = array();

$members_repository = new MembersRepository();
$user_memberships   = $members_repository->get_member_membership_by_id( $member_id );

if ( empty( $user_memberships ) ) {
	// TODO - Multiple Membership ( May need to allow users to buy membership if they donot have any );
	return esc_html_e( 'You currently do not have memberships assigned to you.', 'user-registration' );
}

$membership_repository = new MembershipRepository();
$membership_service    = new MembershipService();

if ( 'multiple' === $_GET['action'] ) {
	$membership_id                 = absint( $_GET['membership_id'] );
	$actionable_membership_details = $membership_repository->get_single_membership_by_ID( $membership_id );
	$actionable_membership_details = $membership_service->prepare_single_membership_data( $actionable_membership_details );
	$actionable_membership_details = apply_filters( 'build_membership_list_frontend', array( (array) $actionable_membership_details ) )[0];
	$actionable_membership_details = array( $actionable_membership_details );

	if ( empty( $actionable_membership_details ) ) {
		return esc_html_e( 'Selected membership details not found.', 'user-registration' );
	}
} elseif ( 'upgrade' === $_GET['action'] ) {

	if ( isset( $_GET['current'] ) && '' !== $_GET['current'] ) {
		$current_membership_id = absint( $_GET['current'] );

		$members_order_repository = new MembersOrderRepository();
		$orders_repository        = new OrdersRepository();
		$last_order               = $members_order_repository->get_member_orders( $member_id );

		if ( ! empty( $last_order ) ) {
			$order_meta = $orders_repository->get_order_metas( $last_order['ID'] );
			if ( ! empty( $order_meta ) ) {
				$upcoming_subscription = json_decode( get_user_meta( $member_id, 'urm_next_subscription_data', true ), true );
				$membership            = get_post( $upcoming_subscription['membership'] );
				return apply_filters( 'urm_delayed_plan_exist_notice', __( sprintf( 'You already have a scheduled upgrade to the <b>%s</b> plan at the end of your current subscription cycle (<i><b>%s</b></i>) <br> If you\'d like to cancel this upcoming change, click the <b>Cancel Membership</b> button to proceed.', $membership->post_title, date( 'M d, Y', strtotime( $order_meta['meta_value'] ) ) ), 'user-registration' ), $membership->post_title, $order_meta['meta_value'] );
			}
		}
		$membership_service            = new MembershipService();
		$actionable_membership_details = $membership_service->get_upgradable_membership( $current_membership_id );

		if ( empty( $actionable_membership_details ) ) {
			return esc_html_e( 'No upgradable Memberships.', 'user-registration' );
		}
	} else {
		return esc_html_e( 'You donot have permission to purchase the selected membership. Please go through upgrade process from my account.', 'user-registration' );
	}
}

$active_memberships_titles = array_filter(
	array_map(
		function ( $user_memberships ) {
			if ( ! empty( $user_memberships['status'] ) && ! in_array( $user_memberships['status'], array( 'pending', 'inactive' ) ) ) {
				return $user_memberships['post_title'];
			}
		},
		$user_memberships
	)
);

$payment_gateways = get_option( 'ur_membership_payment_gateways', array() );
?>
<div class="membership-upgrade-container">
	<span>
		<?php
		echo wp_kses_post( sprintf( __( 'Your currently active plans are: <b>%s</b>', 'user-registration' ), implode( ', ', $active_memberships_titles ) ) );
		?>
	</span>
	<div class="upgrade-plan-container">
		<span class="ur-upgrade-label"><?php esc_html_e( 'Select Plan', 'user-registration' ); ?></span>
		<div id="upgradable-plans">
			<?php
			foreach ( $actionable_membership_details as $membership_details ) {
				?>
				<label class="upgrade-membership-label" for="ur-membership-select-membership-23">
					<input
						class="ur_membership_input_class ur_membership_radio_input ur-frontend-field"
						id="ur-membership-select-membership-<?php echo esc_attr( $membership_details['ID'] ); ?>"
						type="radio"
						name="urm_membership"
						data-label="<?php echo esc_attr( $membership_details['title'] ); ?>"
						required="required"
						value="<?php echo esc_attr( $membership_details['ID'] ); ?>"
						data-urm-pg='<?php echo esc_attr( $membership_details['active_payment_gateways'] ); ?>'
						data-urm-pg-type="<?php echo esc_attr( $membership_details['type'] ); ?>"
						data-urm-pg-calculated-amount="<?php echo isset( $membership_details['calculated_amount'] ) ? esc_attr( $membership_details['calculated_amount'] ) : esc_attr( $membership_details['amount'] ); ?>"
					>
					<span class="ur-membership-duration"><?php echo esc_html( $membership_details['title'] ); ?></span>
					<span class="ur-membership-duration"> - <?php echo esc_html( $membership_details['period'] ); ?></span>
				</label>
				<?php
			}
			?>

		</div>
		<div class="ur_membership_registration_container urm-d-none">
			<div class="ur_membership_frontend_input_container urm_hidden_payment_container ur_payment_gateway_container urm-d-none">
				<span class="ur-upgrade-label ur-label required"><?php esc_html_e( 'Select Payment Gateway', 'user-registration' ); ?></span>

				<div id="payment-gateway-body" class="ur_membership_frontend_input_container">
					<?php
					foreach ( $payment_gateways as $index => $gateway ) {
						$gateway_value = strtolower( $gateway );
						$gateway_label = ucfirst( $gateway_value );
						$checked       = ( $index === 0 && count( $payment_gateways ) === 1 ) ? 'checked' : '';
						?>
						<label class="ur_membership_input_label ur-label" for="ur-membership-<?php echo esc_attr( $gateway_value ); ?>">
							<input
								class="ur_membership_input_class pg-list"
								data-key-name="ur-payment-method"
								id="ur-membership-<?php echo esc_attr( $gateway_value ); ?>"
								type="radio"
								name="urm_payment_method"
								value="<?php echo esc_attr( $gateway_value ); ?>"
								required
								<?php echo $checked; ?>
							>
							<span class="ur-membership-duration"><?php echo esc_html( $gateway_label ); ?></span>
						</label>
						<?php
					}
					?>
					<span id="payment-gateway-notice" class="notice_red"></span>
				</div>
			</div>
			<div class="ur_membership_frontend_input_container">
				<div class="stripe-container urm-d-none">
					<button type="button" class="stripe-card-indicator ur-stripe-element-selected" id="credit_card">
						<?php esc_html_e( 'Credit Card', 'user-registration' ); ?>
					</button>
					<div class="stripe-input-container">
						<div id="card-element"></div>
					</div>
				</div>
			</div>

			<div id="authorize-net-container" class="urm-d-none membership-only authorize-net-container">
				<div
					data-field-id="authorizenet_gateway"
					class="ur-field-item field-authorize_net_gateway"
					data-ref-id="authorizenet_gateway"
				>
					<div class="form-row" id="authorizenet_gateway_field">
						<label class="ur-label" for="Authorize.net">
							<?php esc_html_e( 'Authorize.net', 'user-registration' ); ?> <abbr class="required" title="required">*</abbr>
						</label>

						<div
							id="user_registration_authorize_net_gateway"
							data-gateway="authorize_net"
							class="input-text"
						>
							<div class="ur-field-row">
								<div class="user-registration-authorize-net-card-number">
									<input
										type="text"
										id="user_registration_authorize_net_card_number"
										name="user_registration_authorize_net_card_number"
										maxlength="16"
										placeholder="411111111111111"
										class="widefat ur-anet-sub-field user_registration_authorize_net_card_number"
									>
									<br>
									<label class="user-registration-sub-label"><?php esc_html_e( 'Card Number', 'user-registration' ); ?></label>
								</div>
							</div>

							<div class="ur-field-row clearfix">
								<div class="user-registration-authorize-net-expiration user-registration-one-half">
									<div class="user-registration-authorize-net-expiration-month user-registration-one-half">
										<select
											class="widefat ur-anet-sub-field user_registration_authorize_net_expiration_month"
											id="user_registration_authorize_net_expiration_month"
											name="user_registration_authorize_net_expiration_month"
										>
										<option><?php esc_html_e( 'MM', 'user-registration' ); ?>  </option>
										<?php
										$months = range( 01, 12 );
										foreach ( $months as $month ) {
											$value = sprintf( '%02d', $month ); // format month with leading zero.
											?>
											<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></option>
											<?php
										}
										?>
										</select>
										<label class="user-registration-sub-label"><?php esc_html_e( 'Expiration', 'user-registration' ); ?></label>
									</div>

									<div class="user-registration-authorize-net-expiration-year user-registration-one-half last">
										<select
											class="widefat ur-anet-sub-field user_registration_authorize_net_expiration_year"
											id="user_registration_authorize_net_expiration_year"
											name="user_registration_authorize_net_expiration_year"
										>
										<option><?php esc_html_e( 'YY', 'user-registration' ); ?> </option>
										<?php
										$base = gmdate( 'y' );
										$end  = gmdate( 'y' ) + 10;
										for ( $i = $base; $i <= $end; $i++ ) {
											?>
											<option value="<?php echo absint( $i ); ?>"><?php echo absint( $i ); ?></option>
											<?php
										}
										?>
										</select>
									</div>
								</div>

								<div class="user-registration-authorize-net-cvc user-registration-one-half last">
									<input
										type="text"
										id="user_registration_authorize_net_card_code"
										name="user_registration_authorize_net_card_code"
										placeholder="900"
										maxlength="4"
										class="widefat ur-anet-sub-field user_registration_authorize_net_card_code"
									>
									<br>
									<label class="user-registration-sub-label"><?php esc_html_e( 'CVC', 'user-registration' ); ?> </label>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<span id="upgrade-membership-notice"></span>
	</div>
	<button type="submit" class="user-registration-Button button urm-update-membership-button">
		<?php esc_html_e( 'Submit', 'user-registration' ); ?>
	</button>
</div>
<div class="notice-container">
	<div class="notice_red">
		<span class="notice_message"></span>
		<span class="close_notice">&times;</span>
	</div>
</div>
