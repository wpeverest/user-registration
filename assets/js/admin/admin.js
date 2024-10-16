/**
 * UserRegistration Admin JS
 * global i18n_admin
 */
jQuery(function ($) {
	// Bind UI Action handlers for searching fields.
	$(document.body).on("input", "#ur-search-fields", function () {
		var search_string = $(this).val().toLowerCase();

		// Show/Hide fields.
		$(".ur-registered-item").each(function () {
			var field_label = $(this).text().toLowerCase();
			if (field_label.search(search_string) > -1) {
				$(this).addClass("ur-searched-item");
				$(this).show();
			} else {
				$(this).removeClass("ur-searched-item");
				$(this).hide();
			}
		});

		// Show/Hide field sections.
		$(".ur-registered-list").each(function () {
			var search_result_fields_count = $(this).find(
				".ur-registered-item.ur-searched-item"
			).length;
			var hr = $(this).prev("hr"),
				heading = $(this).prev("hr").prev(".ur-toggle-heading");

			if (0 === search_result_fields_count) {
				hr.hide();
				heading.hide();
				$(this).hide();
			} else {
				hr.show();
				heading.show();
				$(this).show();
			}
		});

		// Show/Hide fields not found indicator.
		if ($(".ur-registered-item.ur-searched-item").length) {
			$(".ur-fields-not-found").hide();
		} else {
			$(".ur-fields-not-found").show();
		}
	});

	//Bind UI Actions for locked fields
	$(document).on("mousedown", ".ur-locked-field", function (e) {
		e.preventDefault();
		var icon =
			'<i class="dashicons dashicons-lock" style="color:#72aee6; border-color: #72aee6;"></i>';

		if ($(this).hasClass("ur-one-time-draggable-disabled")) {
			var title =
					icon +
					'<span class="user-registration-swal2-modal__title">' +
					user_registration_form_builder_data.form_one_time_draggable_fields_locked_title.replace(
						"%field%",
						$(this).text()
					) +
					"</span>",
				message =
					user_registration_form_builder_data.form_one_time_draggable_fields_locked_message.replace(
						"%field%",
						$(this).text()
					);

			Swal.fire({
				title: title,
				html: message,
				showCloseButton: true,
				customClass:
					"user-registration-swal2-modal user-registration-swal2-modal--center user-registration-locked-field"
			}).then(function (result) {
				// Do Nothing here.
			});
		} else {
			var field_data = $(this).data("field-data");
			var title =
				icon +
				'<span class="user-registration-swal2-modal__title">' +
				field_data.title +
				"</span>";
			Swal.fire({
				title: title,
				html: field_data.message,
				showCloseButton: true,
				customClass:
					"user-registration-swal2-modal user-registration-swal2-modal--center user-registration-locked-field",
				confirmButtonText: field_data.button_title
			}).then(function (result) {
				if (result.value) {
					var url = field_data.link;
					window.open(url, "_blank");
				}
			});
		}
	});
	// Bind UI Actions for upgradable fields
	$(document).on("click", ".ur-upgradable-field", function (e) {
		e.preventDefault();

		var icon =
			'<i class="dashicons dashicons-lock" style="color:#72aee6; border-color: #72aee6;"></i>';
		var plan = $(this).data("plan");
		var name = $(this).data("name");
		var video_id = $(this).data("video");
		var slug = $(this).data("slug"),
			$this = $(this);

		if (slug != "" && plan != "") {
			$.ajax({
				url: user_registration_locked_form_fields_notice_params.ajax_url,
				type: "POST",
				data: {
					action: "user_registration_locked_form_fields_notice",
					slug: slug,
					plan: plan,
					name: name,
					video_id: video_id,
					security:
						user_registration_locked_form_fields_notice_params.user_registration_locked_form_fields_notice_nonce
				},
				success: function (response) {
					if (video_id !== "") {
						var video =
							'<div style="width: 535px; height: 300px;"><iframe width="100%" height="100%" frameborder="0" src="https://www.youtube.com/embed/' +
							video_id +
							'" rel="1" allowfullscreen></iframe></div><br>';
					}
					var action_button = $(response.data.action_button).find(
						"a"
					);

					if (!action_button.length) {
						action_button = $(response.data.action_button).find(
							"form"
						);
					}

					var title =
						icon +
						'<span class="user-registration-swal2-modal__title" > ';

					if (action_button.hasClass("activate-license-now")) {
						var message =
							user_registration_locked_form_fields_notice_params.license_activation_required_message;
						title +=
							user_registration_locked_form_fields_notice_params.license_activation_required_title;
					} else if (action_button.hasClass("activate-now")) {
						var message =
							user_registration_locked_form_fields_notice_params.activation_required_message.replace(
								"%plugin%",
								name
							);
						title +=
							user_registration_locked_form_fields_notice_params.activation_required_title;
					} else if (action_button.hasClass("install-now")) {
						var message =
							user_registration_locked_form_fields_notice_params.installation_required_message.replace(
								"%plugin%",
								name
							);
						title +=
							user_registration_locked_form_fields_notice_params.installation_required_title;
					} else {
						var message =
							user_registration_locked_form_fields_notice_params.unlock_message
								.replace("%field%", $this.text())
								.replace("%plan%", plan);
						title +=
							$this.text() +
							" " +
							user_registration_locked_form_fields_notice_params.lock_message;
					}

					title += "</span>";
					message =
						video +
						message +
						"<br><br>" +
						response.data.action_button;
					Swal.fire({
						title: title,
						html: message,
						customClass:
							"user-registration-swal2-modal user-registration-swal2-modal--centered user-registration-locked-field",
						showCloseButton: true,
						showConfirmButton: false,
						allowOutsideClick: true,
						heightAuto: false,
						width: "575px"
					}).then(function (result) {
						// Do Nothing.
					});
				}
			});
		}
	});

	// Adjust builder width
	$(window).on("resize orientationchange", function () {
		var resizeTimer;

		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function () {
			$(document.body).trigger("adjust_builder_width");
		}, 250);
	});

	$(document.body).on("click", "#collapse-button", function () {
		$(document.body).trigger("ur_adjust_builder_width");
	});

	$(document.body)
		.on("ur_adjust_builder_width", function () {
			var adminMenuWidth = $("#adminmenuwrap").width(),
				$builder = $(
					".user-registration_page_add-new-registration .ur-form-subcontainer .menu-edit"
				),
				$loading = $(
					".user-registration_page_add-new-registration .ur-form-subcontainer .ur-loading-container"
				);

			$builder.css({ left: adminMenuWidth + "px" });
			$loading.fadeOut(1000);
		})
		.trigger("ur_adjust_builder_width");

	// Form name edit.
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
			$input.attr(
				"data-editing",
				$input.attr("data-editing") == "true" ? "false" : "true"
			);
		}
	);

	$("#ur-form-name").on("change", function () {
		$(".ur-form-title").text($(this).val());
	});
	// In case the user goes out of focus from title edit state.
	$(document)
		.not($(".user-registration-editable-title"))
		.on("click", function (e) {
			var field = $(".user-registration-editable-title__input");

			// Both of these controls should in no way allow stopping event propagation.
			if (
				"ur-form-name" === e.target.id ||
				"ur-form-name-edit-button" === e.target.id
			) {
				return;
			}

			if (!field.attr("hidden") && field.hasClass("is-editing")) {
				e.stopPropagation();

				// Only allow flipping state if currently editing.
				if (
					"true" !== field.data("data-editing") &&
					field.val() &&
					"" !== field.val().trim()
				) {
					field
						.toggleClass("is-editing")
						.trigger("blur")
						.attr(
							"data-editing",
							field.attr("data-editing") == "true"
								? "false"
								: "true"
						);
				}
			}
		});

	$(document).on(
		"init_perfect_scrollbar update_perfect_scrollbar",
		function () {
			// Init perfect Scrollbar.
			if ("undefined" !== typeof PerfectScrollbar) {
				var tab_content = $(".ur-tab-contents");

				if (
					tab_content.length >= 1 &&
					"undefined" === typeof window.ur_tab_scrollbar
				) {
					window.ur_tab_scrollbar = new PerfectScrollbar(
						document.querySelector(".ur-tab-contents"),
						{
							suppressScrollX: true
						}
					);

					var collapseBtn = document.querySelector("#ur-collapse");

					collapseBtn.addEventListener("click", function () {
						if (collapseBtn.classList.contains("open")) {
							$(collapseBtn).removeClass("open");
							$(collapseBtn).addClass("close");
						} else {
							$(collapseBtn).addClass("open");
							$(collapseBtn).removeClass("close");
						}

						var targetEl = document.querySelector(
							".ur-registered-inputs"
						);

						if (targetEl.classList.contains("collapsed")) {
							targetEl.classList.remove("collapsed");
							$(".ur-registered-inputs")
								.find("nav.ur-tabs")
								.show();
							$(".ur-registered-inputs").css("width", "412px");
							window.ur_tab_scrollbar.update(); // Refresh the scrollbar
						} else {
							targetEl.classList.add("collapsed");
							$(".ur-registered-inputs").css("width", "0px");
							$(".ur-registered-inputs")
								.find("nav.ur-tabs")
								.hide();
						}
					});
				} else if ("undefined" !== typeof window.ur_tab_scrollbar) {
					window.ur_tab_scrollbar.update();
					tab_content.scrollTop(0);
				}
			}
		}
	);

	/**
	 * Append form settings to fileds section.
	 */
	$(document).ready(function () {
		$(document).trigger("init_perfect_scrollbar");

		var fields_panel = $(".ur-selected-inputs");
		var form_settings_section = $(".ur-registered-inputs nav").find(
			"#ur-tab-field-settings"
		);
		var form_settings = form_settings_section.find("form");

		form_settings.appendTo(fields_panel);

		fields_panel
			.find("form #ur-field-all-settings > div")
			.each(function (index, el) {
				var appending_text = $(el).find("h3").text();
				var appending_id = $(el).attr("id");

				form_settings_section.append(
					'<div id="' +
						appending_id +
						'" class="form-settings-tab">' +
						appending_text +
						"</div>"
				);
				$(el).hide();
			});

		// Add active class to general settings and form-settings-tab for all settings.
		form_settings_section.find("#general-settings").addClass("active");
		fields_panel.find("#ur-field-all-settings div#general-settings").show();

		form_settings_section
			.find(".form-settings-tab")
			.on("click", function () {
				this_id = $(this).attr("id");
				// Remove all active classes initially.
				$(this).siblings().removeClass("active");

				// Add active class on clicked tab.
				$(this).addClass("active");

				// Hide other settings and show respective id's settings.
				fields_panel.find("form #ur-field-all-settings > div").hide();
				fields_panel
					.find("form #ur-field-all-settings > div#" + this_id)
					.show();
				$(document).trigger("update_perfect_scrollbar");
				$(".ur-builder-wrapper").scrollTop(0);
			});
	});

	$(document).on(
		"click",
		'.ur-tab-lists li[role="tab"] a.nav-tab',
		function (e, $type) {
			$(document).trigger("update_perfect_scrollbar");

			if ("triggered_click" != $type) {
				$(".ur-builder-wrapper").scrollTop(0);
				$(".ur-builder-wrapper-content").scrollTop(0);
			}
		}
	);

	// Setting Tab.
	$(document).on(
		"click",
		'.ur-tab-lists li[aria-controls="ur-tab-field-settings"]',
		function () {
			// Empty fields panels.
			$(".ur-builder-wrapper-content").hide();
			$(".ur-builder-wrapper-footer").hide();
			// Show only the form settings in fields panel.
			$(".ur-selected-inputs").find("form#ur-field-settings").show();
		}
	);

	/**
	 * Display fields panels on fields tab click.
	 */
	$(document).on(
		"click",
		'ul.ur-tab-lists li[aria-controls="ur-tab-registered-fields"]',
		function () {
			// Show field panels.
			$(".ur-builder-wrapper-content").show();
			$(".ur-builder-wrapper-footer").show();
			// Hide the form settings in fields panel.
			$(".ur-selected-inputs").find("form#ur-field-settings").hide();
			$("#user_registration_form_setting_redirect_after_field").hide();
		}
	);

	/**
	 * Hide/Show minimum password strength field on the basis of enable strong password value.
	 */
	var minimum_password_strength_wrapper_field = $("#general-settings").find(
		"#user_registration_form_setting_minimum_password_strength_field"
	);
	var strong_password_field = $("#general-settings").find(
		"#user_registration_form_setting_enable_strong_password_field input#user_registration_form_setting_enable_strong_password"
	);
	var custom_password_params = $("#general-settings").find(
		".custom-password-params"
	);
	var custom_password_field = $("#general-settings").find(
		"#user_registration_form_setting_minimum_password_strength_Custom"
	);
	var no_repeat_char_field = $("#general-settings").find(
		"#user_registration_form_setting_no_repeat_chars"
	);
	var max_repeat_char_field = $("#general-settings").find(
		"#user_registration_form_setting_form_max_char_repeat_length_field"
	);

	var enable_strong_password = strong_password_field.is(":checked");
	var enable_custom_password = custom_password_field.is(":checked");
	var enable_no_repetitive_chars = no_repeat_char_field.is(":checked");

	if (enable_custom_password) {
		if (enable_no_repetitive_chars) {
			max_repeat_char_field.show();
		} else {
			max_repeat_char_field.hide();
		}
	} else {
		custom_password_params.hide();
	}

	if (enable_strong_password) {
		minimum_password_strength_wrapper_field.show();
	} else {
		custom_password_params.hide();
		minimum_password_strength_wrapper_field.hide();
	}
	var password_strength_option = minimum_password_strength_wrapper_field.find(
		"[data-id='user_registration_form_setting_minimum_password_strength']"
	);

	// show password strength info.
	$(document).ready(function () {
		var strength_info = "";
		var password_hint = "";

		password_strength_option.each(function () {
			if ($(this).is(":checked")) {
				var password_strength_value = $(this).val();
				show_password_strength_info(password_strength_value);
			}
		});

		$(password_strength_option).on("change", function () {
			password_hint = minimum_password_strength_wrapper_field
				.find("span")
				.not(".user-registration-help-tip");
			var $strength = "";

			if ($(this).is(":checked")) {
				$strength = $(this).val();
			}

			password_hint.remove();
			show_password_strength_info($strength);
			custom_password_params.hide();

			if ($strength === "4") {
				custom_password_params.show();
				max_repeat_char_field.hide();
				if (enable_no_repetitive_chars) {
					max_repeat_char_field.show();
				} else {
					max_repeat_char_field.hide();
				}
			}
		});

		$(no_repeat_char_field).on("click", function () {
			max_repeat_char_field.hide();

			if ($(this).is(":checked")) {
				max_repeat_char_field.show();
			} else {
				max_repeat_char_field.hide();
			}
		});

		function show_password_strength_info($strength_value) {
			switch ($strength_value) {
				case "0":
					strength_info =
						user_registration_form_builder_data.user_registration_very_weak_password_info;
					break;
				case "1":
					strength_info =
						user_registration_form_builder_data.user_registration_weak_password_info;
					break;
				case "2":
					strength_info =
						user_registration_form_builder_data.user_registration_medium_password_info;
					break;
				case "3":
					strength_info =
						user_registration_form_builder_data.user_registration_strong_password_info;
					break;
				case "4":
					strength_info =
						user_registration_form_builder_data.user_registration_custom_password_info;
					break;
				default:
					strength_info = "";
					break;
			}
			minimum_password_strength_wrapper_field.append(
				"<span class='description' style='margin-bottom: 20px'>" +
					strength_info +
					"</span>"
			);
		}
	});

	// Toggle Akismet Settings
	$(document).ready(function () {
		wrapper = $("#user_registration_enable_akismet_field");
		var akismet_activate = $("#user_registration_enable_akismet");
		var akismet_message = $("#user_registration_akismet_warning_field");
		if (akismet_activate.is(":checked")) {
			akismet_message.show();
		} else {
			akismet_message.hide();
		}
		akismet_activate.change(function () {
			if ($(this).is(":checked")) {
				akismet_message.show();
			} else {
				akismet_message.hide();
			}
		});
	});

	$(strong_password_field).on("change", function () {
		enable_strong_password = $(this).is(":checked");

		if (enable_strong_password) {
			if (enable_custom_password) {
				custom_password_params.show();
			}
			minimum_password_strength_wrapper_field.show("slow");
		} else {
			minimum_password_strength_wrapper_field.hide("slow");
			custom_password_params.hide();
		}
	});

	$(document).ready(function () {
		hide_show_redirection_options();

		$("#user_registration_form_setting_redirect_after_registration").on(
			"change",
			hide_show_redirection_options
		);
	});

	/**
	 * Hide or Show Redirection settings.
	 */
	var hide_show_redirection_options = function () {
		var redirect_after_registration = $(
			"#user_registration_form_setting_redirect_after_registration"
		);
		var selected_redirection_option =
			redirect_after_registration.find(":selected");
		var custom_redirection_page = $(
			"#user_registration_form_setting_redirect_page"
		)
			.closest(".form-row")
			.slideUp(800);
		var redirect_url = $("#user_registration_form_setting_redirect_options")
			.closest(".form-row")
			.slideUp(800);

		if (selected_redirection_option.length) {
			switch (selected_redirection_option.val()) {
				case "internal-page":
					$(
						"#user_registration_form_setting_redirect_after_field"
					).show();
					custom_redirection_page.slideDown(800);
					break;
				case "external-url":
					$(
						"#user_registration_form_setting_redirect_after_field"
					).show();
					redirect_url.slideDown(800);
					break;
				case "no-redirection":
					$(
						"#user_registration_form_setting_redirect_after_field"
					).hide();
					break;
				case "previous-page":
					$(
						"#user_registration_form_setting_redirect_after_field"
					).show();
					break;
				default:
					break;
			}
		}
	};

	/**
	 * Prevent negative input for Waiting Period Before Redirection setting.
	 */
	$("#user_registration_form_setting_redirect_after").on(
		"change input paste",
		function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $this = $(e.target);

			$this.val(Math.abs($this.val()));
		}
	);

	// Tooltips
	$(document.body)
		.on("init_tooltips", function () {
			ur_init_tooltips(".tips, .help_tip, .user-registration-help-tip");
			ur_init_tooltips(".ur-copy-shortcode, .ur-portal-tooltip", {
				keepAlive: false
			});

			// Add Tooltipster to parent element for widefat tables
			$(".parent-tips").each(function () {
				$(this)
					.closest("a, th")
					.attr("data-tip", $(this).data("tip"))
					.tooltipster()
					.css("cursor", "help");
			});
		})
		.trigger("init_tooltips");
	$("body").on("keypress", "#ur-form-name", function (e) {
		if (13 === e.which) {
			$("#save_form_footer").eq(0).trigger("click");
		}
	});

	$("#ur-full-screen-mode").on("click", function (e) {
		e.preventDefault();
		var $this = $(this);

		if ($this.hasClass("closed")) {
			$this.removeClass("closed");
			$this.addClass("opened");
			$(this).attr('title' ,  user_registration_form_builder_data.i18n_admin.i18n_exit_fullscreen_mode )

			$("body").addClass("ur-full-screen-mode");
		} else {
			$this.removeClass("opened");
			$this.addClass("closed");
			$(this).attr('title' , user_registration_form_builder_data.i18n_admin.i18n_fullscreen_mode );

			$("body").removeClass("ur-full-screen-mode");
		}
	});

	$(document).on("keyup", function (e) {
		if ("Escape" === e.key) {
			$("#ur-full-screen-mode.opened").trigger("click");
		}
	});

	// 	Hide SMS Verification phone field mapping setting if not set to sms verification
	if (
		$("#user_registration_form_setting_login_options").val() ===
		"sms_verification"
	) {
		$("#user_registration_form_setting_default_phone_field")
			.parent()
			.show();
	} else {
		$("#user_registration_form_setting_default_phone_field")
			.parent()
			.hide();
	}

	// Toggle display of enable email approval setting
	$("#user_registration_form_setting_login_options").on(
		"change",
		function () {
			if ($(this).val() === "sms_verification") {
				$("#user_registration_form_setting_default_phone_field")
					.parent()
					.show();
			} else {
				$("#user_registration_form_setting_default_phone_field")
					.parent()
					.hide();
			}
		}
	);

	$("#user_registration_form_setting_default_phone_field").on(
		"change",
		function () {
			$(
				"#user_registration_form_setting_default_phone_field option"
			).each(function () {
				var phone_options = $(this);
				var field_name = $(this).val();

				$(".ur-selected-item").each(function () {
					var old_field_name = $(this)
						.find(".ur-general-setting-block")
						.find('input[data-field="field_name"]')
						.attr("value");

					if (field_name === old_field_name) {
						var phone_format = $(this)
							.find(".ur-general-setting-block")
							.find('select[data-field="phone_format"]')
							.val();

						phone_options.attr("data-phone-format", phone_format);
					}
				});
			});

			// Change Field Name of field in Form Setting Default Phone field for SMS Verification.
			$(
				'[id="user_registration_form_setting_default_phone_field"] option[value="' +
					old_field_name +
					'"]'
			).attr("data-phone-format", $this_obj.val());
		}
	);

	$("input.input-color").wpColorPicker();
	// send test email message
	$(".user_registration_send_email_test").on("click", function (e) {
		var email = $("#user_registration_email_send_to").val();
		e.preventDefault();
		$.ajax({
			url: user_registration_send_email.ajax_url,
			data: {
				action: "user_registration_send_test_email",
				email: email,
				nonce: user_registration_send_email.test_email_nonce
			},
			type: "post",
			beforeSend: function () {
				var spinner =
					'<span class="ur-spinner is-active" style="margin-left: 20px"></span>';
				$(".user_registration_send_email_test").append(spinner);
			},
			complete: function (response) {
				$(".ur-spinner").remove();
				$(
					".user-registration_page_user-registration-settings .notice"
				).remove();
				if (response.responseJSON.success === true) {
					message_string =
						'<div class="success notice notice-success is-dismissible"><p><strong>' +
						response.responseJSON.data.message +
						"</strong></p></div>";
					$(".user-registration-options-container").prepend(
						message_string
					);
				} else {
					message_string =
						'<div class="error notice notice-error is-dismissible"><p><strong>' +
						response.responseJSON.data.message +
						"</strong></p></div>";
					$(".user-registration-options-container").prepend(
						message_string
					);
				}
				$(
					".user-registration_page_user-registration-settings .notice"
				).css("display", "block");
				$(window).scrollTop($(".notice").position());
			}
		});
	});

	// Email Status
	$(".user-registration-email-status-toggle").on("change", function (e) {
		e.preventDefault();
		var status = $(this).find('input[type="checkbox"]').is(":checked");
		var id = $(this).find('input[type="checkbox"]').attr("id");
		$.ajax({
			url: user_registration_email_setting_status.ajax_url,
			type: "POST",
			data: {
				action: "user_registration_email_setting_status",
				status: status,
				id: id,
				security:
					user_registration_email_setting_status.user_registration_email_setting_status_nonce
			},
			success: function (response) {}
		});
	});

	$("#ur-lists-page-settings-button").on("click", function () {
		$("#show-settings-link").click();
	});

	$(document)
		.find(".ur-form-locate")
		.on("click", function (e) {
			var id = $(this).data("id");
			var data = {
				action: "user_registration_locate_form_action",
				id: id,
				security: user_registration_admin_locate.ajax_locate_nonce
			};
			var tag = e.target;
			var target_tag = tag.closest(".row-actions");
			$.ajax({
				url: user_registration_admin_locate.ajax_url,
				dataType: "json", // JSON type is expected back from the PHP script.
				cache: false,
				data: data,
				type: "POST",
				beforeSend: function () {
					var spinner =
						'<i class="ur-spinner ur-spinner-active"></i>';
					$(target_tag).append(spinner);
				},
				success: function (response) {
					var len = Object.keys(response.data).length;
					if (len > 0) {
						var add_tag =
							'<div class = "locate-form"><span>' +
							user_registration_admin_locate.form_found +
							"</span>";
						var i = 1;
						$.each(response.data, function (index, value) {
							if (i > 1) {
								add_tag += ", ";
							}
							var wordsArray = index.split(" ");
							if (wordsArray.length > 4) {
								var slicedArray = wordsArray.slice(0, 4);
								index = slicedArray.join(" ");
								index = index + "...";
							}
							add_tag +=
								' <a href="' +
								value +
								' " rel="noreferrer noopener" target="_blank">' +
								index +
								"</a>";
							i++;
						});
						add_tag += "</div>";
						if ($(target_tag).find(".locate-form").length != 0) {
							$(target_tag).find(".locate-form").remove();
						}
						$(target_tag).find("span:first").prepend(add_tag);
					} else {
						if ($(target_tag).find(".locate-form").length != 0) {
							$(target_tag).find(".locate-form").remove();
						}
						$(target_tag)
							.find("span:first")
							.prepend(
								'<div class = "locate-form"><span>' +
									user_registration_admin_locate.form_found_error +
									"</span></div>"
							);
					}
					$(target_tag).find(".ur-spinner").remove();
				}
			});
		});

	// Smart Tags picker
	if (
		$("#ur-smart-tags-selector").siblings(".ur_advance_setting").length > 0
	) {
		var smart_tag_div = $("#ur-smart-tags-selector");
		$(smart_tag_div).insertBefore(
			$("#ur-smart-tags-selector").siblings(".ur_advance_setting")
		);
		$("#select-smart-tags").insertAfter($(smart_tag_div));
		$(smart_tag_div).css({
			position: "absolute",
			left: "65%",
			top: "-10px"
		});
	}

	if ($("#ur-smart-tags-selector").closest(".wp-media-buttons").length > 0) {
		$("#ur-smart-tags-selector")
			.closest(".wp-media-buttons")
			.css({ width: "100%", position: "relative" });
	}

	$(document.body).on("click", "#ur-smart-tags-selector", function () {
		var $this = $(this);

		$(this)
			.siblings("#select-smart-tags")
			.select2({
				placeholder: "",
				dropdownCssClass: "ur-select2-dropdown",
				templateResult: function (data, container) {
					if ($this.siblings(".ur_advance_setting").length > 0) {
						if (data.element) {
							$(container).addClass("ur-select-smart-tag");
						}
					}
					return data.text;
				}
			});

		$(this).siblings(".select2-container").addClass("ur-hide-select2");

		$(this).siblings("#select-smart-tags").select2("open");
		$(this)
			.siblings(".select2-container")
			.find(".select2-selection__rendered")
			.show();
		$(this)
			.siblings(".select2-container")
			.find(".select2-selection--open")
			.show();

		var buttonOffset = $(this).offset(),
			buttonOffsetTop = Math.round(
				buttonOffset.top + $(this).innerHeight()
			),
			buttonOffsetRight = Math.round(buttonOffset.left);

		var select2_container = $(
			".select2-container--open:not(.ur-hide-select2)"
		);
		select2_container.css({
			top: buttonOffsetTop,
			left: buttonOffsetRight - $(this).innerHeight() - 10
		});

		var newDiv =
			'<span class="ur-select2-title"><p>' +
			user_registration_admin_data.smart_tags_dropdown_title +
			"</p></span>";
		$(newDiv).insertBefore(select2_container.find(".select2-search"));

		var searchField = select2_container.find(".select2-search__field");
		searchField.attr(
			"placeholder",
			user_registration_admin_data.smart_tags_dropdown_search_placeholder
		);
		searchField.before(
			'<span class="search-icon"><svg xmlns="http://www.w3.org/2000/svg" height="16px" width="16px" viewBox="0 0 24 24" fill="#a1a4b9"><path d="M21.71,20.29,18,16.61A9,9,0,1,0,16.61,18l3.68,3.68a1,1,0,0,0,1.42,0A1,1,0,0,0,21.71,20.29ZM11,18a7,7,0,1,1,7-7A7,7,0,0,1,11,18Z"></path></svg></span>'
		);

		$("#select-smart-tags").on("change", function (event) {
			event.preventDefault();
			var input_value = $(this).val(),
				inputElement = $(this)
					.closest(".ur-advance-setting")
					.find("input");

			var advanceFieldData = inputElement.data("advance-field"),
				fieldData = inputElement.data("field"),
				field_name =
					advanceFieldData !== undefined
						? advanceFieldData
						: fieldData;
			update_input(field_name, input_value);

			inputElement.val(input_value);
			$(document.body).find(".ur-smart-tags-list").hide();
		});
	});

	/**
	 * For update the default value.
	 */
	function update_input(field_name, input_value) {
		active_field = $(".ur-item-active");
		target_input_field = $(active_field).find(
			".user-registration-field-option-group.ur-advance-setting-block"
		);
		ur_toggle_content = target_input_field.find(
			".ur-advance-setting.ur-advance-default_value"
		);
		target_input = $(ur_toggle_content).find(
			'input[data-advance-field="' + field_name + '"]'
		);
		target_textarea = $(ur_toggle_content).find(
			'input[data-advance-field="' + field_name + '"]'
		);

		target_input_hidden_field = $(active_field).find(
			".ur-general-setting-block"
		);
		ur_toggle_hidden_content = target_input_hidden_field.find(
			".ur-general-setting.ur-general-setting-hidden-value"
		);
		target_hidden_input = $(ur_toggle_hidden_content).find(
			'input[data-field="' + field_name + '"]'
		);
		// pattern value
		ur_toggle_pattern_content = target_input_field.find(
			".ur-advance-setting.ur-advance-pattern_value"
		);
		target_pattern_input = $(ur_toggle_pattern_content).find(
			'input[data-advance-field="' + field_name + '"]'
		);
		target_input.val(input_value);
		target_textarea.val(input_value);
		target_hidden_input.val(input_value);
		target_pattern_input.val(input_value);
	}
});

