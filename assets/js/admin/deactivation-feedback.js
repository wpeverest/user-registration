/* global ur_plugins_params */
jQuery(function ($) {
	var ur_deactivation_feedback = {
		init: function () {
			this.event_init();
		},
		event_init: function () {
			var _that = this;
			$(document.body).on(
				"click",
				'tr[data-plugin="user-registration/user-registration.php"] span.deactivate a',
				function (e) {
					e.preventDefault();
					$('#ur-deactivate-feedback-popup-wrapper').addClass('active');
				});
			$('#ur-deactivate-feedback-popup-wrapper').click(function (event) {
				var $target = $(event.target);
				if (!$target.closest('.ur-deactivate-feedback-popup-inner').length) {
					$('#ur-deactivate-feedback-popup-wrapper').removeClass('active');
				}
			});
			$('form.ur-deactivate-feedback-form').on('submit', function (e) {
				e.preventDefault();
				_that.send_data($(this));
			});
		},
		send_data: function (form) {
			var reason_slug = form.find('input[name="reason_slug"]:checked').val();
			if (reason_slug === undefined) {
				alert('Please select at least one option from the list');
				return;
			}
			if (form.find('button.submit').hasClass('button-disabled')) {
				return;
			}
			var reason_text = '';
			var reason_text_el = form.find('input[name="reason_' + reason_slug + '"]');
			if (reason_text_el.length > 0) {
				reason_text = reason_text_el.val();
			}
			var data = {
				reason_slug: "user_registration_deactivation_notice",
			};
			data[('reason_' + reason_slug)] = reason_text;
			$.ajax({
				url: ur_plugins_params.ajax_url,
				data: form.serializeArray(),
				type: "post",
				beforeSend: function () {
					form.find('button.submit').addClass('button-disabled button updating-message');
				},

			}).done(function () { //use this
				window.location = form.find('a.skip').attr('href');
			});
		}
	};

	ur_deactivation_feedback.init();

});
