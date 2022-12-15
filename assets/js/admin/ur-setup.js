(function ($, wp) {
	var $document = $(document);
	(__ = wp.i18n.__), (_x = wp.i18n._x), (sprintf = wp.i18n.sprintf);

	$(".user-registration__wrap.ur-form-container").remove();
	/**
	 * Sends an Ajax request to the server to install a extension.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}                   args         Arguments.
	 * @param {string}                   args.slug    Plugin identifier in the WordPress.org Plugin repository.
	 * @param {installExtensionSuccess=} args.success Optional. Success callback. Default: wp.updates.installPluginSuccess
	 * @param {installExtensionError=}   args.error   Optional. Error callback. Default: wp.updates.installPluginError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	wp.updates.installExtension = function (args) {
		var $card = $(".plugin-card-" + args.slug),
			$message = $card.find(".install-now, .activate-now");

		args = _.extend(
			{
				success: wp.updates.installExtensionSuccess,
				error: wp.updates.installExtensionError,
			},
			args
		);

		if ($message.html() !== __("Installing...")) {
			$message.data("originaltext", $message.html());
		}

		$message
			.addClass("updating-message")
			.attr(
				"aria-label",
				sprintf(
					/* translators: %s: Plugin name and version. */
					_x("Installing %s...", "user-registration"),
					$message.data("name")
				)
			)
			.text(__("Installing..."));

		wp.a11y.speak(__("Installing... please wait."), "polite");

		// Remove previous error messages, if any.
		$card
			.removeClass("plugin-card-install-failed")
			.find(".notice.notice-error")
			.remove();

		$document.trigger("wp-extension-installing", args);

		return wp.updates.ajax("user_registration_install_extension", args);
	};

	/**
	 * Updates the UI appropriately after a successful extension install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installPluginSuccess
	 * @param {object} response             Response from the server.
	 * @param {string} response.slug        Slug of the installed plugin.
	 * @param {string} response.pluginName  Name of the installed plugin.
	 * @param {string} response.activateUrl URL to activate the just installed plugin.
	 */
	wp.updates.installExtensionSuccess = function (response) {
		if ("user-registration_page_add-new-registration" === pagenow) {
			var $pluginRow = $('tr[data-slug="' + response.slug + '"]')
					.removeClass("install")
					.addClass("installed"),
				$updateMessage = $pluginRow.find(".plugin-status span");

			$updateMessage
				.removeClass("updating-message install-now")
				.addClass("updated-message active")
				.attr(
					"aria-label",
					sprintf(
						/* translators: %s: Plugin name and version. */
						_x("%s installed!", "user-registration"),
						response.pluginName
					)
				)
				.text(_x("Installed!", "plugin"));

			wp.a11y.speak(__("Installation completed successfully."), "polite");

			$document.trigger("wp-plugin-bulk-install-success", response);
		} else {
			var $message = $(".plugin-card-" + response.slug).find(
					".install-now"
				),
				$status = $(".plugin-card-" + response.slug).find(
					".status-label"
				);

			$message
				.removeClass("updating-message")
				.addClass("updated-message installed button-disabled")
				.attr(
					"aria-label",
					sprintf(
						/* translators: %s: Plugin name and version. */
						_x("%s installed!", "user-registration"),
						response.pluginName
					)
				)
				.text(_x("Installed!", "user-registration"));

			wp.a11y.speak(__("Installation completed successfully."), "polite");

			$document.trigger("wp-plugin-install-success", response);

			if (response.activateUrl) {
				setTimeout(function () {
					$status
						.removeClass("status-install-now")
						.addClass("status-active")
						.text(wp.updates.l10n.pluginInstalled);

					// Transform the 'Install' button into an 'Activate' button.
					$message
						.removeClass(
							"install-now installed button-disabled updated-message"
						)
						.addClass("activate-now")
						.attr("href", response.activateUrl);

					if ("plugins-network" === pagenow) {
						$message
							.attr(
								"aria-label",
								sprintf(
									/* translators: %s: Plugin name. */
									_x(
										"Network Activate %s",
										"user-registration"
									),
									response.pluginName
								)
							)
							.text(__("Network Activate"));
					} else {
						$message
							.attr(
								"aria-label",
								sprintf(
									/* translators: %s: Plugin name. */
									_x("Activate %s", "user-registration"),
									response.pluginName
								)
							)
							.text(__("Activate"));
					}
				}, 1000);
			}
		}
	};

	/**
	 * Updates the UI appropriately after a failed extension install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installExtensionError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the plugin to be installed.
	 * @param {string=} response.pluginName   Optional. Name of the plugin to be installed.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	wp.updates.installExtensionError = function (response) {
		if ("user-registration_page_add-new-registration" === pagenow) {
			var $pluginRow = $('tr[data-slug="' + response.slug + '"]'),
				$updateMessage = $pluginRow.find(".plugin-status span"),
				errorMessage;

			if (!wp.updates.isValidResponse(response, "install")) {
				return;
			}

			if (
				wp.updates.maybeHandleCredentialError(
					response,
					"install-plugin"
				)
			) {
				return;
			}

			errorMessage = sprintf(
				/* translators: %s: Error string for a failed installation. */
				__("Installation failed: %s"),
				response.errorMessage
			);

			$updateMessage
				.removeClass("updating-message")
				.addClass("updated-message")
				.attr(
					"aria-label",
					sprintf(
						/* translators: %s: Plugin name and version. */
						_x("%s installation failed", "user-registration"),
						$updateMessage.closest("tr").data("name")
					)
				)
				.text(__("Installation Failed!"));

			wp.a11y.speak(errorMessage, "assertive");

			$document.trigger("wp-plugin-bulk-install-error", response);
		} else {
			var $card = $(".plugin-card-" + response.slug),
				$button = $card.find(".install-now"),
				errorMessage;

			if (!wp.updates.isValidResponse(response, "install")) {
				return;
			}

			if (
				wp.updates.maybeHandleCredentialError(
					response,
					"user_registration_install_extension"
				)
			) {
				return;
			}

			errorMessage = sprintf(
				/* translators: %s: Error string for a failed installation. */
				__("Installation failed: %s"),
				response.errorMessage
			);

			$card
				.addClass("plugin-card-update-failed")
				.append(
					'<div class="notice notice-error notice-alt is-dismissible"><p>' +
						errorMessage +
						"</p></div>"
				);

			$card.on(
				"click",
				".notice.is-dismissible .notice-dismiss",
				function () {
					// Use same delay as the total duration of the notice fadeTo + slideUp animation.
					setTimeout(function () {
						$card
							.removeClass("plugin-card-update-failed")
							.find(".column-name a")
							.focus();
					}, 200);
				}
			);

			$button
				.removeClass("updating-message")
				.addClass("button-disabled")
				.attr(
					"aria-label",
					sprintf(
						/* translators: %s: Plugin name and version. */
						_x("%s installation failed", "user-registration"),
						$button.data("name")
					)
				)
				.text(__("Installation Failed!"));

			wp.a11y.speak(errorMessage, "assertive");

			$document.trigger("wp-plugin-install-error", response);
		}
	};

	/**
	 * Pulls available jobs from the queue and runs them.
	 * @see https://core.trac.wordpress.org/ticket/39364
	 */
	wp.updates.queueChecker = function () {
		var job;

		if (wp.updates.ajaxLocked || !wp.updates.queue.length) {
			return;
		}

		job = wp.updates.queue.shift();

		// Handle a queue job.
		switch (job.action) {
			case "user_registration_install_extension":
				wp.updates.installExtension(job.data);
				break;

			default:
				break;
		}

		// Handle a queue job.
		$document.trigger("wp-updates-queue-job", job);
	};
})(jQuery, window.wp);

