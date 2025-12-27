<?php
if ( 'block' === $type ) :
	?>
	<div class="ur-membership-list-container">

		<!--		<div class="membership-list-notice-div">-->
		<!--			<div class="membership-title">-->
		<!--				-->
		<?php
		// echo esc_html__( 'Available Memberships', 'user-registration' );
		?>
		<!--			</div>-->
		<!--			<div class="subscription-message">-->
		<!--				<p>-->
		<!--					-->
		<?php
		// echo esc_html__( 'We have the following subscriptions available for our site. Please select one to continue.' );
		?>
		<!--				</p>-->
		<!--			</div>-->
		<!--		</div>-->
		<form id="membership-old-selection-form" class="membership-selection-form ur-membership-container"
				method="GET">
			<?php
			foreach ( $memberships as $k => $membership ) :
				$current_plan = false;
				$button_text  = $sign_up_text;

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
						} else {
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
						<div class="membership-description">
							<?php echo( $membership['description'] ); ?>
						</div>
					</div>
					<div class="membership-footer">
						<input type="hidden" name="membership_id" value="<?php echo esc_html( $membership['ID'] ); ?>">
						<input type="hidden" name="action" value="<?php echo esc_html( $intended_action ); ?>">
						<input type="hidden" name="redirection_url"
								value="<?php echo esc_url( $redirect_page_url ); ?>">
						<input type="hidden" name="urm_uuid" value="<?php echo esc_attr( $uuid ); ?>">
						<input type="hidden" name="thank_you_page_id" value="<?php echo $thank_you_page_id; ?>">
						<span
							class="membership-amount"><?php echo $symbol; ?><?php echo esc_html( sprintf( '%.2f', $membership['amount'] ) ); ?></span>
						<button type="button"
								class="membership-signup-button" <?php echo( empty( $registration_page_id ) || $current_plan ? 'disabled' : '' ); ?> ><?php echo $button_text; ?></button>
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
	<form id="membership-selection-form-<?php echo esc_attr( $uuid ); ?>" class="membership-selection-form ur-membership-container" method="GET" >
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
					?>
					<div class="membership-block">
						<label class="ur_membership_input_label ur-label"
								for="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>">
							<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field"
									id="ur-membership-select-membership-<?php echo esc_attr( $membership['ID'] ); ?>"
									type="radio"
									name="membership_id"
									value="<?php echo esc_attr( $membership['ID'] ); ?>"
									data-action="<?php echo esc_attr( $intended_action ); ?>"
									data-redirect="<?php echo esc_url( $redirect_page_url ); ?>"
									data-thankyou="<?php echo esc_attr( $thank_you_page_id ); ?>"
							>
							<span
								class="ur-membership-duration"><?php echo esc_html__( $membership['title'], 'user-registration' ); ?></span>
							<span
								class="ur-membership-duration"> - <?php echo esc_html__( $membership['period'], 'user-registration' ); ?></span>
						</label>
					</div>

					<?php
				endforeach;
			endif;
			?>
			<div class="membership-footer">
				<button type="submit"
						class="membership-signup-button" <?php echo( empty( $registration_page_id ) ? 'disabled' : '' ); ?>><?php echo $sign_up_text; ?></button>
			</div>
		</div>
	</form>
	<?php
endif;
?>
