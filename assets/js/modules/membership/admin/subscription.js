/*global console, UR_Snackbar, Swal*/
(function ($, ur_subscription_data) {
	"use strict";

	var snackbar;
	if (UR_Snackbar) {
		snackbar = new UR_Snackbar();
	}

	$(".user-membership-enhanced-select2").select2();

	var $paymentGatewayContainer = $("#ur-payment-gateway-container");
	var $subscriptionIdContainer = $("#ur-subscription-id-container");
	var $paymentGatewaySelect = $("#ur-subscription-payment-gateway");
	var $planSelect = $("#ur-subscription-plan");
	var $billingAmount = $("#ur-subscription-billing-amount");
	var $billingCycle = $("#ur-subscription-billing-cycle");
	var $billingCycleContainer = $("#ur-billing-cycle-container");
	var $startDateContainer = $("#ur-start-date-container");
	var $expiryDateContainer = $("#ur-expiry-date-container");
	var $startDateField = $("#ur-subscription-start-date");
	var $expiryDateField = $("#ur-subscription-expiry-date");
	var selectedPlanData = null;
	var billingCycleValue = null;

	function updatePlanFields(plan) {
		if (!plan || !plan.meta_value) {
			return;
		}

		selectedPlanData = plan.meta_value;

		if (plan.meta_value.amount) {
			$billingAmount.val(plan.meta_value.amount);
		}

		var isRecurring = plan.meta_value.type === "subscription";

		if (isRecurring) {
			var cycleDuration = plan.meta_value.subscription.duration;
			$billingCycle.val(cycleDuration).trigger("change.select2");
			billingCycleValue = plan.meta_value.subscription.value || 1;

			[
				$billingCycleContainer,
				$startDateContainer,
				$expiryDateContainer
			].forEach(function (container) {
				container.removeClass("ur-d-none").addClass("ur-d-flex");
			});

			$startDateField.prop("required", true);
			$expiryDateField.prop("required", true);
		} else {
			$billingCycle.val("").trigger("change.select2");
			billingCycleValue = null;

			[
				$billingCycleContainer,
				$startDateContainer,
				$expiryDateContainer
			].forEach(function (container) {
				container.removeClass("ur-d-flex").addClass("ur-d-none");
			});

			$startDateField.prop("required", false).val("");
			$expiryDateField.prop("required", false).val("");
		}
	}

	$planSelect.on("change", function () {
		var planId = $(this).val();
		if (
			planId &&
			typeof ur_membership_plans !== "undefined" &&
			Array.isArray(ur_membership_plans)
		) {
			var selectedPlan = ur_membership_plans.find(function (plan) {
				return String(plan.ID) === String(planId);
			});

			if (selectedPlan) {
				updatePlanFields(selectedPlan);

				if (
					selectedPlan.meta_value &&
					selectedPlan.meta_value.payment_gateways
				) {
					var paymentGateways =
						selectedPlan.meta_value.payment_gateways;
					var enabledGateways = {};
					var allGateways =
						ur_subscription_data.payment_gateways || {};

					Object.keys(paymentGateways).forEach(function (gatewayKey) {
						if (paymentGateways[gatewayKey].status === "on") {
							var label =
								allGateways[gatewayKey] ||
								gatewayKey.charAt(0).toUpperCase() +
									gatewayKey.slice(1);
							enabledGateways[gatewayKey] = label;
						}
					});

					if (Object.keys(enabledGateways).length > 0) {
						if (
							$paymentGatewaySelect.hasClass(
								"select2-hidden-accessible"
							)
						) {
							$paymentGatewaySelect.select2("destroy");
						}
						$paymentGatewaySelect.empty();
						$paymentGatewaySelect.append(
							'<option value="">' +
								(ur_subscription_data.i18n_select_payment_gateway ||
									"Select Payment Gateway") +
								"</option>"
						);
						$.each(enabledGateways, function (key, label) {
							$paymentGatewaySelect.append(
								'<option value="' +
									key +
									'" data-gateway-key="' +
									key +
									'">' +
									label +
									"</option>"
							);
						});
						$paymentGatewaySelect.select2();
						$paymentGatewayContainer.show();
					} else {
						$paymentGatewayContainer.hide();
						$subscriptionIdContainer.hide();
					}
				} else {
					$paymentGatewayContainer.hide();
					$subscriptionIdContainer.hide();
				}
			} else {
				$billingAmount.val("");
				$billingCycle.val("").trigger("change.select2");
				selectedPlanData = null;
				billingCycleValue = null;
				$paymentGatewayContainer.hide();
				$subscriptionIdContainer.hide();
				$billingCycleContainer.hide();
				$startDateContainer.hide();
				$expiryDateContainer.hide();
				$startDateField.prop("required", false).val("");
				$expiryDateField.prop("required", false).val("");
			}
		} else {
			$billingAmount.val("");
			$billingCycle.val("").trigger("change.select2");
			selectedPlanData = null;
			billingCycleValue = null;
			$paymentGatewayContainer.hide();
			$subscriptionIdContainer.hide();
			$billingCycleContainer.hide();
			$startDateContainer.hide();
			$expiryDateContainer.hide();
			$startDateField.prop("required", false).val("");
			$expiryDateField.prop("required", false).val("");
		}
	});

	$paymentGatewaySelect.on("change", function () {
		var selectedGateway = $(this).val();
		if (selectedGateway === "stripe") {
			$subscriptionIdContainer.show();
		} else {
			$subscriptionIdContainer.hide();
			$("#ur-subscription-id-field").val("");
		}
	});

	$('button[form="ur-membership-subscription-edit-form"]').on(
		"click",
		function () {
			$('form[id="ur-membership-subscription-edit-form"]').trigger(
				"submit"
			);
		}
	);

	$('form[id="ur-membership-subscription-create-form"]').on(
		"submit",
		function (e) {
			e.preventDefault();
			var $btn = $(
				'button[form="ur-membership-subscription-create-form"]'
			);

			$btn.prop("disabled", true).append(
				'<span class="ur-spinner"></span>'
			);

			$.ajax({
				url:
					ur_subscription_data.ajax_url +
					"?action=user_registration_membership_create_subscription",
				type: "POST",
				data: $(this).serialize(),
				success: function (response) {
					if (response.success) {
						snackbar.add({
							type: "success",
							message: response.data.message,
							duration: 5
						});
						setTimeout(function () {
							window.location.href =
								ur_subscription_data.subscriptions_url;
						}, 1000);
					} else {
						snackbar.add({
							type: "failed",
							message: response.data.message,
							duration: 5
						});
					}
				},
				error: function (jqXHR, exception) {
					snackbar.add({
						type: "failed",
						message: ur_subscription_data.i18n_error,
						duration: 5
					});
				},
				complete: function () {
					$btn.prop("disabled", false).find(".ur-spinner").remove();
				}
			});
		}
	);

	$('form[id="ur-membership-subscription-edit-form"]').on(
		"submit",
		function (e) {
			e.preventDefault();
			var $btn = $('button[form="ur-membership-subscription-edit-form"]');

			$btn.prop("disabled", true).append(
				'<span class="ur-spinner"></span>'
			);

			$.ajax({
				url:
					ur_subscription_data.ajax_url +
					"?action=user_registration_membership_update_subscription",
				type: "POST",
				data: $(this).serialize(),
				success: function (response) {
					if (response.success) {
						snackbar.add({
							type: "success",
							message: response.data.message,
							duration: 5
						});
					} else {
						snackbar.add({
							type: "failed",
							message: response.data.message,
							duration: 5
						});
					}
				},
				error: function (jqXHR, exception) {
					snackbar.add({
						type: "failed",
						message: ur_subscription_data.i18n_error,
						duration: 5
					});
				},
				complete: function () {
					$btn.prop("disabled", false).find(".ur-spinner").remove();
				}
			});
		}
	);

	$(".submitdelete,.single-delete-subscription").on("click", function (e) {
		e.preventDefault();
		var $this = $(this);
		Swal.fire({
			title:
				'<img src="' +
				ur_subscription_data.delete_icon +
				'" id="delete-user-icon">' +
				ur_subscription_data.i18n_prompt_delete_title,
			html:
				'<p id="html_1">' +
				ur_subscription_data.i18n_prompt_delete_description +
				"</p>",
			showCancelButton: true,
			confirmButtonText: ur_subscription_data.i18n_prompt_delete_confirm,
			cancelButtonText: ur_subscription_data.i18n_prompt_delete_cancel,
			allowOutsideClick: false
		}).then(function (result) {
			if (result.isConfirmed) {
				window.location.href = $this.attr("href");
			}
		});
	});

	$(".urm-load-more-events").on("click", function () {
		var $wrapper = $(this).closest(
			".ur-subscription__main-content-wrapper"
		);
		var $button = $(this);

		var offset = parseInt($wrapper.data("offset"), 10);
		var limit = parseInt($wrapper.data("limit"), 10);
		var total = parseInt($wrapper.data("total"), 10);

		$button
			.prop("disabled", true)
			.text(ur_subscription_data.i18n_loading_text);

		$.post(ur_subscription_data.ajax_url, {
			action: "user_registration_pro_load_more_subscription_events",
			nonce: ur_subscription_data._nonce,
			subscription_id: $wrapper.data("subscription-id"),
			limit: limit,
			offset: offset
		}).done(function (response) {
			if (!response.success) {
				$button.remove();
				return;
			}

			var $newItems = $(response.data.html).find(
				".ur-subscription__event"
			);

			$wrapper
				.find(".ur-subscription__events-timeline")
				.append($newItems);

			offset += response.data.count;
			$wrapper.data("offset", offset);

			if (offset >= total) {
				$button.remove();
			} else {
				$button
					.prop("disabled", false)
					.text(ur_subscription_data.i18n_view_more_text);
			}
		});
	});

	$("#ur-subscription-start-date, #ur-subscription-expiry-date").on(
		"click focus",
		function () {
			if (this.showPicker) {
				this.showPicker();
			}
		}
	);
})(jQuery, window.ur_subscription_data);
