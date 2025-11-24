/**
 * UserRegistrationContentRestriction Content Access Rule Creator JS
 * global urcr_localized_data
 */
(function ($, urcr_data, DEBUG) {
	var snackbar = null;

	// Initialize snackbar library.
	if (UR_Snackbar) {
		snackbar = new UR_Snackbar();
	} else if (DEBUG) {
		console.warn(
			'URCR: "UR_Snackbar" is not defined. Maybe there was a problem while enqueuing the script.'
		);
	}

	// On document ready.
	$(function () {
		// Initialize controllers.
		urcr_access_rule_creator.init();
		urcr_content_targets_creator.init();
		urcr_rule_action_controller.init();
		ux_controls.init();

		// Initialize widgets.
		urcr_utils.init_all_flatpickr();
		urcr_utils.init_all_tooltips();

		// Render access rule data.
		urcr_renderer.render_access_rule();

		// Enable save buttons.
		urcr_utils.toggleSaveButtons(false);

		// Focus on the title editor input.
		$(".user-registration-editable-title__icon").trigger("click");
		$(".user-registration-editable-title__input").trigger("select");

		// Save/Publish button handler.
		$(document.body).on("click", ".urcr-save-rule", function (e) {
			e.preventDefault();
			e.stopPropagation();

			if (urcr_data.rule_id && "" !== urcr_data.rule_id) {
				urcr_api.save_rule();
			} else {
				urcr_api.create_rule();
			}
		});

		// Save as Draft button handler.
		$(document.body).on("click", ".urcr-save-rule-as-draft", function (e) {
			e.preventDefault();
			e.stopPropagation();

			urcr_api.save_rule_as_draft();
		});

		// Disable form submit on 'Enter' key hit.
		$(document.body).on(
			"submit",
			".urcr-content-access-rule-creator-form",
			function (e) {
				e.preventDefault();
				e.stopPropagation();
			}
		);

		// Opens up a sweetalert popup to create content rules.
		$(document.body).on("click", ".page-title-action", function (e) {
			e.preventDefault();
			var icon =
				'<i class="dashicons dashicons-plus-alt" style="color: #2778c4; border-color: #2778c4;"></i>';
			Swal.fire({
				customClass:
					"user-registration-swal2-modal user-registration-swal2-modal--center",
				title: icon + "</br>" + urcr_data.labels.add_new,
				confirmButtonText: urcr_data.labels.confirm_text,
				allowOutsideClick: false,
				showCancelButton: true,
				cancelButtonText: urcr_data.labels.cancel_text,
				html:
					'<label for="select-box">' +
					urcr_data.labels.content_rule_name +
					'</label><input id="content_rule_name" class="swal2-input" placeholder="Give it a name">' +
					'<label for="select-box">' +
					urcr_data.labels.access_control +
					"</label>" +
					'<select id="access-control" name="access-control" class="swal2-input">' +
					'<option value="access">' +
					urcr_data.labels.access +
					"</option>" +
					'<option value="restrict">' +
					urcr_data.labels.restrict +
					"</option>",
				inputAttributes: {
					autocapitalize: "off",
				},
				inputPlaceholder: urcr_data.labels.content_rule_name,
			}).then(function (result) {
				if (result.value) {
					var contentName = $("#content_rule_name").val();
					var accessControlValue = $("#access-control").val();
					var data = {
						action: "urcr_create_content_rules",
						security: urcr_data._nonce,
						contentName: contentName,
						accessControlValue: accessControlValue,
						url: urcr_data.content_rule_url,
					};
					$.ajax({
						url: urcr_data.ajax_url,
						data: data,
						type: "POST",
						success: function (response) {
							if (response.success) {
								window.location.href = response.data.redirect;
							}
						},
					});
				}
			});
		});

		$(document).on(
			"click",
			".urcr-logic-gates-container span",
			function (e) {
				e.preventDefault();
				var logicGateValue = $(this).attr("data-value");
				var classAttr = $(this).attr("class");
				var regex = /x\d+/g;
				var match = classAttr.match(regex);
				if (match) {
					var value = match[0].substring(1);
					if (
						$("#urcr-logic-group-rule-x" + value).next(
							".ur-form-group"
						).length > 0
					) {
						$("#urcr-logic-group-rule-x" + value)
							.removeAttr("class")
							.attr(
								"class",
								"urcr-logic-group-rule-" + logicGateValue
							);
						$("#urcr-sub-logic-group-rule-x" + value)
							.removeAttr("class")
							.attr(
								"class",
								"urcr-sub-logic-group-rule-" + logicGateValue
							);

						$("#urcr-sub-logic-group-rule-x" + value).text(
							logicGateValue
						);
					}
				}
			}
		);

		$(document).ready(function () {
			$(".urcr-conditional-logic-definitions").each(function () {
				var $this = $(this);
				var Id = $this.attr("id");
				if ($("#" + Id).find(".ur-form-group").length <= 0) {
					$("#" + Id)
						.find(".ucr-logic-group-rule span")
						.text("");
					$("#" + Id).before(
						'<p class="urcr-conditional-logic-warning"><b>Warning:</b> Please add the condition logic field</p>'
					);
					$("#" + Id)
						.find(".ucr-logic-group-rule")
						.children("span")
						.removeAttr("Class");
					$("#" + Id)
						.find(".ucr-logic-group-rule")
						.removeAttr("Class");
				}
			});
		});

		// Trigger: All intializations have been performed.
		$(document.body).trigger("urcr_initializations_complete");

		$(document.body).on("change", ".urcr-enable-access-rule", function () {
			var $this = $(this),
				rule_id = $this.data("rule-id"),
				enabled = $this.is(":checked");

			var data = {
				action: "urcr_enable_disable_access_rule",
				security: urcr_localized_data._nonce,
				rule_id: rule_id,
				enabled: enabled,
			};

			$this.closest(".column-status").css({
				display: "flex",
				"align-items": "center",
			});
			urcr_utils.append_spinner($this);

			$.ajax({
				url: urcr_localized_data.ajax_url,
				type: "POST",
				data: data,
				success: function (response) {
					if (response.success) {
						urcr_utils.show_success_message(response.data.message);
					} else {
						urcr_utils.show_failure_message(response.data.message);
					}
				},
				complete: function () {
					urcr_utils.remove_spinner($this);
				},
			});
		});
	});

	// Utility-like functions to avoid code repetition.
	var urcr_utils = {
		if_empty: function (value, _default) {
			if (null === value || undefined === value || "" === value) {
				return _default;
			}
			return value;
		},

		if_not_object: function (value, _default) {
			if (
				null === value ||
				undefined === value ||
				"object" !== typeof value
			) {
				return _default;
			}
			return value;
		},

		if_not_json_object: function (value, _default) {
			if (
				null === value ||
				undefined === value ||
				Array.isArray(value) ||
				"object" !== typeof value
			) {
				return _default;
			}
			return value;
		},

		if_not_array: function (value, _default) {
			if (
				null === value ||
				undefined === value ||
				!Array.isArray(value)
			) {
				return _default;
			}
			return value;
		},

		/**
		 * Initialize all tooltip elements.
		 */
		init_all_tooltips: function () {
			var tooltipster_args = {
				theme: "tooltipster-borderless",
				maxWidth: 200,
				multiple: true,
				interactive: true,
				position: "bottom",
				contentAsHTML: true,
				functionInit: function (instance, helper) {
					var $origin = jQuery(helper.origin),
						dataTip = $origin.attr("data-tip");

					if (dataTip) {
						instance.content(dataTip);
					}
				},
			};
			$(".user-registration-help-tip").tooltipster(tooltipster_args);
		},

		/**
		 * Initialize all enhanced select elements.
		 */
		init_all_enhanced_select: function () {
			var select2_changed_flag_up = false;

			$(".urcr-enhanced-select2").each(function () {
				var select2_class = $(this).data("select2_class");

				$(this)
					.select2({
						containerCssClass: select2_class,
					})
					.on("select2:selecting", function () {
						select2_changed_flag_up = true;
					})
					.on("select2:unselecting", function () {
						select2_changed_flag_up = true;
					})
					.on("select2:closing", function () {
						// Prevent closing only if user has just selected an option.
						if (select2_changed_flag_up && this.multiple) {
							select2_changed_flag_up = false;
							return false;
						}
					});
			});
		},

		/**
		 * Initialize all flatpickr input elements.
		 */
		init_all_flatpickr: function () {
			$(".urcr-flatpickr").each(function () {
				urcr_utils.init_a_flatpickr($(this));
			});
		},

		/**
		 * Initialize a single flatpickr input element.
		 *
		 * @param {jQuery} $element Input element to initialize as flatpickr.
		 */
		init_a_flatpickr: function ($element) {
			if ("string" === typeof $element && "" !== $element) {
				$element = $($element);
			}

			var mode = $element.data("mode"),
				enable_time = true === $element.data("enable-time"),
				no_calendar = true === $element.data("no-calendar");
			value = $element.val();

			$element.flatpickr({
				mode: mode,
				enableTime: enable_time,
				noCalender: no_calendar,
				defaultDate: value ? value : "",
			});
		},

		create_input_tag: function (args) {
			// var html = '<div class="' + args.divClassName + '">';
			var html =
				'<input name="' +
				args.id +
				'" type="text" class="' +
				args.inputClassName +
				'" value="' +
				args.value +
				'" style="' +
				args.style +
				'">';
			// html += "</div>";

			return html;
		},
		create_anchor_tag: function (args) {
			// var html = '<div class="' + args.divClassName + '">';
			var html =
				'<a class="' +
				args.buttonClassName +
				'" ' +
				args.buttonData +
				">" +
				args.buttonText +
				"</a>";
			// html += "</div>";

			return html;
		},

		create_switch_tag: function (args) {
			var html = '<div class="' + args.divClassName + '">';
			html +=
				'<input id="' +
				args.id +
				'" type="checkbox" class="' +
				args.inputClassName +
				'" ' +
				args.default_checked +
				"  " +
				args.disabled +
				">";
			html +=
				'<label class="' +
				args.labelClassName +
				'" for="' +
				args.id +
				'"> ' +
				args.default +
				" </label>";
			html += "</div>";
			html +=
				'<div class="urcr-notice urcr-notice-warning"><p><b>Warning:</b> It will restrict the whole site.Please add the login shortcode in the action tab.</p></div>';

			return html;
		},

		/**
		 * Create a html select tag.
		 *
		 * @param {JSON} args Arguments.
		 */
		create_select_tag: function (args) {
			var id = args.id ? 'id="' + args.id + '"' : "",
				className = args.className ? args.className : "",
				multiple = args.multiple ? 'multiple="multiple"' : "",
				html = "",
				options =
					args.options && "object" === typeof args.options
						? args.options
						: {},
				data =
					args.data && "object" === typeof args.data ? args.data : {},
				data_string = "",
				empty_option =
					true === args.prepend_empty_option
						? "<option></option>"
						: "", // This empty option is used for placeholder by select2.
				attr =
					args.attr && "object" === typeof args.attr ? args.attr : {},
				attr_string = "",
				style_string =
					args.style && "string" === typeof args.style
						? 'style="' + args.style + '"'
						: "",
				value = args.value ? args.value : "",
				selected = "";

			// Prepare element attributes.
			Object.keys(attr).forEach(function (key) {
				attr_string += key + '="' + attr[key] + '" ';
			});

			// Prepare element data attributes.
			Object.keys(data).forEach(function (key) {
				data_string += "data-" + key + '="' + data[key] + '" ';
			});

			html =
				"<select " +
				id +
				' class="' +
				className +
				'" ' +
				multiple +
				" " +
				data_string +
				" " +
				attr_string +
				" " +
				style_string +
				">" +
				empty_option;

			// Prepare option tags list.
			Object.keys(options).forEach(function (key) {
				if (
					(Array.isArray(value) && value.includes(key)) ||
					value === key
				) {
					selected = 'selected="selected"';
				} else {
					selected = "";
				}

				html +=
					'<option value="' +
					key +
					'" ' +
					selected +
					">" +
					options[key] +
					"</option>";
			});
			html += "</select>";

			return html;
		},

		/**
		 * Generate a unique string.
		 *
		 * @param {String} prefix Prefix a string.
		 */
		generate_unique_string: function (prefix) {
			prefix = urcr_utils.if_empty(prefix, "x");

			return prefix + new Date().getTime();
		},

		/**
		 * Resolve smart tags in a string.
		 * - Normal Smart Tag:
		 * -- Format: `{{tag}}`
		 * -- Example: `{{label}}`
		 *
		 * - Variable Smart Tag:
		 * -- Format: `{{placeholder:value}}`
		 * -- Example: `{{logic_gate:OR}}`
		 * -- Usage: When you have a dynamic value and you need to put that in only the place meeting a certain requirement, like it should have specific value.
		 *
		 * @param {String} str Subject string to operate on.
		 * @param {JSON} smart_tags Normal smart tags.
		 * @param {Array<JSON>} smart_tag_variables Variable smart tags.
		 */
		resolve_smart_tags: function (str, smart_tags, smart_tag_variables) {
			var regex;

			smart_tag_variables = urcr_utils.if_not_array(
				smart_tag_variables,
				[]
			);

			// Resolve normal smart tags.
			if (str && smart_tags && "object" === typeof smart_tags) {
				Object.keys(smart_tags).forEach(function (tag) {
					regex = new RegExp("{{{" + tag + "}}}", "g");
					str = str.replace(regex, smart_tags[tag]);
				});
			}

			// Resolve variable smart tags.
			if (
				smart_tag_variables &&
				Array.isArray(smart_tag_variables) &&
				smart_tag_variables.length
			) {
				smart_tag_variables.forEach(function (variable) {
					// Placeholder must be validated against the empty string to avoid unintended behaviour.
					if (
						variable &&
						"object" === typeof variable &&
						variable.placeholder &&
						"" !== variable.placeholder
					) {
						// Place the value.
						regex = new RegExp(
							"{{{" +
								variable.placeholder +
								":" +
								variable.tag +
								"}}}",
							"g"
						);
						str = str.replace(regex, variable.value);
						//class smart tag replace.
						str = str.replaceAll("{{Class}}", variable.tag);

						// Discard the remaining variables.
						regex = new RegExp(
							"{{{" + variable.placeholder + ":[A-z0-9_]+}}}",
							"g"
						);

						str = str.replace(regex, "");
					} else if (DEBUG) {
						console.warn(
							"URCR: Invalid smart tag variable",
							variable
						);
					}
				});
			}
			return str;
		},

		/**
		 * Show success message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_success_message: function (message) {
			if (snackbar) {
				snackbar.add({
					type: "success",
					message: message,
					duration: 5,
				});
				return true;
			}
			return false;
		},

		/**
		 * Show failure message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_failure_message: function (message) {
			if (snackbar) {
				snackbar.add({
					type: "failure",
					message: message,
					duration: 6,
				});
				return true;
			}
			return false;
		},

		/**
		 * Insert/Append a template into a container after resolving smart tags.
		 *
		 * @param {jQuery} $container Container to insert template into.
		 * @param {String} template Template string.
		 * @param {JSON} smart_tags Normal smart tags.
		 * @param {Array<JSON>} smart_tag_variables Variable smart tags.
		 */
		insert_template: function (
			$container,
			template,
			smart_tags,
			smart_tag_variables
		) {
			smart_tags = urcr_utils.if_not_json_object(smart_tags, {});
			smart_tag_variables = urcr_utils.if_not_array(
				smart_tag_variables,
				[]
			);

			if ($container && $container.append) {
				if (smart_tags.ID && true === smart_tags.ID) {
					smart_tags.ID = urcr_utils.generate_unique_string();
				}
				template = urcr_utils.resolve_smart_tags(
					template,
					smart_tags,
					smart_tag_variables
				);

				$container.append(template);
			}
		},

		/**
		 * Append a spinner element.
		 *
		 * @param {jQuery} $element
		 */
		append_spinner: function ($element) {
			if ($element && $element.append) {
				var spinner = '<span class="ur-spinner is-active"></span>';

				if ("checkbox" === $element.attr("type")) {
					$element.after(spinner);
				} else {
					$element.append(spinner);
				}
				return true;
			}
			return false;
		},

		/**
		 * Remove spinner elements from a element.
		 *
		 * @param {jQuery} $element
		 */
		remove_spinner: function ($element) {
			if ($element && $element.remove) {
				if ("checkbox" === $element.attr("type")) {
					$element.parent().find(".ur-spinner").remove();
				} else {
					$element.find(".ur-spinner").remove();
				}
				return true;
			}
			return false;
		},

		/**
		 * Update browser URL according to the access rule state.
		 * For example: If user created a new access rule then add post-id argument in the URL.
		 */
		updateBrowserLocationURL: function () {
			if (urcr_data.rule_id) {
				var queryParams = new URLSearchParams(window.location.search);

				// Do nothing if the post-id has been set and it matches the current post-id.
				if (
					queryParams.get("post-id") &&
					queryParams.get("post-id") === urcr_data.rule_id.toString()
				) {
					return;
				}

				queryParams.delete("post-id");
				queryParams.set("post-id", urcr_data.rule_id);
				window.history.pushState(
					{},
					"",
					window.location.origin +
						window.location.pathname +
						"?" +
						queryParams.toString()
				);
			}
		},

		/**
		 * Enable/Disable save buttons i.e. 'Save' button and 'Save as Draft' button.
		 *
		 * @param {Boolean} disable Whether to disable or enable.
		 */
		toggleSaveButtons: function (disable) {
			disable = urcr_utils.if_empty(disable, true);

			$(".urcr-save-rule").prop("disabled", !!disable);
			$(".urcr-save-rule-as-draft").prop("disabled", !!disable);
		},

		/**
		 * Validate access rule data.
		 *
		 * @param {jQuery} $form Form element or container.
		 * @param {Boolean} show_error Whether show message to user if the data is invalid.
		 *
		 * @return {JSON} Validation object: `{ result: boolean, message: string }`.
		 */
		validate_access_rule_form: function (show_error) {
			var title = $(".urcr-content-access-rule-title-input").val().trim(),
				validation = {
					result: true,
				};

			show_error = urcr_utils.if_empty(show_error, true);

			if (!title || "" === title) {
				validation.result = false;
				validation.message = urcr_data.labels.title_is_required;
			}

			if (show_error && !validation.result) {
				this.show_failure_message(validation.message);
			}

			return validation;
		},
	};

	// Information extractor functions.
	var urcr_extractor = {
		/**
		 * Extract access rule data.
		 *
		 * @param {jQuery} $rule_container An element containing elements that hold access rule data.
		 */
		extract_rule_data: function ($rule_container) {
			var data = {
				enabled: $("#urcr-enable-access-rule").is(":checked"),
				logic_map: this.extract_conditional_logic_map(
					$rule_container.find(
						".urcr-conditional-logic-map-container > .urcr-conditional-logic-item"
					)
				),
				target_contents: this.extract_target_contents($rule_container),
				actions: this.extract_rule_actions(),
			};
			return data;
		},

		/**
		 * Extract conditional logic map for content restriction.
		 *
		 * @param {jQuery} $urcrcl_field jQuery object containing a conditional logic field element, that is either field or group.
		 * @param {Boolean} auto_generate_id_if_missing Whether to generate ID for the fields wich don't have ID.
		 */
		extract_conditional_logic_map: function (
			$urcrcl_field,
			auto_generate_id_if_missing
		) {
			var type = $urcrcl_field.data("type"),
				id = $urcrcl_field.data("store-id"),
				logic_map = {};

			auto_generate_id_if_missing = urcr_utils.if_empty(
				auto_generate_id_if_missing,
				false
			);

			if ($urcrcl_field && $urcrcl_field instanceof jQuery) {
				// Auto generate ID.
				if (auto_generate_id_if_missing && (!id || "" === id)) {
					id = urcr_utils.generate_unique_string();
				}

				if (type && "" !== type) {
					if ("group" === type) {
						var $childs = $urcrcl_field
								.children(".urcr-cld-wrapper")
								.children(".urcr-conditional-logic-definitions")
								.children(".urcr-conditional-logic-item"),
							conditions = [],
							logic_gate = $urcrcl_field
								.find(".urcr-logic-gate-" + id + ".is-active")
								.data("value");

						$childs.each(function () {
							conditions.push(
								urcr_extractor.extract_conditional_logic_map(
									$(this)
								)
							);
						});

						logic_map = {
							type: "group",
							id: id,
							logic_gate: logic_gate,
							conditions: conditions,
						};
					} else if ("user_state" === type) {
						var $active_item = $urcrcl_field.find(
							".urbg-item-" + id + ".is-active"
						);

						logic_map = {
							type: type,
							id: id,
							value: $active_item.length
								? $active_item.data("value")
								: null,
						};
					} else if ("access_period" === type) {
						$select_value = $urcrcl_field.find("select").val();
						$input_value = $urcrcl_field.find("input").val();
						$access_period_value = {
							select: $select_value,
							input: $input_value,
						};
						logic_map = {
							type: type,
							id: id,
							value: $access_period_value,
						};
					} else if ("ur_form_field" === type) {
						$select_value = $urcrcl_field.find("select").val();
						$form_fields = [];
						$fields_row = $urcrcl_field.find(".ur_form_fields_row");
						if ($fields_row.length > 0) {
							$fields_row.each(function () {
								var tmpArray = {
									field_name: $(this)
										.find(".urcr_form_field_name")
										.val(),
									operator: $(this)
										.find(".urcr_form_field_logic")
										.val(),
									value: $(this)
										.find(".urcr_form_field_value")
										.val(),
								};

								$form_fields.push(tmpArray);
							});
						}
						$ur_form_field_data = {
							form_id: $select_value,
							form_fields: $form_fields,
						};
						logic_map = {
							type: type,
							id: id,
							value: $ur_form_field_data,
						};
					} else {
						logic_map = {
							type: type,
							id: id,
							value: $urcrcl_field.find("select, input").val(),
						};
					}
				}
			}

			return logic_map;
		},

		/**
		 * Extract targets for content restriction.
		 *
		 * @param {jQuery} $container Elements containing information on target contents.
		 * @param {Boolean} auto_generate_id_if_missing Whether to generate ID if missing.
		 */
		extract_target_contents: function (
			$container,
			auto_generate_id_if_missing
		) {
			var target_contents = [],
				id,
				type,
				value,
				target_content;

			auto_generate_id_if_missing = urcr_utils.if_empty(
				auto_generate_id_if_missing,
				true
			);

			if ($container && $container instanceof jQuery) {
				$targets = $container.find(".urcr-target-content");

				if ($targets.length) {
					$targets.each(function () {
						id = $(this).data("store-id");
						value = $(this)
							.find(".urcr-content-target-input")
							.val();
						type = $(this).data("type");

						// Auto generate ID.
						if ((!id || "" === id) && auto_generate_id_if_missing) {
							id = urcr_utils.generate_unique_string();
						}

						target_content = {
							id: id,
							type: type,
							value: value,
						};

						// Process taxonomy targets.
						if ("taxonomy" === type) {
							target_content.taxonomy = value;
							target_content.value = $(this)
								.find(".urcr-terms-selector")
								.val();
						}
						target_contents.push(target_content);
					});
				}
			}

			return target_contents;
		},

		/**
		 * Extract access rule actions data.
		 *
		 * @param {jQuery} $container Elements containing information on access rule actions.
		 * @param {Boolean} encode_restriction_message Whether to encode message, for example: to avoid conflict with JSON string literals.
		 * @param {Boolean} encode_redirect_uri Whether to encode URI, for example: to avoid conflict with JSON string literals.
		 * @param {Boolean} encode_shortcode_args Whether to encode shortcode args, for example: to avoid conflict with JSON string literals.
		 */
		extract_rule_actions: function (
			$container,
			encode_restriction_message,
			encode_redirect_uri,
			encode_shortcode_args
		) {
			var actions = [];

			encode_restriction_message = urcr_utils.if_empty(
				encode_restriction_message,
				true
			);
			encode_redirect_uri = urcr_utils.if_empty(
				encode_redirect_uri,
				true
			);
			encode_shortcode_args = urcr_utils.if_empty(
				encode_shortcode_args,
				true
			);

			if (!$container) {
				$container = $(".urcr-rule-actions-container");
			}

			if ($container && $container instanceof jQuery) {
				/**
				 * Fix Code:
				 * This line of code fixes the new content not getting returned by calling 'getContent' method on tinymce
				 * when the editor is in Text mode.
				 */
				$("#urcr-rule-action-message-input-tmce").trigger("click");

				var type = $container
						.find(".urcr-rule-action-type-input")
						.val(),
					label = $container
						.find(
							'.urcr-rule-action-type-input option[value="' +
								type +
								'"]'
						)
						.text(),
					message_editor = tinyMCE.get(
						"urcr-rule-action-message-input"
					),
					restriction_message = message_editor
						? message_editor.getContent()
						: "",
					access_control = $(
						".urcr-input-container .urcr-rule-access-control"
					).val(),
					redirect_url = $(
						".urcrra-redirect-input-container .urcr-input"
					).val(),
					local_page = $(
						".urcrra-redirect-to-local-page-input-container .urcr-input"
					).val(),
					ur_form_id = $(
						".urcrra-ur-form-input-container .urcr-input"
					).val(),
					shortcode = $(
						".urcrra-shortcode-input-container .urcr-input"
					).val(),
					shortcode_args = $(
						".urcrra-shortcode-input-container .urcrra-shortcode-args"
					).val();

				// Encode message to avoid conflicts with JSON format strings.
				if (
					encode_restriction_message &&
					restriction_message &&
					"" !== restriction_message
				) {
					restriction_message =
						encodeURIComponent(restriction_message);
				}

				// Encode redirect url to avoid conflicts with JSON format strings.
				if (
					encode_redirect_uri &&
					redirect_url &&
					"" !== redirect_url
				) {
					redirect_url = encodeURIComponent(redirect_url);
				}

				// Encode shortcode arguments to avoid conflicts with JSON format strings as it can contain any strings.
				if (
					encode_shortcode_args &&
					shortcode_args &&
					"" !== shortcode_args
				) {
					shortcode_args = encodeURIComponent(shortcode_args);
				}

				actions.push({
					id: urcr_utils.generate_unique_string(),
					type: type,
					label: label ? label : "",
					message: restriction_message ? restriction_message : "",
					redirect_url: redirect_url ? redirect_url : "",
					access_control: access_control ? access_control : "",
					local_page: local_page ? local_page : "",
					ur_form: ur_form_id ? ur_form_id : "",
					shortcode: {
						tag: shortcode ? shortcode : "",
						args: shortcode_args ? shortcode_args : "",
					},
				});
			}

			return actions;
		},
	};

	// Renderer functions.
	var urcr_renderer = {
		/**
		 * Render an access rule UI using available data.
		 */
		render_access_rule: function () {
			var rule = urcr_data.access_rule_data;

			// Set initial values.
			if (urcr_data.rule_id && "" !== urcr_data.rule_id) {
				// Set page header.
				$("h1.wp-heading-inline").text(
					urcr_data.labels.edit_access_rule
				);

				// Set access rule Title.
				$(".urcr-content-access-rule-title-input").val(urcr_data.title);

				// Set access rule state i.e. enabled or disabled and labels.
				if (urcr_data.access_rule_data.enabled) {
					$("#urcr-enable-access-rule").attr("checked", "checked");
					$(".urcr-enable-access-rule-label").text(
						urcr_data.labels.enabled
					);
				} else {
					$("#urcr-enable-access-rule").attr("checked", false);
					$(".urcr-enable-access-rule-label").text(
						urcr_data.labels.disabled
					);
				}

				// Set save buttons text.
				if (urcr_data.is_draft) {
					$(".urcr-save-rule-as-draft").text(
						urcr_data.labels.save_draft
					);
				} else {
					$(".urcr-save-rule").text(urcr_data.labels.save_rule);
				}
			}
			if (urcr_data.is_draft) {
				// Set publish button text.
				$(".urcr-save-rule").text(urcr_data.labels.publish_rule);
			}

			if (urcr_data.rule_id && "" !== urcr_data.rule_id && rule) {
				// Render conditional logic map.
				if (rule.logic_map) {
					this.render_conditional_logic_map(
						rule.logic_map,
						$(".urcr-conditional-logic-map-container")
					);
				} else if (DEBUG) {
					console.warn("Logic map is unavailable", rule.logic_map);
				}

				// Render rule actions.
				if (rule.actions) {
					this.render_rule_actions(rule.actions);
				} else if (DEBUG) {
					console.warn("Rule action is unavailable", rule.actions);
				}

				// Render target contents.
				if (rule.target_contents) {
					this.render_target_contents(rule.target_contents);
				} else if (DEBUG) {
					console.warn(
						"Target contents are unavailable",
						rule.target_contents
					);
				}

				urcr_utils.init_all_enhanced_select();
				urcr_utils.init_all_flatpickr();
				urcr_utils.init_all_tooltips();
			} else {
				if (DEBUG && !rule && urcr_data.rule_id) {
					console.warn(
						"URCR: Content access rule data is unavailable. It means the rule was unable to be localized or parsed in the server. Could be because of invalid rule ID or invalid rule data.",
						rule
					);
				}
				urcr_access_rule_creator.add_new_group(
					$(".urcr-conditional-logic-map-container")
				);
			}

			$(".urcr-widget-header-label")
				.first()
				.text(urcr_data.labels.main_logic_group); // Change the root conditional logic group label to "Main Logic Group".
			$(".urcr-spinner-container").remove();
			$(".urcrcl-trash").first().remove();
		},

		/**
		 * Render a conditional logic map.
		 *
		 * @param {JSON} logic_map Logic map or a logic map field.
		 * @param {jQuery} $container Container to render the logic map into.
		 */
		render_conditional_logic_map: function (logic_map, $container) {
			$container = urcr_utils.if_empty(
				$container,
				$(".urcr-conditional-logic-map-container")
			);

			if (logic_map && $container && $container instanceof jQuery) {
				if ("group" === logic_map.type) {
					urcr_access_rule_creator.add_new_group(
						$container,
						logic_map
					);
					$container = $(
						"#urcr-conditional-logic-definitions-" + logic_map.id
					);

					for (var i = 0; i < logic_map.conditions.length; i++) {
						var sub_logic_map = logic_map.conditions[i];

						this.render_conditional_logic_map(
							sub_logic_map,
							$container
						);
					}
				} else {
					urcr_access_rule_creator.add_new_field(
						logic_map.type,
						$container,
						logic_map
					);
				}
				return true;
			}
			return false;
		},

		/**
		 * Render target contents input UI.
		 *
		 * @param {JSON} target_contents Target contents data.
		 * @param {jQuery} $container Container to render target contents input UI into.
		 */
		render_target_contents: function (target_contents, $container) {
			$container = urcr_utils.if_empty(
				$container,
				$(".urcr-target-contents-container")
			);

			if (target_contents && $container && $container instanceof jQuery) {
				for (var i = 0; i < target_contents.length; i++) {
					var target_content = target_contents[i];

					if (target_content.type) {
						urcr_content_targets_creator.add_new_target(
							target_content.type,
							$container,
							target_content
						);
					}
				}
				return true;
			}
			return false;
		},

		/**
		 * Render access rule action input UI.
		 *
		 * @param {JSON} actions Actions for the access rule.
		 * @param {jQuery} $container Container to render actions input UI into.
		 */
		render_rule_actions: function (actions, $container) {
			$container = urcr_utils.if_empty(
				$container,
				$(".urcr-rule-actions-container")
			);

			if (
				actions &&
				Array.isArray(actions) &&
				$container &&
				$container instanceof jQuery
			) {
				for (var i = 0; i < actions.length; i++) {
					var action = actions[i];

					switch (action.type) {
						default:
							$(".urcr-rule-action-type-input").val(action.type);
							$(".urcrra-ur-form-input").val(action.ur_form);
							$(".urcrra-shortcode-input").val(
								action.shortcode.tag
							);
							$(".urcrra-shortcode-args").val(
								decodeURIComponent(action.shortcode.args)
							);
							$(
								".urcrra-redirect-input-container .urcr-input"
							).val(decodeURIComponent(action.redirect_url));
							$(
								".urcrra-redirect-to-local-page-input-container .urcr-input"
							).val(action.local_page);

							for (
								var i = 0;
								i < tinymce.editors.length;
								i += 1
							) {
								var editor = tinymce.editors[i];

								if (
									"urcr-rule-action-message-input" ===
									editor.id
								) {
									editor.on("init", function (event) {
										event.target.setContent(
											decodeURIComponent(action.message)
										);
									});
									break;
								}
							}
							urcr_rule_action_controller.updateToggleRuleActionInput(
								action.type
							);
							break;
					}
				}
				return true;
			}
			return false;
		},
	};

	// URCR Content Access Rule creator controller.
	var urcr_access_rule_creator = {
		init: function () {
			this.init_conditional_logic_field_creation_handler();
			this.init_conditional_logic_ur_form_field_handler();
			this.init_conditional_logic_group_creation_handler();
			this.init_conditional_logic_field_deletion_handler();
		},
		init_conditional_logic_ur_form_field_handler: function () {
			// For taxonomy.
			$(document.body).on(
				"change",
				".urcr-ur-form-field-selector",
				function () {
					$(this)
						.closest(".urcr-input-container")
						.find(".ur_form_fields_row")
						.remove();
					var form_id = $(this).val();
					urcr_access_rule_creator.add_new_form_field(
						form_id,
						this,
						[]
					);
				}
			);

			$(document.body).on(
				"change",
				"#urcr_form_field_logic",
				function () {
					var $this = $(this);
					$(this).siblings('.urcr_form_field_value').show();
					if($this.val() === 'empty' || $this.val() === 'not empty') {
						$this.siblings('.urcr_form_field_value').hide();
					}
				}
			);

			$(document.body).on(
				"click",
				".urcr_add_new_form_field_button",
				function () {
					var form_id = $(this).data("form-id");
					urcr_access_rule_creator.add_new_form_field(
						form_id,
						this,
						[]
					);
				}
			);

			$(document.body).on(
				"click",
				".urcr_remove_form_field_button",
				function () {
					if (
						$(this)
							.closest(".urcr-input-container")
							.find(".ur_form_fields_row").length > 1
					) {
						$(this).closest(".ur_form_fields_row").remove();
					}
				}
			);
		},

		init_conditional_logic_field_creation_handler: function () {
			$(document.body).on(
				"change",
				".urcr-add-new-conditional-logic-field",
				function () {
					var type = $(this).val();
					urcr_access_rule_creator.add_new_field(
						type,
						$(this)
							.closest(".urcr-settings-widget")
							.children(".urcr-cld-wrapper")
							.children(".urcr-conditional-logic-definitions")
					);

					urcr_utils.init_all_enhanced_select();
					urcr_utils.init_all_flatpickr();
					urcr_utils.init_all_tooltips();
				}
			);
		},

		init_conditional_logic_group_creation_handler: function () {
			$(document.body).on(
				"click",
				".urcr-add-new-conditional-logic-group",
				function () {
					var $definitions_container = $(this)
						.closest(".urcr-settings-widget")
						.children(".urcr-cld-wrapper")
						.children(".urcr-conditional-logic-definitions");
					urcr_access_rule_creator.add_new_group(
						$definitions_container
					);
				}
			);
		},

		/**
		 * Initialize conditional logic field/group deletion handler.
		 */
		init_conditional_logic_field_deletion_handler: function () {
			$(document.body).on("click", ".urcrcl-trash", function () {
				$this = $(this);

				if (
					!$this
						.closest(".urcr-conditional-logic-item")
						.is(
							".urcr-conditional-logic-map-container > .urcr-settings-widget"
						)
				) {
					Swal.fire({
						title: urcr_data.labels.are_you_sure,
						text: urcr_data.labels.clfog_deletion_message,
						icon: "warning",
						customClass: {
							confirmButton: "button button-danger",
							cancelButton: "button button-secondary ur-mr-1",
						},
						showCancelButton: true,
						confirmButtonText: urcr_data.labels.delete,
						reverseButtons: true,
					}).then(function (result) {
						if (result.value) {
							$this
								.closest(".urcr-conditional-logic-item")
								.fadeOut({
									complete: function () {
										$(this).remove();
									},
								});
						}
					});
				}
			});
		},

		add_new_form_field: function (form_id, pointer, data) {
			var ur_form_data = urcr_data.ur_form_data[form_id]
				? urcr_data.ur_form_data[form_id]
				: {};
			inputHtml = '<div class="ur_form_fields_row">';

			inputHtml += urcr_utils.create_select_tag({
				options: ur_form_data,
				className: "urcr_form_field_name",
				value: data.field_name ? data.field_name : [],
			});
			var empty_options = ["empty", "not empty"];
			inputHtml += urcr_utils.create_select_tag({
				options: {"is": "is", "is not" : "is not", "empty" : "empty", "not empty" : "not empty"},
				id: "urcr_form_field_logic",
				className: "urcr_form_field_logic",
				value: data.operator ? data.operator : [],
			});

			inputHtml += urcr_utils.create_input_tag({
				divClassName: "ur-ml-auto",
				inputClassName: "urcr_form_field_value",
				labelClassName: "",
				value: data.value ? data.value : "",
				style: empty_options.includes(data.operator) ? "display:none" : "",
				id: "urcr_form_field_value",
			});

			inputHtml += urcr_utils.create_anchor_tag({
				buttonClassName: "urcr_add_new_form_field_button",
				buttonData: 'data-form-id="' + form_id + '"',
				buttonText:
					'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <path fill="#000" d="M12 21.95c-.6 0-1-.4-1-1v-8H3.1c-.6 0-1-.4-1-1s.4-1 1-1H11v-7.9c0-.6.4-1 1-1s1 .4 1 1v7.9h7.9c.6 0 1 .4 1 1s-.4 1-1 1H13v8c0 .6-.4 1-1 1Z"/> </svg>',
			});
			inputHtml += urcr_utils.create_anchor_tag({
				buttonClassName: "urcr_remove_form_field_button",
				buttonData: 'data-form-id="' + form_id + '"',
				buttonText:
					'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <path fill="#000" d="M20.9 13H3.1c-.6 0-1-.4-1-1s.4-1 1-1h17.8c.6 0 1 .4 1 1s-.4 1-1 1Z"/> </svg>',
			});
			inputHtml += "</div>";
			$(pointer)
				.closest(".urcr-input-container")
				.find(".urcr-form-fields-wrapper")
				.append(inputHtml);

			urcr_utils.init_all_enhanced_select();
		},

		/**
		 * Add new conditional logic group.
		 *
		 * @param {jQuery} $container Container element to add new group to.
		 * @param {JSON} data Options and initial values.
		 */
		add_new_group: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var template = urcr_data.templates.conditional_logic_group_template,
				smart_tags = {
					ID: data.id ? data.id : true,
				},
				smart_tag_variables = [
					{
						placeholder: "logic_gate",
						tag: data.logic_gate ? data.logic_gate : "",
						value: "is-active",
					},
				];

			urcr_utils.insert_template(
				$container,
				template,
				smart_tags,
				smart_tag_variables
			);
		},

		/**
		 * Add new conditional logic field.
		 *
		 * @param {String} type Type of field to add.
		 * @param {jQuery} $container Container element to add new field to.
		 * @param {JSON} data Options and initial values.
		 */
		add_new_field: function (type, $container, data) {
			$("#" + $container[0].id)
				.closest(".inside")
				.find("p.urcr-conditional-logic-warning")
				.remove();
			data = urcr_utils.if_not_json_object(data, {});
			switch (type) {
				case "roles":
					this.add_new_roles_field($container, data);
					break;

				case "user_registered_date":
					this.add_new_user_registered_date_picker($container, data);
					break;

				case "access_period":
					this.add_new_access_period_selector($container, data);
					break;

				case "user_state":
					this.add_new_user_state_selector($container, data);
					break;

				case "profile_completeness":
					this.add_new_profile_completeness_selector(
						$container,
						data
					);
					break;

				case "email_domain":
					this.add_new_email_domain_selector($container, data);
					break;

				case "post_count":
					this.add_new_post_count_selector($container, data);
					break;

				case "capabilities":
					this.add_new_capabilities_selector($container, data);
					break;

				case "registration_source":
					this.add_new_registration_source_selector($container, data);
					break;

				case "ur_form_field":
					this.add_new_ur_form_field_selector($container, data);
					break;

				case "payment_status":
					this.add_new_payment_status_field_selector($container, data);
					break;

				case "membership":
					this.add_new_membership_field_selector($container, data);
					break;

				default:
					break;
			}
		},

		add_new_roles_field: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcrcl-roles-selector widefat",
					multiple: true,
					options: urcr_data.wp_roles,
					value: data.value ? data.value : [],
				}),
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.roles,
				type: "roles",
				tooltip: urcr_data.labels.roles_tooltip,
			});
		},

		add_new_user_registered_date_picker: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var value = data.value ? 'value="' + data.value + '"' : "",
				inputHtml =
					'<input class="urcr-flatpickr urcrcl-user-registered-date-picker widefat" type="text" data-mode="range" ' +
					value +
					"/>",
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.user_registered_date,
				type: "user_registered_date",
				tooltip: urcr_data.labels.registered_date_tooltip,
			});
		},
		add_new_access_period_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});
			var value_input =
				"undefined" !== typeof data.value && "" !== data.value
					? 'value="' + data.value.input + '"'
					: "";
			var during_selected =
				"undefined" !== typeof data.value &&
				"" !== data.value &&
				data.value.select == "During"
					? "selected=true"
					: "";
			var after_selected =
				"undefined" !== typeof data.value &&
				"" !== data.value &&
				data.value.select == "After"
					? "selected=true"
					: "";
			(inputHtml =
				"<select>" +
				'<option value="During" ' +
				during_selected +
				">During</option>" +
				'<option value="After"  ' +
				after_selected +
				">After</option>" +
				"</select>" +
				'<input type="number" min="1" ' +
				value_input +
				"/> Days"),
				(template =
					urcr_data.templates.conditional_logic_field_template);

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.access_period,
				type: "access_period",
				tooltip: urcr_data.labels.access_period_tooltip,
			});
		},

		add_new_user_state_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var template = urcr_data.templates.conditional_logic_field_template,
				inputHtml = ur_create_toggle_buttons({
					id: data.id ? data.id : urcr_utils.generate_unique_string(),
					className: "urcr-user-state",
					value: data.value,
					buttons: [
						{
							text: urcr_data.labels.logged_in,
							value: "logged-in",
						},
						{
							text: urcr_data.labels.logged_out,
							value: "logged-out",
						},
					],
				});

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.user_state,
				type: "user_state",
				tooltip: urcr_data.labels.user_state_tooltip,
			});
		},

		/**
		 * Payment status field selector
		 * @param $container
		 * @param data
		 */
		add_new_payment_status_field_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 widefat",
					multiple: true,
					options: urcr_data.payment_status,
					value: data.value ? data.value : [],
				}),
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.payment_status,
				type: "payment_status",
				tooltip: urcr_data.labels.payment_status_tooltip,
			});
		},

		/**
		 * Membership field selector
		 * @param $container
		 * @param data
		 */
		add_new_membership_field_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 widefat",
					multiple: true,
					options: urcr_data.memberships,
					value: data.value ? data.value : [],
				}),
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.membership,
				type: "membership",
				tooltip: urcr_data.labels.membership_tooltip,
			});
		},

		/**
		 * Append Profile Completeness Selector when selected in logic option.
		 * @param {*} $container
		 * @param {*} data
		 */
		add_new_profile_completeness_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var value_input =
				"undefined" !== typeof data.value && "" !== data.value
					? 'value="' + data.value + '"'
					: "";

			(inputHtml =
				"More than&nbsp;" +
				'<input type="number" min="1" max="100" ' +
				value_input +
				"/>&nbsp;%"),
				(template =
					urcr_data.templates.conditional_logic_field_template);

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.profile_completeness,
				type: "profile_completeness",
				tooltip: urcr_data.labels.profile_completeness_tooltip,
			});
		},

		add_new_email_domain_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var value = data.value ? 'value="' + data.value + '"' : "",
				inputHtml =
					'<input class="urcrcl-email-domain-selector widefat" type="text" placeholder="Eg. gmail.com, yahoo.com"' +
					value +
					"/>",
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.email_domain,
				type: "email_domain",
				tooltip: urcr_data.labels.email_domains_tooltip,
			});
		},

		add_new_post_count_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var value = data.value ? data.value : 0,
				inputHtml =
					'<input class="urcrcl-post-count-selector" type="number" min="0" value="' +
					value +
					'"/>',
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.post_count,
				type: "post_count",
				tooltip: urcr_data.labels.min_post_count_tooltip,
			});
		},

		add_new_capabilities_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcrcl-capabilities-selector widefat",
					multiple: true,
					options: urcr_data.wp_capabilities,
					value: data.value ? data.value : [],
				}),
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.capabilities,
				type: "capabilities",
				tooltip: urcr_data.labels.capabilities_tooltip,
			});
		},

		add_new_registration_source_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcrcl-rigistration-source-selector widefat",
					multiple: true,
					options: urcr_data.registration_sources,
					value: data.value ? data.value : [],
				}),
				template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.registration_source,
				type: "registration_source",
				tooltip: urcr_data.labels.registration_source_tooltip,
			});
		},
		add_new_ur_form_field_selector: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
				className:
					"urcr-enhanced-select2 urcr-ur-form-field-selector widefat",
				options: urcr_data.ur_forms,
				prepend_empty_option: true,
				data: {
					placeholder: "Select a Form...",
				},
				value:
					data.hasOwnProperty("value") &&
					data.value.hasOwnProperty("form_id")
						? data.value.form_id
						: [],
			});

			inputHtml += '<div class="urcr-form-fields-wrapper"></div>';

			template = urcr_data.templates.conditional_logic_field_template;

			urcr_utils.insert_template($container, template, {
				ID: data.id ? data.id : true,
				input: inputHtml,
				label: urcr_data.labels.ur_form_field,
				type: "ur_form_field",
				tooltip: urcr_data.labels.ur_form_field_tooltip,
			});

			$form_field_data =
				data.hasOwnProperty("value") &&
				data.value.hasOwnProperty("form_fields")
					? data.value.form_fields
					: [];
			if ($form_field_data.length > 0) {
				$($form_field_data).each(function (e, form_fields) {
					$selector = $(
						"#urcr-conditional-logic-field-" + data.id
					).find(".urcr-ur-form-field-selector");
					urcr_access_rule_creator.add_new_form_field(
						data.value.form_id,
						$selector,
						form_fields
					);
				});
			}

			// TODO.
		},
	};

	// URCR content targets creator controller.
	var urcr_content_targets_creator = {
		init: function () {
			this.init_target_content_creation_handler();
			this.init_subfield_creation_handler();
			this.init_target_content_deletion_handler();
		},

		init_target_content_creation_handler: function () {
			$(document.body).on(
				"change",
				".urcr-add-new-target-contents",
				function () {
					var type = $(this).val();

					urcr_content_targets_creator.add_new_target(
						type,
						$(this)
							.closest(".urcr-settings-widget")
							.find(".urcr-target-contents-container")
					);

					urcr_utils.init_all_enhanced_select();
					urcr_utils.init_all_tooltips();
				}
			);
		},

		init_subfield_creation_handler: function () {
			// For taxonomy.
			$(document.body).on(
				"change",
				".urcr-taxonomy-selector",
				function () {
					var selected_taxonomy = $(this).val(),
						terms = urcr_data.terms_list[selected_taxonomy]
							? urcr_data.terms_list[selected_taxonomy]
							: {},
						inputHtml = urcr_utils.create_select_tag({
							className:
								"urcr-enhanced-select2 urcr-content-target-input urcr-terms-selector widefat",
							multiple: true,
							options: terms,
							data: {
								select2_class: "urcr-terms-selector-select2",
							},
						});

					$(this)
						.closest(".urcr-input-container")
						.find(".urcr-terms-selector")
						.remove();
					$(this)
						.closest(".urcr-input-container")
						.find(".urcr-terms-selector-select2")
						.closest(".select2-container")
						.remove();
					$(this).closest(".urcr-input-container").append(inputHtml);

					urcr_utils.init_all_enhanced_select();
				}
			);
		},

		init_target_content_deletion_handler: function () {
			$(document.body).on(
				"click",
				".urcr-trash-target-content",
				function () {
					var $this = $(this);

					Swal.fire({
						title: urcr_data.labels.are_you_sure,
						text: urcr_data.labels.cannot_revert,
						icon: "warning",
						customClass: {
							confirmButton: "button button-danger",
							cancelButton: "button button-secondary ur-mr-1",
						},
						showCancelButton: true,
						confirmButtonText: urcr_data.labels.delete,
						reverseButtons: true,
					}).then(function (result) {
						if (result.value) {
							$this.closest(".urcr-target-content").fadeOut({
								complete: function () {
									$(this).remove();
								},
							});
						}
					});
				}
			);
		},

		/**
		 * Add new target content field.
		 *
		 * @param {String} type Type of target.
		 * @param {jQuery} $container Container element to add the target UI to.
		 * @param {JSON} data Options and initial values.
		 */
		add_new_target: function (type, $container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			switch (type) {
				case "post_types":
					this.add_new_post_types_target($container, data);
					break;

				case "taxonomy":
					this.add_new_taxonomy_target($container, data);
					break;

				case "wp_posts":
					this.add_new_post_targets_picker($container, data);
					break;

				case "wp_pages":
					this.add_new_page_targets_picker($container, data);
					break;

				case "whole_site":
					this.add_new_whole_site_targets_picker($container, data);
					break;
			}
		},

		add_new_post_types_target: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcr-content-target-input urcrcl-roles-selector widefat",
					multiple: true,
					options: urcr_data.post_types,
					value: data.value,
				}),
				template = urcr_data.templates.target_content_template;

			urcr_utils.insert_template($container, template, {
				ID: true,
				input: inputHtml,
				label: urcr_data.labels.post_types,
				type: "post_types",
				tooltip: urcr_data.labels.post_types_tooltip,
			});
		},

		add_new_taxonomy_target: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var taxonomyInputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcr-content-target-input urcr-taxonomy-selector widefat",
					options: urcr_data.taxonomies,
					prepend_empty_option: true,
					data: {
						placeholder: "Select a taxonomy...",
					},
					value: data.taxonomy,
				}),
				terms = urcr_data.terms_list[data.taxonomy]
					? urcr_data.terms_list[data.taxonomy]
					: {},
				termInputHtml = "",
				template = urcr_data.templates.target_content_template;

			if (data.taxonomy && "" !== data.taxonomy) {
				termInputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcr-content-target-input urcr-terms-selector widefat",
					multiple: true,
					options: terms,
					data: {
						select2_class: "urcr-terms-selector-select2",
					},
					value: data.value,
				});
			}

			urcr_utils.insert_template($container, template, {
				ID: true,
				input: taxonomyInputHtml + termInputHtml,
				label: urcr_data.labels.taxonomy,
				type: "taxonomy",
				tooltip: urcr_data.labels.taxonomy_tooltip,
			});
		},

		add_new_post_targets_picker: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcr-content-target-input urcrcl-capabilities-selector widefat",
					multiple: true,
					options: urcr_data.posts,
					value: data.value,
				}),
				template = urcr_data.templates.target_content_template;

			urcr_utils.insert_template($container, template, {
				ID: true,
				input: inputHtml,
				label: urcr_data.labels.pick_posts,
				type: "wp_posts",
				tooltip: urcr_data.labels.pick_posts_tooltip,
			});
		},

		add_new_page_targets_picker: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});

			var inputHtml = urcr_utils.create_select_tag({
				className:
					"urcr-enhanced-select2 urcr-content-target-input urcrcl-capabilities-selector widefat",
				multiple: true,
				options: urcr_data.pages,
				value: data.value,
			});
			template = urcr_data.templates.target_content_template;

			urcr_utils.insert_template($container, template, {
				ID: true,
				input: inputHtml,
				label: urcr_data.labels.pick_pages,
				type: "wp_pages",
				tooltip: urcr_data.labels.pick_pages_tooltip,
			});
		},

		add_new_whole_site_targets_picker: function ($container, data) {
			data = urcr_utils.if_not_json_object(data, {});
			var inputHtml = urcr_utils.create_switch_tag({
				divClassName: "user-registration-switch ur-ml-auto",
				inputClassName:
					"user-registration-switch__control hide-show-check enabled",
				labelClassName: "urcr-enable-access-rule-label",
				default_checked: 'checked="checked"',
				disabled: 'disabled="true"',
				default: "Enabled",
				id: "urcr_whole_site_restriction",
			});
			template = urcr_data.templates.target_content_template;

			urcr_utils.insert_template($container, template, {
				ID: true,
				input: inputHtml,
				label: urcr_data.labels.whole_site,
				type: "whole_site",
				tooltip: urcr_data.labels.whole_site_tooltip,
			});
		},
	};

	// URCR rule action controller.
	var urcr_rule_action_controller = {
		init: function () {
			this.fill_options();
			this.init_rule_action_input_switcher();
		},

		/**
		 * Fill access rule action inputs with available options list.
		 * For example: User Registration forms list, Shortcodes list, etc.
		 */
		fill_options: function () {
			var ur_forms_list = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcr-input urcrra-ur-form-input",
					options: urcr_data.ur_forms,
					prepend_empty_option: true,
					data: {
						placeholder: urcr_data.labels.select_ur_form,
					},
					style: "width: 100% !important;",
				}),
				shortcodes_list = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcr-input urcrra-shortcode-input",
					options: urcr_data.shortcodes,
					prepend_empty_option: true,
					data: {
						placeholder: urcr_data.labels.select_ur_shortcode,
					},
					style: "width: 100% !important;",
				}),
				shortcode_args_input =
					'<input class="widefat urcrra-shortcode-args" type="text" placeholder="' +
					urcr_data.labels.enter_shortcode_args +
					'">',
				local_page_list = urcr_utils.create_select_tag({
					className:
						"urcr-enhanced-select2 urcr-input urcrra-redirect-to-local-page-input",
					options: urcr_data.pages,
					prepend_empty_option: true,
					data: {
						placeholder: urcr_data.labels.select_a_page,
					},
					style: "width: 100% !important;",
				});

			$(".urcrra-ur-form-input-container .urcr-body").html(ur_forms_list);
			$(".urcrra-shortcode-input-container .urcr-body").html(
				shortcodes_list + shortcode_args_input
			);
			$(".urcrra-redirect-to-local-page-input-container .urcr-body").html(
				local_page_list
			);
		},

		/**
		 * Intialize rule action input handler.
		 * Switch between different inputs when action type changes.
		 */
		init_rule_action_input_switcher: function () {
			$(document.body).on(
				"change",
				".urcr-rule-action-type-input",
				function () {
					urcr_rule_action_controller.updateToggleRuleActionInput(
						$(this).val()
					);
				}
			);
		},

		/**
		 * Show selected action type's input element and hide others.
		 *
		 * @param {String} action_type Type of action.
		 */
		updateToggleRuleActionInput: function (action_type) {
			var $action_input_container = null;

			switch (action_type) {
				case "message":
					$action_input_container = $(
						".urcrra-message-input-container"
					);
					break;

				case "redirect":
					$action_input_container = $(
						".urcrra-redirect-input-container"
					);
					break;

				case "redirect_to_local_page":
					$action_input_container = $(
						".urcrra-redirect-to-local-page-input-container"
					);
					break;

				case "ur-form":
					$action_input_container = $(
						".urcrra-ur-form-input-container"
					);
					break;

				case "shortcode":
					$action_input_container = $(
						".urcrra-shortcode-input-container"
					);
					break;
			}

			$(".urcr-rule-action-input-container.urcr-active").slideUp(400);
			$(".urcr-rule-action-input-container.urcr-active").removeClass(
				"urcr-active"
			);

			if ($action_input_container) {
				$action_input_container.slideDown(400);
				$action_input_container.addClass("urcr-active");
			}
		},
	};

	/**
	 * UX Controls like toggling widgets handler.
	 */
	var ux_controls = {
		init: function () {
			this.init_access_rule_state_label_toggler();
			this.init_toggle_animate_title_editor_handler();
			this.init_tabs_switcher();
			this.init_widgets_toggler();

			$(document.body).on("urcr_initializations_complete", function () {
				ux_controls.enable_constant_selection();
			});
		},

		/**
		 * Change label when Enabled/Disabled the access rule.
		 */
		init_access_rule_state_label_toggler: function () {
			$(document.body).on(
				"change",
				"#urcr-enable-access-rule",
				function () {
					if ($(this).is(":checked")) {
						$(".urcr-enable-access-rule-label").text(
							urcr_data.labels.enabled
						);
					} else {
						$(".urcr-enable-access-rule-label").text(
							urcr_data.labels.disabled
						);
					}
				}
			);
		},

		/**
		 * Animate and toggle title editor.
		 */
		init_toggle_animate_title_editor_handler: function () {
			$(document.body).on(
				"click",
				".user-registration-editable-title__icon",
				function () {
					var $input = $(this).siblings(
						".user-registration-editable-title__input"
					);

					if (!$input.hasClass("is-editing")) {
						$input.trigger("focus");
					}
					$input.toggleClass("is-editing");
				}
			);
		},

		/**
		 * It allows you to switch tabs.
		 */
		init_tabs_switcher: function () {
			$(document.body).on(
				"click",
				".urcr-nav-tab-wrapper .urcr-tab",
				function (e) {
					e.preventDefault();

					if (!$(this).is(".nav-tab-active")) {
						// Change active tab.
						$(
							".urcr-nav-tab-wrapper .urcr-tab.nav-tab-active"
						).removeClass("nav-tab-active");
						$(this).addClass("nav-tab-active");

						// Change active tab content.
						var tab_content_selector = $(this).data(
							"tab-content-selector"
						);

						if (tab_content_selector) {
							$(
								".urcr-tab-contents-wrapper .urcr-tab-content.urcr-tab-content-active"
							).slideUp(300);

							$(tab_content_selector)
								.removeAttr("hidden")
								.slideDown(300);

							$(
								".urcr-tab-contents-wrapper .urcr-tab-content.urcr-tab-content-active"
							).removeClass("urcr-tab-content-active");
							$(tab_content_selector).addClass(
								"urcr-tab-content-active"
							);
						}

						urcr_utils.init_all_enhanced_select();
					}
				}
			);
		},

		/**
		 * Toggle the body of a widget-like UI.
		 */
		init_widgets_toggler: function () {
			var prevented_tag_elements = [
				"select",
				"input",
				"button",
				"option",
			];

			$(document.body).on(
				"click",
				".urcr-settings-widget .urcr-settings-widget-header",
				function (e) {
					if (
						!prevented_tag_elements.includes(
							e.target.tagName.toLowerCase()
						) &&
						!$(e.target).is(".urcrcl-trash") &&
						!$(e.target).closest(".select2").length &&
						!$(e.target).is(".urbg-item")
					) {
						$(this)
							.closest(".urcr-settings-widget")
							.toggleClass("closed");
						urcr_utils.init_all_enhanced_select();
					}
				}
			);
		},

		/**
		 * Enable constant selection for a select tag. It will always show only one value as selected.
		 *
		 * Warning: This function needs to be called only when all the initializations are done because it should be
		 * called at the end of the 'change' event handlers chain.
		 */
		enable_constant_selection: function () {
			$(document.body).on(
				"change",
				".urcr-constant-selection-enabled",
				function () {
					$(this)
						.find(".urcr-logic-field-placeholder")
						.prop("selected", true);
				}
			);
		},
	};

	/**
	 * API calling functions such as creating an access rule, saving it, etc.
	 */
	var urcr_api = {
		/**
		 * Create a Content Access Rule.
		 *
		 * @param {jQuery} $rule_container Container element containing rule data.
		 */
		create_rule: function ($rule_container) {
			if (!urcr_utils.validate_access_rule_form().result) {
				return;
			}

			$rule_container = urcr_utils.if_empty(
				$rule_container,
				$(".urcr-content-access-rule-creator-form")
			);

			var rule_data = urcr_extractor.extract_rule_data($rule_container),
				$save_button = $(".urcr-save-rule");

			urcr_utils.append_spinner($save_button);
			urcr_utils.toggleSaveButtons(true);

			this.send_data(
				{
					action: "urcr_create_access_rule",
					title: $(".urcr-content-access-rule-title-input").val(),
					access_rule_data: JSON.stringify(rule_data),
				},
				{
					success: function (response) {
						urcr_data.rule_id = response.data.rule_id;

						urcr_utils.updateBrowserLocationURL();
						$save_button.text(urcr_data.labels.save_rule);
						$("h1.wp-heading-inline").text(
							urcr_data.labels.edit_access_rule
						);
						$(".urcr-save-rule-as-draft").remove();

						if (response.success) {
							urcr_utils.show_success_message(
								response.data.message
							);
						} else {
							urcr_utils.show_failure_message(
								response.data.message
							);
						}
					},
					failure: function (xhr, statusText) {
						urcr_utils.show_failure_message(
							urcr_data.labels.network_error +
								" (" +
								statusText +
								")"
						);
					},
					complete: function () {
						urcr_utils.remove_spinner($save_button);
						urcr_utils.toggleSaveButtons(false);
					},
				}
			);
		},

		/**
		 * Save a Content Access Rule.
		 *
		 * @param {jQuery} $rule_container Container element containing rule data.
		 */
		save_rule: function ($rule_container) {
			if (!urcr_utils.validate_access_rule_form().result) {
				return;
			}

			if (urcr_data.rule_id && "" !== urcr_data.rule_id) {
				$rule_container = urcr_utils.if_empty(
					$rule_container,
					$(".urcr-content-access-rule-creator-form")
				);

				var rule_data =
						urcr_extractor.extract_rule_data($rule_container),
					$save_button = $(".urcr-save-rule"),
					proceed = function () {
						urcr_utils.append_spinner($save_button);
						urcr_utils.toggleSaveButtons(true);

						this.send_data(
							{
								action: "urcr_save_access_rule",
								rule_id: urcr_data.rule_id,
								title: $(
									".urcr-content-access-rule-title-input"
								).val(),
								access_rule_data: JSON.stringify(rule_data),
							},
							{
								success: function (response) {
									$(".urcr-save-rule-as-draft").remove();
									$save_button.text(
										urcr_data.labels.save_rule
									);

									if (response.success) {
										urcr_utils.show_success_message(
											response.data.message
										);
									} else {
										urcr_utils.show_failure_message(
											response.data.message
										);
									}
								},
								failure: function (xhr, statusText) {
									urcr_utils.show_failure_message(
										urcr_data.labels.network_error +
											" (" +
											statusText +
											")"
									);
								},
								complete: function () {
									urcr_utils.remove_spinner($save_button);
									urcr_utils.toggleSaveButtons(false);
								},
							}
						);
					}.bind(this);

				if (urcr_data.is_draft) {
					Swal.fire({
						title: urcr_data.labels.are_you_sure,
						text: urcr_data.labels.publish_draft_warning,
						icon: "warning",
						showCancelButton: true,
						confirmButtonColor: "#3085d6",
						cancelButtonColor: "#d33",
						confirmButtonText: urcr_data.labels.publish,
					}).then(function (result) {
						if (result.value) {
							urcr_data.is_draft = false;
							proceed();
						}
					});
				} else {
					proceed();
				}
			} else if (DEBUG) {
				console.error(
					"Rule cannot be saved without an ID",
					urcr_data.rule_id
				);
			}
		},

		/**
		 * Save a Content Access Rule as draft.
		 *
		 * @param {jQuery} $rule_container Container element containing rule data.
		 */
		save_rule_as_draft: function ($rule_container) {
			if (!urcr_utils.validate_access_rule_form().result) {
				return;
			}

			$rule_container = urcr_utils.if_empty(
				$rule_container,
				$(".urcr-content-access-rule-creator-form")
			);

			var rule_data = urcr_extractor.extract_rule_data($rule_container),
				$save_as_draft_button = $(".urcr-save-rule-as-draft"),
				$save_button = $(".urcr-save-rule");

			urcr_utils.append_spinner($save_as_draft_button);
			urcr_utils.toggleSaveButtons(true);

			this.send_data(
				{
					action: "urcr_save_access_rule_as_draft",
					rule_id:
						urcr_data.rule_id && "" !== urcr_data.rule_id
							? urcr_data.rule_id
							: "",
					title: $(".urcr-content-access-rule-title-input").val(),
					access_rule_data: JSON.stringify(rule_data),
				},
				{
					success: function (response) {
						urcr_data.rule_id = response.data.rule_id;

						urcr_utils.updateBrowserLocationURL();
						$save_button.text(urcr_data.labels.publish_rule);
						$save_as_draft_button.text(urcr_data.labels.save_draft);
						urcr_data.is_draft = true;

						if (response.success) {
							urcr_utils.show_success_message(
								response.data.message
							);
						} else {
							urcr_utils.show_failure_message(
								response.data.message
							);
						}
					},
					failure: function (xhr, statusText) {
						urcr_utils.show_failure_message(
							urcr_data.labels.network_error +
								" (" +
								statusText +
								")"
						);
					},
					complete: function () {
						urcr_utils.remove_spinner($save_as_draft_button);
						urcr_utils.toggleSaveButtons(false);
					},
				}
			);
		},

		/**
		 * Send data to the backend API.
		 *
		 * @param {JSON} data Data to send.
		 * @param {JSON} callbacks Callbacks list.
		 */
		send_data: function (data, callbacks) {
			var success_callback =
					"function" === typeof callbacks.success
						? callbacks.success
						: function () {},
				failure_callback =
					"function" === typeof callbacks.failure
						? callbacks.failure
						: function () {},
				beforeSend_callback =
					"function" === typeof callbacks.beforeSend
						? callbacks.beforeSend
						: function () {},
				complete_callback =
					"function" === typeof callbacks.complete
						? callbacks.complete
						: function () {};

			// Inject default data.
			if (!data._wpnonce && urcr_data) {
				data._wpnonce = urcr_data._nonce;
			}

			$.ajax({
				type: "post",
				dataType: "json",
				url: urcr_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback,
			});
		},
	};
})(jQuery, window.urcr_localized_data, window.urcr_localized_data.URCR_DEBUG);
