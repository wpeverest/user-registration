/* global user_registration_settings_params, ur_login_form_params, UR_Snackbar */
(function ($) {
	if (UR_Snackbar) {
		var snackbar = new UR_Snackbar();
	}

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
			border: true
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
	var recaptchav2_invisible_input_value = $(".user-registration")
		.find("#user_registration_captcha_setting_invisible_recaptcha_v2")
		.is(":checked");

	if (recaptchav2_invisible_input_value != undefined) {
		handleReCaptchaHideShow(recaptchav2_invisible_input_value);
	}

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
		}
	);

	function handleReCaptchaHideShow(value) {
		if (value) {
			$("#user_registration_captcha_setting_recaptcha_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_site_secret")
				.closest(".user-registration-global-settings")
				.hide();
			$("#user_registration_captcha_setting_recaptcha_invisible_site_key")
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
			$("#user_registration_captcha_setting_recaptcha_invisible_site_key")
				.closest(".user-registration-global-settings")
				.hide();
			$(
				"#user_registration_captcha_setting_recaptcha_invisible_site_secret"
			)
				.closest(".user-registration-global-settings")
				.hide();
		}
	}

	var captchaSettingsChanged = false;

	$(".user-registration-global-settings")
		.find("input, select")
		.on("change", function () {
			captchaSettingsChanged = true;
			$(this)
				.closest(".ur-captcha-settings-body")
				.find(".user_registration_captcha_setting_captcha_test")
				.closest(".user-registration-global-settings")
				.hide();
		});
	/**
	 * Test Captcha from settings page.
	 */
	$(".user-registration").on(
		"click",
		".user_registration_captcha_setting_captcha_test",
		function (e) {
			e.preventDefault();
			e.stopPropagation();

			if (captchaSettingsChanged) {
				alert(user_registration_settings_params.i18n.unsaved_changes);
				return;
			}

			var captcha_type = $(this).attr("data-captcha-type"),
				invisible_recaptcha = false;

			if ("v2" === captcha_type) {
				var invisible_recaptcha = $(
					"#user_registration_captcha_setting_invisible_recaptcha_v2"
				).is(":checked");
			}

			$.ajax({
				type: "POST",
				url: user_registration_settings_params.ajax_url,
				data: {
					action: "user_registration_captcha_test",
					security:
						user_registration_settings_params.user_registration_captcha_test_nonce,
					captcha_type: captcha_type,
					invisible_recaptcha: invisible_recaptcha
				},
				beforeSend: function () {
					var spinner = $(
						"#user_registration_captcha_setting_" +
							captcha_type +
							"_captcha_test .spinner"
					);
					spinner.show();
					setTimeout(function () {
						spinner.hide();
					}, 2500);
				},
				success: function (response) {
					var ur_recaptcha_node = $(
							'.ur-captcha-test-container[data-captcha-type="' +
								captcha_type +
								'"] .ur-captcha-node'
						),
						ur_recaptcha_code = response.data.ur_recaptcha_code;

					if (
						"undefined" !== typeof ur_recaptcha_code &&
						ur_recaptcha_code.site_key.length
					) {
						if (ur_recaptcha_node.length !== 0) {
							switch (captcha_type) {
								case "v2":
									google_recaptcha_login = grecaptcha.render(
										ur_recaptcha_node
											.find(".g-recaptcha")
											.attr("id"),
										{
											sitekey: ur_recaptcha_code.site_key,
											theme: "light",
											style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;"
										}
									);

									if (
										"false" !==
										ur_recaptcha_code.is_invisible
									) {
										grecaptcha
											.execute(google_recaptcha_login)
											.then(function (token) {
												if (null !== token) {
													display_captcha_test_status(
														user_registration_settings_params
															.i18n
															.captcha_failed,
														"error",
														captcha_type
													);
													return;
												} else {
													display_captcha_test_status(
														user_registration_settings_params
															.i18n
															.captcha_success,
														"success",
														captcha_type
													);
												}
											});
									}
									break;

								case "v3":
									try {
										grecaptcha
											.execute(
												ur_recaptcha_code.site_key,
												{
													action: "click"
												}
											)
											.then(function (d) {
												display_captcha_test_status(
													user_registration_settings_params
														.i18n.captcha_success,
													"success",
													captcha_type
												);
											});
									} catch (err) {
										display_captcha_test_status(
											err.message,
											"error",
											captcha_type
										);
									}
									break;

								case "hCaptcha":
									console.log(hcaptcha);

									google_recaptcha_login = hcaptcha.render(
										ur_recaptcha_node
											.find(".g-recaptcha-hcaptcha")
											.attr("id"),
										{
											sitekey: ur_recaptcha_code.site_key,
											theme: "light",
											"error-callback": function (e) {
												console.log(e);
											},
											style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;"
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
												sitekey:
													ur_recaptcha_code.site_key,
												theme: ur_recaptcha_code.theme_mode,
												style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;"
											}
										);

										ur_recaptcha_node
											.find("iframe")
											.css("display", "block");
									} catch (err) {
										display_captcha_test_status(
											err.message,
											"error",
											captcha_type
										);
									}
									break;
							}
						}
					}

					if (!response.success) {
						var msg = response.data;
						console.log(msg);
						display_captcha_test_status(msg, "error", captcha_type);
						return;
					}
				}
			});
		}
	);

	/**
	 *
	 * @param {string} notice Notice message.
	 * @param {string} type Notice type.
	 */
	function display_captcha_test_status(notice, type, captcha_type) {
		if (notice.length) {
			var notice_container = $(
				'.ur-captcha-test-container[data-captcha-type="' +
					captcha_type +
					'"]'
			).find(".ur-captcha-notice");
			var notice_icon = $(
				'.ur-captcha-test-container[data-captcha-type="' +
					captcha_type +
					'"]'
			).find(".ur-captcha-notice--icon");
			var notice_text = $(
				'.ur-captcha-test-container[data-captcha-type="' +
					captcha_type +
					'"]'
			).find(".ur-captcha-notice--text");

			if (notice_text.length) {
				notice_text.html(notice);

				if ("success" === type) {
					notice_container
						.removeClass()
						.addClass("success")
						.addClass("ur-captcha-notice");
					notice_icon.addClass("dashicons dashicons-yes-alt");
				} else if ("error" === type) {
					notice_container
						.removeClass()
						.addClass("error")
						.addClass("ur-captcha-notice");
					notice_icon.addClass("dashicons dashicons-dismiss");
				}
			}
		}

		var spinner = $(
			"#user_registration_captcha_setting_" +
				captcha_type +
				"_captcha_test .spinner"
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
				.closest(".user-registration-login-form-global-settings--field")
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
	// Display the sync profile picture settings when the disable profile picture is checked and advanced fields is active.
	$("#user_registration_disable_profile_picture").on("change", function () {
		var is_advanced_fields_active = parseInt(
			user_registration_settings_params.is_advanced_field_active
		);
		if ($(this).prop("checked") && is_advanced_fields_active === 1) {
			$("#user_registration_sync_profile_picture")
				.closest(".user-registration-global-settings")
				.css("display", "flex");
		} else {
			$("#user_registration_sync_profile_picture").prop("checked", false);
			$("#user_registration_sync_profile_picture")
				.closest(".user-registration-global-settings")
				.css("display", "none");
		}
	});
	// If not checked on load hide the sync profile picture settings.
	$("#user_registration_sync_profile_picture").ready(function () {
		$this = $("#user_registration_sync_profile_picture");
		if ($this.prop("checked")) {
			$this
				.closest(".user-registration-global-settings")
				.css("display", "flex");
		} else if (
			$("#user_registration_disable_profile_picture").prop("checked") &&
			parseInt(
				user_registration_settings_params.is_advanced_field_active
			) === 1
		) {
			$this
				.closest(".user-registration-global-settings")
				.css("display", "flex");
		} else {
			$this
				.closest(".user-registration-global-settings")
				.css("display", "none");
		}
	});

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
					type: ["image"]
				},
				title: ur_uploader.upload_file,
				// multiple: true if you want to upload multiple files at once
				multiple: false
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
				}
			});
		},
		classes: {
			"ui-autocomplete": "user-registration-ui-autocomplete"
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
		}
	});

	// Display error when page with our my account or login shortcode is not selected
	$("#user_registration_myaccount_page_id").on("change", function () {
		var $this = $(this),
			data = {
				action: "user_registration_my_account_selection_validator",
				security:
					user_registration_settings_params.user_registration_my_account_selection_validator_nonce
			};

		data.user_registration_selected_my_account_page = $this.val();

		$this.prop("disabled", true);
		$this.css("border", "1px solid #e1e1e1");
		$this
			.closest(".user-registration-global-settings--field")
			.find(".error.inline")
			.remove();
		$this
			.closest(".user-registration-global-settings")
			.append('<div class="ur-spinner is-active"></div>');

		$.ajax({
			url: user_registration_settings_params.ajax_url,
			data: data,
			type: "POST",
			complete: function (response) {
				if (response.responseJSON.success === false) {
					$this
						.closest(".user-registration-global-settings--field")
						.append(
							"<div id='message' class='error inline' style='padding:10px;'>" +
								response.responseJSON.data.message +
								"</div>"
						);
					$this.css("border", "1px solid red");
					$this
						.closest("form")
						.find("input[name='save']")
						.prop("disabled", true);
				} else {
					$this
						.closest("form")
						.find("input[name='save']")
						.prop("disabled", false);
					$this
						.closest(".user-registration-global-settings")
						.find(".error inline")
						.remove();
				}
				$this.prop("disabled", false);

				$this
					.closest(".user-registration-global-settings")
					.find(".ur-spinner")
					.remove();
			}
		});
	});

	// Display error when page with our lost password shortcode is not selected.
	$("#user_registration_lost_password_page_id").on("change", function () {
		var $this = $(this),
			data = {
				action: "user_registration_lost_password_selection_validator",
				security:
					ur_login_form_params.user_registration_lost_password_selection_validator_nonce
			};

		data.user_registration_selected_lost_password_page = $this.val();

		$this.prop("disabled", true);
		$this.css("border", "1px solid #e1e1e1");

		$this
			.closest(".user-registration-global-settings--field")
			.find(".error.inline")
			.remove();

		$.ajax({
			url: ur_login_form_params.ajax_url,
			data: data,
			type: "POST",
			complete: function (response) {
				if (response.responseJSON.success === false) {
					if (
						$this
							.closest(
								".user-registration-login-form-global-settings"
							)
							.find(".error.inline").length === 0
					) {
						$this
							.closest(
								".user-registration-login-form-global-settings"
							)
							.append(
								"<div id='message' class='error inline' style='padding:10px;'>" +
									response.responseJSON.data.message +
									"</div>"
							);
					}
					$this.css("border", "1px solid red");
					var login_form = $this.closest(
						".user-registration-login-form-container"
					);
					$(login_form)
						.closest("#wpbody-content")
						.find("#ur-lists-page-topnav")
						.find('button[name="save_login_form"]')
						.prop("disabled", true);
				} else {
					var login_form = $this.closest(
						".user-registration-login-form-container"
					);
					$(login_form)
						.closest("#wpbody-content")
						.find("#ur-lists-page-topnav")
						.find('button[name="save_login_form"]')
						.prop("disabled", false);
					$this
						.closest(
							".user-registration-login-form-global-settings"
						)
						.find(".error.inline")
						.remove();
				}

				$this.prop("disabled", false);
			}
		});
	});

	// Set localStorage with expiry
	function setStorageValue(key, value) {
		var current = new Date();

		var data = {
			value: value,
			expiry: current.getTime() + 86400000 // 1day of expiry time
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
			behavior: "smooth"
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

	/**
	 * Display the upgrade message for the top addons.
	 */
	$("body").on("click", ".user-registration-inactive-addon", function (e) {
		$this = $(this);
		e.preventDefault();
		var video_id = $this.data("video");
		var plugin_title = $this.data("title");
		var available_in = $(this).data("available-in");
		var video = "";

		if (video_id !== "") {
			video =
				'<div style="width: 535px; height: 300px;"><iframe width="100%" height="100%" frameborder="0" src="https://www.youtube.com/embed/' +
				video_id +
				'" rel="1" allowfullscreen></iframe></div><br>';
		}
		var icon =
			'<i class="dashicons dashicons-lock" style="color:#475bb2; border-color: #475bb2;"></i>';

		var message =
			video + user_registration_settings_params.i18n.upgrade_message;

		message = message
			.replace("%title%", plugin_title)
			.replace("%plan%", available_in);

		var title =
			icon +
			'<span class="user-registration-swal2-modal__title">' +
			plugin_title +
			" " +
			user_registration_settings_params.i18n.pro_feature_title;
		("</span>");
		Swal.fire({
			title: title,
			html: message,
			customClass:
				"user-registration-swal2-modal user-registration-swal2-modal--centered user-registration-locked-field",
			showCloseButton: true,
			showConfirmButton: true,
			allowOutsideClick: true,
			heightAuto: false,
			width: "575px",
			confirmButtonText:
				user_registration_settings_params.i18n.upgrade_plan
		}).then(function (result) {
			if (result.isConfirmed) {
				window.open(
					user_registration_settings_params.i18n.upgrade_link,
					"_blank"
				);
			}
		});
	});

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

	// Function to handle changes in the premium sidebar.
	$(document).ready(function () {
		function handleSettingsSidebar(node) {
			var isCheckboxChecked = $(node).is(":checked");

			localStorage.setItem("isSidebarEnabled", isCheckboxChecked);

			document.cookie =
				"isSidebarEnabled=" + isCheckboxChecked + "; path=/;";

			if (isCheckboxChecked) {
				$("body")
					.removeClass("ur-settings-sidebar-hidden")
					.addClass("ur-settings-sidebar-show");
				$(node)
					.closest(".user-registration-options-header--top__right")
					.find(".user-registration-toggle-text")
					.text("Sidebar");
			} else {
				$("body")
					.removeClass("ur-settings-sidebar-show")
					.addClass("ur-settings-sidebar-hidden");
				$(node)
					.closest(".user-registration-options-header--top__right")
					.find(".user-registration-toggle-text")
					.text("Sidebar");
			}
		}

		$(document).on(
			"change",
			"#user_registration_hide_show_sidebar",
			function (e) {
				handleSettingsSidebar($(this));
			}
		);

		disableFormChangeModal();
		init_accordion_settings();
	});

	/**
	 * Initialize accordion_settings elements.
	 */
	function init_accordion_settings() {
		var acc = document.getElementsByClassName("accordion");
		var i;
		for (i = 0; i < acc.length; i++) {
			var panel = acc[i].nextElementSibling;
			panel.style.display = "none";

			acc[i].addEventListener("click", function () {
				/* Toggle between adding and removing the "active" class,
			to highlight the button that controls the panel */
				this.classList.toggle("active");

				/* Toggle between hiding and showing the active panel */
				var panel = this.nextElementSibling;
				if (panel.style.display === "block") {
					panel.style.display = "none";
				} else {
					panel.style.display = "block";
				}
			});
		}

		$.each($(".ur-captcha-settings"), function () {
			var is_enabled = $(this)
				.find(".ur-captcha-settings-body .ur-captcha-enable")
				.is(":checked");
			if (is_enabled) {
				$(this)
					.find(".ur-captcha-settings-header .integration-status")
					.addClass("ur-integration-account-connected");
			}
		});
	}

	/**
	 * Disable leave page before saving changes modal when hid/show sidebar is clicked.
	 */
	function disableFormChangeModal() {
		var form = $(".user-registration").find("form")[0];

		var formChanged = false;

		$(form).on("change", function (event) {
			if (event.target.name !== "user_registration_enable_sidebar") {
				formChanged = true;
			}
		});

		var skipBeforeUnloadPopup = false;
		$(form).on("submit", function () {
			skipBeforeUnloadPopup = true;
		});

		$(form)
			.find(".ur-nav__link")
			.on("click", function () {
				skipBeforeUnloadPopup = true;
			});

		$(window).on("beforeunload", function (event) {
			if (formChanged && !skipBeforeUnloadPopup) {
				event.preventDefault();
				event.returnValue = "";
			} else {
				event.stopImmediatePropagation();
			}
		});
	}

	/**
	 * Show success message using snackbar.
	 *
	 * @param {String} message Message to show.
	 */
	function show_success_message(message) {
		if (snackbar) {
			snackbar.add({
				type: "success",
				message: message,
				duration: 5
			});
			return true;
		}
		return false;
	}

	/**
	 * Show failure message using snackbar.
	 *
	 * @param {String} message Message to show.
	 */
	function show_failure_message(message) {
		if (snackbar) {
			snackbar.add({
				type: "failure",
				message: message,
				duration: 6,
				dismissible: true
			});
			return true;
		}
		return false;
	}

	function update_payment_section_settings(
		setting_id,
		section_data,
		$this,
		settings_container
	) {
		$.ajax({
			url: user_registration_settings_params.ajax_url,
			data: {
				action: "user_registration_save_payment_settings",
				security:
					user_registration_settings_params.user_registration_membership_payment_settings_nonce,
				setting_id: setting_id,
				section_data: JSON.stringify(section_data)
			},
			type: "POST",
			complete: function (response) {
				$this.find(".ur-spinner").remove();
				if (response.responseJSON.success) {
					show_success_message(response.responseJSON.data.message);
					settings_container
						.find(".integration-status")
						.addClass("ur-integration-account-connected");
				} else {
					show_failure_message(response.responseJSON.data.message);
				}
			}
		});
	}

	$(document)
		.find(".wp-list-table")
		.wrap("<div class='ur-list-table-wrapper'></div>");

	$(
		"#user_registration_member_registration_page_id, #user_registration_thank_you_page_id"
	).on("change", function () {
		var $this = $(this),
			type = $this.attr("id"),
			val = $(this).val();
		// $this.prop("disabled", true);

		$this
			.closest(".user-registration-global-settings--field")
			.find(".error.inline")
			.remove();
		$this
			.closest(".user-registration-global-settings")
			.find(".ur-spinner")
			.remove();
		$this
			.closest(".user-registration-global-settings")
			.append('<div class="ur-spinner is-active"></div>');

		$.ajax({
			url: user_registration_settings_params.ajax_url,
			data: {
				action: "user_registration_membership_verify_pages",
				type: type,
				value: val,
				security:
					user_registration_settings_params.user_registration_membership_pages_selection_validator_nonce
			},
			type: "POST",
			complete: function (response) {
				if (response.responseJSON.status === false) {
					$this
						.closest(".user-registration-global-settings--field")
						.append(
							"<div id='message' class='error inline' style='padding:10px;'>" +
								response.responseJSON.message +
								"</div>"
						);

					$this
						.closest("form")
						.find("input[name='save']")
						.prop("disabled", true);
				} else {
					if (
						$this
							.closest(".user-registration-options-container")
							.find(".error.inline").length
					) {
						$this
							.closest("form")
							.find("input[name='save']")
							.prop("disabled", true);
					} else {
						$this
							.closest("form")
							.find("input[name='save']")
							.prop("disabled", false);
					}
				}
				$this.prop("disabled", false);

				$this
					.closest(".user-registration-global-settings")
					.find(".ur-spinner")
					.remove();
			}
		});
	});

	$(".payment-settings-btn").on("click", function () {
		var $this = $(this),
			setting_id = $this.data("id"),
			settings_container = $this.closest("#" + setting_id);

		if ($this.find(".ur-spinner").length > 0) {
			return;
		}
		$this.append("<span class='ur-spinner'></span>");

		var section_data = {};

		settings_container
			.find("input, select, textarea")
			.each(function (key, item) {
				var $item = $(item);
				var name = $item.attr("name");
				if (!name) return;

				var value;
				if ($item.attr("type") === "checkbox") {
					value = $item.is(":checked");
				} else if (
					$item.is("textarea") &&
					typeof tinymce !== "undefined" &&
					tinymce.get(name)
				) {
					value = tinymce.get(name).getContent();
				} else {
					value = $item.val();
				}
				section_data[name] = value;
			});

		update_payment_section_settings(
			setting_id,
			section_data,
			$this,
			settings_container
		);
	});
	$("#user_registration_payment_currency").on("change", function () {
		var $this = $(this);
		var currency = $this.val();
		$this
			.closest(".user-registration-global-settings--field")
			.find(".error.inline")
			.remove();
		$this
			.closest(".user-registration-global-settings")
			.append('<div class="ur-spinner is-active"></div>');

		$.ajax({
			url: user_registration_settings_params.ajax_url,
			data: {
				action: "user_registration_validate_payment_currency",
				security:
					user_registration_settings_params.user_registration_membership_validate_payment_currency_nonce,
				currency: currency
			},
			type: "POST",
			complete: function (response) {
				if (response.responseJSON.success === false) {
					$this
						.closest(".user-registration-global-settings")
						.find(".warning")
						.remove();
					$this
						.closest(".user-registration-global-settings--field")
						.append(
							"<div id='message' class='warning inline' style='padding:10px;'>" +
								response.responseJSON.data.message +
								"</div>"
						);
				} else {
					$this
						.closest(".user-registration-global-settings")
						.find(".warning")
						.remove();
				}
				$this.prop("disabled", false);
				$this
					.closest(".user-registration-global-settings")
					.find(".ur-spinner")
					.remove();
			}
		});
	});
	$("#user_registration_payment_currency").trigger("change");
	var searchParams = new URLSearchParams(window.location.search);

	var license_activation_status = ur_get_cookie("urm_license_status");
	if (
		searchParams.get("activated_license") == "user-registration" &&
		license_activation_status === "license_activated"
	) {
		ur_remove_cookie("urm_license_status");
		var urmProInstallHtml =
			'<div style="display: flex; align-items: center; width: 60%;margin: 0px auto; position: relative;">' +
			'<img src="' +
			user_registration_settings_params.assets_url +
			'/images/logo.png" alt="URM Logo" width="50" style="margin: 0 20px;" />' +
			'<img src="' +
			user_registration_settings_params.assets_url +
			'/images/connect.gif" alt="Connect gif" >' +
			'<img src="' +
			user_registration_settings_params.assets_url +
			'/images/wordpress.png" ' +
			'alt="WordPress Logo" width="50" style="margin: 0 10px 0 30px;" />' +
			"</div>" +
			'<p style="margin-bottom: 20px;font-size:13px;text-align:center;">' +
			user_registration_settings_params.i18n.license_activated_text +
			"</p>" +
			'<form method="post" class="ur-install-urm-pro">' +
			'<input type="hidden" name="download_user_registration_pro" value="1" />' +
			'<input type="hidden" name="ur_license_nonce" value="' +
			user_registration_settings_params.ur_license_nonce +
			'" />' +
			'<button id="install-urm-pro-btn" class="swal2-confirm button button-primary" style="margin-bottom: 20px;">' +
			user_registration_settings_params.i18n.pro_install_popup_button +
			"</button>" +
			"</form>" +
			'<p class="ur-install-urm-pro-p-tag" style="font-size:13px;text-align:center;">' +
			user_registration_settings_params.i18n
				.will_install_and_activate_pro_text +
			"</p>";
		Swal.fire({
			title: user_registration_settings_params.i18n
				.pro_install_popup_title,
			html: urmProInstallHtml,
			showConfirmButton: false,
			showCloseButton: false,
			allowOutsideClick: false,

			customClass:
				"user-registration-swal2-modal user-registration-swal2-modal--centered user-registration-swal2-modal--install-urm-pro",
			width: 600,
			didOpen: function () {
				$("#install-urm-pro-btn").on("click", function () {
					$(this)
						.prop("disabled", true)
						.text(
							user_registration_settings_params.i18n
								.installing_plugin_text
						)
						.prepend(
							'<div class="ur-spinner is-active" style="margin-right: 8px;"></div>'
						);
					$(this).closest("form").submit();
				});
			}
		});
	}
	if (
		searchParams.get("activated_license") == "user-registration" &&
		license_activation_status === "pro_activated"
	) {
		ur_remove_cookie("urm_license_status");
		$successModalHtml =
			'<p style="margin: 10px 0 20px;">' +
			user_registration_settings_params.i18n.pro_activated_success_text +
			"</p>" +
			'<button id="dashboard-redirect-btn" style="' +
			"background: transparent;" +
			"border: 1px solid #475bb2;" +
			"color: #475bb2;" +
			"padding: 8px 16px;" +
			"margin-bottom: 16px;" +
			"border-radius: 6px;" +
			"font-size: 14px;" +
			"cursor: pointer;" +
			'">' +
			user_registration_settings_params.i18n.continue_to_dashboard_text +
			"</button>";
		Swal.fire({
			icon: "success",
			title: user_registration_settings_params.i18n
				.pro_activated_success_title,
			html: $successModalHtml,
			showConfirmButton: false,
			showCloseButton: true,
			customClass:
				"user-registration-swal2-modal user-registration user-registration-swal2-modal--center user-registration-info swal2-show",
			width: 400,
			didOpen: function () {
				$("#dashboard-redirect-btn").on("click", function () {
					window.location.href =
						"/wp-admin/admin.php?page=user-registration-dashboard";
				});
			}
		});
	}

	var searchParams = new URLSearchParams(window.location.search);
	if (
		searchParams.has("method") &&
		searchParams.get("method") !== "" &&
		$(".user-registration-settings-container").find(
			"#" + searchParams.get("method")
		).length > 0
	) {
		var container = $(".user-registration-settings-container").find(
			"#" + searchParams.get("method")
		);
		setTimeout(function () {
			container.find(".integration-header-info").trigger("click");
		}, 400);
	}

	/**
	 * Retrieves the cookie values set.
	 */
	function ur_get_cookie(cookie_key) {
		var matches = document.cookie.match(
			new RegExp(
				"(?:^|; )" +
					cookie_key.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") +
					"=([^;]*)"
			)
		);
		return matches ? decodeURIComponent(matches[1]) : undefined;
	}

	/**
	 * Deletes the cookie values.
	 */
	function ur_remove_cookie(cookie_key) {
		document.cookie = cookie_key + "=; Max-Age=-99999999; path=/";
	}
})(jQuery);
