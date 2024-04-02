/* global user_registration_settings_params */
(function ($) {
	// Allowed Screens
	$("select#user_registration_allowed_screens")
		.on("change", function () {
			if ("specific" === $(this).val()) {
				$(this)
					.closest(".user-registration-global-settings")
					.next(".user-registration-global-settings")
					.hide();
				$(this)
					.closest(".user-registration-global-settings")
					.next()
					.next(".user-registration-global-settings")
					.show();
			} else if ("all_except" === $(this).val()) {
				$(this)
					.closest(".user-registration-global-settings")
					.next(".user-registration-global-settings")
					.show();
				$(this)
					.closest(".user-registration-global-settings")
					.next()
					.next(".user-registration-global-settings")
					.hide();
			} else {
				$(this)
					.closest(".user-registration-global-settings")
					.next(".user-registration-global-settings")
					.hide();
				$(this)
					.closest(".user-registration-global-settings")
					.next()
					.next(".user-registration-global-settings")
					.hide();
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
			$(this)
				.closest(".user-registration-global-settings--field")
				.find("> .iris-picker")
				.show();
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
			'input[name="user_registration_captcha_setting_recaptcha_version"]:checked'
		)
		.val();
	if (recaptcha_input_value != undefined) {
		handleReCaptchaHideShow(recaptcha_input_value);
	}

	$(".user-registration").on(
		"change",
		'input[name="user_registration_captcha_setting_recaptcha_version"]',
		function () {
			handleReCaptchaHideShow($(this).val());
		}
	);

	$(".user-registration").on(
		"change",
		"input#user_registration_captcha_setting_invisible_recaptcha_v2",
		function () {
			if ($(this).is(":checked")) {
				$("#user_registration_captcha_setting_recaptcha_site_key")
					.closest(".user-registration-global-settings")
					.hide();
				$("#user_registration_captcha_setting_recaptcha_site_secret")
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_key"
				)
					.closest(".user-registration-global-settings")
					.show();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
				)
					.closest(".user-registration-global-settings")
					.show();
			} else {
				$("#user_registration_captcha_setting_recaptcha_site_key")
					.closest(".user-registration-global-settings")
					.show();
				$("#user_registration_captcha_setting_recaptcha_site_secret")
					.closest(".user-registration-global-settings")
					.show();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_key"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
				)
					.closest(".user-registration-global-settings")
					.hide();
			}
			$("#user_registration_captcha_setting_recaptcha_threshold_score_v3")
				.closest(".user-registration-global-settings")

				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key_hcaptcha")
				.closest(".user-registration-global-settings")

				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")

				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")

				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")

				.hide();
			$("#user_registration_captcha_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")

				.show();
		}
	);

	function handleReCaptchaHideShow(value) {
		if (value == "v3") {
			$("#user_registration_captcha_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_captcha_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_captcha_setting_recaptcha_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key_hcaptcha")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_threshold_score_v3")
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_captcha_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_invisible_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_key_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_cloudflare_theme")
				.closest(".user-registration-global-settings")
				.hide();
		} else if (value == "hCaptcha") {
			$("#user_registration_captcha_setting_recaptcha_site_key_hcaptcha")
				.closest(".user-registration-global-settings")
				.show();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_captcha_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_invisible_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_threshold_score_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_key_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_cloudflare_theme")
				.closest(".user-registration-global-settings")
				.hide();
		} else if (value == "cloudflare") {
			$(
				"#user_registration_captcha_setting_recaptcha_site_key_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.show();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_captcha_setting_recaptcha_cloudflare_theme")
				.closest(".user-registration-global-settings")
				.show();
			$("#user_registration_captcha_setting_recaptcha_site_key_hcaptcha")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_invisible_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_threshold_score_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")
				.hide();
		} else {
			if (
				value == "v2" &&
				$(
					"input#user_registration_captcha_setting_invisible_recaptcha_v2"
				).is(":checked")
			) {
				$("#user_registration_captcha_setting_recaptcha_site_key_v3")
					.closest(".user-registration-global-settings")
					.hide();
				$("#user_registration_captcha_setting_recaptcha_site_secret_v3")
					.closest(".user-registration-global-settings")
					.hide();
				$("#user_registration_captcha_setting_recaptcha_site_key")
					.closest(".user-registration-global-settings")
					.hide();
				$("#user_registration_captcha_setting_recaptcha_site_secret")
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_captcha_setting_recaptcha_site_key_cloudflare"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_captcha_setting_recaptcha_site_secret_cloudflare"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_captcha_setting_recaptcha_cloudflare_theme"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_key"
				)
					.closest(".user-registration-global-settings")
					.show();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
				)
					.closest(".user-registration-global-settings")
					.show();
			} else {
				$("#user_registration_captcha_setting_recaptcha_site_key")
					.closest(".user-registration-global-settings")
					.show();
				$("#user_registration_captcha_setting_recaptcha_site_secret")
					.closest(".user-registration-global-settings")
					.show();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_key"
				)
					.closest(".user-registration-global-settings")
					.hide();
				$(
					"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
				)
					.closest(".user-registration-global-settings")
					.hide();
			}

			// Common Hide for V2
			$("#user_registration_captcha_setting_recaptcha_threshold_score_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key_hcaptcha")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_hcaptcha"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_key_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret_v3")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_key_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_site_secret_cloudflare"
			)
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_cloudflare_theme")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_invisible_recaptcha_v2")
				.closest(".user-registration-global-settings")
				.show();
		}
	}

	var captchaSettingsChanged = false;

	$(".user-registration-global-settings")
		.find("input, select")
		.on("change", function () {
			captchaSettingsChanged = true;
			$("#user_registration_captcha_setting_captcha_test")
				.parent()
				.hide();
		});

	/**
	 * Test Captcha from settings page.
	 */
	$(".user-registration").on(
		"click",
		"#user_registration_captcha_setting_captcha_test",
		function (e) {
			e.preventDefault();
			e.stopPropagation();

			if (captchaSettingsChanged) {
				alert(user_registration_settings_params.i18n.unsaved_changes);
				return;
			}

			var spinner = $(
				"#user_registration_captcha_setting_captcha_test .spinner"
			);
			spinner.show();

			setTimeout(function () {
				spinner.hide();
			}, 2500);

			var ur_recaptcha_node = $("#ur-captcha-node");
			if (
				"undefined" !== typeof ur_recaptcha_code &&
				ur_recaptcha_code.site_key.length
			) {
				if (ur_recaptcha_node.length !== 0) {
					switch (ur_recaptcha_code.version) {
						case "v2":
							google_recaptcha_login = grecaptcha.render(
								ur_recaptcha_node
									.find(".g-recaptcha")
									.attr("id"),
								{
									sitekey: ur_recaptcha_code.site_key,
									theme: "light",
									style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
								}
							);

							if (ur_recaptcha_code.is_invisible) {
								grecaptcha
									.execute(google_recaptcha_login)
									.then(function (token) {
										if (null !== token) {
											display_captcha_test_status(
												user_registration_settings_params
													.i18n.captcha_failed,
												"error"
											);
											return;
										} else {
											display_captcha_test_status(
												user_registration_settings_params
													.i18n.captcha_success,
												"success"
											);
										}
									});
							}
							break;

						case "v3":
							try {
								grecaptcha
									.execute(ur_recaptcha_code.site_key, {
										action: "click",
									})
									.then(function (d) {
										display_captcha_test_status(
											user_registration_settings_params
												.i18n.captcha_success,
											"success"
										);
									});
							} catch (err) {
								display_captcha_test_status(
									err.message,
									"error"
								);
							}
							break;

						case "hCaptcha":
							google_recaptcha_login = hcaptcha.render(
								ur_recaptcha_node
									.find(".g-recaptcha-hcaptcha")
									.attr("id"),
								{
									sitekey: ur_recaptcha_code.site_key,
									theme: "light",
									style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
								}
							);
							break;

						case "cloudflare":
							try {
								turnstile.render(
									"#" +
										ur_recaptcha_node
											.find(".cf-turnstile")
											.attr("id"),
									{
										sitekey: ur_recaptcha_code.site_key,
										theme: ur_recaptcha_code.theme_mode,
										style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
									}
								);

								ur_recaptcha_node
									.find("iframe")
									.css("display", "block");
							} catch (err) {
								display_captcha_test_status(
									err.message,
									"error"
								);
							}
							break;
					}
				}
			}
		}
	);

	/**
	 *
	 * @param {string} notice Notice message.
	 * @param {string} type Notice type.
	 */
	function display_captcha_test_status(notice, type) {
		if (notice.length) {
			var notice_container = $("#ur-captcha-notice");
			var notice_icon = $("#ur-captcha-notice--icon");
			var notice_text = $("#ur-captcha-notice--text");

			if (notice_text.length) {
				notice_text.html(notice);

				if ("success" === type) {
					notice_container.removeClass().addClass("success");
					notice_icon.addClass("dashicons dashicons-yes-alt");
				} else if ("error" === type) {
					notice_container.removeClass().addClass("error");
					notice_icon.addClass("dashicons dashicons-dismiss");
				}
			}
		}

		var spinner = $(
			"#user_registration_captcha_setting_captcha_test .spinner"
		);
		spinner.hide();
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

	$(".user-registration #mainform").on("keyup keypress", function (e) {
		var keyCode = e.keyCode || e.which;
		if (keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	// Set up the autocomplete feature
	$(".user-registration #ur-search-settings").autocomplete({
		source: function (request, response) {
			// Make an AJAX call to the PHP script with the search query as data
			var search_string = request.term;
			var form_data = new FormData();
			form_data.append("search_string", search_string);
			form_data.append(
				"action",
				"user_registration_search_global_settings"
			);
			form_data.append(
				"security",
				user_registration_settings_params.user_registration_search_global_settings_nonce
			);
			$(".user-registration-search-icon").hide();

			$.ajax({
				url: user_registration_settings_params.ajax_url,
				dataType: "json", // what to expect back from the PHP script, if anything
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: "post",
				complete: function (responsed) {
					if (responsed.responseJSON.success === true) {
						var results = responsed.responseJSON.data.results;
						response(results);
					}
					$(".user-registration-search-icon").show();
				},
			});
		},
		classes: {
			"ui-autocomplete": "user-registration-ui-autocomplete",
		},
		minLength: 3, // Minimum characters required to trigger autocomplete
		focus: function (event, ui) {
			$(".user-registration-ui-autocomplete > li").attr(
				"title",
				ui.item.desc
			);
			$("#ur-search-settings").val(ui.item.label);
			return false;
		},
		select: function (event, ui) {
			// Update the input field value with the selected value
			if ("no_result_found" !== ui.item.value) {
				$(".user-registration #ur-search-settings").val(ui.item.label);
				// Redirect the user to the selected URL
				window.location.href = ui.item.value;
			}
			return false; // Prevent the default behavior of the widget
		},
	});

	// Set localStorage with expiry
	function setStorageValue(key, value) {
		var current = new Date();

		var data = {
			value: value,
			expiry: current.getTime() + 86400000, // 1day of expiry time
		};

		localStorage.setItem(key, JSON.stringify(data));
	}

	// Get localStorage with expiry
	function getStorageValue(key) {
		var item = localStorage.getItem(key);

		if (!item) {
			return false;
		}

		var data = JSON.parse(item);
		var current = new Date();

		if (current.getTime() > data.expiry) {
			localStorage.removeItem(key);
			return false;
		}
		return true;
	}

	// Handles collapse of side menu.
	$("#ur-settings-collapse").on("click", function (e) {
		e.preventDefault();

		if ($(this).hasClass("close")) {
			$(this).closest("header").addClass("collapsed");
			$(this).removeClass("close").addClass("open");
			setStorageValue("ur-settings-navCollapsed", true); // set to localStorage
		} else {
			$(this).closest("header").removeClass("collapsed");
			$(this).removeClass("open").addClass("close");
			localStorage.removeItem("ur-settings-navCollapsed"); // remove from localStorage
		}
	});

	// Persist the collapsable state through page reload
	var isNavCollapsed =
		getStorageValue("ur-settings-navCollapsed") === true
			? "collapsed"
			: "not-collapsed";
	if (isNavCollapsed == "collapsed") {
		$(".user-registration-header").addClass("collapsed");
		$("#ur-settings-collapse").removeClass("close").addClass("open");
	} else {
		$(".user-registration-header").removeClass("collapsed");
		$("#ur-settings-collapse").removeClass("open").addClass("close");
	}

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

	if (
		typeof getUrlVars()["searched_option"] != "undefined" ||
		getUrlVars()["searched_option"] != null
	) {
		var $searched_id = $("#" + getUrlVars()["searched_option"]);
		var wrapper_div = $searched_id.closest(
			".user-registration-global-settings"
		);
		wrapper_div.addClass("ur-searched-settings-focus");

		var offset = $(".ur-searched-settings-focus").offset().top;
		window.scrollTo({
			top: offset - 300,
			behavior: "smooth",
		});
		setTimeout(function () {
			wrapper_div.removeClass("ur-searched-settings-focus");
		}, 2000);
	}
	/**
	 * Get Query String.
	 *
	 * @returns
	 */
	function getUrlVars() {
		var vars = [],
			hash;
		var hashes = window.location.href
			.slice(window.location.href.indexOf("?") + 1)
			.split("&");
		for (var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split("=");
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}

	$(document)
		.find(".user-registration-global-settings--field")
		.find(".ur-radio-group-list--item")
		.each(function () {
			$(this).on("click", function () {
				$(this)
					.closest(".ur-radio-group-list")
					.find(".active")
					.find("input")
					.prop("checked", false);
				$(this)
					.closest(".ur-radio-group-list")
					.find(".active")
					.removeClass("active");
				$(this).addClass("active");
				$(this).find("input").prop("checked", true);
			});
		});
})(jQuery);
