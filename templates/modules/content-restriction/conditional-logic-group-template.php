<!-- Wrapped with WP Widget UI -->
<div class="urcr-settings-widget postbox urcr-conditional-logic-item user-registration-card" data-store-id="{{{ID}}}"
	 data-type="group">
	<div class="urcr-settings-widget-header user-registration-card__header ur-border-0">
		<div class="hndle ui-sortable-handle ur-d-flex ur-align-items-center ur-border-0">
			<h4 class="urcr-widget-header-label ur-h4 ur-m-0"><?php esc_html_e( 'Sub Logic Group', 'user-registration' ); ?></h4>
			<div class="user-registration-button-group urcr-logic-gates-container ur-ml-2">
				<span
					class="urbg-item button button-tertiary urcr-logic-gate urcr-logic-gate-{{{ID}}} {{{logic_gate:OR}}}"
					data-value="OR"><?php esc_html_e( 'OR', 'user-registration' ); ?></span>
				<span
					class="urbg-item button button-tertiary urcr-logic-gate urcr-logic-gate-{{{ID}}} {{{logic_gate:AND}}}"
					data-value="AND"><?php esc_html_e( 'AND', 'user-registration' ); ?></span>
				<span
					class="urbg-item button button-tertiary urcr-logic-gate urcr-logic-gate-{{{ID}}} {{{logic_gate:NOT}}}"
					data-value="NOT"><?php esc_html_e( 'NOT', 'user-registration' ); ?></span>
			</div>
			<div class="ur-d-flex ur-ml-auto">
				<select
					class="button button-secondary urcr-add-new-conditional-logic-field urcr-constant-selection-enabled">
					<option class="urcr-logic-field-placeholder" selected hidden disabled>
						+ <?php esc_html_e( 'Add Field', 'user-registration' ); ?></option>
					<optgroup label="<?php esc_html_e( 'User Based', 'user-registration' ); ?>">
						<option value="roles"><?php esc_html_e( 'Roles', 'user-registration' ); ?></option>
						<option
							value="user_registered_date"><?php esc_html_e( 'User Registered Date', 'user-registration' ); ?></option>
						<option
							value="access_period"><?php esc_html_e( 'Period after Registration', 'user-registration' ); ?></option>
						<option value="user_state"><?php esc_html_e( 'User State', 'user-registration' ); ?></option>
						<?php if ( (function_exists('ur_check_module_activation')) && ur_check_module_activation('membership') ): ?>
							<option
								value="membership"><?php esc_html_e( 'Membership', 'user-registration' ); ?></option>
						<?php endif; ?>
						<?php do_action( 'user_registration_content_restriction_add_user_based_logic_field' ); ?>
					</optgroup>
					<optgroup label="<?php esc_html_e( 'User Assets Based', 'user-registration' ); ?>">
						<option
							value="email_domain"><?php esc_html_e( 'Email Domain', 'user-registration' ); ?></option>
						<option
							value="post_count"><?php esc_html_e( 'Minimum Public Posts Count', 'user-registration' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_html_e( 'Others', 'user-registration' ); ?>">
						<option
							value="capabilities"><?php esc_html_e( 'Capabilities', 'user-registration' ); ?></option>
						<option
							value="registration_source"><?php esc_html_e( 'User Registration Source', 'user-registration' ); ?></option>
						<option
							value="ur_form_field"><?php esc_html_e( 'UR Form Field', 'user-registration' ); ?></option>
						<option
							value="payment_status"><?php esc_html_e( 'Payment Status', 'user-registration' ); ?></option>
					</optgroup>
				</select>
				<button type="button" class="button button-secondary urcr-add-new-conditional-logic-group ur-mx-1">
					+ <?php esc_html_e( 'Add Group', 'user-registration' ); ?></button>
				<button type="button" title="Remove"
						class="button button-icon button-danger urcrcl-trash urcr-trash ur-mr-1">
					<span class="dashicons dashicons-trash"></span>
				</button>
				<button type="button" class="handlediv"><span class="toggle-indicator"></span></button>
			</div>
		</div>
	</div>
	<div class="inside urcr-cld-wrapper user-registration-card__body ur-border-top ur-pt-2"
		 id="urcr-cld-wrapper-{{{ID}}}">
		<div class="main urcr-conditional-logic-definitions" id="urcr-conditional-logic-definitions-{{{ID}}}">
			<span class="urcr-logic-group-rule-{{Class}} ucr-logic-group-rule" id="urcr-logic-group-rule-{{{ID}}}">
			<span class="urcr-sub-logic-group-rule-{{Class}}" id="urcr-sub-logic-group-rule-{{{ID}}}">{{Class}}</span>
			</span>

		</div>
	</div>
</div>

