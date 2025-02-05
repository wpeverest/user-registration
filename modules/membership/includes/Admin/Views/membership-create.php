<div class="ur-membership">
	<?php
	require __DIR__ . '/./Partials/header.php';
	$is_pro     = is_plugin_active( 'user-registration-pro/user-registration.php' );
	$return_url = admin_url( 'admin.php?page=user-registration-membership' );
	$is_editing = !empty($_GET['post_id']);
	?>
	<div
		class="ur-membership-tab-contents-wrapper ur-registered-from ur-align-items-center ur-justify-content-center">
		<form id="ur-membership-create-form" method="post" style="width: 80%">
			<div class="user-registration-card">
				<div class="user-registration-card__header ur-d-flex ur-align-items-center">
					<a class="ur-text-muted ur-d-flex"
					   href="<?php echo $return_url; ?>">
						<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
							 fill="none"
							 stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
							<line x1="19" y1="12" x2="5" y2="12"></line>
							<polyline points="12 19 5 12 12 5"></polyline>
						</svg>
					</a>
					<h3>
						<?php echo isset( $_GET['post_id'] ) ? esc_html_e( 'Edit Membership', 'user-registration' ) : esc_html_e( 'Create New Membership', 'user-registration' ); ?>
					</h3>
				</div>
				<div class="user-registration-card__body">
					<div id="ur-membership-main-fields">
						<!--					membership name-->
						<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-name"><?php esc_html_e( 'Membership Name', 'user-registration' ); ?>
									<span style="color:red">*</span>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field" data-field-key="membership_name">
									<input type="text" data-key-name="Membership Name"
										   id="ur-input-type-membership-name" name="ur_membership_name"
										   style="width: 100%"
										   autocomplete="off"
										   value="<?php echo isset( $membership->post_title ) && ! empty( $membership->post_title ) ? $membership->post_title : ''; ?>"
										   required>
								</div>
							</div>

						</div>
						<!--					membership description-->
						<div class="ur-membership-input-container ur-input-type-textarea ur-d-flex ur-p-1 ur-mt-3"
							 style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label for="ur-input-type-membership-description">Membership Description</label>
							</div>
							<div class="ur-field" data-field-key="textarea" style="width: 100%">
								<?php
								$membership_description = '';
								if ( isset( $membership->post_content ) && ! empty( $membership->post_content ) ) {
									$membership_content     = json_decode( wp_unslash( $membership->post_content ), true );
									$membership_description = $membership_content['description'];
								}
								?>
								<textarea data-key-name="Membership Description"
										  id="ur-input-type-membership-description"
										  name="ur_membership_description"
										  style="width: 100%" rows="5"
										  value=""><?php echo $membership_description; ?></textarea>
							</div>
						</div>
						<!--					membership status-->
						<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3" style="gap:20px; <?php echo $is_editing ? '' : 'display:none !important'; ?> ">
							<div class="ur-label" style="width: 30%">
								<label class="ur-membership-enable-status"
									   for="ur-membership-status"><?php esc_html_e( 'Membership Status', 'user-registration' ); ?>
									<span class="user-registration-help-tip tooltipstered"
										  data-tip="<?php echo esc_attr__( "Active or Inactive state of a membership." ) ?>"></span>

								</label>
							</div>
							<div class="user-registration-switch ur-ml-auto" style="width: 100%">

								<input
									data-key-name="Membership Status"
									id="ur-membership-status" type="checkbox"
									class="user-registration-switch__control hide-show-check enabled"
									<?php echo isset( $membership_content ) && $membership_content['status'] == 'true' ? 'checked' : ($is_editing ? '' : 'checked') ; ?>
									name="ur_membership_status"
									style="width: 100%; text-align: left">
							</div>

						</div>

						<!--						role-->
						<div class="ur-membership-input-container ur-d-flex ur-p-3" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-role"><?php esc_html_e( 'Membership Role', 'user-registration' ); ?>
									<span style="color:red">*</span>
									<span class="user-registration-help-tip tooltipstered"
										  data-tip="Assign members to the selected role upon registration.(Overrides role set through form)"></span>
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field">
									<select
										data-key-name="<?php echo esc_html__( 'Role', 'user-registration' ); ?>"
										id="ur-input-type-membership-role"
										class="user-membership-enhanced-select2">
										<?php
										foreach ( $roles as $k => $role ) :

											$selected = ( isset( $membership_details['role'] ) && $k === $membership_details['role'] )
												? 'selected="selected"'
												: ( ( $k === 'subscriber' && ! isset( $membership_details['role'] ) ) ? 'selected="selected"' : '' );
											?>
											<option
												<?php echo $selected ?>
												value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $role ); ?></option>
										<?php
										endforeach;
										?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<!--					membership plan type and pricing section-->
					<div id="ur-membership-plan-and-price-section" class="ur-p-2 ur-mt-2">
						<div class="user-registration-card" style="background: #f8f8fa">
							<!--							membership type and price header-->

							<div class="user-registration-card__header ur-d-flex ur-align-items-center">
								<h3><?php esc_html_e( 'Membership Type & Price', 'user-registration' ); ?></h3>
							</div>
							<div class="user-registration-card__body">
								<!--								membership type-->
								<div class="ur-membership-selection-container ur-d-flex ur-p-1" style="gap:20px;">
									<div class="ur-label" style="width: 30%">
										<label
											for="ur-membership-free-type"><?php esc_html_e( 'Type', 'user-registration' ); ?></label>
									</div>
									<div class="ur-input-type-select ur-admin-template" style="width: 100%">
										<div class="ur-field ur-d-flex"
											 data-field-key="radio">
											<!--											free type-->
											<label class="ur-membership-types" for="ur-membership-free-type">
												<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
													<input data-key-name="Type" id="ur-membership-free-type"
														   type="radio" value="free"
														   name="ur_membership_type"
														   style="margin: 0"
														   checked
														<?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'free' ? 'checked' : ''; ?>
														   required>
													<label class="ur-p-2" for="ur-membership-free-type">
														<b
															class="user-registration-image-label "><?php esc_html_e( 'Free', 'user-registration' ); ?>
														</b>
													</label>
												</div>
												<div class="ur-membership-type-description">
													<p style="word-break: break-word; font-size: 12px;">
														<?php
														echo __(
															"This is a free membership. This is free of cost and doesn't
														require any fees.",
															'user-registration'
														)
														?>
													</p>
												</div>
											</label>
											<!--											paid type-->
											<label class="ur-membership-types" for="ur-membership-paid-type">
												<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
													<input
														data-key-name="Type"
														id="ur-membership-paid-type" type="radio" style="margin: 0"
														value="paid"
														name="ur_membership_type"
														class="ur_membership_paid_type"
														<?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'paid' ? 'checked' : ''; ?>

													>
													<label class="ur-p-2" for="ur-membership-paid-type">
														<b
															class="user-registration-image-label"><?php esc_html_e( 'Paid', 'user-registration' ); ?>
														</b>
													</label>
												</div>
												<div class="ur-membership-type-description">
													<p style="word-break: break-word; font-size: 12px;">
														<?php
														echo __( 'This is a paid membership plan. It requires a certain amount to get you started.', 'user-registration' )
														?>
													</p>
												</div>
											</label>
											<!--											subscription type-->
											<label
												class="ur-membership-types <?php echo ! $is_pro ? 'upgradable-type' : '' ?>"
												for="ur-membership-subscription-type">
												<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
													<input
														data-key-name="Type"
														id="ur-membership-subscription-type" style="margin: 0"
														type="radio"
														value="subscription"
														name="ur_membership_type"
														class="ur_membership_paid_type"
														<?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'subscription' ? 'checked' : ''; ?>
														<?php echo ! $is_pro ? 'disabled' : '' ?>
													>
													<label class="ur-p-2" for="ur-membership-subscription-type">
														<b
															class="user-registration-image-label"><?php esc_html_e( 'Subscription Based', 'user-registration' ); ?>
														</b>
													</label>
												</div>
												<div class="ur-membership-type-description">
													<p style="word-break: break-word; font-size: 12px;">
														<?php
														echo __( 'This plan requires to get renewed after certain period of time.', 'user-registration' )
														?>

													</p>
												</div>
											</label>
										</div>
									</div>
								</div>
								<?php
								if ( false ):
									?>
									<div
										class="ur-membership-cancellation-container"
									>
										<!--								cancel subscription section-->
										<div class="ur-membership-selection-container ur-d-flex ur-p-1 ur-mt-3"
											 style="gap:20px;">
											<div class="ur-label" style="width: 30%">
												<label
													for="ur-membership-cancel-sub-immediately"><?php esc_html_e( 'Cancel Membership', 'user - registration' ); ?></label>
											</div>
											<div class="ur-input-type-select ur-admin-template" style="width: 100%">
												<div class="ur-field ur-d-flex ur-align-items-center" style="gap: 10px">
													<!--												uncomment in future if finite subscription logic needs to be added-->
													<!--												<input data-key-name="Cancel Subscription" type="radio"-->
													<!--													   id="ur-membership-cancel-sub-on-expiry"-->
													<!--													   name="ur_membership_cancel_on" style="margin: 0"-->
													<!--													   value="expiry"-->
													<!--													--><?php // echo isset( $membership_details['cancel_subscription'] ) && $membership_details['cancel_subscription'] == 'expiry' ? 'checked' : ''
													?>
													<!--												>-->
													<!--												<label for="ur-membership-cancel-sub-on-expiry">-->
													<?php // echo __("Do not cancel subscription until plan expired.", "user-registration")
													?><!--</label>-->
													<input data-key-name="Cancel Subscription" type="radio"
														   id="ur-membership-cancel-sub-immediately"
														   style="margin: 0"
														   name="ur_membership_cancel_on"
														   value="immediately"
														<?php echo ! isset( $membership_details['cancel_subscription'] ) ? 'checked' : ''; ?>
														<?php echo isset( $membership_details['cancel_subscription'] ) && $membership_details['cancel_subscription'] == 'immediately' ? 'checked' : ''; ?>
													>
													<label
														for="ur-membership-cancel-sub-immediately"><?php echo __( 'Cancel immediately.', 'user-registration' ); ?></label>
												</div>
											</div>
										</div>
									</div>
								<?php
								endif;
								?>
								<!-- paid plan fields including subscription wise membership fields-->
								<div id="paid-plan-container"
									 class="
									<?php
									 echo isset( $membership_details['type'] ) && in_array(
										 $membership_details['type'],
										 array(
											 'paid',
											 'subscription',
										 )
									 ) ? '' : 'ur-d-none'
									 ?>
									 ">
									<!--								membership amount-->
									<div class="ur-membership-selection-container ur-d-flex ur-p-1 ur-mt-3"
										 style="gap:20px;">
										<div class="ur-label" style="width: 30%">
											<label
												for="ur-membership-amount"><?php esc_html_e( 'Amount', 'user-registration' ); ?>
												<span style="color:red">*</span>
											</label>
										</div>

										<div class="ur-field field-amount" data-field-key="membership_amount"
											 style="width: 100%">
											<span>
												<?php
												$currency   = get_option( 'user_registration_payment_currency', 'USD' );
												$currencies = ur_payment_integration_get_currencies();
												$symbol     = $currencies[ $currency ]['symbol'];
												echo $symbol;
												?>
											</span>
											<input data-key-name="Amount" type="number" id="ur-membership-amount"
												   value="<?php echo $membership_details['amount'] ?? 1; ?>"
												   name="ur_membership_amount"
												   style="width: 100%" min="0"
												   required>
										</div>
									</div>
									<!--									subscription fields container-->
									<div
										class="ur-membership-subscription-field-container <?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'subscription' ? '' : 'ur-d-none'; ?>">
										<!--								membership duration-->
										<div class="ur-membership-selection-container ur-d-flex ur-p-1 ur-mt-3"
											 style="gap:20px;">
											<div class="ur-label" style="width: 30%">
												<label
													for="ur-membership-duration"><?php esc_html_e( 'Duration', 'user-registration' ); ?>
													<span style="color:red">*</span>
												</label>
											</div>
											<div class="ur-field ur-d-flex ur-align-items-center"
												 data-field-key="membership_duration" style="width: 100%; gap: 20px;">
												<input
													data-key-name="Duration Value"
													value="<?php echo isset( $membership_details['subscription'] ) ? $membership_details['subscription']['value'] : 1; ?>"
													class=""
													type="number" name="ur_membership[duration]_value"
													autocomplete="off" id="ur-membership-duration-value"
													min="1"
												>
												<select
													id="ur-membership-duration"
													data-key-name="Duration"
													class=""
													name="ur_membership[duration]_period" style="width: 100%">
													<option
														value="day" <?php echo isset( $membership_details['subscription'] ) && $membership_details['subscription']['duration'] == 'day' ? 'selected="selected"' : ''; ?>
													>
														Day(s)
													</option>
													<option
														value="week" <?php echo isset( $membership_details['subscription'] ) && $membership_details['subscription']['duration'] == 'week' ? 'selected="selected"' : ''; ?>
													>
														Week(s)
													</option>
													<option
														value="month" <?php echo isset( $membership_details['subscription'] ) && $membership_details['subscription']['duration'] == 'month' ? 'selected="selected"' : ''; ?>
													>
														Month(s)
													</option>
													<option
														value="year" <?php echo isset( $membership_details['subscription'] ) && $membership_details['subscription']['duration'] == 'year' ? 'selected="selected"' : ''; ?>
													>Year(s)
													</option>
												</select>
											</div>
										</div>
										<!--								trial section-->
										<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
											 style="gap:20px">
											<div class="ur-label" style="width: 30%">
												<label class="ur-membership-trial-status"
													   for="ur-membership-trial-status"><?php esc_html_e( 'Trial Period', 'user - registration' ); ?></label>
											</div>
											<div class="user-registration-switch ur-ml-auto" style="width: 100%">
												<input
													data-key-name="Trial Period"
													id="ur-membership-trial-status"
													type="checkbox"
													class="user-registration-switch__control hide-show-check enabled"
													name="ur_membership[trial]_status"
													style="width: 100%;"
													value="<?php echo isset( $membership_details['trial_status'] ) && $membership_details['trial_status'] == 'on' ? 'on' : 'off'; ?>"
													<?php echo isset( $membership_details['trial_status'] ) && $membership_details['trial_status'] == 'on' ? 'checked' : ''; ?>
												>
											</div>
										</div>
										<div
											class="trial-container <?php echo isset( $membership_details['trial_status'] ) && $membership_details['trial_status'] == 'on' ? '' : 'ur-d-none'; ?>">
											<div
												class="trial-container--wrapper ur-d-flex ur-p-3 ur-ml-2 ur-align-items-center">
												<div class="ur-label">
													<label class="ur-membership-trial-status"
														   for="ur-membership-trial-duration"><?php esc_html_e( 'Trial Period Duration', 'user - registration' ); ?>
														<span style="color:red">*</span>
													</label>
												</div>
												<div class="ur-field ur-d-flex ur-align-items-center">
													<input
														data-key-name="Trial Period Duration Value"
														value="<?php echo isset( $membership_details['trial_data'] ) ? $membership_details['trial_data']['value'] : 1; ?>"
														class=""
														type="number" name="ur_membership[trial]_value"
														autocomplete="off"
														id="ur-membership-trial-duration-value"
														min="1"
													>
													<select
														class="Trial Period Duration"
														id="ur-membership-trial-duration"
														name="ur_membership[trial]_duration">
														<option value="day"
															<?php echo isset( $membership_details['trial_data'] ) && $membership_details['trial_data']['duration'] == 'day' ? 'selected="selected"' : ''; ?>
														>Day(s)
														</option>
														<option value="week"
															<?php echo isset( $membership_details['trial_data'] ) && $membership_details['trial_data']['duration'] == 'week' ? 'selected="selected"' : ''; ?>
														>Week(s)
														</option>
														<option value="month"
															<?php echo isset( $membership_details['trial_data'] ) && $membership_details['trial_data']['duration'] == 'month' ? 'selected="selected"' : ''; ?>
														>Month(s)
														</option>
														<option value="year"
															<?php echo isset( $membership_details['trial_data'] ) && $membership_details['trial_data']['duration'] == 'year' ? 'selected="selected"' : ''; ?>
														>Year(s)
														</option>
													</select>
												</div>
											</div>
										</div>

									</div>
								</div>
								<!--								membership all payments-->
								<?php
								require __DIR__ . '/./Partials/membership-admin-payments.php'
								?>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<?php
				$save_btn_class  = 'ur-membership-save-btn';
				$create_btn_text = isset( $_GET['post_id'] ) ? esc_html__( 'Save', 'user-registration' ) : esc_html__( 'Create Membership', 'user-registration' );
				require __DIR__ . '/./Partials/footer-actions.php'
				?>
			</div>
		</form>
	</div>
</div>
