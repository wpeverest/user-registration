/*!
 * JavaScript Library v1.0.0
 * Name: UR_Toggle_Buttons
 * Author: WPEverest
 * Versoin: 1.0.0
 */

/**
 * Create a new toggle buttons group and return html. Following are the options currently supported.
 * - id: ID for the instance.
 * - className: Class for the parent element.
 * - buttons: List of buttons. Consists of two keys i.e. value and text.
 * - value: Initially selected button value.
 */
/* global ur_components_script_params */
window.ur_create_toggle_buttons = function (args) {
	var id = args.id ? args.id : "",
		className = args.className ? args.className : "",
		html =
			'<div class="user-registration-button-group user-registration-button-group-' +
			id +
			" " +
			className +
			'">',
		buttons =
			args.buttons && Array.isArray(args.buttons) ? args.buttons : [],
		value = args.value ? args.value : "",
		active = "";

	buttons.forEach(function (button) {
		if (value === button.value) {
			active = "is-active";
		} else {
			active = "";
		}
		html +=
			'<button class="button button-tertiary urbg-item urbg-item-' +
			id +
			" " +
			active +
			'" data-value="' +
			button.value +
			'">' +
			button.text +
			"</button>";
	});
	html += "</div>";

	return html;
};

jQuery(function ($) {
	$(document.body).on("click", ".urbg-item", function () {
		if (!$(this).is(".is-active")) {
			$(this).siblings().removeClass("is-active");
			$(this).addClass("is-active");
		}
	});

	$(document.body).on(
		"click",
		".user-registration-card__toggle",
		function () {
			var card_body = $(this)
				.closest(".user-registration-card")
				.find(".user-registration-card__body");

			card_body.toggle();

			if (card_body.is(":visible")) {
				$(this).html(
					'<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="6 15 12 9 18 15"></polyline></svg>'
				);
			} else {
				$(this).html(
					'<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="6 9 12 15 18 9"></polyline></svg>'
				);
			}
		}
	);

	$(document.body).on("change", ".user-registration-switch", function () {
		var all_check = $(this).find(".hide-show-check"),
			// set checkbox status
			checked = all_check.is(":checked") ? true : false;

		if (true === checked) {
			all_check.prop("checked", checked);
			all_check.addClass("enabled");
			all_check
				.closest(".user-registration-switch")
				.find("label")
				.html(ur_components_script_params.card_switch_enabled_text);
		} else {
			all_check.prop("checked", checked);
			all_check.removeClass("enabled");
			all_check
				.closest(".user-registration-switch")
				.find("label")
				.html(ur_components_script_params.card_switch_disabled_text);
		}
	});
});