(function ($, user_registration_admin_data) {
	$(function () {
		$(".ur_import_form_action_button").on("click", function () {
			var file_data = $("#jsonfile").prop("files")[0];
			var form_data = new FormData();
			form_data.append("jsonfile", file_data);
			form_data.append("action", "user_registration_import_form_action");
			form_data.append(
				"security",
				user_registration_admin_data.ur_import_form_save
			);

			$.ajax({
				url: user_registration_admin_data.ajax_url,
				dataType: "json", // what to expect back from the PHP script, if anything
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: "post",
				beforeSend: function () {
					var spinner =
						'<span class="spinner is-active" style="float: left;margin-top: 6px;"></span>';
					$(".ur_import_form_action_button")
						.closest(".publishing-action")
						.append(spinner);
					$(".ur-import_notice").remove();
				},
				complete: function (response) {
					var message_string = "";

					$(".ur_import_form_action_button")
						.closest(".publishing-action")
						.find(".spinner")
						.remove();
					$(".ur-import_notice").remove();

					if (response.responseJSON.success === true) {
						message_string =
							'<div id="message" class="updated inline ur-import_notice"><p><strong>' +
							response.responseJSON.data.message +
							"</strong></p></div>";
					} else {
						message_string =
							'<div id="message" class="error inline ur-import_notice"><p><strong>' +
							response.responseJSON.data.message +
							"</strong></p></div>";
					}

					$(".ur-export-users-page").prepend(message_string);
					$("#jsonfile").val("");
					$(".user-registration-custom-selected-file").html(
						user_registration_admin_data.no_file_selected
					);
				}
			});
		});

		$(".ur_export_form_action_button").on("click", function () {
			var formid = $("#selected-export-forms").val();
			$(document).find("#message").remove();
			if (formid.length === 0) {
				message_string =
					'<div id="message" class="error inline ur-import_notice"><p><strong>' +
					user_registration_admin_data.export_error_message +
					"</strong></p></div>";
				$(".ur-export-users-page").prepend(message_string);
			} else {
				$(".ur_export_form_action_button").attr("type", "submit");
			}
		});
		$(".ur_export_user_action_button").on("click", function () {
			var formid = $("#selected-export-user-form").val();
			$(document).find("#message").remove();
			if (formid.length === 0) {
				message_string =
					'<div id="message" class="error inline ur-import_notice"><p><strong>' +
					user_registration_admin_data.export_error_message +
					"</strong></p></div>";
				$(".ur-export-users-page").prepend(message_string);
			} else {
				$(".ur_export_user_action_button").attr("type", "submit");
			}
		});
	});
})(jQuery, window.user_registration_admin_data);

