<?php
/**
 * Membership Create - Advanced Tab Content
 *
 * @var array  $roles Available roles
 * @var array  $memberships Available memberships
 * @var array  $membership_details Membership details data
 * @var bool   $is_editing Whether editing existing membership
 */
?>
	<div class="user-registration-card__body">
		<div id="ur-membership-plan-and-price-section">
			<!-- Membership Role -->
			<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
				<div class="ur-label">
					<label for="ur-input-type-membership-role">
						<?php esc_html_e( 'Membership Role :', 'user-registration' ); ?>
					</label>
				</div>
				<div class="ur-input-type-membership-name ur-admin-template">
					<div class="ur-field">
						<select data-key-name="<?php echo esc_html__( 'Role', 'user-registration' ); ?>"
								id="ur-input-type-membership-role" class="user-membership-enhanced-select2">
							<?php
							foreach ( $roles as $k => $role ) :
								$selected = ( isset( $membership_details['role'] ) && $k === $membership_details['role'] )
									? 'selected="selected"'
									: ( ( 'subscriber' === $k && ! isset( $membership_details['role'] ) ) ? 'selected="selected"' : '' );
								?>
								<option <?php echo $selected; ?> value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $role ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<?php if ( UR_PRO_ACTIVE ) : ?>
				<!-- Subscription Fields Container -->
				<div class="ur-membership-subscription-field-container <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? '' : 'ur-d-none'; ?>">
					<!-- Trial Section -->
					<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3" style="gap:20px">
						<div class="ur-label">
							<label class="ur-membership-trial-status" for="ur-membership-trial-status"><?php esc_html_e( 'Trial Period :', 'user-registration' ); ?></label>
						</div>
						<div class="ur-toggle-section m1-auto">
							<span class="user-registration-toggle-form">
								<input data-key-name="Trial Period" id="ur-membership-trial-status"
										type="checkbox" class="user-registration-switch__control hide-show-check enabled"
										name="ur_membership[trial]_status" style="width: 100%;"
										value="<?php echo isset( $membership_details['trial_status'] ) && 'on' === $membership_details['trial_status'] ? 'on' : 'off'; ?>"
										<?php echo isset( $membership_details['trial_status'] ) && 'on' === $membership_details['trial_status'] ? 'checked' : ''; ?>>
								<span class="slider round"></span>
							</span>
						</div>
					</div>
					<div class="trial-container <?php echo isset( $membership_details['trial_status'] ) && 'on' === $membership_details['trial_status'] ? '' : 'ur-d-none'; ?>">
						<div class="trial-container--wrapper ur-d-flex ur-mt-6 ur-align-items-center" style="gap:20px;">
							<div class="ur-label" style="width: 23%">
								<label class="ur-membership-trial-status" for="ur-membership-trial-duration">
									<?php esc_html_e( 'Trial Period Duration', 'user - registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-field ur-d-flex ur-align-items-center" style="width: 100%;">
								<input data-key-name="Trial Period Duration Value"
										value="<?php echo isset( $membership_details['trial_data'] ) ? esc_attr( $membership_details['trial_data']['value'] ) : 1; ?>"
										class="" type="number" name="ur_membership[trial]_value"
										autocomplete="off" id="ur-membership-trial-duration-value" min="1" style="width: 80%">
								<select class="Trial Period Duration" id="ur-membership-trial-duration" name="ur_membership[trial]_duration">
									<option value="day" <?php echo isset( $membership_details['trial_data'] ) && 'day' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>>Day(s)</option>
									<option value="week" <?php echo isset( $membership_details['trial_data'] ) && 'week' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>>Week(s)</option>
									<option value="month" <?php echo isset( $membership_details['trial_data'] ) && 'month' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>>Month(s)</option>
									<option value="year" <?php echo isset( $membership_details['trial_data'] ) && 'year' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>>Year(s)</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Membership Upgrade Action Toggle -->
			<?php
			$is_upgrade_enabled = isset( $membership_details['upgrade_settings']['upgrade_action'] ) && true == $membership_details['upgrade_settings']['upgrade_action'];
			$is_upgrade_allowed = true;

			if ( isset( $_GET['post_id'] ) ) {
				$membership_group_repository = new WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository();
				$membership_group_service    = new WPEverest\URMembership\Admin\Services\MembershipGroupService();
				$membership_group_id         = $membership_group_repository->get_membership_group_by_membership_id( absint( $_GET['post_id'] ?? 0 ) );

				if ( isset( $membership_group_id['ID'] ) ) {
					$multiple_memberships_allowed = $membership_group_service->check_if_multiple_memberships_allowed( $membership_group_id['ID'] );

					if ( $multiple_memberships_allowed ) {
						$is_upgrade_allowed = false;
					}
				}
			}
			if ( $is_upgrade_allowed ) {
				?>
				<div class="ur-membership-selection-container ur-d-flex ur-mt-2 ur-align-items-center" style="gap:20px;">
					<div class="ur-label">
						<label class="ur-membership-enable-upgrade-action" for="ur-membership-upgrade-action">
							<?php esc_html_e( 'Upgrade Action :', 'user-registration' ); ?>
						</label>
					</div>
					<div class="ur-toggle-section m1-auto">
						<span class="user-registration-toggle-form">
							<input data-key-name="Upgrade Action" id="ur-membership-upgrade-action" type="checkbox"
									class="user-registration-switch__control hide-show-check enabled"
									<?php echo $is_upgrade_enabled ? 'checked' : ''; ?>
									name="ur_membership_upgrade_action" style="width: 100%; text-align: left">
							<span class="slider round"></span>
						</span>
					</div>
				</div>

				<!-- Upgrade Settings Container -->
				<div id="upgrade-settings-container" class="ur-membership-selection-container" style="<?php echo true === $is_upgrade_enabled ? '' : 'display: none'; ?>">
					<!-- Membership Upgrade Path Field -->
					<div class="ur-membership-input-container ur-d-flex ur-align-items-center" style="gap:20px;">
						<div class="ur-label" style="width: 30%; margin-bottom: 0;">
							<label for="ur-input-type-membership-upgrade-path">
								<?php esc_html_e( 'Upgrade Path', 'user-registration' ); ?>
								<span style="color:red">*</span> :
							</label>
						</div>
						<div class="ur-input-type-membership-upgrade-path ur-admin-template" style="width: 100%">
							<div class="ur-field" data-field-key="membership_upgrade_path" style="width: 100%">
								<select multiple data-key-name="<?php echo esc_html__( 'Upgrade Path', 'user-registration' ); ?>"
										id="ur-input-type-membership-upgrade-path" class="user-membership-enhanced-select2">
									<?php
									$upgrade_path_raw = $membership_details['upgrade_settings']['upgrade_path'] ?? '';

									$upgrade_path = is_array( $upgrade_path_raw )
									? $upgrade_path_raw
									: ( ! empty( $upgrade_path_raw ) ? explode( ',', $upgrade_path_raw ) : array() );

									foreach ( $memberships as $k => $m ) :
										if ( isset( $_GET['post_id'] ) && $_GET['post_id'] == $m['ID'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
											continue;
										}
										$selected = ( $upgrade_path ) && in_array( $m['ID'], $upgrade_path, true ) ? 'selected="selected"' : '';
										?>
										<option <?php echo $selected; ?> value="<?php echo esc_attr( $m['ID'] ); ?>"><?php echo esc_html( $m['title'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>

					<!-- Membership Upgrade Path Type -->
					<div class="urm-upgrade-path-type-container ur-d-flex ur-mt-6 ur-align-items-center"
						data-key-name="<?php echo __( 'Upgrade Type', 'user-registration' ); ?>" style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label for="ur-membership-upgrade-type-full">
								<?php echo __( 'Upgrade Type', 'user-registration' ); ?>
								<span style="color:red">*</span> :
							</label>
						</div>
						<div class="ur-input-type-select ur-admin-template" style="width: 100%">
							<div class="ur-field ur-d-flex" data-field-key="radio">
								<!-- Full Amount Upgrade -->
								<label class="ur-membership-upgrade-types" for="ur-membership-upgrade-type-full">
									<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
										<input data-key-name="<?php echo __( 'Upgrade Type', 'user-registration' ); ?>"
												id="ur-membership-upgrade-type-full" type="radio" value="full"
												name="ur_membership_upgrade_type" style="margin: 0"
												<?php echo ( ( isset( $membership_details['upgrade_settings']['upgrade_type'] ) && $membership_details['upgrade_settings']['upgrade_type'] == 'full' ) ) ? 'checked' : ( ! $is_editing ? 'checked' : '' ); ?>
												required>
										<label class="ur-membership-upgrade-type-full--label" for="ur-membership-upgrade-type-full">
											<b class="user-registration-image-label"><?php esc_html_e( 'Full Amount Upgrade', 'user-registration' ); ?></b>
										</label>
									</div>
								</label>
								<!-- Pro Rata Type -->
								<label class="ur-membership-upgrade-types <?php echo ! UR_PRO_ACTIVE ? 'upgradable-type' : ''; ?> <?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'free' ? 'ur-d-none' : ''; ?>" for="ur-membership-upgrade-type-pro-rata">
									<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
										<input data-key-name="Upgrade Type" id="ur-membership-upgrade-type-pro-rata"
												type="radio" value="pro-rata" name="ur_membership_upgrade_type" style="margin: 0"
												<?php echo ( ( isset( $membership_details['upgrade_settings']['upgrade_type'] ) && $membership_details['upgrade_settings']['upgrade_type'] == 'pro-rata' ) ) ? 'checked' : ''; ?>
												<?php echo ! UR_PRO_ACTIVE ? 'disabled' : ''; ?> required>
										<label class="ur-membership-upgrade-type-full--label" for="ur-membership-upgrade-type-pro-rata">
											<b class="user-registration-image-label"><?php esc_html_e( 'Proration Upgrade', 'user-registration' ); ?></b>
										</label>
									</div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			if ( UR_PRO_ACTIVE && function_exists( 'ur_render_email_marketing_sync_settings' ) ) :
				?>
				<!-- Sync Membership to email marketing addons. -->
				<div class="ur-membership-sync-to-email-marketing-addons">
					<div class="ur-membership-selection-container ur-d-flex ur-mt-2 ur-align-items-center"
						style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label class="ur-membership-enable-email-marketing-sync-action"
									for="ur-membership-email-marketing-sync-action"><?php esc_html_e( 'Override Email Marketing Setting :', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-toggle-section m1-auto" style="width: 100%">
							<span class="user-registration-toggle-form">

						<?php
							$email_marketing_sync_details = isset( $membership_details['email_marketing_sync'] ) ? $membership_details['email_marketing_sync'] : array();
							$is_email_marketing_sync      = ur_string_to_bool( isset( $email_marketing_sync_details['is_enable'] ) ? $email_marketing_sync_details['is_enable'] : '0' );
						?>
								<input
									data-key-name="Sync Email Marketing Action"
									id="ur-membership-email-marketing-sync-action" type="checkbox"
									class="user-registration-switch__control hide-show-check enabled"

									name="ur_membership_email_marketing_sync_action"
									style="width: 100%; text-align: left"
								<?php echo $is_email_marketing_sync ? esc_attr( 'checked' ) : ''; ?>
									>
								<span class="slider round"></span>
							</span>
						</div>
					</div>
				</div>

				<?php
				ur_render_email_marketing_sync_settings( $membership_details );
					endif;

					/**
					 * Local Currency Settings Render.
					 *
					 * @since 6.1.0
					 */
					if ( UR_PRO_ACTIVE && ur_check_module_activation( 'local-currency' ) && class_exists('WPEverest\URMembership\Local_Currency\Admin\CoreFunctions')):
						WPEverest\URMembership\Local_Currency\Admin\CoreFunctions::ur_render_local_currency_settings( $membership_details );
					endif;
			?>
		</div>
	</div>
