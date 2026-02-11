/**
 * URM deactivation popup
 */
(function ($) {
	"use strict";

	function initUrmPopup() {
		var $popup = $("#user-registration_uninstall_feedback_popup");
		if (!$popup.length || $popup.hasClass("urm-popup-initialized")) {
			return;
		}

		$popup.addClass("urm-popup-styled urm-popup-initialized");

		var $header = $popup.find(".popup--header");
		var $h5 = $header.find("h5");
		var $body = $popup.find(".popup--body");

		var logoUrl =
			(window.urmDeactivationPopup &&
				window.urmDeactivationPopup.logoUrl) ||
			"";
		var logoHtml = logoUrl
			? '<span class="urm-popup-logo"><img src="' +
			  logoUrl +
			  '" alt="User Registration"></span>'
			: '<span class="urm-popup-logo">U</span>';
		var headerInner =
			'<div class="urm-popup-header-inner">' +
			logoHtml +
			'<span class="urm-popup-title">' +
			((window.urmDeactivationPopup &&
				window.urmDeactivationPopup.quickFeedback) ||
				"Quick Feedback") +
			"</span>" +
			"</div>" +
			'<button type="button" class="urm-popup-close" aria-label="Close">&times;</button>';
		$header.prepend(headerInner);

		var questionText = $h5.text().trim();
		if (questionText) {
			$body.prepend(
				'<p class="urm-popup-question">' + questionText + "</p>"
			);
		}
		$h5.remove();

		var disclaimer =
			(window.urmDeactivationPopup &&
				window.urmDeactivationPopup.disclaimer) ||
			"* By submitting this form, you will send us non-sensitive diagnostic data, site URL and email.";
		// $body.append('<p class="urm-popup-disclaimer">' + disclaimer + "</p>");

		var $close = $popup.find(".urm-popup-close");
		var targetSelector =
			'tr[data-plugin^="user-registration/"] span.deactivate a';
		$close.on("click", function () {
			var $target = $(targetSelector);
			$popup.removeClass("active");
			$("body").removeClass("tgsdk-feedback-open");
			if ($target.length && $target.attr("href")) {
				window.location.href = $target.attr("href");
			}
		});
	}

	$(document).ready(function () {
		initUrmPopup();
	});
})(jQuery);
