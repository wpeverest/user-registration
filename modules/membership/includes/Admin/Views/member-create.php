<?php
require __DIR__ . '/./Partials/header.php';
$return_url = admin_url( 'admin.php?page=user-registration-members' );

?>
<div class="ur-membership-tab-contents-wrapper ur-align-items-center ur-justify-content-center">
	<form id="ur-membership-create-form" method="post">
		<div class="user-registration-card">
			<div id="ur-membership-form-container" class="ur-d-flex">
				<div id="ur-member-form-left">
					<div id="left-title" class=" ur-d-flex ur-align-items-center">
						<a class="ur-text-muted ur-d-flex"
						   href="<?php echo esc_attr( empty( $_SERVER['HTTP_REFERER'] ) ? '#' : $_SERVER['HTTP_REFERER'] ); ?>">
							<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
								 fill="none"
								 stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
								<line x1="19" y1="12" x2="5" y2="12"></line>
								<polyline points="12 19 5 12 12 5"></polyline>
							</svg>
						</a>
						<h3>
							<?php echo isset( $_GET['post_id'] ) ? esc_html_e( 'Edit Member', 'user-registration' ) : esc_html_e( 'Create New Member', 'user-registration' ); ?>
						</h3>
					</div>

					<div id="left-body" class="">
						<!--						first name-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-first-name"><?php esc_html_e( 'First Name', 'user-registration' ); ?>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field" data-field-key="membership_name">
									<input
										class="ur-membership-members-input"
										type="text"
										data-key-name="<?php echo esc_html__( 'firstname', 'user-registration' ); ?>"
										id="ur-input-type-membership-first-name" name="ur_membership_first_name"
										style="width: 100%"
									>
								</div>
							</div>
						</div>
						<!--						last name-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-last-name"><?php esc_html_e( 'Last Name', 'user-registration' ); ?>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<input type="text"
										   class="ur-membership-members-input"
										   data-key-name="<?php echo esc_html__( 'lastname', 'user-registration' ); ?>"
										   id="ur-input-type-membership-last-name" name="ur_membership_last_name"
										   style="width: 100%"
									>
								</div>
							</div>
						</div>
						<!--						username-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-username"><?php esc_html_e( 'Username', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<input type="text"
										   autocomplete="off"
										   class="ur-membership-members-input"
										   data-key-name="<?php echo esc_html__( 'Username', 'user-registration' ); ?>"
										   id="ur-input-type-membership-username" name="ur_membership_username"
										   style="width: 100%"
										   required>
								</div>
							</div>

						</div>
						<!--						email-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-email"><?php echo esc_html_e( 'Email', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-email ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<input type="email"
										   class="ur-membership-members-input"
										   data-key-name="<?php echo esc_html__( 'Email', 'user-registration' ); ?>"
										   id="ur-input-type-membership-email" name="ur_membership_email"
										   style="width: 100%"
										   required>
								</div>
							</div>
						</div>
						<!--						password-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-password"><?php esc_html_e( 'Password', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-password ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<input
										autocomplete="off"
										data-key-name="<?php echo esc_html__( 'Password', 'user-registration' ); ?>"
										class="ur-membership-members-input"
										type="password"
										id="ur-input-type-membership-password" name="ur_membership_password"
										style="width: 100%"
										required>
								</div>
							</div>
						</div>
						<!--						confirm password-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-confirm-password"><?php esc_html_e( 'Confirm Password', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-confirm-password ur-admin-template"
								 style="width: 100%">
								<div class="ur-field">
									<input type="password"
										   data-key-name="<?php echo esc_html__( 'confirm_password', 'user-registration' ); ?>"
										   class="ur-membership-members-input"
										   id="ur-input-type-membership-confirm-password"
										   name="ur_membership_confirm_password"
										   style="width: 100%"
										   required>
								</div>
							</div>
						</div>
						<!--						role-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-member-role"><?php esc_html_e( 'Member Role', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<select
										data-key-name="<?php echo esc_html__( 'Role', 'user-registration' ); ?>"
										id="ur-input-type-membership-member-role"
										class="user-membership-enhanced-select2 ur-membership-members-input ur-enhanced-select">
										<?php
										foreach ( $roles as $k => $role ) :
											?>
											<option
												value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $role ); ?></option>
											<?php
										endforeach;
										?>
									</select>
								</div>
							</div>
						</div>
						<!--						status-->
<!--						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px">-->
<!--							<div class="ur-label" style="width: 30%">-->
<!--								<label class="ur-membership-enable-status"-->
<!--									   for="ur-membership-status">--><?php //esc_html_e( 'Member Status', 'user-registration' ); ?><!--</label>-->
<!--							</div>-->
<!--							<div class="user-registration-switch ur-ml-auto" style="width: 100%">-->
<!---->
<!--								<input-->
<!--									data-key-name="--><?php //echo esc_html__( 'member_status', 'user-registration' ); ?><!--"-->
<!--									id="ur-membership-status" type="checkbox"-->
<!--									class="user-registration-switch__control hide-show-check enabled ur-membership-members-input"-->
<!--									--><?php //echo esc_attr( isset( $membership_content ) && $membership_content['status'] == 'true' ? 'checked' : '' ); ?>
<!--									name="ur_membership_status"-->
<!--									style="width: 100%; text-align: left">-->
<!--							</div>-->
<!---->
<!--						</div>-->
					</div>
				</div>
				<div id="ur-member-form-right" class="ur-p-4">
					<div class="right-title">
						<h3 class="ur-mt-2">
							<?php esc_html_e( 'Select Plan', 'user-registration' ); ?>
						</h3>
					</div>

					<div class="right-body ur-d-flex ur-flex-column">
						<div class="form-row ur-enhanced-select ur-mt-3">
							<label for="ur-membership-select" class="ur-label">
								<?php echo esc_html__( 'Membership', 'user-registration' ); ?>
							</label>
							<select
								class="ur-membership-members-input  ur-enhanced-select user-membership-enhanced-select2"
								data-key-name="<?php echo esc_html__( 'Membership', 'user-registration' ); ?>"
								name="ur-membership-select"
								id="ur-membership-select"
								style="width: 100%"
							>
								<?php
								foreach ( $memberships as $k => $membership ) :
									?>
									<option
										value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $membership ); ?></option>
									<?php
								endforeach;
								?>
							</select>
						</div>
						<div class="form-row ur-mt-3">
							<label for="ur-membership-start-date" class="ur-label">
								<?php echo esc_html__( 'Start Date', 'user-registration' ); ?>
							</label>
							<input
								class="ur-membership-members-input"
								data-key-name="<?php echo esc_html__( 'start_date', 'user-registration' ); ?>"
								id="ur-membership-start-date" type="date" style="width: 100%"
								value="<?php echo date( 'Y-m-d' ); ?>">

						</div>

					</div>
				</div>
			</div>
			<?php
			$save_btn_class  = 'ur-member-save-btn';
			$create_btn_text = isset( $_GET['post_id'] ) ? esc_html__( 'Save', 'user-registration' ) : esc_html__( 'Create Member', 'user-registration' );
			require __DIR__ . '/./Partials/footer-actions.php'
			?>
		</div>
	</form>
</div>
