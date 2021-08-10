jQuery(function ($) {
	// Review notice.
	$(".user-registration-notice").each(function () {
		$(this)
			.find(".notice-dismiss")
			.on("click", function (e) {
				e.preventDefault();

				var notice_type = $(this)
						.closest(".user-registration-notice")
						.data("purpose"),
					notice_type_nonce = notice_type + "_nonce";

				$(this)
					.closest("#user-registration-" + notice_type + "-notice")
					.hide();

				var data = {
					action: "user_registration_dismiss_notice",
					security: ur_notice_params[notice_type_nonce],
					notice_type: notice_type,
					dismissed: true,
				};

				$.post(ur_notice_params.ajax_url, data, function (response) {
					// Success. Do nothing. Silence is golden.
				});
			});
	});
});
