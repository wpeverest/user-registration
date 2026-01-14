<?php
if ( ! empty( $button_class ) && ! empty( $button_hover_style ) ) :
	?>
<style>
.<?php echo esc_attr( $button_class ); ?>:hover {
	<?php echo esc_html( $button_hover_style ); ?>
}


	<?php if ( ! empty( $radio_color ) ) : ?>

.<?php echo esc_attr( $radio_class ); ?> {
	appearance: none;
	-webkit-appearance: none;
	width: 16px !important;
	height: 16px !important;
	border: 2px solid
		<?php
		echo esc_attr( $radio_color . ' !important;' );
		?>
	;
	border-radius: 50%;
	cursor: pointer;
	position: relative;
}
.<?php echo esc_attr( $radio_class ); ?>:checked::before {
	content: "";
	width: 10px;
	height: 10px;
	background:
		<?php
		echo esc_attr( $radio_color ) . ' !important;';
		?>
	;
	border-radius: 50%;
	position: absolute !important;
	top: 50% !important;
	left: 50% !important;
	transform: translate(-50%, -50%) !important;
	margin: 0px !important;
}

		<?php endif ?>
</style>
	<?php
endif;
if ( 'block' === $type ) :
	?>
	<div class="ur-membership-list-container">
		<form id="membership-old-selection-form" class="membership-selection-form ur-membership-container layout-block column-<?php echo esc_attr( $column_number ); ?>"
				method="GET" data-layout="block">
			<?php
			foreach ( $memberships as $k => $membership ) :
				$current_plan = false;
				$button_text  = $sign_up_text;

				$time = '';
				if ( 'paid' === $membership['type'] ) {
					$time = esc_html__( 'lifetime', 'user-registration' );
				}

				if ( in_array( $membership['ID'], $user_membership_ids ) ) {
					$current_plan = true;
					$button_text  = esc_html__( 'Current Plan', 'user-registration' );
				}

				$intended_action = $membership_service->fetch_intended_action( $action_to_take, $membership, $user_membership_ids );

				?>
				<div class="membership-block">
					<div class="membership-title">
						<span><?php echo esc_html( $membership['title'] ); ?></span>
					</div>
					<div class="membership-body">
						<input type="hidden" name="membership_id" value="<?php echo esc_html( $membership['ID'] ); ?>">
						<input type="hidden" name="action" value="<?php echo esc_html( $intended_action ); ?>">
						<input type="hidden" name="redirection_url"
								value="<?php echo esc_url( $redirect_page_url ); ?>">
						<input type="hidden" name="urm_uuid" value="<?php echo esc_attr( $uuid ); ?>">
						<input type="hidden" name="thank_you_page_id" value="<?php echo $thank_you_page_id; ?>">
						<div class="ur-membership-amount-wrapper">
						<?php if ( 'free' !== $membership['type'] ) { ?>
							<div class="ur-membership-amount-wrapper">
									<span
										class="membership-amount">
										<?php echo esc_html( sprintf( '%s%.2f', $symbol, $membership['amount'] ) ); ?>
									</span>
									<span class="ur-membership-duration">
										<?php
										if ( $time || isset( $membership['period'] ) ) {
											echo ' / ' . ( 'subscription' === $membership['type'] ? esc_html( trim( strtolower( explode( '/', $membership['period'] )[1] ) ) ) : esc_html( $time ) ); }
										?>
									</span>
								</div>
						<?php } else { ?>
							<span
						class="membership-amount"><?php echo esc_html__( 'Free', 'user-registration' ); ?></span>
							<?php } ?>
							</div>
						<button type="button"
								class="membership-signup-button <?php echo esc_attr( $button_class ); ?>" <?php echo( empty( $registration_page_id ) || $current_plan ? 'disabled' : '' ); ?> style="<?php echo esc_attr( $button_style ); ?>" <?php echo $open_in_new_tab ? "target = '_blank'" : ''; ?>	><?php echo esc_html( $button_text ); ?>
						</button>
					</div>
					<?php if ( $show_description ) { ?>
					<div class="membership-footer">
						<div class="membership-description">
							<?php echo $membership['description']; ?>
						</div>
					</div>
					<?php } ?>

				</div>
				<?php
			endforeach;
			?>
		</form>

	</div>
	<?php
	elseif ( 'row' === $type ) :
		?>
	<div class="ur-membership-list-container">
		<form id="membership-old-selection-form" class="membership-selection-form ur-membership-container layout-row"
				method="GET" data-layout="row">
			<?php
			foreach ( $memberships as $k => $membership ) :
				$current_plan = false;
				$button_text  = $sign_up_text;

				$time = '';
				if ( 'paid' === $membership['type'] ) {
					$time = esc_html__( 'lifetime', 'user-registration' );
				}

				if ( in_array( $membership['ID'], $user_membership_ids ) ) {
					$current_plan = true;
					$button_text  = esc_html__( 'Current Plan', 'user-registration' );
				}

				$intended_action = $membership_service->fetch_intended_action( $action_to_take, $membership, $user_membership_ids );

				?>
				<div class="membership-block">
					<div class="left-container">

					<div class="membership-title">
						<span><?php echo esc_html( $membership['title'] ); ?></span>
					</div>
					<?php if ( $show_description ) { ?>
					<div class="membership-body">
						<div class="membership-description">
							<?php echo $membership['description']; ?>
						</div>
					</div>
						<?php } ?>
					</div>
					<div class="membership-footer right-container">
						<input type="hidden" name="membership_id" value="<?php echo esc_html( $membership['ID'] ); ?>">
						<input type="hidden" name="action" value="<?php echo esc_html( $intended_action ); ?>">
						<input type="hidden" name="redirection_url"
								value="<?php echo esc_url( $redirect_page_url ); ?>">
						<input type="hidden" name="urm_uuid" value="<?php echo esc_html( $uuid ); ?>">
						<input type="hidden" name="thank_you_page_id" value="<?php echo absint( $thank_you_page_id ); ?>">
						<div class="ur-membership-amount-wrapper">
						<?php if ( 'free' !== $membership['type'] ) { ?>
							<div class="ur-membership-amount-wrapper">
									<span
										class="membership-amount">
										<?php echo esc_html( sprintf( '%s%.2f', $symbol, $membership['amount'] ) ); ?>
									</span>
									<span class="ur-membership-duration">
										<?php
										if ( $time || isset( $membership['period'] ) ) {
											echo ' / ' . ( 'subscription' === $membership['type'] ? esc_html( trim( strtolower( explode( '/', $membership['period'] )[1] ) ) ) : esc_html( $time ) ); }
										?>
									</span>
							</div>
							<?php } else { ?>
								<span
							class="membership-amount"><?php echo esc_html__( 'Free', 'user-registration' ); ?></span>
								<?php } ?>
						</div>
						<button type="button"
								class="membership-signup-button <?php echo esc_attr( $button_class ); ?>" <?php echo( empty( $registration_page_id ) || $is_editor || $current_plan ? 'disabled' : '' ); ?> style="<?php echo esc_attr( $button_style ); ?>" <?php echo $open_in_new_tab ? "target = '_blank'" : ''; ?> ><?php echo esc_html( $button_text ); ?></button>
					</div>
				</div>
				<?php
				endforeach;
			?>
		</form>

	</div>
		<?php
