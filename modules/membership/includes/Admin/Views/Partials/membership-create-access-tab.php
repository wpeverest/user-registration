<?php
/**
 * Membership Create - Access Tab Content
 *
 * @var object $this Membership class instance
 * @var array  $membership_rule_data Membership rule data
 * @var array  $membership_condition_options Condition options
 * @var array  $membership_localized_data Localized data
 */
$conditions = $membership_rule_data['logic_map']['conditions'];


?>
	<div class="user-registration-card__body">
		<div id="ur-membership-access-section" class="urcr-membership-access-section"
			 data-rule-data="<?php echo isset( $membership_rule_data ) ? esc_attr( wp_json_encode( $membership_rule_data ) ) : ''; ?>">
			<div class="urcr-rule-content-panel">
				<div class="urcr-content-group">
					<div class="urcr-rule-body ur-p-2">
						<div class="urcr-condition-row-parent">
							<div class="urcr-conditions-list">
								<?php
								// Render conditions from PHP if rule data exists
								// Note: First membership condition is hidden but always present in data
								if ( isset( $membership_rule_data ) && $membership_rule_data &&
									 isset( $membership_rule_data['logic_map'] ) &&
									 isset( $membership_rule_data['logic_map']['conditions'] ) ) {
									$conditions = $membership_rule_data['logic_map']['conditions'];

									// Sort conditions to ensure membership is first
									usort( $conditions, function( $a, $b ) {
										if ( isset( $a['type'] ) && $a['type'] === 'membership' ) return -1;
										if ( isset( $b['type'] ) && $b['type'] === 'membership' ) return 1;
										return 0;
									} );

									// Skip the first condition if it's a membership condition (it's hidden)
									$first_condition = ! empty( $conditions ) ? $conditions[0] : null;
									$is_first_membership = isset( $first_condition['type'] ) && $first_condition['type'] === 'membership';

									foreach ( $conditions as $index => $condition ) {
										// Skip rendering the first membership condition (it's hidden)
										if ( $index === 0 && $is_first_membership ) {
											continue;
										}
										echo $this->render_condition_row( $condition, $membership_condition_options, $membership_localized_data );
									}
								}
								// Note: For new memberships, the membership condition is added in JavaScript but hidden
								?>
							</div>

							<!-- Access Control Section -->
							<div class="urcr-target-selection-section ur-mt-3">
								<span class="urcr-arrow-icon" aria-hidden="true"></span>

								<div class="urcr-target-selection-wrapper">
									<div class="urcr-target-type-group">
										<?php
										// Render content targets from PHP if rule data exists
										if ( isset( $membership_rule_data ) && $membership_rule_data &&
											 isset( $membership_rule_data['target_contents'] ) ) {
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
						<div class="urcr-buttons-wrapper">
							<span role="button" tabindex="0" class="button urcr-add-condition-button">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Condition', 'user-registration' ); ?>
							</span>
						</div>
						<!-- Action Section -->
						<div class="urcr-action-section ur-mt-3">
							<div class="urcr-label-input-pair urcr-rule-action urcr-align-items-center ">
								<label class="urcr-label-container">
									<span class="urcr-target-content-label"><?php esc_html_e( 'Action', 'user-registration' ); ?></span>
									<span class="user-registration-help-tip tooltipstered" data-tip="<?php esc_attr_e( 'Action to perform for restricting the specified contents', 'user-registration' ); ?>"></span>
								</label>
								<div class="urcr-input-container">
									<select class="urcr-action-type-select urcr-condition-value-input" id="urcr-membership-action-type">
										<?php
										$action_type_options = isset( $membership_localized_data['action_type_options'] ) ? $membership_localized_data['action_type_options'] : array();
										$selected_action = isset( $membership_rule_data['actions'][0]['type'] ) ? $membership_rule_data['actions'][0]['type'] : 'message';
										// Map backend type to frontend type
										if ( $selected_action === 'redirect_to_local_page' ) {
											$selected_action = 'local_page';
										}
										if ( $selected_action === 'ur_form' ) {
											$selected_action = 'ur-form';
										}

										if ( empty( $action_type_options ) ) {
											// Fallback if not in localized data
											$action_type_options = array(
												array( 'value' => 'message', 'label' => __( 'Show Message', 'user-registration' ) ),
												array( 'value' => 'redirect', 'label' => __( 'Redirect', 'user-registration' ) ),
												array( 'value' => 'local_page', 'label' => __( 'Redirect to a Local Page', 'user-registration' ) ),
												array( 'value' => 'ur-form', 'label' => __( 'Show UR Form', 'user-registration' ) ),
												array( 'value' => 'shortcode', 'label' => __( 'Render Shortcode', 'user-registration' ) ),
											);
										}

										foreach ( $action_type_options as $option ) {
											$value = isset( $option['value'] ) ? $option['value'] : '';
											$label = isset( $option['label'] ) ? $option['label'] : $value;
											$selected = ( $value === $selected_action ) ? 'selected="selected"' : '';
											echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
										}
										?>
									</select>
								</div>
							</div>

							<!-- Action Input Containers (will be shown/hidden by JS based on action type) -->
							<div class="urcr-action-inputs">
								<!-- Message Input -->
								<div class="urcr-title-body-pair urcr-action-input-container urcrra-message-input-container ">
									<label class="urcr-label-container">
										<span class="urcr-target-content-label"><?php esc_html_e( 'Redirection Message', 'user-registration' ); ?></span>
									</label>
									<div class="urcr-body">
										<?php
										$action_message = '';
										if ( isset( $membership_rule_data['actions'][0]['message'] ) ) {
											$action_message = urldecode( $membership_rule_data['actions'][0]['message'] );
										} else {
											$action_message = '<p>' . esc_html__( 'You do not have sufficient permission to access this content.', 'user-registration' ) . '</p>';
										}
										wp_editor(
											$action_message,
											'urcr-membership-action-message',
											array(
												'textarea_name' => 'urcr_action_message',
												'textarea_rows' => 10,
												'media_buttons' => true,
												'quicktags'     => false,
												'teeny'         => false,
												'tinymce'       => array(
													'toolbar1'    => 'undo,redo,formatselect,fontselect,fontsizeselect,bold,italic,forecolor,alignleft,aligncenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat',
													'statusbar'   => false,
													'plugins'     => 'wordpress,wpautoresize,wplink,wpdialogs,wptextpattern,wpview,colorpicker,textcolor,hr,charmap,link,fullscreen,lists',
												),
											)
										);
										?>
									</div>
								</div>

								<!-- Redirect URL Input -->
								<div class="urcr-title-body-pair urcr-action-input-container urcrra-redirect-input-container ">
									<label class="urcr-label-container">
										<span class="urcr-target-content-label"><?php esc_html_e( 'Redirection URL', 'user-registration' ); ?></span>
									</label>
									<div class="urcr-body">
										<input type="url" class="urcr-input urcr-action-redirect-url"
											   value="<?php echo isset( $membership_rule_data['actions'][0]['redirect_url'] ) ? esc_attr( $membership_rule_data['actions'][0]['redirect_url'] ) : ''; ?>"
											   placeholder="<?php esc_attr_e( 'Enter a URL to redirect to...', 'user-registration' ); ?>" />
									</div>
								</div>

								<!-- Local Page Input -->
								<div class="urcr-title-body-pair urcr-action-input-container urcrra-redirect-to-local-page-input-container ">
									<label class="urcr-label-container">
										<span class="urcr-target-content-label"><?php esc_html_e( 'Redirect to a local page', 'user-registration' ); ?></span>
									</label>
									<div class="urcr-body">
										<select class="urcr-input urcr-action-local-page user-membership-enhanced-select2">
											<option value=""><?php esc_html_e( 'Select a page', 'user-registration' ); ?></option>
											<?php
											$pages = isset( $membership_localized_data['pages'] ) ? $membership_localized_data['pages'] : array();
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
								<div class="urcr-title-body-pair urcr-action-input-container urcrra-ur-form-input-container ">
									<label class="urcr-label-container">
										<span class="urcr-target-content-label"><?php esc_html_e( 'Display User Registration Form', 'user-registration' ); ?></span>
									</label>
									<div class="urcr-body">
										<select class="urcr-input urcr-action-ur-form user-membership-enhanced-select2">
											<option value=""><?php esc_html_e( 'Select a form', 'user-registration' ); ?></option>
											<?php
											$ur_forms = isset( $membership_localized_data['ur_forms'] ) ? $membership_localized_data['ur_forms'] : array();
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
								<div class="urcr-title-body-pair urcr-action-input-container urcrra-shortcode-input-container ">
									<label class="urcr-label-container">
										<span class="urcr-target-content-label"><?php esc_html_e( 'Render a Shortcode', 'user-registration' ); ?></span>
									</label>
									<div class="urcr-body">
										<div class="urcrra-shortcode-input">
											<select class="urcr-input urcr-action-shortcode-tag user-membership-enhanced-select2 ur-mb-2">
												<option value=""><?php esc_html_e( 'Select shortcode', 'user-registration' ); ?></option>
												<?php
												$shortcodes = isset( $membership_localized_data['shortcodes'] ) ? $membership_localized_data['shortcodes'] : array();
												$selected_tag = isset( $membership_rule_data['actions'][0]['shortcode']['tag'] ) ? $membership_rule_data['actions'][0]['shortcode']['tag'] : '';
												foreach ( $shortcodes as $tag => $tag_name ) {
													$selected = ( $tag === $selected_tag ) ? 'selected="selected"' : '';
													echo '<option value="' . esc_attr( $tag ) . '" ' . $selected . '>' . esc_html( $tag ) . '</option>';
												}
												?>
											</select>
											<input type="text" class="urcr-input urcr-action-shortcode-args"
												   value="<?php echo isset( $membership_rule_data['actions'][0]['shortcode']['args'] ) ? esc_attr( $membership_rule_data['actions'][0]['shortcode']['args'] ) : ''; ?>"
												   placeholder='<?php esc_attr_e( 'Enter shortcode arguments here. Eg: id="345"', 'user-registration' ); ?>' />
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

