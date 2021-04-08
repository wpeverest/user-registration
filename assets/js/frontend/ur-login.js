jQuery(function ($) {
	$(".ur-frontend-form").each(function () {
		var $ur_login_ajax_form = $(this);
		$ur_login_ajax_form
			.find("form.login")
			.on("click", "#user_registration_ajax_login_submit", function (e) {
				e.preventDefault();

				var username = $("#username").val();
				var password = $("#password").val();
				var rememberme = $("#rememberme").val();
				var url =
					ur_login_params.ajax_url +
					"?action=user_registration_ajax_login_submit&security=" +
					ur_login_params.ur_login_form_save_nonce;
				$.ajax({
					type: "POST",
					url: url,
					data: {
						username: username,
						password: password,
						rememberme: rememberme,
					},
					success: function (res) {
						// cutom error message
						if (res.success == false) {
							$("#user-registration")
								.find(".user-registration-error")
								.remove();
							$("#user-registration").prepend(
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
