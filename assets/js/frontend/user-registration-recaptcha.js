/* global  ur_google_recaptcha_code */
/* global  grecaptcha */
/* global  user_registration_params */

(function ($) {
	var ursL10n = user_registration_params.ursL10n;
	var user_registration_recaptcha_init = function () {
		$(function () {
			request_recaptcha_token();
		});
	};

	user_registration_recaptcha_init();

	// /**
	//  * Reinitialize the form again after page is fully loaded,
	//  * in order to support third party popup plugins like elementor.
	//  *
	//  * @since 1.9.0
	//  */
	// $(window).on("load", function () {
	// 	user_registration_recaptcha_init();
	// });

	$(function () {
		$(document).on(
			"user_registration_frontend_before_form_submit",
			function (event, data, $registration_form, $error_message) {
				if ("undefined" !== typeof ur_google_recaptcha_code) {
					var captchaResponse;
					if ("1" == $registration_form.data("captcha-enabled")) {
						if ("yes" === ur_google_recaptcha_code.is_invisible) {
							captchaResponse = $registration_form
								.find(
									"#ur-recaptcha-node .g-recaptcha .grecaptcha-badge input#recaptcha-token"
								)
								.val();
						} else {
							captchaResponse = $registration_form
								.find('[name="g-recaptcha-response"]')
								.val();
						}
						if (ur_google_recaptcha_code.version == "v3") {
							request_recaptcha_token();
						} else {
							for (
								var i = 0;
								i <= google_recaptcha_user_registration;
								i++
							) {
								grecaptcha.reset(i);
							}
						}
						console.log(captchaResponse);
						if (0 === captchaResponse.length) {
							$error_message["message"] = ursL10n.captcha_error;
						}
					}
				}
			}
		);

		$(document).on(
			"user_registration_after_login_failed",
			function (event, $login_form) {
				if ("undefined" !== typeof ur_google_recaptcha_code) {
					var ur_recaptcha_node = $login_form
						.closest("form")
						.find(
							"#ur-recaptcha-node #node_recaptcha_login.g-recaptcha"
						).length;
					if (ur_recaptcha_node !== 0) {
						if (ur_google_recaptcha_code.version == "v3") {
							request_recaptcha_token();
						} else {
							for (var i = 0; i <= google_recaptcha_login; i++) {
								grecaptcha.reset(i);
							}
						}
					}
				}
			}
		);
	});
})(jQuery);

var google_recaptcha_user_registration;
var google_recaptcha_login;
var onloadURCallback = function () {
	jQuery(".ur-frontend-form")
		.find("form.register")
		.each(function (i) {
			$this = jQuery(this);
			var form_id = $this.closest(".ur-frontend-form").attr("id");

			var node_recaptcha_register = $this.find(
				"#ur-recaptcha-node #node_recaptcha_register"
			).length;

			if (node_recaptcha_register !== 0) {
				$this
					.find("#ur-recaptcha-node .g-recaptcha")
					.attr("id", "node_recaptcha_register_" + form_id);
				if (ur_google_recaptcha_code.is_invisible == "yes") {
					$this
						.find("#ur-recaptcha-node .grecaptcha-badge")
						.css("visibility", "hidden");
					google_recaptcha_user_registration = grecaptcha.render(
						// "node_recaptcha_register",
						{
							sitekey: ur_google_recaptcha_code.site_key,
						}
					);
					console.log(google_recaptcha_user_registration);
					grecaptcha.execute(google_recaptcha_user_registration);
				} else {
					google_recaptcha_user_registration = grecaptcha.render(
						"node_recaptcha_register_" + form_id,
						{
							sitekey: ur_google_recaptcha_code.site_key,
							theme: "light",
							style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
						}
					);
				}
			}
		});

	jQuery(".ur-frontend-form")
		.find("form.login")
		.each(function (i) {
			$this = jQuery(this);
			var ur_recaptcha_node = $this.find("#ur-recaptcha-node");

			if (ur_recaptcha_node.length !== 0) {
				if (ur_google_recaptcha_code.is_invisible == "yes") {
					google_recaptcha_login = grecaptcha.render(
						ur_recaptcha_node.find(".g-recaptcha").attr("id"),
						{
							sitekey: ur_google_recaptcha_code.site_key,
						},
						true
					);
					grecaptcha.execute(google_recaptcha_login);
				} else {
					google_recaptcha_login = grecaptcha.render(
						ur_recaptcha_node.find(".g-recaptcha").attr("id"),
						{
							sitekey: ur_google_recaptcha_code.site_key,
							theme: "light",
							style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
						}
					);
				}
			}
		});
};

function request_recaptcha_token() {
	var node_recaptcha_register = jQuery(".ur-frontend-form").find(
		"form.register #ur-recaptcha-node #node_recaptcha_register.g-recaptcha-v3"
	).length;

	if (node_recaptcha_register !== 0) {
		grecaptcha.ready(function () {
			grecaptcha
				.execute(ur_google_recaptcha_code.site_key, {
					action: "register",
				})
				.then(function (token) {
					jQuery("form.register")
						.find("#g-recaptcha-response")
						.text(token);

					var captchaResponse = jQuery("form.register")
						.find('[name="g-recaptcha-response"]')
						.val();
				});
		});
	}
	var node_recaptcha_login = jQuery(".ur-frontend-form").find(
		"form.login .ur-form-row .ur-form-grid #ur-recaptcha-node #node_recaptcha_login.g-recaptcha-v3"
	).length;
	if (node_recaptcha_login !== 0) {
		grecaptcha.ready(function () {
			grecaptcha
				.execute(ur_google_recaptcha_code.site_key, { action: "login" })
				.then(function (token) {
					jQuery("form.login")
						.find("#g-recaptcha-response")
						.text(token);
				});
		});
	}
}
