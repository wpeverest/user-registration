<div class="ur-membership">
	<?php
	require __DIR__ . '/./Partials/header.php';
	$return_url = admin_url( 'admin.php?page=user-registration-membership&action=list_groups' );

	?>
	<div
		class="ur-membership-tab-contents-wrapper ur-registered-from ur-align-items-center ur-justify-content-center">
		<form id="ur-membership-create-form" method="post" style="width: 80%">
			<div id="ur-membership-group-create-form" class="user-registration-card">
				<div class="user-registration-card__header ur-d-flex ur-align-items-center">
					<a class="ur-text-muted ur-d-flex"
					   href="<?php echo admin_url( 'admin.php?page=user-registration-membership&action=list_groups' ); ?>">
						<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
							 fill="none"
							 stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
							<line x1="19" y1="12" x2="5" y2="12"></line>
							<polyline points="12 19 5 12 12 5"></polyline>
						</svg>
					</a>
					<h3>
						<?php echo isset( $_GET['post_id'] ) ? esc_html_e( 'Edit Membership Group', 'user-registration' ) : esc_html_e( 'Create New Membership Group', 'user-registration' ); ?>
					</h3>
				</div>
				<div class="user-registration-card__body">
					<div id="ur-membership-main-fields">
						<!--					membership group name-->
						<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-group-name"><?php esc_html_e( 'Group Name', 'user-registration' ); ?>
									<span style="color:red">*</span>
									<span class="user-registration-help-tip tooltipstered"
										  data-tip="<?php echo esc_attr__( "Title for the group." ) ?>"></span>
								</label>
							</div>
							<div class="ur-input-type-membership-group-name ur-admin-template" style="width: 100%">
								<div class="ur-field" data-field-key="membership_group_name">
									<input type="text" data-key-name="Membership Group Name"
										   id="ur-input-type-membership-group-name" name="ur_membership_group_name"
										   style="width: 100%"
										   autocomplete="off"
										   value="<?php echo ! empty( $membership_group['post_title'] ) ? $membership_group['post_title'] : ''; ?>"
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
										  data-tip="<?php echo esc_attr__( "Describe the group." ) ?>"></span>
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
										  data-tip="<?php echo esc_attr__( "Only active groups will be visible in the frontend." ) ?>"></span>
								</label>
							</div>
							<div class="user-registration-switch ur-ml-auto" style="width: 100%">
								<input
									data-key-name="Membership Status"
									id="ur-membership-group-status" type="checkbox"
									class="user-registration-switch__control hide-show-check enabled urmg-input"
									<?php echo isset( $membership_content ) && $membership_content['status'] == 'true' ? 'checked' : ''; ?>
									name="ur_membership_status"
									style="width: 100%; text-align: left">
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
										if ( isset( $membership_group["memberships"] ) ) {
											$selected_memberships = json_decode( $membership_group["memberships"], true );
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
