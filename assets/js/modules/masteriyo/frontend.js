(function ($) {
	$(document).ready(function () {
		// Masteriyo Course Portal Membership Selection
		// $(document).on(
		// 	"click",
		// 	".urm-masteriyo-membership-list .membership-block input[type='radio']",
		// 	function () {
		// 		var checkoutUrl = $(
		// 			".masteriyo-single-course--btn.masteriyo-enroll-btn"
		// 		).attr("href");

		// 		if (checkoutUrl) {
		// 			$(
		// 				".masteriyo-single-course--btn.masteriyo-enroll-btn"
		// 			).attr(
		// 				"href",
		// 				checkoutUrl +
		// 					(checkoutUrl.includes("?") ? "&" : "?") +
		// 					"membership_id=" +
		// 					$(this).val()
		// 			);
		// 		}
		// 	}
		// );

		$(document).on(
			"click",
			".urm-masteriyo-single-membership-radio",
			function () {
				$(".masteriyo-enroll-btn").attr("disabled", "disabled");

				$.ajax({
					url: ur_members_localized_data.ajax_url,
					type: "POST",
					data: {
						action: "urm_masteriyo_single_membership_redirect",
						membership_id: $(this).val()
					},
					success: function (response) {
						$(".masteriyo-enroll-btn").attr("disabled", false);

						if (response.success) {
							const redirectUrl = response.data.redirect_url;

							$(".masteriyo-enroll-btn").attr(
								"href",
								redirectUrl
							);
						} else {
							console.error("Failed:", response);
						}
					},
					error: function (xhr) {
						console.error("AJAX Error:", xhr);
					}
				});
			}
		);
	});
})(jQuery);
