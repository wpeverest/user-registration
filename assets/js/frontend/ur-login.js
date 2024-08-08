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
						redirect_url = $this
							.closest("form")
							.find('input[name="redirect"]')
							.val();
					if ("hCaptcha" === ur_login_params.recaptcha_type) {
						var CaptchaResponse = $this
							.closest("form")
							.find('[name="h-captcha-response"]')
							.val();
					} else if (
						"cloudflare" === ur_login_params.recaptcha_type
					) {
						var CaptchaResponse = $this
							.closest("form")
							.find('[name="cf-turnstile-response"]')
							.val();
					} else {
						var CaptchaResponse = $this
							.closest("form")
							.find('[name="g-recaptcha-response"]')
							.val();
					}

					var url =
						ur_login_params.ajax_url +
						"?action=user_registration_ajax_login_submit&security=" +
						ur_login_params.ur_login_form_save_nonce;

					if (window.location.href.indexOf("pl=true") > -1) {
						// "pl=true" is present in the URL.
						url += "&pl=true";
					}

					$this
						.closest("form")
						.find(".ur-submit-button")
						.siblings("span")
						.addClass("ur-front-spinner");

					var data = {
						username: username,
						password: password,
						CaptchaResponse: CaptchaResponse,
						redirect: redirect_url
					};

					if (
						$this
							.closest("form")
							.find('input[name="rememberme"]')
							.is(":checked")
					) {
						data.rememberme = rememberme;
					}

					$.ajax({
						type: "POST",
						url: url,
						data: data,
						success: function (res) {
							$this
								.closest("form")
								.find(".ur-submit-button")
								.siblings("span")
								.removeClass("ur-front-spinner");

							// custom error message
							if (res.success == false) {
								$(document).trigger(
									"user_registration_after_login_failed",
									[$this]
								);

								$this
									.closest("#user-registration")
									.find(".user-registration-error")
									.remove();

								$this
									.closest("#user-registration")
									.find(".user-registration-message")
									.remove();

								$this
									.closest("#user-registration")
									.prepend(
										'<ul class="user-registration-error">' +
											res.data.message +
											"</ul>"
									);
							} else {
								if (res.data.status) {
									$this
										.closest("#user-registration")
										.find(".user-registration-error")
										.remove();

									$this
										.closest("#user-registration")
										.find(".user-registration-message")
										.remove();

									$this
										.closest("#user-registration")
										.prepend(
											'<ul class="user-registration-message">' +
												res.data.message +
												"</ul>"
										);

									$this
										.closest("#user-registration")
										.find("input#username")
										.val("");
								} else {
									window.location.href = res.data.message;
								}
							}
						}
					}).fail(function () {
						$this
							.closest("#user-registration")
							.find(".user-registration-error")
							.remove();

						$this
							.closest("#user-registration")
							.find(".user-registration-message")
							.remove();

						$this
							.closest("#user-registration")
							.prepend(
								'<ul class="user-registration-error">' +
									ur_login_params.ajax_form_submit_error +
									"</ul>"
							);
						return;
					});
				});
		});
});
