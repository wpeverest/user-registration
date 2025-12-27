<?php
$return_url = admin_url( 'admin.php?page=user-registration-membership&action=list_groups' );

$membership_group_service = new WPEverest\URMembership\Admin\Services\UpgradeMembershipService();
$upgrade_path             = array();
$upgrade_path_html        = '';

if ( isset( $membership_group['upgrade_path'] ) ) {
	$upgrade_path      = json_decode( $membership_group['upgrade_path'], true );
	$upgrade_path_html = $membership_group_service->build_upgrade_paths( $upgrade_path );
}

?>
<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
	<div class="ur-page-title__wrapper">
		<div class="ur-page-title__wrapper--left">
			<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2" href="<?php echo esc_attr( $return_url ); ?>">
				<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			</a>
			<div class="ur-page-title__wrapper--left-menu">
				<div class="ur-page-title__wrapper--left-menu__items">
					<p>
						<?php echo isset( $_GET['post_id'] ) ? esc_html_e( 'Edit Membership Group', 'user-registration' ) : esc_html_e( 'Create New Membership Group', 'user-registration' ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="ur-membership">
	<div
		class="ur-membership-tab-contents-wrapper ur-registered-from ur-align-items-center ur-justify-content-center">
		<form id="ur-membership-create-form" method="post">
			<div id="ur-membership-group-create-form" class="user-registration-card">
				<div class="user-registration-card__body">
					<div id="ur-membership-main-fields">
						<!--					membership group name-->
						<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-group-name"><?php esc_html_e( 'Group Name', 'user-registration' ); ?>
									<span style="color:red">*</span>
									<span class="user-registration-help-tip tooltipstered"
											data-tip="<?php echo esc_attr__( 'Title for the group.' ); ?>"></span>
								</label>
							</div>
							<div class="ur-input-type-membership-group-name ur-admin-template" style="width: 100%">
								<div class="ur-field" data-field-key="membership_group_name">
									<input type="text" data-key-name="Membership Group Name"
											id="ur-input-type-membership-group-name" name="ur_membership_group_name"
											style="width: 100%"
											autocomplete="off"
											value="<?php echo ! empty( $membership_group['post_title'] ) ? esc_attr( $membership_group['post_title'] ) : ''; ?>"
											class="urmg-input"
											required>
								</div>
							</div>

						</div>
						<!--					membership description-->
						<div class="ur-membership-input-container ur-input-type-textarea ur-d-flex ur-p-1 ur-mt-3"
							style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label for="ur-input-type-membership-group-description">
									Group Description
									<span class="user-registration-help-tip tooltipstered"
											data-tip="<?php echo esc_attr__( 'Describe the group.' ); ?>"></span>
								</label>
							</div>
							<div class="ur-field" data-field-key="textarea" style="width: 100%">
								<?php
								$membership_description = '';

								if ( ! empty( $membership_group['post_content'] ) ) {
									$membership_content     = json_decode( wp_unslash( $membership_group['post_content'] ), true );
									$membership_description = $membership_content['description'];
								}


								?>
								<textarea data-key-name="Membership Description"
											id="ur-input-type-membership-group-description"
											name="ur_membership_description"
											style="width: 100%" rows="5"
											class="urmg-input"
											value=""><?php echo $membership_description; ?></textarea>
							</div>
						</div>
						<!--					membership status-->
						<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3" style="gap:20px">
							<div class="ur-label" style="width: 30%">
								<label class="ur-membership-group-enable-status"
										for="ur-membership-group-status"><?php esc_html_e( 'Group Status', 'user-registration' ); ?>
									<span class="user-registration-help-tip tooltipstered"
											data-tip="<?php echo esc_attr__( 'Only active groups will be visible in the frontend.' ); ?>"></span>
								</label>
							</div>
							<div class="ur-toggle-section m1-auto" style="width:100%">
								<span class="user-registration-toggle-form">
									<input
										data-key-name="Membership Status"
										id="ur-membership-group-status" type="checkbox"
										class="user-registration-switch__control hide-show-check enabled urmg-input"
										<?php echo isset( $membership_content ) && $membership_content['status'] == 'true' ? 'checked' : ''; ?>
										name="ur_membership_status"
										style="width: 100%; text-align: left">
									<span class="slider round"></span>
								</span>
							</div>
						</div>
						<!--						role-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-group-role"><?php esc_html_e( 'Select Memberships', 'user-registration' ); ?>
									<span style="color:red">*</span>
									<span class="user-registration-help-tip tooltipstered"
											data-tip="Select which membership fall under this group."></span>
								</label>
							</div>
							<div class="ur-input-type-membership-group-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<select
										data-key-name="<?php echo esc_html__( 'Memberships', 'user-registration' ); ?>"
										id="ur-input-type-membership-group-memberships"
										class="user-membership-group-enhanced-select2 urmg-input"
										multiple="multiple"
										required>

										<?php
										$selected_memberships = array();
										if ( isset( $membership_group['memberships'] ) ) {
											$selected_memberships = json_decode( $membership_group['memberships'], true );
										}

										foreach ( $memberships as $membership ) :
											?>
											<option
												<?php echo isset( $membership_group['memberships'] ) && in_array( $membership['ID'], $selected_memberships ) ? 'selected="selected"' : ''; ?>
												value="<?php echo esc_attr( $membership['ID'] ); ?>"><?php echo esc_html( $membership['title'] ); ?></option>
											<?php
										endforeach;
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3" style="gap:20px">
							<div class="ur-label" style="width: 30%">
								<label class="ur-membership-group-enable-status"
										for="ur-membership-group-status"><?php esc_html_e( 'Multiple Membership Selections', 'user-registration' ); ?>
									<span class="user-registration-help-tip tooltipstered"
											data-tip="<?php echo esc_attr__( 'Users can buy more than one membership plan from this group', 'user-registration' ); ?>"></span>
								</label>
							</div>
							<div class="ur-toggle-section m1-auto" style="width:100%">
								<span class="user-registration-toggle-form">
									<input
										data-key-name="Allow Multiple Memberships Selections"
										id="ur-membership-group-multiple-membership" type="checkbox"
										class="user-registration-switch__control hide-show-check enabled urmg-input"
										<?php echo isset( $membership_group['multiple_memberships'] ) && $membership_group['multiple_memberships'] ? 'checked' : ''; ?>
										name="ur_membership_group_multiple_membership"
										style="width: 100%; text-align: left">
									<span class="slider round"></span>
								</span>
							</div>
						</div>
						<div class="ur-membership-selection-container ur-d-flex ur-p-1" style="gap:20px;" bis_skin_checked="1">
							<div class="ur-label" style="width: 30%" bis_skin_checked="1">
								<label for="ur-membership-management-mode"><?php esc_html_e( 'Membership Management Mode', 'user-registration' ); ?></label>
							</div>
							<div class="ur-input-type-select ur-admin-template" style="width: 100%" bis_skin_checked="1">
								<div class="ur-field ur-d-flex" data-field-key="radio" bis_skin_checked="1">
									<label class="ur-membership-types ur-membership-management-mode" for="ur-membership-upgrade-management-mode">
										<div class="ur-membership-management-mode-title ur-d-flex ur-align-items-center" bis_skin_checked="1">
											<input data-key-name="Management Mode" id="ur-membership-upgrade-management-mode"
													type="radio" value="upgrade"
													name="ur_membership_management_mode"
													style="margin: 0"
												<?php echo isset( $membership_group['mode'] ) && 'upgrade' === $membership_group['mode'] ? 'checked' : ''; ?>
													required>
											<label class="ur-p-2" for="ur-membership-upgrade-management-mode">
												<b class="user-registration-image-label "><?php esc_html_e( 'Upgrade', 'user-registration' ); ?></b>
											</label>
										</div>
									</label>
									<label class="ur-membership-types ur-membership-management-mode" for="ur-membership-multiple-management-mode">
										<div class="ur-membership-management-mode-title ur-d-flex ur-align-items-center" bis_skin_checked="1">
											<input data-key-name="Management Mode" id="ur-membership-multiple-management-mode"
													type="radio" value="multiple"
													name="ur_membership_management_mode"
													style="margin: 0"
												<?php echo isset( $membership_group['mode'] ) && 'multiple' === $membership_group['mode'] ? 'checked' : ''; ?>
													required>
											<label class="ur-p-2" for="ur-membership-multiple-management-mode">
												<b class="user-registration-image-label"><?php esc_html_e( 'Multiple Membership', 'user-registration' ); ?></b>
											</label>
										</div>
									</label>
								</div>
							</div>
						</div>
						<?php
						$upgrade_style = isset( $membership_group['mode'] ) && 'upgrade' === $membership_group['mode'] ? '' : 'display:none;';
						?>
						<!--Membership Upgrade Process-->
						<!-- <div
							class="urm-upgrade-process-container ur-d-flex ur-mt-6 ur-align-items-center"
							data-key-name="<?php echo __( 'Upgrade Process', 'user-registration' ); ?>"
							style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-membership-upgrade-process">
									<?php echo __( 'Upgrade Process', 'user-registration' ); ?>
									<span style="color:red">*</span> :
								</label>

							</div> -->
							<!-- <div class="ur-input-type-select ur-admin-template" style="width: 100%">
								<div class="ur-field ur-d-flex"
									data-field-key="radio">
									<label class="ur-membership-upgrade-processes"
											for="ur-membership-upgrade-process-automatic">
										<div
											class="ur-membership-type-title ur-d-flex ur-align-items-center ">
											<input
												data-key-name="<?php echo __( 'Upgrade Process', 'user-registration' ); ?>"
												id="ur-membership-upgrade-process-automatic"
												type="radio" value="automatic"
												name="ur_membership_upgrade_process"
												style="margin: 0"
												<?php echo ( ( isset( $membership_group['upgrade_settings']['upgrade_process'] ) && $membership_group['upgrade_settings']['upgrade_process'] == 'automatic' ) ) ? 'checked' : ''; ?>
												required>
											<label class="ur-membership-upgrade-process-automatic--label" for="ur-membership-upgrade-process-automatic">
												<b
													class="user-registration-image-label "><?php esc_html_e( 'Automatic', 'user-registration' ); ?>
												</b>
											</label>
										</div>
									</label>
									<label
										class="ur-membership-upgrade-processes"
										for="ur-membership-upgrade-process-manual">
										<div
											class="ur-membership-type-title ur-d-flex ur-align-items-center">
											<input
												data-key-name="<?php echo __( 'Upgrade Process', 'user-registration' ); ?>"
												id="ur-membership-upgrade-process-manual"
												type="radio"
												value="manual"
												name="ur_membership_upgrade_process"
												style="margin: 0"
												<?php echo ( isset( $membership_group['upgrade_settings']['upgrade_process'] ) && $membership_group['upgrade_settings']['upgrade_process'] == 'manual' ) ? 'checked' : ''; ?>
												required>
											<label class="ur-membership-upgrade-process-manual--label" for="ur-membership-upgrade-process-manual">
												<b
													class="user-registration-image-label "><?php esc_html_e( 'Manual', 'user-registration' ); ?>
												</b>
											</label>
										</div>
									</label>
								</div>
							</div>
						</div> -->
						<div class="ur-membership-upgrade-container" style="<?php echo esc_attr( $upgrade_style ); ?>">
							<!--						Membership Upgrade Path Type-->
							<div
								class="urm-upgrade-path-type-container ur-membership-selection-container ur-d-flex ur-mt-6 ur-align-items-center ur-d-none"
								data-key-name="<?php echo __( 'Upgrade Type', 'user-registration' ); ?>"
								style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label
										for="ur-membership-upgrade-type-full">
										<?php echo __( 'Upgrade Type', 'user-registration' ); ?>
										<span style="color:red">*</span> :
									</label>

								</div>
								<div class="ur-input-type-select ur-admin-template" style="width: 100%">
									<div class="ur-field ur-d-flex"
										data-field-key="radio">
										<label class="ur-membership-types"
												for="ur-membership-upgrade-type-full">
											<div
												class="ur-membership-type-title ur-d-flex ur-align-items-center ">
												<input
													data-key-name="<?php echo __( 'Upgrade Type', 'user-registration' ); ?>"
													id="ur-membership-upgrade-type-full"
													type="radio" value="full"
													name="ur_membership_upgrade_type"
													style="margin: 0"
													<?php echo ( ( isset( $membership_group['upgrade_type'] ) && $membership_group['upgrade_type'] == 'full' ) ) ? 'checked' : ''; ?>
													required>
												<label class="ur-membership-upgrade-type-full--label" for="ur-membership-upgrade-type-full">
													<b
														class="user-registration-image-label "><?php esc_html_e( 'Full Amount Upgrade', 'user-registration' ); ?>
													</b>
												</label>
											</div>
										</label>
										<!--								Pro rata type-->
										<label
											class="ur-membership-types <?php echo ! UR_PRO_ACTIVE ? 'upgradable-type' : ''; ?>  <?php echo isset( $membership_group['upgrade_type'] ) && $membership_group['upgrade_type'] == 'free' ? 'ur-d-none' : ''; ?>"
											for="ur-membership-upgrade-type-pro-rata">
											<div
												class="ur-membership-type-title ur-d-flex ur-align-items-center">
												<input
													data-key-name="Upgrade Type"
													id="ur-membership-upgrade-type-pro-rata"
													type="radio"
													value="pro-rata"
													name="ur_membership_upgrade_type"
													style="margin: 0"
													<?php echo ( ( isset( $membership_group['upgrade_type'] ) && $membership_group['upgrade_type'] == 'pro-rata' ) ) ? 'checked' : ''; ?>
													<?php echo ! UR_PRO_ACTIVE ? 'disabled' : ''; ?>
													required>
												<label class="ur-membership-upgrade-type-full--label" for="ur-membership-upgrade-type-pro-rata">
													<b
														class="user-registration-image-label "><?php esc_html_e( 'Proration Upgrade', 'user-registration' ); ?>
													</b>
												</label>
											</div>
										</label>
									</div>
								</div>
							</div>
							<div class="ur-membership-upgrade-paths-info">
							<?php
							echo $upgrade_path_html;
							?>
							</div>
							<input type="hidden" name="ur_membership_upgrade_path" value="<?php echo esc_attr( wp_json_encode( $upgrade_path ) ); ?>"/>
						</div>
						<!--						Membership Upgrade Path field-->
						<!-- <div class="ur-membership-input-container ur-d-flex ur-align-items-center ur-d-none"
							style="gap:20px;display:none !important;">
							<div class="ur-label" style="width: 30%; margin-bottom: 0;">
								<label
									for="ur-input-type-membership-upgrade-path"><?php esc_html_e( 'Upgrade Path', 'user-registration' ); ?>
									<span style="color:red">*</span> :
								</label>
							</div>
							<div class="ur-input-type-membership-upgrade-path ur-admin-template"
								style="width: 100%">
								<div class="ur-field" data-field-key="membership_upgrade_path"
									style="width: 100%">
									<select
										multiple
										data-key-name="<?php echo esc_html__( 'Upgrade Path', 'user-registration' ); ?>"
										id="ur-input-type-membership-upgrade-path"
										class="user-membership-enhanced-select2">
										<?php
										$upgrade_path = isset( $membership_details['upgrade_settings']['upgrade_path'] ) ? explode( ',', $membership_details['upgrade_settings']['upgrade_path'] ) : array();

										foreach ( $memberships as $k => $m ) :
											if ( isset( $_GET['post_id'] ) && $_GET['post_id'] == $m['ID'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
												continue;
											}
											$membership_group_repository = new WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository();
											$membership_group_service    = new WPEverest\URMembership\Admin\Services\MembershipGroupService();
											$membership_group_id         = $membership_group_repository->get_membership_group_by_membership_id( $m['ID'] );

											if ( isset( $membership_group_id['ID'] ) ) {
												$multiple_memberships_allowed = $membership_group_service->check_if_multiple_memberships_allowed( $membership_group_id['ID'] );

												if ( $multiple_memberships_allowed ) {
													continue;
												}
											}

											$selected = ( $upgrade_path ) && in_array( $m['ID'], $upgrade_path, true ) ? 'selected="selected"' : '';
											?>
											<option
												<?php echo $selected; ?>
												value="<?php echo esc_attr( $m['ID'] ); ?>"><?php echo esc_html( $m['title'] ); ?></option>
											<?php
										endforeach;
										?>
									</select>
								</div>
							</div>
						</div> -->
					</div>
				</div>
				<hr>
				<?php
				$save_btn_class  = 'ur-membership-group-save-btn';
				$create_btn_text = isset( $_GET['post_id'] ) ? esc_html__( 'Save', 'user-registration' ) : esc_html__( 'Create Membership Group', 'user-registration' );
				require __DIR__ . '/./Partials/footer-actions.php'
				?>
			</div>
		</form>
	</div>
</div>