elseif ( 'list' === $type ) :

	?>
	<form id="membership-old-selection-form-<?php echo esc_attr( $uuid ); ?>" class="membership-selection-form layout-list ur-membership-container" method="GET" >
		<input type="hidden" name="urm_uuid" value="<?php echo esc_html( $uuid ); ?>">
		<input type="hidden" name="thank_you_page_id" value="<?php echo absint( $thank_you_page_id ); ?>">
		<input type="hidden" name="redirection_url"
								value="<?php echo esc_url( $redirect_page_url ); ?>">
		<div class="ur_membership_frontend_input_container radio">
			<?php
			$membership_listing_div = '';
			ob_start();
			if ( ! empty( $memberships ) ) :
				foreach ( $memberships as $m => $membership ) :
					$current_plan = false;

					if ( in_array( $membership['ID'], $user_membership_ids ) ) {
						unset( $memberships[ $m ] );
						continue;
					}
					$intended_action = $membership_service->fetch_intended_action( $action_to_take, $membership, $user_membership_ids );
					$time            = '';

					if ( 'paid' === $membership['type'] ) {
						$time = esc_html__( 'lifetime', 'user-registration' );
					}
					?>
					<div class="membership-block">
						<label class="ur_membership_input_label ur-label"
								for="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>">
							<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field <?php echo esc_html( $radio_class ); ?>"
									id="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>"
									type="radio"
									name="membership_id"
									value="<?php echo esc_attr( $membership['ID'] ); ?>"
									data-urm-uuid="<?php echo esc_html( $uuid ); ?>"
									data-action="<?php echo esc_attr( $intended_action ); ?>"
									data-redirect="<?php echo esc_url( $redirect_page_url ); ?>"
									data-thankyou="<?php echo esc_attr( $thank_you_page_id ); ?>"
							>
							<div class="ur-membership-title-wrapper">
							<span
							class="ur-membership-title"><?php echo esc_html__( $membership['title'], 'user-registration' ); ?></span>
							<?php if ( 'free' !== $membership['type'] ) { ?>
								<div class="ur-membership-amount-wrapper">
									<span
										class="membership-amount">
										<?php echo esc_html( sprintf( '%s%.2f', $symbol, $membership['amount'] ) ); ?>
									</span>
									<span class="ur-membership-duration">
										<?php
										if ( $time || isset( $membership['period'] ) ) {
											echo ' / ' . ( 'subscription' === $membership['type'] ? esc_html( trim( strtolower( explode( '/', $membership['period'] )[1] ) ) ) : esc_html( $time ) ); }
										?>
									</span>
								</div>
							<?php } else { ?>
								<div class="ur-membership-amount-wrapper">

									<span
									class="membership-amount">
										<?php echo esc_html__( 'Free', 'user-registration' ); ?>
									</span>
								</div>
								<?php } ?>
							</div>
						</label>
					</div>

					<?php
				endforeach;
			endif;
			$membership_listing_div = ob_get_clean();

			if ( $membership_listing_div ) {
				echo $membership_listing_div;
				?>
				<div class="notice-container">
					<div id="urm-listing-error" class="notice_red">
						<span class="notice_message"></span>
						<!-- <span class="close_notice">&times;</span> -->
					</div>
				</div>
				<div class="membership-footer">
					<button type="button"
							class="membership-signup-button <?php echo esc_attr( $button_class ); ?>" <?php echo( empty( $registration_page_id ) ? 'disabled' : '' ); ?> style="<?php echo esc_attr( $button_style ); ?>" <?php echo $open_in_new_tab ? "target = '_blank'" : ''; ?> ><?php echo esc_html( $sign_up_text ); ?></button>
				</div>
				<?php
			} else {
				?>
				<div id="user-registration" class="user-registration"><?php esc_html_e( 'You have purchased all available membership plans.', 'user-registration' ); ?></div>
				<?php
			}
			?>
		</div>
	</form>
	<?php
endif;
?>
