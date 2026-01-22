/*global console, Swal*/
(function ($, ur_membership_data) {
	if (UR_Snackbar) {
		var snackbar = new UR_Snackbar();
	}
	var basic_error = false,
		advanced_error = false;
	$(".user-membership-enhanced-select2").select2();

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
			disable = ur_membership_utils.if_empty(disable, true);
			$(".ur-membership-save-btn").prop("disabled", !!disable);
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

		/**
		 * convert value and duration to timestamp
		 * @param value
		 * @param duration
		 * @returns {null|number}
		 */
		convert_to_timestamp: function (value, duration) {
			var multiplier;
			switch (duration) {
				case "day":
					multiplier = 24 * 60 * 60 * 1000;
					break;
				case "week":
					multiplier = 7 * 24 * 60 * 60 * 1000;
					break;
				case "month":
					multiplier = 30 * 24 * 60 * 60 * 1000;
					break;
				case "year":
					multiplier = 365 * 24 * 60 * 60 * 1000;
					break;
				default:
					return null;
			}
			return new Date().getTime() + value * multiplier;
		},

		//regular required validation
		regular_validation: function (inputs, no_errors, from) {
			inputs.every(function (item) {
				var $this = $(item),
					value = $this.val(),
					is_required = $this.attr("required"),
					type = $this.attr("type"),
					name = $this.data("key-name");
				$this.removeClass("ur-membership-error");
				if (is_required && value === "") {
					no_errors = false;
					if ("form" === from) {
						basic_error = true;
					} else if ("paypal" === from) {
						advanced_error = true;
					}
					var message =
						("paypal" === from
							? ur_membership_data.labels.i18n_paypal
							: "") +
						ur_membership_data.labels.i18n_error +
						"! " +
						name +
						" " +
						ur_membership_data.labels.i18n_field_is_required +
						" " +
						("paypal" === from
							? ur_membership_data.labels.i18n_paypal_setup_error
							: "");
					ur_membership_utils.show_failure_message(message);
					$this.addClass("ur-membership-error");
					return false;
				} else if (type === "url") {
					if (!ur_membership_utils.url_validations(value)) {
						no_errors = false;
						if ("form" === from) {
							basic_error = true;
						} else if ("paypal" === from) {
							advanced_error = true;
						}
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_error +
								"! " +
								name +
								" " +
								ur_membership_data.labels
									.i18n_valid_url_field_validation +
								" " +
								name
						);
						$this.addClass("ur-membership-error");
						return false;
					}
				}
				return true;
			});
			return no_errors;
		},

		url_validations: function (url) {
			var regex = new RegExp(
				"^https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$"
			);
			return regex.test(url);
		},

		handle_bulk_delete_action: function (form) {
			Swal.fire({
				title:
					'<img src="' +
					ur_membership_data.delete_icon +
					'" id="delete-user-icon">' +
					ur_membership_data.labels.i18n_prompt_title,
				html:
					'<p id="html_1">' +
					ur_membership_data.labels.i18n_prompt_bulk_subtitle +
					"</p>",
				showCancelButton: true,
				confirmButtonText: ur_membership_data.labels.i18n_prompt_delete,
				cancelButtonText: ur_membership_data.labels.i18n_prompt_cancel,
				allowOutsideClick: false
			}).then(function (result) {
				if (result.isConfirmed) {
					var selected_memberships = form.find(
							'input[name="membership[]"]:checked'
						),
						membership_ids = [];

					if (selected_memberships.length < 1) {
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels
								.i18n_prompt_no_membership_selected
						);
						return;
					}
					//prepare orders data
					selected_memberships.each(function () {
						if ($(this).val() !== "") {
							membership_ids.push($(this).val());
						}
					});

					//send request
					ur_membership_request_utils.send_data(
						{
							action: "user_registration_membership_delete_memberships",
							membership_ids: JSON.stringify(membership_ids)
						},
						{
							success: function (response) {
								if (response.success) {
									ur_membership_utils.show_success_message(
										response.data.message
									);
									ur_membership_request_utils.remove_deleted_memberships(
										selected_memberships,
										true
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
										"(" +
										statusText +
										")"
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
				form = $("#ur-membership-create-form"),
				description = tinyMCE
					.get("ur-input-type-membership-description")
					.getContent(),
				regex = /(<img[^>]*?)(")([^>]*?>)/g;

			description = description.replace(
				regex,
				function (match, p1, p2, p3) {
					return p1 + "'" + p3.replace(/"/g, "'");
				}
			);

			post_data = {
				name: form.find("#ur-input-type-membership-name").val(),
				description: description,
				status: $("#ur-membership-status").prop("checked")
			};
			if (ur_membership_data.membership_id) {
				post_data.ID = ur_membership_data.membership_id;
			}
			post_meta_data.type = form
				.find('input[name="ur_membership_type"]:checked')
				.val();
			post_meta_data.cancel_subscription = form
				.find('input[name="ur_membership_cancel_on"]:checked')
				.val();

			var syncActionEl = form.find(
				'input[name="ur_membership_email_marketing_sync_action"]:checked'
			);

			post_meta_data.email_marketing_sync = {};
			var email_marketing_sync = {};
			post_meta_data.email_marketing_sync.is_enable = syncActionEl.val();

			if (syncActionEl.length && syncActionEl.val()) {
				var marketingAddonsList = [
					"activecampaign",
					"brevo",
					"convertkit",
					"klaviyo",
					"mailchimp",
					"mailerlite",
					"mailpoet",
					"salesforce"
				];
				marketingAddonsList.forEach(function (val) {
					if (!email_marketing_sync[val]) {
						email_marketing_sync[val] = {};
					}
					var checkbox = form.find(
						'input[name="sync_membership_plan_with_' + val + '"]'
					);

					if (checkbox.length && checkbox.is(":checked")) {
						email_marketing_sync[val].email_marketing_sync =
							checkbox.is(":checked");

						var accountSelect = form.find(
							"#ur_sync_email_marketing_" + val + "_account"
						);

						if (accountSelect.length) {
							email_marketing_sync[val].email_marketing_account =
								accountSelect.val();
						}

						var listSelect = form.find(
							"#ur_sync_email_marketing_" +
								val +
								"_integration_list_id"
						);

						if (listSelect.length) {
							email_marketing_sync[val].integration_list_id =
								listSelect.val();
						}

						if ("mailchimp" === val) {
							var tagSelect = form.find(
								"#ur_sync_email_marketing_mailchimp_tag_id"
							);

							if (tagSelect.length) {
								email_marketing_sync[val].list_tags =
									tagSelect.val();
							}
						}
					}
				});
			}

			post_meta_data.email_marketing_sync.addons_sync_details =
				email_marketing_sync;

			post_meta_data.role = form
				.find("#ur-input-type-membership-role")
				.find(":selected")
				.val();
			if (post_meta_data.type !== "free") {
				if (post_meta_data.type !== "paid") {
					post_meta_data.subscription = {
						value: form.find("#ur-membership-duration-value").val(),
						duration: form.find("#ur-membership-duration").val()
					};
					post_meta_data.trial_status = form
						.find("#ur-membership-trial-status")
						.val();
					if (post_meta_data.trial_status === "on") {
						post_meta_data.trial_data = {
							value: form
								.find("#ur-membership-trial-duration-value")
								.val(),
							duration: form
								.find("#ur-membership-trial-duration")
								.val()
						};
					}
					post_meta_data.cancel_subscription = form
						.find('input[name="ur_membership_cancel_on"]:checked')
						.val();
				}
				post_meta_data.amount = form
					.find("#ur-membership-amount")
					.val();
				var is_paypal_selected = form
						.find("#ur-membership-pg-paypal:checked")
						.val(),
					is_bank_selected = form
						.find("#ur-membership-pg-bank:checked")
						.val(),
					is_stripe_selected = form
						.find("#ur-membership-pg-stripe:checked")
						.val();

				var is_authorize_selected = form
					.find("#ur-membership-pg-authorize:checked")
					.val();
				var is_mollie_selected = form
					.find("#ur-membership-pg-mollie:checked")
					.val();

				//since all the pgs have different params , they must be handled differently.
				post_meta_data.payment_gateways = {
					paypal: {
						status: "off"
					}, //paypal section
					stripe: {
						status: "off"
					}, // stripe section
					bank: {
						status: "off"
					}, //direct bank transfer section
					authorize: {
						status: "off"
					},
					mollie: {
						status: "off"
					}
				};

				//check if paypal is selected
				if (is_paypal_selected) {
					post_meta_data.payment_gateways.paypal = {
						status: is_paypal_selected,
						email: form.find("#ur-input-type-paypal-email").val(),
						mode: form.find("#ur-membership-paypal-mode").val(),
						payment_type: form
							.find("#ur-membership-paypal-payment-type")
							.val(),
						cancel_url: form
							.find("#ur-input-type-cancel-url")
							.val(),
						return_url: form.find("#ur-input-type-return-url").val()
					};
					if (post_meta_data.type === "subscription") {
						post_meta_data.payment_gateways.paypal.client_id = form
							.find("#ur-input-type-client-id")
							.val();
						post_meta_data.payment_gateways.paypal.client_secret =
							form.find("#ur-input-type-client-secret").val();
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
					};
				}

					// team pricing
				var is_team_pricing_enabled = form
					.find('#ur-membership-team-pricing')
					.is(':checked');
				if (is_team_pricing_enabled) {
					post_meta_data.team_pricing = [];

					$('#ur-team-pricing-container .ur-team-pricing-wrapper').each(
						function () {
							var teamWrapper = $(this);
							var seatModel = teamWrapper
								.find("input[name^='ur_seat_model']:checked")
								.val();
							var planType = teamWrapper
								.find("input[name^='ur_team_plan_type']:checked")
								.val();
							var durationValue = teamWrapper
								.find("input[name^='ur_team_duration_value']")
								.val();

							var durationPeriod = teamWrapper
								.find("select[name^='ur_team_duration_period']")
								.val();

							var teamName = teamWrapper
								.find("input[name^='ur_team_name']")
								.val();

							var teamData = {
								seat_model: seatModel,
								team_name: teamName,
								team_plan_type: planType,
								team_duration_value: durationValue,
								team_duration_period: durationPeriod,
							};

							if (seatModel === 'fixed') {
								teamData.team_size = teamWrapper
									.find("input[name^='ur_team_size']")
									.val();
								teamData.team_price = teamWrapper
									.find("input[name^='ur_team_pricing']")
									.val();
							} else {
								teamData.minimum_seats = teamWrapper
									.find("input[name^='ur_minimum_seats']")
									.val();
								teamData.maximum_seats = teamWrapper
									.find("input[name^='ur_maximum_seats']")
									.val();
								var pricingModel = teamWrapper
									.find("input[name^='ur_pricing_model']:checked")
									.val();
								teamData.pricing_model = pricingModel;

								if (pricingModel === 'per_seat') {
									teamData.per_seat_price = teamWrapper
										.find("input[name^='ur_per_seat_pricing']")
										.val();
								} else {
									var tiers = [];
									var tierWrappers = teamWrapper.find(
										'.ur-team-tier-field-wrapper',
									);
									tierWrappers.each(function () {
										var tierWrapper = $(this);
										var tierData = {};
										tierData.tier_from = tierWrapper
											.find("input[name^='ur_tier_from']")
											.val();
										tierData.tier_to = tierWrapper
											.find("input[name^='ur_tier_to']")
											.val();
										tierData.tier_per_seat_price = tierWrapper
											.find("input[name^='ur_tier_per_seat_price']")
											.val();
										tiers.push(tierData);
									});
									teamData.tiers = tiers;
								}
							}

							post_meta_data.team_pricing.push(teamData);
						},
					);
				}
			}

			/**
			 * Save local currency details.
			 *
			 * @since 5.0.0
			 */
			var localCurrencyEl = form.find(
					'input[name="ur_membership_local_currency"]:checked'
				);
			post_meta_data.local_currency = {};
			post_meta_data.local_currency.is_enable = localCurrencyEl.val();

			if (localCurrencyEl.length && localCurrencyEl.val()) {

				post_meta_data.local_currency.zones = {};

				form.find('.ur-local-currency-card').each(function () {
					var $card = $(this);
					var zoneId = $card.data('zone-id');

					var isEnabled = $card
						.find('.ur-local-currency-toggle-input')
						.is(':checked') ? 1 : 0;

					var pricingMethod = $card
						.find('.ur-local-currency-radio-group input[type="radio"]:checked')
						.val();

					var manualPrice = '';

					if (pricingMethod === 'manual') {
						manualPrice = $card
							.find('.local-currency-manual-local-price')
							.val();
					}

					post_meta_data.local_currency.zones[zoneId] = {
						enable: isEnabled,
						pricing_method: pricingMethod,
						manual_price: manualPrice
					};
				});
			}

			//upgrade settings

			post_meta_data.upgrade_settings = {
				upgrade_action: form
					.find("#ur-membership-upgrade-action")
					.is(":checked"),
				upgrade_path: form
					.find("#ur-input-type-membership-upgrade-path")
					.val(),
				upgrade_type: form
					.find(".urm-upgrade-path-type-container")
					.find('input[name="ur_membership_upgrade_type"]:checked')
					.val()
			};
			return {
				post_data: post_data,
				post_meta_data: post_meta_data
			};
		},
		/**
		 * validate membership form before submit
		 * @returns {boolean}
		 */
		validate_membership_form: function () {
			basic_error = false;
			advanced_error = false;

			var plan_and_price_section = $(
					"#ur-membership-plan-and-price-section"
				),
				main_fields = $("#ur-membership-main-fields").find("input"),
				form = $("#ur-membership-create-form"),
				upgrade_action = $("#ur-membership-upgrade-action").is(
					":checked"
				),
				team_pricing = $('#ur-membership-team-pricing').is(':checked'),
				no_errors = true;

			var selectedPlanTypeEarly = $("#ur-membership-main-fields")
				.find('input[name="ur_membership_type"]:checked')
				.val();

			if (selectedPlanTypeEarly === "free") {
				$("#ur-membership-amount")
					.prop("required", false)
					.removeAttr("required")
					.removeClass("ur-membership-error");

				main_fields = main_fields.not("#ur-membership-amount");
			}

			main_fields = Object.values(main_fields).reverse().slice(2);

			var result = ur_membership_utils.regular_validation(
				main_fields,
				true,
				"form"
			);
			if (!result) {
				return false;
			}

			var selectedPlanType = selectedPlanTypeEarly,
				amount = $("#ur-membership-main-fields")
					.find("#ur-membership-amount")
					.val();

			var subscription_duration = $("#ur-membership-duration").val(),
				subscription_duration_value = $(
					"#ur-membership-duration-value"
				).val();

			if (
				selectedPlanType === "paid" ||
				selectedPlanType === "subscription"
			) {
				$("#ur-membership-amount").removeClass("ur-membership-error");

				if (amount <= 0) {
					no_errors = false;
					basic_error = true;
					ur_membership_utils.show_failure_message(
						ur_membership_data.labels.i18n_error +
							"! " +
							ur_membership_data.labels
								.i18n_valid_price_field_validation
					);
					$("#ur-membership-amount").addClass("ur-membership-error");
				}

				// team pricing validations
				if (team_pricing) {
					var team_names = $("input[name^='ur_team_name']"),
						seat_models = $("input[name^='ur_seat_model']:checked");

					// team names validation
					team_names.each(function (index) {
						var $this = $(this);
						var teamIndex = index + 1;

						if ($this.val().trim() === '') {
							no_errors = false;
							basic_error = true;
							ur_membership_utils.show_failure_message(
								ur_membership_data.labels.i18n_error +
									'! Team ' +
									teamIndex +
									': ' +
									$this.data('key-name') +
									' ' +
									ur_membership_data.labels.i18n_field_is_required,
							);
							$this.addClass('ur-membership-error');
						} else {
							$this.removeClass('ur-membership-error');
						}
					});

					// seat model validation
					seat_models.each(function (index) {
						var seatModel = $(this);
						var wrapper = seatModel.closest('.ur-team-pricing-wrapper');
						var currentIndex = index + 1;
						// fixed seats validation
						if (seatModel.val() === 'fixed') {
							var team_size = wrapper.find("input[name^='ur_team_size']");
							var team_pricing = wrapper.find(
								"input[name^='ur_team_pricing']",
							);
							var teamSizeVal = parseInt(team_size.val(), 10) || 0;
							var teamPricingVal = parseInt(team_pricing.val(), 10) || 0;
							// team size validation
							if (teamSizeVal <= 0) {
								no_errors = false;
								basic_error = true;
								ur_membership_utils.show_failure_message(
									ur_membership_data.labels.i18n_error +
										'! Team ' +
										currentIndex +
										': ' +
										team_size.data('key-name') +
										' ' +
										ur_membership_data.labels
											.i18n_valid_amount_field_validation,
								);
								team_size.addClass('ur-membership-error');
							} else {
								team_size.removeClass('ur-membership-error');
							}
							// team pricing validation
							if (teamPricingVal <= 0) {
								no_errors = false;
								basic_error = true;
								ur_membership_utils.show_failure_message(
									ur_membership_data.labels.i18n_error +
										'! Team ' +
										currentIndex +
										': ' +
										team_pricing.data('key-name') +
										' ' +
										ur_membership_data.labels
											.i18n_valid_amount_field_validation,
								);
								team_pricing.addClass('ur-membership-error');
							} else {
								team_pricing.removeClass('ur-membership-error');
							}
						}
						// variable seats validation
						else {
							// minimum and maximum seats validation
							var minimum_seat = wrapper.find(
									"input[name^='ur_minimum_seats']",
								),
								maximum_seat = wrapper.find("input[name^='ur_maximum_seats']");

							var minVal = parseInt(minimum_seat.val(), 10) || 0;
							var maxVal = parseInt(maximum_seat.val(), 10) || 0;
							minimum_seat.removeClass('ur-membership-error');
							maximum_seat.removeClass('ur-membership-error');

							if (minVal <= 0) {
								no_errors = false;
								basic_error = true;
								ur_membership_utils.show_failure_message(
									ur_membership_data.labels.i18n_error +
										'! Team ' +
										currentIndex +
										': ' +
										minimum_seat.data('key-name') +
										' ' +
										ur_membership_data.labels
											.i18n_valid_amount_field_validation,
								);
								minimum_seat.addClass('ur-membership-error');
							} else if (maxVal <= 0) {
								no_errors = false;
								basic_error = true;
								ur_membership_utils.show_failure_message(
									ur_membership_data.labels.i18n_error +
										'! Team ' +
										currentIndex +
										': ' +
										maximum_seat.data('key-name') +
										' ' +
										ur_membership_data.labels
											.i18n_valid_amount_field_validation,
								);
								maximum_seat.addClass('ur-membership-error');
							} else if (maxVal <= minVal) {
								no_errors = false;
								basic_error = true;
								ur_membership_utils.show_failure_message(
									ur_membership_data.labels.i18n_error +
										'! Team ' +
										currentIndex +
										': ' +
										maximum_seat.data('key-name') +
										' must be greater than ' +
										minimum_seat.data('key-name'),
								);
								maximum_seat.addClass('ur-membership-error');
							}

							var pricing_model = wrapper.find(
								"input[name^='ur_pricing_model']:checked",
							);
							// pricing model validation
							if (pricing_model.val() === 'per_seat') {
								var per_seat_price = wrapper.find(
									"input[name^='ur_per_seat_pricing']",
								);
								// per seat validation
								if (per_seat_price.val() <= 0) {
									no_errors = false;
									basic_error = true;
									ur_membership_utils.show_failure_message(
										ur_membership_data.labels.i18n_error +
											'! Team ' +
											currentIndex +
											': ' +
											per_seat_price.data('key-name') +
											' ' +
											ur_membership_data.labels
												.i18n_valid_amount_field_validation,
									);
									per_seat_price.addClass('ur-membership-error');
								} else {
									per_seat_price.removeClass('ur-membership-error');
								}
							} else {
								var tierWrappers = wrapper.find('.ur-team-tier-field-wrapper');
								tierWrappers.each(function (tierIndex) {
									var tierWrapper = $(this);
									var currentTierIndex = tierIndex + 1;
									var isFirstTier = tierIndex === 0;
									var isLastTier = tierIndex === tierWrappers.length - 1;

									// from, to and per_seat_price validation
									var tier_from = tierWrapper.find(
											"input[name^='ur_tier_from']",
										),
										tier_to = tierWrapper.find("input[name^='ur_tier_to']"),
										seat_price = tierWrapper.find(
											"input[name^='ur_tier_per_seat_price']",
										);

									var fromVal = parseInt(tier_from.val(), 10) || 0;
									var toVal = parseInt(tier_to.val(), 10) || 0;
									var seatPriceVal = parseInt(seat_price.val(), 10) || 0;
									tier_from.removeClass('ur-membership-error');
									tier_to.removeClass('ur-membership-error');
									seat_price.removeClass('ur-membership-error');

									if (fromVal <= 0) {
										no_errors = false;
										basic_error = true;
										ur_membership_utils.show_failure_message(
											ur_membership_data.labels.i18n_error +
												'! Team ' +
												currentIndex +
												' Tier ' +
												currentTierIndex +
												': ' +
												tier_from.data('key-name') +
												' ' +
												ur_membership_data.labels
													.i18n_valid_amount_field_validation,
										);
										tier_from.addClass('ur-membership-error');
									} else if (toVal <= 0) {
										no_errors = false;
										basic_error = true;
										ur_membership_utils.show_failure_message(
											ur_membership_data.labels.i18n_error +
												'! Team ' +
												currentIndex +
												' Tier ' +
												currentTierIndex +
												': ' +
												tier_to.data('key-name') +
												' ' +
												ur_membership_data.labels
													.i18n_valid_amount_field_validation,
										);
										tier_to.addClass('ur-membership-error');
									} else if (toVal <= fromVal) {
										no_errors = false;
										basic_error = true;
										ur_membership_utils.show_failure_message(
											ur_membership_data.labels.i18n_error +
												'! Team ' +
												currentIndex +
												' Tier ' +
												currentTierIndex +
												': ' +
												tier_to.data('key-name') +
												' must be greater than ' +
												tier_from.data('key-name'),
										);
										tier_to.addClass('ur-membership-error');
									}else if (isFirstTier && fromVal !== minVal) {
										no_errors = false;
										basic_error = true;
										ur_membership_utils.show_failure_message(
											ur_membership_data.labels.i18n_error +
												'! Team ' +
												currentIndex +
												' Tier 1: ' +
												tier_from.data('key-name') +
												' must be equal to ' +
												minimum_seat.data('key-name'),
										);
										tier_from.addClass('ur-membership-error');
									}else if (isLastTier && toVal !== maxVal) {
										no_errors = false;
										basic_error = true;
										ur_membership_utils.show_failure_message(
											ur_membership_data.labels.i18n_error +
												'! Team ' +
												currentIndex +
												' Tier ' +
												currentTierIndex +
												': ' +
												tier_to.data('key-name') +
												' must be equal to ' +
												maximum_seat.data('key-name'),
										);
										tier_to.addClass('ur-membership-error');
									}else if (seatPriceVal <= 0) {
										no_errors = false;
										basic_error = true;
										ur_membership_utils.show_failure_message(
											ur_membership_data.labels.i18n_error +
												'! Team ' +
												currentIndex +
												' Tier ' +
												currentTierIndex +
												': ' +
												seat_price.data('key-name') +
												' ' +
												ur_membership_data.labels
													.i18n_valid_amount_field_validation,
										);
										seat_price.addClass('ur-membership-error');
									}

									if(! no_errors){
										return no_errors;
									}
								});
							}
						}
					});
				}

				var trial_status = $("#ur-membership-trial-status").val();
				if (
					trial_status === "on" &&
					selectedPlanType === "subscription"
				) {
					var trial_duration = $(
							"#ur-membership-trial-duration"
						).val(),
						trial_duration_value = $(
							"#ur-membership-trial-duration-value"
						).val(),
						total_trial_time =
							ur_membership_utils.convert_to_timestamp(
								parseInt(trial_duration_value, 10),
								trial_duration
							),
						total_subscription_time =
							ur_membership_utils.convert_to_timestamp(
								parseInt(subscription_duration_value, 10),
								subscription_duration
							);

					$("#ur-membership-trial-duration-value").removeClass(
						"ur-membership-error"
					);
					$("#ur-membership-trial-duration").removeClass(
						"ur-membership-error"
					);

					if (total_trial_time >= total_subscription_time) {
						no_errors = false;
						advanced_error = true;
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_error +
								"! " +
								ur_membership_data.labels
									.i18n_valid_trial_period_field_validation
						);
						$("#ur-membership-trial-duration-value").addClass(
							"ur-membership-error"
						);
						$("#ur-membership-trial-duration").addClass(
							"ur-membership-error"
						);
					}

					$("#ur-membership-duration-value").removeClass(
						"ur-membership-error"
					);
					$("#ur-membership-trial-duration-value").removeClass(
						"ur-membership-error"
					);

					if (trial_duration_value < 1) {
						no_errors = false;
						advanced_error = true;
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_error +
								"! " +
								ur_membership_data.labels
									.i18n_valid_min_trial_period_field_validation
						);
						$("#ur-membership-trial-duration-value").addClass(
							"ur-membership-error"
						);
					}
				}

				if (selectedPlanType === "subscription") {
					$("#ur-membership-duration-value").removeClass(
						"ur-membership-error"
					);
					$("#ur-membership-duration").removeClass(
						"ur-membership-error"
					);

					if (
						subscription_duration_value === "" ||
						subscription_duration_value === null ||
						typeof subscription_duration_value === "undefined"
					) {
						no_errors = false;
						basic_error = true;

						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_error +
								"! Billing Cycle " +
								ur_membership_data.labels.i18n_field_is_required
						);

						$("#ur-membership-duration-value").addClass(
							"ur-membership-error"
						);
						$("#ur-membership-duration").addClass(
							"ur-membership-error"
						);
					} else if (parseInt(subscription_duration_value, 10) < 1) {
						no_errors = false;
						basic_error = true;

						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.i18n_error +
								"! " +
								ur_membership_data.labels
									.i18n_valid_min_subs_period_field_validation
						);

						$("#ur-membership-duration-value").addClass(
							"ur-membership-error"
						);
					}
				}
			}

			if (upgrade_action) {
				var upgrade_path = $("#ur-input-type-membership-upgrade-path"),
					upgrade_type_container = $(
						".urm-upgrade-path-type-container"
					),
					upgrade_type = upgrade_type_container
						.find(
							'input[name="ur_membership_upgrade_type"]:checked'
						)
						.val();

				$(
					".ur-input-type-membership-upgrade-path .select2-selection--multiple"
				).removeClass("ur-membership-error");

				if (upgrade_path.val().length < 1) {
					no_errors = false;
					advanced_error = true;
					ur_membership_utils.show_failure_message(
						ur_membership_data.labels.i18n_error +
							"! " +
							upgrade_path.data("key-name") +
							" " +
							ur_membership_data.labels.i18n_field_is_required
					);
					$(
						".ur-input-type-membership-upgrade-path .select2-selection--multiple"
					).addClass("ur-membership-error");
				}

				if (upgrade_type === undefined) {
					no_errors = false;
					advanced_error = true;
					ur_membership_utils.show_failure_message(
						ur_membership_data.labels.i18n_error +
							"! " +
							upgrade_type_container.data("key-name") +
							" " +
							ur_membership_data.labels.i18n_field_is_required
					);
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
				// Prepare access rules data before creating membership
				var ruleData = null;
				if (
					typeof window.URCRMembershipAccess !== "undefined" &&
					typeof window.URCRMembershipAccess.prepareRuleData ===
						"function"
				) {
					ruleData = window.URCRMembershipAccess.prepareRuleData();
				}
				var prepare_membership_data = this.prepare_membership_data();

				var ajaxData = {
					action: "user_registration_membership_create_membership",
					membership_data: JSON.stringify(prepare_membership_data)
				};

				// Add rule data to AJAX request if available
				if (ruleData) {
					ajaxData.urcr_membership_access_rule_data =
						JSON.stringify(ruleData);
				}

				this.send_data(ajaxData, {
					success: function (response) {
						if (response.success) {
							ur_membership_data.membership_id =
								response.data.membership_id;
							$this.text(ur_membership_data.labels.i18n_save);
							ur_membership_utils.show_success_message(
								response.data.message
							);
							// var current_url = $(location).attr('href');
							// current_url += '&post_id=' + ur_membership_data.membership_group_id;
							$(location).attr(
								"href",
								ur_membership_data.membership_page_url
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
								"(" +
								statusText +
								")"
						);
					},
					complete: function () {
						ur_membership_utils.remove_spinner($this);
						ur_membership_utils.toggleSaveButtons(false);
					}
				});
			} else {
				if (basic_error) {
					$("#ur-basic-tab").trigger("click");
				} else if (advanced_error) {
					$("#ur-advanced-tab").trigger("click");
				}
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
				// Prepare access rules data before updating membership
				var ruleData = null;
				if (
					typeof window.URCRMembershipAccess !== "undefined" &&
					typeof window.URCRMembershipAccess.prepareRuleData ===
						"function"
				) {
					ruleData = window.URCRMembershipAccess.prepareRuleData();
				}
				var prepare_membership_data = this.prepare_membership_data();

				var ajaxData = {
					action: "user_registration_membership_update_membership",
					membership_data: JSON.stringify(prepare_membership_data),
					membership_id: ur_membership_data.membership_id
				};

				// Add rule data to AJAX request if available
				if (ruleData) {
					ajaxData.urcr_membership_access_rule_data =
						JSON.stringify(ruleData);
				}

				this.send_data(ajaxData, {
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
								"(" +
								statusText +
								")"
						);
					},
					complete: function () {
						ur_membership_utils.remove_spinner($this);
						ur_membership_utils.toggleSaveButtons(false);
					}
				});
			} else {
				if (basic_error) {
					$("#ur-basic-tab").trigger("click");
				} else if (advanced_error) {
					$("#ur-advanced-tab").trigger("click");
				}
				ur_membership_utils.remove_spinner($this);
				ur_membership_utils.toggleSaveButtons(false);
			}
		},

		update_membership_status: function ($this) {
			ur_membership_utils.prepend_spinner($this.parents(".row-actions"));
			$this.attr("disabled", true);
			var status = $this.prop("checked"),
				ID = $this.data("ur-membership-id");
			this.send_data(
				{
					action: "user_registration_membership_update_membership_status",
					membership_data: JSON.stringify({
						status: status,
						ID: ID
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
								"(" +
								statusText +
								")"
						);
					},
					complete: function () {
						//update UI after successful update
						ur_membership_utils.remove_spinner(
							$this.parents(".row-actions")
						);
						$this.attr("disabled", false);
						var state = status ? "Active" : "Inactive",
							status_span = $("#ur-membership-list-status-" + ID);
						status_span.text(state);
						if (state === "Inactive") {
							status_span.removeClass(
								"user-registration-badge--success-subtle"
							);
							status_span.addClass(
								"user-registration-badge--secondary-subtle"
							);
						} else {
							status_span.removeClass(
								"user-registration-badge--secondary-subtle"
							);
							status_span.addClass(
								"user-registration-badge--success-subtle"
							);
						}
					}
				}
			);
		},

		validate_payment_gateway: function ($this) {
			var switch_container = $this.closest(".ur-toggle-section "),
				pg = $this.attr("id").split("ur-membership-pg-")[1],
				membership_type = $(
					"input:radio[name=ur_membership_type]:checked"
				).val();
			ur_membership_utils.prepend_spinner(switch_container);

			this.send_data(
				{
					action: "user_registration_membership_validate_pg",
					pg: pg,
					membership_type: membership_type
				},
				{
					success: function (response) {
						if (!response.status) {
							ur_membership_utils.show_failure_message(
								response.message
							);
							$this.prop("checked", false);
							$this
								.closest(".user-registration-switch")
								.closest(".ur-payment-option-header")
								.siblings(".payment-option-body")
								.show();
						} else {
							$this.prop("checked", true);
							$this
								.closest(".user-registration-switch")
								.closest(".ur-payment-option-header")
								.siblings(".payment-option-body")
								.hide();
						}
					},
					failure: function (xhr, statusText) {
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.network_error +
								"(" +
								statusText +
								")"
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
			if (!data._wpnonce && ur_membership_data) {
				data._wpnonce = ur_membership_data._nonce;
			}
			$.ajax({
				type: "post",
				dataType: "json",
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
		remove_deleted_memberships: function (
			selected_memberships,
			is_multiple
		) {
			if (is_multiple) {
				selected_memberships.each(function () {
					$(this).parents("tr").remove();
				});
			} else {
				$(selected_memberships).parents("tr").remove();
			}
		}
	};

	//toggle event for different payment types
	$(document).on(
		"click",
		"input:radio[name=ur_membership_type]",
		function () {
			var val = $(this).val(),
				plan_container = $("#paid-plan-container"),
				sub_container = $(
					".ur-membership-subscription-field-container"
				),
				pro_rate_settings = $(
					'label.ur-membership-upgrade-types[for="ur-membership-upgrade-type-pro-rata"]'
				),
				membership_duration_period = $("#ur-membership-duration"),
				membership_duration_container_period = $(
					"#ur-membership-duration-container"
				),
				team_pricing_container = $('#ur-membership-team-pricing-container'),
				payment_notice = $("#ur-membership-payment-settings-notice");
			var paidConfigured = payment_notice.data("paid-configured") === 1;
			var subscriptionConfigured =
				payment_notice.data("subscription-configured") === 1;
			plan_container.addClass("ur-d-none");
			pro_rate_settings.addClass("ur-d-none");
			membership_duration_period.addClass("ur-d-none");
			membership_duration_container_period.removeClass("ur-d-flex");
			membership_duration_container_period.addClass("ur-d-none");
			team_pricing_container.removeClass('ur-d-flex');
			team_pricing_container.addClass('ur-d-none');
			payment_notice.addClass("ur-d-none");
			sub_container.show();
			if ("free" !== val) {
				if ("paid" === val) {
					sub_container.hide();
					if (!paidConfigured) {
						payment_notice.removeClass("ur-d-none");
					}
				} else {
					sub_container.removeClass("ur-d-none");
					membership_duration_period.removeClass("ur-d-none");
					membership_duration_container_period.addClass("ur-d-flex");
					membership_duration_container_period.removeClass(
						"ur-d-none"
					);
					if (!subscriptionConfigured) {
						payment_notice.removeClass("ur-d-none");
					}
				}
				pro_rate_settings.removeClass("ur-d-none");
				plan_container.removeClass("ur-d-none");
				team_pricing_container.addClass('ur-d-flex');
				team_pricing_container.removeClass('ur-d-none');
			}else{
				$('input[name="ur_membership_local_currency"]').prop('checked', false);
				$('input[name="ur_membership_local_currency"]').trigger( 'change' );
			}
		}
	);

	$(document).on("click", "#ur-membership-upgrade-action", function () {
		$("#upgrade-settings-container").toggle();
		$("input:radio[name=ur_membership_type]:checked").trigger("click");
	});

	$(document).on("keydown", function (e) {
		if (e.ctrlKey && e.key === "s") {
			e.preventDefault();
			$(".ur-membership-save-btn").trigger("click");
		}
	});
	/**
	 * membership save button event
	 */
	$(".ur-membership-save-btn").on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();

		var $this = $(this);
		if ($(this).find(".ur-spinner.is-active").length) {
			ur_membership_utils.show_failure_message(
				ur_membership_data.labels.i18n_previous_save_action_ongoing
			);
			return;
		}

		if (
			ur_membership_data.membership_id &&
			ur_membership_data.membership_id !== ""
		) {
			ur_membership_request_utils.update_membership($this);
		} else {
			ur_membership_request_utils.create_membership($this);
		}
	});

	//toggle trial section
	$("#ur-membership-trial-status").on("click", function () {
		var isChecked = $(this).prop("checked"),
			trial_container = $(".trial-container");
		$(this).val("on");
		if (!isChecked) {
			$(this).val("off");
		}
		trial_container.toggleClass("ur-d-none");
	});

	//change mmeberhsip status from list
	$(".ur-membership-change-status").on("change", function () {
		ur_membership_request_utils.update_membership_status($(this));
	});

	/**
	 * For toggling payment options.
	 */
	$(document).on("click", ".ur-payment-option-header", function () {
		$(this).find("input").trigger("click");
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
	$(document).on("click", ".pg-switch", function (e) {
		e.stopImmediatePropagation();

		if (
			$(this).is(":checked") &&
			$(this)
				.closest(".user-registration-switch")
				.find(".ur-spinner.is-active").length < 1
		) {
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
	$(".delete-membership").on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();

		var $this = $(this),
			$membership_id = $this.data("membership-id"),
			parent = $this.closest(".delete");
		if (parent.find("span").hasClass("is-active")) {
			return;
		}
		ur_membership_utils.append_spinner(parent);

		Swal.fire({
			title:
				'<img src="' +
				ur_membership_data.delete_icon +
				'" id="delete-user-icon">' +
				ur_membership_data.labels.i18n_prompt_title,
			html:
				'<p id="html_1">' +
				ur_membership_data.labels.i18n_prompt_single_subtitle +
				"</p>",
			showCancelButton: true,
			confirmButtonText: ur_membership_data.labels.i18n_prompt_delete,
			cancelButtonText: ur_membership_data.labels.i18n_prompt_cancel,
			allowOutsideClick: false
		}).then(function (result) {
			if (result.isConfirmed) {
				ur_membership_request_utils.send_data(
					{
						action: "user_registration_membership_delete_membership",
						membership_id: $membership_id
					},
					{
						success: function (response) {
							if (response.success) {
								ur_membership_utils.show_success_message(
									response.data.message
								);
								ur_membership_request_utils.remove_deleted_memberships(
									$this,
									false
								);
							} else {
								Swal.fire({
									title:
										'<img src="' +
										ur_membership_data.delete_icon +
										'" id="delete-user-icon">' +
										ur_membership_data.labels
											.i18n_prompt_title,
									html: response.data.message,
									confirmButtonText:
										ur_membership_data.labels
											.i18n_prompt_ok,
									allowOutsideClick: false
								});
							}
						},
						failure: function (xhr, statusText) {
							ur_membership_utils.show_failure_message(
								ur_membership_data.labels.network_error +
									"(" +
									statusText +
									")"
							);
						},
						complete: function () {
							ur_membership_utils.remove_spinner(
								$this.closest(".delete")
							);
							// window.location.reload(); //Todo: Can be removed after fixing checkbox error and adding no content image if empty for all delete on ajax
						}
					}
				);
			} else {
				ur_membership_utils.remove_spinner($this.closest(".delete"));
			}
		});
	});

	$("#membership-list #doaction,#doaction2").on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();
		var form = $("#membership-list"),
			selectedAction = form
				.find("select#bulk-action-selector-top option:selected")
				.val();
		switch (selectedAction) {
			case "delete":
				ur_membership_utils.handle_bulk_delete_action(form);
				break;
			default:
				break;
		}
	});

	var current = 0;
	var $steps = $(
		".ur-page-title__wrapper--steps .ur-page-title__wrapper--steps-btn"
	);
	var $forms = $(".user-registration-card--form-step");

	function showStep(i) {
		$forms.removeClass("user-registration-card--form-step-active");
		$steps.removeClass("ur-page-title__wrapper--steps-btn-active");
		$forms.eq(i).addClass("user-registration-card--form-step-active");
		$steps.eq(i).addClass("ur-page-title__wrapper--steps-btn-active");
		current = i;
	}

	// Click step buttons
	$steps.on("click", function () {
		showStep($(this).data("step"));
	});

	$('#ur-membership-team-pricing').on('change', function () {
		if ($(this).is(':checked')) {
			$('#ur-team-pricing-container').show();
		} else {
			$('#ur-team-pricing-container').hide();
		}
	});

	$(document).on('change', 'input[name^="ur_seat_model"]', function () {
		var wrapper = $(this).closest('.ur-team-pricing-wrapper');

		if ($(this).val() === 'fixed') {
			wrapper.find('.ur-team-fixed-seats-field').show();
			wrapper.find('.ur-team-variable-seats-field').hide();
		} else {
			wrapper.find('.ur-team-fixed-seats-field').hide();
			wrapper.find('.ur-team-variable-seats-field').show();
		}
	});

	$(document).on('change', 'input[name^="ur_pricing_model"]', function () {
		var wrapper = $(this).closest('.ur-team-pricing-wrapper');
		if ($(this).val() === 'per_seat') {
			wrapper.find('.ur-team-per-seats-field').show();
			wrapper.find('.ur-team-tier-field').hide();
		} else {
			wrapper.find('.ur-team-per-seats-field').hide();
			wrapper.find('.ur-team-tier-field').show();
		}
	});

	var ur_team_pricing_template = $(
		'#ur-team-pricing-container .ur-team-pricing-wrapper:first',
	).clone();

	var wrapperCounter = $(
		'#ur-team-pricing-container .ur-team-pricing-wrapper',
	).length;
	$('#ur-add-team-pricing-btn').on('click', function (e) {
		e.preventDefault();
		var newWrapper = ur_team_pricing_template.clone();
		newWrapper.attr('data-pricing-wrapper-id', wrapperCounter);
		newWrapper.find('[id]').each(function () {
			var oldId = $(this).attr('id');
			var newId;
			// Handle bracket notation like ur_team_plan_type[0]
			if (oldId.includes('[') && oldId.includes(']')) {
				newId = oldId.replace(/\[\d+\]/, '_' + wrapperCounter);
			} else if (/_\d+$/.test(oldId)) {
				// Handle underscore notation like ur_team_plan_type_0
				newId = oldId.replace(/_\d+$/, '_' + wrapperCounter);
			} else {
				// Handle hyphen notation like something-0
				newId = oldId.replace(/-\d+$/, '-' + wrapperCounter);
			}
			$(this).attr('id', newId);
		});
		newWrapper.find('[name]').each(function () {
			var oldName = $(this).attr('name');
			if (
				oldName.includes('ur_tier_from') ||
				oldName.includes('ur_tier_to') ||
				oldName.includes('ur_tier_per_seat_price')
			) {
				var fieldType = oldName.split('[')[0];
				$(this).attr('name', fieldType + '[' + wrapperCounter + '][0]');
			} else {
				var baseName = oldName.replace(/\[\d*\]$/, '');
				$(this).attr('name', baseName + '[' + wrapperCounter + ']');
			}
		});
		newWrapper.find('label[for]').each(function () {
			var oldFor = $(this).attr('for');
			var newFor;
			// Handle bracket notation like ur_team_plan_type[0]
			if (oldFor.includes('[') && oldFor.includes(']')) {
				newFor = oldFor.replace(/\[\d+\]/, '_' + wrapperCounter);
			} else if (/_\d+$/.test(oldFor)) {
				// Handle underscore notation like ur_team_plan_type_0
				newFor = oldFor.replace(/_\d+$/, '_' + wrapperCounter);
			} else {
				// Handle hyphen notation like something-0
				newFor = oldFor.replace(/-\d+$/, '-' + wrapperCounter);
			}
			$(this).attr('for', newFor);
		});

		newWrapper.find('input').each(function () {
			if ($(this).attr('type') === 'number') $(this).val('0');
			if ($(this).attr('type') === 'text') $(this).val('');
			if ($(this).attr('type') === 'radio') $(this).prop('checked', false);
		});
		newWrapper
			.find(
				'input[name="ur_seat_model[' + wrapperCounter + ']"][value="fixed"]',
			)
			.prop('checked', true);
		newWrapper
			.find(
				'input[name="ur_pricing_model[' +
					wrapperCounter +
					']"][value="per_seat"]',
			)
			.prop('checked', true);
		newWrapper
			.find(
				'input[name="ur_team_plan_type[' +
					wrapperCounter +
					']"][value="one-time"]',
			)
			.prop('checked', true);
		newWrapper.find('.ur-team-tier-field-wrapper:not(:first)').remove();
		newWrapper
			.find('.ur-team-tier-field-wrapper:first')
			.attr('data-tier-wrapper-id', '0');
		$('#ur-add-team-pricing-btn-wrapper').before(newWrapper);
		newWrapper
			.find('input[name="ur_seat_model[' + wrapperCounter + ']"]:checked')
			.trigger('change');
		newWrapper
			.find('input[name="ur_pricing_model[' + wrapperCounter + ']"]:checked')
			.trigger('change');
		newWrapper
			.find('input[name="ur_team_plan_type[' + wrapperCounter + ']"]:checked')
			.trigger('change');
		wrapperCounter++;
	});

	$(document).on('click', '.ur-remove-team-pricing-btn', function () {
		$(this).closest('.ur-team-pricing-wrapper ').remove();
	});

	// Toggle paid-plan-container based on team plan type
	$(document).on('change', 'input[name^="ur_team_plan_type["]', function () {
		var teamWrapper = $(this).closest('.ur-team-pricing-wrapper');
		var paidPlanContainer = teamWrapper.find('#paid-plan-container');
		var selectedValue = $(this).val();

		if ('subscription' === selectedValue) {
			paidPlanContainer.show();
		} else {
			paidPlanContainer.hide();
		}
	});

	$(document).on('click', '.ur-add-tier-btn', function (e) {
		e.preventDefault();
		var teamWrapper = $(this).closest('.ur-team-pricing-wrapper');
		var tierContainer = teamWrapper.find('.ur-team-tier-field');
		var wrapperId = teamWrapper.attr('data-pricing-wrapper-id');
		var newTierWrapper = $('.ur-team-tier-field-wrapper:first').clone();
		var tierCounter = tierContainer.find('.ur-team-tier-field-wrapper').length;
		newTierWrapper.attr('data-tier-wrapper-id', tierCounter);
		newTierWrapper.find('input').each(function () {
			var base = $(this).attr('name').split('[')[0];
			$(this)
				.attr('name', base + '[' + wrapperId + '][' + tierCounter + ']')
				.val(0);
		});

		$(this).closest('.ur-add-tier-btn-wrapper').before(newTierWrapper);
		tierCounter++;
	});

	$(document).on('click', '.ur-remove-tier-btn', function () {
		$(this).closest('.ur-team-tier-field-wrapper').remove();
	});

	var $membershipTable = $("#membership-list tbody#the-list");

	if ($membershipTable.length > 0 && $.fn.sortable) {
		var updateOrderButtonText = ur_membership_data.labels.i18n_update_order,
			$updateOrderContainer = $(
				'<div class="ur-membership-order-controls ur-d-none"><button type="button" class="button button-primary ur-update-membership-order-btn">' +
					'<svg xmlns="http://www.w3.org/2000/svg" fill="#000" viewBox="0 0 24 24">' +
					'<path d="M2 12A10 10 0 0 1 12 2h.004l.519.015a10.75 10.75 0 0 1 6.53 2.655l.394.363 2.26 2.26a1 1 0 1 1-1.414 1.414l-2.248-2.248-.31-.286A8.75 8.75 0 0 0 11.996 4 8 8 0 0 0 4 12a1 1 0 1 1-2 0Z"/>' +
					'<path d="M20 3a1 1 0 1 1 2 0v5a1 1 0 0 1-1 1h-5a1 1 0 1 1 0-2h4V3Zm0 9a1 1 0 1 1 2 0 10 10 0 0 1-10 10h-.004a10.75 10.75 0 0 1-7.05-2.67l-.393-.363-2.26-2.26a1 1 0 1 1 1.414-1.414l2.248 2.248.31.286A8.749 8.749 0 0 0 12.003 20 7.999 7.999 0 0 0 20 12Z"/>' +
					'<path d="M2 21v-5a1 1 0 0 1 1-1h5a1 1 0 1 1 0 2H4v4a1 1 0 1 1-2 0Z"/>' +
					"</svg>" +
					'<span class="ur-update-order-btn-text">' +
					updateOrderButtonText +
					"</span>" +
					"</button></div>"
			),
			$updateOrderBtn = $updateOrderContainer.find(
				".ur-update-membership-order-btn"
			),
			$spinner = '<span class="ur-spinner"></span>',
			initialOrder = [];
		$(".user-registration-base-list-table-heading").append(
			$updateOrderContainer
		);

		// Helper function to get current order of membership IDs
		// Excludes rows that are being dragged (ui-sortable-helper)
		function getCurrentOrder() {
			var order = [];
			$membershipTable.find("tr[data-membership-id]").each(function () {
				var $row = $(this);
				// Skip rows that are being dragged (helper) or are placeholders
				if (
					$row.hasClass("ui-sortable-helper") ||
					$row.hasClass("ur-sortable-placeholder")
				) {
					return;
				}
				var membershipId = $row.attr("data-membership-id");
				if (membershipId) {
					order.push(parseInt(membershipId, 10));
				}
			});
			return order;
		}

		// Helper function to compare two arrays
		function arraysEqual(arr1, arr2) {
			if (arr1.length !== arr2.length) {
				return false;
			}
			for (var i = 0; i < arr1.length; i++) {
				if (arr1[i] !== arr2[i]) {
					return false;
				}
			}
			return true;
		}

		// Initialize jQuery UI Sortable
		$membershipTable.sortable({
			items: "tr[data-membership-id]",
			cancel: ".no-items",
			cursor: "move",
			opacity: 0.8,
			placeholder: "ur-sortable-placeholder",
			helper: function (e, tr) {
				// Capture initial order before the drag starts affecting the DOM
				// Get order from all rows in their original positions
				initialOrder = [];
				$membershipTable
					.find("tr[data-membership-id]")
					.each(function () {
						var membershipId = $(this).attr("data-membership-id");
						if (membershipId) {
							initialOrder.push(parseInt(membershipId, 10));
						}
					});

				var $originals = tr.children();
				var $helper = tr.clone();
				$helper.children().each(function (index) {
					// Set width of each cell to match original
					$(this).width($originals.eq(index).width());
				});
				// Create a temporary table to maintain table structure
				var $table = $("<table></table>");
				$table.css({
					width: tr.closest("table").width() + "px",
					margin: 0
				});
				$table.append($helper);
				return $table;
			},
			start: function (e, ui) {
				// Initial order should already be captured in helper callback
				// This is just a fallback in case helper didn't run
				if (initialOrder.length === 0) {
					initialOrder = getCurrentOrder();
				}
			},
			stop: function (e, ui) {
				// Get the current order after dragging stops
				var currentOrder = getCurrentOrder();

				// Only show the update button if the order has actually changed
				if (!arraysEqual(initialOrder, currentOrder)) {
					$updateOrderBtn.prop("disabled", false);
					$updateOrderContainer.removeClass("ur-d-none");
					$updateOrderContainer.find(".ur-spinner").remove();
				}

				// Reset initialOrder for next drag
				initialOrder = [];
			}
		});

		$updateOrderContainer.on(
			"click",
			".ur-update-membership-order-btn",
			function (e) {
				e.preventDefault();
				if ($updateOrderContainer.find(".ur-spinner").length > 0) {
					return;
				}
				$updateOrderContainer.append($spinner);

				// Collect membership IDs in current order
				var membershipOrder = [];
				$membershipTable
					.find("tr[data-membership-id]")
					.each(function () {
						var membershipId = $(this).attr("data-membership-id");
						if (membershipId) {
							membershipOrder.push(parseInt(membershipId, 10));
						}
					});

				if (membershipOrder.length === 0) {
					$spinner.removeClass("is-active");
					$updateOrderBtn.prop("disabled", false);
					$updateOrderContainer.find(".ur-spinner").remove();
					return;
				}

				// Send AJAX request
				$.ajax({
					url: ur_membership_data.ajax_url,
					type: "POST",
					data: {
						action: ur_membership_data.update_order_action,
						nonce: ur_membership_data.update_order_nonce,
						membership_order: membershipOrder
					},
					success: function (response) {
						// Remove spinner and reset button
						$updateOrderContainer.find(".ur-spinner").remove();

						if (response.success) {
							ur_membership_utils.show_success_message(
								response.data.message
							);

							$updateOrderContainer.addClass("ur-d-none");
						} else {
							ur_membership_utils.show_failure_message(
								response.data.message
							);
						}
					},
					error: function (xhr, status, error) {
						$updateOrderContainer.find(".ur-spinner").remove();
						ur_membership_utils.show_failure_message(
							ur_membership_data.labels.network_error
						);
					}
				});
			}
		);
	}

	$( document ).on( 'change', '.ur-local-currency-toggle-input', function () {
		var $el = $( this );
		var $card = $el.closest( '.ur-local-currency-card' );
		var $content = $card.find('.ur-local-currency-content');
		var $collapseBtn = $card.find('.ur-local-currency-collapse-btn');
		var zone_id =  $el.data( 'zone-id' );

		if ( $( this ).is( ':checked' ) ) {
			$content.removeClass('hidden');
			$collapseBtn.addClass('collapsed');

			data = {
				'action' : 'user_registration_membership_validate_payment_currency',
				'zone_id' : zone_id,
				'security' : ur_membership_data.validate_payment_currency_nonce
			}

		$.ajax({
				type: "post",
				url: ur_membership_data.ajax_url,
				data: data,
				beforeSend: function( ){
					var spinner = '<span class="ur-spinner is-active"></span>';

					$el.closest( '.ur-local-currency-controls' ).append( spinner )
				},
				success: function( response ){
					$el.closest( '.ur-local-currency-controls' ).find( '.ur-spinner' ).remove();

					if ( ! response.success ) {
						$( document ).find( '.ur-local-currency-' + zone_id + '-message' ).removeClass( 'hidden' );
						$( document ).find( '.ur-local-currency-' + zone_id + '-message' ).html( response.data.message );
					}
				}
			});

		} else {
			$content.addClass('hidden');
			$collapseBtn.removeClass('collapsed');
			$( document ).find( '.ur-local-currency-' + zone_id + '-message' ).addClass( 'hidden' );
			$( document ).find( '.ur-local-currency-' + zone_id + '-message' ).empty();
		}
	});

	 $( '.ur-local-currency-toggle-input:checked' ).each( function () {
		$( this ).trigger( 'change' );
	});

	$( document ).on('click', '.ur-local-currency-collapse-btn', function ( e ) {
		e.preventDefault();
		var $card = $( this ).closest('.ur-local-currency-card');
		var $content = $card.find('.ur-local-currency-content');
		var $toggle = $card.find('.ur-local-currency-toggle-input');

		if ( !$toggle.is(':checked') ) {
			return;
		}

		$content.toggleClass( 'hidden' );
		$(this).toggleClass( 'collapsed' );
	});

	function urHandleLocalCurrencyPricingMethod($card) {
		var $checkedRadio = $card.find('.ur-local-currency-radio-group input[type="radio"]:checked');
		var $manualInput = $card.find('.local-currency-manual-local-price');

		if ($checkedRadio.val() === 'manual') {
			$manualInput.removeClass('hidden').show();
		} else {
			$manualInput.addClass('hidden').hide();
		}
	}

	$(document).on('change', '.ur-local-currency-radio-group input[type="radio"]', function () {
		var $card = $(this).closest('.ur-local-currency-card');
		urHandleLocalCurrencyPricingMethod($card);
	});

	$('.ur-local-currency-card').each(function () {
		urHandleLocalCurrencyPricingMethod($(this));
	});

	function urToggleLocalCurrencyCards( load = '' ) {
		var $toggle = $('#ur-membership-local-currency-action');
		var $cards  = $('.ur-local-currency-card');

		if ( 'free' === $( 'input:radio[name=ur_membership_type]:checked' ).val() && '' === load ) {
			$toggle.prop( 'checked', false );
			ur_membership_utils.show_failure_message(
						ur_membership_data.local_currency_not_support_msg
					);
		}

		if ($toggle.is(':checked')) {
			$cards.show();
		} else {
			$cards.hide();
		}
	}

	$(document).on('change', '#ur-membership-local-currency-action', function () {
		urToggleLocalCurrencyCards();
	});

	urToggleLocalCurrencyCards( 'load' );

	$(document).on(
		"change",
		"#ur-membership-email-marketing-sync-action",
		function (e) {
			e.stopPropagation();
			e.preventDefault();
			var $el = $(this),
				$syncEmailContainer = $(
					"#ur-sync-to-email-marketing-container"
				);
			hideSyncEmailContainer($el.is(":checked"), $syncEmailContainer);
		}
	);

	function hideSyncEmailContainer($isChecked, $syncEmailContainer) {
		if ($isChecked) {
			$syncEmailContainer.show();
		} else {
			$syncEmailContainer.hide();
		}
	}

	var $initialEl = $("#ur-membership-email-marketing-sync-action");
	var $initialContainer = $("#ur-sync-to-email-marketing-container");

	hideSyncEmailContainer($initialEl.is(":checked"), $initialContainer);
	toggleEmailMarketingFields();

	function toggleEmailMarketingFields() {
		$(".ur-sync-to-email-marketing-addon-sync-container").each(function () {
			var $container = $(this);
			var $checkbox = $container.find(
				'.ur-sync-to-email-marketing-addon-sync-toggle-container input[type="checkbox"]'
			);
			var $fields = $container.find(
				".ur-sync-to-email-marketing-addon-sync-toggle-label-container .form-row, " +
					".urmc-sync-email-marketing-mailchimp-list-wrap, " +
					".urmc-sync-email-marketing-brevo-list-wrap, " +
					'[class*="urmc-sync-email-marketing-"][class$="-list-wrap"]'
			);

			if ($checkbox.is(":checked")) {
				$fields.show();
			} else {
				$fields.hide();
			}
		});
	}

	$(document).on(
		"change",
		'.ur-sync-to-email-marketing-addon-sync-toggle-container input[type="checkbox"]',
		function () {
			toggleEmailMarketingFields();
		}
	);

	$(document).on(
		"change",
		".ur_sync_email_marketing_addon_account",
		function (e) {
			e.stopPropagation();
			e.preventDefault();

			var $el = $(this),
				addon = $el.data("addon_name"),
				apiKey = $el.val();

			var data = {
				action: "user_registration_membership_addons_get_lists",
				addon: addon,
				api_key: apiKey
			};

			$.ajax({
				type: "post",
				url: ur_membership_data.ajax_url,
				data: data,
				beforeSend: function () {
					var $listWrap = $(document).find(
						".urmc-sync-email-marketing-" + addon + "-list-wrap"
					);

					ur_membership_utils.append_spinner($listWrap);

					$listWrap.find("select").prop("disabled", true);

					$listWrap
						.closest(
							".ur-sync-to-email-marketing-addon-sync-container"
						)
						.find("select")
						.prop("disabled", true);
				},
				success: function (response) {
					var $listContainer = $(
						".urmc-sync-email-marketing-" + addon + "-list-wrap"
					);

					$listContainer.find(".ur-spinner").remove();

					if (response.success) {
						$listContainer.find("select").remove();

						$listContainer.append(response.data.html);

						if ('mailchimp' === addon && response.data?.tag_html) {
							var $tagContainer = $(
								".urmc-sync-email-marketing-" + addon + "-list-tag-wrap"
							);

							$tagContainer.find("select").each(function () {
								if ($(this).hasClass("select2-hidden-accessible")) {
									$(this).select2("destroy");
								}
							});

							$tagContainer.find("select").remove();

							$tagContainer.append(response.data.tag_html);

							$tagContainer.find("select.ur-enhanced-select").select2({
								width: "100%",
								placeholder: "Select Tags",
								allowClear: true
							});
						}
					}

					$listContainer
						.closest(
							".ur-sync-to-email-marketing-addon-sync-container"
						)
						.find("select")
						.prop("disabled", false);
				}
			});
		}
	);
})(jQuery, window.ur_membership_localized_data);
