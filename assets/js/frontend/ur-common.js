(function ($) {
	$(document).on("click", ".password_preview", function (e) {
		e.preventDefault();
		var ursL10n = user_registration_params.ursL10n;

		var current_task = $(this).hasClass("dashicons-hidden")
			? "show"
			: "hide";
		var $password_field = $(this)
			.closest(".user-registration-form-row")
			.find('input[name="password"]');

		// Hide/show password for user registration form
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".field-user_pass")
				.find('input[name="user_pass"]');
		}
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".field-user_confirm_password")
				.find('input[name="user_confirm_password"]');
		}

		// Hide/show password for edit password form
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".user-registration-form-row")
				.find('input[name="password_current"]');
		}
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".user-registration-form-row")
				.find('input[name="password_1"]');
		}
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".user-registration-form-row")
				.find('input[name="password_2"]');
		}
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".field-password")
				.find(".input-password");
		}

		if ($password_field.length > 0) {
			switch (current_task) {
				case "show":
					$password_field.attr("type", "text");
					$(this)
						.removeClass("dashicons-hidden")
						.addClass("dashicons-visibility");
					$(this).attr("title", ursL10n.hide_password_title);
					break;
				case "hide":
					$password_field.attr("type", "password");
					$(this)
						.removeClass("dashicons-visibility")
						.addClass("dashicons-hidden");
					$(this).attr("title", ursL10n.show_password_title);
					break;
			}
		}
	});
})(jQuery);
