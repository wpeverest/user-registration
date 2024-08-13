/* global wp, ur_password_strength_meter_params */
jQuery(function ($) {
	var pwsL10n = ur_password_strength_meter_params.pwsL10n;
	var custom_password_params = ur_password_strength_meter_params.custom_password_params;

	/**
	 * Password Strength Meter class.
	 */
	var ur_password_strength_meter = {
		/**
		 * Initialize strength meter actions.
		 */
		init: function () {
			var $this = this;
			$(document.body).on(
				"keyup change",
				'input[name="user_pass"], .user-registration-EditAccountForm input[name="password_1"], input[name="password_1"].user-registration-Input--password,.user-registration-ResetPassword input[name="password_1"]',
				function () {
					var enable_strength_password = $(this)
						.closest("form")
						.attr("data-enable-strength-password");
					if ("" === enable_strength_password) {
						return;
					}

					$this.strengthMeter($(this));
				}
			);
		},
		/**
		 * Strength Meter.
		 */
		strengthMeter: function (self) {
			var wrapper = self.closest("form"),
				field = $(self, wrapper);

			ur_password_strength_meter.includeMeter(wrapper, field);
			ur_password_strength_meter.checkPasswordStrength(wrapper, field);
		},

		/**
		 * Include meter HTML.
		 *
		 * @param {Object} wrapper
		 * @param {Object} field
		 */
		includeMeter: function (wrapper, field) {
			var minimum_password_strength = wrapper.attr(
				"data-minimum-password-strength"
			);

			var meter = wrapper.find(".user-registration-password-strength");

			var password_field = wrapper.find(".password-input-group");
			if ("" === field.val()) {
				meter.remove();
				$(document.body).trigger("ur-password-strength-removed");
			} else if (0 === meter.length) {
				var html =
					'<div class="user-registration-password-strength" aria-live="polite" data-min-strength="' +
					minimum_password_strength +
					'"></div>';

				if (wrapper.hasClass("register")) {
					password_field.closest(".field-user_pass").after(html);
				} else {
					$("#password_1")
						.closest(".password-input-group")
						.after(html);
				}
				$(document.body).trigger("ur-password-strength-added");
			}
		},
		/**
		 * Check password strength.
		 *
		 * @param {Object} field
		 *
		 * @return {Int}
		 */
		checkPasswordStrength: function (wrapper, field) {
			var meter = wrapper.find(".user-registration-password-strength"),
				hint = wrapper.find(".user-registration-password-hint"),
				minimum_password_strength = wrapper.attr(
					"data-minimum-password-strength"
				),
				hint_name = "i18n_password_hint_" + minimum_password_strength,
				hint_html =
					"undefined" !==
					typeof ur_password_strength_meter_params[hint_name]
						? '<small class="user-registration-password-hint">' +
						  ur_password_strength_meter_params[hint_name] +
						  "</small>"
						: "",
				submit_button = wrapper.find(
					'input[type="submit"].user-registration-Button'
				),
				disallowedListArray = [];
			var strength = ur_password_strength_meter.extraPasswordChecks(
				field.val(),
				disallowedListArray
			);
			if('4' === minimum_password_strength) {
				strength = customPasswordChecks(field.val());
				hint_html = '<small class="user-registration-password-hint">' +
					custom_password_params.hint +
					'</small>';
			}
			if (
				"function" ===
				typeof wp.passwordStrength.userInputDisallowedList
			) {
				disallowedListArray =
					wp.passwordStrength.userInputDisallowedList();
			} else {
				disallowedListArray = wp.passwordStrength.userInputBlacklist();
			}
			disallowedListArray.push(
				wrapper.find('input[data-id="user_email"]').val()
			); // Add email address in disallowedList.
			disallowedListArray.push(
				wrapper.find('input[data-id="user_login"]').val()
			); // Add username in disallowedList.


			// Reset
			meter.removeClass("short bad good strong");
			hint.remove();
			meter.after(hint_html);

			wrapper
				.find(".user-registration-password-strength")
				.attr("data-current-strength", strength);
			switch (strength) {
				case 0:
					meter.addClass("short").html(pwsL10n.shortpw);
					break;
				case 1:
					meter.addClass("bad").html(pwsL10n.bad);
					break;
				case 2:
					meter.addClass("good").html(pwsL10n.good);
					break;
				case 3:
				case 4:
					meter.addClass("strong").html(pwsL10n.strong);
					break;
				case 5:
					meter.addClass("short").html(pwsL10n.mismatch);
					break;
			}

			if (strength >= minimum_password_strength) {
				submit_button.prop("disabled", false);
			} else {
				submit_button.prop("disabled", true);
			}

			return strength;
		},
		/**
		 *
		 * @param string password Password provided by the user.
		 * @param array disallowedListArray Disallowed List.
		 * @returns
		 */
		extraPasswordChecks: function (password, disallowedListArray) {
			var strength = wp.passwordStrength.meter(
					password,
					disallowedListArray
				),
				pattern = /[A-ZА-Я]/;

			switch (strength) {
				case 1:
					if (!pattern.test(password)) {
						strength = 0;
					}
					break;
				case 2:
					if (pattern.test(password)) {
						pattern = /\d/;
						if (!pattern.test(password)) {
							strength = 1;
						}
					} else {
						strength = 0;
					}

					break;
				case 3:
				case 4:
					if (pattern.test(password)) {
						pattern = /\d/;
						if (pattern.test(password)) {
							pattern = /[!@#$%^&*(),.?":{}|<>]/;
							if (
								pattern.test(password) &&
								password.length >= 9
							) {
								strength = 4;
							} else {
								strength = 2;
							}
						} else {
							strength = 1;
						}
					} else {
						strength = 0;
					}
					break;
				default:
					strength = strength;
			}

			return strength;
		},
	};
	ur_password_strength_meter.init();
});