/**
 * Set tooltips for specified elements.
 *
 * @param {String|jQuery} $elements Elements to set tooltips for.
 * @param {JSON} options Overriding options for tooltips.
 */
function ur_init_tooltips($elements, options) {
	if (undefined !== $elements && null !== $elements && "" !== $elements) {
		var args = {
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
			}
		};

		if (options && "object" === typeof options) {
			Object.keys(options).forEach(function (key) {
				args[key] = options[key];
			});
		}

		if ("string" === typeof $elements) {
			jQuery($elements).tooltipster(args);
		} else {
			$elements.tooltipster(args);
		}
	}
}

/**
 * Sweetalert2 alert confirmation modal.
 *
 * @param string message Message to be shown in confirmation modal.
 * @param object options Options for confirmation modal.
 */
function ur_confirmation(message, options) {
	if ("undefined" === typeof options) {
		options = {};
	}
	var icon = '<i class="dashicons dashicons-trash"></i>';
	var title =
		icon +
		'<span class="user-registration-swal2-modal__title">' +
		options.title;
	Swal.fire({
		customClass:
			"user-registration-swal2-modal user-registration-swal2-modal--centered user-registration-trashed",
		title: title,
		text: message,
		showCancelButton:
			"undefined" !== typeof options.showCancelButton
				? options.showCancelButton
				: true,
		confirmButtonText:
			"undefined" !== typeof options.confirmButtonText
				? options.confirmButtonText
				: user_registration_form_builder_data.i18n_admin
						.i18n_choice_delete,
		confirmButtonColor: "#ff4149",
		cancelButtonText:
			"undefined" !== typeof options.cancelButtonText
				? options.cancelButtonText
				: user_registration_form_builder_data.i18n_admin
						.i18n_choice_cancel
	}).then(function (result) {
		if (result.value) {
			options.confirm();
		} else {
			options.reject();
		}
	});
}
