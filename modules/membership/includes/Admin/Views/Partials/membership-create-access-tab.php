<?php
/**
 * Membership Create - Access Tab Content
 *
 * @var object $this Membership class instance
 * @var array  $membership_rule_data Membership rule data
 * @var array  $membership_condition_options Condition options
 * @var array  $membership_localized_data Localized data
 */
?>
<div class="user-registration-card user-registration-card--form-step">
	<div class="user-registration-card__body">
		<?php if ( isset( $membership_rule_data ) && $membership_rule_data ) : ?>
		<script type="text/javascript">
			window.urcrMembershipRuleData = <?php echo wp_json_encode( $membership_rule_data ); ?>;
		</script>
		<?php endif; ?>
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
							<div class="urcr-target-selection-section ur-d-flex ur-align-items-start ur-mt-3">
								<span class="urcr-arrow-icon" aria-hidden="true"></span>

								<div class="ur-d-flex ur-flex-column">
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

									<div class="urcr-content-dropdown-wrapper" style="position: relative;">
										<button type="button" class="button urcr-add-content-button">
											<span class="dashicons dashicons-plus-alt2"></span>
											<?php esc_html_e( 'Content', 'user-registration' ); ?>
										</button>
										<div class="urcr-content-type-dropdown-menu urcr-dropdown-menu" style="display: none;"></div>
									</div>
								</div>
							</div>
						</div>

						<!-- Add Condition Button -->
						<div class="urcr-buttons-wrapper" style="display: flex; gap: 10px; margin-top: 16px;">
							<button type="button" class="button urcr-add-condition-button">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Condition', 'user-registration' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Hidden input to store rule data -->
			<input type="hidden" id="urcr-membership-access-rule-data" name="urcr_membership_access_rule_data" value="">
		</div>
	</div>
</div>

