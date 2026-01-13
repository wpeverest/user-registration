/*global console, UR_Snackbar, Swal*/
(function ($, urmo_data) {
	var modal = $("#payment-detail-modal");
	if (UR_Snackbar) {
		var snackbar = new UR_Snackbar();
	}

	var handle_orders_utils = {
		/**
		 *
		 * @param $element
		 * @returns {boolean}
		 */
		append_spinner: function ($element) {
			if ($element && $element.append) {
				var spinner = '<span class="ur-spinner is-active"></span>';

				$element.append(spinner);
				return true;
			}
			return false;
		},
		/**
		 * Remove spinner elements from a element.
		 *
		 * @param {jQuery} $element
		 */
		remove_spinner: function ($element) {
			if ($element && $element.remove) {
				$element.find(".ur-spinner").remove();
				return true;
			}
			return false;
		},
		if_empty: function (value, _default) {
			if (null === value || undefined === value || "" === value) {
				return _default;
			}
			return value;
		},
		/**
		 * Enable/Disable save buttons i.e. 'Save' button and 'Save as Draft' button.
		 *
		 * @param {Boolean} disable Whether to disable or enable.
		 */
		toggleSaveButtons: function (disable) {
			disable = handle_orders_utils.if_empty(disable, true);
			$(".approve-payment").prop("disabled", !!disable);
		},
		handle_bulk_delete_action: function (form) {
			Swal.fire({
				title:
					'<img src="' +
					urmo_data.delete_icon +
					'" id="delete-user-icon">' +
					urmo_data.labels.i18n_prompt_title,
				html:
					'<p id="html_1">' +
					urmo_data.labels.i18n_prompt_bulk_subtitle +
					"</p>",
				showCancelButton: true,
				confirmButtonText: urmo_data.labels.i18n_prompt_delete,
				cancelButtonText: urmo_data.labels.i18n_prompt_cancel,
				allowOutsideClick: false
			}).then(function (result) {
				if (result.isConfirmed) {
					var selected_orders = form.find(
							"tbody input[type=checkbox]:checked"
						),
						order_ids = [],
						user_ids = [];

					if (selected_orders.length < 1) {
						handle_orders_utils.show_failure_message(
							urmo_data.labels.i18n_prompt_no_order_selected
						);
						return;
					}
					//prepare orders data
					selected_orders.each(function () {
						if ($(this).val() !== "") {
							order_ids.push($(this).val());
						} else if ($(this).data("user-id") !== "") {
							user_ids.push($(this).data("user-id"));
						}
					});

					//send request
					handle_orders_utils.send_data(
						{
							action: "user_registration_membership_delete_orders",
							order_ids: JSON.stringify(order_ids),
							user_ids: JSON.stringify(user_ids)
						},
						{
							success: function (response) {
								if (response.success) {
									$(".ur-member-save-btn").text("Save");
									handle_orders_utils.show_success_message(
										response.data.message
									);
									handle_orders_utils.remove_deleted_orders(
										selected_orders,
										true
									);
								} else {
									handle_orders_utils.show_failure_message(
										response.data.message
									);
								}
							},
							failure: function (xhr, statusText) {
								handle_orders_utils.show_failure_message(
									urmo_data.labels.network_error +
										"(" +
										statusText +
										")"
								);
							},
							complete: function () {
								window.location.reload();
							}
						}
					);
				}
			});
		},

		handle_single_delete_action: function ($this) {
			var order_id = $this.data("order-id");
			var user_id = $this.data("user-id");
			Swal.fire({
				title:
					'<img src="' +
					urmo_data.delete_icon +
					'" id="delete-user-icon">' +
					urmo_data.labels.i18n_prompt_title,
				html:
					'<p id="html_1">' +
					urmo_data.labels.i18n_prompt_single_subtitle +
					"</p>",
				showCancelButton: true,
				confirmButtonText: urmo_data.labels.i18n_prompt_delete,
				cancelButtonText: urmo_data.labels.i18n_prompt_cancel,
				allowOutsideClick: false
			}).then(function (result) {
				if (result.isConfirmed) {
					//send request
					handle_orders_utils.send_data(
						{
							action: "user_registration_membership_delete_order",
							order_id: order_id,
							user_id: user_id
						},
						{
							success: function (response) {
								if (response.success) {
									handle_orders_utils.show_success_message(
										response.data.message
									);

									handle_orders_utils.remove_deleted_orders(
										$this,
										false
									);
								} else {
									handle_orders_utils.show_failure_message(
										response.data.message
									);
								}
							},
							failure: function (xhr, statusText) {
								handle_orders_utils.show_failure_message(
									urmo_data.labels.network_error +
										"(" +
										statusText +
										")"
								);
							},
							complete: function () {
								var url = new URL(window.location.href);
								if (
									url.searchParams.has("id") &&
									url.searchParams.has("action")
								) {
									url.searchParams.delete("id");
									url.searchParams.delete("action");
									window.location.replace(url.toString());
								} else {
									window.location.reload();
								}
							}
						}
					);
				}
			});
		},
		/**
		 * Send data to the backend API.
		 *
		 * @param {JSON} data Data to send.
		 * @param {JSON} callbacks Callbacks list.
		 */
		send_data: function (data, callbacks) {
			var success_callback =
					"function" === typeof callbacks.success
						? callbacks.success
						: function () {},
				failure_callback =
					"function" === typeof callbacks.failure
						? callbacks.failure
						: function () {},
				beforeSend_callback =
					"function" === typeof callbacks.beforeSend
						? callbacks.beforeSend
						: function () {},
				complete_callback =
					"function" === typeof callbacks.complete
						? callbacks.complete
						: function () {};

			// Inject default data.
			if (!data._wpnonce && urmo_data) {
				data._wpnonce = urmo_data._nonce;
			}
			$.ajax({
				type: "post",
				dataType: "json",
				url: urmo_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback
			});
		},
		/**
		 * Show success message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_success_message: function (message) {
			if (snackbar) {
				snackbar.add({
					type: "success",
					message: message,
					duration: 5
				});
				return true;
			}
			return false;
		},

		/**
		 * Show failure message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_failure_message: function (message) {
			if (snackbar) {
				snackbar.add({
					type: "failure",
					message: message,
					duration: 6
				});
				return true;
			}
			return false;
		},

		remove_deleted_orders: function (selected_orders, is_multiple) {
			if (is_multiple) {
				selected_orders.each(function (index) {
					if (index !== 0) {
						$(this).parents("tr").remove();
					}
				});
			} else {
				$(selected_orders).parents("tr").remove();
			}
		},

		open_modal: function () {
			this.clear_modal();
			modal.css({ display: "flex" });
		},
		close_modal: function () {
			modal.css({ display: "none" });
		},
		clear_modal: function () {
			modal.find(".modal-body").empty();
		},
		handle_edit_action: function (form) {
			$(".ur-payment-update-btn")
				.prop("disabled", true)
				.append('<span class="ur-spinner"></span>');

			var data = {};
			$.each(form.serializeArray(), function (idx, field) {
				var name = field.name.replace(/\[\]$/, "");
				if (data["name"]) {
					if (!$.isArray(data[name])) {
						data[name] = [data[name]];
					}
					data[name].push(field.value);
				} else {
					data[name] = field.value;
				}
			});
			data["security"] = data["ur_membership_edit_order_nonce"];
			handle_orders_utils.send_data(
				$.extend(
					{
						action: "user_registration_membership_edit_order"
					},
					data
				),
				{
					success: function (response) {
						if (!response.success) {
							handle_orders_utils.show_failure_message(
								response.data.message
							);
						} else {
							handle_orders_utils.show_success_message(
								response.data.message
							);
							setTimeout(function () {
								window.location.reload();
							}, 1000);
						}
					},
					failure: function (xhr, statusText) {
						handle_orders_utils.show_failure_message(
							urmo_data.labels.network_error +
								"(" +
								statusText +
								")"
						);
					},
					complete: function () {
						$(".ur-payment-update-btn")
							.prop("disabled", false)
							.find(".ur-spinner")
							.remove();
					}
				}
			);
		}
	};

	$(document).ready(function () {
		$("#doaction-orders,#doaction-orders2").on("click", function (e) {
			e.preventDefault();
			e.stopPropagation();
			var form = $("#ur-membership-payment-history-form"),
				selectedAction = form
					.find("select#bulk-action-selector-top option:selected")
					.val();
			switch (selectedAction) {
				case "delete":
					handle_orders_utils.handle_bulk_delete_action(form);
					break;
				default:
					break;
			}
		});

		$(".ur-payment-update-btn").on("click", function (e) {
			e.preventDefault();
			handle_orders_utils.handle_edit_action($("#ur-payments-edit-form"));
		});

		$(".single-delete-order").on("click", function () {
			handle_orders_utils.handle_single_delete_action($(this));
		});
		//show the order detail
		$(document).on("click", ".show-order-detail", function () {
			var $this = $(this),
				order_id = $this.data("order-id"),
				modal_body = modal.find(".modal-body"),
				user_id = $this.data("user-id");

			handle_orders_utils.open_modal();
			handle_orders_utils.append_spinner(modal_body);

			handle_orders_utils.send_data(
				{
					action: "user_registration_membership_show_order_detail",
					order_id: order_id,
					user_id: user_id
				},
				{
					success: function (response) {
						if (response.success) {
							var template = JSON.parse(response.data);
							modal_body.html(template);
						} else {
							handle_orders_utils.show_failure_message(
								response.data.message
							);
						}
					},
					failure: function (xhr, statusText) {
						handle_orders_utils.show_failure_message(
							urmo_data.labels.network_error +
								"(" +
								statusText +
								")"
						);
					},
					complete: function () {}
				}
			);
		});
		//close modal if clicked outside the box
		$(window).click(function (event) {
			if ($(event.target).is(modal)) {
				handle_orders_utils.close_modal();
			}
		});
		//close modal on click
		$(document).on("click", ".close-button", function () {
			handle_orders_utils.close_modal();
		});
		// toggle advance search select for membership and form
		$(document).on(
			"change",
			"#user-registration-pro-payment-type-filters",
			function () {
				var $this = $(this),
					selected_val = $this.find("option:selected").val(),
					module_box = $(".module-box");

				module_box.each(function (key, item) {
					$(item).hide();
					$(item).find("select option").removeAttr("selected");
				});
				$(
					"#user-registration-pro-" +
						selected_val +
						"-filters-container"
				).css({ display: "flex" });
			}
		);
	});
	$(document).ready(function () {
		$(".manual-payment-button").on("click", function () {
			window.location.href = urmo_data.add_manual_payment_url;
		});

		// Datalist: map selected member label to hidden user ID

		var $memberHidden = $("#ur-member-id-hidden");
		var $form = $("#ur-membership-payment-history-form");
	});
	var $memberInput = $("#ur-member-search-input"); //

	$(document).ready(function () {
		$("#ur-input-type-member").select2({
			placeholder: "Select a member",
			allowClear: true,
			width: "100%"
		});
		$("#ur-input-type-payment-method").select2({
			placeholder: "Select Payment Method",
			allowClear: true,
			minimumResultsForSearch: Infinity,
			width: "100%"
		});

		$("#ur-input-type-transaction-status").select2({
			placeholder: "Select status",
			minimumResultsForSearch: Infinity,
			width: "100%"
		});
	});

	$(document).ready(function ($) {
		$("#ur-membership-order-create-form").on("submit", function (e) {
			e.preventDefault();

			// Get the form button and show spinner.
			var $submitButton = $(".ur-add-new-payment");
			$submitButton
				.prop("disabled", true)
				.prepend('<span class="ur-spinner"></span>');

			var formData = {
				ur_member_id: $("#ur-input-type-member").val(),
				ur_membership_plan: $("#ur-input-type-membership-plan").val(),
				ur_membership_amount: $(
					"#ur-input-type-membership-amount"
				).val(),
				ur_payment_date: $("#ur-input-type-payment-date").val(),
				ur_transaction_status: $(
					"#ur-input-type-transaction-status"
				).val(),
				ur_payment_notes: $("#ur-input-type-payment-notes").val(),
				ur_payment_method: $("#ur-input-type-payment-method").val()
			};

			$.ajax({
				url: urmo_data.ajax_url,
				type: "POST",
				data: {
					action: "user_registration_membership_create_order",
					security: $("#ur_membership_order_nonce").val(),
					order_data: JSON.stringify(formData)
				},
				success: function (response) {
					if (response.success) {
						$submitButton
							.prop("disabled", false)
							.find(".ur-spinner")
							.remove();

						handle_orders_utils.show_success_message(
							response.data.message ||
								"Payment created successfully"
						);
						setTimeout(function () {
							window.location.href =
								urmo_data.payment_history_url;
						}, 1000);
					} else {
						handle_orders_utils.show_failure_message(
							response.data.message || "Error creating payment",
							"error"
						);
						$submitButton
							.prop("disabled", false)
							.find(".ur-spinner")
							.remove();
					}
				},
				error: function (xhr, status, error) {
					handle_orders_utils.show_failure_message(
						"Server error occurred. Please try again.",
						"error"
					);
					$submitButton
						.prop("disabled", false)
						.find(".ur-spinner")
						.remove();
				}
			});
		});

		$("#ur-input-type-membership-plan").on("change", function () {
			var selectedOption = $(this).find("option:selected");
			var amount = selectedOption.data("amount");
			if (amount) {
				$("#ur-input-type-membership-amount").val(amount);
				$("#ur-input-type-membership-amount").prop("disabled", false);
			} else {
				$("#ur-input-type-membership-amount").val(0);
				$("#ur-input-type-membership-amount").prop("disabled", true);
			}
			$(".ur-membership-plan-name").html(selectedOption.text());
		});

		$("#ur-input-type-member").on("change", function () {
			var selectedOption = $(this).find("option:selected");
			var membership_plan_id = selectedOption.data("membership-plan-id");

			$("#ur-input-type-membership-plan")
				.val(membership_plan_id)
				.trigger("change");
		});

		$("#ur-input-type-payment-date").on("click focus", function () {
			if (this.showPicker) {
				this.showPicker();
			}
		});
	});

	/**
	 * Resets the set filter in payments table.
	 */
	$("#user-registration-payments-filter-reset-btn").on("click", function (e) {
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
	});
})(jQuery, window.urm_orders_localized_data);
