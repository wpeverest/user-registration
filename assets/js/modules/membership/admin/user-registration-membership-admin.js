/*global console, Swal*/
(function ($, ur_membership_data) {
	if (UR_Snackbar) {
		var snackbar = new UR_Snackbar();
	}
	$('.user-membership-enhanced-select2').select2();

	//extra utils for membership add ons
	var ur_membership_utils = {
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
				$element.find('.ur-spinner').remove();
				return true;
			}
			return false;
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
			disable = ur_membership_utils.if_empty(disable, true);
			$('.ur-membership-save-btn').prop('disabled', !!disable);
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
		 * convert value and duration to timestamp
		 * @param value
		 * @param duration
		 * @returns {null|number}
		 */
		convert_to_timestamp: function (value, duration) {
			var multiplier;
			switch (duration) {
				case 'day':
					multiplier = 24 * 60 * 60 * 1000;
					break;
				case 'week':
					multiplier = 7 * 24 * 60 * 60 * 1000;
					break;
				case 'month':
					multiplier = 30 * 24 * 60 * 60 * 1000;
					break;
				case 'year':
					multiplier = 365 * 24 * 60 * 60 * 1000;
					break;
				default:
					return null;
			}
			return new Date().getTime() + (value * multiplier);
		},

		//regular required validation
		regular_validation: function (inputs, no_errors, from) {
			inputs.every(function (item) {
				var $this = $(item),
					value = $this.val(),
					is_required = $this.attr('required'),
					type = $this.attr('type'),
					name = $this.data('key-name');
				if (is_required && value === '') {
					no_errors = false;
					var message = ('paypal' === from ? ur_membership_data.labels.i18n_paypal : '') + ur_membership_data.labels.i18n_error + '! ' + name + ' ' + ur_membership_data.labels.i18n_field_is_required + ' ' + ('paypal' === from ? ur_membership_data.labels.i18n_paypal_setup_error : '');
					ur_membership_utils.show_failure_message(message);
					return false;
				} else if (type === 'url') {
					if (!ur_membership_utils.url_validations(value)) {
						no_errors = false;
						ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + name + ' ' + ur_membership_data.labels.i18n_valid_url_field_validation + ' ' + name);
						return false;
					}
				}
				return true;
			});
			return no_errors;
		},

		url_validations: function (url) {
			var regex = new RegExp('^https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$');
			return regex.test(url);

		},

		handle_bulk_delete_action: function (form) {
			Swal.fire({
				title:
					'<img src="' +
					ur_membership_data.delete_icon +
					'" id="delete-user-icon">' +
					ur_membership_data.labels.i18n_prompt_title,
				html: '<p id="html_1">' +
					ur_membership_data.labels.i18n_prompt_bulk_subtitle +
					'</p>',
				showCancelButton: true,
				confirmButtonText: ur_membership_data.labels.i18n_prompt_delete,
				cancelButtonText: ur_membership_data.labels.i18n_prompt_cancel,
				allowOutsideClick: false
			}).then(function (result) {
				if (result.isConfirmed) {
					var selected_memberships = form.find('input[name="membership[]"]:checked'),
						membership_ids = [];

					if (selected_memberships.length < 1) {
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_prompt_no_membership_selected
						);
						return;
					}
					//prepare orders data
					selected_memberships.each(function () {
						if ($(this).val() !== '') {
							membership_ids.push($(this).val());
						}
					});

					//send request
					ur_membership_request_utils.send_data(
						{
							action: 'user_registration_membership_delete_memberships',
							membership_ids: JSON.stringify(membership_ids)
						},
						{
							success: function (response) {
								if (response.success) {

									ur_membership_utils.show_success_message(
										response.data.message
									);
									ur_membership_request_utils.remove_deleted_memberships(selected_memberships, true);
								} else {
									ur_membership_utils.show_failure_message(
										response.data.message
									);
								}
							},
							failure: function (xhr, statusText) {
								ur_membership_utils.show_failure_message(
									ur_membership_data.labels.network_error +
									'(' +
									statusText +
									')'
								);
							},
							complete: function () {
								window.location.reload(); //Todo: Can be removed after fixing checkbox error and adding no content image if empty for all delete on ajax
							}
						}
					);
				}
			});
		}

	};

	//utils related with ajax requests
	var ur_membership_request_utils = {
		/**
		 * prepare membership data before ajax requests
		 * @returns {{post_data: {name: *, description: *, status: *}, post_meta_data: {}}}
		 */
		prepare_membership_data: function () {
			var post_data = {},
				post_meta_data = {},
				form = $('#ur-membership-create-form'),
				description = tinyMCE.get('ur-input-type-membership-description').getContent(),
				regex = /(<img[^>]*?)(")([^>]*?>)/g;

			description = description.replace(regex, function (match, p1, p2, p3) {
				return p1 + '\'' + p3.replace(/"/g, '\'');
			});


			post_data = {
				'name': form.find('#ur-input-type-membership-name').val(),
				'description': description,
				'status': form.find('#ur-membership-status').prop('checked')
			};
			if (ur_membership_data.membership_id) {
				post_data.ID = ur_membership_data.membership_id;
			}
			post_meta_data.type = form.find('input[name="ur_membership_type"]:checked').val();
			post_meta_data.cancel_subscription = form.find('input[name="ur_membership_cancel_on"]:checked').val();
			post_meta_data.role = form.find('#ur-input-type-membership-role').find(":selected").val();
			if (post_meta_data.type !== 'free') {
				if (post_meta_data.type !== 'paid') {
					post_meta_data.subscription = {
						value: form.find('#ur-membership-duration-value').val(),
						duration: form.find('#ur-membership-duration').val()
					};
					post_meta_data.trial_status = form.find('#ur-membership-trial-status').val();
					if (post_meta_data.trial_status === 'on') {
						post_meta_data.trial_data = {
							value: form.find('#ur-membership-trial-duration-value').val(),
							duration: form.find('#ur-membership-trial-duration').val()
						};
					}
					post_meta_data.cancel_subscription = form.find('input[name="ur_membership_cancel_on"]:checked').val();
				}
				post_meta_data.amount = form.find('#ur-membership-amount').val();
				var is_paypal_selected = form.find('#ur-membership-pg-paypal:checked').val(),
					is_bank_selected = form.find('#ur-membership-pg-bank:checked').val(),
					is_stripe_selected = form.find('#ur-membership-pg-stripe:checked').val();


				var is_authorize_selected = form.find("#ur-membership-pg-authorize:checked").val();
				var is_mollie_selected = form.find("#ur-membership-pg-mollie:checked").val();

				//since all the pgs have different params , they must be handled differently.
				post_meta_data.payment_gateways = {
					paypal: {
						status: 'off'
					}, //paypal section
					stripe: {
						status: 'off'
					}, // stripe section
					bank: {
						status: 'off'
					}, //direct bank transfer section
					authorize: {
						status: 'off'
					},
					mollie: {
						status: 'off'
					}
				};

				//check if paypal is selected
				if (is_paypal_selected) {
					post_meta_data.payment_gateways.paypal = {
						status: is_paypal_selected,
						email: form.find('#ur-input-type-paypal-email').val(),
						mode: form.find('#ur-membership-paypal-mode').val(),
						payment_type: form.find('#ur-membership-paypal-payment-type').val(),
						cancel_url: form.find('#ur-input-type-cancel-url').val(),
						return_url: form.find('#ur-input-type-return-url').val()
					};
					if (post_meta_data.type === 'subscription') {
						post_meta_data.payment_gateways.paypal.client_id = form.find('#ur-input-type-client-id').val();
						post_meta_data.payment_gateways.paypal.client_secret = form.find('#ur-input-type-client-secret').val();
					}
				}

				// check if bank transfer is selected
				if (is_bank_selected) {
					post_meta_data.payment_gateways.bank = {
						status: is_bank_selected
					};
				}

				// check if stripe is selected
				if (is_stripe_selected) {
					post_meta_data.payment_gateways.stripe = {
						status: is_stripe_selected
					};
				}

				if (is_authorize_selected) {
					post_meta_data.payment_gateways.authorize = {
						status: is_authorize_selected
					};
				}

				//check if mollie is selected
				if (is_mollie_selected) {
					post_meta_data.payment_gateways.mollie = {
						status: is_mollie_selected
					}
				}
			}

			//upgrade settings

			post_meta_data.upgrade_settings = {
				'upgrade_action': form.find('#ur-membership-upgrade-action').is(':checked'),
				'upgrade_path': form.find('#ur-input-type-membership-upgrade-path').val(),
				'upgrade_type': form.find('.urm-upgrade-path-type-container').find('input[name="ur_membership_upgrade_type"]:checked').val()
			};
			return {
				'post_data': post_data,
				'post_meta_data': post_meta_data
			};
		},
		/**
		 * validate membership form before submit
		 * @returns {boolean}
		 */
		validate_membership_form: function () {
			var plan_and_price_section = $('#ur-membership-plan-and-price-section'),
				main_fields = $('#ur-membership-main-fields').find('input'),
				form = $('#ur-membership-create-form'),
				upgrade_action = $('#ur-membership-upgrade-action').is(':checked'),
				no_errors = true;
			//main fields validation
			main_fields = Object.values(main_fields).reverse().slice(2);
			var result = ur_membership_utils.regular_validation(main_fields, true, 'form');

			if (!result) {
				return false;
			}
			//all validations related with paid membership
			var selectedPlanType = plan_and_price_section.find('input[name="ur_membership_type"]:checked').val(),
				amount = plan_and_price_section.find('#ur-membership-amount').val();
			if (selectedPlanType === 'paid' || selectedPlanType === 'subscription') {
				if (amount <= 0) {
					no_errors = false;
					ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + ur_membership_data.labels.i18n_valid_amount_field_validation);
				}
				//trial validations
				var trial_status = $('#ur-membership-trial-status').val();
				if (trial_status === 'on' && selectedPlanType === 'subscription') {
					var trial_duration = $('#ur-membership-trial-duration').val(),
						trial_duration_value = $('#ur-membership-trial-duration-value').val(),
						subscription_duration = $('#ur-membership-duration').val(),
						subscription_duration_value = $('#ur-membership-duration-value').val(),
						total_trial_time = ur_membership_utils.convert_to_timestamp(parseInt(trial_duration_value, 10), trial_duration),
						total_subscription_time = ur_membership_utils.convert_to_timestamp(parseInt(subscription_duration_value, 10), subscription_duration);
					if (total_trial_time >= total_subscription_time) {
						no_errors = false;
						ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + ur_membership_data.labels.i18n_valid_trial_period_field_validation);
					}
					if (subscription_duration_value < 1) {
						no_errors = false;
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_error +
							"! " +
							ur_membership_data.labels
								.i18n_valid_min_subs_period_field_validation
						);
					}
					if (trial_duration_value < 1) {
						no_errors = false;
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_error +
							"! " +
							ur_membership_data.labels
								.i18n_valid_min_trial_period_field_validation
						);
					}
				}
				// payment gateway validations

				// check if atleast one pg is enabled
				var available_pgs = $('#payment-gateway-container .user-registration-switch'),
					is_one_selected = false;

				available_pgs.each(function (index, item) {
					if ($(item).find('input').is(':checked')) {
						is_one_selected = true;
					}
				});

				if (!is_one_selected) {
					no_errors = false;
					ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + ur_membership_data.labels.i18n_pg_validation_error);

				}
				// paypal validations
				var is_paypal_selected = form.find('#ur-membership-pg-paypal:checked').val();
				if (is_paypal_selected) {
					var paypal_section = $('#paypal-section'),
						paypal_inputs = paypal_section.find('input');
					if (selectedPlanType !== 'subscription') {
						paypal_inputs = paypal_section.find('input').not('[name^="ur_membership_client_"]');
						paypal_inputs = Object.values(paypal_inputs).reverse().slice(2).reverse();
						result = ur_membership_utils.regular_validation(paypal_inputs, true, 'paypal');
						if (!result) {
							no_errors = false;
						}
					} else {
						var client_id = paypal_section.find('#ur-input-type-client-id').val(),
							client_secret = paypal_section.find('#ur-input-type-client-secret').val();

						if (client_id === '' || client_secret === '') {
							no_errors = false;
							ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_paypal + ' ' + ur_membership_data.labels.i18n_error + '! ' + ur_membership_data.labels.i18n_paypal_client_secret_id_error);
						}
					}


				}

			}
			//upgrade settings validation
			if (upgrade_action) {
				var upgrade_path = $('#ur-input-type-membership-upgrade-path'),
					upgrade_type_container = $('.urm-upgrade-path-type-container'),
					upgrade_type = upgrade_type_container.find('input[name="ur_membership_upgrade_type"]:checked').val();
				if (upgrade_path.val().length < 1) {
					no_errors = false;
					ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + upgrade_path.data("key-name") + ' ' + ur_membership_data.labels.i18n_field_is_required);
				}

				if (upgrade_type === undefined) {
					no_errors = false;
					ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + upgrade_type_container.data("key-name") + ' ' + ur_membership_data.labels.i18n_field_is_required);

				}
			}
			return no_errors;
		},

		/**
		 * called to create a new membership
		 * @param $this
		 */
		create_membership: function ($this) {
			ur_membership_utils.toggleSaveButtons(true);
			ur_membership_utils.append_spinner($this);
			if (this.validate_membership_form()) {
				var prepare_membership_data = this.prepare_membership_data();
				this.send_data(
					{
						action: 'user_registration_membership_create_membership',
						membership_data: JSON.stringify(prepare_membership_data)
					},
					{
						success: function (response) {
							if (response.success) {
								ur_membership_data.membership_id = response.data.membership_id;
								$this.text(ur_membership_data.labels.i18n_save);
								ur_membership_utils.show_success_message(
									response.data.message
								);
								// var current_url = $(location).attr('href');
								// current_url += '&post_id=' + ur_membership_data.membership_group_id;
								$(location).attr('href', ur_membership_data.membership_page_url);
							} else {
								ur_membership_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_utils.show_failure_message(
								ur_membership_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						},
						complete: function () {
							ur_membership_utils.remove_spinner($this);
							ur_membership_utils.toggleSaveButtons(false);
						}
					}
				);

			} else {
				ur_membership_utils.remove_spinner($this);
				ur_membership_utils.toggleSaveButtons(false);
			}
		},

		/**
		 * called to update an existing membership
		 * @param $this
		 */
		update_membership: function ($this) {
			ur_membership_utils.toggleSaveButtons(true);
			ur_membership_utils.append_spinner($this);
			if (this.validate_membership_form()) {
				var prepare_membership_data = this.prepare_membership_data();
				this.send_data(
					{
						action: 'user_registration_membership_update_membership',
						membership_data: JSON.stringify(prepare_membership_data),
						membership_id: ur_membership_data.membership_id
					},
					{
						success: function (response) {
							if (response.success) {
								ur_membership_utils.show_success_message(
									response.data.message
								);
							} else {
								ur_membership_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_utils.show_failure_message(
								ur_membership_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						},
						complete: function () {
							ur_membership_utils.remove_spinner($this);
							ur_membership_utils.toggleSaveButtons(false);
						}
					}
				);

			} else {
				ur_membership_utils.remove_spinner($this);
				ur_membership_utils.toggleSaveButtons(false);
			}
		},

		update_membership_status: function ($this) {
			ur_membership_utils.prepend_spinner($this.parents('.row-actions'));
			$this.attr('disabled', true);
			var status = $this.prop('checked'),
				ID = $this.data('ur-membership-id');
			this.send_data(
				{
					action: 'user_registration_membership_update_membership_status',
					membership_data: JSON.stringify({
						'status': status,
						'ID': ID
					})
				},
				{
					success: function (response) {
						if (response.success) {
							ur_membership_utils.show_success_message(
								response.data.message
							);
						} else {
							ur_membership_utils.show_failure_message(
								response.data.message
							);
						}
					},
					failure: function (xhr, statusText) {
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.network_error +
							'(' +
							statusText +
							')'
						);
					},
					complete: function () {
						//update UI after successful update
						ur_membership_utils.remove_spinner($this.parents('.row-actions'));
						$this.attr('disabled', false);
						var state = status ? 'Active' : 'Inactive',
							status_span = $('#ur-membership-list-status-' + ID);
						status_span.text(state);
						if (state === 'Inactive') {
							status_span.removeClass('user-registration-badge--success-subtle');
							status_span.addClass('user-registration-badge--secondary-subtle');
						} else {
							status_span.removeClass('user-registration-badge--secondary-subtle');
							status_span.addClass('user-registration-badge--success-subtle');
						}

					}
				}
			);
		},

		validate_payment_gateway: function ($this) {

			var switch_container = $this.closest('.user-registration-switch'),
				pg = $this.attr('id').split('ur-membership-pg-')[1],
				membership_type = $('input:radio[name=ur_membership_type]:checked').val();
			ur_membership_utils.prepend_spinner(switch_container);

			this.send_data(
				{
					action: 'user_registration_membership_validate_pg',
					pg: pg,
					membership_type: membership_type
				},
				{
					success: function (response) {
						if (!response.status) {
							ur_membership_utils.show_failure_message(
								response.message
							);
							$this.prop('checked', false);
							$this.closest('.user-registration-switch').closest('.ur-payment-option-header').siblings('.payment-option-body').show();
						} else {
							$this.prop('checked', true);
							$this.closest('.user-registration-switch').closest('.ur-payment-option-header').siblings('.payment-option-body').hide();
						}
					},
					failure: function (xhr, statusText) {
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.network_error +
							'(' +
							statusText +
							')'
						);
					},
					complete: function () {
						ur_membership_utils.remove_spinner(switch_container);
						ur_membership_utils.toggleSaveButtons(false);
					}
				}
			);
		},

		/**
		 * Send data to the backend API.
		 *
		 * @param {JSON} data Data to send.
		 * @param {JSON} callbacks Callbacks list.
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
			if (!data._wpnonce && ur_membership_data) {
				data._wpnonce = ur_membership_data._nonce;
			}
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: ur_membership_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback
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
		}

	};

	//toggle event for different payment types
	$(document).on('click', 'input:radio[name=ur_membership_type]', function () {
		var val = $(this).val(),
			plan_container = $('#paid-plan-container'),
			sub_container = $('.ur-membership-subscription-field-container'),
			payment_gateway_container = $('#payment-gateway-container'),
			pro_rate_settings = $('label.ur-membership-upgrade-types[for="ur-membership-upgrade-type-pro-rata"]');

		plan_container.addClass('ur-d-none');
		plan_container.addClass('ur-d-none');
		payment_gateway_container.addClass('ur-d-none');
		pro_rate_settings.addClass('ur-d-none');
		sub_container.show();
		if ('free' !== val) {
			if ('paid' === val) {
				sub_container.hide();
			} else {
				sub_container.removeClass('ur-d-none');
			}
			pro_rate_settings.removeClass('ur-d-none');
			payment_gateway_container.removeClass('ur-d-none');
			plan_container.removeClass('ur-d-none');
		}
	});

	$(document).on('click', '#ur-membership-upgrade-action', function () {
		$('#upgrade-settings-container').toggle();
		$('input:radio[name=ur_membership_type]:checked').trigger('click');
	});

	$(document).on('keydown', function (e) {
		if (e.ctrlKey && e.key === 's') {
			e.preventDefault();
			$('.ur-membership-save-btn').trigger('click');
		}
	});
	/**
	 * membership save button event
	 */
	$('.ur-membership-save-btn').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $this = $(this);
		if ($(this).find('.ur-spinner.is-active').length) {
			ur_membership_utils.show_failure_message(
				ur_membership_data.labels.i18n_previous_save_action_ongoing
			);
			return;
		}
		if (ur_membership_data.membership_id && ur_membership_data.membership_id !== '') {
			ur_membership_request_utils.update_membership($this);
		} else {
			ur_membership_request_utils.create_membership($this);
		}
	});

	//toggle trial section
	$('#ur-membership-trial-status').on('click', function () {
		var isChecked = $(this).prop('checked'),
			trial_container = $('.trial-container');
		$(this).val('on');
		if (!isChecked) {
			$(this).val('off');
		}
		trial_container.toggleClass('ur-d-none');

	});

	//change mmeberhsip status from list
	$('.ur-membership-change-status').on('change', function () {
		ur_membership_request_utils.update_membership_status($(this));
	});

	/**
	 * For toggling payment options.
	 */
	$(document).on('click', '.ur-payment-option-header', function () {
		$(this).find('input').trigger('click');
		// if ($(this).hasClass('closed')) {
		// 	$(this).removeClass('closed');
		// } else {
		// 	$(this).addClass('closed');
		// }
		// var data_id = $(this).attr('id');
		// $('div[data-target-id="' + data_id + '"]').slideToggle();
		// $(this).find('.ur-pg-arrow').toggleClass('expand');
	});
	//prevent status toggle
	$(document).on('click', '.pg-switch', function (e) {
		e.stopImmediatePropagation();

		if ($(this).is(':checked') && $(this).closest('.user-registration-switch').find('.ur-spinner.is-active').length < 1) {
			ur_membership_request_utils.validate_payment_gateway($(this));
		}

		// if ($(this).attr('id') === "ur-membership-pg-stripe" && $(this).is(":checked")) {
		// 	if ($('#ur-input-type-publishable-key').val() === "" || $('#ur-input-type-secret-key').val() === "" || $('#stripe-section .stripe-settings').length) {
		// 		ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + ur_membership_data.labels.i18n_stripe_setup_error);
		// 		$(this).prop('checked', false);
		// 	}
		// }
		//
		// if ($(this).attr('id') === "ur-membership-pg-paypal" && $(this).is(":checked")) {
		// 	if ($('#ur-input-type-paypal-email').val() === "" || $('#paypal-section #settings-section').length) {
		// 		ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + ur_membership_data.labels.i18n_paypal_setup_error);
		// 		$(this).prop('checked', false);
		// 	}
		//
		// }
		// if ($(this).attr('id') === "ur-membership-pg-bank" && $(this).is(":checked")) {
		// 	if ($('#bank-section .bank-settings').length) {
		// 		ur_membership_utils.show_failure_message(ur_membership_data.labels.i18n_error + '! ' + ur_membership_data.labels.i18n_bank_setup_error);
		// 		$(this).prop('checked', false);
		// 	}
		// }
	});

	//delete membership
	$('.delete-membership').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $this = $(this);
		Swal.fire({
			title:
				'<img src="' +
				ur_membership_data.delete_icon +
				'" id="delete-user-icon">' +
				ur_membership_data.labels.i18n_prompt_title,
			html: '<p id="html_1">' +
				ur_membership_data.labels.i18n_prompt_single_subtitle +
				'</p>',
			showCancelButton: true,
			confirmButtonText: ur_membership_data.labels.i18n_prompt_delete,
			cancelButtonText: ur_membership_data.labels.i18n_prompt_cancel,
			allowOutsideClick: false
		}).then(function (result) {
			if (result.isConfirmed) {
				$(location).attr('href', $this.attr('href'));
			}
		});
	});

	$('#membership-list #doaction,#doaction2').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var form = $('#membership-list'),
			selectedAction = form.find('select#bulk-action-selector-top option:selected').val();
		switch (selectedAction) {
			case 'delete' :
				ur_membership_utils.handle_bulk_delete_action(form);
				break;
			default:
				break;
		}

	});

})(jQuery, window.ur_membership_localized_data);
