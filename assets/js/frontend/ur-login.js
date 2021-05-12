jQuery(function ($) {
	$(".ur-frontend-form")
		.find("form.login")
		.each(function () {
			var $ur_login_ajax_form = $(this);
			$ur_login_ajax_form
				.find("#user_registration_ajax_login_submit")
				.on("click", function (e) {
					e.preventDefault();
					var $this = $(this);
					var username = $this
						.closest("form")
						.find('input[name="username"]')
						.val();
					var password = $this
						.closest("form")
						.find('input[name="password"]')
						.val();
					var rememberme = $this
							.closest("form")
							.find('input[name="rememberme"]')
							.val(),
						CaptchaResponse = $this
							.closest("form")
							.find("#g-recaptcha-response")
							.val();

					var url =
						ur_login_params.ajax_url +
						"?action=user_registration_ajax_login_submit&security=" +
						ur_login_params.ur_login_form_save_nonce;

					$this
						.closest("form")
						.find(".ur-submit-button")
						.siblings("span")
						.addClass("ur-front-spinner");

					$.ajax({
						type: "POST",
						url: url,
						data: {
							username: username,
							password: password,
							rememberme: rememberme,
							CaptchaResponse: CaptchaResponse,
						},
						success: function (res) {
							$this
								.closest("form")
								.find(".ur-submit-button")
								.siblings("span")
								.removeClass("ur-front-spinner");

							// custom error message
							if (res.success == false) {
								$this
									.closest("#user-registration")
									.find(".user-registration-error")
									.remove();

								$this
									.closest("#user-registration")
									.prepend(
										'<ul class="user-registration-error">' +
											res.data.message +
											"</ul>"
									);
							} else {
								window.location = res.data.message;
							}
						},
					});
				});
		});
});
