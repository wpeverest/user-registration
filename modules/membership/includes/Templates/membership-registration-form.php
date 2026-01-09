<div class="notice-container">
	<div class="notice_red">
		<span class="notice_message"></span>
		<span class="close_notice">&times;</span>
	</div>
</div>
<!--user registration section-->
<div id="ur-membership-registration" class="ur_membership_registration_container ur-form-container">
	<?php

	$is_coupon_addon_activated        = ur_check_module_activation( 'coupon' );
	$membership_ids_link_with_coupons = array();
	if ( $is_coupon_addon_activated && function_exists( 'ur_get_membership_ids_link_with_coupons' ) ) :
		$membership_ids_link_with_coupons = ur_get_membership_ids_link_with_coupons();
	endif;
	?>

	<!--	membership-->
	<div id="urm-membership-list" class="ur_membership_frontend_input_container radio">

		<label
			class="ur-label ur_membership_input_label required"><?php echo esc_html($attributes['label']); ?>
			<abbr class="required" title="required">*</abbr>
			<?php

			if (!empty($attributes['tooltip']) && ur_string_to_bool($attributes['tooltip']) !== false) :

				$tooltip = esc_html($attributes['tooltip_message']);
				?>
				<span class="ur-portal-tooltip tooltipstered" data-tip="<?=$tooltip?>"></span>
			<?php endif ?>
		</label>
		<span class="description">
			<?php

			echo esc_html( $attributes['description'] );
			?>
		</span>
		<?php
		if ( ! empty( $memberships ) ) :
			if ( is_user_logged_in() ) {
				$membership_service = new WPEverest\URMembership\Admin\Services\MembershipService();
				$fetched_data       = $membership_service->fetch_membership_details_from_intended_actions( $_GET );
				if ( isset( $fetched_data['status'] ) && $fetched_data['status'] ) {
					$memberships = $fetched_data['memberships'] ?? array();
				}
			} else {
				// Checkout page for user registering into the site.
				$memberships = apply_filters( 'user_registration_membership_lists', $memberships );
			}

			foreach ( $memberships as $m => $membership ) :
				$urm_default_pg = apply_filters( 'user_registration_membership_default_payment_gateway', '' );
				?>
				<label class="ur_membership_input_label ur-label"
					   for="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>">
					<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field"
						   data-key-name="ur-membership-id"
						   id="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>"
						   type="radio"
						   name="urm_membership"
						   data-name=<?php echo esc_attr( $attributes['field_name'] ); ?>
						   data-label=<?php echo esc_attr( $attributes['type'] ); ?>
						   required="required"
						   value="<?php echo esc_attr( $membership['ID'] ); ?>"
						   data-urm-pg='<?php echo esc_attr( ( $membership['active_payment_gateways'] ?? '' ) ); ?>'
						   data-urm-pg-type="<?php echo esc_attr( $membership['type'] ); ?>"
						   data-urm-pg-calculated-amount="<?php echo esc_attr( $membership['amount'] ); ?>"
						<?php
						echo isset( $_GET['action'] ) && 'upgrade' === $_GET['action'] && $membership['amount'] < $membership['calculated_amount'] ? 'data-urm-upgrade-type="' . esc_attr__( 'Prorated', 'user-registration' ) . '"' : '';
						?>
						   data-urm-default-pg="<?php echo esc_attr( $urm_default_pg ); ?>"
						   data-has-coupon-link="<?php echo esc_attr( in_array( $membership['ID'], $membership_ids_link_with_coupons ) ? 'yes' : 'no' ); ?>"
						<?php echo isset( $_GET['membership_id'] ) && ! empty( $_GET['membership_id'] ) && $_GET['membership_id'] == $membership['ID'] ? 'checked' : ''; ?>
					>
					<span
						class="ur-membership-duration"><?php echo esc_html__( $membership['title'], 'user-registration' ); ?></span>
					<span
						class="ur-membership-duration"><?php echo esc_html__( $membership['period'], 'user-registration' ); ?></span>
				</label>
			<?php
			endforeach;
		else :
			$message = wp_kses_post( apply_filters( 'user_registration_membership_no_membership_message', __( 'No membership\'s group selected.', 'user-registration' ) ) );
			echo '<label data-form-id="' . absint( $form_id ) . '"  class="user-registration-error no-membership">' . $message . '</label>';
		endif;
		?>
		<span id="membership-input-notice">
		</span>
	</div>
	<!--	coupon container-->
	<?php
	$is_coupon_addon_activated = ur_check_module_activation( 'coupon' );

	if ( $is_coupon_addon_activated ) :
		?>
		<div class="ur_membership_frontend_input_container urm_hidden_payment_container urm-d-none"
			 id="ur_coupon_container">

			<label class="ur_membership_input_label ur-label" for="ur-membership-coupon">
				<?php echo esc_html__( 'Coupon', 'user-registration' ); ?>
			</label>
			<div class="coupon-input-area">
				<div class="input_with_clear_btn">
					<input class="ur_membership_input_class"
						   data-key-name="<?php echo esc_html__( 'coupon', 'user-registration' ); ?>"
						   id="ur-membership-coupon"
						   type="text"
						   placeholder="<?php echo esc_html__( 'Coupon', 'user-registration' ); ?>"
						   name="urm_coupon"
					>
					<span class="ur_clear_coupon">x</span>
				</div>

				<button type="button"
						class="urm_apply_coupon membership-primary-btn"><?php echo esc_html__( 'Apply Coupon', 'user-registration' ); ?></button>
			</div>
			<span id="coupon-validation-error" class="notice_red"></span>
		</div>
	<?php
	endif;
	?>

	<!--	total container-->
	<div id="urm-total_container"
		 class="ur_membership_frontend_input_container urm-d-none urm_hidden_payment_container">
		<div class="urm-membership-total-value">
			<label class="ur_membership_input_label ur-label"
				   for="ur-membership-total"><?php echo esc_html__( 'Total', 'user-registration' ); ?></label>
			<span class="ur_membership_input_class"
				  id="ur-membership-total"
				  data-key-name="<?php echo esc_html__( 'Total', 'user-registration' ); ?>"
				  disabled
			>
				<?php echo ceil( 0 ); ?>
			</span>
		</div>
		<span id="total-input-notice">
		</span>
	</div>

	<!--	payment gateway container -->
	<div
		class="ur_membership_frontend_input_container urm_hidden_payment_container ur_payment_gateway_container urm-d-none">
		<hr class="ur_membership_divider">
		<span
			class="ur_membership_input_label ur-label required"><?php echo apply_filters( 'user_registration_membership_subscription_payment_gateway_title', esc_html__( 'Select Payment Gateway', 'user-registration' ) ); ?>
		</span>
		<div id="payment-gateway-body" class="ur_membership_frontend_input_container">
			<div class="ur-membership-payment-gateway-lists">
				<?php
				$width_map = array(
					'paypal' => '70px',
					'stripe' => '50px',
					'bank'   => '40px',
				);
				foreach ( get_option( 'ur_membership_payment_gateways' ) as $g => $gateway ) :
					?>
					<label class="ur_membership_input_label ur-label"
						   for="ur-membership-<?php echo esc_attr( strtolower( $g ) ); ?>">
						<input class="ur_membership_input_class pg-list"
							   data-key-name="ur-payment-method"
							   id="ur-membership-<?php echo esc_attr( strtolower( $g ) ); ?>"
							   type="radio"
							   name="urm_payment_method"
							   required
							   value="<?php echo esc_attr( strtolower( $g ) ); ?>"
							<?php echo 0 === $g ? 'checked' : ''; ?>
						>
						<span class="ur-membership-duration">
						<img
							src="<?php echo esc_url( plugins_url( 'assets/images/settings-icons/membership-field/' . strtolower( $g ) . '-logo.png', UR_PLUGIN_FILE ) ); ?>"
							alt="<?php echo esc_attr( $gateway ); ?>"
							class="ur-membership-payment-gateway-logo"
							width="<?php echo isset( $width_map[ strtolower( $g ) ] ) ? $width_map[ strtolower( $g ) ] : '60px'; ?>"
						/>
					</span>
					</label>
				<?php endforeach; ?>
			</div>
			<span id="payment-gateway-notice" class="notice_red"></span>
		</div>
	</div>
	<div class="ur_membership_frontend_input_container">
		<div class="stripe-container urm-d-none">
			<button type="button" class="stripe-card-indicator ur-stripe-element-selected"
					id="credit_card"><?php echo esc_html__( 'Credit Card', 'user-registration' ); ?></button>
			<div class="stripe-input-container">
				<div id="card-element">
				</div>
			</div>
		</div>
		<?php
		/**
		 * Fires when payment fields is rendered on membership registration form.
		 *
		 *  This action allows developers to output payment gateway fields
		 *  within the registration form.
		 */
		do_action( 'user_registration_membership_render_payment_field', $form_id );
		?>
	</div>

	<?php

	if ( ! empty( $fetched_data['current_subscription_id'] ) && ! empty( $fetched_data['current_membership_id'] ) ) {
		?>
		<input type="hidden" class="urm_membership_upgrade_data"
			   data-current-subscription-id="<?php echo esc_attr( $fetched_data['current_subscription_id'] ); ?>"
			   data-current-membership-id="<?php echo esc_attr( $fetched_data['current_membership_id'] ); ?>"/>
		<?php
	}
	?>

</div>
<!--user order successful section-->