/* global ur_setup_params */
jQuery(function ($) {
	/**
	 * Setup actions.
	 */
	var ur_setup_actions = {
		$setup_form: $(".user-registration-setup--form"),
		$button_install: ur_setup_params.i18n_activating,
		init: function () {
			this.title_focus();

			// Template actions.
			$(document).on(
				"click",
				".user-registration-template-install-addon",
				this.install_addon
			);

			$(document).on("click", ".upgrade-modal", this.message_upgrade);
			$(document).on(
				"click",
				".ur-template-preview",
				this.template_preview
			);

			// Select and apply a template.
			this.$setup_form.on(
				"click",
				".ur-template-select",
				this.template_select
			);

			// Prevent <ENTER> key for setup actions.
			$(document.body).on(
				"keypress",
				".user-registration-setup-form-name input",
				this.input_keypress
			);
		},
		title_focus: function () {
			setTimeout(function () {
				$("#user-registration-setup-name").focus();
			}, 100);
		},
		install_addon: function (event) {
			var pluginsList = $(".plugins-list-table").find("#the-list tr"),
				$target = $(event.target),
				success = 0,
				error = 0,
				errorMessages = [];

			wp.updates.maybeRequestFilesystemCredentials(event);

			$(".user-registration-template-install-addon")
				.html(
					ur_setup_actions.$button_install +
						'<div class="ur-spinner"></div>'
				)
				.closest("button")
				.prop("disabled", true);

			$(document).trigger("wp-plugin-bulk-install", pluginsList);

			// Find all the plugins which are required.
			pluginsList.each(function (index, element) {
				var $itemRow = $(element);

				// Only add inactive items to the update queue.
				if (
					!$itemRow.hasClass("inactive") ||
					$itemRow.find("notice-error").length
				) {
					return;
				}

				// Add it to the queue.
				wp.updates.queue.push({
					action: "user_registration_install_extension",
					data: {
						page: pagenow,
						name: $itemRow.data("name"),
						slug: $itemRow.data("slug"),
					},
				});
			});

			// Display bulk notification for install of plugin.
			$(document).on(
				"wp-plugin-bulk-install-success wp-plugin-bulk-install-error",
				function (event, response) {
					var $itemRow = $('[data-slug="' + response.slug + '"]'),
						$bulkActionNotice,
						itemName;

					if (
						"wp-" + response.install + "-bulk-install-success" ===
						event.type
					) {
						success++;
					} else {
						itemName = response.pluginName
							? response.pluginName
							: $itemRow.find(".plugin-name").text();
						error++;
						errorMessages.push(
							itemName + ": " + response.errorMessage
						);
					}

					wp.updates.adminNotice = wp.template(
						"wp-bulk-installs-admin-notice"
					);

					// Remove previous error messages, if any.
					$(
						".user-registration-recommend-addons .bulk-action-notice"
					).remove();

					$(
						".user-registration-recommend-addons .plugins-list-table"
					).before(
						wp.updates.adminNotice({
							id: "bulk-action-notice",
							className: "bulk-action-notice notice-alt",
							successes: success,
							errors: error,
							errorMessages: errorMessages,
							type: response.install,
						})
					);

					$bulkActionNotice = $("#bulk-action-notice").on(
						"click",
						"button",
						function () {
							// $( this ) is the clicked button, no need to get it again.
							$(this)
								.toggleClass("bulk-action-errors-collapsed")
								.attr(
									"aria-expanded",
									!$(this).hasClass(
										"bulk-action-errors-collapsed"
									)
								);
							// Show the errors list.
							$bulkActionNotice
								.find(".bulk-action-errors")
								.toggleClass("hidden");
						}
					);

					if (!wp.updates.queue.length) {
						if (error > 0) {
							$target
								.removeClass("updating-message")
								.text($target.data("originaltext"));
						}
					}

					if (0 === wp.updates.queue.length) {
						$(".user-registration-template-install-addon")
							.parent()
							.prop("disabled", false);
						$(".user-registration-template-install-addon")
							.removeClass(
								"user-registration-template-install-addon"
							)
							.addClass("user-registration-template-continue")
							.text(ur_setup_params.i18n_form_ok);
					}
				}
			);

			// Check the queue, now that the event handlers have been added.
			wp.updates.queueChecker();
		},
		message_upgrade: function (e) {
			var templateName = $(this).data("template-name-raw");

			e.preventDefault();

			Swal.fire({
				customClass:
					"user-registration-swal2-modal user-registration-swal2-modal--center",
				icon: "error",
				title:
					'<span class="user-registration-swal2-modal__title">' +
					templateName +
					" " +
					ur_setup_params.upgrade_title +
					"</span>",
				text: ur_setup_params.upgrade_message,
				allowOutsideClick: false,
				confirmButtonText: ur_setup_params.upgrade_button,
				showCancelButton: true,
				cancelButtonText: ur_setup_params.i18n_ok,
				cancelButtonColor: "#DD6B55",
			}).then(function (result) {
				if (result.isConfirmed) {
					window.open(ur_setup_params.upgrade_url, "_blank");
				}
			});
		},
		template_preview: function () {
			var $this = $(this),
				previewLink = $this.data("preview-link");

			$this
				.closest(".user-registration-setup--form")
				.find(".ur-template-preview-iframe #frame")
				.attr("src", previewLink);
		},
		template_select: function (event) {
			var $this = $(this),
				template = $this.data("template"),
				templateName = $this.data("template-name-raw"),
				formName = "",
				button =
					'<a href="#" class="user-registration-btn user-registration-template-continue">' +
					ur_setup_params.i18n_form_ok +
					"</a>",
				namePrompt = "";

			event.preventDefault();

			$target = $(event.target);

			if (
				$target.hasClass("disabled") ||
				$target.hasClass("updating-message")
			) {
				return;
			}

			var label = ur_setup_params.i18n_form_title,
				title =
					'<span class="user-registration-swal2-modal__title">' +
					label +
					"</span>";

			if (
				$target
					.closest(".ur-template")
					.find("span.user-registration-badge").length
			) {
				var data = {
					action: "user_registration_template_licence_check",
					plan: $this
						.attr("data-licence-plan")
						.replace("-lifetime", ""),
					slug: $this.attr("data-template"),
					security: ur_setup_params.template_licence_check_nonce,
				};

				$.ajax({
					url: ur_setup_params.ajax_url,
					data: data,
					type: "POST",
					async: false,
				}).done(function (response) {
					$target
						.closest(".ur-template")
						.append(
							"<div class='user-registration-template-addons' style='display:none'>" +
								response.data.html +
								"</div>"
						);
					if (response.data.activate) {
						$(
							".user-registration-builder-setup .swal2-confirm"
						).show();
					} else {
						button = "";
						if (response.data.html.includes("install-now")) {
							button = ur_setup_params.i18n_install_activate;
							ur_setup_actions.$button_install =
								ur_setup_params.i18n_installing;
						} else {
							button = ur_setup_params.i18n_install_only;
						}
						var installButton =
							'<a href="#" class="user-registration-btn user-registration-template-install-addon">' +
							button +
							"</a>";
						$(".user-registration-template-addons").append(
							'<div class="ur-install-now">' +
								installButton +
								"</div>"
						);
					}
				});
			}

			if (
				$target
					.closest(".ur-template")
					.find(".user-registration-template-addons").length
			) {
				var installButton = $(".user-registration-template-addons")
					.find(".ur-install-now")
					.html();

				if (typeof installButton !== "undefined") {
					button = installButton;
				}

				$(".user-registration-template-addons")
					.find(".ur-install-now")
					.remove();
				namePrompt += $target
					.closest(".ur-template")
					.find(".user-registration-template-addons")
					.html();
				$target
					.closest(".ur-template")
					.find(".user-registration-template-addons")
					.remove();
			}

			namePrompt += "<h3>" + ur_setup_params.i18n_form_name + "</h3>";

			var templateNameError = false;

			Swal.fire({
				customClass:
					"user-registration-swal2-modal user-registration-swal2-modal--center",
				title: title,
				html: namePrompt,
				input: "text",
				inputPlaceholder: ur_setup_params.i18n_form_placeholder,
				inputAttributes: {
					id: "user-registration-setup-name",
				},
				showCloseButton: true,
				allowOutsideClick: false,
				confirmButtonText: button,
				inputValidator: function(value) {
					if (
						$(".user-registration-template-install-addon").length >
						0
					) {
						return;
					}

					if ($(".swal2-validation-message").length > 0) {
						$(".swal2-validation-message").remove();
					}

					if ("" === value) {
						$(".swal2-content").append(
							"<div class='swal2-validation-message' id='swal2-validation-message' style='display: flex;'>" +
								ur_setup_params.i18n_form_error_name +
								"</div>"
						);
						templateNameError = true;
						return;
					} else {
						templateNameError = false;
					}
				},
				preConfirm: function () {
					if (
						$(".user-registration-template-install-addon").length >
						0
					) {
						if (!templateNameError) {
							return false;
						}
					} else {
						if (!templateNameError) {
							return true;
						}
					}

					return false;
				},
			}).then(function (result) {
				if ($(".user-registration-template-continue").length > 0) {
					var $formName = $("#user-registration-setup-name");

					// Check that form title is provided.
					if ($formName.val()) {
						formName = $formName.val();
					} else {
						return;
					}

					var data = {
						title: formName,
						action: "user_registration_create_form",
						template: template,
						security: ur_setup_params.create_form_nonce,
					};

					$.post(ur_setup_params.ajax_url, data, function (response) {
						if (response.success) {
							window.location.href = response.data.redirect;
						} else {
							$(".user-registartion-setup-name").focus();
							Swal.fire({
								icon: "error",
								title: "Oops...",
								text: response.data.error,
							});
						}
					}).fail(function (xhr) {
						Swal.fire({
							icon: "error",
							title: "Oops...",
							text: xhr.responseText,
						});
					});
				}
			});
		},
		input_keypress: function (e) {
			var button = e.keyCode || e.which;

			$(this).removeClass("user-registration-required");

			// Enter key.
			if (13 === button && e.target.tagName.toLowerCase() === "input") {
				e.preventDefault();
				return false;
			}
		},
	};

	ur_setup_actions.init();
});
