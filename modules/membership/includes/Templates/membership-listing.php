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

				$membership_group_repository = new WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository();
				$membership_group_service    = new WPEverest\URMembership\Admin\Services\MembershipGroupService();
				$current_membership_group    = $membership_group_repository->get_membership_group_by_membership_id( $membership['ID'] );
				$user_membership_group_ids   = array();

				foreach ( $user_membership_ids as $user_membership_id ) {
					$user_membership_group_id    = $membership_group_repository->get_membership_group_by_membership_id( $user_membership_id );
					$user_membership_group_ids[] = $user_membership_group_id['ID'];
				}

				$user_membership_group_ids = array_values( array_unique( $user_membership_group_ids ) );
				$intended_action           = $action_to_take;

				if ( is_user_logged_in() ) {

					if ( ! empty( $current_membership_group ) ) {

						if ( in_array( $current_membership_group['ID'], $user_membership_group_ids ) ) {
							foreach ( $user_membership_group_ids as $group_id ) {
								if ( $current_membership_group['ID'] === $group_id ) {
									$multiple_memberships_allowed = $membership_group_service->check_if_multiple_memberships_allowed( $current_membership_group['ID'] );
									$upgrade_allowed              = $membership_group_service->check_if_upgrade_allowed( $current_membership_group['ID'] );

									if ( $multiple_memberships_allowed ) {
										$intended_action = 'multiple';
									} elseif ( $upgrade_allowed ) {
										$intended_action = 'upgrade';
									}
								}
							}
						} elseif ( UR_PRO_ACTIVE && ur_check_module_activation( 'multi-membership' ) ) {
								$intended_action = 'multiple';
						}
					} else {
						$intended_action = 'upgrade';
					}
				} else {
					$intended_action = 'register';
				}

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
		<form id="membership-old-selection-form" class="ur-membership-container layout-row"
				method="GET" data-layout="row">
			<?php
			foreach ( $memberships as $k => $membership ) :
				$time = '';

				if ( 'paid' === $membership['type'] ) {
					$time = esc_html__( 'lifetime', 'user-registration' );
				}

				?>
				<div class="membership-block">
					<div class="left-container">

					<div class="membership-title">
						<span><?php echo esc_html( $membership['title'] ); ?></span>
					</div>
					<div class="membership-body">
						<div class="membership-description">
							<?php echo $membership['description']; ?>
						</div>
					</div>
					</div>
					<div class="membership-footer right-container">
						<input type="hidden" name="membership_id" value="<?php echo esc_html( $membership['ID'] ); ?>">
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
								class="membership-signup-button <?php echo esc_attr( $button_class ); ?>" <?php echo( empty( $registration_page_id ) || $is_editor ? 'disabled' : '' ); ?> style="<?php echo esc_attr( $button_style ); ?>" <?php echo $open_in_new_tab ? "target = '_blank'" : ''; ?> ><?php echo esc_html( $sign_up_text ); ?></button>
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
			if ( ! empty( $memberships ) ) :
				foreach ( $memberships as $m => $membership ) :
					$current_plan = false;

					if ( in_array( $membership['ID'], $user_membership_ids ) ) {
						unset( $memberships[ $m ] );
						continue;
					}

					$membership_group_repository = new WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository();
					$membership_group_service    = new WPEverest\URMembership\Admin\Services\MembershipGroupService();
					$current_membership_group    = $membership_group_repository->get_membership_group_by_membership_id( $membership['ID'] );
					$user_membership_group_ids   = array();

					foreach ( $user_membership_ids as $user_membership_id ) {
						$user_membership_group_id    = $membership_group_repository->get_membership_group_by_membership_id( $user_membership_id );
						$user_membership_group_ids[] = $user_membership_group_id['ID'];
					}

					$user_membership_group_ids = array_values( array_unique( $user_membership_group_ids ) );
					$intended_action           = $action_to_take;

					if ( is_user_logged_in() ) {

						if ( ! empty( $current_membership_group ) ) {

							if ( in_array( $current_membership_group['ID'], $user_membership_group_ids ) ) {
								foreach ( $user_membership_group_ids as $group_id ) {
									if ( $current_membership_group['ID'] === $group_id ) {
										$multiple_memberships_allowed = $membership_group_service->check_if_multiple_memberships_allowed( $current_membership_group['ID'] );
										$upgrade_allowed              = $membership_group_service->check_if_upgrade_allowed( $current_membership_group['ID'] );

										if ( $multiple_memberships_allowed ) {
											$intended_action = 'multiple';
										} elseif ( $upgrade_allowed ) {
											$intended_action = 'upgrade';
										}
									}
								}
							} else {
								$intended_action = 'multiple';
							}
						} else {
							$intended_action = 'upgrade';
						}
					} else {
						$intended_action = 'register';
					}

					$time = '';

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
			?>
			<div class="membership-footer">
				<button type="button"
						class="membership-signup-button <?php echo esc_attr( $button_class ); ?>" <?php echo( empty( $registration_page_id ) ? 'disabled' : '' ); ?> style="<?php echo esc_attr( $button_style ); ?>" <?php echo $open_in_new_tab ? "target = '_blank'" : ''; ?> ><?php echo esc_html( $sign_up_text ); ?></button>
			</div>
		</div>
	</form>
	<?php
endif;
?>
