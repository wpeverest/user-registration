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
				   notice_id = $(this)
						.closest(".user-registration-notice")
						.data("notice-id"),
					notice_type_nonce = notice_type + "_nonce",
					dismiss_forever = $(this).hasClass(
						"notice-dismiss-permanently"
					);

				$(this)
					.closest("#user-registration-" + notice_id + "-notice")
					.hide();


					var data = {
						action: "user_registration_dismiss_notice",
						security: ur_notice_params[notice_type_nonce],
						notice_type: notice_type,
						notice_id: notice_id,
						dismissed: true,
						dismiss_forever: dismiss_forever,
					};

				$.post(ur_notice_params.ajax_url, data, function (response) {
					// Success. Do nothing. Silence is golden.
				});
			});
	});
	$(".urm-per-user-notice").each(function () {
		var notice_id = $(this).data('notice-id'),
			notice_type = $(this).data('notice-type');

		$(document)
			.on('click', '.urm-per-user-notice .notice-dismiss', function(e) {
				e.preventDefault();
				$.post(ur_notice_params.ajax_url, {
					action: 'user_registration_dismiss_notice_per_user',
					notice_id: notice_id,
					notice_type: notice_type,
					security: ur_notice_params[notice_type + '_nonce'],
				});
			});
	});
});
