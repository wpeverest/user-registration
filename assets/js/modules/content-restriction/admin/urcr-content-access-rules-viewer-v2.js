/**
 * Content Access Rules Viewer JavaScript
 * Handles expand/collapse, settings panel, and interactions
 */
(function ($) {
	'use strict';

	var URCRRulesViewer = {
		init: function () {
			this.bindEvents();
			this.initTooltips();
		},

		bindEvents: function () {
			var self = this;

			// Expand/Collapse toggle
			$(document).on('click', '.urcr-expand-toggle', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var ruleId = $(this).data('rule-id');
				self.toggleRuleCard(ruleId);
			});

			// Settings icon click
			$(document).on('click', '.urcr-settings-icon', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var ruleId = $(this).data('rule-id');
				self.toggleSettingsPanel(ruleId);
			});

			// Menu toggle
			$(document).on('click', '.urcr-menu-toggle', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var ruleId = $(this).data('rule-id');
				self.toggleMenuDropdown(ruleId);
			});

			// Close menu when clicking outside
			$(document).on('click', function (e) {
				if (!$(e.target).closest('.urcr-rule-menu').length) {
					$('.urcr-menu-dropdown').removeClass('show');
				}
			});

			// Status toggle
			$(document).on('change', '.urcr-rule-status-toggle', function () {
				var ruleId = $(this).data('rule-id');
				var isEnabled = $(this).is(':checked');
				self.updateRuleStatus(ruleId, isEnabled);
			});

			// Save rule button
			$(document).on('click', '.urcr-save-rule-btn', function (e) {
				e.preventDefault();
				var ruleId = $(this).data('rule-id');
				self.saveRule(ruleId);
			});

			// Access control change
			$(document).on('change', '.urcr-access-control-select', function () {
				var ruleId = $(this).data('rule-id');
				// Mark as changed for save
				$('.urcr-save-rule-btn[data-rule-id="' + ruleId + '"]').prop('disabled', false);
			});

			// Redirect URL input change
			$(document).on('input', '.urcr-redirect-url-input', function () {
				var ruleId = $(this).data('rule-id');
				$('.urcr-save-rule-btn[data-rule-id="' + ruleId + '"]').prop('disabled', false);
			});

			// Save settings button
			$(document).on('click', '.urcr-save-settings-btn', function (e) {
				e.preventDefault();
				var ruleId = $(this).data('rule-id');
				self.saveSettings(ruleId);
			});
		},

		saveSettings: function (ruleId) {
			// This would need to extract data from the settings panel
			// For now, redirect to edit page
			var editUrl = urcr_viewer_data.edit_url || '';
			if (editUrl) {
				window.location.href = editUrl.replace('%RULE_ID%', ruleId);
			} else {
				window.location.href = 'admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule&post-id=' + ruleId;
			}
		},

		toggleRuleCard: function (ruleId) {
			var $cardBody = $('.urcr-rule-card-body[data-rule-id="' + ruleId + '"]');
			var $toggle = $('.urcr-expand-toggle[data-rule-id="' + ruleId + '"]');

			if ($cardBody.is(':visible')) {
				$cardBody.slideUp(300);
				$toggle.removeClass('active');
			} else {
				$cardBody.slideDown(300);
				$toggle.addClass('active');
			}
		},

		toggleSettingsPanel: function (ruleId) {
			var $settingsPanel = $('.urcr-rule-settings-panel[data-rule-id="' + ruleId + '"]');
			var $contentPanel = $('.urcr-rule-card-body[data-rule-id="' + ruleId + '"] .urcr-rule-content-panel');

			// Ensure card body is visible
			var $cardBody = $('.urcr-rule-card-body[data-rule-id="' + ruleId + '"]');
			if (!$cardBody.is(':visible')) {
				$cardBody.slideDown(300);
				$('.urcr-expand-toggle[data-rule-id="' + ruleId + '"]').addClass('active');
			}

			if ($settingsPanel.is(':visible')) {
				$settingsPanel.slideUp(300);
				$contentPanel.slideDown(300);
			} else {
				$contentPanel.slideUp(300);
				$settingsPanel.slideDown(300).addClass('show');
			}
		},

		toggleMenuDropdown: function (ruleId) {
			var $dropdown = $('.urcr-menu-dropdown[data-rule-id="' + ruleId + '"]');
			$('.urcr-menu-dropdown').not($dropdown).removeClass('show');
			$dropdown.toggleClass('show');
		},

		updateRuleStatus: function (ruleId, isEnabled) {
			// Use the existing AJAX handler
			$.ajax({
				url: urcr_viewer_data.ajax_url,
				type: 'POST',
				data: {
					action: 'urcr_enable_disable_access_rule',
					rule_id: ruleId,
					enabled: isEnabled ? 'true' : 'false',
					security: urcr_viewer_data.nonce
				},
				success: function (response) {
					if (response.success) {
						if (typeof UR_Snackbar !== 'undefined') {
							var snackbar = new UR_Snackbar();
							var message = response.data && response.data.message ? response.data.message : (isEnabled ? urcr_viewer_data.labels.rule_enabled : urcr_viewer_data.labels.rule_disabled);
							snackbar.show(message, 'success');
						}
					} else {
						if (typeof UR_Snackbar !== 'undefined') {
							var snackbar = new UR_Snackbar();
							snackbar.show(
								response.data && response.data.message ? response.data.message : urcr_viewer_data.labels.error_occurred,
								'error'
							);
						}
						// Revert toggle on error
						$('.urcr-rule-status-toggle[data-rule-id="' + ruleId + '"]').prop('checked', !isEnabled);
					}
				},
				error: function () {
					if (typeof UR_Snackbar !== 'undefined') {
						var snackbar = new UR_Snackbar();
						snackbar.show(urcr_viewer_data.labels.error_occurred, 'error');
					}
				}
			});
		},

		saveRule: function (ruleId) {
			var $btn = $('.urcr-save-rule-btn[data-rule-id="' + ruleId + '"]');
			var accessControl = $('.urcr-access-control-select[data-rule-id="' + ruleId + '"]').val();
			var redirectUrl = $('.urcr-redirect-url-input[data-rule-id="' + ruleId + '"]').val() || '';

			$btn.prop('disabled', true).text(urcr_viewer_data.labels.saving || 'Saving...');

			$.ajax({
				url: urcr_viewer_data.ajax_url,
				type: 'POST',
				data: {
					action: 'urcr_update_rule_from_viewer',
					rule_id: ruleId,
					access_control: accessControl,
					redirect_url: redirectUrl,
					security: urcr_viewer_data.nonce
				},
				success: function (response) {
					if (response.success) {
						if (typeof UR_Snackbar !== 'undefined') {
							var snackbar = new UR_Snackbar();
							snackbar.show(
								urcr_viewer_data.labels.rule_saved || 'Rule saved successfully',
								'success'
							);
						}
						$btn.text(urcr_viewer_data.labels.save || 'Save');
					} else {
						if (typeof UR_Snackbar !== 'undefined') {
							var snackbar = new UR_Snackbar();
							snackbar.show(
								response.data && response.data.message ? response.data.message : urcr_viewer_data.labels.error_occurred,
								'error'
							);
						}
						$btn.prop('disabled', false).text(urcr_viewer_data.labels.save || 'Save');
					}
				},
				error: function () {
					if (typeof UR_Snackbar !== 'undefined') {
						var snackbar = new UR_Snackbar();
						snackbar.show(urcr_viewer_data.labels.error_occurred, 'error');
					}
					$btn.prop('disabled', false).text(urcr_viewer_data.labels.save || 'Save');
				}
			});
		},

		initTooltips: function () {
			if (typeof $.fn.tipTip !== 'undefined') {
				$('.user-registration-help-tip').tipTip({
					attribute: 'data-tip',
					defaultPosition: 'top'
				});
			}
		}
	};

	// Initialize on document ready
	$(document).ready(function () {
		URCRRulesViewer.init();
	});

})(jQuery);

