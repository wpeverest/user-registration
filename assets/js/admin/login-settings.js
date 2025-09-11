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
				.find('li a[href="admin.php?page=user-registration"]')
				.first()
				.closest("li")
				.addClass("current");
		}

		// Save the form when Update Form button is clicked.
		$(".ur_save_login_form_action_button").on("click", function () {
			ur_save_login_form_settings();
		});

		$(".ur-submit-button.ur-disabled-btn").on("click", function (e) {
			e.preventDefault();
		});
	});

	function ur_save_login_form_settings() {
		var settings = get_login_form_settings(
				ur_login_form_params.login_settings
			),
			form_values = [];

		$.each(settings, function (index, setting) {
			if (setting.type === "toggle") {
				var value = $("#" + setting.option).is(":checked");
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

				if (response.responseJSON.success === true) {
					show_message(success_message, "success");
				} else {
					var res = JSON.parse(response.responseText);
					show_message(res.data.message, "error");
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

	function hide_show_labels() {
		var value = $("#user_registration_login_options_hide_labels").is(
				":checked"
			),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");

		if (!value) {
			form.find(".user-registration-form-row label").show();
		} else {
			form.find(".user-registration-form-row label").hide();
		}
	}

	function handleRecaptchaLoginSettings() {
		var login_captcha_enabled = $(
			"#user_registration_login_options_enable_recaptcha"
		).is(":checked");
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

		$(document).on(
			"change",
			"#user_registration_login_options_hide_labels",
			function (e) {
				hide_show_labels();
			}
		);
		hide_show_labels();

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
			console.log($check.is(":checked"));

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

				$(".single_select_page").toggle();
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
	}
})(jQuery);
