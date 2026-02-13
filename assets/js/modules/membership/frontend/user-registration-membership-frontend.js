/*global console, user_registration_params, Promise */
(function ($, urmf_data) {
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
				$element.find(".urm-spinner").remove();
				return true;
			}
			return false;
		},


		/**
		 * Shows the payment processing spinner on the button container.
		 * Used during Stripe payment processing to indicate payment is in progress.
		 */
		show_payment_processing_overlay: function () {
			var $form = $('.ur-frontend-form').find('form.register');
			var $buttonContainer = $form.find('.ur-button-container');
			var $submitButton = $buttonContainer.find('.ur-submit-button');

			$submitButton.prop('disabled', true);
			if (!$submitButton.find('span.ur-front-spinner').length) {
				$submitButton.find('span').addClass('ur-front-spinner');
			}
		},

		/**
		 * Hides and removes the payment processing overlay.
		 */
		hide_payment_processing_overlay: function () {
			var $form = $('.ur-frontend-form').find('form.register');
			var $buttonContainer = $form.find('.ur-button-container');
			var $submitButton = $buttonContainer.find('.ur-submit-button');

			$submitButton.find('span').removeClass('ur-front-spinner');
		},

		/**
		 * Updates the payment processing state (kept for API compatibility).
		 * Since we're using the button spinner, this just ensures spinner is shown.
		 */
		update_payment_processing_overlay: function () {
			this.show_payment_processing_overlay();
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
			$(btn).prop("disabled", !!disable);
		},

		/**
		 *
		 * @param value
		 * @param _default
		 * @returns {*}
		 */
		if_empty: function (value, _default) {
			if (null === value || undefined === value || "" === value) {
				return _default;
			}
			return value;
		},

		clear_validation_error: function () {
			$("span.notice_red").each(function () {
				$(this).text("");
			});
		},

		show_validation_error: function (notice_div, message) {
			notice_div
				.removeClass("notice_blue")
				.addClass("notice_red")
				.text(message);
			this.clear_validation_error();
			var input = notice_div.siblings("input");
			$("html, body").animate(
				{
					scrollTop: notice_div.parent().offset().top
				},
				200
			);
			notice_div.text(message);
		},
		// Function to toggle the notice
		toggleNotice: function () {
			$(".notice-container").toggleClass("active");
			setTimeout(this.toggleNotice, 5000);
		},

		show_failure_message: function (message) {
			$(".notice-container .notice_blue")
				.removeClass("notice_blue")
				.addClass("notice_red");
			$(".notice_message").text(message);
			this.toggleNotice();
		},

		show_success_message: function (message) {
			$(".notice-container .notice_red")
				.removeClass("notice_red")
				.addClass("notice_blue");
			$(".notice_message").text(message);
			this.toggleNotice();
		},
		show_form_success_message: function (form_response, thank_you_data) {
			var response_data = form_response.data,
				ursL10n = user_registration_params.ursL10n,
				$registration_form = $(
					"#user-registration-form-" + form_response.form_id
				),
				message = $('<ul class=""/>'),
				success_message_position =
					response_data.success_message_positon,
				redirect_url = $registration_form
					.find('input[name="ur-redirect-url"]')
					.val(),
				timeout = form_response.data.redirect_timeout
					? form_response.data.redirect_timeout
					: 2000;
			var originalRedirectUrl = redirect_url;

			if ("undefined" !== typeof response_data.role_based_redirect_url) {
				redirect_url = response_data.role_based_redirect_url;
			}
			if (
				typeof form_response.data.form_login_option !== "undefined" &&
				form_response.data.form_login_option === "sms_verification"
			) {
				window.setTimeout(function () {
					if (
						typeof form_response.data.redirect_url !==
							"undefined" &&
						form_response.data.redirect_url
					) {
						window.location = form_response.data.redirect_url;
					}
				}, timeout);
			}
			if ("undefined" !== typeof redirect_url && redirect_url !== "") {
				$(document).trigger(
					"user_registration_frontend_before_redirect_url",
					[redirect_url]
				);

				window.setTimeout(function () {
					window.location = redirect_url;
				}, timeout);

				if ("" != originalRedirectUrl) {
					return;
				}
			} else {
				redirect_url = urmf_data.thank_you_page_url;
			}
			/**
			 * Remove Spinner.
			 */
			$registration_form
				.find(".ur-submit-button")
				.find("span")
				.removeClass("ur-front-spinner");

			/**
			 * Append Success Message according to login option.
			 */
			if (response_data.form_login_option == "admin_approval") {
				message.append("<li>" + ursL10n.user_under_approval + "</li>");
			} else if (
				response_data.form_login_option === "email_confirmation" ||
				response_data.form_login_option ===
					"admin_approval_after_email_confirmation"
			) {
				message.append("<li>" + ursL10n.user_email_pending + "</li>");
			} else {
				message.append(
					"<li>" + ursL10n.user_successfully_saved + "</li>"
				);
			}

			var searchParams = new URLSearchParams(window.location.search),
				action = searchParams.get("action");

			if (
				"hide_message" != thank_you_data.context &&
				(action === "register" || null === action)
			) {
				$registration_form.find("form")[0].reset();
				var wrapper = $(
					'<div class="ur-message user-registration-message" id="ur-submit-message-node"/>'
				);
				wrapper.append(message);

				// Check the position set by the admin and append message accordingly.
				if ("1" === success_message_position) {
					$registration_form.find("form").append(wrapper);
					$(window).scrollTop(
						$registration_form
							.find("form")
							.find(".ur-button-container")
							.offset().top
					);
				} else {
					$registration_form.find("form").prepend(wrapper);
					$(window).scrollTop(
						$registration_form
							.find("form")
							.closest(".ur-frontend-form")
							.offset().top
					);
				}
			}

			$registration_form
				.find("form")
				.find(".ur-submit-button")
				.prop("disabled", false);

			if ("undefined" !== typeof redirect_url && redirect_url !== "") {
				ur_membership_ajax_utils.show_default_response(
					redirect_url,
					thank_you_data,
					timeout
				);
			} else {
				if (
					typeof response_data.auto_login !== "undefined" &&
					response_data.auto_login
				) {
					ur_membership_ajax_utils.show_default_response(
						redirect_url,
						thank_you_data,
						timeout
					);
				}
			}
		},
		isEventRegistered: function (selector, eventType) {
			var events = $._data($(selector)[0], "events");
			return events && events[eventType];
		}
	};
	var ur_membership_ajax_utils = {
		/**
		 *
		 * @returns {{}}
		 */
		prepare_members_data: function () {
			var user_data = {},
				form_inputs = $("#ur-membership-registration").find(
					"input.ur_membership_input_class"
				);
			form_inputs =
				ur_membership_frontend_utils.convert_to_array(form_inputs);
			form_inputs.forEach(function (item) {
				var $this = $(item);
				if ($this.attr("name") !== undefined) {
					var name = $this
						.attr("name")
						.toLowerCase()
						.replace("urm_", "");
					user_data[name] = $this.val();
				}
			});
			var membership_input = $('input[name="urm_membership"]:checked');
			user_data.membership = membership_input.val();
			user_data.payment_method = "free";
			if (membership_input.data("urm-pg-type") !== "free") {
				user_data.payment_method = $(
					'input[name="urm_payment_method"]:checked'
				).val();
			}
			var date = new Date();
			user_data.start_date =
				date.getFullYear() +
				"-" +
				(date.getMonth() + 1) +
				"-" +
				date.getDate();

			// Append tax details if available
			var taxDetails = $(document).find("#ur-tax-details");

			if (taxDetails.length > 0) {
				user_data.tax_rate = taxDetails.data("tax-rate");
				user_data.tax_calculation_method = taxDetails.data(
					"tax-calculation-method"
				);
			}

			var localCurrency = membership_input.data("local-currency"),
				geoZoneId = membership_input.data("zone-id");
			if (localCurrency && geoZoneId) {
				user_data.switched_currency = localCurrency;
				user_data.urm_zone_id = geoZoneId;
			}

			if ($("#ur-local-currency-switch-currency").length) {
				user_data.switched_currency = $(
					"#ur-local-currency-switch-currency"
				).val();
				user_data.urm_zone_id = $(
					"#ur-local-currency-switch-currency"
				).data("urm-zone-id");
			}

			var container = membership_input.closest(".urm-team-pricing-card");
			var selected_team = container.find(
				".urm-team-pricing-tier.selected"
			);
			if (selected_team.length > 0) {
				user_data.team = selected_team.data("team");
				var seat_model = selected_team.data("seat-model");
				if ("variable" === seat_model) {
					user_data.no_of_seats = selected_team
						.find('input[name="no_of_seats"]')
						.val();
					var pricing_model = selected_team.data("pricing-model");
					if ("tier" === pricing_model) {
						var selected_tier = selected_team.data("selected-tier");
						user_data.tier = selected_tier;
					}
				}
			}

			return user_data;
		},
		prepare_coupons_apply_data: function () {
			var coupon_data = {};
			coupon_data.coupon = $("#ur-membership-coupon").val();
			coupon_data.membership_id = $(
				'input[name="urm_membership"]:checked'
			).val();
			var membership_input = $('input[name="urm_membership"]:checked');

			var taxDetails = $(document).find("#ur-tax-details");

			if (taxDetails.length > 0) {
				coupon_data.tax_rate = taxDetails.data("tax-rate");
				coupon_data.tax_calculation_method = taxDetails.data(
					"tax-calculation-method"
				);
			}

			var localCurrency = membership_input.data("local-currency"),
				geoZoneId = membership_input.data("zone-id");
			if (localCurrency && geoZoneId) {
				coupon_data.switched_currency = localCurrency;
				coupon_data.urm_zone_id = geoZoneId;
			}

			if ($("#ur-local-currency-switch-currency").length) {
				coupon_data.switched_currency = $(
					"#ur-local-currency-switch-currency"
				).val();
				coupon_data.urm_zone_id = $(
					"#ur-local-currency-switch-currency"
				).data("urm-zone-id");
			}

			return coupon_data;
		},
		/**
		 * validate membership form before submit
		 * @returns {boolean}
		 */
		validate_membership_form: function (is_upgrade) {
			if (typeof is_upgrade === "undefined") {
				is_upgrade = false;
			}
			var no_errors = true,
				pg_inputs = $('input[name="urm_payment_method"]:visible'),
				selected_tier = $(".urm-team-pricing-tier.selected"),
				seat_input = selected_tier.find(".ur-team-tier-seats-input");

			if (pg_inputs.length > 0) {
				pg_inputs.each(function () {
					if ($(this).val() === "stripe" && $(this).is(":checked")) {
						var is_empty = is_upgrade
							? $(".membership-upgrade-container").find(
									".stripe-input-container .StripeElement--empty"
								).length
							: $(".ur-frontend-form").find(
									".stripe-input-container .StripeElement--empty"
								).length;

						if (is_empty) {
							no_errors = false;
							var event = {
								error: {
									message:
										urmf_data.labels.i18n_empty_card_details
								}
							};

							elements.card.emit("change", event);
						}
					}
				});
			}
			// seats validation if less than min seats, set the seat input to min seats and if greater than max seats, set the seat input to max seats and calculate total.
			if (seat_input.length > 0) {
				var no_of_seats = parseInt(seat_input.val(), 10);
				var pricing_model = selected_tier.data("pricing-model");
				var min_seats = parseInt(
					selected_tier.data("minimum-seats"),
					10
				);
				var max_seats = parseInt(
					selected_tier.data("maximum-seats"),
					10
				);
				var amount = 0;

				if (isNaN(no_of_seats) || no_of_seats < min_seats) {
					seat_input.val(min_seats);
					no_of_seats = min_seats;
				}
				if (no_of_seats > max_seats) {
					seat_input.val(max_seats);
					no_of_seats = max_seats;
				}

				if ("per_seat" === pricing_model) {
					amount = selected_tier.data("per-seat-price");
				} else {
					var tiers = selected_tier.data("price-tiers");
					$.each(tiers, function (index, tierItem) {
						var from = parseInt(tierItem.tier_from, 10);
						var to = parseInt(tierItem.tier_to, 10);

						if (no_of_seats >= from && no_of_seats <= to) {
							amount = tierItem.tier_per_seat_price;
							return false;
						}
					});
				}
				if (no_of_seats >= min_seats && no_of_seats <= max_seats) {
					var container = selected_tier.closest(
						".urm-team-pricing-card"
					);
					var radio = container.find('input[type="radio"]');
					var total = no_of_seats * amount;
					radio.data("urm-pg-calculated-amount", total);
					ur_membership_ajax_utils.calculate_total(radio);
				}
			}
			if (no_errors) {
				ur_membership_frontend_utils.clear_validation_error();
			}

			return no_errors;
		},
		validate_coupon_data: function () {
			var coupon = $("#ur-membership-coupon").val(),
				membership = $('input[name="urm_membership"]:checked'),
				error_div = $("#coupon-validation-error"),
				no_error = true;
			ur_membership_frontend_utils.clear_validation_error();
			//coupon can not be empty
			if (coupon.length < 1) {
				no_error = false;
				ur_membership_frontend_utils.show_validation_error(
					error_div,
					urmf_data.labels.i18n_error +
						"! " +
						urmf_data.labels.i18n_coupon_empty_error
				);
				return no_error;
			}
			//membership must be selected
			if (membership.length === 0) {
				no_error = false;
				ur_membership_frontend_utils.show_validation_error(
					error_div,
					urmf_data.labels.i18n_error +
						"! " +
						urmf_data.labels.i18n_membership_required
				);
				return no_error;
			}
			//membership cannot be free
			if (membership.data("urm-pg-type") === "free") {
				no_error = false;
				ur_membership_frontend_utils.show_validation_error(
					error_div,
					urmf_data.labels.i18n_error +
						"! " +
						urmf_data.labels.i18n_coupon_free_membership_error
				);
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
					action: "user_registration_membership_register_member",
					members_data: JSON.stringify(prepare_members_data),
					form_response: JSON.stringify(form_response.data)
				},
				{
					success: function (response) {
						if (response.success) {
							ur_membership_ajax_utils.handle_response(
								response,
								prepare_members_data,
								form_response
							);
						} else {
							ur_membership_frontend_utils.show_failure_message(
								response.data.message
							);
							form_object.hide_loader(form_response.form_id);
						}
					},
					failure: function (xhr, statusText) {
						ur_membership_frontend_utils.show_failure_message(
							urmf_data.labels.network_error +
								"(" +
								statusText +
								")"
						);
						form_object.hide_loader(form_response.form_id);
					},
					complete: function () {
						// form_object.hide_loader(form_response.form_id);
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
		handle_response: function (
			response,
			prepare_members_data,
			form_response
		) {
			switch (prepare_members_data.payment_method) {
				case "paypal": //for paypal response must contain `payment_url` field
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					window.location.replace(response.data.pg_data.payment_url);
					break;
				case "bank":
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					this.show_bank_response(
						response,
						prepare_members_data,
						form_response
					);
					break;
				case "stripe":
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					stripe_settings.handle_stripe_response(
						response,
						prepare_members_data,
						form_response
					);
					break;
				case "authorize":
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					window.location.replace(response.data.redirect);
					break;
				case "mollie":
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					window.location.replace(response.data.pg_data.payment_url);
					break;
				default:
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					ur_membership_frontend_utils.show_form_success_message(
						form_response,
						{
							username: prepare_members_data.username,
							context: "hide_message"
						}
					);
					break;
			}
		},

		/**
		 * Handles the response for showing bank information.
		 *
		 * @param {Object} response - The response data from the server.
		 * @return {void} No return value.
		 */
		show_bank_response: function (
			response,
			prepare_members_data,
			form_response
		) {
			var bank_data = {
				transaction_id: response.data.transaction_id,
				payment_type: "unpaid",
				info: response.data.pg_data.data,
				username: prepare_members_data.username,
				context: "hide_message"
			};

			if (response.data.is_renewing) {
				ur_membership_ajax_utils.show_default_response(
					window.location.href,
					bank_data
				);
			} else {
				ur_membership_frontend_utils.show_form_success_message(
					form_response,
					bank_data
				);
			}
		},

		/**
		 * Shows the default response when payment method is free.
		 */
		show_default_response: function (url, thank_you_data, timeout) {
			timeout = timeout || 2000;
			var thank_you_page_url = urmf_data.thank_you_page_url;

			var url_params = $.param(thank_you_data).toString();
			window.setTimeout(function () {
				window.location.replace(thank_you_page_url + "?" + url_params);
			}, timeout);
		},
		validate_coupon: function ($this) {
			ur_membership_frontend_utils.toggleSaveButtons(true, $this);
			ur_membership_frontend_utils.append_spinner($this);

			if (this.validate_coupon_data()) {
				var data = {
						action: "user_registration_membership_validate_coupon",
						coupon_data: this.prepare_coupons_apply_data()
					},
					membership_field = $this
						.closest(".ur_membership_registration_container")
						.find("input[name='urm_membership']:checked"),
					upgrade_type = membership_field.data("urm-upgrade-type");

				if (upgrade_type) {
					data.coupon_data.upgrade_amount = membership_field.data(
						"urm-pg-calculated-amount"
					);
				}

				this.send_data(data, {
					success: function (response) {
						if (response.success) {
							ur_membership_ajax_utils.handle_coupon_validation_response(
								response
							);
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
									"(" +
									statusText +
									")"
							);
						} else {
							ur_membership_frontend_utils.show_validation_error(
								$("#coupon-validation-error"),
								urmf_data.labels.i18n_error +
									"! " +
									xhr.responseJSON.data.message
							);
						}
					},
					complete: function () {
						ur_membership_frontend_utils.remove_spinner($this);
						ur_membership_frontend_utils.toggleSaveButtons(
							false,
							$this
						);
					}
				});
			} else {
				ur_membership_frontend_utils.toggleSaveButtons(false, $this);
				ur_membership_frontend_utils.remove_spinner($this);
			}
		},
		handle_coupon_validation_response: function (response) {
			$(".urm_apply_coupon").hide();
			//show success message
			ur_membership_frontend_utils.clear_validation_error();
			$("#coupon-validation-error")
				.removeClass("notice_red")
				.addClass("notice_blue")
				.text(response.data.message);
			//handle discount notice part
			response = JSON.parse(response.data.data);
			var selected_membership = $('input[name="urm_membership"]:checked'),
				prefix = "";
			//add discount amount as attribute on selected membership

			selected_membership.attr(
				"data-ur-discount-amount",
				response.discount_amount
			);
			//calculate total
			ur_membership_ajax_utils.calculate_total(selected_membership);

			prefix =
				response.coupon_details.coupon_discount_type === "fixed"
					? "" + response.coupon_details.coupon_discount
					: response.coupon_details.coupon_discount + "%";
			// show notice below total
			$("#total-input-notice").text(
				prefix + " " + urmf_data.labels.i18n_coupon_discount_message
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
			if (!data._wpnonce && urmf_data) {
				data._wpnonce = urmf_data._nonce;
			}
			$.ajax({
				type: "post",
				dataType: "json",
				url: urmf_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback
			});
		},

		calculate_total: function ($this) {
			var urm_calculated_total = $this.data("urm-pg-calculated-amount");
			var subTotalInput = $("#ur-membership-subtotal");
			var taxInput = $("#ur-membership-tax");
			var couponInput = $("#ur-membership-coupons");
			var localCurrency = "";
			var currency = urmf_data.currency_symbol;

			subTotal = urm_calculated_total;

			if ($this.data("local-currency")) {
				localCurrency = $this.data("local-currency");
				currency = register_events.decodeHtmlEntity(
					urmf_data.local_currencies_symbol[localCurrency] || currency
				);
			}

			var total_input = $("#ur-membership-total"),
				discount_amount = $this.data("ur-discount-amount"),
				total =
					discount_amount !== undefined && discount_amount !== ""
						? urm_calculated_total - discount_amount
						: urm_calculated_total,
				upgrade_type = $this.data("urm-upgrade-type");

			var total_label = $(".urm-membership-total-value").find(
				".ur_membership_input_label"
			);

			if (total_label.find(".user-registration-badge").length > 0) {
				total_label.find(".user-registration-badge").remove();
			}
			if (upgrade_type) {
				total_label.append(
					'<span class="user-registration-badge">' +
						upgrade_type +
						"</span>"
				);
			}

			var totalDetails =
				ur_membership_ajax_utils.convert_currency_and_calculate_tax(
					$this,
					total,
					subTotal,
					discount_amount
				);

			total = parseFloat(total).toFixed(2);
			if ("left" === urmf_data.curreny_pos) {
				total_input.text(
					currency + parseFloat(totalDetails.total).toFixed(2)
				);
				subTotalInput.text(
					currency + parseFloat(totalDetails.subTotal).toFixed(2)
				);
				taxInput.text(
					currency + parseFloat(totalDetails.taxAmount).toFixed(2)
				);
				couponInput.text(
					currency +
						parseFloat(totalDetails.discountAmount).toFixed(2)
				);
			} else {
				total_input.text(
					parseFloat(totalDetails.total).toFixed(2) + currency
				);
				subTotalInput.text(
					parseFloat(totalDetails.subTotal).toFixed(2) + currency
				);
				taxInput.text(
					parseFloat(totalDetails.taxAmount).toFixed(2) + currency
				);
				couponInput.text(
					parseFloat(totalDetails.discountAmount).toFixed(2) +
						currency
				);
			}
		},
		upgrade_membership: function (
			data,
			current_plan,
			selected_membership_id,
			current_subscription_id,
			selected_pg,
			btn
		) {
			//handle differently in case of Authorize.NET
			//gets the nonce token from ANET and send it via the AJAX request.
			if ("authorize" === selected_pg) {
				this.handle_authorize_upgrade(
					data,
					current_plan,
					selected_membership_id,
					current_subscription_id,
					selected_pg,
					btn
				);
			} else {
				this.send_data(
					{
						_wpnonce: urmf_data.upgrade_membership_nonce,
						action: "user_registration_membership_upgrade_membership",
						form_data: data.form_data,
						form_id: data.form_id,
						current_membership_id: current_plan,
						selected_membership_id: selected_membership_id,
						current_subscription_id: current_subscription_id,
						selected_pg: selected_pg,
						coupon: data.coupon
					},
					{
						success: function (response) {
							if (response.success) {
								ur_membership_frontend_utils.show_success_message(
									response.data.message
								);
								var prepare_members_data = {
									payment_method: selected_pg,
									username: response.data.username
								};

								ur_membership_ajax_utils.handle_update_response(
									response,
									prepare_members_data
								);
							} else {
								ur_membership_frontend_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_frontend_utils.show_failure_message(
								user_registration_params.network_error +
									"(" +
									statusText +
									")"
							);
						},
						complete: function () {
							if (selected_pg !== "stripe") {
								ur_membership_frontend_utils.remove_spinner(
									btn
								);
							}
							ur_membership_frontend_utils.toggleSaveButtons(
								false,
								btn
							);
						}
					}
				);
			}
		},
		convert_currency_and_calculate_tax: function (
			$this,
			total,
			subTotal,
			discount_amount
		) {
			var $membershipRadio = $this,
				localCurrencyDetails =
					$membershipRadio.data("urm-local-currency-details") || {},
				membershipType = $membershipRadio.data("urm-pg-type"),
				totalDetails = {},
				taxAmount = 0,
				$localCurrencyEl = $("#ur-local-currency-switch-currency"),
				currency = $localCurrencyEl.val(),
				currencySymbols =
					ur_membership_frontend_localized_data.local_currencies_symbol,
				symbol = register_events.decodeHtmlEntity(
					currencySymbols[currency] || ""
				),
				discount_amount =
					typeof discount_amount !== "undefined"
						? parseInt(discount_amount)
						: 0;

			totalDetails.total = total;
			totalDetails.taxAmount = 0;
			totalDetails.subTotal = subTotal;
			totalDetails.discountAmount = discount_amount;

			if (membershipType !== "free") {
				if (
					localCurrencyDetails[currency] &&
					localCurrencyDetails[currency].hasOwnProperty("ID")
				) {
					$localCurrencyEl
						.data("urm-zone-id", localCurrencyDetails[currency].ID)
						.attr(
							"data-urm-zone-id",
							localCurrencyDetails[currency].ID
						);
				}
				var $span = $membershipRadio.siblings(
					".ur-membership-period-span"
				);

				var oldText = $span.text();
				var parts = oldText.split("/");
				var durationPart = parts[1] ? "/ " + parts[1].trim() : "";

				if (localCurrencyDetails[currency]) {
					var newCalculatedValue =
						total * parseFloat(localCurrencyDetails[currency].rate);
					var newSubTotal =
						subTotal *
						parseFloat(localCurrencyDetails[currency].rate);
					var newDiscount =
						discount_amount *
						parseFloat(localCurrencyDetails[currency].rate);

					if (
						"manual" ==
						localCurrencyDetails[currency].pricing_method
					) {
						newCalculatedValue = parseFloat(
							localCurrencyDetails[currency].rate
						);
						newSubTotal = newCalculatedValue;
					}

					if (urmf_data.curreny_pos === "left") {
						$span.text(
							symbol + newSubTotal.toFixed(2) + " " + durationPart
						);
					} else {
						$span.text(
							newSubTotal.toFixed(2) + symbol + " " + durationPart
						);
					}

					$membershipRadio.data(
						"urm-converted-amount",
						newCalculatedValue
					);
					$membershipRadio
						.data("local-currency", currency)
						.attr("data-local-currency", currency);
					if ($membershipRadio.is(":checked")) {
						taxAmount =
							ur_membership_ajax_utils.calculate_tax_amount(
								newCalculatedValue
							);
						totalDetails.total =
							parseFloat(newCalculatedValue) +
							parseFloat(taxAmount);

						totalDetails.taxAmount = taxAmount;
						totalDetails.subTotal = newSubTotal;
						totalDetails.discountAmount = newDiscount;
					}
				} else {
					$membershipRadio.data("urm-converted-amount", 0);
					if (urmf_data.curreny_pos === "left") {
						if (subTotal) {
							$span.text(
								urmf_data.currency_symbol +
									subTotal.toFixed(2) +
									" " +
									durationPart
							);
						}
						if ($membershipRadio.is(":checked")) {
							taxAmount =
								ur_membership_ajax_utils.calculate_tax_amount(
									total
								);

							totalDetails.total =
								parseFloat(total) + parseFloat(taxAmount);

							totalDetails.taxAmount = taxAmount;
							totalDetails.subTotal = total;
						}
					} else {
						$span.text(
							subTotal.toFixed(2) +
								urmf_data.currency_symbol +
								" " +
								durationPart
						);
						if ($membershipRadio.is(":checked")) {
							taxAmount =
								ur_membership_ajax_utils.calculate_tax_amount(
									total
								);
							totalDetails.total =
								parseFloat(total) + parseFloat(taxAmount);

							totalDetails.taxAmount = taxAmount;
							totalDetails.subTotal = total;
						}
					}
				}
			}

			return totalDetails;
		},
		calculate_tax_amount: function (total) {
			var countryField = $(document).find(".ur-field-address-country");
			var stateField = $(document).find(".ur-field-address-state");
			var taxRate = 0;
			var taxAmount = 0;

			if (countryField.length) {
				var country = countryField.val() || "";
				var state = stateField.val() || "";

				var regions = null;

				if (
					typeof urmf_data !== "undefined" &&
					urmf_data.regions_list &&
					urmf_data.regions_list.regions &&
					country &&
					urmf_data.regions_list.regions.hasOwnProperty(country)
				) {
					regions = urmf_data.regions_list.regions[country];
				}

				var tax_calculation_method = urmf_data.tax_calculation_method;
				var total_input = $("#ur-membership-total");

				if (urmf_data.is_tax_calculation_enabled) {
					if (regions) {
						if (regions.hasOwnProperty("states") && "" !== state) {
							if (regions.states.hasOwnProperty(state)) {
								taxRate = regions.states[state];
							} else {
								taxRate = regions.rate;
							}
						} else {
							taxRate = regions.rate;
						}

						if (taxRate > 0) {
							if (tax_calculation_method) {
								taxAmount = (total * taxRate) / 100;
								total =
									parseFloat(total) + parseFloat(taxAmount);
							} else {
								// Price include tax
							}
						}
					}

					$("#ur-tax-details").remove();

					var taxDetailsInput =
						'<input type="hidden" ' +
						'id="ur-tax-details" ' +
						'name="ur_tax_details" ' +
						'data-tax-rate="' +
						taxRate +
						'" ' +
						'data-tax-calculation-method="' +
						tax_calculation_method +
						'" ' +
						'data-total="' +
						total +
						'">';

					total_input.after(taxDetailsInput);
				}
			}
			$(".urm-membership-tax-value")
				.find(".ur_membership_input_label")
				.text(taxRate + "% Tax");

			return taxAmount;
		},
		add_multiple_membership: function (
			data,
			selected_membership_id,
			selected_pg,
			btn,
			type
		) {
			//handle differently in case of Authorize.NET
			//gets the nonce token from ANET and send it via the AJAX request.
			if ("authorize" === selected_pg) {
				this.handle_authorize_multiple_purchase(
					data,
					selected_membership_id,
					selected_pg,
					btn,
					type
				);
			} else {
				this.send_data(
					{
						_wpnonce: urmf_data.upgrade_membership_nonce,
						action: "user_registration_membership_add_multiple_membership",
						selected_membership_id: selected_membership_id,
						selected_pg: selected_pg,
						form_data: data.form_data,
						coupon: data.coupon,
						form_id: data.form_id,
						type: type
					},
					{
						success: function (response) {
							if (response.success) {
								ur_membership_frontend_utils.show_success_message(
									response.data.message
								);
								var prepare_members_data = {
									payment_method: selected_pg,
									username: response.data.username
								};

								ur_membership_ajax_utils.handle_update_response(
									response,
									prepare_members_data
								);
							} else {
								ur_membership_frontend_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_frontend_utils.show_failure_message(
								user_registration_params.network_error +
									"(" +
									statusText +
									")"
							);
						},
						complete: function () {
							if (selected_pg !== "stripe") {
								ur_membership_frontend_utils.remove_spinner(
									btn
								);
							}
							ur_membership_frontend_utils.toggleSaveButtons(
								false,
								btn
							);
						}
					}
				);
			}
		},
		renew_membership: function (
			data,
			selected_pg,
			btn,
			membership_id,
			team_id
		) {
			this.send_data(
				{
					_wpnonce: urmf_data.renew_membership_nonce,
					action: "user_registration_membership_renew_membership",
					form_data: data.form_data,
					selected_pg: selected_pg,
					membership_id: membership_id,
					form_id: data.form_id,
					coupon: data.coupon
				},
				{
					success: function (response) {
						if (response.success) {
							ur_membership_frontend_utils.show_success_message(
								response.data.message
							);
							var prepare_members_data = {
								payment_method: selected_pg,
								username: response.data.username
							};

							ur_membership_ajax_utils.handle_renewal_response(
								response,
								prepare_members_data
							);
						} else {
							ur_membership_frontend_utils.show_failure_message(
								response.data.message
							);
						}
					},
					failure: function (xhr, statusText) {
						ur_membership_frontend_utils.show_failure_message(
							user_registration_params.network_error +
								"(" +
								statusText +
								")"
						);
					},
					complete: function () {
						ur_membership_frontend_utils.toggleSaveButtons(
							false,
							btn
						);
					}
				}
			);
		},
		handle_authorize_upgrade: function (
			submittedData,
			current_plan,
			selected_membership_id,
			current_subscription_id,
			selected_pg,
			btn
		) {
			var data = {
				current_plan: current_plan,
				selected_membership_id: selected_membership_id,
				current_subscription_id: current_subscription_id,
				selected_pg: selected_pg,
				btn: btn,
				form_data: submittedData.form_data,
				coupon: submittedData.coupon
			};

			$(document).trigger("urm_before_upgrade_membership_submit", {
				data: data,
				onComplete: function (data) {
					ur_membership_ajax_utils.send_data(
						{
							_wpnonce: urmf_data.upgrade_membership_nonce,
							action: "user_registration_membership_upgrade_membership",
							current_membership_id: data.current_plan,
							selected_membership_id: data.selected_membership_id,
							current_subscription_id:
								data.current_subscription_id,
							selected_pg: data.selected_pg,
							ur_authorize_data: data.ur_authorize_data,
							form_data: submittedData.form_data,
							coupon: submittedData.coupon
						},
						{
							success: function (response) {
								if (response.success) {
									ur_membership_frontend_utils.show_success_message(
										response.data.message
									);
									var prepare_members_data = {
										payment_method: selected_pg,
										username: response.data.username
									};

									ur_membership_ajax_utils.handle_update_response(
										response,
										prepare_members_data
									);
								} else {
									ur_membership_frontend_utils.show_failure_message(
										response.data.message
									);
									$(document)
										.find(".swal2-confirm")
										.find("span")
										.removeClass("urm-spinner");
								}
							},
							failure: function (xhr, statusText) {
								ur_membership_frontend_utils.show_failure_message(
									user_registration_params.network_error +
										"(" +
										statusText +
										")"
								);
								$(document)
									.find(".swal2-confirm")
									.find("span")
									.removeClass("urm-spinner");
							},
							complete: function () {
								ur_membership_frontend_utils.toggleSaveButtons(
									false,
									btn
								);
							}
						}
					);
				}
			});
		},
		handle_authorize_multiple_purchase: function (
			selected_membership_id,
			selected_pg,
			btn,
			type
		) {
			var data = {
				selected_membership_id: selected_membership_id,
				selected_pg: selected_pg,
				btn: btn,
				type: type
			};

			$(document).trigger("urm_before_multiple_membership_submit", {
				data: data,
				onComplete: function (data) {
					ur_membership_ajax_utils.send_data(
						{
							_wpnonce: urmf_data.upgrade_membership_nonce,
							action: "user_registration_membership_add_multiple_membership",
							selected_membership_id: data.selected_membership_id,
							selected_pg: data.selected_pg,
							ur_authorize_data: data.ur_authorize_data
						},
						{
							success: function (response) {
								if (response.success) {
									ur_membership_frontend_utils.show_success_message(
										response.data.message
									);
									var prepare_members_data = {
										payment_method: selected_pg,
										username: response.data.username
									};

									ur_membership_ajax_utils.handle_update_response(
										response,
										prepare_members_data
									);
								} else {
									ur_membership_frontend_utils.show_failure_message(
										response.data.message
									);
									$(document)
										.find(".swal2-confirm")
										.find("span")
										.removeClass("urm-spinner");
								}
							},
							failure: function (xhr, statusText) {
								ur_membership_frontend_utils.show_failure_message(
									user_registration_params.network_error +
										"(" +
										statusText +
										")"
								);
								$(document)
									.find(".swal2-confirm")
									.find("span")
									.removeClass("urm-spinner");
							},
							complete: function () {
								ur_membership_frontend_utils.toggleSaveButtons(
									false,
									btn
								);
							}
						}
					);
				}
			});
		},

		authorize_net_container_html: function () {
			return (
				"" +
				'<div id="authorize-net-container" class="urm-d-none membership-only authorize-net-container">' +
				'<div data-field-id="authorizenet_gateway" class="ur-field-item field-authorize_net_gateway" data-ref-id="authorizenet_gateway" data-field-pattern-enabled="0" data-field-pattern-value=" " data-field-pattern-message=" ">' +
				'<div class="form-row" id="authorizenet_gateway_field"><label class="ur-label" for="Authorize.net">Authorize.net <abbr class="required" title="required">*</abbr></label><p></p>' +
				'<div id="user_registration_authorize_net_gateway" data-gateway="authorize_net" class="input-text" conditional_rules="">' +
				'<div class="ur-field-row">' +
				'<div class="user-registration-authorize-net-card-number">' +
				'<input type="text" id="user_registration_authorize_net_card_number" name="user_registration_authorize_net_card_number" maxlength="16" placeholder="411111111111111" class="widefat ur-anet-sub-field user_registration_authorize_net_card_number"><br>' +
				'<label class="user-registration-sub-label">Card Number</label></div>' +
				"</div>" +
				'<div class="ur-field-row clearfix">' +
				'<div class="user-registration-authorize-net-expiration user-registration-one-half">' +
				'<div class="user-registration-authorize-net-expiration-month user-registration-one-half"><select class="widefat ur-anet-sub-field user_registration_authorize_net_expiration_month" id="user_registration_authorize_net_expiration_month" name="user_registration_authorize_net_expiration_month"><option> MM </option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option></select><label class="user-registration-sub-label">Expiration</label></div>' +
				'<div class="user-registration-authorize-net-expiration-year user-registration-one-half last"><select class="widefat ur-anet-sub-field user_registration_authorize_net_expiration_year" id="user_registration_authorize_net_expiration_year" name="user_registration_authorize_net_expiration_year"><option> YY </option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option></select></div>' +
				"</div>" +
				'<div class="user-registration-authorize-net-cvc user-registration-one-half last">' +
				'<input type="text" id="user_registration_authorize_net_card_code" name="user_registration_authorize_net_card_code" placeholder="900" maxlength="4" class="widefat ur-anet-sub-field user_registration_authorize_net_card_code"><br>' +
				'<label class="user-registration-sub-label">CVC</label>' +
				"</div>" +
				"</div>" +
				"</div>" +
				"</div></div></div>"
			);
		},
		prepare_pg_html: function (gateways) {
			var gateway_html = "";
			$.each(gateways, function (index, gateway) {
				var gateway_value = gateway.toLowerCase();
				gateway_html +=
					'<label class="ur_membership_input_label ur-label" for="ur-membership-' +
					gateway_value +
					'">' +
					'<input class="ur_membership_input_class pg-list" ' +
					'data-key-name="ur-payment-method" ' +
					'id="ur-membership-' +
					gateway_value +
					'" ' +
					'type="radio" ' +
					'name="urm_payment_method" ' +
					(index === 0 && gateways && gateways.length === 1
						? "checked"
						: "") +
					" " +
					"required " +
					'value="' +
					gateway_value +
					'">' +
					'<span class="ur-membership-duration">' +
					gateway_value.charAt(0).toUpperCase() +
					gateway_value.slice(1) +
					"</span>" +
					"</label>";
			});
			return gateway_html;
		},
		prepare_renew_membership_html: function (gateways) {
			return (
				'<div class="membership-upgrade-container">' +
				'<div class="ur_membership_registration_container">' +
				'<div class="ur_membership_frontend_input_container urm_hidden_payment_container ur_payment_gateway_container">' +
				'<div id="payment-gateway-body" class="ur_membership_frontend_input_container">' +
				ur_membership_ajax_utils.prepare_pg_html(gateways) +
				'<span id="payment-gateway-notice" class="notice_red"></span>' +
				"</div>" +
				"</div>" +
				'<div class="ur_membership_frontend_input_container">' +
				'<div class="stripe-container urm-d-none">' +
				'<button type="button" class="stripe-card-indicator ur-stripe-element-selected" id="credit_card">Credit Card</button>' +
				'<div class="stripe-input-container"><div id="card-element"></div></div>' +
				"</div>" +
				"</div>" +
				ur_membership_ajax_utils.authorize_net_container_html() +
				"</div>" +
				'<span id="upgrade-membership-notice"></span>' +
				"</div>"
			);
		},
		prepare_upgrade_membership_html: function (data) {
			var membership_title = $("#membership-title").text() || "";
			var options_html = "",
				gateways = urmf_data.membership_gateways || [];

			//plans html
			$.each(data, function (key, membership) {
				var id = membership.ID || "",
					title = membership.title || "",
					type = membership.type || "",
					period = membership.period || "",
					calculated_amount = membership.calculated_amount || "",
					amount = membership.amount || "",
					active_pg = membership.active_payment_gateways || "{}";

				options_html +=
					'<label class="upgrade-membership-label" for="ur-membership-select-membership-' +
					id +
					'">' +
					'<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field" ' +
					'id="ur-membership-select-membership-' +
					id +
					'" ' +
					'type="radio" ' +
					'name="urm_membership" ' +
					'data-label="' +
					title +
					'" ' +
					'required="required" ' +
					'value="' +
					id +
					'" ' +
					"data-urm-pg='" +
					active_pg +
					"' " +
					'data-urm-pg-type="' +
					type +
					'" ' +
					'data-urm-pg-calculated-amount="' +
					calculated_amount +
					'">' +
					'<span class="ur-membership-duration">' +
					title +
					"</span>" +
					'<span class="ur-membership-duration"> - ' +
					period +
					"</span>" +
					"</label>";
			});

			return (
				'<div class="membership-upgrade-container">' +
				"<span>Your current Plan is <b>" +
				membership_title +
				"</b></span>" +
				'<div class="upgrade-plan-container">' +
				'<span class="ur-upgrade-label">Select Plan</span>' +
				'<div id="upgradable-plans">' +
				options_html +
				"</div>" +
				"</div>" +
				'<div class="ur_membership_registration_container urm-d-none">' +
				'<div class="ur_membership_frontend_input_container urm_hidden_payment_container ur_payment_gateway_container urm-d-none">' +
				'<span class="ur-upgrade-label ur-label required">Select Payment Gateway</span>' +
				'<div id="payment-gateway-body" class="ur_membership_frontend_input_container">' +
				ur_membership_ajax_utils.prepare_pg_html(gateways) +
				'<span id="payment-gateway-notice" class="notice_red"></span>' +
				"</div>" +
				"</div>" +
				'<div class="ur_membership_frontend_input_container">' +
				'<div class="stripe-container urm-d-none">' +
				'<button type="button" class="stripe-card-indicator ur-stripe-element-selected" id="credit_card">Credit Card</button>' +
				'<div class="stripe-input-container"><div id="card-element"></div></div>' +
				"</div>" +
				"</div>" +
				ur_membership_ajax_utils.authorize_net_container_html() +
				"</div>" +
				"</div>"
			);
		},
		prepare_intended_membership_purchase_html: function (membership) {
			var membership_title = $("#membership-title").text() || "";
			var options_html = "",
				gateways = urmf_data.membership_gateways || [];

			//plans html
			var id = membership.ID || "",
				title = membership.title || "",
				type = membership.type || "",
				period = membership.period || "",
				calculated_amount = membership.calculated_amount || "",
				amount = membership.amount || "",
				active_pg = membership.active_payment_gateways || "{}";

			options_html +=
				'<label class="upgrade-membership-label" for="ur-membership-select-membership-' +
				id +
				'">' +
				'<input class="ur_membership_input_class ur_membership_radio_input ur-frontend-field" ' +
				'id="ur-membership-select-membership-' +
				id +
				'" ' +
				'type="radio" ' +
				'name="urm_membership" ' +
				'data-label="' +
				title +
				'" ' +
				'required="required" ' +
				'value="' +
				id +
				'" ' +
				"data-urm-pg='" +
				active_pg +
				"' " +
				'data-urm-pg-type="' +
				type +
				'" ' +
				'data-urm-pg-calculated-amount="' +
				calculated_amount +
				'">' +
				'<span class="ur-membership-duration">' +
				title +
				"</span>" +
				'<span class="ur-membership-duration"> - ' +
				period +
				"</span>" +
				"</label>";

			return (
				'<div class="membership-upgrade-container">' +
				"<span>Your current Plan is <b>" +
				membership_title +
				"</b></span>" +
				'<div class="upgrade-plan-container">' +
				'<span class="ur-upgrade-label">Select Plan</span>' +
				'<div id="upgradable-plans">' +
				options_html +
				"</div>" +
				"</div>" +
				'<div class="ur_membership_registration_container urm-d-none">' +
				'<div class="ur_membership_frontend_input_container urm_hidden_payment_container ur_payment_gateway_container urm-d-none">' +
				'<span class="ur-upgrade-label ur-label required">Select Payment Gateway</span>' +
				'<div id="payment-gateway-body" class="ur_membership_frontend_input_container">' +
				ur_membership_ajax_utils.prepare_pg_html(gateways) +
				'<span id="payment-gateway-notice" class="notice_red"></span>' +
				"</div>" +
				"</div>" +
				'<div class="ur_membership_frontend_input_container">' +
				'<div class="stripe-container urm-d-none">' +
				'<button type="button" class="stripe-card-indicator ur-stripe-element-selected" id="credit_card">Credit Card</button>' +
				'<div class="stripe-input-container"><div id="card-element"></div></div>' +
				"</div>" +
				"</div>" +
				ur_membership_ajax_utils.authorize_net_container_html() +
				"</div>" +
				"</div>"
			);
		},
		/**
		 * Handles the response based on the payment method selected.
		 *
		 * @param {Object} response - The response data from the server.
		 * @param {Object} prepare_members_data - The data for preparing members.
		 */
		handle_update_response: function (response, prepare_members_data) {
			switch (prepare_members_data.payment_method) {
				case "paypal":
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					window.location.replace(response.data.pg_data.payment_url);
					break;
				case "stripe":
					stripe_settings.handle_stripe_response(
						response,
						prepare_members_data,
						{ data: {} }
					);
					break;
				case "mollie":
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					window.location.replace(response.data.pg_data.payment_url);
					break;
				case "authorize":
					window.location.replace(response.data.redirect);
					break;
				case "free":
					var cleanUrl =
						window.location.origin + window.location.pathname;

					window.location.replace(urmf_data.thank_you_page_url);

				default:
					ur_membership_ajax_utils.show_bank_response(
						response,
						{
							username: prepare_members_data.username,
							payment_method: prepare_members_data.payment_method
						},
						{
							data: {}
						}
					);
					break;
			}
		},
		handle_renewal_response: function (response, prepare_members_data) {
			switch (prepare_members_data.payment_method) {
				case "paypal":
					ur_membership_frontend_utils.show_success_message(
						response.data.message
					);
					window.location.replace(response.data.pg_data.payment_url);
					break;
				case "stripe":
					stripe_settings.handle_stripe_response(
						response,
						prepare_members_data,
						{ data: {} }
					);
					break;
				default:
					ur_membership_ajax_utils.show_bank_response(
						response,
						{
							username: prepare_members_data.username,
							payment_method: prepare_members_data.payment_method
						},
						{
							data: {}
						}
					);
					break;
			}
		},
		cancel_delayed_subscription: function (btn) {
			ur_membership_frontend_utils.toggleSaveButtons(true, btn);
			ur_membership_frontend_utils.append_spinner(btn);

			this.send_data(
				{
					_wpnonce: urmf_data.upgrade_membership_nonce,
					action: "user_registration_membership_cancel_upcoming_subscription"
				},
				{
					success: function (response) {
						if (response.success) {
							Swal.close();
							ur_membership_frontend_utils.show_success_message(
								response.data.message
							);
						} else {
							ur_membership_frontend_utils.show_failure_message(
								response.data.message
							);
						}
					},
					failure: function (xhr, statusText) {
						ur_membership_frontend_utils.show_failure_message(
							urmf_data.labels.network_error +
								" (" +
								xhr.statusText +
								")"
						);
					},
					complete: function () {
						ur_membership_frontend_utils.remove_spinner(btn);
						ur_membership_frontend_utils.toggleSaveButtons(
							false,
							btn
						);
					}
				}
			);
		}
	};
	var form_object = {
		hide_loader: function (form_id) {
			var $registration_form = $('#user-registration-form-' + form_id);
			$registration_form
				.find('.ur-submit-button')
				.find('span')
				.removeClass('ur-front-spinner');
			$registration_form
				.find('form')
				.find('.ur-submit-button')
				.prop('disabled', false);
		},
	};
	var stripe_settings = {
		show_stripe_error: function (message) {
			if ($membership_registration_form.find('#stripe-errors').length > 0) {
				$membership_registration_form
					.find('#stripe-errors')
					.html(message)
					.show();
			} else {
				var error_message =
					'<label id="stripe-errors" class="user-registration-error" role="alert">' +
					message +
					'</label>';
				$membership_registration_form
					.find('.stripe-container')
					.closest('.ur_membership_frontend_input_container')
					.append(error_message);
			}
		},
		init: function (is_upgrading) {
			elements = stripe_settings.setupElements();
			$membership_registration_form = is_upgrading
				? $('.membership-upgrade-container')
				: $('#ur-membership-registration');
			this.triggerInputChange();
		},
		triggerInputChange: function () {
			elements.card.addEventListener('change', function (e) {
				if (e.error) {
					stripe_settings.show_stripe_error(e.error.message);
				} else {
					if ($membership_registration_form.find('#stripe-errors').length > 0) {
						$membership_registration_form.find('#stripe-errors').remove();
					}
				}
			});
		},
		setupElements: function () {
			var stripe = Stripe(urmf_data.stripe_publishable_key), //take this from global variable
				elements = stripe.elements();

			var style = {
				base: {
					color: '#32325d',
					fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
					fontSmoothing: 'antialiased',
					fontSize: '14px',
					'::placeholder': {
						color: '#8f9194',
					},
				},
				invalid: {
					color: '#fa755a',
					iconColor: '#fa755a',
				},
			};

			var card = elements.create('card', {
				style: style,
				hidePostalCode:
					urmf_data.urm_hide_stripe_card_postal_code == '1' ? true : false,
			});
			var idealBank = elements.create('idealBank', { style: style });

			card.mount('#card-element');
			return {
				stripe: stripe,
				card: card,
				ideal: idealBank,
				clientSecret: '',
			};
		},

		handle_stripe_response: function (
			response,
			prepare_members_data,
			form_response,
		) {
			ur_membership_frontend_utils.show_payment_processing_overlay();

			if (response.data.pg_data.type === 'paid') {
				this.handle_one_time_payment(
					response,
					prepare_members_data,
					form_response,
				);
			} else {
				this.handle_recurring_payment(response, {
					paymentElements: elements,
					user_id: response.data.member_id,
					response_data: response,
					prepare_members_data: prepare_members_data,
					form_response: form_response,
					team_id: response.data.team_id ? response.data.team_id : ''
				})
					.then(function () {
						ur_membership_frontend_utils.show_success_message(
							response.data.message,
						);
						form_object.hide_loader(form_response.form_id);
					})
					.catch(function () {
						form_object.hide_loader(form_response.form_id);
					});
			}
		},

		handle_one_time_payment: function (
			response,
			prepare_members_data,
			form_response,
		) {
			ur_membership_frontend_utils.show_payment_processing_overlay();

			return elements.stripe
				.confirmCardPayment(response.data.pg_data.client_secret, {
					payment_method: {
						card: elements.card,
					},
				})
				.then(function (result) {
					var button = $('.membership_register_button');
					ur_membership_frontend_utils.toggleSaveButtons(true, button);
					ur_membership_frontend_utils.append_spinner(button);
					stripe_settings.update_order_status(
						result,
						response,
						prepare_members_data,
						form_response,
					);
					ur_membership_frontend_utils.hide_payment_processing_overlay();
				});
		},
		update_order_status: function (
			result,
			response,
			prepare_members_data,
			form_response,
		) {
			ur_membership_ajax_utils.send_data(
				{
					_wpnonce: urmf_data._confirm_payment_nonce,
					action: 'user_registration_membership_confirm_payment',
					members_data: JSON.stringify(prepare_members_data),
					member_id: response.data.member_id,
					payment_status: result.error ? 'failed' : 'succeeded',
					form_response: JSON.stringify(form_response.data),
					payment_result: result,
					selected_membership_id: response.data.selected_membership_id
						? response.data.selected_membership_id
						: '',
					current_membership_id: response.data.current_membership_id
						? response.data.current_membership_id
						: '',
					team_id: response.data.team_id? response.data.team_id : ''
				},
				{
					success: function (response) {
						if (response.success) {
							if (
								response.data.is_upgrading ||
								response.data.is_renewing ||
								response.data.is_purchasing_multiple
							) {
								var thank_you_data = {
									username: prepare_members_data.username,
									message: response.data.message,
								};
								if (response.data.is_upgrading) {
									thank_you_data.is_upgrading = response.data.is_upgrading;
								} else if (response.data.is_renewing) {
									thank_you_data.is_renewing = response.data.is_renewing;
								}
								ur_membership_ajax_utils.show_default_response(
									window.location.href,
									thank_you_data,
								);
							} else {
								ur_membership_ajax_utils.show_default_response(
									urmf_data.thank_you_page_url,
									{
										username: prepare_members_data.username,
										transaction_id:
											result.paymentIntent && result.paymentIntent.id
												? result.paymentIntent.id
												: result.id,
									},
								);
							}
							//first show successful toast
						} else {
							ur_membership_frontend_utils.hide_payment_processing_overlay();
							stripe_settings.show_stripe_error(response.data.message);
							form_object.hide_loader(form_response.form_id);
						}
					},
					failure: function (xhr, statusText) {
						ur_membership_frontend_utils.hide_payment_processing_overlay();
						ur_membership_frontend_utils.show_failure_message(
							urmf_data.labels.i18n_error +
								'(' +
								xhr.responseJSON.data.message +
								')',
						);
					},
					complete: function () {
						var swal_btn = $('.swal2-confirm ');
						form_object.hide_loader(form_response.form_id);
						ur_membership_frontend_utils.toggleSaveButtons(false, swal_btn);
						ur_membership_frontend_utils.remove_spinner(swal_btn);
					},
				},
			);
		},
		handle_recurring_payment: function (response, data) {
			return Promise.resolve(
				$.extend({}, data, {
					customer_id: response.data.pg_data.stripe_cus_id,
					selected_membership_id: response.data.selected_membership_id
						? response.data.selected_membership_id
						: '',
					current_membership_id: response.data.current_membership_id
						? response.data.current_membership_id
						: '',
				}),
			)
				.then(stripe_settings.createPaymentMethod)
				.then(stripe_settings.createSubscription)
				.then(stripe_settings.handleCustomerActionRequired)
				.then(stripe_settings.handleOnComplete)
				.catch(function (message, error) {
					ur_membership_frontend_utils.hide_payment_processing_overlay();
					stripe_settings.update_order_status(
						{ error: {} },
						response,
						data.prepare_members_data,
						data.form_response,
					);
					return Promise.reject(error);
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
						type: 'card',
						card: data.paymentElements.card,
					})
					.then(function (result) {
						if (result.error) {
							reject(result.error.message, result); // Reject the promise with the error
						} else {
							resolve(
								$.extend({}, data, {
									payment_method_id: result.paymentMethod.id,
								}),
							);
						}
					})
					.catch(function (error) {
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
				ur_membership_ajax_utils.send_data(
					{
						_wpnonce: urmf_data._confirm_payment_nonce,
						action: 'user_registration_membership_create_stripe_subscription',
						member_id: data.user_id,
						customer_id: data.customer_id,
						payment_method_id: data.payment_method_id,
						form_response: JSON.stringify(data.form_response.data),
						selected_membership_id: data.selected_membership_id
							? data.selected_membership_id
							: '',
						current_membership_id: data.current_membership_id
							? data.current_membership_id
							: '',
						team_id: data.team_id ? data.team_id : ''
					},
					{
						success: function (response) {
							if (response.success) {
								var paymentIntent =
									response.data.subscription.latest_invoice.payment_intent;

								if (response.error) {
									var message = response.error.message;
									reject(message, data);
									return;
								}

								if ('trialing' !== response.data.subscription.status) {
									if (
										paymentIntent &&
										'requires_payment_method' === paymentIntent.status
									) {
										var message = 'Your card was declined';
										reject(response, message);
									}
								}

								resolve(
									$.extend({}, data, {
										subscription: response.data.subscription,
										message: response.data.message,
									}),
								);
							} else {
								form_object.hide_loader(data.form_id);
								reject(response, message);
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_frontend_utils.show_failure_message(
								urmf_data.labels.network_error + '(' + statusText + ')',
							);
						},
					},
				);
			});
		},

		/**
		 * Handle customer action if it is required.
		 *
		 * @param {object} data Contains stripe, card, formid, paymentItems, form_selector, customerId, paymentMethodId and subscription.
		 *
		 */
		handleCustomerActionRequired: function (data) {
			ur_membership_frontend_utils.show_payment_processing_overlay();

			return new Promise(function (resolve, reject) {
				if (
					data.subscription &&
					(data.subscription.status === 'active' ||
						data.subscription.status === 'trialing')
				) {
					resolve({
						subscription: data.subscription,
						response_data: data.response_data,
						message: data.message,
						prepare_members_data: data.prepare_members_data,
						form_response: data.form_response,
					});
					ur_membership_frontend_utils.hide_payment_processing_overlay();
				}

				var paymentIntent = data.subscription.latest_invoice.payment_intent;

				if ('trialing' !== data.subscription.status) {
					if ('requires_action' === paymentIntent.status) {
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

								if ('succeeded' === result.paymentIntent.status) {
									data.subscription.status = 'active';
									resolve({
										subscription: data.subscription,
										form_id: data.form_response.form_id,
										response_data: data.response_data,
										prepare_members_data: data.prepare_members_data,
										form_response: data.form_response,
										three_d_secure: true,
									});
								} else {
									var message = 'Unable to complete the payment.';
									reject(message, data);
								}
								ur_membership_frontend_utils.hide_payment_processing_overlay();
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
			var is_upgrading =
					data.response_data.data.is_upgrading !== undefined
						? data.response_data.data.is_upgrading
						: false,
				is_renewing =
					data.response_data.data.is_renewing !== undefined
						? data.response_data.data.is_renewing
						: false,
				is_purchasing_multiple =
					data.response_data.data.is_purchasing_multiple !== undefined
						? data.response_data.data.is_purchasing_multiple
						: false,
				is_three_d_secure =
					undefined !== data.three_d_secure ? data.three_d_secure : false;

			if (
				is_upgrading ||
				is_renewing ||
				is_purchasing_multiple ||
				is_three_d_secure
			) {
				stripe_settings.update_order_status(
					data.subscription,
					data.response_data,
					data.prepare_members_data,
					data.form_response,
				);
			}

			if (
				data.subscription &&
				(data.subscription.status === 'active' ||
					data.subscription.status === 'trialing') &&
				!is_upgrading &&
				!is_renewing &&
				!is_purchasing_multiple &&
				!data.three_d_secure
			) {
				ur_membership_frontend_utils.show_form_success_message(
					data.form_response,
					{
						username: data.prepare_members_data.username,
						transaction_id: data.subscription.id,
					},
				);
			}
			return { success: true };
		},
	};
	var register_events = {
		init: function () {
			$('input[name="urm_payment_method"]').on('change', function () {
				var selected_method = $(this).val(),
					stripe_container = $('.stripe-container'),
					stripe_error_container = $('#stripe-errors');

				// register_events.validateSwitchCurrency( selected_method );

				var authorize_container = $('.authorize-net-container');
				var authorize_error_container = $('#authorize-errors');

				stripe_container.addClass('urm-d-none');
				stripe_error_container.remove();

				authorize_container.addClass('urm-d-none');
				authorize_error_container.remove();

				elements = {};
				if (selected_method === 'stripe') {
					if (urmf_data.stripe_publishable_key.length == 0) {
						ur_membership_frontend_utils.show_failure_message(
							urmf_data.labels.i18n_incomplete_stripe_setup_error,
						);
						return;
					}
					stripe_container.removeClass('urm-d-none');
					stripe_settings.init();
				}
				if (selected_method === 'authorize') {
					authorize_container.removeClass('urm-d-none');
				}
			});

			//activate payment gateways

			$(document).on('change', 'input[name="urm_membership"]', function () {
				$('.ur_membership_registration_container').removeClass('urm-d-none');
				// $('#ur-local-currency-switch-currency').trigger('change');
				// $('.ur-field-address-country').trigger('change');
				// clear coupon total notice
				$('#total-input-notice').text('');

				var urm_payment_gateways = $(this).data('urm-pg'),
					urm_payment_type = $(this).data('urm-pg-type'),
					urm_pg_container = $('.ur_payment_gateway_container'),
					urm_pg_inputs = urm_pg_container.find('input'),
					urm_hidden_pg_containers = $('.urm_hidden_payment_container'),
					stripe_container = $('.stripe-container'),
					stripe_error_container = $('#stripe-errors'),
					upgrade_error_container = $('#upgrade-membership-notice'),
					urm_default_pg = $(this).data('urm-default-pg'),
					hasCouponLink = $(this).data('has-coupon-link');
				if ('yes' === hasCouponLink) {
					$(document).find('#ur_coupon_container').show();
				} else {
					$(document).find('#ur_coupon_container').hide();
				}

				var authorize_container = $('.authorize-net-container');
				var authorize_error_container = $('#authorize-errors');

				authorize_error_container.remove();

				stripe_error_container.remove();
				upgrade_error_container.text('');

				//Selects a default payment gateway. Needs to be updated for translation.
				if (urm_default_pg && urm_default_pg.toLowerCase() === urm_default_pg) {
					$(this)
						.closest('#ur-membership-registration')
						.find('#ur-membership-' + urm_default_pg)
						.prop('checked', true)
						.trigger('change');

					if (urm_default_pg.toLowerCase() === 'stripe') {
						stripe_settings.init();
					}

					if (urm_default_pg.toLowerCase() !== 'authorize') {
						authorize_container.addClass('urm-d-none');
					}
				} else {
					$('input[name="urm_payment_method"]').prop('checked', false);
					stripe_container.addClass('urm-d-none');
				}

				urm_hidden_pg_containers.addClass('urm-d-none');

				$('.urm_apply_coupon').show();
				if (urm_payment_type !== 'free') {
					if (
						urmf_data.gateways_configured &&
						Object.keys(urmf_data.gateways_configured).length > 0
					) {
						urm_hidden_pg_containers.removeClass('urm-d-none');

						urm_pg_inputs.each(function (key, item) {
							var current_gateway = $(item).val(),
								input_container = $(
									'label[for="ur-membership-' + current_gateway + '"]',
								);
							input_container.removeClass('urm-d-none');

							if (!(current_gateway in urmf_data.gateways_configured)) {
								input_container.addClass('urm-d-none');
							}
						});
					}
					urm_pg_inputs.each(function (key, item) {
						var current_gateway = $(item).val(),
							input_container = $(
								'label[for="ur-membership-' + current_gateway + '"]',
							);
						if (urmf_data.gateways_configured) {
						}
						if (!urm_payment_gateways.hasOwnProperty(current_gateway)) {
							input_container.addClass('urm-d-none');
						}
					});

					if (urm_pg_container.find('input:visible').length === 1) {
						var lone_pg = urm_pg_container.find('input:visible');
						$(lone_pg[0]).prop('checked', true);
						lone_pg.trigger('change');
					}
					ur_membership_ajax_utils.calculate_total($(this));
				} else {
					stripe_container.addClass('urm-d-none');
				}
			});
			// membership input change trigger for page with membership id as params.
			var searchParams = new URLSearchParams(window.location.search),
				visible_memberships = $('input[name="urm_membership"]');

			$(document).on(
				'user_registration_membership_update_before_form_submit',
				function (e, data) {
					e.preventDefault();

					var has_error = false,
						selected_pg = 'free',
						selected_plan = '';
					var pg_type = $('input[name="urm_membership"]:checked').data(
							'urm-pg-type',
						),
						btn = $(this);
					//validation before request start
					selected_plan = $('input[name="urm_membership"]:checked').val();
					selected_pg =
						$('input[name="urm_payment_method"]:checked').val() === undefined
							? selected_pg
							: $('input[name="urm_payment_method"]:checked').val();

					//validation end
					var action = searchParams.get('action'),
						current_membership_id = searchParams.get('current'),
						subscription_id = searchParams.get('subscription_id');

					if ($('#ur-membership-coupon').length > 0) {
						data.coupon = $('#ur-membership-coupon').val().trim();
					}

					if (action == 'multiple') {
						ur_membership_ajax_utils.add_multiple_membership(
							data,
							selected_plan,
							selected_pg,
							btn,
							'multiple',
						);
					} else if (action == 'upgrade') {
						if ($('#ur-membership-coupon').length > 0) {
							data.coupon = $('#ur-membership-coupon').val().trim();
						}

						if (!subscription_id && !current_membership_id) {
							subscription_id = $('.urm_membership_upgrade_data').data(
								'current-subscription-id',
							);
							current_membership_id = $('.urm_membership_upgrade_data').data(
								'current-membership-id',
							);
						}

						ur_membership_ajax_utils.upgrade_membership(
							data,
							current_membership_id,
							selected_plan,
							subscription_id,
							selected_pg,
							btn,
						);
					} else if (action == 'register') {
						ur_membership_ajax_utils.add_multiple_membership(
							data,
							selected_plan,
							selected_pg,
							btn,
							'register',
						);
					} else if (action === 'renew') {
						var team_id = btn.data('team-id');

						ur_membership_ajax_utils.renew_membership(
							data,
							selected_pg,
							btn,
							current_membership_id,
							team_id,
						);
					}
				},
			);

			if (searchParams.has('membership_id')) {
				$('input[name="urm_membership"]:checked').change();
			}
			if (
				visible_memberships !== undefined &&
				visible_memberships.length === 1
			) {
				$(visible_memberships[0]).prop('checked', true).change();
			}
			$('.close_notice').on('click', ur_membership_frontend_utils.toggleNotice);

			$('#ur-membership-password').on('keyup change', function () {
				var $this = $(this),
					password = $this.val(),
					notice_div = $('#password-notice'),
					pass_regex = new RegExp(
						'^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*()_+}{"\':;?/>.<,]).*$',
					);
				ur_membership_frontend_utils.show_validation_error(notice_div, '');

				if (password.length < 8) {
					ur_membership_frontend_utils.show_validation_error(
						notice_div,
						urmf_data.labels.i18n_field_password_field_length_validation,
					);
					return;
				}
				if (!pass_regex.test(password)) {
					ur_membership_frontend_utils.show_validation_error(
						notice_div,
						urmf_data.labels.i18n_field_password_field_regex_validation,
					);
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
					ur_membership_frontend_utils.show_validation_error(
						notice_div,
						urmf_data.labels.i18n_field_password_empty_validation,
					);
				}
				if (confirm_password !== password) {
					ur_membership_frontend_utils.show_validation_error(
						notice_div,
						urmf_data.labels.i18n_field_confirm_password_field_validation,
					);
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
				selected_membership
					.removeData('ur-discount-amount')
					.removeAttr('data-ur-discount-amount');
				ur_membership_ajax_utils.calculate_total(selected_membership);
			});
			//redirect to membership member registration form
			$(document).on(
				'click',
				'.membership-selection-form .membership-signup-button',
				function (e) {
					e.preventDefault();

					if (urmf_data.isEditor) {
						return;
					}

					var $this = $(this),
						membership_id = $this
							.siblings('input[name="membership_id"]')
							.attr('value'),
						redirection_url = $this
							.siblings('input[name="redirection_url"]')
							.val(),
						thank_you_page_id = $this
							.siblings('input[name="thank_you_page_id"]')
							.val(),
						uuid = $this.siblings('input[name="urm_uuid"]').val(),
						action = $this.siblings('input[name="action"]').val();

					ur_membership_frontend_utils.clear_validation_error();

					if (
						$this
							.closest('.membership-selection-form')
							.find('.ur_membership_frontend_input_container')
							.hasClass('radio')
					) {
						var selected = $('input[name="membership_id"]:checked');

						if (selected.length > 0) {
							membership_id = selected.val();
							redirection_url = selected.data('redirect');
							thank_you_page_id = selected.data('thankyou');
							uuid = selected.data('urm-uuid');
							action = selected.data('action');
						} else {
							var error_div = $this
								.closest('.membership-selection-form')
								.find('#urm-listing-error');

							error_div
								.parent()
								.css('position', 'static')
								.css('margin-bottom', '10px');

							ur_membership_frontend_utils.show_validation_error(
								error_div,
								urmf_data.labels.i18n_error +
									'! ' +
									urmf_data.membership_selection_message,
							);
							return;
						}
					}

					var concatenator = redirection_url.indexOf('?') === -1 ? '?' : '&';

					var url =
						redirection_url +
						concatenator +
						'membership_id=' +
						membership_id +
						'&action=' +
						action +
						'&thank_you=' +
						thank_you_page_id;

					if (action === 'register') {
						url += '&uuid=' + uuid;
					}

					if ($(this).attr('target') === '_blank') {
						window.open(url, '_blank');
						return;
					}

					window.location.replace(url);
				},
			);

			//validate before submit
			$(document).on(
				'user_registration_frontend_validate_before_form_submit',
				function () {
					ur_membership_ajax_utils.validate_membership_form();
				},
			);
			$(document).on(
				'user_registration_frontend_before_form_submit',
				function (event, data, pointer, $error_message) {
					if ($(pointer).find('#ur-membership-registration').length > 0) {
						data['is_membership_active'] = $(pointer)
							.find('input[name="urm_membership"]:checked')
							.val();
						data['membership_type'] = $(
							'input[name="urm_membership"]:checked',
						).val();
					}
				},
			);
			$(document).on(
				'user_registration_frontend_before_ajax_complete_success_message',
				function (event, ajax_response, ajaxFlag, form) {
					var flag = true,
						response = JSON.parse(ajax_response.responseText),
						required_data = {
							data: response.data,
							form_id: $(form).data('form-id'),
						};

					if (
						typeof response.data.registration_type !== 'undefined' &&
						response.data.registration_type === 'membership'
					) {
						flag = false;
						ur_membership_ajax_utils.create_member(required_data);
					}
					ajaxFlag['status'] = flag;
				},
			);

			$(document).on(
				'change',
				'.membership-upgrade-container input[name="urm_payment_method"]',
				function () {
					var selected_method = $(this).val(),
						stripe_container = $('.stripe-container'),
						stripe_error_container = $('#stripe-errors'),
						upgrade_error_container = $('#upgrade-membership-notice');

					var authorize_container = $('.authorize-net-container');
					var authorize_error_container = $('#authorize-errors');

					upgrade_error_container.text('');
					stripe_container.addClass('urm-d-none');
					stripe_error_container.remove();

					authorize_container.addClass('urm-d-none');
					authorize_error_container.remove();

					elements = {};
					if (selected_method === 'stripe') {
						if (urmf_data.stripe_publishable_key.length == 0) {
							ur_membership_frontend_utils.show_failure_message(
								urmf_data.labels.i18n_incomplete_stripe_setup_error,
							);
							return;
						}
						stripe_container.removeClass('urm-d-none');
						stripe_settings.init(true);
					}
					if (selected_method === 'authorize') {
						authorize_container.removeClass('urm-d-none');
					}
				},
			);
			//cancel membership button
			$(document).on('click', '.cancel-membership-button', function (e) {
				e.preventDefault();
				var $this = $(this),
					error_div = $('#membership-error-div'),
					button_text = $this.text(),
					membership_title = $('#membership-title').text();

				Swal.fire({
					icon: 'warning',
					title:
						urmf_data.labels.i18n_cancel_membership_text +
						' ' +
						membership_title.trim(),
					text: urmf_data.labels.i18n_cancel_membership_subtitle,
					customClass: 'user-registration-upgrade-membership-swal2-container',
					showConfirmButton: true,
					showCancelButton: true,
				}).then(function (result) {
					if (result.isConfirmed) {
						$.ajax({
							url: urmf_data.ajax_url,
							type: 'POST',
							data: {
								action: 'user_registration_membership_cancel_subscription',
								security: urmf_data._nonce,
								subscription_id: $this.data('id'),
							},
							beforeSend: function () {
								$this.text(urmf_data.labels.i18n_sending_text);
							},
							success: function (response) {
								if (response.success) {
									if (error_div.hasClass('btn-error')) {
										error_div.removeClass('btn-error');
										error_div.addClass('btn-success');
									}
									error_div.text(response.data.message);
									error_div.show();
									location.reload();
								} else {
									if (error_div.hasClass('btn-success')) {
										error_div.removeClass('btn-success');
										error_div.addClass('btn-error');
									}
									error_div.text(response.data.message);
									error_div.show();
								}
							},
							complete: function () {
								$this.text(button_text);
							},
						});
					}
				});
			});
			$(document).on('click', '.reactivate-membership-button', function (e) {
				e.preventDefault();

				var $this = $(this),
					error_div = $('#membership-error-div'),
					button_text = $this.text(),
					membership_title = $('#membership-title').text();
				$.ajax({
					url: urmf_data.ajax_url,
					type: 'POST',
					data: {
						action: 'user_registration_membership_reactivate_membership',
						security: urmf_data._nonce,
						subscription_id: $this.data('id'),
					},
					beforeSend: function () {
						$this.text(urmf_data.labels.i18n_sending_text);
					},
					success: function (response) {
						if (!response.success) {
							if ($('.user-registration-page .notice-container').length === 0) {
								$('.user-registration-membership-notice__container').remove();
								// Adds the toast container on the top of page.
								$(document)
									.find('.user-registration-page')
									.prepend(
										'<div class="user-registration-membership-notice__container"><div class="ur-toaster urm-error user-registration-membership-notice__red"><span class="user-registration-membership-notice__message"></span><span class="user-registration-membership__close_notice">&times;</span></div></div>',
									);
							}
							$(document).trigger('urm_show_action_message', {
								message: response.data.message,
								type: response.success ? 'success' : 'error',
							});
						} else {
							location.reload();
						}
					},
					complete: function () {
						$this.text(button_text);
					},
				});
			});

			$('#membership-error-div .cancel-notice').on('click', function () {
				$(this).siblings('span').text('');
				$(this).closest('#membership-error-div').hide();
			});
			//disable submit button if empty membership field
			if ($('.field-membership').length) {
				$('.field-membership').each(function (key, item) {
					if ($(item).find('.no-membership')) {
						var form_id = $(item).find('.no-membership').attr('data-form-id');
						$('#user-registration-form-' + form_id)
							.find('.ur-submit-button')
							.prop('disabled', true);
					}
				});
			}

			$(document).on('click', '.view-bank-data', function (e) {
				e.preventDefault();
				e.stopPropagation();

				jQuery('.user-registration-help-tip.tooltipstered').tooltipster(
					'close',
				);

				var html =
					jQuery(this)
						.closest('.tooltipster-box')
						.find('.upgrade-info')
						.html() || jQuery(this).siblings('.upgrade-info').html();

				Swal.fire({
					title: urmf_data.labels.i18n_bank_details_title,
					html: html,
					customClass: 'user-registration-upgrade-membership-swal2-container',
					showCancelButton: false,
					showConfirmButton: false,
				});
			});

			$(document).on(
				'change',
				'#ur-local-currency-switch-currency',
				function (e) {
					e.preventDefault();

					var $el = $(this),
						currency = $el.val(),
						urmMembership = $('input[name="urm_membership"]'),
						currencySymbols =
							ur_membership_frontend_localized_data.local_currencies_symbol,
						symbol = register_events.decodeHtmlEntity(
							currencySymbols[currency] || '',
						);

					urmMembership.each(function () {
						var $membershipRadio = $(this),
							localCurrencyDetails =
								$membershipRadio.data('urm-local-currency-details') || {},
							calculatedAmount =
								parseFloat($membershipRadio.data('urm-pg-calculated-amount')) ||
								0,
							discountAmount =
								parseFloat($membershipRadio.data('ur-discount-amount')) || 0,
							total = discountAmount
								? calculatedAmount - discountAmount
								: calculatedAmount,
							membershipType = $membershipRadio.data('urm-pg-type');

						if (membershipType !== 'free') {
							if (
								localCurrencyDetails[currency] &&
								localCurrencyDetails[currency].hasOwnProperty('ID')
							) {
								$el
									.data('urm-zone-id', localCurrencyDetails[currency].ID)
									.attr('data-urm-zone-id', localCurrencyDetails[currency].ID);
							}
							var $span = $membershipRadio.siblings(
								'.ur-membership-period-span',
							);

							var oldText = $span.text();
							var parts = oldText.split('/');
							var durationPart = parts[1] ? '/ ' + parts[1].trim() : '';

							if (localCurrencyDetails[currency]) {
								var newCalculatedValue =
									total * parseFloat(localCurrencyDetails[currency].rate);
								newCalculatedValue = newCalculatedValue.toFixed(2);

								if ('manual' == localCurrencyDetails[currency].pricing_method) {
									newCalculatedValue = localCurrencyDetails[currency].rate;
								}

								if (urmf_data.curreny_pos === 'left') {
									$span.text(symbol + newCalculatedValue + ' ' + durationPart);
								} else {
									$span.text(newCalculatedValue + symbol + ' ' + durationPart);
								}

								$membershipRadio.data(
									'urm-converted-amount',
									newCalculatedValue,
								);
								$membershipRadio
									.data('local-currency', currency)
									.attr('data-local-currency', currency);
								if ($membershipRadio.is(':checked')) {
									ur_membership_ajax_utils.calculate_total($membershipRadio);
								}
							} else {
								$membershipRadio.data('urm-converted-amount', 0);
								total = total.toFixed(2);
								if (urmf_data.curreny_pos === 'left') {
									$span.text(
										urmf_data.currency_symbol + total + ' ' + durationPart,
									);
									if ($membershipRadio.is(':checked')) {
										ur_membership_ajax_utils.calculate_total($membershipRadio);
									}
								} else {
									$span.text(
										total + urmf_data.currency_symbol + ' ' + durationPart,
									);
									if ($membershipRadio.is(':checked')) {
										ur_membership_ajax_utils.calculate_total($membershipRadio);
									}
								}
							}
						}
					});
				},
			);

			$(document).on(
				'change',
				'.ur-field-address-country, .ur-field-address-state',
				function (e) {
					e.stopPropagation();
					e.preventDefault();
					var membership_input = $('input[name="urm_membership"]:checked');
					ur_membership_ajax_utils.calculate_total(membership_input);
				},
			);

			$('.ur-label.ur-has-team-pricing').on('click', function (e) {
				var label = $(this);
				var membership_id = $(this).data('membership-id');
				var teamContainer = $('#urm-team-pricing-container-' + membership_id);
				var radio = teamContainer.find('input[type="radio"]');

				$(this).hide();
				teamContainer.show();
				radio.prop('checked', true).trigger('change');

				label.hide();
				teamContainer.show();

				// Hide all other team containers and show their regular labels
				$('.urm-team-pricing-container').not(teamContainer).hide();
				$('.ur-label.ur-has-team-pricing').not(label).show();

				e.preventDefault();
			});

			$('.ur-label.ur-normal-pricing').on('click', function () {
				var label = $(this);
				var radio = label.find('input[type="radio"]');

				radio.prop('checked', true).trigger('change');

				// Hide all team containers and show their regular labels
				$('.urm-team-pricing-container').hide();
				$('.ur-label.ur-has-team-pricing').show();
			});

			$('.ur-team-pricing-label').on('click', function (e) {
				var label = $(this);
				var radio = label.find('input[type="radio"]');

				radio.prop('checked', true).trigger('change');

				e.preventDefault();
			});

			$('.ur-membership-price').on('click', function () {
				$(this).addClass('ur-membership-price-selected');
				var container = $(this).closest('.urm-team-pricing-card');
				var radio = container.find('input[type="radio"]');
				container.find('.urm-team-pricing-tier').removeClass('selected');
				container.find('.ur-team-tier-seats-wrapper').hide();
				radio.data('urm-pg-calculated-amount', $(this).data('price'));
				ur_membership_ajax_utils.calculate_total(radio);
				$('.ur-membership-payment-gateway-lists label[for="ur-membership-mollie"]').show();
				$('.ur-membership-payment-gateway-lists label[for="ur-membership-authorize"]').show();
				$('.ur-team-tier-seats-tier').hide();
			});

			function calculatePrice(tier) {
				var radio = tier.closest(".urm-team-pricing-card")
					.find('input[name="urm_membership"]');

				var seatInput = tier.find('input[name="no_of_seats"]');
				var seats = parseInt(seatInput.val(), 10) || 0;

				var seatModel = tier.data("seat-model");
				var pricingModel = tier.data("pricing-model");

				var amount = 0;

				if (seatModel === "fixed") {
					amount = tier.data("fixed-price") || 0;
				}

				if (seatModel === "variable" && pricingModel === "per_seat") {
					var perSeat = tier.data("per-seat-price") || 0;
					amount = seats * perSeat;
				}

				if (seatModel === "variable" && pricingModel === "tier") {
					var selectedTierRadio = tier.find(".ur-tier-radio-input:checked");
					if (!selectedTierRadio.length) return;

					var tierPrice = parseFloat(selectedTierRadio.data("tier-price")) || 0;
					amount = seats * tierPrice;
				}

				radio.data("urm-pg-calculated-amount", amount);
				ur_membership_ajax_utils.calculate_total(radio);
			}

			$(document).on("click", ".urm-team-pricing-tier", function (e) {
				if ($(e.target).is(".ur-tier-radio-input") || $(e.target).is("input[name='no_of_seats']")) return;

    			var tier = $(this);
				var container = tier.closest(".urm-team-pricing-card");
				var singlePrice = container.find(
					".ur-membership-price-selected"
				);
				$(".urm-team-pricing-tier").removeClass("selected");
				$(".ur-team-tier-seats-wrapper").hide();
				$(".ur-team-tier-seats-tier").hide();
				$('.ur-membership-payment-gateway-lists label[for="ur-membership-mollie"]').hide();
				$('.ur-membership-payment-gateway-lists label[for="ur-membership-authorize"]').hide();

				tier.addClass("selected");
				singlePrice.removeClass("ur-membership-price-selected");
				$(".ur-tier-radio-input").prop("checked", false);
				tier.find(".ur-tier-radio-input").prop("disabled", false);
    			$(".ur-tier-radio-input").not(tier.find(".ur-tier-radio-input")).prop("disabled", true);

				var seatModel = tier.data("seat-model");
				var pricingModel = tier.data("pricing-model");
				var seatInput = tier.find('input[name="no_of_seats"]');

				$('input[name="no_of_seats"]')
					.not(seatInput)
					.each(function () {
						var defaultValue =
							$(this).attr('min') || $(this).attr('placeholder') || '';
						$(this).val(defaultValue);
						$(this).prop('disabled', true);
						$(this).removeClass('ur-input-border-red');
						$(this).removeAttr('aria-invalid');
					});
				// validation fix
				$('#no_of_seats-error').remove();

				if (seatModel === "variable" && pricingModel === "per_seat") {
					tier.find(".ur-team-tier-seats-wrapper").show();
					seatInput.prop("disabled", false).val(seatInput.attr("min") || 1);
					calculatePrice(tier);
				}

				if (seatModel === "fixed") {
					calculatePrice(tier);
				}

				if (seatModel === "variable" && pricingModel === "tier") {
					var tier_range_wrapper = tier.find('.ur-team-tier-seats-tier');
					tier_range_wrapper.show();
					tier.find(".ur-team-tier-seats-wrapper").hide();
					seatInput.prop("disabled", true);
				}
			});

			$(document).on(
				'input',
				'.urm-team-pricing-tier.selected input[name="no_of_seats"]',
				function () {
					calculatePrice($(this).closest(".urm-team-pricing-tier"));
				}
			);

			$(document).on("change", ".ur-tier-radio-input", function (e) {
				var radio = $(this);
    			var tier = radio.closest(".urm-team-pricing-tier");
				if (!tier.hasClass('selected')) {
					e.preventDefault();
					e.stopImmediatePropagation();
					radio.prop("checked", false);
					return;
				}

				var seatInput = tier.find('input[name="no_of_seats"]');
				var from = parseInt(radio.data("tier-from"), 10);
				var to = parseInt(radio.data("tier-to"), 10);

				tier.attr("data-selected-tier", radio.val());
				$(".ur-team-tier-seats-wrapper").hide();
    			tier.find(".ur-team-tier-seats-wrapper").show();
				seatInput.attr({ min: from, max: to }).val(from).prop("disabled", false);
				seatInput.trigger('input');
				$('input[name="no_of_seats"]').not(seatInput).prop("disabled", true);
			});
		},
		validateSwitchCurrency: function (paymentMethod) {
			var $select = $('#ur-local-currency-switch-currency');

			if (!urmf_data.supported_currencies.hasOwnProperty(paymentMethod)) {
				$select.find('option').show();
				$select.val($select.find('option:first').val()).trigger('change');
				return;
			}

			var supportedCurrencies = urmf_data.supported_currencies[paymentMethod];

			$select.find('option').each(function () {
				var currency = $(this).val();

				if (supportedCurrencies.indexOf(currency) === -1) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});

			var firstVisible = $select.find('option:visible:first').val();
			$select.val(firstVisible).trigger('change');
		},
		decodeHtmlEntity: function (str) {
			var txt = document.createElement('textarea');
			txt.innerHTML = str;
			return txt.value;
		},
	};
	register_events.init();
	$(document).ready(function () {
		$('#ur-local-currency-switch-currency').trigger('change');
	});
})(jQuery, window.ur_membership_frontend_localized_data);
