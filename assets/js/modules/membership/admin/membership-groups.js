/**UR_Snackbar**/
(function ($, urmg_data) {

	if (typeof UR_Snackbar !== 'undefined') {
		var snackbar = new UR_Snackbar();
	}
	var membership_group_object = {
		init: function () {
			membership_group_object.bind_ui_actions();
		},

		if_empty: function (value, _default) {
			if (null === value || undefined === value || '' === value) {
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
			disable = membership_group_object.if_empty(disable, true);
			$('.ur-membership-group-save-btn').prop('disabled', !!disable);
		},
		/**
		 * Show success message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_success_message: function (message) {
			if (snackbar) {
				snackbar.add({
					type: 'success',
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
					type: 'failure',
					message: message,
					duration: 6
				});
				return true;
			}
			return false;
		},
		/**
		 * Append spinner element.
		 *
		 * @param {jQuery} $element
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
				$element.find('.ur-spinner').remove();
				return true;
			}
			return false;
		},

		validate_membership_group_form: function () {
			var
				form = $('#ur-membership-group-create-form'),
				main_fields = form.find('#ur-membership-main-fields .urmg-input'),
				no_errors = true;
			//main fields validation
			main_fields = Object.values(main_fields).reverse().slice(2);

			var result = membership_group_object.regular_validation(main_fields, true);

			if (!result) {
				return false;
			}
			return no_errors;
		},

		prepare_membership_data: function () {
			var post_data = {},
				post_meta_data = {},
				form = $('#ur-membership-group-create-form');
			post_data = {
				'name': form.find('#ur-input-type-membership-group-name').val(),
				'description': form.find('#ur-input-type-membership-group-description').val(),
				'status': form.find('#ur-membership-group-status').prop('checked')
			};
			if (urmg_data.membership_group_id) {
				post_data.ID = urmg_data.membership_group_id;
			}

			post_meta_data.memberships = form.find('#ur-input-type-membership-group-memberships').select2('val');

			return {
				'post_data': post_data,
				'post_meta_data': post_meta_data
			};
		},
		//regular required validation
		regular_validation: function (inputs, no_errors) {
			inputs.every(function (item) {
				var $this = $(item),
					value = $this.val(),
					is_required = $this.attr('required'),
					name = $this.data('key-name');
				if (is_required && (value === '' || value === null || value.length < 1)) {
					no_errors = false;
					membership_group_object.show_failure_message(urmg_data.labels.i18n_error + '! ' + name + ' ' + urmg_data.labels.i18n_field_is_required);
					return false;
				}
				return true;
			});
			return no_errors;
		},
		create_membership_group: function ($this) {
			membership_group_object.toggleSaveButtons(true);
			membership_group_object.append_spinner($this);
			if (this.validate_membership_group_form()) {
				var prepare_membership_groups_data = this.prepare_membership_data();

				this.send_data(
					{
						action: 'user_registration_membership_create_membership_group',
						membership_groups_data: JSON.stringify(prepare_membership_groups_data)
					},
					{
						success: function (response) {
							if (response.success) {
								urmg_data.membership_group_id = response.data.membership_group_id;
								$this.text(urmg_data.labels.i18n_save);
								membership_group_object.show_success_message(
									response.data.message
								);

								// var current_url = $(location).attr('href');
								//
								// current_url += '&post_id=' + urmg_data.membership_group_id;
								$(location).attr('href', urmg_data.membership_group_url);

							} else {
								membership_group_object.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							membership_group_object.show_failure_message(
								urmg_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						},
						complete: function () {
							membership_group_object.remove_spinner($this);
							membership_group_object.toggleSaveButtons(false);
						}
					}
				);

			} else {
				membership_group_object.remove_spinner($this);
				membership_group_object.toggleSaveButtons(false);
			}
		},
		/**
		 * Binds UI actions to the membership group page.
		 *
		 * Specifically, this function:
		 *
		 * 1. Listens for changes in the membership group select box, and
		 *    fetches the memberships for the selected group.
		 * 2. Listens for clicks on the membership group save button, and
		 *    triggers the creation of a membership group.
		 * 3. Initializes the select2 for the membership group select box.
		 * 4. Listens for clicks on the bulk action buttons, and
		 *    triggers the bulk deletion of membership groups.
		 */
		bind_ui_actions: function () {
			$(document).on('change', '#ur-setting-form .ur-general-setting-membership_listing_option select', function () {
				var $this = $(this),
					group_select_field = $('#ur-setting-form .ur-general-setting-membership_group');
				group_select_field.hide();
				$('.ur-general-setting-membership_listing_option select').val($this.val());

				if ($this.val() === 'group') {
					group_select_field.show();
				} else {
					membership_group_object.fetch_memberships(-1);
				}
			});
			$(document).on('change', '[data-field-group="payments"] input[name^="user_registration_enable_"]', function () {
				var $checkboxes = $("input[name^='user_registration_enable_']");
				if( $checkboxes.is(':checked')) {
					// disable membership field.
					$membershipField = $(".ur-registered-list").find("li[data-field-id='user_registration_membership']");
					$membershipField.draggable("disable");
					$membershipField.addClass("ur-membership-field-disabled");
					$membershipField.addClass("ur-locked-field");
				} else {
					// enable membership field.
					$membershipField = $(".ur-registered-list").find("li[data-field-id='user_registration_membership']");
					$membershipField.draggable("enable");
					$membershipField.removeClass("ur-membership-field-disabled");
					$membershipField.removeClass("ur-locked-field");
				}
			});
			// listen for changes in the membership group select box
			$(document).on('change', '#ur-setting-form .ur-general-setting-membership_group select', function () {

				var $this = $(this),
					group_id = Number($this.val());

				$('.ur-general-setting-membership_group select').val(group_id);
				membership_group_object.fetch_memberships(group_id);

			});
			// listen for clicks on the membership group save button
			$(document).on('click', '#ur-membership-group-create-form .ur-membership-group-save-btn', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $this = $(this);
				membership_group_object.create_membership_group($this);
			});
			// initialize select2
			$('#ur-input-type-membership-group-memberships').select2({
				placeholder: 'Select memberships.',
				minimumResultsForSearch: -1,
				multiple: true
			});
			// listen for clicks on the bulk action buttons
			$('#membership-group-list #doaction,#doaction2').on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var form = $('#membership-group-list'),
					selectedAction = form.find('select#bulk-action-selector-top option:selected').val();
				switch (selectedAction) {
					case 'delete' :
						membership_group_object.handle_bulk_delete_action(form);
						break;
					default:
						break;
				}

			});
			//delete membership
			$('.delete-membership-groups').on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				membership_group_object.delete_single_membership_group($(this));
			});
			$(document).on('ur_new_field_created', function () {
				var paypal_settings = $('#paypal-standard-settings'),
					stripe_settings = $('#stripe-settings'),
					group_select_field = $('#ur-setting-form .ur-general-setting-membership_group');
				paypal_settings.show();
				stripe_settings.show();
				group_select_field.hide();
				if ($('.ur-selected-inputs').find('div[data-field-key="membership"]').length) {
					user_registration_form_builder_data.form_has_membership_field = true;
					paypal_settings.addClass('disabled');
					stripe_settings.addClass('disabled');
					//➔ disable payment form settings on membership field added to the form.
					var payment_form_settings = $("#ur-tab-field-settings").find(".form-settings-tab[data-field-group='payments']");
					$.each(payment_form_settings, function() {
						$(this).addClass("disabled");
					});
					$("#ur-field-settings").find("[data-field-group='payments']").each(function() {
						$(this).find(":input").prop("disabled", true);
						$(this).css('opacity', 0.25);
					});

					//-> Disable payment fields from dragging when membership field is present.
					var payment_nodes = $('.ur-registered-from').find(".ur-payment-fields").find("li");

					$.each(payment_nodes, function(index, elem) {
						$this = $(elem);
						//➔ disable payment fields from dragging when membership field is present.
						var has_membership_field = $(".ur-input-grids").find('.ur-field[data-field-key="membership"]').length > 0;

						if(has_membership_field) {
							$this.draggable("disable");
							$this.addClass("ur-locked-field");
							$this.addClass("ur-membership-payment-field-disabled");
						}
					});
				}
			});
			$(document).on('ur_field_removed', function (event, data) {
				if (data.fieldKey === 'membership') {
					user_registration_form_builder_data.form_has_membership_field = false;

					//➔ enable payment form settings on membership field removal.
					var payment_form_settings = $("#ur-tab-field-settings").find(".form-settings-tab[data-field-group='payments']");
					$.each(payment_form_settings, function () {
						$(this).removeClass("disabled");
					});

					$("#ur-field-settings").find("[data-field-group='payments']").each(function() {
						$(this).find(":input").prop("disabled", false);
						$(this).css('opacity', 1);
					});


					//➔ unlock payment fields on membership field removal.
					var ul_node = $(
						"#ur-tab-registered-fields"
					).find("ul.ur-registered-list");
					var payment_nodes = ul_node.find('li.ur-membership-payment-field-disabled');

					$.each(payment_nodes, function () {
						var $field = $(this);

						if ($field.hasClass('ur-locked-field')) {
							$field.removeClass('ur-locked-field');
						}
						$field.removeClass('ur-membership-payment-field-disabled');
						$field.draggable("enable");
					});
				}
			});
			$(document).on('ur_rendered_field_options', function () {
				var membership_listing_option_field = $('#ur-setting-form .ur-general-setting-membership_listing_option select'),
					group_select_field = $('#ur-setting-form .ur-general-setting-membership_group');
				group_select_field.show();
				if (membership_listing_option_field.val() === 'all') {
					group_select_field.hide();
				}
			});
			$(document).on(
				'user_registration_admin_before_form_submit',
				function (event, data) {
					if ($('[data-field="membership_listing_option"]').val() === "all" && $('.urmg-container input').length < 1) {
						data.data['empty_membership_status'] = [
							{
								validation_status: false,
								validation_message: user_registration_form_builder_data.i18n_admin.i18n_prompt_no_membership_available
							}
						];
					}
					// validation for empty membership group.
					if ($('[data-field="membership_group"]').length && $('[data-field="membership_group"]').val() == "0" && $('[data-field="membership_listing_option"]').val() === "group") {
						data.data['empty_membership_group_status'] = [
							{
								validation_status: false,
								validation_message: user_registration_form_builder_data.i18n_admin.i18n_prompt_no_membership_group_selected
							}
						];
					}
					if (data.data.payment_field_present && $('.ur-selected-inputs').find('div[data-field-key="membership"]').length) {

						data.data['payment_field_present_status'] = [
							{
								validation_status: false,
								validation_message: user_registration_form_builder_data.i18n_admin.i18n_prompt_payment_field_present
							}
						];
					}
				});
			//remove membership settings on field delete
			$(document).on('ur_field_removed', function (event, data) {
				if (data.fieldKey === 'membership') {
					$('.ur-general-setting-membership_listing_option').remove();
				}
			});
		},
		delete_single_membership_group: function ($this) {
			var urlParams = new URLSearchParams($this.attr('href'));
			var form_title = urlParams.get('form');

			if (form_title !== null) {
				Swal.fire({
					title:
						'<img src="' +
						urmg_data.delete_icon +
						'" id="delete-user-icon">' +
						urmg_data.labels.i18n_prompt_title,
					html: '<p id="html_1">' +
						urmg_data.labels.i18n_prompt_cannot_delete + '`' + form_title + '`.' +
						'</p>',
					allowOutsideClick: true
				});
				return;
			}
			Swal.fire({
				title:
					'<img src="' +
					urmg_data.delete_icon +
					'" id="delete-user-icon">' +
					urmg_data.labels.i18n_prompt_title,
				html: '<p id="html_1">' +
					urmg_data.labels.i18n_prompt_single_subtitle +
					'</p>',
				showCancelButton: true,
				confirmButtonText: urmg_data.labels.i18n_prompt_delete,
				cancelButtonText: urmg_data.labels.i18n_prompt_cancel,
				allowOutsideClick: false
			}).then(function (result) {
				if (result.isConfirmed) {
					$(location).attr('href', $this.attr('href'));
				}
			});
		},
		/**
		 * Handles the bulk deletion of membership groups.
		 *
		 * Displays a confirmation dialog using SweetAlert2 before proceeding
		 * with deletion. If confirmed, it collects the selected membership
		 * group IDs from the provided form and sends a request to delete them.
		 * Shows a success or failure message based on the server response.
		 *
		 * @param {jQuery} form The form element containing membership groups to delete.
		 */
		handle_bulk_delete_action: function (form) {
			Swal.fire({
				title:
					'<img src="' +
					urmg_data.delete_icon +
					'" id="delete-user-icon">' +
					urmg_data.labels.i18n_prompt_title,
				html: '<p id="html_1">' +
					urmg_data.labels.i18n_prompt_bulk_subtitle +
					'</p>',
				showCancelButton: true,
				confirmButtonText: urmg_data.labels.i18n_prompt_delete,
				cancelButtonText: urmg_data.labels.i18n_prompt_cancel,
				allowOutsideClick: false
			}).then(function (result) {
				if (result.isConfirmed) {
					var selected_membership_groups = form.find('input[name="membership_group[]"]:checked'),
						membership_group_ids = [];

					if (selected_membership_groups.length < 1) {
						membership_group_object.show_failure_message(
							urmg_data.labels.i18n_prompt_no_membership_selected
						);
						return;
					}
					//prepare orders data
					selected_membership_groups.each(function () {
						if ($(this).val() !== '') {
							membership_group_ids.push($(this).val());
						}
					});

					//send request
					membership_group_object.send_data(
						{
							action: 'user_registration_membership_delete_membership_groups',
							membership_group_ids: JSON.stringify(membership_group_ids)
						},
						{
							success: function (response) {
								if (response.success) {

									membership_group_object.show_success_message(
										response.data.message
									);
									membership_group_object.remove_deleted_memberships(selected_membership_groups, true);
								} else {
									membership_group_object.show_failure_message(
										response.data.message
									);
								}
							},
							failure: function (xhr, statusText) {
								membership_group_object.show_failure_message(
									urmg_data.labels.network_error +
									'(' +
									statusText +
									')'
								);
							},
							complete: function () {
								// window.location.reload(); //Todo: Can be removed after fixing checkbox error and adding no content image if empty for all delete on ajax
							}
						}
					);
				}
			});
		},
		/**
		 *
		 * @param selected_memberships
		 * @param is_multiple
		 */
		remove_deleted_memberships: function (selected_memberships, is_multiple) {
			if (is_multiple) {
				selected_memberships.each(function () {
					$(this).parents('tr').remove();
				});
			} else {
				$(selected_memberships).parents('tr').remove();
			}
		},
		fetch_memberships: function (group_id) {
			var loader_container = $('.urmg-loader'),
				urmg_container = $('.urmg-container'),
				empty_urmg = $('.empty-urmg-label');
			urmg_container.empty();

			if (group_id === 0) {
				empty_urmg.text(user_registration_form_builder_data.i18n_admin.i18n_empty_membership_group_text);
				empty_urmg.show();
				return;
			}

			// hide memberships and label
			empty_urmg.hide();
			// append spinner
			membership_group_object.append_spinner(loader_container);

			membership_group_object.send_data({
				action: 'user_registration_membership_get_group_memberships',
				group_id: group_id,
				list_type: group_id === -1 ? 'all' : 'group'
			}, {
				success: function (response) {
					if (response.success) {
						membership_group_object.handle_membership_by_group_success_response(response.data, group_id);
					} else {
						empty_urmg.text(user_registration_form_builder_data.i18n_admin.i18n_prompt_no_membership_available);
						empty_urmg.show();
					}
				},
				failure: function (xhr, statusText) {

				},
				complete: function () {
					membership_group_object.remove_spinner(loader_container);

				}
			});
		},
		/**
		 * Handles the response after a successful ajax request of membership by group
		 * @param {object} data - The response data
		 * @param group_id - The response data
		 * @return {void}
		 */
		handle_membership_by_group_success_response: function (data, group_id) {
			var membership_details = '',
				urmg_container = $('.urmg-container');
			$(data.plans).each(function (k, item) {
				membership_details += '<label><input type="radio" value="' + item.ID + '" disabled/><span class="urm-membership-title">' + item.title + '</span> - <span> ' + item.period + ' </span></label>';
			});
			urmg_container.append(membership_details);
			$('.ur-selected-inputs .ur-general-setting-membership_group').find('select[data-field="membership_group"]  option[value="' + group_id + '"]').attr('selected', 'selected');
		},
		/**
		 * Sends data to the backend API.
		 *
		 * @param {object} data The data to send.
		 * @param {object} callbacks The callbacks list.
		 *
		 * @prop {function} callbacks.success The success callback.
		 * @prop {function} callbacks.failure The failure callback.
		 * @prop {function} callbacks.beforeSend The callback before the request is sent.
		 * @prop {function} callbacks.complete The callback after the request is completed.
		 */
		send_data: function (data, callbacks) {
			var success_callback =
					'function' === typeof callbacks.success ? callbacks.success : function () {
					},
				failure_callback =
					'function' === typeof callbacks.failure ? callbacks.failure : function () {
					},
				beforeSend_callback =
					'function' === typeof callbacks.beforeSend ? callbacks.beforeSend : function () {
					},
				complete_callback =
					'function' === typeof callbacks.complete ? callbacks.complete : function () {
					};

			// Inject default data.
			if (!data._wpnonce && urmg_data) {
				data._wpnonce = urmg_data._nonce;
			}
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: urmg_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback
			});
		}
	};

	$(document).ready(function () {
		membership_group_object.init();
	});
})
(jQuery, window.urmg_localized_data);
