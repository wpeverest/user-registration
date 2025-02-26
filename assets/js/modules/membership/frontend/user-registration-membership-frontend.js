/*global console, user_registration_params, Promise */
(function ($, urmf_data) {
	$(document).on('ready', function () {
		var elements = {};

		var ur_membership_frontend_utils = {
			/**
			 * Appends a spinner element to the specified element.
			 *
			 * @param {jQuery} $element - The jQuery element to which the spinner will be appended.
			 * @return {boolean} Returns true if the spinner was successfully appended, false otherwise.
			 */
			append_spinner: function ($element) {
				if ($element && $element.append) {
					var spinner = '<span class="urm-spinner is-active"></span>';
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
					$element.find('.urm-spinner').remove();
					return true;
				}
				return false;
			},

			/**
			 * A function that converts an object to an array by taking its values, excluding the first two, and preserving the original order.
			 *
			 * @param {jQuery} $object - The jQuery object to be converted to an array.
			 * @return {Array} The array with values from the object, excluding the first two, and in the original order.
			 */
			convert_to_array: function ($object) {
				return Object.values($object).reverse().slice(2).reverse();
			},
			/**
			 *
			 * @param disable
			 * @param btn
			 */
			toggleSaveButtons: function (disable, btn) {
				disable = this.if_empty(disable, true);
				$(btn).prop('disabled', !!disable);
			},

			/**
			 *
			 * @param value
			 * @param _default
			 * @returns {*}
			 */
			if_empty: function (value, _default) {
				if (null === value || undefined === value || '' === value) {
					return _default;
				}
				return value;
			},

			clear_validation_error: function () {
				$('span.notice_red').each(function () {
					$(this).text('');
				});
			},

			show_validation_error: function (notice_div, message) {
				notice_div.removeClass('notice_blue').addClass('notice_red').text(message);
				this.clear_validation_error();
				var input = notice_div.siblings('input');
				$('html, body').animate({
					scrollTop: notice_div.parent().offset().top
				}, 200);
				notice_div.text(message);
			},
			// Function to toggle the notice
			toggleNotice: function () {
				$('.notice-container').toggleClass('active');
				setTimeout(this.toggleNotice, 5000);
			},

			show_failure_message: function (message) {
				$('.notice-container .notice_blue').removeClass('notice_blue').addClass('notice_red');
				$('.notice_message').text(message);
				this.toggleNotice();
			},

			show_success_message: function (message) {
				$('.notice-container .notice_red').removeClass('notice_red').addClass('notice_blue');
				$('.notice_message').text(message);
				this.toggleNotice();
			},
			show_form_success_message: function (form_response, thank_you_data) {
				var response_data = form_response.data,
					ursL10n = user_registration_params.ursL10n,
					$registration_form = $('#user-registration-form-' + form_response.form_id),
					message = $('<ul class=""/>'),
					success_message_position = response_data.success_message_positon,
					redirect_url = urmf_data.thank_you_page_url;


				if ('undefined' !== typeof response_data.role_based_redirect_url) {
					redirect_url = response_data.role_based_redirect_url;
				}
				if ('undefined' !== typeof response_data.redirect_url) {
					redirect_url = response_data.redirect_url;
				}

				/**
				 * Remove Spinner.
				 */
				$registration_form
					.find('.ur-submit-button')
					.find("span")
					.removeClass('ur-front-spinner');

				/**
				 * Append Success Message according to login option.
				 */
				if (response_data.form_login_option == 'admin_approval') {
					message.append('<li>' + ursL10n.user_under_approval + '</li>');
				} else if (
					response_data.form_login_option === 'email_confirmation' ||
					response_data.form_login_option ===
					'admin_approval_after_email_confirmation'
				) {
					message.append("<li>" + ursL10n.user_email_pending + "</li>");
				} else {
					message.append("<li>" + ursL10n.user_successfully_saved + "</li>");
				}

				$registration_form.find('form')[0].reset();
				var wrapper = $(
					'<div class="ur-message user-registration-message" id="ur-submit-message-node"/>'
				);
				wrapper.append(message);

				// Check the position set by the admin and append message accordingly.
				if ('1' === success_message_position) {
					$registration_form.find('form').append(wrapper);
					$(window).scrollTop(
						$registration_form.find('form').find('.ur-button-container').offset().top
					);
				} else {
					$registration_form.find('form').prepend(wrapper);
					$(window).scrollTop(
						$registration_form.find('form').closest('.ur-frontend-form').offset().top
					);
				}
				$registration_form.find('form').find('.ur-submit-button').prop('disabled', false);

				if ('undefined' !== typeof redirect_url && redirect_url !== '') {
					ur_membership_ajax_utils.show_default_response(redirect_url, thank_you_data);
				} else {
					if (
						typeof response_data.auto_login !== 'undefined' &&
						response_data.auto_login
					) {
						ur_membership_ajax_utils.show_default_response(redirect_url, thank_you_data);
					}
				}


			},
			isEventRegistered: function (selector, eventType) {
				var events = $._data($(selector)[0], 'events');
				return (events && events[eventType]);
			}
		};

		var ur_membership_ajax_utils = {
			/**
			 *
			 * @returns {{}}
			 */
			prepare_members_data: function () {
				var user_data = {},
					form_inputs = $('#ur-membership-registration').find('input.ur_membership_input_class');
				form_inputs = ur_membership_frontend_utils.convert_to_array(form_inputs);
				form_inputs.forEach(function (item) {
					var $this = $(item);
					if($this.attr('name') !== undefined) {
						var name = $this.attr('name').toLowerCase().replace('urm_', '');
						user_data[name] = $this.val();
					}
				});
				var membership_input = $('input[name="urm_membership"]:checked');
				user_data.membership = membership_input.val();
				user_data.payment_method = 'free';
				if (membership_input.data('urm-pg-type') !== 'free') {
					user_data.payment_method = $('input[name="urm_payment_method"]:checked').val();
				}
				var date = new Date();
				user_data.start_date = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
				return user_data;
			},
			prepare_coupons_apply_data: function () {
				var coupon_data = {};
				coupon_data.coupon = $('#ur-membership-coupon').val();
				coupon_data.membership_id = $('input[name="urm_membership"]:checked').val();
				return coupon_data;
			},
			/**
			 * validate membership form before submit
			 * @returns {boolean}
			 */
			validate_membership_form: function () {
				var no_errors = true,
					pg_inputs = $('input[name="urm_payment_method"]:visible');
				if (pg_inputs.length > 0) {
					pg_inputs.each(function () {
						if ($(this).val() === 'stripe' && $(this).is(':checked')) {
							var is_empty = $('.ur-frontend-form').find('.stripe-input-container .StripeElement--empty').length;
							if (is_empty) {
								no_errors = false;
								var event = {
									error: {
										message: urmf_data.labels.i18n_empty_card_details,
									}
								};
								elements.card.emit('change', event);
							}
						}
					});
				}
				if (no_errors) {
					ur_membership_frontend_utils.clear_validation_error();
				}
				return no_errors;
			},
			validate_coupon_data: function () {
				var coupon = $('#ur-membership-coupon').val(),
					membership = $('input[name="urm_membership"]:checked'),
					error_div = $('#coupon-validation-error'),
					no_error = true;
				ur_membership_frontend_utils.clear_validation_error();
				//coupon can not be empty
				if (coupon.length < 1) {
					no_error = false;
					ur_membership_frontend_utils.show_validation_error(error_div, urmf_data.labels.i18n_error + '! ' + urmf_data.labels.i18n_coupon_empty_error);
					return no_error;
				}
				//membership must be selected
				if (membership.length === 0) {
					no_error = false;
					ur_membership_frontend_utils.show_validation_error(error_div, urmf_data.labels.i18n_error + '! ' + urmf_data.labels.i18n_membership_required);
					return no_error;
				}
				//membership cannot be free
				if (membership.data('urm-pg-type') === 'free') {
					no_error = false;
					ur_membership_frontend_utils.show_validation_error(error_div, urmf_data.labels.i18n_error + '! ' + urmf_data.labels.i18n_coupon_free_membership_error);
					return no_error;
				}
				return no_error;
			},
			/**
			 * called to create a new membership
			 * @param data
			 */
			create_member: function (form_response) {
				var prepare_members_data = this.prepare_members_data();
				prepare_members_data.username = form_response.data.username;

				this.send_data(
					{
						action: 'user_registration_membership_register_member',
						members_data: JSON.stringify(prepare_members_data),
						form_response: JSON.stringify(form_response.data)
					},
					{
						success: function (response) {
							if (response.success) {
								//first show successful toast
								ur_membership_ajax_utils.handle_response(response, prepare_members_data, form_response);
							} else {
								ur_membership_frontend_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_frontend_utils.show_failure_message(
								urmf_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						},
						complete: function () {
							ur_membership_frontend_utils.remove_spinner($this);
							ur_membership_frontend_utils.toggleSaveButtons(false, $this);
						}
					}
				);

			},
			/**
			 * Handles the response based on the payment method selected.
			 *
			 * @param {Object} response - The response data from the server.
			 * @param {Object} prepare_members_data - The data for preparing members.
			 */
			handle_response:  function (response, prepare_members_data, form_response) {
				switch (prepare_members_data.payment_method) {
					case 'paypal': //for paypal response must contain `payment_url` field
						ur_membership_frontend_utils.show_success_message(
							response.data.message
						);
						window.location.replace(response.data.pg_data.payment_url);
						break;
					case 'bank':
						this.show_bank_response(response, prepare_members_data, form_response);
						break;
					case 'stripe':
						stripe_settings.handle_stripe_response(response, prepare_members_data, form_response);
						break;
					default:
						ur_membership_frontend_utils.show_form_success_message(form_response, {
							'username': prepare_members_data.username
						});
						break;
				}
			},

			/**
			 * Handles the response for showing bank information.
			 *
			 * @param {Object} response - The response data from the server.
			 * @return {void} No return value.
			 */
			show_bank_response: function (response, prepare_members_data, form_response) {
				var bank_data = {
					'transaction_id': response.data.transaction_id,
					'payment_type': 'unpaid',
					'info': response.data.pg_data.data,
					'username': prepare_members_data.username
				};

				ur_membership_frontend_utils.show_form_success_message(form_response, bank_data);
			},

			/**
			 * Shows the default response when payment method is free.
			 */
			show_default_response: function (url, thank_you_data) {
				var url_params = $.param(thank_you_data).toString();
				window.setTimeout(function () {
					window.location.replace(url + '?' + url_params);
				}, 1000);

			},
			validate_coupon: function ($this) {
				ur_membership_frontend_utils.toggleSaveButtons(true, $this);
				ur_membership_frontend_utils.append_spinner($this);

				if (this.validate_coupon_data()) {
					this.send_data(
						{
							action: 'user_registration_membership_validate_coupon',
							coupon_data: this.prepare_coupons_apply_data()
						},
						{
							success: function (response) {
								if (response.success) {
									ur_membership_ajax_utils.handle_coupon_validation_response(response);
								} else {
									ur_membership_frontend_utils.show_failure_message(
										response.data.message
									);
								}
							},
							failure: function (xhr, statusText) {
								if (xhr.status === 500) {
									ur_membership_frontend_utils.show_failure_message(
										urmf_data.labels.network_error +
										'(' +
										statusText +
										')'
									);
								} else {
									ur_membership_frontend_utils.show_validation_error($('#coupon-validation-error'), urmf_data.labels.i18n_error + '! ' + xhr.responseJSON.data.message);
								}
							},
							complete: function () {
								ur_membership_frontend_utils.remove_spinner($this);
								ur_membership_frontend_utils.toggleSaveButtons(false, $this);
							}
						}
					);
				} else {
					ur_membership_frontend_utils.toggleSaveButtons(false, $this);
					ur_membership_frontend_utils.remove_spinner($this);
				}
			},
			handle_coupon_validation_response: function (response) {
				$('.urm_apply_coupon').hide();
				//show success message
				ur_membership_frontend_utils.clear_validation_error();
				$('#coupon-validation-error').removeClass('notice_red').addClass('notice_blue').text(response.data.message);
				//handle discount notice part
				response = JSON.parse(response.data.data);
				var selected_membership = $('input[name="urm_membership"]:checked'),
					prefix = '';
				//add discount amount as attribute on selected membership

				selected_membership.attr('data-ur-discount-amount', response.discount_amount);
				//calculate total
				ur_membership_ajax_utils.calculate_total(selected_membership);

				prefix = response.coupon_details.coupon_discount_type === 'fixed' ?
					urmf_data.currency_symbol + '' + response.coupon_details.coupon_discount :
					response.coupon_details.coupon_discount + '%';
				// show notice below total
				$('#total-input-notice').text(prefix + ' ' + urmf_data.labels.i18n_coupon_discount_message);
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
				if (!data._wpnonce && urmf_data) {
					data._wpnonce = urmf_data._nonce;
				}
				$.ajax({
					type: 'post',
					dataType: 'json',
					url: urmf_data.ajax_url,
					data: data,
					beforeSend: beforeSend_callback,
					success: success_callback,
					error: failure_callback,
					complete: complete_callback
				});
			},

			calculate_total: function ($this) {
				var urm_calculated_total = $this.data('urm-pg-calculated-amount');
				var total_input = $('#ur-membership-total'),
					discount_amount = $this.data('ur-discount-amount'),
					total = (discount_amount !== undefined && discount_amount !== '') ? urm_calculated_total - discount_amount : urm_calculated_total;
				total_input.val(urmf_data.currency_symbol + total);
			}
		};
		var form_object = {
			hide_loader: function (form_id) {
				var $registration_form = $('#user-registration-form-' + form_id);
				$registration_form.find('.ur-submit-button').find("span").removeClass('ur-spinner');
				$registration_form.find('form').find('.ur-submit-button').prop('disabled', false);
			}
		};
		var stripe_settings = {
			show_stripe_error: function (message) {
				if ($membership_registration_form.find("#stripe-errors").length > 0) {
					$membership_registration_form.find("#stripe-errors").html(message).show();
				} else {
					var error_message = '<label id="stripe-errors" class="user-registration-error" role="alert">' + message + '</label>';
					$membership_registration_form.find('.stripe-container').closest('.ur_membership_frontend_input_container').append(error_message);
				}
			},
			init: function () {
				elements = stripe_settings.setupElements();
				$membership_registration_form = $('#ur-membership-registration');
				this.triggerInputChange();
			},
			triggerInputChange: function () {
				elements.card.addEventListener('change', function (e) {
					if (e.error) {
						stripe_settings.show_stripe_error(e.error.message);
					} else {
						if ($membership_registration_form.find("#stripe-errors").length > 0) {
							$membership_registration_form.find("#stripe-errors").remove();
						}
					}
				});
			},
			setupElements: function () {
				var stripe = Stripe(urmf_data.stripe_publishable_key), //take this from global variable
					elements = stripe.elements();


				var style = {
					base: {
						color: "#32325d",
						fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
						fontSmoothing: "antialiased",
						fontSize: "14px",
						"::placeholder": {
							color: "#8f9194",
						},
					},
					invalid: {
						color: "#fa755a",
						iconColor: "#fa755a",
					},
				};

				var card = elements.create("card", {
					style: style
				});
				var idealBank = elements.create("idealBank", {style: style});

				card.mount('#card-element');
				return {
					stripe: stripe,
					card: card,
					ideal: idealBank,
					clientSecret: "",
				};
			},

			handle_stripe_response: function (response, prepare_members_data, form_response) {
				if (response.data.pg_data.type === 'paid') {
					this.handle_one_time_payment(response, prepare_members_data, form_response);
				} else {
					this.handle_recurring_payment(response, {
						paymentElements: elements,
						user_id: response.data.member_id,
						response_data: response,
						prepare_members_data: prepare_members_data,
						form_response: form_response
					});
				}
			},

			handle_one_time_payment: function (response, prepare_members_data, form_response) {
				elements.stripe
					.confirmCardPayment(response.data.pg_data.client_secret, {
						payment_method: {
							card: elements.card,
						},
					})
					.then(function (result) {
						var button = $('.membership_register_button');
						ur_membership_frontend_utils.toggleSaveButtons(true, button);
						ur_membership_frontend_utils.append_spinner(button);
						stripe_settings.update_order_status(result, response, prepare_members_data, form_response)
					});
			},
			update_order_status: function (result, response, prepare_members_data, form_response) {
				ur_membership_ajax_utils.send_data(
					{
						_wpnonce: urmf_data._confirm_payment_nonce,
						action: 'user_registration_membership_confirm_payment',
						members_data: JSON.stringify(prepare_members_data),
						member_id: response.data.member_id,
						payment_status: result.error ? "failed" : "succeeded",
						form_response: JSON.stringify(form_response.data),
						payment_result: result
					},
					{
						success: function (response) {
							if (response.success) {
								//first show successful toast
								ur_membership_ajax_utils.show_default_response(urmf_data.thank_you_page_url, {
									'username': prepare_members_data.username,
									'transaction_id': result.paymentIntent.id
								});

							} else {
								stripe_settings.show_stripe_error(response.data.message)
								form_object.hide_loader(form_response.form_id);

							}
						},
						failure: function (xhr, statusText) {
							ur_membership_frontend_utils.show_failure_message(
								urmf_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						},
						complete: function () {
							var button = $('.membership_register_button');
							ur_membership_frontend_utils.toggleSaveButtons(false, button);
							ur_membership_frontend_utils.remove_spinner(button);
						}
					}
				);
			},
			handle_recurring_payment: function (response, data) {
				Promise.resolve($.extend({}, data, {customer_id: response.data.pg_data.stripe_cus_id}))
					.then(stripe_settings.createPaymentMethod)
					.then(stripe_settings.createSubscription)
					.then(stripe_settings.handleCustomerActionRequired)
					.then(stripe_settings.handleOnComplete)
					.catch(function (message, error) {
						stripe_settings.update_order_status({error: {}}, response, data.prepare_members_data, data.form_response);
					});
			},
			/**
			 * Create payment method.
			 *
			 * @param {object} data Contains Stripe, card, paymentItems, current form selector and customerId.
			 */
			createPaymentMethod: function (data) {
				return new Promise(function (resolve, reject) {

					var button = $('.membership_register_button');
					ur_membership_frontend_utils.toggleSaveButtons(true, button);
					ur_membership_frontend_utils.append_spinner(button);
					// Simulating async process
					data.paymentElements.stripe
						.createPaymentMethod({
							type: "card",
							card: data.paymentElements.card,
						})
						.then(function (result) {
							if (result.error) {
								reject(result.error.message, result); // Reject the promise with the error
							} else {
								resolve($.extend({}, data, {payment_method_id: result.paymentMethod.id}));
							}
						}).catch(function (error) {
						reject(error, result); // Catch any unexpected errors and reject
					});
				});
			},

			/**
			 * Create subscription.
			 *
			 * @param {object} dataContains Stripe, card, formid, paymentItems, current form selector, customerId and paymentMethodId.
			 */
			createSubscription: function (data) {

				return new Promise(function (resolve, reject) {
					ur_membership_ajax_utils.send_data({
						_wpnonce: urmf_data._confirm_payment_nonce,
						action: "user_registration_membership_create_stripe_subscription",
						member_id: data.user_id,
						customer_id: data.customer_id,
						payment_method_id: data.payment_method_id,
						form_response: JSON.stringify(data.form_response.data)
					}, {
						success: function (response) {
							if (response.success) {
								var paymentIntent =
									response.data.subscription.latest_invoice.payment_intent;

								if (response.error) {
									var message = response.error.message;
									reject(message, data);
									return;
								}

								if ("trialing" !== response.data.subscription.status) {
									if (paymentIntent && "requires_payment_method" === paymentIntent.status) {
										var message = "Your card was declined";
										reject(response, message);
									}
								}
								resolve(
									$.extend({}, data, {
										subscription: response.data.subscription,
										message: response.data.message,
									})
								);

							} else {
								form_object.hide_loader(data.form_id);
								reject(response, message);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_frontend_utils.show_failure_message(
								urmf_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						}
					});
				});
			},

			/**
			 * Handle customer action if it is required.
			 *
			 * @param {object} data Contains stripe, card, formid, paymentItems, form_selector, customerId, paymentMethodId and subscription.
			 *
			 */
			handleCustomerActionRequired: function (data) {

				return new Promise(function (resolve, reject) {
					if (
						data.subscription &&
						(data.subscription.status === "active" ||
							data.subscription.status === "trialing")
					) {
						resolve({
							subscription: data.subscription,
							response_data: data.response_data,
							message: data.message,
							prepare_members_data: data.prepare_members_data,
							form_response: data.form_response
						});
					}

					var paymentIntent = data.subscription.latest_invoice.payment_intent;

					if ("trialing" !== data.subscription.status) {

						if ("requires_action" === paymentIntent.status) {
							data.paymentElements.stripe
								.confirmCardPayment(paymentIntent.client_secret, {
									payment_method: data.paymentMethodId,
								})
								.then(function (result) {
									if (result.error) {
										var message = result.error.message;
										reject(message, data);
										return;
									}

									if ("succeeded" === result.paymentIntent.status) {
										data.subscription.status = "active";
										resolve({
											subscription: data.subscription,
											form_id: data.form_id,
											response_data: data.response_data,
											form_response: data.form_response
										});
									} else {
										var message = "Unable to complete the payment.";
										reject(message, data);
									}
								});
						}
					}
				});


			},

			/**
			 * Handle subscription complete.
			 *
			 * @param {object} data  Contains stripe, card, formid, paymentItems, form_selector, customerId, paymentMethodId and subscription.
			 */
			handleOnComplete: function (data) {
				if (
					data.subscription &&
					(data.subscription.status === "active" ||
						data.subscription.status === "trialing")
				) {
					ur_membership_frontend_utils.show_form_success_message(data.form_response, {
						'username': data.prepare_members_data.username,
						'transaction_id': data.subscription.id
					});
				}
			}
		};
		//activate payment gateways
		$('input[name="urm_membership"]').on('change', function () {
			// clear coupon total notice
			$('#total-input-notice').text('');

			var urm_payment_gateways = $(this).data('urm-pg'),
				urm_payment_type = $(this).data('urm-pg-type'),
				urm_pg_container = $('.ur_payment_gateway_container'),
				urm_pg_inputs = urm_pg_container.find('input'),
				urm_hidden_pg_containers = $('.urm_hidden_payment_container'),
				stripe_container = $('.stripe-container'),
				stripe_error_container = $('#stripe-errors');

			stripe_error_container.remove();
			$('input[name="urm_payment_method"]').prop('checked', false);
			stripe_container.addClass('urm-d-none');
			urm_hidden_pg_containers.addClass('urm-d-none');

			$('.urm_apply_coupon').show();
			if (urm_payment_type !== 'free') {
				urm_hidden_pg_containers.removeClass('urm-d-none');

				urm_pg_inputs.each(function (key, item) {
					var current_gateway = $(item).val(),
						input_container = $('label[for="ur-membership-' + current_gateway + '"]');
					input_container.removeClass('urm-d-none');
					if (!urm_payment_gateways.hasOwnProperty(current_gateway)) {
						input_container.addClass('urm-d-none');
					}
				});
				ur_membership_ajax_utils.calculate_total($(this));
			}
		});
		// membership input change trigger for page with membership id as params.
		var searchParams = new URLSearchParams(window.location.search);
		if (searchParams.has('membership_id')) {
			$('input[name="urm_membership"]:checked').change();
		}

		$('.close_notice').on('click', ur_membership_frontend_utils.toggleNotice);

		$('#ur-membership-password').on('keyup change', function () {
			var $this = $(this),
				password = $this.val(),
				notice_div = $('#password-notice'),
				pass_regex = new RegExp('^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*()_+}{"\':;?/>.<,]).*$');
			ur_membership_frontend_utils.show_validation_error(notice_div, '');

			if (password.length < 8) {
				ur_membership_frontend_utils.show_validation_error(notice_div, urmf_data.labels.i18n_field_password_field_length_validation);
				return;
			}
			if (!pass_regex.test(password)) {
				ur_membership_frontend_utils.show_validation_error(notice_div, urmf_data.labels.i18n_field_password_field_regex_validation);
				return;
			}
			return true;
		});

		$('#ur-membership-confirm-password').on('keyup', function () {
			var $this = $(this),
				confirm_password = $this.val(),
				password = $('#ur-membership-password').val(),
				notice_div = $('#confirm-password-notice');
			ur_membership_frontend_utils.show_validation_error(notice_div, '');

			if (password.length === 0) {
				ur_membership_frontend_utils.show_validation_error(notice_div, urmf_data.labels.i18n_field_password_empty_validation);
			}
			if (confirm_password !== password) {
				ur_membership_frontend_utils.show_validation_error(notice_div, urmf_data.labels.i18n_field_confirm_password_field_validation);
				return;
			}
			return true;
		});

		//apply coupon event
		$(document).on('click', '.urm_apply_coupon', function () {
			ur_membership_ajax_utils.validate_coupon($(this));
		});
		//coupon clear input
		$(document).on('click', '.ur_clear_coupon', function () {
			$('#ur-membership-coupon').val('');
			$('#coupon-validation-error').text('');
			$('.urm_apply_coupon').show();
			$('#total-input-notice').text('');
			var selected_membership = $('input[name="urm_membership"]:checked');
			selected_membership.removeData('ur-discount-amount').removeAttr('data-ur-discount-amount');
			ur_membership_ajax_utils.calculate_total(selected_membership);

		});
		//redirect to membership member registration form
		$(document).on('click', '#membership-old-selection-form .membership-signup-button', function () {
			var $this = $(this),
				membership_id = $this.siblings('input').val(),
				url = urmf_data.membership_registration_page_url + '?membership_id=' + membership_id;
			window.location.replace(url);
		});

		//validate before submit
		$(document).on('user_registration_frontend_validate_before_form_submit', function () {
			ur_membership_ajax_utils.validate_membership_form();
		});
		$(document).on(
			"user_registration_frontend_before_form_submit",
			function (event, data, pointer, $error_message) {
				if ($(pointer).find('#ur-membership-registration').length > 0) {
					data['is_membership_active'] = $(pointer).find('input[name="urm_membership"]:checked').val();
					data['membership_type'] = $('input[name="urm_membership"]:checked').val();
				}
			}
		);
		$(document).on('user_registration_frontend_before_ajax_complete_success_message', function (event, ajax_response, ajaxFlag, form) {
			var flag = true,
				response = JSON.parse(ajax_response.responseText),
				required_data = {
					data: response.data,
					form_id: $(form).data('form-id')
				};

			if (typeof response.data.registration_type !== 'undefined' && response.data.registration_type === 'membership') {
				flag = false;
				ur_membership_ajax_utils.create_member(required_data);
			}
			ajaxFlag['status'] = flag;

		});

		//on toggle payment gatewaysw
		$('input[name="urm_payment_method"]').on('change', function () {
			var selected_method = $(this).val(),
				stripe_container = $('.stripe-container'),
				stripe_error_container = $('#stripe-errors');

			stripe_container.addClass('urm-d-none');
			stripe_error_container.remove();
			elements = {};
			if (selected_method === 'stripe') {
				if (urmf_data.stripe_publishable_key.length == 0) {
					ur_membership_frontend_utils.show_failure_message(urmf_data.labels.i18n_incomplete_stripe_setup_error);
					return;
				}
				stripe_container.removeClass('urm-d-none');
				stripe_settings.init();
			}
		});

		//cancel membership button
		$(document).on("click", ".cancel-membership-button", function () {
			var $this = $(this),
				error_div = $("#membership-error-div"),
				button_text = $this.text();

			Swal.fire({
				icon: "warning",
				title: urmf_data.labels.i18n_cancel_membership_text,
				text: urmf_data.labels.i18n_cancel_membership_subtitle,
				customClass:
					"user-registration-swal2-modal user-registration-swal2-modal--center",
				showConfirmButton: true,
				showCancelButton: true,
			}).then(function (result) {
				if (result.isConfirmed) {
					$.ajax({
						url: urmf_data.ajax_url,
						type: "POST",
						data: {
							action: "user_registration_membership_cancel_subscription",
							security: urmf_data._nonce,
							subscription_id: $this.data("id")
						},
						beforeSend: function () {
							$this.text(
								urmf_data.labels.i18n_sending_text
							);
						},
						success: function (response) {
							if (response.success) {
								if (error_div.hasClass("btn-error")) {
									error_div.removeClass("btn-error");
									error_div.addClass("btn-success");
								}
								error_div.text(response.data.message);
								error_div.show();
								location.reload();
							} else {
								if (error_div.hasClass("btn-success")) {
									error_div.removeClass("btn-success");
									error_div.addClass("btn-error");
								}
								error_div.text(response.data.message);
								error_div.show();
							}
						},
						complete: function () {
							$this.text(button_text);
						}
					});
				}
			});
		});

		//disable submit button if empty membership field
		if ($('.field-membership').length) {
			$('.field-membership').each(function (key, item) {
				if ($(item).find('.no-membership')) {
					var form_id = $(item).find('.no-membership').attr('data-form-id');
					$('#user-registration-form-'+form_id).find('.ur-submit-button').prop('disabled', true);
				}
			});
		}
	});
})(jQuery, window.ur_membership_frontend_localized_data);
