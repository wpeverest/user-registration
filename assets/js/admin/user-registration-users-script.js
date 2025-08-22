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
			$("body").on("click", ".disable-user-link", function () {
				var $user_id = $(this).attr("id").split("-").pop();
				var nonce = $(this).data("nonce");
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
					'<input type="hidden" name="user_id" value="' +
					$user_id +
					'">';
				disable_user_content +=
					'<input type="hidden" name="_wpnonce" value="' +
					nonce +
					'">';
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
				$("select#new_role").closest(".alignleft.actions").show();
				$("#doaction").hide();
			} else {
				$("select#new_role").closest(".alignleft.actions").hide();
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
					"user-registration-users-action-form"
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

			var deleteUrl = $(e.target)
				.closest("#user-registration-user-action-delete")
				.find("a")
				.attr("href");

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

			Swal.fire({
				title:
					"<img src='" +
					prompt_data.icon +
					"' id='delete-user-icon'>" +
					prompt_data.title,
				html:
					'<p id="html_1">' +
					confirm_message +
					"</p>" +
					'<p id="html_2">' +
					prompt_data.warning_message +
					"</p>",
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
