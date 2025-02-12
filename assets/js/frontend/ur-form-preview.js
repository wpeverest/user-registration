jQuery(function ($) {
	/**
	 * Save form applying theme style.
	 */
	$(document.body).on("change", "#ur_toggle_form_preview_theme", function () {
		$(".ur-frontend-form").toggleClass("ur-frontend-form-preview");
		$("#ur-form-save").toggleClass("hidden");
	});
	/**
	 * Toggle sidepanel.
	 */
	$(document.body).on(
		"click",
		".ur-form-preview-sidepanel-toggler",
		function () {
			$(".ur-form-side-panel").toggleClass("hidden");
			$(this).toggleClass("inactive");
			$(".ur-form-preview-main-content").toggleClass(
				"ur-form-preview-overlay"
			);
		}
	);

	/**
	 * Change form preview based on device selected.
	 */
	$(document.body).on("click", ".ur-form-preview-device", function () {
		var device = $(this).data("device");
		var container_wrapper = $(".ur-frontend-form");
		var preview_form = $(".ur-preview-content");
		$(this)
			.closest(".ur-form-preview-devices")
			.find(".ur-form-preview-device")
			.removeClass("active");
		$(this).parent().find("svg path").css("fill", "#383838");
		$(this).find("path").css("fill", "#475BB2");

		if (device === "desktop") {
			container_wrapper.addClass("ur-frontend-form-desktop-view");
			container_wrapper.removeClass("ur-frontend-form-table-view");
			container_wrapper.removeClass("ur-frontend-form-mobile-view");
			preview_form.removeClass("ur-preview-tablet-wrapper");
			preview_form.removeClass("ur-preview-mobile-wrapper");
			$(this).addClass("active");
		} else if (device === "tablet") {
			container_wrapper.addClass("ur-frontend-form-table-view");
			container_wrapper.removeClass("ur-frontend-form-desktop-view");
			container_wrapper.removeClass("ur-frontend-form-mobile-view");
			preview_form.addClass("ur-preview-tablet-wrapper");
			preview_form.removeClass("ur-preview-mobile-wrapper");
			$(this).addClass("active");
		} else if (device === "mobile") {
			container_wrapper.addClass("ur-frontend-form-mobile-view");
			container_wrapper.removeClass("ur-frontend-form-desktop-view");
			container_wrapper.removeClass("ur-frontend-form-table-view");
			preview_form.addClass("ur-preview-mobile-wrapper");
			preview_form.removeClass("ur-preview-tablet-wrapper");

			$(this).addClass("active");
		} else {
			container_wrapper.removeClass("ur-frontend-form-desktop-view");
			container_wrapper.removeClass("ur-frontend-form-table-view");
			container_wrapper.removeClass("ur-frontend-form-mobile-view");
			$(this).addClass("active");
		}
	});

	/**
	 * Save form preview settings.
	 */
	$(document.body).on("click", "#ur-form-save", function () {
		var form_id = $(this).data("id");
		var is_enabled = $("#ur_toggle_form_preview_theme").is(":checked");
		if (is_enabled) {
			form_theme = "theme";
		} else {
			form_theme = "default";
		}

		$.ajax({
			url: user_registration_form_preview.ajax_url,
			type: "POST",
			data: {
				action: "user_registration_form_preview_save",
				id: form_id,
				theme: form_theme,
				security: user_registration_form_preview.form_preview_nonce
			},
			beforeSend: function () {
				var spinner =
					'<span class="ur-spinner is-active" style="margin-left: 20px"></span>';
				$(".ur-form-preview-save").append(spinner);
			},
			complete: function (response) {
				$(".ur-spinner").remove();
				$("#ur-form-save").addClass("hidden");
				// $('.ur-form-preview-save').find('img').remove()
				// if (response.responseJSON.success === true) {
				// 	$(".ur-form-preview-save-title").html(  response.responseJSON.data.message);

				// } else {
				// 	$(".ur-form-preview-save-title").html(  response.responseJSON.data.message);
				// }
			}
		});
	});

	$(document).ready(function () {
		// $('#ur_toggle_form_preview_theme').is(":checked") ? $('link#ur-form-preview-theme-style-css').prop('disabled', true) : $('link#ur-form-preview-default-style-css').prop('disabled', false);
		$("#ur_toggle_form_preview_theme").is(":checked")
			? $(".ur-frontend-form").addClass("ur-frontend-form-preview")
			: $(".ur-frontend-form").removeClass("ur-frontend-form-preview");
	});

	$(document.body).on("click", ".ur-form-preview-upgrade", function () {
		window.open(user_registration_form_preview.pro_upgrade_link, "_blank");
	});
});
