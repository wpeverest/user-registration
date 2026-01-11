jQuery(function ($) {
	var l10n = urUsersl10n;

	var URUsers = {
		init: function () {
			URUsers.initUIBindings();
			URUsers.handle_users_filter_reset();
		},
		/**
		 * Bind UI changes.
		 */
		initUIBindings: function () {
			$("#bulk-action-selector-top").on(
				"change",
				URUsers.handleActionsChange
			);
			URUsers.handleActionsChange();

			$("#user-registration-users-advanced-filter-btn").on(
				"click",
				URUsers.hideShowAdvancedFilters
			);
			$("#user_registration_pro_users_date_range_filter").on(
				"change",
				URUsers.hideShowAdvancedFilters
			);
			$(
				"#user_registration_pro_start_date_filter, #user_registration_pro_end_date_filter"
			).on("change", URUsers.handleCustomDateRangeInput);

			$(".ur-back-button").on("click", URUsers.handleBackButton);

			$("#user-registration-user-action-delete a").on(
				"click",
				URUsers.handleSingleUserDelete
			);
			$(".user-registration-member-action-delete").on(
				"click",
				URUsers.handleSingleUserDelete
			);
			$("#doaction.button.action").on("click", URUsers.handleBulkDelete);

			$(".hide-column-tog").on("click", URUsers.handleColumnStateChange);

			$("textarea").each(function () {
				/**
				 * show the character and word count in textarea field.
				 */
				$(this).on("input", user_registration_count);
				var input_count;
				var selected_area_field = $(this).closest(".ur-field-item");
				if (selected_area_field.find(".ur-input-count").length > 0) {
					var selected_area_text = $(this).val().trim();
					if (
						selected_area_field
							.find(".ur-input-count")
							.data("count-type") === "characters"
					) {
						input_count = selected_area_text.length;
					} else {
						input_count =
							selected_area_text === ""
								? 0
								: selected_area_text.split(/\s+/).length;
					}
				}
				selected_area_field.find(".ur-input-count").text(input_count);
			});

			//disable users.
			$("body").on(
				"click",
				"#user-registration-user-action-disable_user",
				function (e) {
					if ($(this).find(".urm-deny").length) {
						e.preventDefault();
					}

					var $selector = $(this).find(".disable-user-link");
					URUsers.handle_disable_user($selector);
				}
			);

			$("body").on(
				"click",
				".ur-row-actions .disable-user-link",
				function (e) {
					var $selector = $(this);
					URUsers.handle_disable_user($selector);
				}
			);
		},
		handle_disable_user: function ($selector) {
			var $user_id = $selector.attr("id").split("-").pop();
			var nonce = $selector.data("nonce");
			var icon = '<i class="dashicons dashicons-warning"></i>';

			var disable_user_content =
				"<span>" +
				user_registration_pro_admin_script_data.disable_user_popup_content +
				"</span>";
			disable_user_content +=
				'<form id="disable-user-form-' +
				$user_id +
				'" class="disable-users-form" style="text-align:center">';
			disable_user_content +=
				'<input type="hidden" name="action" value="user_registration_disable_user">';
			disable_user_content +=
				'<input type="hidden" name="user_id" value="' + $user_id + '">';
			disable_user_content +=
				'<input type="hidden" name="_wpnonce" value="' + nonce + '">';
			disable_user_content +=
				'<input type="number" name="duration_value" min="1" placeholder="' +
				user_registration_pro_admin_script_data.disable_user_placeholder +
				'" style="margin-right:10px;">';
			disable_user_content +=
				'<select name="duration_unit" style="margin-right:10px;"><option value="days">Day(s)</option><option value="weeks">Week(s)</option><option value="months">Month(s)</option><option value="years">Year(s)</option></select>';
			disable_user_content += "</form>";

			swal.fire({
				title:
					icon +
					'<span class="user-registration-swal2-modal__title" >' +
					user_registration_pro_admin_script_data.disable_user_title +
					"</span>",
				html: disable_user_content,
				confirmButtonText:
					user_registration_pro_admin_script_data.disable,
				confirmButtonColor: "#3085d6",
				showConfirmButton: true,
				showCancelButton: true,
				cancelButtonText:
					user_registration_pro_admin_script_data.cancel,
				customClass: {
					container: "user-registration-swal2-container"
				},
				customClass:
					"user-registration-swal2-modal user-registration-swal2-modal--centered",
				focusConfirm: false,
				showLoaderOnConfirm: true,
				preConfirm: function () {
					return new Promise(function (resolve) {
						var duration_value = Swal.getPopup().querySelector(
							'input[name="duration_value"]'
						).value;
						var duration_unit = Swal.getPopup().querySelector(
							'select[name="duration_unit"]'
						).value;

						if (!duration_value || !duration_unit) {
							Swal.showValidationMessage(
								"Please enter duration value and unit."
							);
							Swal.hideLoading();
							$(".swal2-actions")
								.find("button")
								.prop("disabled", false);
						} else {
							$.ajax({
								type: "get",
								url: user_registration_pro_admin_script_data.ajax_url,
								data: {
									action: "user_registration_disable_user",
									user_id: $user_id,
									nonce: nonce,
									duration_value: duration_value,
									duration_unit: duration_unit
								},
								success: function (response) {
									if (response.success) {
										Swal.fire({
											icon: "success",
											title:
												'<span class="user-registration-swal2-modal__title" >' +
												user_registration_pro_admin_script_data.disable_user_success_message_title +
												"</span>",
											customClass:
												"user-registration-swal2-modal user-registration-swal2-modal--centered",
											html: user_registration_pro_admin_script_data.disable_user_success_message
										}).then(function () {
											window.location.href =
												user_registration_pro_admin_script_data.after_disable_redirect_url;
										});
									} else {
										Swal.fire({
											icon: "error",
											title:
												'<span class="user-registration-swal2-modal__title" >' +
												user_registration_pro_admin_script_data.disable_user_error_message_title +
												"</span>",
											customClass:
												"user-registration-swal2-modal user-registration-swal2-modal--centered",
											html:
												response.data.message +
												" " +
												user_registration_pro_admin_script_data.disable_user_error_message
										});
									}
								},
								error: function (response) {
									Swal.fire({
										icon: "error",
										title:
											'<span class="user-registration-swal2-modal__title" >' +
											user_registration_pro_admin_script_data.disable_user_error_message_title +
											"</span>",
										customClass:
											"user-registration-swal2-modal user-registration-swal2-modal--centered",
										html:
											response.data.message +
											" " +
											user_registration_pro_admin_script_data.disable_user_error_message
									});
								}
							});
							Swal.close();
						}
					});
				}
			});
		},

		/**
		 * Send ajax request when the user changes the visibility of
		 * form specific columns from screen options.
		 *
		 * @param {event} e
		 */
		handleColumnStateChange: function (e) {
			var $this = $(e.target);

			var default_columns = [
				"email",
				"role",
				"user_status",
				"user_source",
				"user_registered"
			];

			var form_id_el = $("#user-registration-users-form-id");

			if (!default_columns.includes($this.val()) && form_id_el.length) {
				var form_id = form_id_el.val();

				$.post(l10n.ajax_url, {
					action: "user_registration_pro_users_table_change_column_state",
					form: form_id,
					_wpnonce: l10n.change_column_nonce
				});
			}
		},

		handleActionsChange: function () {
			var action = $("#bulk-action-selector-top").val();

			if ("update_role" === action) {
				$("select#new_role")
					.closest(".alignleft.actions")
					.addClass("show-flex");
				$("#doaction").hide();
			} else {
				$("select#new_role")
					.closest(".alignleft.actions")
					.removeClass("show-flex");
				$("#doaction").show();
			}
		},

		/**
		 * Handle traversing to previous page when clicked on back button.
		 */
		handleBackButton: function () {
			window.history.back();
		},

		/**
		 * Handler for custom date range inputs.
		 */
		handleCustomDateRangeInput: function () {
			$("#user_registration_pro_users_date_range_filter").val("custom");
		},

		/**
		 * Handler to hide/show advanced filters.
		 *
		 * @param {event} e
		 * @returns void
		 */
		hideShowAdvancedFilters: function (e) {
			if (
				$("#user_registration_pro_users_date_range_filter").is(
					$(e.target)
				)
			) {
				// Case: Date Range value changed.

				if (
					"custom" ===
					$("#user_registration_pro_users_date_range_filter").val()
				) {
					$("#user-registration-users-advanced-filters").slideDown(
						600
					);
				} else {
					$("#user-registration-users-advanced-filters").slideUp(600);
				}

				return;
			} else if (
				$("#user-registration-users-advanced-filter-btn").is(
					$(e.target)
				) ||
				$("#user-registration-users-advanced-filter-btn").is(
					$(e.target).parent()
				)
			) {
				// Case: Advanced filters button clicked.

				$("#user-registration-users-advanced-filters").slideToggle(600);

				return;
			}
		},

		/**
		 * Handler for bulk delete action.
		 * @param {event} e
		 */
		handleBulkDelete: function (e) {
			var $this = $(e.target);
			var action = $this.parent().find("#bulk-action-selector-top").val();

			if ("delete" === action) {
				e.preventDefault();
				e.stopPropagation();

				var form = document.getElementById(
					"user-registration-members-list-form"
				);

				var formData = new FormData(form);
				var searchParams = new URLSearchParams(formData);

				// Get the target URL and append query parameters
				var targetURL =
					window.location.origin + window.location.pathname;
				var fullURL = targetURL + "?" + searchParams.toString();

				URUsers.handleDeletePrompt(fullURL, "bulk");
			}
		},

		/**
		 * Handler for single user delete from user view screen.
		 * @param {event} e
		 */
		handleSingleUserDelete: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var deleteUrl = $(e.target).attr("href");

			URUsers.handleDeletePrompt(deleteUrl, "single");
		},

		/**
		 * Show prompt and redirect to delete url on confirmation.
		 * @param {string} deleteUrl The url to redirect to delete users.
		 * @param {string} deleteType The type of delete operation: single or bulk.
		 */
		handleDeletePrompt: function (deleteUrl, deleteType) {
			var prompt_data = l10n.delete_prompt;
			var confirm_message =
				"single" == deleteType
					? prompt_data.confirm_message_single
					: prompt_data.confirm_message_bulk;
			var title =
				"single" == deleteType
					? prompt_data.title
					: prompt_data.bulk_title;

			var warning =
				'<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M8.57514 3.21659L1.51681 14.9999C1.37128 15.2519 1.29428 15.5377 1.29346 15.8287C1.29265 16.1197 1.36805 16.4059 1.51216 16.6587C1.65627 16.9115 1.86408 17.1222 2.1149 17.2698C2.36571 17.4174 2.65081 17.4967 2.94181 17.4999H17.0585C17.3495 17.4967 17.6346 17.4174 17.8854 17.2698C18.1362 17.1222 18.344 16.9115 18.4881 16.6587C18.6322 16.4059 18.7076 16.1197 18.7068 15.8287C18.706 15.5377 18.629 15.2519 18.4835 14.9999L11.4251 3.21659C11.2766 2.97168 11.0674 2.76919 10.8178 2.62866C10.5682 2.48813 10.2866 2.41431 10.0001 2.41431C9.71369 2.41431 9.43208 2.48813 9.18248 2.62866C8.93287 2.76919 8.7237 2.97168 8.57514 3.21659Z" stroke="#F25656" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 8.5V11.8333" stroke="#F25656" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 14.1667H10.01" stroke="#F25656" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			warning += prompt_data.warning_message;
			Swal.fire({
				title:
					"<img src='" +
					prompt_data.icon +
					"' id='delete-user-icon'>" +
					title,
				html: '<p id="html_1">' + confirm_message + "</p>",
				showCancelButton: true,
				confirmButtonText: prompt_data.delete_label,
				cancelButtonText: prompt_data.cancel_label
			}).then(function (result) {
				if (result.isConfirmed) {
					if (deleteUrl) {
						window.location.href = deleteUrl;
					}
				}
			});
		},
		/**
		 * Resets the set filter in users table.
		 */
		handle_users_filter_reset: function () {
			$("#user-registration-users-filter-reset-btn").on(
				"click",
				function (e) {
					e.preventDefault();
					var url = window.location.href;

					var form = $(this).closest("form")[0];
					form.reset();

					$(form).find('input[type="hidden"]').val("");

					$(form)
						.find("select")
						.each(function () {
							$(this).prop("selectedIndex", 0);
						});

					window.location.href = url.split("&")[0];
				}
			);
		}
	};

	$(document).ready(function () {
		if (
			$(
				".user-registration-users-page, #user-registration-pro-single-user-view"
			).length
		) {
			URUsers.init();
		}
	});

	function user_registration_count() {
		$("textarea").each(function () {
			var input_count;
			var selected_area_field = $(this).closest(".ur-field-item");
			if (selected_area_field.find(".ur-input-count").length > 0) {
				var selected_area_text = $(this).val().trim();
				if (
					selected_area_field
						.find(".ur-input-count")
						.data("count-type") === "characters"
				) {
					input_count = selected_area_text.length;
				} else {
					input_count =
						selected_area_text === ""
							? 0
							: selected_area_text.split(/\s+/).length;
				}
			}
			selected_area_field.find(".ur-input-count").text(input_count);
		});
	}
});
