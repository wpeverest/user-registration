/* global user_registration_settings_params */
(function ($) {
	// Allowed Screens
	$("select#user_registration_allowed_screens")
		.on("change", function () {
			if ("specific" === $(this).val()) {
				$(this).closest("tr").next("tr").hide();
				$(this).closest("tr").next().next("tr").show();
			} else if ("all_except" === $(this).val()) {
				$(this).closest("tr").next("tr").show();
				$(this).closest("tr").next().next("tr").hide();
			} else {
				$(this).closest("tr").next("tr").hide();
				$(this).closest("tr").next().next("tr").hide();
			}
		})
		.trigger("change");

	// Color picker
	$(".colorpick")
		.iris({
			change: function (event, ui) {
				$(this)
					.parent()
					.find(".colorpickpreview")
					.css({ backgroundColor: ui.color.toString() });
			},
			hide: true,
			border: true,
		})
		.on("click", function () {
			$(".iris-picker").hide();
			$(this).closest("td").find(".iris-picker").show();
		});

	$("body").on("click", function () {
		$(".iris-picker").hide();
	});

	$(".colorpick").on("click", function (event) {
		event.stopPropagation();
	});

	// Edit prompt
	$(function () {
		var changed = false;

		$("input, textarea, select, checkbox").on("change", function () {
			changed = true;
		});

		$(".ur-nav-tab-wrapper a").on("click", function () {
			if (changed) {
				window.onbeforeunload = function () {
					return user_registration_settings_params.i18n_nav_warning;
				};
			} else {
				window.onbeforeunload = "";
			}
		});

		$(".submit input").on("click", function () {
			window.onbeforeunload = "";
		});
	});

	// Select all/none
	$(".user-registration").on("click", ".select_all", function () {
		$(this)
			.closest("td")
			.find("select option")
			.attr("selected", "selected");
		$(this).closest("td").find("select").trigger("change");
		return false;
	});

	$(".user-registration").on("click", ".select_none", function () {
		$(this).closest("td").find("select option").prop("selected", false);
		$(this).closest("td").find("select").trigger("change");
		return false;
	});

	// reCaptcha version selection
	$("input[name='user_registration_integration_setting_recaptcha_version']")
		.change(function () {
			if ($(this).is(":checked")) {
				if ("v2" === $(this).val()) {
					if (
						$(
							"#user_registration_integration_setting_invisible_recaptcha_v2"
						).is(":checked")
					) {
						$(
							"#user_registration_integration_setting_recaptcha_site_key"
						)
							.closest("tr")
							.hide();
						$(
							"#user_registration_integration_setting_recaptcha_site_secret"
						)
							.closest("tr")
							.hide();
						$(
							"#user_registration_integration_setting_recaptcha_invisible_site_key"
						)
							.closest("tr")
							.show();
						$(
							"#user_registration_integration_setting_recaptcha_invisible_site_secret"
						)
							.closest("tr")
							.show();
					} else {
						$(
							"#user_registration_integration_setting_recaptcha_site_key"
						)
							.closest("tr")
							.show();
						$(
							"#user_registration_integration_setting_recaptcha_site_secret"
						)
							.closest("tr")
							.show();
						$(
							"#user_registration_integration_setting_recaptcha_invisible_site_key"
						)
							.closest("tr")
							.hide();
						$(
							"#user_registration_integration_setting_recaptcha_invisible_site_secret"
						)
							.closest("tr")
							.hide();
					}
					$(
						"#user_registration_integration_setting_invisible_recaptcha_v2"
					)
						.closest("tr")
						.show();
					$(
						"#user_registration_integration_setting_recaptcha_site_key_v3"
					)
						.closest("tr")
						.hide();
					$(
						"#user_registration_integration_setting_recaptcha_site_secret_v3"
					)
						.closest("tr")
						.hide();
					$(
						"#user_registration_integration_setting_recaptcha_threshold_score_v3"
					)
						.closest("tr")
						.hide();
				} else {
					$(
						"#user_registration_integration_setting_recaptcha_site_key"
					)
						.closest("tr")
						.hide();
					$(
						"#user_registration_integration_setting_recaptcha_site_secret"
					)
						.closest("tr")
						.hide();
					$(
						"#user_registration_integration_setting_recaptcha_invisible_site_key"
					)
						.closest("tr")
						.hide();
					$(
						"#user_registration_integration_setting_recaptcha_invisible_site_secret"
					)
						.closest("tr")
						.hide();
					$(
						"#user_registration_integration_setting_invisible_recaptcha_v2"
					)
						.closest("tr")
						.hide();
					$(
						"#user_registration_integration_setting_recaptcha_site_key_v3"
					)
						.closest("tr")
						.show();
					$(
						"#user_registration_integration_setting_recaptcha_site_secret_v3"
					)
						.closest("tr")
						.show();
					$(
						"#user_registration_integration_setting_recaptcha_threshold_score_v3"
					)
						.closest("tr")
						.show();
				}
			}
		})
		.change();

	$(
		"input#user_registration_integration_setting_invisible_recaptcha_v2"
	).change(function () {
		if ($(this).is(":checked")) {
			$("#user_registration_integration_setting_recaptcha_site_key")
				.closest("tr")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_secret")
				.closest("tr")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_key"
			)
				.closest("tr")
				.show();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_secret"
			)
				.closest("tr")
				.show();
		} else {
			$("#user_registration_integration_setting_recaptcha_site_key")
				.closest("tr")
				.show();
			$("#user_registration_integration_setting_recaptcha_site_secret")
				.closest("tr")
				.show();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_key"
			)
				.closest("tr")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_secret"
			)
				.closest("tr")
				.hide();
		}
	});

	$(".ur-redirect-to-login-page").ready(function () {
		var $url = $(".ur-redirect-to-login-page"),
			$check = $("#user_registration_login_options_prevent_core_login"),
			$redirect = $(
				"#user_registration_login_options_login_redirect_url"
			);

		if (!$check.prop("checked")) {
			$url.val("").closest(".single_select_page").css("display", "none");
		} else {
			$redirect.prop("required", true);
		}
	});

	$("#user_registration_login_options_prevent_core_login").on(
		"change",
		function () {
			var $url = $("#user_registration_login_options_prevent_core_login");

			$(".single_select_page").toggle();
			$("#user_registration_login_options_login_redirect_url").prop(
				"required",
				function () {
					return "checked" === $url.prop("checked") ? true : false;
				}
			);
		}
	);

	// Change span with file name when user selects a file.
	$(".user-registration-custom-file__input").on("change", function () {
		var file = $(".user-registration-custom-file__input").prop("files")[0];

		$(".user-registration-custom-selected-file").html(file.name);
	});

	$(document).on(
		"click",
		"#user_registration_pro_general_setting_prevent_active_login",
		function () {
			if ($(this).prop("checked")) {
				$(document)
					.find(
						"#user_registration_pro_general_setting_limited_login"
					)
					.parents("tr")
					.removeClass("userregistration-forms-hidden");
			} else {
				$(document)
					.find(
						"#user_registration_pro_general_setting_limited_login"
					)
					.parents("tr")
					.addClass("userregistration-forms-hidden");
			}
		}
	);
})(jQuery);
