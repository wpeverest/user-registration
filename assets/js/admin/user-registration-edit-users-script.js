/* global  user_registration_params */
jQuery(function ($) {
	if (UR_Snackbar) {
		var snackbar = new UR_Snackbar();
	}
	var l10n = urUsersl10n,
		available_field = [],
		required_fields = l10n.form_required_fields;
	var UREditUsers = {
		init: function () {
			UREditUsers.initUIBindings();
			//load password toggle if password field is available
			if (
				$(
					"#user-registration-edit-user-body  #user_registration_user_pass"
				).length > 0
			) {
				this.hide_password_input();
			}
			//add mask to all input mask classes
			$(".ur-masked-input").inputmask("mask") ||
				$(".ur-masked-input").inputmask();
		},
		/**
		 * Load password toggle in password input
		 *
		 */
		hide_password_input: function () {
			$("#user_registration_user_pass_field input").hide();
			var change_password_btn =
				'<button type="button" class="button btn-primary set-new-pass-btn">' +
				l10n.edit_user_set_new_password +
				"</button>";
			$(
				"#user-registration-edit-user-body  .field-user_pass .input-wrapper"
			).append(change_password_btn);
		},
		/**
		 * toggle user password input visibility
		 */
		toggle_password_input_visibility: function () {
			$("#user_registration_user_pass_field input").val("").show();
			$(".set-new-pass-btn").hide();
			var hide_show_password =
				'<div class="hide-show-password">' +
				'<span class="dashicons dashicons-visibility hide-show-btn"></span>' +
				"</div>";
			$(
				"#user_registration_user_pass_field .password-input-group"
			).append(hide_show_password);
		},

		/**
		 * Show Success message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_success_message: function (message) {
			if (snackbar && message) {
				if ($.isArray(message)) {
					// Loop through each error in the message array
					message.forEach(function (errorObj) {
						Object.keys(errorObj).forEach(function (key) {
							// Only show error if it's not a boolean (e.g., skip 'individual')
							if (typeof errorObj[key] === "string") {
								snackbar.add({
									type: "success",
									message: errorObj[key],
									duration: 5,
									icon: "exclamation"
								});
							}
						});
					});
				} else {
					snackbar.add({
						type: "success",
						message: message,
						duration: 5,
						icon: "exclamation"
					});
				}
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
			if (snackbar && message) {
				if ($.isArray(message)) {
					// Loop through each error in the message array
					message.forEach(function (errorObj) {
						Object.keys(errorObj).forEach(function (key) {
							// Only show error if it's not a boolean (e.g., skip 'individual')
							if (typeof errorObj[key] === "string") {
								snackbar.add({
									type: "failure",
									message: errorObj[key],
									duration: 6
								});
							}
						});
					});
				} else {
					snackbar.add({
						type: "failure",
						message: message,
						duration: 6
					});
				}
				return true;
			}
			return false;
		},

		/**
		 * Bind UI changes.
		 */
		initUIBindings: function () {
			/**
			 * Save changes for edit profile.
			 */
			$(".save_user_details").on("click", function (event) {
				var $this = $(this);

				UREditUsers.edit_profile_event($this);
				$this.submit();
			});

			/**
			 * Hide show password when clicked
			 */
			$(document).on("click", ".hide-show-btn", function (e) {
				e.preventDefault();
				e.stopPropagation();
				var passInput = $("#user_registration_user_pass");
				$(this).toggleClass("dashicons-visibility dashicons-hidden");
				passInput.prop("type", function (i, type) {
					return type === "password" ? "text" : "password";
				});
			});

			$(document).on("click", ".set-new-pass-btn", function () {
				UREditUsers.toggle_password_input_visibility();
			});
		},
		/*
		 * Retrieves fieldwise data from a given field.
		 *
		 * @param {object} field - The field object to retrieve data from.
		 * @return {object} The fieldwise data, including the field name, value, type, and label.
		 */
		get_fieldwise_data: function (field) {
			var formwise_data = {};

			var node_type = field.get(0).tagName.toLowerCase();
			var field_name =
				"undefined" !== field.attr("name")
					? field.attr("name")
					: "null";
			var phone_id = [];
			if (field.attr("name") !== undefined && field.attr("name") !== "") {
				formwise_data.field_name = field.attr("name");
				formwise_data.field_name = formwise_data.field_name.replace(
					"[]",
					""
				);

				if ($(field).closest(".ur-repeater-row").length > 0) {
					if ($(field).closest(".field-multi_select2").length > 0) {
						formwise_data.field_name =
							formwise_data.field_name.slice(0, -2);
					}

					if ($(field).closest(".field-file").length > 0) {
						formwise_data.field_name = $(field)
							.closest(".field-file")
							.attr("data-ref-id");
					}
				}
			} else {
				formwise_data.field_name = "";
			}

			$(".field-phone, .field-billing_phone").each(function () {
				var phone_field_id = $(this).find(".form-row").attr("id");
				// Check if smart phone field is enabled.
				if (
					$(this)
						.find(".form-row")
						.find("#" + phone_field_id)
						.hasClass("ur-smart-phone-field")
				) {
					phone_id.push($(this).find(".form-row").attr("id"));
				}
			});
			var field_type =
				"undefined" !== field.attr("type")
					? field.attr("type")
					: "null";

			var textarea_type = field.get(0).className.split(" ")[0];

			formwise_data.value = "";

			switch (node_type) {
				case "input":
					var checked_value = new Array();
					switch (field_type) {
						case "checkbox":
							if (
								!field.closest(".field-privacy_policy").length >
								0
							) {
								if (field.prop("checked")) {
									checked_value.push(field.val());
									formwise_data.value =
										JSON.stringify(checked_value);
								} else {
									formwise_data.value = "";
								}
							} else {
								formwise_data.value = field.prop("checked")
									? field.val()
									: "";

								formwise_data.field_name = field
									.closest(".field-privacy_policy")
									.data("ref-id");
							}
							break;
						case "radio":
							formwise_data.value = field.prop("checked")
								? field.val()
								: "";
							formwise_data.field_name = field
								.closest(".field-privacy_policy")
								.data("ref-id");
							break;
						default:
							formwise_data.value = field.val();
					}

					if (ur_includes(phone_id, field_name)) {
						formwise_data.value = field
							.siblings('input[type="hidden"]')
							.val();
					}
					break;
				case "select":
					formwise_data.value = field.val();
					break;
				case "textarea":
					switch (textarea_type) {
						case "wysiwyg":
							tinyMCE.triggerSave();
							formwise_data.value = field.val();
							break;
						default:
							formwise_data.value = field.val();
					}
					break;
				default:
			}

			$(document).trigger("user_registration_frontend_form_data_render", [
				field,
				formwise_data
			]);
			formwise_data.field_type =
				"undefined" !== field.eq(0).attr("type")
					? field.eq(0).attr("type")
					: "null";
			var data_field_type = field.attr("data-field-type");

			if (data_field_type === "hidden") {
				formwise_data.field_type = field_type;
			}
			if (field.attr("data-label") !== undefined) {
				formwise_data.label = field.attr("data-label");
			} else if (
				field.prev().length &&
				field.prev().get(0).tagName.toLowerCase() === "label"
			) {
				formwise_data.label = field.prev().text();
			} else {
				formwise_data.label = formwise_data.field_type;
			}

			if (
				$.inArray(
					formwise_data.field_name,
					required_fields.join(",").trim()
				) >= 0
			) {
				available_field.push(formwise_data.field_name);
			}

			return formwise_data;
		},
		/*
		 * Retrieves form data from the given form ID.
		 *
		 * @param {string} form_id - The ID of the form to retrieve data from.
		 * @return {array} An array of form data.
		 */
		get_form_data: function (form_id) {
			var $this = $("form.user-registration-EditProfileForm");
			if (
				form_id === $this.closest(".ur-frontend-form").attr("id") ||
				$(".ur-frontend-form")
					.find("form.edit-profile")
					.hasClass("user-registration-EditProfileForm")
			) {
				var this_instance = this;
				var form_data = [];
				var frontend_field = UREditUsers.separate_form_handler("");

				var repeater_field_data = {};

				$this
					.closest("form")
					.find(".ur-repeater-row")
					.each(function () {
						var fieldName = $(this)
							.closest(".ur-repeater-row")
							.data("repeater-field-name");
						var rowName =
							"row_" +
							$(this)
								.closest(".ur-repeater-row")
								.data("repeater-row");

						if (
							$(this).closest(
								".user-registration-EditProfileForm"
							).length > 0
						) {
							fieldName = "user_registration_" + fieldName;
						}

						if (!repeater_field_data[fieldName]) {
							repeater_field_data[fieldName] = {
								field_name: fieldName,
								field_type: "repeater",
								value: {},
								label: $(this)
									.closest(".ur-repeater-row")
									.find(".ur-repeater-label")
									.find(".ur-label")
									.text(),
								extra_params: {
									field_key: "repeater",
									label: $(this)
										.closest(".ur-repeater-row")
										.find(".ur-repeater-label")
										.find(".ur-label")
										.text()
								}
							};
						}

						if (!repeater_field_data[fieldName]["value"][rowName]) {
							repeater_field_data[fieldName]["value"][rowName] =
								[];
						}
					});

				var multi_value_field = new Array();
				$.each(frontend_field, function () {
					var field_name = $(this).attr("name");
					var field_type = $(this).attr("type");
					var single_field = UREditUsers.separate_form_handler(
						'[name="' + field_name + '"]'
					);
					var selection_fields_array = ["radio"];
					var fieldName = $(this)
						.closest(".ur-repeater-row")
						.data("repeater-field-name");
					var data_field_type = $(this).attr("data-field-type");

					if (data_field_type === "hidden") {
						field_type = "hidden";
					}
					if (
						$(this).closest(".user-registration-EditProfileForm")
							.length > 0
					) {
						fieldName = "user_registration_" + fieldName;
					}

					var rowName =
						"row_" +
						$(this)
							.closest(".ur-repeater-row")
							.data("repeater-row");

					if (
						(single_field.length < 2 ||
							single_field.closest(".ur-repeater-row").length >
								0) &&
						$.inArray(field_type, selection_fields_array) < 0
					) {
						var single_data = this_instance.get_fieldwise_data(
							$(this)
						);

						var invite_code =
							document.querySelector(".field-invite_code");

						if ("invite_code" === single_data.field_name) {
							if ("none" !== invite_code.style.display) {
								form_data.push(single_data);
							}
						} else {
							if (
								$(this).closest(".ur-repeater-row").length > 0
							) {
								if (
									$(this)
										.closest(".form-row")
										.find(
											"*[name='" +
												$(this).attr("name") +
												"']"
										).length < 2 ||
									"range" === $(this).attr("type") ||
									$(this).hasClass("ur-smart-phone-field")
								) {
									repeater_field_data[fieldName]["value"][
										rowName
									].push(single_data);
								} else {
									if (
										multi_value_field.indexOf(
											single_data.field_name + "[]"
										) === -1
									) {
										multi_value_field.push(
											single_data.field_name + "[]"
										);
									}
								}
							} else {
								form_data.push(single_data);
							}
						}
					} else {
						if ($.inArray(field_name, multi_value_field) < 0) {
							multi_value_field.push(field_name);
						}
					}
				});

				for (
					var multi_start = 0;
					multi_start < multi_value_field.length;
					multi_start++
				) {
					var field = UREditUsers.separate_form_handler(
						'[name="' + multi_value_field[multi_start] + '"]'
					);
					var node_type = field.get(0).tagName.toLowerCase();

					var field_type =
						"undefined" !== field.eq(0).attr("type")
							? field.eq(0).attr("type")
							: "null";

					var field_value = new Array();

					var repeater_field_value = {};
					$.each(field, function () {
						var this_field = $(this);

						var this_field_value = "";

						switch (this_field.get(0).tagName.toLowerCase()) {
							case "input":
								switch (field_type) {
									case "checkbox":
									case "radio":
										this_field_value = this_field.prop(
											"checked"
										)
											? this_field.val()
											: "";
										break;
									default:
										this_field_value = this_field.val();
								}
								break;
							case "select":
								this_field_value = this_field.val();
								break;
							case "textarea":
								this_field_value = this_field.val();
								break;
							default:
						}

						if (this_field_value !== "") {
							if (
								this_field.closest(".ur-repeater-row").length >
								0
							) {
								if (
									this_field.closest(".field-radio").length >
									0
								) {
									repeater_field_value[
										this_field.attr("data-id")
									] = this_field_value;
								} else {
									if (
										"undefined" ===
										typeof repeater_field_value[
											this_field.attr("data-id")
										]
									) {
										repeater_field_value[
											this_field.attr("data-id")
										] = new Array();
									}
									repeater_field_value[
										this_field.attr("data-id")
									].push(this_field_value);
								}
							} else {
								field_value.push(this_field_value);
							}
						}
					});

					if (field_type == "checkbox") {
						if ("" !== l10n.is_payment_compatible) {
							if (
								field.eq(0).attr("data-field") ==
								"multiple_choice"
							) {
								$(document).trigger(
									"user_registration_frontend_multiple_choice_data_filter",
									[field_value, field]
								);
								field_value = field
									.closest(".field-multiple_choice")
									.data("payment-value");

								var field_value_json =
									JSON.stringify(field_value);
							} else {
								var field_value_json =
									JSON.stringify(field_value);
							}
						} else {
							if (
								field.eq(0).attr("data-field") ==
								"multiple_choice"
							) {
								var multi_choice = field_value;
								var field_value_json = 0;
								for (var i = 0; i < multi_choice.length; i++) {
									field_value_json += multi_choice[i] << 0;
								}
							} else {
								var field_value_json =
									JSON.stringify(field_value);
							}
						}
					} else if (field_type == "radio") {
						if ("" !== urUsersl10n.is_payment_compatible) {
							if (
								field.eq(0).attr("data-field") ==
								"subscription_plan"
							) {
								$(document).trigger(
									"user_registration_frontend_subscription_plan_data_filter",
									[field_value, field]
								);
								selectedSubscriptionPlan = field
									.closest(".field-subscription_plan")
									.find(
										'input[name="subscription_plan[]"]:checked'
									);

								if (selectedSubscriptionPlan.length > 0) {
									// Get the data attribute value
									var dataValue =
										selectedSubscriptionPlan.data("value");
									var field_value_json = JSON.stringify(
										dataValue +
											":" +
											selectedSubscriptionPlan.val()
									);
								}
							} else {
								var field_value_json = field_value[0];
							}
						} else {
							var field_value_json = field_value[0];
						}
					} else {
						var field_value_json = field.val();
					}

					var single_form_field_name = multi_value_field[multi_start];
					single_form_field_name = single_form_field_name.replace(
						"[]",
						""
					);
					var field_data = {
						value: field_value_json,
						field_type: field_type,
						label: field.eq(0).attr("data-label"),
						field_name: single_form_field_name
					};

					if (Object.keys(repeater_field_value).length > 0) {
						var field_detail = new Array();

						$.each(repeater_field_value, function (key, value) {
							key =
								$("[name='" + key + "']").length < 1 &&
								key.indexOf("[]") === -1
									? key + "[]"
									: key;
							var row_id = $('[name="' + key + '"]')
								.closest(".ur-repeater-row")
								.data("repeater-row");
							var repeater_value = Object.assign({}, field_data);

							repeater_value.value =
								"string" === typeof value
									? value
									: JSON.stringify(value);

							repeater_value.field_name =
								single_form_field_name.slice(0, -2);

							var current_repeater_field_name =
								"undefined" ===
								typeof repeater_field_data[
									$("[name='" + key + "']")
										.closest(".ur-repeater-row")
										.data("repeater-field-name")
								]
									? "user_registration_" +
									  $("[name='" + key + "']")
											.closest(".ur-repeater-row")
											.data("repeater-field-name")
									: $("[name='" + key + "']")
											.closest(".ur-repeater-row")
											.data("repeater-field-name");
							repeater_field_data[current_repeater_field_name][
								"value"
							]["row_" + row_id].push(repeater_value);
						});
					} else {
						form_data.push(field_data);
					}
				}

				Object.keys(repeater_field_data).forEach(function (field_key) {
					if ($("input[name='" + field_key + "'").length > 0) {
						$("input[name='" + field_key + "'").val(
							JSON.stringify(repeater_field_data[field_key])
						);
					}
				});

				if (Object.keys(repeater_field_data).length > 0) {
					$.merge(form_data, Object.values(repeater_field_data));
				}
				$(document).trigger(
					"user_registration_frontend_form_data_filter",
					[form_data]
				);
				return form_data;
			}
		},
		/**
		 * Retrieves a specific form field based on the given element.
		 *
		 * @param {string} element - The element to find the corresponding form field for.
		 * @return {object} The jQuery object of the form field.
		 */
		separate_form_handler: function (element) {
			var $this = $("form.user-registration-EditProfileForm");

			var field = "";

			// Check if the form is edit-profile form.
			if (
				$(".ur-frontend-form")
					.find("form.edit-profile")
					.hasClass("user-registration-EditProfileForm")
			) {
				field = $this
					.find(".user-registration-edit-user-form-details")
					.find(".ur-edit-profile-field" + element);
			} else {
				field = $this
					.closest(".ur-frontend-form")
					.find(".ur-form-grid")
					.find(".ur-frontend-field" + element);
			}

			return field;
		},

		/**
		 * Handles the edit profile event by validating the form,
		 * sending an AJAX request to update the user's profile details,
		 * and handling the response.
		 *
		 * @return {void}
		 */
		edit_profile_event: function (button) {
			$("form.user-registration-EditProfileForm").on(
				"submit",
				function (event) {
					event.preventDefault();
					event.stopImmediatePropagation();

					var $this = $(this);

					// Validator messages.
					$.extend($.validator.messages, {
						required: l10n.message_required_fields,
						url: l10n.message_url_fields,
						email: l10n.message_email_fields,
						number: l10n.message_number_fields
					});

					if (!$this.valid()) {
						return false;
					}

					button.prop("disabled", true);

					var form_data;
					var form_nonce = "0";

					form_data = UREditUsers.get_form_data(
						$(".user-registration-EditProfileForm ").data("form-id")
					);

					try {
						// Handle profile picture
						var profile_picture_url = $("#profile_pic_url").val();

						form_data.push({
							value: profile_picture_url,
							field_name: "user_registration_profile_pic_url"
						});

						form_data = JSON.stringify(form_data);
					} catch (ex) {
						form_data = "";
					}

					var data = {
						action: "user_registration_update_profile_details",
						security: l10n.user_registration_edit_user_nonce,
						form_data: form_data,
						user_id: $this.data("user-id"),
						is_admin_user: true
					};

					$(document).trigger(
						"user_registration_frontend_before_edit_profile_submit",
						[data, $this]
					);

					$this
						.find(".save_user_details")
						.find("span")
						.addClass("ur-spinner");

					$.ajax({
						type: "POST",
						url: l10n.ajax_url,
						dataType: "JSON",
						data: data,
						complete: function (ajax_response) {
							$this
								.find("span.ur-spinner")
								.removeClass("ur-spinner");
							$this
								.closest(".user-registration")
								.find(".user-registration-error")
								.remove();
							$this
								.closest(".user-registration")
								.find(".user-registration-message")
								.remove();

							var message = $('<ul class=""/>');
							var type = "error";

							try {
								var response = JSON.parse(
									ajax_response.responseText
								);
								if (
									typeof response.success !== "undefined" &&
									response.success === true
								) {
									type = "success";
								}
								var individual_field_message = false;
								if (typeof response.data.message === "object") {
									$.each(
										response.data.message,
										function (index, message_value) {
											if (
												message_value.hasOwnProperty(
													"individual"
												)
											) {
												var $field_id = [];
												$.each(
													$this
														.find(".ur-form-row")
														.find(".ur-field-item")
														.find(
															".ur-edit-profile-field"
														),
													function (index) {
														var $this = $(this);
														var $id =
															$this.attr("id");
														$field_id.push($id);
													}
												);

												$.each(
													message_value,
													function (index, value) {
														index =
															index.indexOf(
																"user_registration_"
															) === -1
																? "user_registration_" +
																  index
																: index;

														if (
															$field_id.includes(
																index
															)
														) {
															var error_message =
																'<label id="' +
																index +
																"-error" +
																'" class="user-registration-error" for="' +
																index +
																'">' +
																value +
																"</label>";
															var wrapper =
																$this.find(
																	".ur-form-row"
																);

															wrapper = wrapper
																.find(
																	".ur-field-item"
																)
																.find(
																	"input[id='" +
																		index +
																		"'], textarea[id='" +
																		index +
																		"']"
																);
															wrapper
																.closest(
																	".form-row"
																)
																.append(
																	error_message
																);
															individual_field_message = true;
														}
													}
												);
											} else {
												message.append(
													"<li>" +
														message_value +
														"</li>"
												);
											}
										}
									);
								} else {
									message.append(
										"<li>" + response.data.message + "</li>"
									);
									if (
										undefined !==
										response.data.userEmailPendingMessage
									) {
										$(
											".user-registration-info.user-email-change-update-notice"
										).remove();
										UREditUsers.show_success_message(
											response.data.userEmailUpdateMessage
										);

										if (
											$(
												"input#user_registration_user_email"
											).next("div.email-updated").length
										) {
											$(
												"input#user_registration_user_email"
											)
												.next("div.email-updated")
												.remove();
										}
										$(
											response.data
												.userEmailPendingMessage
										).insertAfter(
											$(
												"input#user_registration_user_email"
											)
										);
										$(
											"input#user_registration_user_email"
										).val(response.data.oldUserEmail);
									}
								}
							} catch (e) {
								message.append("<li>" + e.message + "</li>");
							}

							if (!individual_field_message) {
								if (type === "error") {
									UREditUsers.show_failure_message(
										response.data.message
									);
								} else {
									UREditUsers.show_success_message(
										response.data.message
									);
								}
							}
							// Add trigger to handle functionalities that may be needed after edit-profile ajax submission submissions.
							$(document).trigger(
								"user_registration_edit_profile_after_ajax_complete",
								[ajax_response, $this]
							);
							button.prop("disabled", false);

							// Scroll yo the top on ajax submission complete.
							$(window).scrollTop(
								$(".user-registration").position()
							);
						}
					}).fail(function () {
						UREditUsers.show_failure_message(
							l10n.ajax_form_submit_error
						);
						button.prop("disabled", false);
						return;
					});
				}
			);
		}
	};

	$(document).ready(function () {
		if ($("#user-registration-edit-user-body").length) {
			UREditUsers.init();
		}
	});
});

/**
 * Checks if an array includes a specific item.
 *
 */
function ur_includes(arr, item) {
	if (Array.isArray(arr)) {
		for (var i = 0; i < arr.length; i += 1) {
			if (arr[i] === item) {
				return true;
			}
		}
	}
	return false;
}
