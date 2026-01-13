(function ($) {
	$(document).ready(function () {
		// Masteriyo Course Portal Membership Selection
		$(document).on(
			"click",
			".urm-masteriyo-membership-list .membership-block input[type='radio']",
			function () {
				var checkoutUrl = $(
					".masteriyo-single-course--btn.masteriyo-enroll-btn"
				).attr("href");
				console.log("hi", checkoutUrl);

				if (checkoutUrl) {
					$(
						".masteriyo-single-course--btn.masteriyo-enroll-btn"
					).attr(
						"href",
						checkoutUrl + "?membership_id=" + $(this).val()
					);
				}
			}
		);
	});
})(jQuery);
