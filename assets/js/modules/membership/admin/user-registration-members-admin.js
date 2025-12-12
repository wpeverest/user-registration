/*global console, Swal*/
(function ($, ur_member_data) {
	$(".user-membership-enhanced-select2").select2();

	if (UR_Snackbar) {
		var snackbar = new UR_Snackbar();
	}

	//extra utils for membership add ons
	var ur_membership_members_utils = {
		convert_to_array: function ($object) {
			return Object.values($object).reverse().slice(2).reverse();
		},
		append_spinner: function ($element) {
			if ($element && $element.append) {
				var spinner = '<span class="ur-spinner is-active"></span>';

				$element.append(spinner);
				return true;
			}
			return false;
		},
		prepend_spinner: function ($element) {
			if ($element && $element.prepend) {
				var spinner = '<span class="ur-spinner is-active"></span>';

				$element.prepend(spinner);
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
			disable = this.if_empty(disable, true);
			$(".ur-member-save-btn").prop("disabled", !!disable);
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
		}
	};
	//utils related with ajax requests
	var ur_member_ajax_utils = {
		/**
		 *
		 * @returns {{}}
		 */
		prepare_members_data: function () {
			var user_data = {},
				form_inputs = $(".ur-membership-members-input");
			form_inputs =
				ur_membership_members_utils.convert_to_array(form_inputs);
			form_inputs.forEach(function (item) {
				var $this = $(item),
					name = $this.data("key-name").toLowerCase();
				user_data[name] = $this.is('input[type="checkbox"]')
					? $this.prop("checked")
					: $this.val();
			});
			return user_data;
		},
		/**
		 * validate membership form before submit
		 * @returns {boolean}
		 */
		validate_members_form: function () {
			var form_inputs = $("#ur-membership-create-form").find("input"),
				email_pattern = new RegExp(
					"^[a-zA-Z0-9._%+-]+@(?:[a-zA-Z0-9-]+\\.)+[a-zA-Z]{2,}$"
				),
				today = new Date().toISOString().split("T")[0],
				no_errors = true;
			//main fields validation
			form_inputs =
				ur_membership_members_utils.convert_to_array(form_inputs);

			form_inputs.every(function (item) {
				var $this = $(item),
					value = $this.val(),
					is_required = $this.attr("required"),
					name = $this.data("key-name");
				if (is_required && value === "") {
					no_errors = false;
					ur_membership_members_utils.show_failure_message(
						ur_member_data.labels.i18n_error +
							"! " +
							name +
							" " +
							ur_member_data.labels.i18n_field_is_required
					);
					return false;
				}
				if (name === "Email") {
					if (!email_pattern.test(value)) {
						no_errors = false;
						ur_membership_members_utils.show_failure_message(
							ur_member_data.labels
								.i18n_field_email_field_validation
						);
						return false;
					}
				}
				if (
					name === "Password" &&
					value !==
						$("#ur-input-type-membership-confirm-password").val()
				) {
					no_errors = false;
					ur_membership_members_utils.show_failure_message(
						ur_member_data.labels
							.i18n_field_password_field_validation
					);
					return false;
				}

				if (name === "start_date" && ur_member_data.member_id === "") {
					if (value < today) {
						no_errors = false;
						ur_membership_members_utils.show_failure_message(
							ur_member_data.labels
								.i18n_field_subscription_start_date_validation
						);
						return false;
					}
				}
				return true;
			});

			return no_errors;
		},

		/**
		 * called to create a new membership
		 * @param $this
		 */
		create_member: function ($this) {
			ur_membership_members_utils.toggleSaveButtons(true);
			ur_membership_members_utils.append_spinner($this);

			if (this.validate_members_form()) {
				var prepare_members_data = this.prepare_members_data();

				this.send_data(
					{
						action: "user_registration_membership_create_member",
						members_data: JSON.stringify(prepare_members_data)
					},
					{
						success: function (response) {
							if (response.success) {
								ur_member_data.member_id =
									response.data.member_id;
								$(".ur-member-save-btn").hide();
								ur_membership_members_utils.show_success_message(
									response.data.message
								);
								$(location).attr(
									"href",
									ur_member_data.members_page_url
								);
							} else {
								ur_membership_members_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_members_utils.show_failure_message(
								ur_member_data.labels.network_error +
									"(" +
									statusText +
									")"
							);
						},
						complete: function () {
							ur_membership_members_utils.remove_spinner($this);
							ur_membership_members_utils.toggleSaveButtons(
								false
							);
						}
					}
				);
			} else {
				ur_membership_members_utils.remove_spinner($this);
				ur_membership_members_utils.toggleSaveButtons(false);
			}
		},

		/**
		 * called to update an existing membership
		 * @param $this
		 */
		update_member: function ($this) {
			ur_membership_members_utils.toggleSaveButtons(true);
			ur_membership_members_utils.append_spinner($this);
			if (this.validate_members_form()) {
				var prepare_members_data = this.prepare_members_data();

				this.send_data(
					{
						action: "user_registration_membership_edit_member",
						members_data: JSON.stringify(prepare_members_data),
						_wpnonce: ur_member_data.edit_members_nonce
					},
					{
						success: function (response) {
							if (response.success) {
								ur_member_data.member_id =
									response.data.member_id;
								$(".ur-member-save-btn").hide();
								ur_membership_members_utils.show_success_message(
									response.data.message
								);
								window.location.reload();
							} else {
								ur_membership_members_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_members_utils.show_failure_message(
								ur_member_data.labels.network_error +
									"(" +
									statusText +
									")"
							);
						},
						complete: function () {
							ur_membership_members_utils.remove_spinner($this);
							ur_membership_members_utils.toggleSaveButtons(
								false
							);
						}
					}
				);
			} else {
				ur_membership_members_utils.remove_spinner($this);
				ur_membership_members_utils.toggleSaveButtons(false);
			}
		},

		update_membership_status: function ($this) {},

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
			if (!data._wpnonce && ur_member_data) {
				data._wpnonce = ur_member_data._nonce;
			}
			$.ajax({
				type: "post",
				dataType: "json",
				url: ur_member_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback
			});
		},

		/**
		 *
		 * @param form
		 */
		handle_bulk_delete_action: function (form) {
			Swal.fire({
				title:
					'<img src="' +
					ur_member_data.delete_icon +
					'" id="delete-user-icon">' +
					ur_member_data.labels.i18n_prompt_title,
				html:
					'<p id="html_1">' +
					ur_member_data.labels.i18n_prompt_bulk_subtitle +
					"</p>",
				showCancelButton: true,
				confirmButtonText: ur_member_data.labels.i18n_prompt_delete,
				cancelButtonText: ur_member_data.labels.i18n_prompt_cancel,
				allowOutsideClick: false
			}).then(function (result) {
				if (result.isConfirmed) {
					var selected_members = form.find(
							'input[name="users[]"]:checked'
						),
						members_ids = [];

					if (selected_members.length < 1) {
						ur_membership_members_utils.show_failure_message(
							ur_member_data.labels
								.i18n_prompt_no_membership_selected
						);
						return;
					}
					//prepare orders data
					selected_members.each(function () {
						if ($(this).val() !== "") {
							members_ids.push($(this).val());
						}
					});
					//send request
					ur_member_ajax_utils.send_data(
						{
							action: "user_registration_membership_delete_members",
							members_ids: JSON.stringify(members_ids)
						},
						{
							success: function (response) {
								if (response.success) {
									ur_membership_members_utils.show_success_message(
										response.data.message
									);
									ur_member_ajax_utils.remove_deleted_members(
										selected_members,
										true
									);
								} else {
									ur_membership_members_utils.show_failure_message(
										response.data.message
									);
								}
							},
							failure: function (xhr, statusText) {
								ur_membership_members_utils.show_failure_message(
									ur_member_data.labels.network_error +
										"(" +
										statusText +
										")"
								);
							},
							complete: function () {
								// window.location.reload();
							}
						}
					);
				}
			});
		},
		/**
		 *
		 * @param selected_members
		 * @param is_multiple
		 */
		remove_deleted_members: function (selected_members, is_multiple) {
			if (is_multiple) {
				selected_members.each(function () {
					$(this).parents("tr").remove();
				});
			} else {
				$(selected_members).parents("tr").remove();
			}
		}
	};
	/**
	 * member save button event
	 */
	$(".ur-member-save-btn").on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $this = $(this);

		if (ur_member_data.member_id && ur_member_data.member_id !== "") {
			ur_member_ajax_utils.update_member($this);
		} else {
			ur_member_ajax_utils.create_member($this);
		}
	});

	$("#user-registration-members-list-form #doaction,#doaction2").on(
		"click",
		function (e) {
			var form = $("#user-registration-members-list-form"),
				selectedAction = form
					.find("select#bulk-action-selector-top option:selected")
					.val();

			switch (selectedAction) {
				case "delete":
					e.preventDefault();
					e.stopPropagation();
					ur_member_ajax_utils.handle_bulk_delete_action(form);
					break;
				default:
					break;
			}
		}
	);

	$("#ur-membership-select").on("change", function () {
		var membershipId = $(this).val();
		var $el = $(this);

		if ($el.hasClass("is_loading")) {
			return;
		}

		$el.addClass("is_loading");

		var html =
			'<div class="urm-membership-plan-spinner-container is_loading"><span class="ur-spinner is-active" style="margin-left: 20px"></span></div>';
		$("#plan-detail-container").append(html);

		var data = {
			action: "user_registration_membership_get_membership_details",
			membership_id: membershipId,
			security: ur_members_localized_data.ur_membership_edit_nonce
		};

		$.post(ur_members_localized_data.ajax_url, data, function (response) {
			if (response.success) {
				var membershipDetails = response.data;

				$(document)
					.find(".urm-membership-plan-amount")
					.text(membershipDetails.membership_detail.amount);
				$(document)
					.find(".urm-membership-subscription-status > span")
					.remove();
				$(document)
					.find(".urm-membership-subscription-status")
					.append(
						'<span class="user-registration-badge user-registration-badge--pending">' +
							membershipDetails.membership_detail
								.subscription_status +
							"</span>"
					);
				$(document)
					.find(".urm-membership-expiry-date")
					.text(membershipDetails.membership_detail.expiration_on);
				$el.removeClass("is_loading");
				$(".urm-membership-plan-spinner-container").removeClass(
					"is_loading"
				);
				$(".urm-membership-plan-spinner-container").empty();
			}
		});
	});
})(jQuery, window.ur_members_localized_data);
