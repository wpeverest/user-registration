<?php
/**
 * Membership Create - Access Tab Content
 *
 * @var object $this Membership class instance
 * @var array $membership_rule_data Membership rule data
 * @var array $membership_condition_options Condition options
 * @var array $membership_localized_data Localized data
 */

?>
<div class="user-registration-card__body">
	<div id="ur-membership-access-section" class="urcr-membership-access-section"
		data-rule-data="<?php echo isset( $membership_rule_data ) ? esc_attr( wp_json_encode( $membership_rule_data ) ) : ''; ?>">
		<div class="urcr-rule-content-panel">
			<div class="urcr-content-group">
				<div class="urcr-rule-body ur-p-2">
					<div class="urcr-condition-row-parent">
						<div class="urcr-conditions-list">
							<?php if ( empty( $membership_id ) ) : ?>
								<label class="urcr-label-container">
									<span
										class="urcr-target-content-label"><?php esc_html_e( 'Access', 'user-registration' ); ?></span>
									<span class="user-registration-help-tip tooltipstered"
											data-tip="<?php esc_attr_e( 'Select content to give access to this plan.', 'user-registration' ); ?>"></span>
								</label>
							<?php endif; ?>

							<?php
							// Render conditions if rule data exists
							if ( isset( $membership_rule_data ) && $membership_rule_data &&
								isset( $membership_rule_data['logic_map'] ) &&
								isset( $membership_rule_data['logic_map']['conditions'] ) &&
								! empty( $membership_rule_data['logic_map']['conditions'] ) ) {
								$conditions = $membership_rule_data['logic_map']['conditions'];

								// Sort conditions to ensure membership is first
								usort(
									$conditions,
									function ( $a, $b ) {
										if ( isset( $a['type'] ) && $a['type'] === 'membership' ) {
											return - 1;
										}
										if ( isset( $b['type'] ) && $b['type'] === 'membership' ) {
											return 1;
										}

										return 0;
									}
								);

								// Render all conditions including the first membership condition
								// First condition (membership) should be non-editable
								foreach ( $conditions as $index => $condition ) {
									$first_condition     = ! empty( $conditions ) ? $conditions[0] : null;
									$is_first_membership = isset( $first_condition['type'] ) && $first_condition['type'] === 'membership' && $index === 0;
									$is_locked           = $is_first_membership;
									echo $this->render_condition_row( $condition, $membership_condition_options, $membership_localized_data, $is_locked );
								}
							} elseif ( ! empty( $membership_id ) && $membership_id > 0 ) {
								// If no conditions exist, show membership condition for the current membership
								// This applies to both free and pro users
								$membership_condition = array(
									'id'    => 'x' . time() . '_' . wp_rand(),
									'type'  => 'membership',
									'value' => array( $membership_id ),
								);
								echo $this->render_condition_row( $membership_condition, $membership_condition_options, $membership_localized_data, true );
							}
							?>
						</div>

						<div class="urcr-target-selection-section ur-d-flex ur-align-items-start">
							<div class="urcr-condition-value-input-wrapper urcr-access-content">
									<span
										class="urcr-access-control-button urcr-condition-value-input urcr-dropdown-button">
										<span
											class="urcr-dropdown-button-text"><?php esc_html_e( 'Access', 'user-registration' ); ?></span>
									</span>
							</div>

							<span class="urcr-arrow-icon" aria-hidden="true"></span>

							<div class="ur-d-flex ur-flex-column">
								<div class="urcr-target-type-group">
									<?php

									if ( isset( $membership_rule_data ) && $membership_rule_data && isset( $membership_rule_data['target_contents'] ) && ! empty( $membership_rule_data['target_contents'] ) ) {
										$targets = $membership_rule_data['target_contents'];
										foreach ( $targets as $target ) {
											echo $this->render_content_target( $target, $membership_localized_data );
										}
									}
									?>
								</div>

								<div class="urcr-content-dropdown-wrapper">
										<span role="button" tabindex="0" class="button urcr-add-content-button">
											<span class="dashicons dashicons-plus-alt2"></span>
											<?php esc_html_e( 'Content', 'user-registration' ); ?>
										</span>
									<div class="urcr-content-type-dropdown-menu urcr-dropdown-menu"></div>
								</div>
							</div>
						</div>
					</div>
					<!-- Add Condition Button -->
					<!-- hiding this for now can be added later in future updates.-->
					<?php if ( false ) : ?>
						<div class="urcr-buttons-wrapper">
							<span role="button" tabindex="0" class="button urcr-add-condition-button">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Condition', 'user-registration' ); ?>
							</span>
						</div>
					<?php endif; ?>
					<!-- Action Section -->
					<div class="urcr-action-section">
						<input type="hidden" id="urcr-membership-action-type" value="message"/>

						<?php
						$use_global_message = true;
						if ( isset( $membership_rule_data['actions'][0]['message'] ) && ! empty( trim( $membership_rule_data['actions'][0]['message'] ) ) ) {
							$use_global_message = false;
						}
						?>

						<div class="urcr-label-input-pair urcr-rule-action ur-align-items-center ur-form-group">
							<label class="urcr-label-container ur-col-4">
								<span
									class="urcr-target-content-label"><?php esc_html_e( 'Restriction Message', 'user-registration' ); ?></span>
							</label>
							<div class="urcr-input-container">
								<div class="urcr-checkbox-radio-group">
									<label
										class="urcr-checkbox-radio-option <?php echo $use_global_message ? 'is-checked' : ''; ?>">
										<input
											type="radio"
											name="urcr-membership-message-type"
											value="global"
											<?php checked( $use_global_message, true ); ?>
											class="urcr-checkbox-radio-input"
										/>
										<div class="urcr-checkbox-radio--content">
											<span class="urcr-checkbox-radio-label">
													<?php esc_html_e( 'Use Global Restriction Message', 'user-registration' ); ?>
											</span>
										</div>
									</label>
									<label
										class="urcr-checkbox-radio-option <?php echo ! $use_global_message ? 'is-checked' : ''; ?>">
										<input
											type="radio"
											name="urcr-membership-message-type"
											value="custom"
											<?php checked( $use_global_message, false ); ?>
											class="urcr-checkbox-radio-input"
										/>
										<div class="urcr-checkbox-radio--content">
											<span class="urcr-checkbox-radio-label">
												<?php esc_html_e( 'Custom Message', 'user-registration' ); ?>
											</span>
										</div>
									</label>
								</div>
							</div>
						</div>

						<div class="urcr-action-inputs">
							<div
								class="urcr-title-body-pair urcr-action-input-container urcrra-message-input-container <?php echo $use_global_message ? 'ur-d-none' : ''; ?>">
								<label class="urcr-label-container">
									<span
										class="urcr-target-content-label"><?php esc_html_e( '', 'user-registration' ); ?></span>
								</label>
								<div class="urcr-body">
									<?php
									$action_message = '';
									if ( !empty( $membership_rule_data['actions'][0]['message'] ) ) {
										$action_message = urldecode( $membership_rule_data['actions'][0]['message'] );
									} else {
										$action_message = '<h3>Membership Required</h3>
<p>This content is available to members only.</p>
<p>Sign up to unlock access or log in if you already have an account.</p>
<p>{{sign_up}} {{log_in}}</p>';
									}
									wp_editor(
										$action_message,
										'urcr-membership-action-message',
										array(
											'textarea_name' => 'urcr_action_message',
											'textarea_rows' => 30,
											'media_buttons' => true,
											'quicktags' => false,
											'teeny'     => false,
											'show-reset-content-button' => false,
											'show-ur-registration-form-button' => false,
											'tinymce'   => array(
												'toolbar1' => 'undo,redo,formatselect,fontselect,fontsizeselect,bold,italic,forecolor,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat',
												'statusbar' => false,
												'min_height' => 250,
												'plugins'  => 'wordpress,wpautoresize,wplink,wpdialogs,wptextpattern,wpview,colorpicker,textcolor,hr,charmap,link,fullscreen,lists',
											),
										)
									);
									?>
								</div>
							</div>

							<!-- Redirect URL Input -->
							<div
								class="urcr-title-body-pair urcr-action-input-container urcrra-redirect-input-container ">
								<label class="urcr-label-container">
									<span
										class="urcr-target-content-label"><?php esc_html_e( 'Redirection URL', 'user-registration' ); ?></span>
								</label>
								<div class="urcr-body">
									<input type="url" class="urcr-input urcr-action-redirect-url"
											value="<?php echo isset( $membership_rule_data['actions'][0]['redirect_url'] ) ? esc_attr( $membership_rule_data['actions'][0]['redirect_url'] ) : ''; ?>"
											placeholder="<?php esc_attr_e( 'Enter a URL to redirect to...', 'user-registration' ); ?>"/>
								</div>
							</div>

							<!-- Local Page Input -->
							<div
								class="urcr-title-body-pair urcr-action-input-container urcrra-redirect-to-local-page-input-container ">
								<label class="urcr-label-container">
									<span
										class="urcr-target-content-label"><?php esc_html_e( 'Redirect to a local page', 'user-registration' ); ?></span>
								</label>
								<div class="urcr-body">
									<select class="urcr-input urcr-action-local-page user-membership-enhanced-select2">
										<option
											value=""><?php esc_html_e( 'Select a page', 'user-registration' ); ?></option>
										<?php
										$pages         = isset( $membership_localized_data['pages'] ) ? $membership_localized_data['pages'] : array();
										$selected_page = isset( $membership_rule_data['actions'][0]['local_page'] ) ? $membership_rule_data['actions'][0]['local_page'] : '';
										foreach ( $pages as $page_id => $page_title ) {
											$selected = ( (string) $page_id === (string) $selected_page ) ? 'selected="selected"' : '';
											echo '<option value="' . esc_attr( $page_id ) . '" ' . $selected . '>' . esc_html( $page_title ) . '</option>';
										}
										?>
									</select>
								</div>
							</div>

							<!-- UR Form Input -->
							<div
								class="urcr-title-body-pair urcr-action-input-container urcrra-ur-form-input-container ">
								<label class="urcr-label-container">
									<span
										class="urcr-target-content-label"><?php esc_html_e( 'Display User Registration & Membership Form', 'user-registration' ); ?></span>
								</label>
								<div class="urcr-body">
									<select class="urcr-input urcr-action-ur-form user-membership-enhanced-select2">
										<option
											value=""><?php esc_html_e( 'Select a form', 'user-registration' ); ?></option>
										<?php
										$ur_forms      = isset( $membership_localized_data['ur_forms'] ) ? $membership_localized_data['ur_forms'] : array();
										$selected_form = isset( $membership_rule_data['actions'][0]['ur_form'] ) ? $membership_rule_data['actions'][0]['ur_form'] : '';
										if ( empty( $selected_form ) && isset( $membership_rule_data['actions'][0]['ur-form'] ) ) {
											$selected_form = $membership_rule_data['actions'][0]['ur-form'];
										}
										foreach ( $ur_forms as $form_id => $form_title ) {
											$selected = ( (string) $form_id === (string) $selected_form ) ? 'selected="selected"' : '';
											echo '<option value="' . esc_attr( $form_id ) . '" ' . $selected . '>' . esc_html( $form_title ) . '</option>';
										}
										?>
									</select>
								</div>
							</div>

							<!-- Shortcode Input -->
							<div
								class="urcr-title-body-pair urcr-action-input-container urcrra-shortcode-input-container ">
								<label class="urcr-label-container">
									<span
										class="urcr-target-content-label"><?php esc_html_e( 'Render a Shortcode', 'user-registration' ); ?></span>
								</label>
								<div class="urcr-body">
									<div class="urcrra-shortcode-input">
										<select
											class="urcr-input urcr-action-shortcode-tag user-membership-enhanced-select2 ur-mb-2">
											<option
												value=""><?php esc_html_e( 'Select shortcode', 'user-registration' ); ?></option>
											<?php
											$shortcodes   = isset( $membership_localized_data['shortcodes'] ) ? $membership_localized_data['shortcodes'] : array();
											$selected_tag = isset( $membership_rule_data['actions'][0]['shortcode']['tag'] ) ? $membership_rule_data['actions'][0]['shortcode']['tag'] : '';
											foreach ( $shortcodes as $tag => $tag_name ) {
												$selected = ( $tag === $selected_tag ) ? 'selected="selected"' : '';
												echo '<option value="' . esc_attr( $tag ) . '" ' . $selected . '>' . esc_html( $tag ) . '</option>';
											}
											?>
										</select>
										<input type="text" class="urcr-input urcr-action-shortcode-args"
												value="<?php echo isset( $membership_rule_data['actions'][0]['shortcode']['args'] ) ? esc_attr( $membership_rule_data['actions'][0]['shortcode']['args'] ) : ''; ?>"
												placeholder='<?php esc_attr_e( 'Enter shortcode arguments here. Eg: id="345"', 'user-registration' ); ?>'/>
									</div>
								</div>
							</div>
						</div>
					</div>


				</div>
			</div>
		</div>

		<!-- Hidden input to store rule data -->
		<input type="hidden" id="urcr-membership-access-rule-data" name="urcr_membership_access_rule_data" value="">
	</div>
</div>
