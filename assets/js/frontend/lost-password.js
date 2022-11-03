jQuery(function ($) {
	$(".ur_lost_reset_password").on("submit", function () {
		$('input[type="submit"]', this).prop("disabled", true);
	});
});
