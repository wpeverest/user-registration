/* global user_registration_settings_params */
(function ($) {
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
	});

	function ur_save_login_form_settings() {
		var settings = get_login_form_settings(ur_login_form_params),
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

	function get_login_form_settings(all_settings) {
		var login_settings = all_settings.login_settings,
			settings = [];
		$.each(login_settings.sections, function (index, section) {
			$.each(section.settings, function (index, setting) {
				settings.push({
					option: setting.id,
					type: setting.type
				});
			});
		});
		return settings;
	}

	function hide_show_login_title() {
		var value      = $("#user_registration_login_title").is(":checked"),
			form       = $(".ur-login-form-wrapper").find(".ur-frontend-form.login"),
			loginTitle = $("#ur-login-form-setting").find("#user_registration_general_setting_login_form_title"),
			loginDesc  = $("#ur-login-form-setting").find("#user_registration_general_setting_login_form_desc");

		if (value) {
			form.find(".user-registration-login-title").show();
			form.find(".user-registration-login-description").show();
			loginTitle.closest(".user-registration-login-form-global-settings").show();
			loginDesc.closest(".user-registration-login-form-global-settings").show();

			$(document).on("change keyup keydown", "#user_registration_general_setting_login_form_title", function () {
				form.find(".user-registration-login-title").text(loginTitle.val());
			});

			$(document).on("change keyup keydown", "#user_registration_general_setting_login_form_desc", function () {
				form.find(".user-registration-login-description").text(loginDesc.val());
			});
		} else {
			form.find(".user-registration-login-description").hide();
			form.find(".user-registration-login-title").hide();
			loginTitle.closest(".user-registration-login-form-global-settings").hide();
			loginDesc.closest(".user-registration-login-form-global-settings").hide();
		}
	}

	function hide_show_remember_me() {
		var value = $("#user_registration_login_options_remember_me").is(
				":checked"
			),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");

		if (value) {
			form.find("#rememberme").parent("label").show();
		} else {
			form.find("#rememberme").parent("label").hide();
		}
	}

	function hide_show_lost_password() {
		var value = $("#user_registration_login_options_lost_password").is(
				":checked"
			),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");

		if (value) {
			form.find(".user-registration-LostPassword").show();
		} else {
			form.find(".user-registration-LostPassword").hide();
		}
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

	function hide_show_registration_url() {
		var value = $(
				"#user_registration_general_setting_registration_url_options"
			).val(),
			form = $(".ur-login-form-wrapper").find(".ur-frontend-form.login");

		if ("" === value.trim()) {
			form.find(".user-registration-register").hide();
		} else {
			form.find(".user-registration-register").show();
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

		$("#user_registration_general_setting_registration_url_options").on(
			"keyup",
			function () {
				hide_show_registration_url();
			}
		);
		hide_show_registration_url();

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

			form.find(
				".user-registration-form-row[data-field='password'] label"
			).html(value + '<span class="required">*</span>');
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
	}
})(jQuery);
