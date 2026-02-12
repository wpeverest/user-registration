/* global user_registration_settings_params */
(function ($) {
	var LoginBuilderSettings = {
		init: function () {
			LoginBuilderSettings.init_form_builder();
			$(document).on("click", ".clickable-login-fields", function () {
				LoginBuilderSettings.handle_selected_item($(this));
			});
			$(document).on("click", ".ur-toggle-heading", function () {
				$(this).toggleClass("closed");
				$(this)
					.parent(".user-registration-field-option-group")
					.toggleClass("closed")
					.toggleClass("open");
				var field_list = $(this).find(" ~ .ur-registered-list")[0];
				$(field_list).slideToggle();
				// For `Field Options` section
				$(this).siblings(".ur-toggle-content").stop().slideToggle();
			});
			$(document).on(
				"click",
				'.user-registration-login-form-container ul.ur-tab-lists li[aria-controls="ur-tab-field-options"]',
				function () {
					$("form#ur-field-settings").hide();
					$(".ur-login-form-wrapper").show();
				}
			);
			$(document).on(
				"click",
				'.user-registration-login-form-container ul.ur-tab-lists li[aria-controls="ur-tab-login-form-settings"]',
				function () {
					$(".ur-login-form-wrapper").hide();
					$("form#ur-field-settings").show();
				}
			);
			$('.clickable-login-fields[data-field="username"]').trigger(
				"click"
			);

			$('input[name^="user_registration_hide_label_"]')
				.on("change", function () {
					LoginBuilderSettings.hide_show_field_label($(this));
				})
				.each(function () {
					LoginBuilderSettings.hide_show_field_label($(this));
				});
		},
		init_form_builder: function () {
			$(".ur-tabs .ur-tab-lists").on("click", "a.nav-tab", function () {
				$(".ur-tabs .ur-tab-lists")
					.find("a.nav-tab")
					.removeClass("active");
				$(this).addClass("active");
			});
			$(".ur-tabs").tabs();
			$(".ur-tabs").find("a").eq(0).trigger("click", ["triggered_click"]);
		},
		handle_selected_item: function (selected_item) {
			var clickable_fields = $(".clickable-login-fields"),
				all_settings = $("#ur-tab-field-options div[data-field-key]"),
				selected_field = selected_item.data("field");
			clickable_fields.removeClass("active");

			selected_item.addClass("active");
			all_settings.each(function () {
				$(this).addClass("ur-d-none");
				if ($(this).data("field-key") === selected_field) {
					$(this).removeClass("ur-d-none");
				}
			});
		},
		hide_show_field_label: function (selected_item) {
			var id = (selected_item.attr("id") || "").replace(
				"user_registration_hide_label_",
				""
			);
			var fieldMap = { password: "password" };
			var field_name = fieldMap[id] || "username";
			$("#ur-frontend-form")
				.find('[data-field="' + field_name + '"] label')
				.show();
			if (selected_item.is(":checked")) {
				$("#ur-frontend-form")
					.find('[data-field="' + field_name + '"] label')
					.hide();
			}
		}
	};
	LoginBuilderSettings.init();
	// Function to handle changes in the premium sidebar.
	$(document).ready(function () {
		init_login_form_settings();

		if (ur_login_form_params.is_login_settings_page) {
			$(window).on("keydown", function (event) {
				if (event.ctrlKey || event.metaKey) {
					if (
						"s" ===
							String.fromCharCode(event.which).toLowerCase() ||
						83 === event.which
					) {
						event.preventDefault();
						ur_save_login_form_settings();
						return false;
					}
				}
			});

			var ur_submenu = $("#toplevel_page_user-registration").find(
				".wp-submenu"
			);
			ur_submenu
				.find(
					'li a[href="admin.php?page=user-registration-login-forms"]'
				)
				.addClass("current");
		}

		var URLoginFormBuilder = {
			ur_embed_form: function ($this) {
				// Use global form builder localization if available.
				var fb = window.ur_login_form_params || {};

				var data = {
					action: "user_registration_embed_page_list",
					security: fb.ur_embed_page_list || ""
				};

				$.ajax({
					url: fb.ajax_url,
					data: data,
					type: "POST",
					beforeSend: function () {
						var spinner =
							'<span class="ur-spinner is-active"></span>';
						$this.append(spinner);
						$(".ur-notices").remove();
					},
					success: function (response) {
						$this.find(".ur-spinner").remove();

						function showInitialAlert() {
							var modelContent =
								'<div class=""><p>' +
								(fb.i18n_admin &&
								fb.i18n_admin.i18n_embed_description
									? fb.i18n_admin.i18n_embed_description
									: "") +
								"</p></div>";

							Swal.fire({
								icon: "info",
								title:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_form_title
										? fb.i18n_admin.i18n_embed_form_title
										: "",
								html: modelContent,
								showCancelButton: true,
								confirmButtonText:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_to_existing_page
										? fb.i18n_admin
												.i18n_embed_to_existing_page
										: "Embed to existing page",
								cancelButtonText:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_to_new_page
										? fb.i18n_admin.i18n_embed_to_new_page
										: "Embed to new page",
								showCloseButton: true,
								customClass:
									"user-registration-swal2-modal  user-registration user-registration-swal2-modal--center user-registrationswal2-icon-content-info swal2-show"
							}).then(function (result) {
								var form_id = $this.attr("data-form_id");

								if (result.isConfirmed) {
									showExistingPageSelection(
										response,
										form_id
									);
								} else if (
									result.dismiss === Swal.DismissReason.cancel
								) {
									showCreateNewPageForm(form_id);
								}
							});
						}

						function showExistingPageSelection(response, form_id) {
							var select_start =
								'<div class="ur-embed-select-existing-page-container"><p>' +
								(fb.i18n_admin &&
								fb.i18n_admin
									.i18n_embed_existing_page_description
									? fb.i18n_admin
											.i18n_embed_existing_page_description
									: "") +
								'</p><select style="width:100%; line-height:30px;" name="ur-embed-select-existing-page-name" id="ur-embed-select-existing-page-name">';
							var option =
								"<option disabled selected>Select Page</option>";
							(response.data || []).forEach(function (page) {
								option +=
									'<option data-id="' +
									page.ID +
									'" value="' +
									page.ID +
									'">' +
									page.post_title +
									"</option>";
							});
							var select_end = "</select>";

							modelContent = select_start + option + select_end;
							Swal.fire({
								icon: "info",
								title:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_form_title
										? fb.i18n_admin.i18n_embed_form_title
										: "",
								html: modelContent,
								showCloseButton: true,
								showCancelButton: true,
								cancelButtonText:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_go_back_btn
										? fb.i18n_admin.i18n_embed_go_back_btn
										: "Go Back",
								confirmButtonText:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_lets_go_btn
										? fb.i18n_admin.i18n_embed_lets_go_btn
										: "Let's go",
								customClass:
									"user-registration-swal2-modal  user-registration user-registration-swal2-modal--center swal2-show"
							}).then(function (result) {
								if (result.isDismissed) {
									showInitialAlert();
								} else if (result.isConfirmed) {
									var page_id = $(
										"#ur-embed-select-existing-page-name"
									).val();

									var data = {
										action: "user_registration_embed_form_action",
										security: fb.ur_embed_action || "",
										page_id: page_id,
										form_id: form_id,
										is_login: "yes"
									};
									$.ajax({
										url: fb.ajax_url,
										type: "POST",
										data: data,
										success: function (response) {
											if (response.success) {
												window.location = response.data;
											}
										}
									});
								}
							});
						}

						function showCreateNewPageForm(form_id) {
							var description =
								'<div class="ur-embed-new-page-container"><p>' +
								(fb.i18n_admin &&
								fb.i18n_admin.i18n_embed_new_page_description
									? fb.i18n_admin
											.i18n_embed_new_page_description
									: "") +
								"</p>";
							var page_name =
								'<div style="width: 100%"><input style="width:100%" type="text" name="page_title" /></div>';

							modelContent = description + page_name;
							Swal.fire({
								icon: "info",
								title:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_form_title
										? fb.i18n_admin.i18n_embed_form_title
										: "",
								html: modelContent,
								showCancelButton: true,
								confirmButtonText:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_lets_go_btn
										? fb.i18n_admin.i18n_embed_lets_go_btn
										: "Let's go",
								cancelButtonText:
									fb.i18n_admin &&
									fb.i18n_admin.i18n_embed_go_back_btn
										? fb.i18n_admin.i18n_embed_go_back_btn
										: "Go Back",
								customClass:
									"user-registration-swal2-modal  user-registration user-registration-swal2-modal--center swal2-show"
							}).then(function (result) {
								if (result.isDismissed) {
									showInitialAlert();
								} else if (result.isConfirmed) {
									var page_title = $(
										"[name='page_title']"
									).val();

									var data = {
										action: "user_registration_embed_form_action",
										security: fb.ur_embed_action || "",
										page_title: page_title,
										is_login: "yes",
										form_id: 10
									};
									$.ajax({
										url: fb.ajax_url,
										type: "POST",
										data: data,
										success: function (response) {
											if (response.success) {
												window.location = response.data;
											}
										}
									});
								}
							});
						}

						showInitialAlert();
					}
				});
			}
		};

		$(".ur-embed-form-button").on("click", function () {
			if ($(this).find(".ur-spinner").length > 0) {
				return;
			}
			URLoginFormBuilder.ur_embed_form($(this));
		});

		// Save the form when Update Form button is clicked.
		$(".ur_save_login_form_action_button").on("click", function () {
			ur_save_login_form_settings();
		});

		$(".ur-submit-button.ur-disabled-btn").on("click", function (e) {
			e.preventDefault();
		});
		$(
			".user-registration-login-form-global-settings #user_registration_lost_password_page_id , .user-registration-login-form-global-settings #user_registration_reset_password_page_id , .user-registration-login-form-global-settings #user_registration_login_options_login_redirect_url"
		).on("change", function () {
			urm_validate_login_page_settings($(this));
		});
	});

	function urm_validate_login_page_settings($this) {
		var field_container = $this.closest(
				".user-registration-login-form-global-settings--field"
			),
			main_container = field_container.closest(
				".user-registration-login-form-global-settings"
			),
			page_id = $this.val(),
			type = $this.attr("id"),
			data = {
				action: "user_registration_login_settings_page_validation",
				security: ur_login_form_params.ur_login_settings_save,
				page_id: page_id,
				type: type
			},
			spinner = '<span class="ur-spinner is-active"></span>';

		if (
			field_container.length === 0 ||
			field_container.find(".ur-spinner").length > 0 ||
			page_id === undefined ||
			type === undefined
		) {
			return;
		}
		if (type === "user_registration_login_options_login_redirect_url") {
			main_container = main_container.find("span.select2");
		}
		main_container.find(".error.inline").remove();
		$.ajax({
			url: ur_login_form_params.ajax_url,
			data: data,
			type: "POST",
			beforeSend: function () {
				field_container.append(spinner);
			},
			success: function (response) {
				if (!response.success) {
					var error_message = response.data.message;
					main_container.append(
						'<span class="error inline">' +
							error_message +
							"</span>"
					);
				}
			},
			complete: function (response) {
				field_container.find(".ur-spinner").remove();
			}
		});
	}

	/**
	 * Switch to a specific tab and section.
	 * @param {string} tab_id - The tab ID to switch to (e.g., "ur-tab-field-options" or "ur-tab-login-form-settings")
	 * @param {string} section_id - Optional section ID within Form Settings tab (e.g., "advanced-settings", "general-settings")
	 */
	function switch_to_tab_and_section(tab_id, section_id) {
		var $target_tab = $(
			'.user-registration-login-form-container ul.ur-tab-lists li[aria-controls="' +
				tab_id +
				'"]'
		);

		if ($target_tab.length === 0) {
			return;
		}

		// Trigger the tab click handler to switch tabs
		$target_tab.find("a.nav-tab").trigger("click");

		// If in Form Settings tab and section_id is provided, switch to that section
		if (tab_id === "ur-tab-login-form-settings" && section_id) {
			var $section_nav_item = $(
				"a[href='#" +
					section_id +
					"'], li#" +
					section_id +
					" a, [data-section='" +
					section_id +
					"']"
			).first();

			if ($section_nav_item.length > 0) {
				$section_nav_item.trigger("click");
			} else {
				// Fallback: manually hide/show sections
				$("form#ur-field-settings #ur-field-all-settings > div").hide();
				var $target_section = $(
					"form#ur-field-settings #ur-field-all-settings > div#" +
						section_id
				);
				if ($target_section.length > 0) {
					$target_section.show();
				}
			}
		}
	}

	function focus_on_first_error() {
		var error_selector =
			".user-registration-login-form-global-settings .error.inline, form#ur-field-settings .user-registration-login-form-global-settings .error.inline";
		var $first_error = $(error_selector).first();

		if ($first_error.length === 0) {
			return null;
		}

		// Determine which tab the error is in
		var target_tab_id = null;
		var target_section_id = null;

		if ($first_error.closest("#ur-tab-field-options").length > 0) {
			target_tab_id = "ur-tab-field-options";
		} else if (
			$first_error.closest(
				"form#ur-field-settings, #ur-field-all-settings"
			).length > 0
		) {
			target_tab_id = "ur-tab-login-form-settings";

			var $section = $first_error.closest("div[id$='-settings']");
			if (
				$section.length === 0 ||
				!$section.closest("#ur-field-all-settings").length
			) {
				$(
					"form#ur-field-settings #ur-field-all-settings > div[id$='-settings']"
				).each(function () {
					if (
						$first_error.closest("#" + $(this).attr("id")).length >
						0
					) {
						$section = $(this);
						return false;
					}
				});
			}

			if ($section.length > 0) {
				target_section_id = $section.attr("id");
			}
		}

		// Switch to the correct tab and section
		if (target_tab_id) {
			switch_to_tab_and_section(target_tab_id, target_section_id);
		}

		// Wait for tab/section switch to complete, then scroll to error
		setTimeout(function () {
			var error_container = $first_error.closest(
				".user-registration-login-form-global-settings"
			);

			if (error_container.length && error_container.is(":visible")) {
				$("html, body").animate(
					{
						scrollTop: error_container.offset().top - 100
					},
					500
				);

				error_container.addClass("ur-error-highlight");
				setTimeout(function () {
					error_container.removeClass("ur-error-highlight");
				}, 2000);
			}
		}, 300);

		return $first_error;
	}

	/**
	 * Map server-side error messages to field IDs.
	 */
	function get_field_id_from_error_message(error_message) {
		if (!error_message) {
			return null;
		}

		var message_lower = error_message.toLowerCase();

		// Map error messages to field IDs
		if (
			message_lower.indexOf("lost password shortcode") !== -1 ||
			message_lower.indexOf("lost password page") !== -1
		) {
			return "user_registration_lost_password_page_id";
		}

		if (
			message_lower.indexOf("reset password page") !== -1 ||
			message_lower.indexOf("reset password") !== -1
		) {
			return "user_registration_reset_password_page_id";
		}

		if (
			message_lower.indexOf("redirect") !== -1 ||
			message_lower.indexOf("login redirect") !== -1
		) {
			return "user_registration_login_options_login_redirect_url";
		}

		return null;
	}

	function ur_save_login_form_settings() {
		// Check for existing DOM errors before save
		var error_selector =
			".user-registration-login-form-global-settings .error.inline, form#ur-field-settings .user-registration-login-form-global-settings .error.inline";
		if ($(error_selector).length > 0) {
			focus_on_first_error();
			return;
		}

		perform_save();
	}

	function perform_save() {
		var settings = get_login_form_settings(
				ur_login_form_params.login_settings
			),
			form_values = [];

		$.each(settings, function (index, setting) {
			if (setting.type === "toggle") {
				var value = $("#" + setting.option).is(":checked");
			} else if (setting.type === "html") {
				var value = $(
					"#" + setting.option + " :input"
				).serializeArray();
			} else {
				var value = $("#" + setting.option).val();
			}
			form_values.push({
				option: setting.option,
				value: value
			});
		});
		var data = {
			action: "user_registration_login_settings_save_action",
			security: ur_login_form_params.ur_login_settings_save,
			data: {
				setting_data: form_values
			}
		};

		$.ajax({
			url: ur_login_form_params.ajax_url,
			data: data,
			type: "POST",
			beforeSend: function () {
				var spinner = '<span class="ur-spinner is-active"></span>';
				$(".ur_save_login_form_action_button").append(spinner);
			},
			complete: function (response) {
				$(".ur_save_login_form_action_button")
					.find(".ur-spinner")
					.remove();

				var success_message =
					ur_login_form_params.i18n_admin
						.i18n_settings_successfully_saved;

				if (
					response.responseJSON &&
					response.responseJSON.success === true
				) {
					show_message(success_message, "success");
				} else {
					var res = null;
					if (response.responseJSON) {
						res = response.responseJSON;
					} else if (response.responseText) {
						try {
							res = JSON.parse(response.responseText);
						} catch (e) {
							res = null;
						}
					}
					var error_message =
						ur_login_form_params.i18n_admin
							.i18n_error_occurred_while_saving;
					if (res && res.data && res.data.message) {
						error_message = res.data.message;
					}
					show_message(error_message, "error");

					// Handle server-side errors: map error message to field and focus on it
					var field_id =
						get_field_id_from_error_message(error_message);
					if (field_id) {
						var $field = $("#" + field_id);
						if ($field.length) {
							// Trigger validation to show the error
							urm_validate_login_page_settings($field);
						}
					}
					// Focus on error (validation is async, error will appear in DOM)
					focus_on_first_error();
				}
			}
		});
	}

	/**
	 * Show all the validation messages while saving the form in form builder.
	 *
	 * @param string message Specific validation message.
	 * @param string type The type or status of message, i.e. success or failure
	 */
	function show_message(message, type) {
		var $message_container = $(".ur-form-container").find(
				".ur-builder-message-container"
			),
			$admin_bar = $("#wpadminbar"),
			message_string = "";

		if (0 === $message_container.length) {
			$(".ur-form-container").append(
				'<div class="ur-builder-message-container"></div>'
			);
			$message_container = $(".ur-form-container").find(
				".ur-builder-message-container"
			);
			$message_container.css({ top: $admin_bar.height() + "px" });
		}

		if ("success" === type) {
			message_string =
				'<div class="ur-message"><div class="ur-success"><p><strong>' +
				ur_login_form_params.i18n_admin.i18n_success +
				"! </strong>" +
				message +
				'</p><span class="dashicons dashicons-no-alt ur-message-close"></span></div></div>';
		} else {
			$(".ur-error").remove();
			message_string =
				'<div class="ur-message"><div class="ur-error"><p><strong>' +
				ur_login_form_params.i18n_admin.i18n_error +
				"! </strong>" +
				message +
				'</p><span class="dashicons dashicons-no-alt ur-message-close"></span></div></div>';
		}

		var $message = $(message_string).prependTo($message_container);
		setTimeout(function () {
			$message.addClass("entered");
		}, 50);

		if ($(".ur-error").find(".ur-captcha-error").length == 1) {
			$(".ur-error").css("width", "490px");
			setTimeout(function () {
				removeMessage($message);
			}, 5000);
		} else {
			setTimeout(function () {
				removeMessage($message);
			}, 3000);
		}
	}

	/**
	 * Remove the validation message when calles.
	 *
	 * @param string $message Validation message string.
	 */
	function removeMessage($message) {
		$message.removeClass("entered").addClass("exiting");
		setTimeout(function () {
			$message.remove();
		}, 120);
	}

	function get_login_form_settings(login_settings) {
		var settings = [];
		$.each(login_settings, function (index, setting) {
			settings.push({
				option: setting.id,
				type: setting.type
			});
		});
		return settings;
	}

	function hide_show_login_title() {
		var value = $("#user_registration_login_title").is(":checked"),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login"),
			loginTitle = $("#ur-field-settings").find(
				"#user_registration_general_setting_login_form_title"
			),
			loginDesc = $("#ur-field-settings").find(
				"#user_registration_general_setting_login_form_desc"
			);

		if (value) {
			form.find(".user-registration-login-title").show();
			form.find(".user-registration-login-description").show();
			loginTitle
				.closest(".user-registration-login-form-global-settings")
				.show();
			loginDesc
				.closest(".user-registration-login-form-global-settings")
				.show();

			$(document).on(
				"change keyup keydown",
				"#user_registration_general_setting_login_form_title",
				function () {
					form.find(".user-registration-login-title").text(
						loginTitle.val()
					);
				}
			);

			$(document).on(
				"change keyup keydown",
				"#user_registration_general_setting_login_form_desc",
				function () {
					form.find(".user-registration-login-description").text(
						loginDesc.val()
					);
				}
			);
		} else {
			form.find(".user-registration-login-description").hide();
			form.find(".user-registration-login-title").hide();
			loginTitle
				.closest(".user-registration-login-form-global-settings")
				.hide();
			loginDesc
				.closest(".user-registration-login-form-global-settings")
				.hide();
		}
	}

	function hide_show_remember_me() {
		var value = $("#user_registration_login_options_remember_me").is(
				":checked"
			),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");
		form.find("#rememberme")
			.parent("label")
			.css({ opacity: value ? 1 : 0.5 });

		if (value) {
			$("#user_registration_label_remember_me")
				.closest(".user-registration-login-form-global-settings")
				.show()
				.css("display", "block");
		} else {
			$("#user_registration_label_remember_me")
				.closest(".user-registration-login-form-global-settings")
				.hide();
		}
	}

	function hide_show_lost_password() {
		var value = $("#user_registration_login_options_lost_password").is(
				":checked"
			),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");
		form.find(".user-registration-LostPassword").css({
			opacity: value ? 1 : 0.5
		});
	}

	// function hide_show_labels() {
	// 	var value = $("#user_registration_login_options_hide_labels").is(
	// 			":checked"
	// 		),
	// 		form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");
	//
	// 	if (!value) {
	// 		form.find(".user-registration-form-row label").show();
	// 	} else {
	// 		form.find(".user-registration-form-row label").hide();
	// 	}
	// }

	function handleRecaptchaLoginSettings() {
		var $checkbox = $("#user_registration_login_options_enable_recaptcha");
		var login_captcha_enabled = $checkbox.is(":checked");

		// Prevent checkbox from being checked if captcha is not set
		if (ur_login_form_params.no_captcha_set && login_captcha_enabled) {
			$checkbox.prop("checked", false);
			// Hide the dropdown since checkbox is unchecked
			$("#user_registration_login_options_configured_captcha_type")
				.closest(".user-registration-login-form-global-settings")
				.hide();
			show_message(
				ur_login_form_params.i18n_admin.i18n_captcha_not_set_error,
				"error"
			);
			return;
		}

		if (login_captcha_enabled) {
			$("#user_registration_login_options_configured_captcha_type")
				.closest(".user-registration-login-form-global-settings")
				.show();
		} else {
			$("#user_registration_login_options_configured_captcha_type")
				.closest(".user-registration-login-form-global-settings")
				.hide();
		}
	}

	function handlePreventActiveLogin() {
		var login_captcha_enabled = $(
			"#user_registration_pro_general_setting_prevent_active_login"
		).is(":checked");
		if (login_captcha_enabled) {
			$("#user_registration_pro_general_setting_limited_login")
				.closest(".user-registration-login-form-global-settings")
				.show();
		} else {
			$("#user_registration_pro_general_setting_limited_login")
				.closest(".user-registration-login-form-global-settings")
				.hide();
		}
	}

	function handlePasswordlessLogin() {
		var value = $("#user_registration_pro_passwordless_login").is(
				":checked"
			),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");

		if (value) {
			form.find(".user-registration-passwordless-login").show();
			$("#user_registration_pro_passwordless_login_default_login_area")
				.closest(".user-registration-login-form-global-settings")
				.show();
		} else {
			form.find(".user-registration-passwordless-login").hide();
			$("#user_registration_pro_passwordless_login_default_login_area")
				.closest(".user-registration-login-form-global-settings")
				.hide();
		}

		handlePasswordlessLoginArea(
			$("#user_registration_pro_passwordless_login_default_login_area")
		);
	}

	function handlePasswordlessLoginArea($node) {
		var value = $node.is(":checked"),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");

		if (
			$("#user_registration_pro_passwordless_login").is(":checked") &&
			value
		) {
			form.find(".user-registration-passwordless-login").hide();
			form.find(".password-input-group").closest(".form-row").hide();
			form.find(".user-registration-before-login-btn").hide();
		} else if (
			$("#user_registration_pro_passwordless_login").is(":checked") &&
			!value
		) {
			form.find(".user-registration-passwordless-login").show();
			form.find(".password-input-group").closest(".form-row").show();
			form.find(".user-registration-before-login-btn").show();
		} else {
			form.find(".user-registration-passwordless-login").hide();
			form.find(".password-input-group").closest(".form-row").show();
			form.find(".user-registration-before-login-btn").show();
		}
	}

	function hide_show_field_icon() {
		var login_captcha_enabled = $(
				"#user_registration_pro_general_setting_login_form"
			).is(":checked"),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");

		if (login_captcha_enabled) {
			form.find(".user-registration-form-row span.ur-icon").show();
		} else {
			form.find(".user-registration-form-row span.ur-icon").hide();
		}
	}

	function init_login_form_settings() {
		handleRecaptchaLoginSettings();
		$(document).on(
			"change",
			"#user_registration_login_options_enable_recaptcha",
			function () {
				handleRecaptchaLoginSettings();
			}
		);

		$(document).on(
			"change",
			"#user_registration_pro_general_setting_login_form",
			function () {
				hide_show_field_icon();
			}
		);
		hide_show_field_icon();

		handlePreventActiveLogin();
		$(document).on(
			"change",
			"#user_registration_pro_general_setting_prevent_active_login",
			function () {
				handlePreventActiveLogin();
			}
		);

		handlePasswordlessLogin();
		$(document).on(
			"change",
			"#user_registration_pro_passwordless_login",
			function () {
				handlePasswordlessLogin();
			}
		);
		$(document).on(
			"change",
			"#user_registration_pro_passwordless_login_default_login_area",
			function () {
				handlePasswordlessLoginArea($(this));
			}
		);

		$("#user_registration_login_options_form_template").on(
			"change",
			function () {
				var value = $(
						"#user_registration_login_options_form_template"
					).val(),
					form = $(".ur-login-form-wrapper").find(
						".ur-frontend-form.login"
					);

				form.removeClass("ur-frontend-form--rounded-edge");
				form.removeClass("ur-frontend-form--rounded");
				form.removeClass("ur-frontend-form--flat");
				form.removeClass("ur-frontend-form--bordered");
				if ("default" !== value) {
					value =
						"rounded_edge" === value
							? "rounded ur-frontend-form--rounded-edge"
							: value;
					form.addClass("ur-frontend-form--" + value);
				}
			}
		);

		$(document).on(
			"change",
			"#user_registration_login_title",
			function (e) {
				hide_show_login_title();
			}
		);
		hide_show_login_title();

		$(document).on(
			"change",
			"#user_registration_login_options_remember_me",
			function (e) {
				hide_show_remember_me();
			}
		);
		hide_show_remember_me();

		$(document).on(
			"change",
			"#user_registration_login_options_lost_password",
			function (e) {
				hide_show_lost_password();
			}
		);
		hide_show_lost_password();

		// $(document).on(
		// 	"change",
		// 	"#user_registration_login_options_hide_labels",
		// 	function (e) {
		// 		hide_show_labels();
		// 	}
		// );
		// hide_show_labels();

		$("#user_registration_general_setting_registration_label").on(
			"keyup",
			function () {
				var value = $(
						"#user_registration_general_setting_registration_label"
					).val(),
					form = $(".ur-login-form-wrapper").find(
						".ur-frontend-form.login"
					);

				form.find(".user-registration-register a").html(value);
			}
		);

		$("#user_registration_label_lost_your_password").on(
			"keyup",
			function () {
				var value = $(
						"#user_registration_label_lost_your_password"
					).val(),
					form = $(".ur-login-form-wrapper").find(
						".ur-frontend-form.login"
					);

				form.find(".user-registration-LostPassword a").html(value);
			}
		);

		$("#user_registration_label_login").on("keyup", function () {
			var value = $("#user_registration_label_login").val(),
				form = $(".ur-login-form-wrapper").find(
					".ur-frontend-form.login"
				);

			form.find(".user-registration-Button").html(value);
		});

		$("#user_registration_label_remember_me").on("keyup", function () {
			var value = $("#user_registration_label_remember_me").val(),
				form = $(".ur-login-form-wrapper").find(
					".ur-frontend-form.login"
				);

			form.find(".user-registration-form__label-for-checkbox span").html(
				value
			);
		});

		$("#user_registration_label_password").on("keyup", function () {
			var value = $("#user_registration_label_password").val(),
				form = $(".ur-login-form-wrapper").find(
					".ur-frontend-form.login"
				);

			form.find(".user-registration-form-row label[for='password']").html(
				value + '<span class="required">*</span>'
			);
		});

		$("#user_registration_label_username_or_email").on(
			"keyup",
			function () {
				var value = $(
						"#user_registration_label_username_or_email"
					).val(),
					form = $(".ur-login-form-wrapper").find(
						".ur-frontend-form.login"
					);

				form.find(
					".user-registration-form-row label[for='username']"
				).html(value + '<span class="required">*</span>');
			}
		);

		$("#user_registration_placeholder_username_or_email").on(
			"keyup",
			function () {
				var value = $(
						"#user_registration_placeholder_username_or_email"
					).val(),
					form = $(".ur-login-form-wrapper").find(
						".ur-frontend-form.login"
					);

				form.find(".user-registration-form-row #username").attr(
					"placeholder",
					value
				);
			}
		);

		$("#user_registration_placeholder_password").on("keyup", function () {
			var value = $("#user_registration_placeholder_password").val(),
				form = $(".ur-login-form-wrapper").find(
					".ur-frontend-form.login"
				);

			form.find(".user-registration-form-row #password").attr(
				"placeholder",
				value
			);
		});

		$(".ur-redirect-to-login-page").ready(function () {
			var $url = $(".ur-redirect-to-login-page"),
				$check = $(
					"#user_registration_login_options_prevent_core_login"
				),
				$redirect = $(
					"#user_registration_login_options_login_redirect_url"
				);

			if (!$check.is(":checked")) {
				$url.val("")
					.closest(".single_select_page")
					.css("display", "none");
			} else {
				var $selected_page = $check
					.closest(".ur-login-form-setting-block")
					.find(".ur-redirect-to-login-page")
					.val();
				var login_form_settings = $check.closest(
					".user-registration-login-form-container"
				);
				var wpbody_class =
					$(login_form_settings).closest("#wpbody-content");

				if ("" === $selected_page) {
					$check
						.closest(".ur-login-form-setting-block")
						.find(".ur-redirect-to-login-page")
						.closest(
							".user-registration-login-form-global-settings--field"
						)
						.append(
							'<div class="error inline" style="padding:10px;">' +
								ur_login_form_params.user_registration_membership_redirect_default_page_message +
								"</div>"
						);
				} else {
					$(wpbody_class)
						.find("#ur-lists-page-topnav")
						.find(".ur_save_login_form_action_button")
						.prop("disabled", false);
					$check
						.closest(".ur-login-form-setting-block")
						.find(".ur-redirect-to-login-page")
						.closest(
							".user-registration-login-form-global-settings--field"
						)
						.find(".error.inline")
						.remove();
				}

				$redirect.prop("required", true);
			}

			// Handling the "clear" button click event for Select2.
			$(
				'select[name="user_registration_login_options_login_redirect_url"]'
			).on("select2:unselect", function () {
				$check
					.closest(".ur-login-form-setting-block")
					.find(".ur-redirect-to-login-page")
					.closest(
						".user-registration-login-form-global-settings--field"
					)
					.append(
						'<div class="error inline" style="padding:10px;">' +
							ur_login_form_params.user_registration_membership_redirect_default_page_message +
							"</div>"
					);

				$redirect.prop("required", true);
			});
		});

		$("#user_registration_login_options_prevent_core_login").on(
			"change",
			function () {
				var $url = $(
					"#user_registration_login_options_prevent_core_login"
				);

				$("#user_registration_login_options_login_redirect_url").closest(".single_select_page").toggle();
				$("#user_registration_login_options_login_redirect_url").prop(
					"required",
					function () {
						return "checked" === $url.prop("checked")
							? true
							: false;
					}
				);
			}
		);

		$(document).ready(function () {
			$(
				"#user_registration_login_options_enable_custom_redirect"
			).trigger("change");
		});
		$(document).on(
			"change",
			"#user_registration_login_options_enable_custom_redirect",
			function () {
				var $redirect_after_login = $(
					"#user_registration_login_options_redirect_after_login"
				);
				var $redirect_after_logout = $(
					"#user_registration_login_options_redirect_after_logout"
				);
				if ($(this).is(":checked")) {
					$redirect_after_login
						.closest(
							".user-registration-login-form-global-settings"
						)
						.show();
					$redirect_after_logout
						.closest(
							".user-registration-login-form-global-settings"
						)
						.show();
				} else {
					$redirect_after_login
						.closest(
							".user-registration-login-form-global-settings"
						)
						.hide();
					$redirect_after_logout
						.closest(
							".user-registration-login-form-global-settings"
						)
						.hide();
				}
				$redirect_after_login.trigger("change");
				$redirect_after_logout.trigger("change");
			}
		);
		$(document).on(
			"change",
			"#user_registration_login_options_redirect_after_login",
			function () {
				var redirect_after_login_option = $(
					"#user_registration_login_options_enable_custom_redirect"
				).is(":checked")
					? $(this).val()
					: "hidden";
				var $external_url = $(
					"#user_registration_login_options_after_login_redirect_external_url"
				).closest(".user-registration-login-form-global-settings");
				var $internal_page = $(
					"#user_registration_login_options_after_login_redirect_page"
				).closest(".user-registration-login-form-global-settings");
				switch (redirect_after_login_option) {
					case "no-redirection":
						$external_url.hide();
						$internal_page.hide();
						break;
					case "internal-page":
						$external_url.hide();
						$internal_page.show();
						break;
					case "external-url":
						$external_url.show();
						$internal_page.hide();
						break;
					case "previous-page":
						$external_url.hide();
						$internal_page.hide();
						break;
					default:
						$external_url.hide();
						$internal_page.hide();
						break;
				}
			}
		);
		$(document).on(
			"change",
			"#user_registration_login_options_redirect_after_logout",
			function () {
				var redirect_after_logout_option = $(
					"#user_registration_login_options_enable_custom_redirect"
				).is(":checked")
					? $(this).val()
					: "hidden";
				var $external_url = $(
					"#user_registration_login_options_after_logout_redirect_external_url"
				).closest(".user-registration-login-form-global-settings");
				var $internal_page = $(
					"#user_registration_login_options_after_logout_redirect_page"
				).closest(".user-registration-login-form-global-settings");
				switch (redirect_after_logout_option) {
					case "no-redirection":
						$external_url.hide();
						$internal_page.hide();
						break;
					case "internal-page":
						$external_url.hide();
						$internal_page.show();
						break;
					case "external-url":
						$external_url.show();
						$internal_page.hide();
						break;
					case "previous-page":
						$external_url.hide();
						$internal_page.hide();
						break;
					default:
						$external_url.hide();
						$internal_page.hide();
						break;
				}
			}
		);
	}
})(jQuery);
