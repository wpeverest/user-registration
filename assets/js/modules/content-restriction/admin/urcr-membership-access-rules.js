(function ($) {
	"use strict";

	var URCRMembershipAccess = {
		conditions: [],
		contentTargets: [],
		accessControl: "access",
		membershipId: 0,
		conditionCounter: 0,
		targetCounter: 0,
		ruleData: null,
		initialized: false,

		init: function () {
			var self = this;

			if (typeof urcr_membership_access_data === "undefined") {
				return;
			}

			if (urcr_membership_access_data.membership_id) {
				self.membershipId = parseInt(
					urcr_membership_access_data.membership_id,
					10
				);
			}

			var ruleData = null;

			if (
				typeof window.urcrMembershipRuleData !== "undefined" &&
				window.urcrMembershipRuleData
			) {
				ruleData = window.urcrMembershipRuleData;
			} else {
				var $section = $("#ur-membership-access-section");
				if ($section.length) {
					var dataAttr = $section.attr("data-rule-data");
					if (dataAttr) {
						ruleData = JSON.parse(dataAttr);
					}
				}
			}

			var $conditionsList = $(".urcr-conditions-list");
			var $targetsList = $(".urcr-target-type-group");
			var hasExistingConditions =
				$conditionsList.find(".urcr-condition-wrapper").length > 0;
			var hasExistingTargets =
				$targetsList.find(".urcr-target-item").length > 0;

			if (hasExistingConditions || hasExistingTargets) {
				self.syncFromExistingHTML();
			} else if (ruleData) {
				self.ruleData = ruleData;
				self.populateRuleData(ruleData);
			} else {
				self.initializeEmptyRule();
			}

			self.bindEvents();
			self.initSelect2();
			self.initActionSection();

			self.initialized = true;

			self.dripInit();
		},

		initializeEmptyRule: function () {
			var self = this;
			if (self.membershipId > 0) {
				self.addCondition("membership", true, [
					self.membershipId.toString()
				]);
			}
		},

		syncFromExistingHTML: function () {
			var self = this;

			$(".urcr-condition-wrapper").each(function () {
				var $wrapper = $(this);
				var conditionId = $wrapper.data("condition-id");
				var $fieldSelect = $wrapper.find(
					".urcr-condition-field-select"
				);
				var $valueInput = $wrapper.find(".urcr-condition-value-input");
				var type = $fieldSelect.val() || "roles";

				var inputType = "multiselect";

				var conditionOptions = self.getConditionOptions();
				var selectedOption = conditionOptions.find(function (opt) {
					return opt.value === type;
				});
				if (selectedOption) {
					inputType = selectedOption.type || "multiselect";
				}

				var value = "";
				if (inputType === "multiselect") {
					var valueData = $valueInput.attr("data-value");
					if (valueData) {
						try {
							value = JSON.parse(valueData);
						} catch (e) {
							value = [];
						}
					} else {
						value = [];
					}
				} else if (inputType === "checkbox") {
					value =
						$valueInput.find('input[type="radio"]:checked').val() ||
						"logged-in";
				} else if (
					inputType === "date" ||
					inputType === "number" ||
					inputType === "text"
				) {
					value = $valueInput.val() || "";
				} else if (inputType === "period") {
					var select =
						$valueInput.find('[data-period-part="select"]').val() ||
						"During";
					var input =
						$valueInput.find('[data-period-part="input"]').val() ||
						"";
					value = { select: select, input: input };
				}

				self.conditions.push({
					id: conditionId,
					type: type,
					value: value,
					isLocked: false
				});

				if (inputType === "multiselect") {
					self.initConditionSelect2(conditionId, inputType, value);
				}
			});

			$(".urcr-target-item").each(function () {
				var $target = $(this);
				var targetId = $target.data("target-id");
				var $label = $target.find(".urcr-target-type-label");
				var type = "";

				var labelText = $label.length
					? $label.text().toLowerCase()
					: "";
				if (labelText.indexOf("pages") !== -1) type = "pages";
				else if (labelText.indexOf("posts") !== -1) type = "posts";
				else if (labelText.indexOf("post type") !== -1)
					type = "post_types";
				else if (labelText.indexOf("taxonomy") !== -1)
					type = "taxonomy";
				else if (labelText.indexOf("whole site") !== -1)
					type = "whole_site";
				else {
					var $contentInput = $target.find(
						".urcr-content-target-input"
					);
					if ($contentInput.length) {
						type =
							$contentInput.data("content-type") ||
							$contentInput.data("field-type") ||
							"";
					}
					if (
						!type &&
						$target.find("span").length &&
						$target.text().indexOf("Whole Site") !== -1
					) {
						type = "whole_site";
					}
				}

				var value = "";
				if (type === "whole_site") {
					value = "whole_site";
				} else if (type === "taxonomy") {
					var taxonomy =
						$target.find(".urcr-taxonomy-select").val() || "";
					var $termSelect = $target.find(
						".urcr-content-target-input"
					);
					var termsData = $termSelect.attr("data-value");
					var terms = [];
					if (termsData) {
						try {
							terms = JSON.parse(termsData);
						} catch (e) {
							terms = [];
						}
					}
					value = { taxonomy: taxonomy, value: terms };
				} else {
					var $contentSelect = $target.find(
						".urcr-content-target-input"
					);
					var contentData = $contentSelect.attr("data-value");
					if (contentData) {
						try {
							value = JSON.parse(contentData);
						} catch (e) {
							value = [];
						}
					} else {
						value = [];
					}
				}

				self.contentTargets.push({
					id: targetId,
					type: type,
					value: value
				});

				if (type !== "whole_site") {
					setTimeout(function () {
						self.initContentTargetSelect2(targetId, type, value);
					}, 100);
				}
			});
		},

		populateRuleData: function (ruleData) {
			var self = this;

			$(".urcr-conditions-list").empty();
			$(".urcr-target-type-group").empty();
			self.conditions = [];
			self.contentTargets = [];

			if (ruleData.access_control) {
				self.accessControl = ruleData.access_control;
				self.updateAccessControlClass();
			} else {
				self.accessControl = "access";
				self.updateAccessControlClass();
			}

			if (
				ruleData.logic_map &&
				ruleData.logic_map.conditions &&
				ruleData.logic_map.conditions.length > 0
			) {
				var conditions = ruleData.logic_map.conditions;

				conditions.sort(function (a, b) {
					if (a.type === "membership") return -1;
					if (b.type === "membership") return 1;
					return 0;
				});

				conditions.forEach(function (condition, index) {
					var firstCondition = conditions[0];
					var isFirstMembership =
						firstCondition &&
						firstCondition.type === "membership" &&
						index === 0;
					var isLocked = isFirstMembership;
					var value = condition.value;

					if (Array.isArray(value)) {
						value = value;
					} else if (typeof value === "object" && value !== null) {
						value = value;
					} else {
						value = value || "";
					}

					self.addCondition(
						condition.type,
						isLocked,
						value,
						condition.id
					);
				});
			} else {
				if (self.membershipId > 0) {
					self.addCondition("membership", true, [
						self.membershipId.toString()
					]);
				}
			}

			if (
				ruleData.target_contents &&
				ruleData.target_contents.length > 0
			) {
				ruleData.target_contents.forEach(function (target) {
					var type = target.type;
					if (type === "wp_pages") type = "pages";
					if (type === "wp_posts") type = "posts";

					var value =
						target.value ||
						(type === "whole_site" ? "whole_site" : []);

					if (type === "taxonomy") {
						if (target.taxonomy) {
							value = {
								taxonomy: target.taxonomy,
								value: Array.isArray(target.value)
									? target.value
									: []
							};
						} else if (
							typeof target.value === "object" &&
							target.value !== null &&
							!Array.isArray(target.value)
						) {
							if (target.value.taxonomy) {
								value = {
									taxonomy: target.value.taxonomy,
									value: Array.isArray(target.value.value)
										? target.value.value
										: []
								};
							}
						}
					} else if (type !== "whole_site") {
						if (!Array.isArray(value)) {
							value = value ? [value] : [];
						}
					}

					self.addContentTarget(type, value, target.id);
				});
			}
		},

		bindEvents: function () {
			var self = this;

			$(document).on("click", ".urcr-add-condition-button", function (e) {
				e.preventDefault();
				self.addCondition("roles", false, "");
			});

			$(document).on(
				"keydown",
				".urcr-add-condition-button",
				function (e) {
					if (e.key === "Enter" || e.key === " ") {
						e.preventDefault();
						self.addCondition("roles", false, "");
					}
				}
			);

			$(document).on("click", ".urcr-condition-remove", function (e) {
				e.preventDefault();
				var $wrapper = $(this).closest(".urcr-condition-wrapper");
				var conditionId = $wrapper.data("condition-id");
				self.removeCondition(conditionId);
			});

			$(document).on(
				"change",
				".urcr-condition-field-select",
				function () {
					var $wrapper = $(this).closest(".urcr-condition-wrapper");
					var conditionId = $wrapper.data("condition-id");
					var newType = $(this).val();
					self.updateConditionType(conditionId, newType);
				}
			);

			$(document).on(
				"change",
				".urcr-condition-value-input",
				function () {
					var $wrapper = $(this).closest(".urcr-condition-wrapper");
					if ($wrapper.length) {
						var conditionId = $wrapper.data("condition-id");
						self.updateConditionValue(conditionId, $(this));
					}
				}
			);

			$(document).on(
				"change",
				".urcr-period-input-group input, .urcr-period-input-group select",
				function () {
					var $wrapper = $(this).closest(".urcr-condition-wrapper");
					if ($wrapper.length) {
						var conditionId = $wrapper.data("condition-id");
						var $periodContainer = $(this).closest(
							".urcr-period-input-group"
						);
						var select = $periodContainer
							.find('[data-period-part="select"]')
							.val();
						var input = $periodContainer
							.find('[data-period-part="input"]')
							.val();
						var periodValue = {
							select: select || "During",
							input: input || ""
						};

						var condition = self.conditions.find(function (c) {
							return c.id === conditionId;
						});
						if (condition) {
							condition.value = periodValue;
						}
					}
				}
			);

			$(document).on("click", ".urcr-add-content-button", function (e) {
				e.preventDefault();
				self.showContentTypeDropdown($(this));
			});

			$(document).on("click", ".urcr-target-remove", function (e) {
				e.preventDefault();
				var $target = $(this).closest(".urcr-target-item");
				var targetId = $target.data("target-id");
				self.removeContentTarget(targetId);
			});

			$(document).on("click", ".urcr-content-type-option", function (e) {
				e.preventDefault();
				// if (
				// 	$(this).hasClass("urcr-dropdown-option-disabled") ||
				// 	$(this).attr("aria-disabled") === "true"
				// ) {
				// 	return;
				// }
				var contentType = $(this).data("content-type");
				self.addContentTarget(contentType);
				$(".urcr-content-type-dropdown-menu")
					.removeClass("ur-d-flex")
					.addClass("ur-d-none");
			});

			$(document).on(
				"keydown",
				".urcr-content-type-option",
				function (e) {
					// if (
					// 	$(this).hasClass("urcr-dropdown-option-disabled") ||
					// 	$(this).attr("aria-disabled") === "true"
					// ) {
					// 	return;
					// }
					if (e.key === "Enter" || e.key === " ") {
						e.preventDefault();
						var contentType = $(this).data("content-type");
						self.addContentTarget(contentType);
						$(".urcr-content-type-dropdown-menu")
							.removeClass("ur-d-flex")
							.addClass("ur-d-none");
					}
				}
			);

			$(document).on("click", function (e) {
				if (
					!$(e.target).closest(".urcr-content-dropdown-wrapper")
						.length &&
					!$(e.target).closest(".urcr-add-content-button").length
				) {
					$(".urcr-content-type-dropdown-menu")
						.removeClass("ur-d-flex")
						.addClass("ur-d-none");
				}
			});

			$(document).on("change", ".urcr-action-type-select", function () {
				var actionType = $(this).val() || "message";
				self.handleActionTypeChange(actionType);
			});

			$(document).on(
				"change",
				'input[name="urcr-membership-message-type"]',
				function () {
					var messageType = $(this).val();
					var $messageContainer = $(
						".urcrra-message-input-container"
					);
					var $radioOptions = $(
						'input[name="urcr-membership-message-type"]'
					).closest(".urcr-checkbox-radio-option");

					$radioOptions.removeClass("is-checked");
					$(this)
						.closest(".urcr-checkbox-radio-option")
						.addClass("is-checked");

					if (messageType === "global") {
						$messageContainer
							.removeClass("ur-d-flex")
							.addClass("ur-d-none");
						$messageContainer.hide();
					} else if (messageType === "custom") {
						$messageContainer
							.removeClass("ur-d-none")
							.addClass("ur-d-flex");
						$messageContainer.show();

						// Check if editor value is empty and add default message
						var editorContent = "";
						var $editorField = $("#urcr-membership-action-message");

						if (
							typeof wp !== "undefined" &&
							wp.editor &&
							$editorField.length
						) {
							var editor =
								window.tinymce &&
								window.tinymce.get(
									"urcr-membership-action-message"
								);
							if (editor) {
								editorContent = editor.getContent();
							} else {
								editorContent = wp.editor.getContent(
									"urcr-membership-action-message"
								);
							}
						} else {
							editorContent = $editorField.val() || "";
						}

						// If editor is empty, set default message
						if (!editorContent || editorContent.trim() === "") {
							var defaultMessage = "";
							if (
								typeof urcr_membership_access_data !==
									"undefined" &&
								urcr_membership_access_data.membership_default_message
							) {
								defaultMessage =
									urcr_membership_access_data.membership_default_message;
							}

							if (defaultMessage) {
								setTimeout(function () {
									if ($editorField.length) {
										var editor =
											window.tinymce &&
											window.tinymce.get(
												"urcr-membership-action-message"
											);
										if (editor) {
											editor.setContent(defaultMessage);
										} else {
											wp.editor.setContent(
												"urcr-membership-action-message",
												defaultMessage
											);
										}
									} else {
										$editorField.val(defaultMessage);
									}
								}, 100);
							}
						}
					}
				}
			);
		},

		addCondition: function (type, isLocked, value, conditionId) {
			var self = this;

			if (!type) {
				type = "roles";
			}

			if (type === "membership") {
				return;
			}

			if (!conditionId) {
				conditionId = "x" + Date.now() + "_" + self.conditionCounter++;
			}

			var conditionOptions = self.getConditionOptions();
			if (!conditionOptions || conditionOptions.length === 0) {
				return;
			}

			var selectedOption = conditionOptions.find(function (opt) {
				return opt.value === type;
			});

			if (!selectedOption) {
				selectedOption = conditionOptions[0];
				type = selectedOption.value;
			}

			var inputType = selectedOption.type || "multiselect";
			var shouldLock = false;

			var conditionHtml = self.getConditionRowHtml(
				conditionId,
				type,
				selectedOption.label,
				inputType,
				value || "",
				shouldLock
			);

			var $conditionsList = $(".urcr-conditions-list");
			if ($conditionsList.length === 0) {
				return;
			}

			$conditionsList.append(conditionHtml);

			self.initConditionSelect2(conditionId, inputType, value);

			self.conditions.push({
				id: conditionId,
				type: type,
				value: value || "",
				isLocked: shouldLock
			});
		},

		getConditionRowHtml: function (
			id,
			type,
			label,
			inputType,
			value,
			isLocked
		) {
			var self = this;
			var conditionOptions = self.getConditionOptions();
			var removeButton = "";

			if (!isLocked) {
				removeButton =
					'<button type="button" class="button button-link-delete urcr-condition-remove" aria-label="Remove condition">' +
					'<span class="dashicons dashicons-no-alt"></span>' +
					"</button>";
			}

			var disabledAttr = isLocked ? " disabled" : "";
			var fieldSelect =
				'<select class="urcr-condition-field-select urcr-condition-value-input"' +
				disabledAttr +
				">";
			conditionOptions.forEach(function (option) {
				var selected = option.value === type ? "selected" : "";
				fieldSelect +=
					'<option value="' +
					option.value +
					'" ' +
					selected +
					">" +
					option.label +
					"</option>";
			});
			fieldSelect += "</select>";

			var valueInput = self.getConditionValueInputHtml(
				id,
				inputType,
				type,
				value,
				isLocked
			);

			return (
				'<div class="urcr-condition-wrapper" data-condition-id="' +
				id +
				'">' +
				'<div class="urcr-condition-row ur-d-flex ur-mt-2 ur-align-items-start">' +
				'<div class="urcr-condition-only ur-d-flex ur-align-items-start">' +
				'<div class="urcr-condition-selection-section ur-d-flex ur-align-items-center ur-g-4">' +
				'<div class="urcr-condition-field-name">' +
				fieldSelect +
				"</div>" +
				'<div class="urcr-condition-operator"><span>is</span></div>' +
				'<div class="urcr-condition-value">' +
				valueInput +
				"</div>" +
				"</div>" +
				"</div>" +
				"</div>" +
				removeButton +
				"</div>"
			);
		},

		getConditionValueInputHtml: function (
			id,
			inputType,
			fieldType,
			value,
			isLocked
		) {
			var self = this;
			var html = "";
			var disabledAttr = isLocked ? " disabled" : "";

			if (fieldType === "ur_form_field") {
				var formId = "";
				var formFields = [];
				if (value && typeof value === "object") {
					formId = value.form_id || "";
					formFields = value.form_fields || [];
				}
				var valueJson = value
					? JSON.stringify(value)
					: '{"form_id":"","form_fields":[]}';
				var valueAttr =
					' data-value="' + $("<div>").text(valueJson).html() + '"';
				var urForms = urcr_membership_access_data.ur_forms || {};

				html =
					'<div class="urcr-ur-form-field-condition" data-condition-id="' +
					id +
					'"' +
					valueAttr +
					">";
				html +=
					'<div class="urcr-form-selection ur-d-flex ur-align-items-center ur-g-4 ur-mb-2">';
				html +=
					'<select class="urcr-form-select components-select-control__input urcr-condition-value-input"' +
					disabledAttr +
					">";
				html += '<option value="">Select a form</option>';
				for (var formIdKey in urForms) {
					if (urForms.hasOwnProperty(formIdKey)) {
						var selected = formIdKey === formId ? "selected" : "";
						html +=
							'<option value="' +
							formIdKey +
							'" ' +
							selected +
							">" +
							urForms[formIdKey] +
							"</option>";
					}
				}
				html += "</select>";
				html += "</div>";
				html += '<div class="urcr-form-fields-list"></div>';
				html += "</div>";
			} else if (inputType === "multiselect") {
				html =
					'<select class="urcr-enhanced-select2 urcr-condition-value-input" multiple data-condition-id="' +
					id +
					'" data-field-type="' +
					fieldType +
					'" ' +
					disabledAttr +
					"></select>";
			} else if (inputType === "checkbox") {
				var checkedLoggedIn =
					value === "logged-in" ||
					value === "logged_in" ||
					value === ""
						? "checked"
						: "";
				var checkedLoggedOut =
					value === "logged-out" || value === "logged_out"
						? "checked"
						: "";
				html =
					'<div class="urcr-checkbox-radio-input">' +
					'<label><input type="radio" name="condition_' +
					id +
					'_user_state" value="logged-in" ' +
					checkedLoggedIn +
					" " +
					disabledAttr +
					"> " +
					(urcr_membership_access_data.labels.logged_in ||
						"Logged In") +
					"</label>" +
					'<label><input type="radio" name="condition_' +
					id +
					'_user_state" value="logged-out" ' +
					checkedLoggedOut +
					" " +
					disabledAttr +
					"> " +
					(urcr_membership_access_data.labels.logged_out ||
						"Logged Out") +
					"</label>" +
					"</div>";
			} else if (inputType === "date") {
				html =
					'<input type="date" class="urcr-condition-value-input" data-condition-id="' +
					id +
					'" data-field-type="' +
					fieldType +
					'" value="' +
					(value || "") +
					'" ' +
					disabledAttr +
					">";
			} else if (inputType === "period") {
				var periodSelect = "During";
				var periodInput = "";
				if (value && typeof value === "object") {
					periodSelect = value.select || "During";
					periodInput = value.input || "";
				} else if (value && typeof value === "object" && value.value) {
					periodInput = value.value || "";
					periodSelect = "During";
				}
				html =
					'<div class="urcr-period-input-group ur-d-flex ur-align-items-center" style="gap: 8px;">' +
					'<select class="urcr-period-select urcr-condition-value-input" data-condition-id="' +
					id +
					'" data-field-type="' +
					fieldType +
					'" data-period-part="select" ' +
					disabledAttr +
					">" +
					'<option value="During" ' +
					(periodSelect === "During" ? "selected" : "") +
					">During</option>" +
					'<option value="After" ' +
					(periodSelect === "After" ? "selected" : "") +
					">After</option>" +
					"</select>" +
					'<input type="number" class="urcr-period-number urcr-condition-value-input" data-condition-id="' +
					id +
					'" data-field-type="' +
					fieldType +
					'" data-period-part="input" value="' +
					periodInput +
					'" min="0" placeholder="Days" ' +
					disabledAttr +
					">" +
					"</div>";
			} else if (inputType === "number") {
				html =
					'<input type="number" class="urcr-condition-value-input" data-condition-id="' +
					id +
					'" data-field-type="' +
					fieldType +
					'" value="' +
					(value || "") +
					'" ' +
					disabledAttr +
					">";
			} else if (inputType === "text") {
				html =
					'<input type="text" class="urcr-condition-value-input" data-condition-id="' +
					id +
					'" data-field-type="' +
					fieldType +
					'" value="' +
					(value || "") +
					'" ' +
					disabledAttr +
					">";
			} else {
				html =
					'<input type="text" class="urcr-condition-value-input" data-condition-id="' +
					id +
					'" data-field-type="' +
					fieldType +
					'" value="' +
					(value || "") +
					'" ' +
					disabledAttr +
					">";
			}

			return html;
		},

		initConditionSelect2: function (conditionId, inputType, value) {
			var self = this;
			var $select = $(
				'.urcr-condition-wrapper[data-condition-id="' +
					conditionId +
					'"] .urcr-enhanced-select2'
			);

			if (
				inputType === "ur_form_field" ||
				$(
					'.urcr-condition-wrapper[data-condition-id="' +
						conditionId +
						'"] .urcr-ur-form-field-condition'
				).length
			) {
				self.initURFormFieldCondition(conditionId, value);
				return;
			}

			if ($select.length && inputType === "multiselect") {
				var fieldType = $select.data("field-type");
				var options = self.getSelect2Options(fieldType);

				if ($select.hasClass("select2-hidden-accessible")) {
					$select.select2("destroy");
				}

				setTimeout(function () {
					$select.select2({
						data: options,
						multiple: true,
						width: "100%"
					});

					var valueToSet = value;
					var valueData = $select.attr("data-value");
					if (valueData) {
						try {
							valueToSet = JSON.parse(valueData);
						} catch (e) {
							valueToSet = value;
						}
					}

					if (
						valueToSet &&
						Array.isArray(valueToSet) &&
						valueToSet.length > 0
					) {
						$select.val(valueToSet).trigger("change");
					} else if (valueToSet && !Array.isArray(valueToSet)) {
						$select.val([valueToSet]).trigger("change");
					}
				}, 100);
			}
		},

		initURFormFieldCondition: function (conditionId, value) {
			var self = this;
			var $container = $(
				'.urcr-condition-wrapper[data-condition-id="' +
					conditionId +
					'"] .urcr-ur-form-field-condition'
			);
			if (!$container.length) return;

			var formId = "";
			var formFields = [];
			var valueData = $container.attr("data-value");
			if (valueData) {
				try {
					var parsed = JSON.parse(valueData);
					formId = parsed.form_id || "";
					formFields = parsed.form_fields || [];
				} catch (e) {
					if (value && typeof value === "object") {
						formId = value.form_id || "";
						formFields = value.form_fields || [];
					}
				}
			} else if (value && typeof value === "object") {
				formId = value.form_id || "";
				formFields = value.form_fields || [];
			}

			var $formSelect = $container.find(".urcr-form-select");
			var $fieldsList = $container.find(".urcr-form-fields-list");

			if (formId) {
				$formSelect.val(formId);
				self.renderFormFields(
					$fieldsList,
					formId,
					formFields,
					conditionId
				);
			}

			$formSelect.off("change").on("change", function () {
				var selectedFormId = $(this).val();
				if (selectedFormId) {
					self.renderFormFields(
						$fieldsList,
						selectedFormId,
						[{ field_name: "", operator: "is", value: "" }],
						conditionId
					);
					self.updateURFormFieldValue(conditionId);
				} else {
					$fieldsList.empty();
					self.updateURFormFieldValue(conditionId);
				}
			});
		},

		renderFormFields: function (
			$container,
			formId,
			formFields,
			conditionId
		) {
			var self = this;
			$container.empty();

			if (
				!formId ||
				!urcr_membership_access_data.ur_form_data ||
				!urcr_membership_access_data.ur_form_data[formId]
			) {
				return;
			}

			var formFieldData =
				urcr_membership_access_data.ur_form_data[formId];
			var fieldOptions = [];
			for (var fieldName in formFieldData) {
				if (formFieldData.hasOwnProperty(fieldName)) {
					fieldOptions.push({
						value: fieldName,
						label: formFieldData[fieldName] || fieldName
					});
				}
			}

			if (formFields.length === 0) {
				formFields = [{ field_name: "", operator: "is", value: "" }];
			}

			formFields.forEach(function (field, index) {
				self.renderFormFieldRow(
					$container,
					field,
					index,
					fieldOptions,
					conditionId
				);
			});
		},

		renderFormFieldRow: function (
			$container,
			field,
			index,
			fieldOptions,
			conditionId
		) {
			var self = this;
			var fieldName = field.field_name || "";
			var operator = field.operator || "is";
			var fieldValue = field.value || "";

			var rowHtml =
				'<div class="urcr-form-field-row ur-d-flex ur-align-items-center ur-mb-2">' +
				'<div class="urcr-form-field-name">' +
				'<select class="components-select-control__input urcr-condition-value-input urcr-form-field-select">' +
				'<option value="">Select field</option>';
			fieldOptions.forEach(function (opt) {
				var selected = opt.value === fieldName ? "selected" : "";
				rowHtml +=
					'<option value="' +
					opt.value +
					'" ' +
					selected +
					">" +
					opt.label +
					"</option>";
			});
			rowHtml +=
				"</select></div>" +
				'<div class="urcr-form-field-operator">' +
				'<select class="components-select-control__input urcr-condition-value-input urcr-form-field-operator-select">' +
				'<option value="is"' +
				(operator === "is" ? " selected" : "") +
				">is</option>" +
				'<option value="is not"' +
				(operator === "is not" ? " selected" : "") +
				">is not</option>" +
				'<option value="empty"' +
				(operator === "empty" ? " selected" : "") +
				">empty</option>" +
				'<option value="not empty"' +
				(operator === "not empty" ? " selected" : "") +
				">not empty</option>" +
				"</select></div>";

			if (operator !== "empty" && operator !== "not empty") {
				rowHtml +=
					'<div class="urcr-form-field-value ur-flex-1">' +
					'<input type="text" class="components-text-control__input urcr-condition-value-input urcr-condition-value-text urcr-form-field-value-input" value="' +
					(fieldValue || "") +
					'" placeholder="Enter value">' +
					"</div>";
			}

			rowHtml +=
				'<button type="button" class="button urcr-add-field-button" aria-label="Add field">' +
				'<span class="dashicons dashicons-plus-alt2"></span></button>' +
				'<button type="button" class="button urcr-remove-field-button" aria-label="Remove field">' +
				'<span class="dashicons dashicons-minus"></span></button>' +
				"</div>";

			$container.append(rowHtml);

			var $row = $container.find(".urcr-form-field-row").last();
			$row.find(
				".urcr-form-field-select, .urcr-form-field-operator-select, .urcr-form-field-value-input"
			)
				.off("change input")
				.on("change input", function () {
					self.updateURFormFieldValue(conditionId);
				});

			$row.find(".urcr-form-field-operator-select")
				.off("change")
				.on("change", function () {
					var op = $(this).val();
					var $valueContainer = $row.find(".urcr-form-field-value");
					if (op === "empty" || op === "not empty") {
						$valueContainer.remove();
					} else if (!$valueContainer.length) {
						$row.find(".urcr-form-field-operator").after(
							'<div class="urcr-form-field-value ur-flex-1">' +
								'<input type="text" class="components-text-control__input urcr-condition-value-input urcr-condition-value-text urcr-form-field-value-input" placeholder="Enter value">' +
								"</div>"
						);
						$row.find(".urcr-form-field-value-input")
							.off("input")
							.on("input", function () {
								self.updateURFormFieldValue(conditionId);
							});
					}
					self.updateURFormFieldValue(conditionId);
				});

			$row.find(".urcr-add-field-button")
				.off("click")
				.on("click", function (e) {
					e.preventDefault();
					e.stopPropagation();
					var newField = {
						field_name: "",
						operator: "is",
						value: ""
					};
					var newIndex = $container.find(
						".urcr-form-field-row"
					).length;
					self.renderFormFieldRow(
						$container,
						newField,
						newIndex,
						fieldOptions,
						conditionId
					);
					self.updateURFormFieldValue(conditionId);
				});

			$row.find(".urcr-remove-field-button")
				.off("click")
				.on("click", function (e) {
					e.preventDefault();
					e.stopPropagation();
					var $allRows = $container.find(".urcr-form-field-row");
					var currentCount = $allRows.length;
					if (currentCount > 1 && !$(this).prop("disabled")) {
						$row.remove();
						var remainingRows = $container.find(
							".urcr-form-field-row"
						).length;
						$container
							.find(".urcr-remove-field-button")
							.prop("disabled", remainingRows <= 1);
						self.updateURFormFieldValue(conditionId);
					}
				});

			var $allRowsAfter = $container.find(".urcr-form-field-row");
			var shouldDisable = $allRowsAfter.length <= 1;
			$container
				.find(".urcr-remove-field-button")
				.prop("disabled", shouldDisable);
		},

		updateURFormFieldValue: function (conditionId) {
			var self = this;
			var $container = $(
				'.urcr-condition-wrapper[data-condition-id="' +
					conditionId +
					'"] .urcr-ur-form-field-condition'
			);
			if (!$container.length) return;

			var formId = $container.find(".urcr-form-select").val() || "";
			var formFields = [];

			$container.find(".urcr-form-field-row").each(function () {
				var fieldName =
					$(this).find(".urcr-form-field-select").val() || "";
				var operator =
					$(this).find(".urcr-form-field-operator-select").val() ||
					"is";
				var $valueInput = $(this).find(".urcr-form-field-value-input");
				var value =
					$valueInput.length &&
					operator !== "empty" &&
					operator !== "not empty"
						? $valueInput.val()
						: "";

				if (fieldName) {
					formFields.push({
						field_name: fieldName,
						operator: operator,
						value: value
					});
				}
			});

			var conditionValue = {
				form_id: formId,
				form_fields: formFields
			};

			var condition = self.conditions.find(function (c) {
				return c.id === conditionId;
			});
			if (condition) {
				condition.value = conditionValue;
			}
		},

		getSelect2Options: function (fieldType) {
			var self = this;
			var options = [];

			if (fieldType === "roles") {
				if (urcr_membership_access_data.wp_roles) {
					options = Object.keys(
						urcr_membership_access_data.wp_roles
					).map(function (key) {
						return {
							id: key,
							text: urcr_membership_access_data.wp_roles[key]
						};
					});
				}
			} else if (fieldType === "membership") {
				if (self.membershipId > 0) {
					var membershipTitle = "";
					if (
						urcr_membership_access_data.memberships &&
						urcr_membership_access_data.memberships[
							self.membershipId
						]
					) {
						membershipTitle =
							urcr_membership_access_data.memberships[
								self.membershipId
							];
					}
					options = [
						{
							id: self.membershipId.toString(),
							text: membershipTitle || "Current Membership"
						}
					];
				} else if (urcr_membership_access_data.memberships) {
					options = Object.keys(
						urcr_membership_access_data.memberships
					).map(function (key) {
						return {
							id: key,
							text: urcr_membership_access_data.memberships[key]
						};
					});
				}
			} else if (fieldType === "capabilities") {
				if (urcr_membership_access_data.wp_capabilities) {
					options = Object.keys(
						urcr_membership_access_data.wp_capabilities
					).map(function (key) {
						return {
							id: key,
							text: urcr_membership_access_data.wp_capabilities[
								key
							]
						};
					});
				}
			} else if (fieldType === "registration_source") {
				if (urcr_membership_access_data.registration_sources) {
					options = Object.keys(
						urcr_membership_access_data.registration_sources
					).map(function (key) {
						return {
							id: key,
							text: urcr_membership_access_data
								.registration_sources[key]
						};
					});
				}
			} else if (fieldType === "ur_form_field") {
				options = [];
			} else if (fieldType === "payment_status") {
				if (urcr_membership_access_data.payment_status) {
					options = Object.keys(
						urcr_membership_access_data.payment_status
					).map(function (key) {
						return {
							id: key,
							text: urcr_membership_access_data.payment_status[
								key
							]
						};
					});
				}
			}

			return options;
		},

		removeCondition: function (conditionId) {
			var self = this;
			$(
				'.urcr-condition-wrapper[data-condition-id="' +
					conditionId +
					'"]'
			).remove();
			self.conditions = self.conditions.filter(function (cond) {
				return cond.id !== conditionId;
			});
		},

		updateConditionType: function (conditionId, newType) {
			var self = this;
			var conditionOptions = self.getConditionOptions();
			var selectedOption = conditionOptions.find(function (opt) {
				return opt.value === newType;
			});

			if (selectedOption) {
				var $wrapper = $(
					'.urcr-condition-wrapper[data-condition-id="' +
						conditionId +
						'"]'
				);
				var $valueContainer = $wrapper.find(".urcr-condition-value");
				var newInputHtml = self.getConditionValueInputHtml(
					conditionId,
					selectedOption.type,
					newType,
					"",
					false
				);

				$valueContainer.html(newInputHtml);
				self.initConditionSelect2(conditionId, selectedOption.type, "");

				var condition = self.conditions.find(function (c) {
					return c.id === conditionId;
				});
				if (condition) {
					condition.type = newType;
					condition.value = "";
				}
			}
		},

		updateConditionValue: function (conditionId, $input) {
			var self = this;
			var value = "";

			var $container = $(
				'.urcr-condition-wrapper[data-condition-id="' +
					conditionId +
					'"] .urcr-ur-form-field-condition'
			);
			if ($container.length) {
				self.updateURFormFieldValue(conditionId);
				return;
			}

			if ($input.is("select")) {
				if ($input.is("[multiple]")) {
					value = $input.val() || [];
				} else {
					value = $input.val() || "";
				}
			} else if ($input.is('input[type="radio"]')) {
				var $wrapper = $input.closest(".urcr-condition-wrapper");
				value =
					$wrapper.find('input[type="radio"]:checked').val() || "";
			} else if (
				$input.is('input[type="number"]') ||
				$input.is('input[type="text"]') ||
				$input.is('input[type="date"]')
			) {
				value = $input.val() || "";
			} else {
				value = $input.val() || "";
			}

			var condition = self.conditions.find(function (c) {
				return c.id === conditionId;
			});
			if (condition) {
				condition.value = value;
			}
		},

		addContentTarget: function (type, value, targetId) {
			var self = this;

			if (!targetId) {
				targetId = "x" + Date.now() + "_" + self.targetCounter++;
			}

			var typeLabel = self.getContentTypeLabel(type);
			var targetHtml = self.getContentTargetHtml(
				targetId,
				type,
				typeLabel,
				value || ""
			);
			$(".urcr-target-type-group").append(targetHtml);
			var $newTarget = $(
				'.urcr-target-item[data-target-id="' + targetId + '"]'
			);
			self.dripInit($newTarget);
			self.initContentTargetSelect2(targetId, type, value);

			self.contentTargets.push({
				id: targetId,
				type: type,
				value: value || (type === "whole_site" ? "whole_site" : [])
			});
		},

		getContentTargetHtml: function (id, type, label, value) {
			var self = this;
			var inputHtml = "";
			var displayLabel = type === "whole_site" ? "Includes" : label;

			if (type === "whole_site") {
				var wholeSiteValue = label || "Whole Site";
				inputHtml =
					'<span data-content-type="whole_site" data-field-type="whole_site">' +
					wholeSiteValue +
					"</span>";
			} else if (type === "pages" || type === "posts") {
				inputHtml =
					'<select class="urcr-enhanced-select2 urcr-content-target-input" multiple data-target-id="' +
					id +
					'" data-content-type="' +
					type +
					'"></select>';
			} else if (type === "taxonomy") {
				inputHtml =
					'<div class="urcr-taxonomy-select-group">' +
					'<select class="urcr-taxonomy-select" data-target-id="' +
					id +
					'">' +
					'<option value="">Select Taxonomy</option>' +
					"</select>" +
					'<select class="urcr-enhanced-select2 urcr-content-target-input" multiple data-target-id="' +
					id +
					'" data-content-type="taxonomy"></select>' +
					"</div>";
			} else if (type === "custom_uri") {
				try {
					if (typeof value === "string") {
						value = JSON.parse(value);
					}
				} catch {
					value = ["", false];
				}
				inputHtml = `<div style="display:flex;align-items:center;gap:4px;flex:1" data-content-type="${type}" data-target-id="${id}">
					<input style="flex:1" type="text" class="components-text-control__input urcr-condition-value-input urcr-condition-value-text urcr-form-field-value-input" value="${value[0]}">
				</div>`;
			} else {
				inputHtml =
					'<select class="urcr-enhanced-select2 urcr-content-target-input" multiple data-target-id="' +
					id +
					'" data-content-type="' +
					type +
					'"></select>';
			}
			var contentDrip = "";

			if (
				![
					"whole_site",
					"masteriyo_courses",
					"whole_site",
					"masteriyo_courses",
					"menu_items",
					"files",
					"custom_uri"
				].includes(type) &&
				urcr_membership_access_data.is_pro &&
				urcr_membership_access_data.is_drip_content
			) {
				let drip = {
					activeType: "fixed_date",
					value: {
						fixed_date: { date: "", time: "" },
						days_after: { days: 0 }
					}
				};

				contentDrip += `
    <div class="urcr-membership-drip"
        data-active_type="${drip.activeType}"
        data-fixed_date_date="${drip.value.fixed_date.date}"
        data-fixed_date_time="${drip.value.fixed_date.time}"
        data-days_after_days="${drip.value.days_after.days}">

        <button type="button" class="urcr-drip__trigger">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
				<path d="M11.09 6.545a.91.91 0 1 1 1.82 0v4.893l3.133 1.567a.91.91 0 0 1-.813 1.626l-3.637-1.818a.91.91 0 0 1-.502-.813V6.545Z"/>
				<path d="M20.182 12a8.182 8.182 0 1 0-16.364 0 8.182 8.182 0 0 0 16.364 0ZM22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10Z"/>
			</svg> ${urcr_membership_access_data.drip_content_label.drip_this_content}
        </button>

        <div class="urcr-drip__popover" style="display:none;">
            <div class="urcr-drip__arrow"></div>

            <div class="urcr-drip__tabs">
                <div class="urcr-drip__tabList">
                    <button type="button" class="urcr-drip__tab" data-value="fixed_date">${urcr_membership_access_data.drip_content_label.fixed_date}</button>
                    <button type="button" class="urcr-drip__tab" data-value="days_after">${urcr_membership_access_data.drip_content_label.days_after}</button>
                </div>

                <div class="urcr-drip__panels">
                    <div class="urcr-drip__panel fixed_date-panel">
                        <input type="date" class="urcr-drip__input drip-date"
                            min="${urcr_membership_access_data.today}"
                            value="${drip.value.fixed_date.date}" />

                        <input type="time" class="urcr-drip__input drip-time"
                            value="${drip.value.fixed_date.time}" />
                    </div>

                    <div class="urcr-drip__panel days_after-panel" style="display:none;">
                        <input type="number" class="urcr-drip__input drip-days"
                            value="${drip.value.days_after.days}" min="0" />
                    </div>
                </div>
            </div>
        </div>
    </div>`;
			}

			return (
				'<div class="urcr-target-item" data-target-id="' +
				id +
				'">' +
				'<span class="urcr-target-type-label">' +
				displayLabel +
				":</span>" +
				inputHtml +
				contentDrip +
				'<button type="button" class="button-link urcr-target-remove" aria-label="Remove">' +
				'<span class="dashicons dashicons-no-alt"></span>' +
				"</button>" +
				"</div>"
			);
		},

		initContentTargetSelect2: function (targetId, type, value) {
			var self = this;
			var $select = $(
				'.urcr-target-item[data-target-id="' +
					targetId +
					'"] .urcr-content-target-input'
			);

			if (
				$select.length &&
				type !== "whole_site" &&
				type !== "custom_uri"
			) {
				var options = self.getContentTargetOptions(type);

				if ($select.hasClass("select2-hidden-accessible")) {
					$select.select2("destroy");
				}

				$select.select2({
					data: options,
					multiple: true,
					width: "100%"
				});

				setTimeout(function () {
					if (value && Array.isArray(value) && value.length > 0) {
						$select.val(value).trigger("change");
					} else if (
						value &&
						!Array.isArray(value) &&
						typeof value === "object" &&
						value.value
					) {
						if (
							Array.isArray(value.value) &&
							value.value.length > 0
						) {
							$select.val(value.value).trigger("change");
						}
					} else if (value && !Array.isArray(value)) {
						$select.val([value]).trigger("change");
					}
				}, 50);
			}

			if (type === "taxonomy") {
				self.initTaxonomySelect(targetId, value);
			}
		},

		initTaxonomySelect: function (targetId, value) {
			var self = this;
			var $taxonomySelect = $(
				'.urcr-target-item[data-target-id="' +
					targetId +
					'"] .urcr-taxonomy-select'
			);
			var $termSelect = $(
				'.urcr-target-item[data-target-id="' +
					targetId +
					'"] .urcr-content-target-input'
			);

			if (
				$taxonomySelect.length &&
				urcr_membership_access_data.taxonomies
			) {
				if ($taxonomySelect.find("option").length <= 1) {
					Object.keys(urcr_membership_access_data.taxonomies).forEach(
						function (taxKey) {
							$taxonomySelect.append(
								'<option value="' +
									taxKey +
									'">' +
									urcr_membership_access_data.taxonomies[
										taxKey
									] +
									"</option>"
							);
						}
					);
				}

				var taxonomy = null;
				var terms = [];

				if (value && value.taxonomy) {
					taxonomy = value.taxonomy;
					terms = value.value || [];
				} else {
					taxonomy = $taxonomySelect.val();

					var termsData = $termSelect.attr("data-value");
					if (termsData) {
						try {
							terms = JSON.parse(termsData);
						} catch (e) {
							terms = [];
						}
					}
				}

				if (taxonomy) {
					if ($taxonomySelect.val() !== taxonomy) {
						$taxonomySelect.val(taxonomy);
					}

					self.updateTaxonomyTerms(targetId, taxonomy, terms);
				}

				$taxonomySelect.off("change").on("change", function () {
					var selectedTaxonomy = $(this).val();
					self.updateTaxonomyTerms(targetId, selectedTaxonomy, []);
				});
			}
		},

		updateTaxonomyTerms: function (targetId, taxonomy, selectedTerms) {
			var self = this;
			var $termSelect = $(
				'.urcr-target-item[data-target-id="' +
					targetId +
					'"] .urcr-content-target-input'
			);

			if (
				$termSelect.length &&
				taxonomy &&
				urcr_membership_access_data.terms_list &&
				urcr_membership_access_data.terms_list[taxonomy]
			) {
				var terms = urcr_membership_access_data.terms_list[taxonomy];
				var options = Object.keys(terms).map(function (termId) {
					return { id: termId, text: terms[termId] };
				});

				if ($termSelect.hasClass("select2-hidden-accessible")) {
					$termSelect.select2("destroy");
				}

				$termSelect.empty().select2({
					data: options,
					multiple: true,
					width: "100%"
				});

				if (selectedTerms && selectedTerms.length > 0) {
					setTimeout(function () {
						var termIds = selectedTerms.map(function (term) {
							return String(term);
						});
						$termSelect.val(termIds).trigger("change");
					}, 50);
				}
			}
		},

		getContentTargetOptions: function (type) {
			var self = this;
			var options = [];

			if (
				typeof urcr_membership_access_data === "object" &&
				urcr_membership_access_data.hasOwnProperty(type)
			) {
				const data = urcr_membership_access_data[type];

				if (
					Array.isArray(data) &&
					data.length > 0 &&
					typeof data[0] === "object" &&
					data[0] !== null &&
					"group" in data[0] &&
					"options" in data[0] &&
					Array.isArray(data[0].options)
				) {
					options = data.map((x) => ({
						text: x.group,
						children: x.options.map((y) => ({
							id: y.value,
							text: y.label
						}))
					}));
				} else {
					options = Object.keys(
						urcr_membership_access_data[type]
					).map(function (key) {
						return {
							id: key,
							text: urcr_membership_access_data[type][key]
						};
					});
				}
			}

			return options;
		},

		removeContentTarget: function (targetId) {
			var self = this;
			var $target = $(
				'.urcr-target-item[data-target-id="' + targetId + '"]'
			);
			if ($target.length) {
				$target.removeClass("ur-d-flex").addClass("ur-d-none");
				$target.remove();
			}
			self.contentTargets = self.contentTargets.filter(function (target) {
				return target.id !== targetId;
			});
		},

		showContentTypeDropdown: function ($button) {
			var self = this;
			var existingTypes = self.contentTargets.map(function (t) {
				return t.type;
			});
			var isPro = urcr_membership_access_data.is_pro || false;
			var allContentTypes =
				urcr_membership_access_data.content_type_options;

			var contentTypes = isPro
				? allContentTypes
				: allContentTypes.filter(function (ct) {
						var filtercase =
							ct.value === "posts" ||
							ct.value === "pages" ||
							ct.value === "whole_site";
						if (
							urcr_membership_access_data.is_membership_module_enabled
						) {
							filtercase =
								filtercase || ct.value === "masteriyo_courses";
						}
						return filtercase;
					});
			var $wrapper = $button.closest(".urcr-content-dropdown-wrapper");

			if ($wrapper.length === 0) {
				$wrapper = $button
					.closest(".urcr-target-selection-section")
					.find(".urcr-content-dropdown-wrapper");
			}

			if ($wrapper.length === 0) {
				$wrapper = $button
					.closest(".urcr-target-selection-wrapper")
					.find(".urcr-content-dropdown-wrapper");
			}

			if ($wrapper.length === 0) {
				return;
			}

			var $dropdown = $wrapper.find(".urcr-content-type-dropdown-menu");

			if ($dropdown.length === 0) {
				$dropdown = $(
					'<div class="urcr-content-type-dropdown-menu urcr-dropdown-menu"></div>'
				);
				$wrapper.append($dropdown);
			}

			$dropdown.empty();
			contentTypes.forEach(function (ct) {
				var isDisabled = existingTypes.indexOf(ct.value) !== -1;
				var disabledClass = isDisabled
					? "urcr-dropdown-option-disabled"
					: "";
				var disabledAttr = isDisabled ? 'aria-disabled="true"' : "";
				var tabIndex = isDisabled ? "-1" : "0";
				$dropdown.append(
					'<span role="button" tabindex="' +
						tabIndex +
						'" class="urcr-dropdown-option urcr-content-type-option ' +
						'" data-content-type="' +
						ct.value +
						'" ' +
						">" +
						ct.label +
						"</span>"
				);
			});

			if ($dropdown.hasClass("ur-d-none")) {
				$dropdown.removeClass("ur-d-none").addClass("ur-d-flex");
			} else {
				$dropdown.removeClass("ur-d-flex").addClass("ur-d-none");
			}
		},

		updateAccessControlClass: function () {
			var self = this;
			var $wrapper = $(".urcr-condition-value-input-wrapper");
			$wrapper.removeClass("urcr-restrict-content");
			$wrapper.addClass("urcr-access-content");
		},

		initSelect2: function () {
			var self = this;
		},

		getConditionOptions: function () {
			var self = this;
			var allOptions =
				urcr_membership_access_data.condition_options || [];
			var isPro = urcr_membership_access_data.is_pro || false;

			if (!isPro) {
				allOptions = allOptions.filter(function (opt) {
					return (
						opt.value === "membership" ||
						opt.value === "roles" ||
						opt.value === "user_state"
					);
				});
			}

			allOptions = allOptions.filter(function (opt) {
				return opt.value !== "membership";
			});

			return allOptions;
		},

		getContentTypeLabel: function (type) {
			var contentTypeOptions =
				urcr_membership_access_data.content_type_options || [];

			for (var i = 0; i < contentTypeOptions.length; i++) {
				if (contentTypeOptions[i].value === type) {
					return contentTypeOptions[i].label || type;
				}
			}

			return type;
		},

		prepareRuleData: function () {
			var self = this;

			var $membershipIdInput = $("#ur-input-type-membership-name")
				.closest("form")
				.find('input[name="membership_id"]');
			if ($membershipIdInput.length && $membershipIdInput.val()) {
				self.membershipId = parseInt($membershipIdInput.val(), 10);
			}

			if (!self.membershipId && window.location.search) {
				var urlParams = new URLSearchParams(window.location.search);
				var postId = urlParams.get("post_id");
				if (postId) {
					self.membershipId = parseInt(postId, 10);
				}
			}

			var hasMembershipCondition = false;
			$(".urcr-condition-wrapper").each(function () {
				var type = $(this).find(".urcr-condition-field-select").val();
				if (type === "membership") {
					hasMembershipCondition = true;
					return false;
				}
			});

			var conditions = [];
			$(".urcr-condition-wrapper").each(function () {
				var $wrapper = $(this);
				var conditionId = $wrapper.data("condition-id");
				var type = $wrapper.find(".urcr-condition-field-select").val();
				var value = "";

				if (type === "ur_form_field") {
					var condition = self.conditions.find(function (c) {
						return c.id === conditionId;
					});
					if (
						condition &&
						condition.value &&
						typeof condition.value === "object"
					) {
						value = condition.value;
					} else {
						var $container = $wrapper.find(
							".urcr-ur-form-field-condition"
						);
						if ($container.length) {
							self.updateURFormFieldValue(conditionId);
							var updatedCondition = self.conditions.find(
								function (c) {
									return c.id === conditionId;
								}
							);
							if (updatedCondition && updatedCondition.value) {
								value = updatedCondition.value;
							} else {
								value = { form_id: "", form_fields: [] };
							}
						} else {
							value = { form_id: "", form_fields: [] };
						}
					}
				} else {
					var $select2 = $wrapper.find(".select2-hidden-accessible");
					if ($select2.length) {
						value = $select2.val() || [];
						if (!Array.isArray(value)) {
							value = value ? [value] : [];
						}
					} else {
						var $valueInput = $wrapper.find(
							".urcr-condition-value-input"
						);
						if ($valueInput.is("select[multiple]")) {
							var selectedValues = $valueInput.val();
							value = Array.isArray(selectedValues)
								? selectedValues
								: selectedValues
									? [selectedValues]
									: [];
						} else if ($valueInput.is('input[type="radio"]')) {
							value =
								$wrapper
									.find('input[type="radio"]:checked')
									.val() || "";
						} else if (
							$wrapper.find(".urcr-period-input-group").length
						) {
							var $periodContainer = $wrapper.find(
								".urcr-period-input-group"
							);
							var periodSelect = $periodContainer
								.find('[data-period-part="select"]')
								.val();
							var periodInput = $periodContainer
								.find('[data-period-part="input"]')
								.val();
							value = {
								select: periodSelect || "During",
								input: periodInput || ""
							};
						} else {
							value = $valueInput.val() || "";
						}
					}
				}

				if (type === "membership" && self.membershipId > 0) {
					value = [self.membershipId.toString()];
				}

				conditions.push({
					id: conditionId,
					type: type,
					value: value
				});
			});

			if (!hasMembershipCondition && self.membershipId > 0) {
				conditions.unshift({
					id: "x" + Date.now(),
					type: "membership",
					value: [self.membershipId.toString()]
				});
			}

			var targetContents = [];
			$(".urcr-target-item").each(function () {
				var $target = $(this);
				var targetId = $target.data("target-id");

				var type = "";
				var $elementWithType = $target.find("[data-content-type]");
				if ($elementWithType.length) {
					type = $elementWithType.first().data("content-type") || "";
				}

				if (!type) {
					if ($target.find(".urcr-taxonomy-select").length) {
						type = "taxonomy";
					} else if (
						$target.find(".urcr-content-target-input").length
					) {
						type =
							$target
								.find(".urcr-content-target-input")
								.data("content-type") || "";
					} else {
						var $label = $target.find(".urcr-target-type-label");
						if (
							$label.length &&
							$label.text().indexOf("Whole Site") !== -1
						) {
							type = "whole_site";
						}
					}
				}
				var value = "";
				switch (type) {
					case "whole_site":
						value = "whole_site";
						break;
					case "taxonomy":
						var taxonomy = $target
							.find(".urcr-taxonomy-select")
							.val();
						var $termSelect = $target.find(
							".urcr-content-target-input"
						);
						var terms = [];

						if (
							$termSelect.hasClass("select2-hidden-accessible") ||
							$termSelect.hasClass("urcr-enhanced-select2")
						) {
							terms = $termSelect.val() || [];
							if (!Array.isArray(terms)) {
								terms = terms ? [terms] : [];
							}
						} else {
							var termsData = $termSelect.attr("data-value");
							if (termsData) {
								try {
									terms = JSON.parse(termsData);
								} catch (e) {
									terms = [];
								}
							} else {
								terms = $termSelect.val() || [];
							}
						}

						if (!Array.isArray(terms)) {
							terms = terms ? [terms] : [];
						}
						value = {
							taxonomy: taxonomy,
							value: terms
						};
						break;
					case "custom_uri":
						var $customUriInput =
							$target.find('input[type="text"]');
						var $customUriCheckbox = $target.find(
							'input[type="checkbox"]'
						);
						value = [
							$customUriInput.val() || "",
							$customUriCheckbox.is(":checked")
						];
						break;
					case "pages":
					case "posts":
						var $contentSelect = $target.find(
							".urcr-content-target-input"
						);
						var selectedValues = [];

						if (
							$contentSelect.hasClass(
								"select2-hidden-accessible"
							) ||
							$contentSelect.hasClass("urcr-enhanced-select2")
						) {
							selectedValues = $contentSelect.val() || [];
							if (!Array.isArray(selectedValues)) {
								selectedValues = selectedValues
									? [selectedValues]
									: [];
							}
						} else {
							var contentData = $contentSelect.attr("data-value");
							if (contentData) {
								try {
									selectedValues = JSON.parse(contentData);
								} catch (e) {
									selectedValues = [];
								}
							} else {
								selectedValues = $contentSelect.val() || [];
							}
						}

						value = Array.isArray(selectedValues)
							? selectedValues
							: selectedValues
								? [selectedValues]
								: [];
						break;
					default:
						var $contentSelect = $target.find(
							".urcr-content-target-input"
						);
						var defaultValue = [];

						if (
							$contentSelect.hasClass(
								"select2-hidden-accessible"
							) ||
							$contentSelect.hasClass("urcr-enhanced-select2")
						) {
							defaultValue = $contentSelect.val() || [];
							if (!Array.isArray(defaultValue)) {
								defaultValue = defaultValue
									? [defaultValue]
									: [];
							}
						} else {
							var defaultData = $contentSelect.attr("data-value");
							if (defaultData) {
								try {
									defaultValue = JSON.parse(defaultData);
								} catch (e) {
									defaultValue = [];
								}
							} else {
								defaultValue = $contentSelect.val() || [];
							}
						}

						if (!Array.isArray(defaultValue)) {
							value = defaultValue ? [defaultValue] : [];
						} else {
							value = defaultValue;
						}
						break;
				}

				switch (type) {
					case "pages":
						type = "wp_pages";
						break;
					case "posts":
						type = "wp_posts";
						break;
				}

				var defaultDrip = {
					activeType: "fixed_date",
					value: {
						fixed_date: { date: "", time: "" },
						days_after: { days: 0 }
					}
				};

				var dripData = {};

				if (!["whole_site", "masteriyo_courses"].includes(type)) {
					var dripThisContent = $target.find(".urcr-membership-drip");

					if (dripThisContent) {
						dripData = {
							activeType: dripThisContent.data("active_type"),
							value: {
								fixed_date: {
									date: dripThisContent.data(
										"fixed_date_date"
									),
									time: dripThisContent.data(
										"fixed_date_time"
									)
								},
								days_after: {
									days: dripThisContent.data(
										"days_after_days"
									)
								}
							}
						};
					}
				}

				var targetData = {
					id: targetId,
					type: type,
					drip: dripData ?? defaultDrip
				};

				// Only add value field if type is not whole_site
				if (type !== "whole_site") {
					targetData.value = value;
				}

				targetContents.push(targetData);
			});

			var actions = self.getActionsFromForm();

			var ruleData = {
				title: "",
				access_rule_data: {
					enabled: true,
					access_control: self.accessControl || "access",
					logic_map: {
						type: "group",
						id: "x" + Date.now(),
						conditions: conditions,
						logic_gate: "AND"
					},
					target_contents: targetContents,
					actions: actions
				},
				rule_type: "membership",
				membership_id: self.membershipId
			};
			$("#urcr-membership-access-rule-data").val(
				JSON.stringify(ruleData)
			);

			window.urcrMembershipAccessRuleData = ruleData;

			return ruleData;
		},

		initActionSection: function () {
			var self = this;
			var $actionTypeSelect = $("#urcr-membership-action-type");

			$(".urcr-action-input-container")
				.removeClass("ur-d-flex")
				.addClass("ur-d-none");

			if ($actionTypeSelect.length) {
				$(
					".urcr-action-local-page, .urcr-action-ur-form, .urcr-action-shortcode-tag"
				).each(function () {
					if (!$(this).hasClass("select2-hidden-accessible")) {
						$(this).select2({
							width: "100%"
						});
					}
				});

				var currentType = $actionTypeSelect.val() || "message";
				self.handleActionTypeChange(currentType);
			}

			var $messageTypeRadio = $(
				'input[name="urcr-membership-message-type"]'
			);
			if ($messageTypeRadio.length) {
				var $checkedRadio = $messageTypeRadio.filter(":checked");

				if (!$checkedRadio.length) {
					$messageTypeRadio
						.filter('[value="global"]')
						.prop("checked", true);
					$checkedRadio =
						$messageTypeRadio.filter('[value="global"]');
				}

				$checkedRadio.trigger("change");
			} else {
				var $messageContainer = $(".urcrra-message-input-container");
				if ($messageContainer.length) {
					$messageContainer
						.removeClass("ur-d-flex")
						.addClass("ur-d-none");
					$messageContainer.hide();
				}
			}
		},

		handleActionTypeChange: function (actionType) {
			$(".urcr-action-input-container")
				.removeClass("ur-d-flex")
				.addClass("ur-d-none");

			if (!actionType) {
				actionType = "message";
			}
			var $container = null;
			switch (actionType) {
				case "message":
					$container = $(".urcrra-message-input-container");
					break;
				case "redirect":
					$container = $(".urcrra-redirect-input-container");
					break;
				case "local_page":
					$container = $(
						".urcrra-redirect-to-local-page-input-container"
					);
					break;
				case "ur-form":
					$container = $(".urcrra-ur-form-input-container");
					break;
				case "shortcode":
					$container = $(".urcrra-shortcode-input-container");
					break;
				default:
					$container = $(".urcrra-message-input-container");
					break;
			}

			if ($container && $container.length) {
				$container.removeClass("ur-d-none").addClass("ur-d-flex");
			}
		},

		getActionsFromForm: function () {
			var self = this;
			var $actionTypeSelect = $("#urcr-membership-action-type");

			if (!$actionTypeSelect.length) {
				return self.getDefaultActions();
			}

			var actionType = $actionTypeSelect.val() || "message";
			var actionId = "x" + Date.now();
			var actionData = {
				id: actionId,
				type: actionType,
				access_control: self.accessControl || "access"
			};

			switch (actionType) {
				case "message":
					actionData.label = "Show Message";
					var $messageTypeRadio = $(
						'input[name="urcr-membership-message-type"]:checked'
					);
					var messageType = $messageTypeRadio.length
						? $messageTypeRadio.val()
						: "global";
					var message = "";

					if (messageType === "global") {
						message = "";
					} else {
						if (
							typeof wp !== "undefined" &&
							wp.editor &&
							$("#urcr-membership-action-message").length
						) {
							var editor =
								window.tinymce &&
								window.tinymce.get(
									"urcr-membership-action-message"
								);
							if (editor) {
								message = editor.getContent();
							} else {
								message = wp.editor.getContent(
									"urcr-membership-action-message"
								);
							}
						} else {
							message =
								$("#urcr-membership-action-message").val() ||
								"";
						}
					}
					actionData.message = message
						? encodeURIComponent(message)
						: "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "redirect":
					actionData.label = "Redirect";
					actionData.message = "";
					actionData.redirect_url =
						$(".urcr-action-redirect-url").val() || "";
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "local_page":
					actionData.type = "redirect_to_local_page";
					actionData.label = "Redirect to a Local Page";
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page =
						$(".urcr-action-local-page").val() || "";
					actionData.ur_form = "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "ur-form":
					actionData.type = "ur-form";
					actionData.label = "Show UR Form";
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = $(".urcr-action-ur-form").val() || "";
					actionData.shortcode = { tag: "", args: "" };
					break;
				case "shortcode":
					actionData.label = "Render Shortcode";
					actionData.message = "";
					actionData.redirect_url = "";
					actionData.local_page = "";
					actionData.ur_form = "";
					actionData.shortcode = {
						tag: $(".urcr-action-shortcode-tag").val() || "",
						args: $(".urcr-action-shortcode-args").val() || ""
					};
					break;
			}

			return [actionData];
		},

		getDefaultActions: function () {
			return [
				{
					id: "x" + Date.now(),
					type: "message",
					label: "Show Message",
					message:
						"<h3>Membership Required</h3>\n<p>This content is available to members only.</p>\n<p>Sign up to unlock access or log in if you already have an account.</p>\n<p>{{sign_up}} {{log_in}}</p>",
					redirect_url: "",
					access_control: this.accessControl,
					local_page: "",
					ur_form: "",
					shortcode: {
						tag: "",
						args: ""
					}
				}
			];
		},
		dripInit: function (context) {
			var self = this;

			var $scope = context ? $(context) : $(document);

			$scope.find(".urcr-membership-drip").each(function () {
				var $wrap = $(this);

				// avoid re-initializing the same row again
				if ($wrap.data("drip-initialized")) {
					return;
				}

				var activeType = $wrap.data("active_type") || "fixed_date";
				setActiveType($wrap, activeType);

				$wrap.data("drip-initialized", true);
			});

			if (!self.dripEventsBound) {
				self.dripEventsBound = true;

				// open/close popover (per row)
				$(document).on(
					"click",
					".urcr-membership-drip .urcr-drip__trigger",
					function (e) {
						e.preventDefault();
						e.stopPropagation();

						$(".urcr-drip__popover").fadeOut();

						var $wrap = $(this).closest(".urcr-membership-drip");
						$wrap.find(".urcr-drip__popover").fadeToggle();
					}
				);

				// click outside closes all
				$(document).on("click", function (e) {
					if (!$(e.target).closest(".urcr-membership-drip").length) {
						$(".urcr-drip__popover").fadeOut();
					}
				});

				// tab click
				$(document).on(
					"click",
					".urcr-membership-drip .urcr-drip__tab",
					function (e) {
						e.preventDefault();

						var $wrap = $(this).closest(".urcr-membership-drip");
						var type = $(this).data("value");

						$wrap.data("active_type", type);
						$wrap.attr("data-active_type", type);

						setActiveType($wrap, type);
					}
				);

				// date change
				$(document).on(
					"change",
					".urcr-membership-drip .drip-date",
					function () {
						var $wrap = $(this).closest(".urcr-membership-drip");
						var v = $(this).val();

						$wrap.attr("data-fixed_date_date", v);
						$wrap.data("fixed_date_date", v);
					}
				);

				// time change
				$(document).on(
					"change",
					".urcr-membership-drip .drip-time",
					function () {
						var $wrap = $(this).closest(".urcr-membership-drip");
						var v = $(this).val();

						$wrap.attr("data-fixed_date_time", v);
						$wrap.data("fixed_date_time", v);
					}
				);

				// days change
				$(document).on(
					"change",
					".urcr-membership-drip .drip-days",
					function () {
						var $wrap = $(this).closest(".urcr-membership-drip");
						var v = $(this).val();

						$wrap.attr("data-days_after_days", v);
						$wrap.data("days_after_days", v);
					}
				);
			}

			function setActiveType($wrap, type) {
				$wrap.find(".urcr-drip__tab").removeClass("active");
				$wrap
					.find('.urcr-drip__tab[data-value="' + type + '"]')
					.addClass("active");

				if (type === "fixed_date") {
					$wrap.find(".fixed_date-panel").show();
					$wrap.find(".days_after-panel").hide();
				} else {
					$wrap.find(".fixed_date-panel").hide();
					$wrap.find(".days_after-panel").show();
				}
			}
		}
	};

	$(document).ready(function () {
		if ($("#ur-membership-access-section").length === 0) {
			setTimeout(function () {
				if ($("#ur-membership-access-section").length > 0) {
					URCRMembershipAccess.init();
				}
			}, 500);
		} else {
			URCRMembershipAccess.init();
		}
	});

	window.URCRMembershipAccess = URCRMembershipAccess;
})(jQuery);
