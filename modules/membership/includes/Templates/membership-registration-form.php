<div class="notice-container">
	<div class="notice_red">
		<span class="notice_message"></span>
		<span class="close_notice">&times;</span>
	</div>
</div>
<!--user registration section-->
<div id="ur-membership-registration" class="ur_membership_registration_container ur-form-container">
	<?php
	if ( false ):
		?>
		<h3 class="ur_membership_title"><?php echo esc_html__( 'Sign Up', 'user-registration' ); ?></h3>
		<hr class="ur_membership_divider">
		<!--	firstname-->
		<div class="ur_membership_frontend_input_container">
			<label class="ur_membership_input_label ur-label" for="ur-membership-first-name">
				<?php echo esc_html__( 'First Name', 'user-registration' ); ?>
			</label>
			<input class="ur_membership_input_class"
				   data-key-name="<?php echo esc_html__( 'First Name', 'user-registration' ); ?>"
				   id="ur-membership-first-name"
				   type="text"
				   placeholder="<?php echo esc_html__( 'First Name', 'user-registration' ); ?>"
				   name="urm_firstname"
				   te
			>
			<span class="notice_red"></span>

		</div>
		<!--	lastname-->
		<div class="ur_membership_frontend_input_container">
			<label class="ur_membership_input_label ur-label"
				   for="ur-membership-last-name"><?php echo esc_html__( 'Last Name', 'user-registration' ); ?></label>
			<input class="ur_membership_input_class"
				   id="ur-membership-last-name"
				   data-key-name="<?php echo esc_html__( 'Last Name', 'user-registration' ); ?>"
				   type="text"
				   placeholder="<?php echo esc_html__( 'Last Name', 'user-registration' ); ?>"
				   name="urm_lastname"

			>
			<span class="notice_red"></span>

		</div>
		<!--	username-->
		<div class="ur_membership_frontend_input_container">
			<label class="ur_membership_input_label ur-label required"
				   for="ur-membership-username"><?php echo esc_html__( 'Username', 'user-registration' ); ?></label>
			<input class="ur_membership_input_class"
				   id="ur-membership-username"
				   type="text"
				   data-key-name="<?php echo esc_html__( 'Username', 'user-registration' ); ?>"
				   placeholder="<?php echo esc_html__( 'abc123', 'user-registration' ); ?>"
				   name="urm_username"
				   required
			>
			<span class="notice_red"></span>

		</div>
		<!--	email-->
		<div class="ur_membership_frontend_input_container">
			<label class="ur_membership_input_label ur-label required"
				   for="ur-membership-email"><?php echo esc_html__( 'Email', 'user-registration' ); ?></label>
			<input class="ur_membership_input_class"
				   id="ur-membership-email"
				   data-key-name="<?php echo esc_html__( 'Email', 'user-registration' ); ?>"
				   type="email"
				   placeholder="<?php echo esc_html__( 'example@email.com', 'user-registration' ); ?>"
				   name="urm_email"
				   required
			>
			<span class="notice_red"></span>

		</div>
		<!--	password-->
		<div class="ur_membership_frontend_input_container">
			<label class="ur_membership_input_label ur-label required"
				   for="ur-membership-password"><?php echo esc_html__( 'Password', 'user-registration' ); ?></label>
			<input class="ur_membership_input_class"
				   id="ur-membership-password"
				   data-key-name="<?php echo esc_html__( 'Password', 'user-registration' ); ?>"
				   type="password"
				   name="urm_password"
				   required
			>
			<span id="password-notice" class="notice_red"></span>
		</div>

		<!--	confirm password-->
		<div class="ur_membership_frontend_input_container">
			<label class="ur_membership_input_label ur-label required"
				   for="ur-membership-confirm-password"><?php echo esc_html__( 'Confirm Password', 'user-registration' ); ?></label>
			<input class="ur_membership_input_class"
				   data-key-name="<?php echo esc_html__( 'Confirm Password', 'user-registration' ); ?>"
				   id="ur-membership-confirm-password"
				   type="password"
				   name="urm_confirm_password"
				   required
			>
			<span id="confirm-password-notice" class="notice_red"></span>
		</div>

	<?php
	endif;
	?>

	<!--	membership-->
	<div id="urm-membership-list" class="ur_membership_frontend_input_container radio">

		<label
			class="ur-label ur_membership_input_label required"><?php echo esc_html__( esc_html( $attributes['label'] ), 'user-registration' ); ?>
			<abbr class="required" title="required">*</abbr>
		</label>
		<?php
		if ( ! empty( $memberships ) ) :
			foreach ( $memberships as $m => $membership ) :
				?>
				<label class="ur_membership_input_label ur-label"
					   for="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>">
					<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field"
						   data-key-name="ur-membership-id"
						   id="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>"
						   type="radio"
						   name="urm_membership"
						   data-name=<?php echo esc_html__( esc_html( $attributes['field_name'] ), 'user-registration' ); ?>
						   data-label=<?php echo esc_html__( esc_html( $attributes['type'] ), 'user-registration' ); ?>
						   required="required"
						   value="<?php echo esc_attr( $membership['ID'] ); ?>"
						   data-urm-pg='<?php echo esc_attr( ( $membership['active_payment_gateways'] ?? '' ) ); ?>'
						   data-urm-pg-type="<?php echo esc_attr( $membership['type'] ); ?>"
						   data-urm-pg-calculated-amount="<?php echo esc_attr( $membership['calculated_amount'] ); ?>"
						<?php echo isset( $_GET['membership_id'] ) && ! empty( $_GET['membership_id'] ) && $_GET['membership_id'] == $membership['ID'] ? 'checked' : ''; ?>
					>
					<span
						class="ur-membership-duration"><?php echo esc_html__( $membership['title'], 'user-registration' ); ?></span>
					<span
						class="ur-membership-duration"> - <?php echo esc_html__( $membership['period'], 'user-registration' ); ?></span>
				</label>
			<?php endforeach;
		else:
			$message = wp_kses_post( apply_filters( 'user_registration_membership_no_membership_message', __( 'No membership\'s group selected.', 'user-registration' ) ) );
			echo '<label data-form-id="' . absint( $form_id ) . '"  class="user-registration-error no-membership">' . $message . '</label>';
		endif;
		?>
		<span id="membership-input-notice">
		</span>
	</div>
	<!--	coupon container-->
	<?php
	$is_coupon_addon_activated = ur_check_module_activation('coupon');

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
		<label class="ur_membership_input_label ur-label"
			   for="ur-membership-total"><?php echo esc_html__( 'Total', 'user-registration' ); ?></label>
		<input class="ur_membership_input_class"
			   id="ur-membership-total"
			   data-key-name="<?php echo esc_html__( 'Total', 'user-registration' ); ?>"
			   name="urm_total"
			   value="<?php echo ceil( 0 ); ?>"
			   disabled
		>
		<span id="total-input-notice">
		</span>
	</div>

	<!--	payment gateway container-->
	<div
		class="ur_membership_frontend_input_container urm_hidden_payment_container ur_payment_gateway_container urm-d-none">
		<hr class="ur_membership_divider">
		<span
			class="ur_membership_input_label ur-label required"><?php echo esc_html__( 'Select Payment Gateway', 'user-registration' ); ?>
		</span>
		<div id="payment-gateway-body" class="ur_membership_frontend_input_container">
			<?php foreach ( get_option( 'ur_membership_payment_gateways' ) as $g => $gateway ) : ?>
				<label class="ur_membership_input_label ur-label"
					   for="ur-membership-<?php echo esc_attr( strtolower( $gateway ) ); ?>">
					<input class="ur_membership_input_class pg-list"
						   data-key-name="ur-payment-method"
						   id="ur-membership-<?php echo esc_attr( strtolower( $gateway ) ); ?>"
						   type="radio"
						   name="urm_payment_method"
						   required
						   value="<?php echo esc_attr( strtolower( $gateway ) ); ?>"
						<?php echo 0 === $g ? 'checked' : ''; ?>
					>
					<span class="ur-membership-duration"><?php echo esc_html__( $gateway ); ?></span>
				</label>
			<?php endforeach; ?>
			<span id="payment-gateway-notice" class="notice_red"></span>
		</div>
	</div>
	<div class="ur_membership_frontend_input_container">
		<div class="stripe-container urm-d-none">
			<button type="button" class="stripe-card-indicator ur-stripe-element-selected"
					id="credit_card"><?php echo esc_html__( "Credit Card", "user-registration" ) ?></button>
			<div class="stripe-input-container">
				<div id="card-element">
				</div>
			</div>
		</div>
	</div>

	<span class="description">
		<?php
		echo esc_html( $attributes['description'] );
		?>
	</span>

</div>
<!--user order successful section-->


