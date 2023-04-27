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
	$(".colorpick, .colorpickpreview")
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

	$(".colorpick, .colorpickpreview").on("click", function (event) {
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
	var recaptcha_input_value = $(".user-registration")
		.find(
			'input[name="user_registration_integration_setting_recaptcha_version"]:checked'
		)
		.val();
	if (recaptcha_input_value != undefined) {
		handleReCaptchaHideShow(recaptcha_input_value);
	}

	$(".user-registration").on(
		"change",
		'input[name="user_registration_integration_setting_recaptcha_version"]',
		function () {
			handleReCaptchaHideShow($(this).val());
		}
	);

	$(".user-registration").on(
		"change",
		"input#user_registration_integration_setting_invisible_recaptcha_v2",
		function () {
			if ($(this).is(":checked")) {
				$("#user_registration_integration_setting_recaptcha_site_key")
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
				$("#user_registration_integration_setting_recaptcha_site_key")
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
				"#user_registration_integration_setting_recaptcha_threshold_score_v3"
			)
				.closest("tr")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_site_key_hcaptcha"
			)
				.closest("tr")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest("tr")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_key_v3")
				.closest("tr")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_secret_v3")
				.closest("tr")
				.hide();
			$("#user_registration_integration_setting_invisible_recaptcha_v2")
				.closest("tr")
				.show();
		}
	);

	function handleReCaptchaHideShow(value) {
		if (value == "v3") {
			$("#user_registration_integration_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_integration_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_integration_setting_recaptcha_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_secret")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_site_key_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_threshold_score_v3"
			)
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_integration_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_key"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_secret"
			)
				.closest(".user-registration-global-settings")
				.hide();
		} else if (value == "hCaptcha") {
			$(
				"#user_registration_integration_setting_recaptcha_site_key_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.show();
			$(
				"#user_registration_integration_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_integration_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_secret")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_key"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_invisible_site_secret"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_threshold_score_v3"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")
				.hide();
		} else {
			if (
				value == "v2" &&
				$(
					"input#user_registration_integration_setting_invisible_recaptcha_v2"
				).is(":checked")
			) {
				$(
					"#user_registration_integration_setting_recaptcha_site_key_v3"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_integration_setting_recaptcha_site_secret_v3"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$("#user_registration_integration_setting_recaptcha_site_key")
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_integration_setting_recaptcha_site_secret"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_integration_setting_recaptcha_invisible_site_key"
				)
					.closest(".user-registration-global-settings")
					.show();
				$(
					"#user_registration_integration_setting_recaptcha_invisible_site_secret"
				)
					.closest(".user-registration-global-settings")
					.show();
			} else {
				$("#user_registration_integration_setting_recaptcha_site_key")
					.closest(".user-registration-global-settings")
					.show();
				$(
					"#user_registration_integration_setting_recaptcha_site_secret"
				)
					.closest(".user-registration-global-settings")
					.show();
				$(
					"#user_registration_integration_setting_recaptcha_invisible_site_key"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_integration_setting_recaptcha_invisible_site_secret"
				)
					.closest(".user-registration-global-settings")
					.hide();
			}

			// Common Hide for V2
			$(
				"#user_registration_integration_setting_recaptcha_threshold_score_v3"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_site_key_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_integration_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_integration_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")
				.show();
		}
	}
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

	$(".ur-image-uploader").on("click", function (e) {
		ur_uploader = $(this);
		e.preventDefault();
		var image = wp
			.media({
				library: {
					type: ["image"],
				},
				title: ur_uploader.upload_file,
				// multiple: true if you want to upload multiple files at once
				multiple: false,
			})
			.open()
			.on("select", function (e) {
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get("selection").first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				var image_url = uploaded_image.toJSON().url;
				// Let's assign the url value to the input field
				ur_uploader.attr("src", image_url);
				if (ur_uploader.hasClass("ur-button")) {
					ur_uploader.siblings("img").show();
					ur_uploader.siblings("img").attr("src", image_url);
					ur_uploader
						.siblings("#user_registration_pdf_logo_image")
						.val(image_url);
					ur_uploader.hide();
					ur_uploader.siblings(".ur-image-remover").show();
				} else {
					ur_uploader.attr("src", image_url);
					ur_uploader
						.siblings("#user_registration_pdf_logo_image")
						.val(image_url);
				}
			});
	});

	$(".ur-image-remover").on("click", function (e) {
		var ur_remover = $(this);
		e.preventDefault();

		ur_remover.siblings("img").attr("src", "");
		ur_remover.siblings("#user_registration_pdf_logo_image").val("");
		ur_remover.siblings(".ur-image-uploader").show();
		ur_remover.hide();
		ur_remover.siblings("img").hide();
	});

	// Handles radio images option click.
	$(".radio-image")
		.find("input")
		.each(function () {
			var $option_selector = $(this);

			$option_selector.on("click", function () {
				$(this).closest("ul").find("label").removeClass("selected");
				$(this).closest("label").addClass("selected");
			});
		});

	// Handles collapse of side menu.
	$("#ur-settings-collapse").on("click", function (e) {
		e.preventDefault();

		if ($(this).hasClass("close")) {
			$(this).closest("header").addClass("collapsed");
			$(this).removeClass("close").addClass("open");
		} else {
			$(this).closest("header").removeClass("collapsed");
			$(this).removeClass("open").addClass("close");
		}
	});

	$(".ur-nav-premium").each(function () {
		$(this).hover(
			function (e) {
				$(this).find(".ur-tooltip").show();
			},
			function (e) {
				$(this).find(".ur-tooltip").hide();
			}
		);
	});

	/**
	 * Open collapsed menu on search input clicked.
	 */
	$(".ur-search-input").on("click", function () {
		if (
			$(this).closest(".user-registration-header").hasClass("collapsed")
		) {
			$(this)
				.closest(".user-registration-header")
				.removeClass("collapsed");
			$(this)
				.closest(".user-registration-header")
				.find("#ur-settings-collapse")
				.addClass("close");
			$(this).find("#ur-search-settings").focus();
		}
	});
})(jQuery);
