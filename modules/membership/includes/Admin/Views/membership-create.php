<?php
$return_url = admin_url( 'admin.php?page=user-registration-membership' );
$is_editing = ! empty( $_GET['post_id'] );
if ( isset( $membership->post_content ) && ! empty( $membership->post_content ) ) {
	$membership_content = json_decode( wp_unslash( $membership->post_content ), true );
}
?>
<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
	<div class="ur-page-title__wrapper">
		<div class="ur-page-title__wrapper--left">
			<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2" href="<?php echo esc_attr( $return_url ); ?>">
				<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			</a>
			<div class="ur-page-title__wrapper--left-menu">
				<div class="ur-page-title__wrapper--left-menu__items ur-page-title__wrapper--steps">
					<button class="ur-page-title__wrapper--steps-btn ur-page-title__wrapper--steps-btn-active" data-step="0" id="ur-basic-tab">
						<div class="ur-page-title__wrapper--steps-wrapper">
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#e9e9e9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3.667c6.811 0 12.334 5.521 12.334 12.333 0 6.811-5.523 12.334-12.334 12.334S3.667 22.81 3.667 16C3.667 9.188 9.189 3.667 16 3.667"/><g clip-path="url(#a)"><path stroke="#222" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15.997 10.802a.65.65 0 0 1 .36.11l.097.08 4.554 4.553a.65.65 0 0 1 .08.817l-.08.098-4.554 4.553a.65.65 0 0 1-.816.08l-.098-.08-4.554-4.554a.65.65 0 0 1-.19-.457l.014-.125a.6.6 0 0 1 .096-.234l.08-.098 4.554-4.553a.65.65 0 0 1 .457-.19"/></g><defs><clipPath id="a"><path fill="#fff" d="M10 9.5h12v13H10z"/></clipPath></defs></svg>
							<span>Basics</span>
						</div>
					</button>
					<hr class="ur-page-title__wrapper--steps-separator" />
					<button class="ur-page-title__wrapper--steps-btn" data-step="1" id="ur-advanced-tab">
						<div class="ur-page-title__wrapper--steps-wrapper">
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#e9e9e9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 29.333c7.364 0 13.334-5.97 13.334-13.333S23.364 2.667 16 2.667 2.667 8.637 2.667 16 8.637 29.333 16 29.333"/><g stroke="#222" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.569" clip-path="url(#a)"><path d="M17.199 19h-5.4M20.199 13h-5.4M19.001 20.8a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6M13.001 14.8a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6"/></g><defs><clipPath id="a"><path fill="#fff" d="M10 10h12v12H10z"/></clipPath></defs></svg>
							<span>Advanced</span>
						</div>
					</button>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<div class="ur-page-title__wrapper--right-menu">
				<div class="ur-page-title__wrapper--right-menu__item">
					<div class="ur-page-title__wrapper--actions">
						<div class="ur-page-title__wrapper--actions-status">
							<p>Status</p>
							<span class="separator">|</span>
							<div class="ur-d-flex ur-align-items-center visible" style="gap: 5px">
								<div class="ur-toggle-section">
									<span class="user-registration-toggle-form">
										<input
										data-key-name="Membership Status"
										id="ur-membership-status"
										class="ur-membership-change__status user-registration-switch__control hide-show-check enabled"
										type="checkbox"
										value="1"
										<?php
										checked(
											true,
											isset( $membership_content['status'] ) && 'true' === $membership_content['status'] ? ur_string_to_bool( $membership_content['status'] ) : true,
											true
										);
										?>
										>
										<span class="slider round"></span>
									</span>
								</div>
							</div>
						</div>
						<div class="ur-page-title__wrapper--actions-publish">
							<button class="button-primary ur-membership-save-btn" type="submit">
								<?php esc_html_e( 'Publish', 'user-registration' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="ur-membership">
	<div
		class="ur-membership-tab-contents-wrapper ur-registered-from ur-align-items-center ur-justify-content-center">
		<form id="ur-membership-create-form" method="post">
			<div class="user-registration-card user-registration-card--form-step user-registration-card--form-step-active">
				<div class="user-registration-card__body">
					<div id="ur-membership-main-fields">
						<!--					membership name-->
						<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-name">
									<?php esc_html_e( 'Name', 'user-registration' ); ?>
									<span style="color:red">*</span> :
								</label>
							</div>
							<div class="ur-input-type-membership-name ur-admin-template" style="width: 100%">
								<div class="ur-field" data-field-key="membership_name">
									<input type="text" data-key-name="Membership Name"
											id="ur-input-type-membership-name" name="ur_membership_name"
											style="width: 100%"
											autocomplete="off"
											value="<?php echo isset( $membership->post_title ) && ! empty( $membership->post_title ) ? esc_html( $membership->post_title ) : ''; ?>"
											required>
								</div>
							</div>

						</div>
						<!--					membership description-->
						<div class="ur-membership-input-container ur-input-type-textarea ur-d-flex ur-p-1 ur-mt-3"
							style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label for="ur-input-type-membership-description"><?php esc_html_e( 'Description :', 'user-registration' ); ?></label>
							</div>
							<div class="ur-field" data-field-key="textarea" style="width: 100%">
								<?php
								wp_editor(
									! empty( $membership_content['description'] ) ? $membership_content['description'] : ( ! empty( $membership_details['description'] ) ? $membership_details['description'] : '' ),
									'ur-input-type-membership-description',
									array(
										'textarea_name' => 'Membership Description',
										'textarea_rows' => 50,
										'media_buttons' => false,
										'quicktags'     => false,
										'teeny'         => true,
										'show-ur-registration-form-button' => false, // Hide Add Registration button
										'show-smart-tags-button' => true, // Show Smart Tags button
										'tinymce'       => array(
											'theme'       => 'modern',
											'skin'        => 'lightgray',
											'toolbar1'    => 'undo,redo,formatselect,fontselect,fontsizeselect,bold,italic,forecolor,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat',
											'content_css' => 'default',
											'branding'    => false,
											'resize'      => true,
											'statusbar'   => false,
											'menubar'     => false,
											'menu'        => false,
											'elementpath' => true,
											'plugins'     => 'wordpress,wpautoresize,wplink,wpdialogs,wptextpattern,wpview,colorpicker,textcolor,hr,charmap,link,fullscreen,lists',
										),
									)
								);
								?>
							</div>
						</div>
						<!--								membership type-->
						<div class="ur-membership-selection-container ur-d-flex ur-p-1" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-membership-free-type"><?php esc_html_e( 'Type :', 'user-registration' ); ?></label>
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
												<?php echo isset( $membership_details['type'] ) && 'free' === $membership_details['type'] ? 'checked' : ''; ?>
													required>
											<label class="ur-p-2" for="ur-membership-free-type">
												<b
													class="user-registration-image-label "><?php esc_html_e( 'Free', 'user-registration' ); ?>
												</b>
											</label>
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
												<?php echo isset( $membership_details['type'] ) && 'paid' === $membership_details['type'] ? 'checked' : ''; ?>

											>
											<label class="ur-p-2" for="ur-membership-paid-type">
												<b
													class="user-registration-image-label"><?php esc_html_e( 'One-Time Payment', 'user-registration' ); ?>
												</b>
											</label>
										</div>
									</label>
									<!--											subscription type-->
									<label
										class="ur-membership-types <?php echo !UR_PRO_ACTIVE ? 'upgradable-type' : ''; ?>"
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
												<?php echo !UR_PRO_ACTIVE ? 'disabled' : ''; ?>
											>
											<label class="ur-p-2" for="ur-membership-subscription-type">
												<b
													class="user-registration-image-label"><?php esc_html_e( 'Subscription Based', 'user-registration' ); ?>
												</b>
											</label>
										</div>
									</label>
								</div>
							</div>
						</div>
						<?php
						if ( false ) :
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
											<!--													-->
										<?php
										// echo isset( $membership_details['cancel_subscription'] ) && $membership_details['cancel_subscription'] == 'expiry' ? 'checked' : ''
										?>
											<!--												>-->
											<!--												<label for="ur-membership-cancel-sub-on-expiry">-->
										<?php
										// echo __("Do not cancel subscription until plan expired.", "user-registration")
										?>
											<!--</label>-->
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
						<!-- paid plan fields -->
						<div id="paid-plan-container"
							class="
							<?php
							echo isset( $membership_details['type'] ) && in_array(
								$membership_details['type'],
								array(
									'paid',
									'subscription',
								),
								true
							) ? '' : 'ur-d-none'
							?>
							">
							<!--					membership amount and duration-->
							<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label
										for="ur-membership-amount">
										<?php esc_html_e( 'Price', 'user-registration' ); ?>
										<span style="color:red">*</span> :
									</label>
								</div>
								<div class="ur-d-flex" style="gap:16px;width:100%;">
									<div class="ur-field field-amount" data-field-key="membership_amount">
										<span class="ur-currency-symbol">
											<?php
											$currency   = get_option( 'user_registration_payment_currency', 'USD' );
											$currencies = ur_payment_integration_get_currencies();
											$symbol     = $currencies[ $currency ]['symbol'];
											echo esc_html( $symbol );
											?>
										</span>
										<input data-key-name="Amount" type="number" id="ur-membership-amount"
												value="<?php echo esc_html( $membership_details['amount'] ?? 1 ); ?>"
												name="ur_membership_amount"
												style="width: 80%" min="0"
												required>
										<span class="ur-currency"><?php echo esc_html( $currency ); ?></span>
									</div>
									<select
										id="ur-membership-duration"
										data-key-name="Duration"
										class="ur-subscription-fields <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? '' : 'ur-d-none'; ?>"
										name="ur_membership[duration]_period" style="width: 15%">
										<option
											value="day" <?php echo isset( $membership_details['subscription'] ) && 'day' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>
										>
											Day(s)
										</option>
										<option
											value="week" <?php echo isset( $membership_details['subscription'] ) && 'week' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>
										>
											Week(s)
										</option>
										<option
											value="month" <?php echo isset( $membership_details['subscription'] ) && 'month' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>
										>
											Month(s)
										</option>
										<option
											value="year" <?php echo isset( $membership_details['subscription'] ) && 'year' === $membership_details['subscription']['duration'] ? 'selected="selected"' : ''; ?>
										>Year(s)
										</option>
									</select>
								</div>
							</div>
							<!--				membership duration-->
							<div class="ur-membership-selection-container ur-p-1 ur-mt-3 ur-subscription-fields <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? 'ur-d-flex' : 'ur-d-none'; ?>" id="ur-membership-duration-container"
								style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label
										for="ur-membership-duration"><?php esc_html_e( 'Duration', 'user-registration' ); ?>
										<span style="color:red">*</span> :
									</label>
								</div>
								<div class="ur-field ur-d-flex ur-align-items-center"
									data-field-key="membership_duration" style="width: 100%; gap: 20px;">
									<input
										data-key-name="Duration Value"
										value="<?php echo isset( $membership_details['subscription'] ) ? esc_attr( $membership_details['subscription']['value'] ) : 1; ?>"
										class=""
										type="number" name="ur_membership[duration]_value"
										autocomplete="off" id="ur-membership-duration-value"
										min="1"
									>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="user-registration-card user-registration-card--form-step" >
				<div class="user-registration-card__body">
					<div id="ur-membership-plan-and-price-section">
						<!--						role-->
						<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label
									for="ur-input-type-membership-role"><?php esc_html_e( 'Membership Role :', 'user-registration' ); ?>
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
												: ( ( 'subscriber' === $k && ! isset( $membership_details['role'] ) ) ? 'selected="selected"' : '' );
											?>
											<option
												<?php echo $selected; ?>
												value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $role ); ?></option>
											<?php
										endforeach;
										?>
									</select>
								</div>
							</div>
						</div>
						<?php if ( ! UR_PRO_ACTIVE ) : ?>
						<!--									subscription fields container-->
						<div
							class="ur-membership-subscription-field-container <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? '' : 'ur-d-none'; ?>">
							<!--						trial section-->
							<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3"
								style="gap:20px">
								<div class="ur-label" style="width: 30%">
									<label class="ur-membership-trial-status"
											for="ur-membership-trial-status"><?php esc_html_e( 'Trial Period :', 'user-registration' ); ?></label>
								</div>
								<div class="ur-toggle-section m1-auto" style="width: 100%">
									<span class="user-registration-toggle-form">
										<input
											data-key-name="Trial Period"
											id="ur-membership-trial-status"
											type="checkbox"
											class="user-registration-switch__control hide-show-check enabled"
											name="ur_membership[trial]_status"
											style="width: 100%;"
											value="<?php echo isset( $membership_details['trial_status'] ) && 'on' === $membership_details['trial_status'] ? 'on' : 'off'; ?>"
											<?php echo isset( $membership_details['trial_status'] ) && 'on' === $membership_details['trial_status'] ? 'checked' : ''; ?>
										>
										<span class="slider round"></span>
									</span>
								</div>
							</div>
							<div class="trial-container <?php echo isset( $membership_details['trial_status'] ) && 'on' === $membership_details['trial_status'] ? '' : 'ur-d-none'; ?>">
								<div
									class="trial-container--wrapper ur-d-flex ur-mt-6 ur-align-items-center" style="gap:20px;">
									<div class="ur-label" style="width: 23%">
										<label class="ur-membership-trial-status"
												for="ur-membership-trial-duration"><?php esc_html_e( 'Trial Period Duration', 'user - registration' ); ?>
											<span style="color:red">*</span>
										</label>
									</div>
									<div class="ur-field ur-d-flex ur-align-items-center" style="width: 100%;">
										<input
											data-key-name="Trial Period Duration Value"
											value="<?php echo isset( $membership_details['trial_data'] ) ? esc_attr( $membership_details['trial_data']['value'] ) : 1; ?>"
											class=""
											type="number" name="ur_membership[trial]_value"
											autocomplete="off"
											id="ur-membership-trial-duration-value"
											min="1"
											style="width: 80%"
										>
										<select
											class="Trial Period Duration"
											id="ur-membership-trial-duration"
											name="ur_membership[trial]_duration">
											<option value="day"
												<?php echo isset( $membership_details['trial_data'] ) && 'day' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>
											>Day(s)
											</option>
											<option value="week"
												<?php echo isset( $membership_details['trial_data'] ) && 'week' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>
											>Week(s)
											</option>
											<option value="month"
												<?php echo isset( $membership_details['trial_data'] ) && 'month' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>
											>Month(s)
											</option>
											<option value="year"
												<?php echo isset( $membership_details['trial_data'] ) && 'year' === $membership_details['trial_data']['duration'] ? 'selected="selected"' : ''; ?>
											>Year(s)
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<!--						Membership Upgrade Action toggle-->
						<?php
						$is_upgrade_enabled = isset( $membership_details['upgrade_settings']['upgrade_action'] ) && true == $membership_details['upgrade_settings']['upgrade_action'];
						?>
						<div class="ur-membership-selection-container ur-d-flex ur-mt-2 ur-align-items-center"
							style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label class="ur-membership-enable-upgrade-action"
										for="ur-membership-upgrade-action"><?php esc_html_e( 'Upgrade Action :', 'user-registration' ); ?>
								</label>
							</div>
							<div class="ur-toggle-section m1-auto" style="width: 100%">
								<span class="user-registration-toggle-form">
									<input
										data-key-name="Upgrade Action"
										id="ur-membership-upgrade-action" type="checkbox"
										class="user-registration-switch__control hide-show-check enabled"
										<?php echo $is_upgrade_enabled ? 'checked' : ''; ?>
										name="ur_membership_upgrade_action"
										style="width: 100%; text-align: left">
									<span class="slider round"></span>
								</span>
							</div>
						</div>
						<div id="upgrade-settings-container" class="ur-membership-selection-container"
							style="<?php echo true === $is_upgrade_enabled ? '' : 'display: none'; ?>"
						>
							<!--						Membership Upgrade Path field-->
							<div class="ur-membership-input-container ur-d-flex ur-align-items-center"
								style="gap:20px;">
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
							</div>
							<!--						Membership Upgrade Path Type-->
							<div
								class="urm-upgrade-path-type-container ur-d-flex ur-mt-6 ur-align-items-center"
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
										<label class="ur-membership-upgrade-types"
												for="ur-membership-upgrade-type-full">
											<div
												class="ur-membership-type-title ur-d-flex ur-align-items-center ">
												<input
													data-key-name="<?php echo __( 'Upgrade Type', 'user-registration' ); ?>"
													id="ur-membership-upgrade-type-full"
													type="radio" value="full"
													name="ur_membership_upgrade_type"
													style="margin: 0"
													<?php echo ( ( isset( $membership_details['upgrade_settings']['upgrade_type'] ) && $membership_details['upgrade_settings']['upgrade_type'] == 'full' ) ) ? 'checked' : ( ! $is_editing ? 'checked' : '' ); ?>
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
											class="ur-membership-upgrade-types <?php echo ! UR_PRO_ACTIVE ? 'upgradable-type' : ''; ?>  <?php echo isset( $membership_details['type'] ) && $membership_details['type'] == 'free' ? 'ur-d-none' : ''; ?>"
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
													<?php echo ( ( isset( $membership_details['upgrade_settings']['upgrade_type'] ) && $membership_details['upgrade_settings']['upgrade_type'] == 'pro-rata' ) ) ? 'checked' : ''; ?>
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
						</div>

						<?php
							if ( UR_PRO_ACTIVE ):
						?>
							<!-- Sync Membership to email marketing addons. -->
							<div class="ur-membership-sync-to-email-marketing-addons">
								<div class="ur-membership-selection-container ur-d-flex ur-mt-2 ur-align-items-center"
									style="gap:20px;">
									<div class="ur-label" style="width: 30%">
										<label class="ur-membership-enable-email-marketing-sync-action"
												for="ur-membership-email-marketing-sync-action"><?php esc_html_e( 'Enable email marketing sync :', 'user-registration' ); ?>
										</label>
									</div>
									<div class="ur-toggle-section m1-auto" style="width: 100%">
										<span class="user-registration-toggle-form">

										<?php
											$email_marketing_sync_details = isset( $membership_details[ 'email_marketing_sync'] ) ? $membership_details[ 'email_marketing_sync'] : array();
											$is_email_marketing_sync      = ur_string_to_bool( isset( $email_marketing_sync_details[ 'is_enable' ] ) ? $email_marketing_sync_details[ 'is_enable' ] : '0' );
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

							<?php ur_render_email_marketing_sync_settings( $membership_details );
								endif;
							?>

						<!--								membership all payments-->
						<?php
						require __DIR__ . '/./Partials/membership-admin-payments.php'
						?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
