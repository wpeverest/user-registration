(function ($, urmg_data) {
	var membership_group_object = {
		init: function () {
			membership_group_object.bind_ui_actions();
		},
		/**
		 * Append spinner element.
		 *
		 * @param {jQuery} $element
		 */
		append_spinner: function ($element) {
			if ($element && $element.append) {
				var spinner = '<span class="ur-spinner is-active"></span>';

				$element.append(spinner);
				return true;
			}
			return false;
		},
		/**
		 * Remove spinner elements from a element.
		 *
		 * @param {jQuery} $element
		 */
		remove_spinner: function ($element) {
			if ($element && $element.remove) {
				$element.find('.ur-spinner').remove();
				return true;
			}
			return false;
		},
		bind_ui_actions: function () {
			$(document).on('change', '#ur-setting-form .ur-general-setting-membership_group select', function () {
				var $this = $(this),
					group_id = Number($this.val()),
					loader_container = $('.urmg-loader'),
					urmg_container = $('.urmg-container'),
					empty_urmg = $('.empty-urmg-label');

				$('.ur-general-setting-membership_group select').val(group_id);
				urmg_container.empty();

				if (group_id === 0) {
					empty_urmg.show();
					return;
				}

				// hide memberships and label
				empty_urmg.hide();
				// append spinner
				membership_group_object.append_spinner(loader_container);

				membership_group_object.send_data({
					action: 'user_registration_membership_get_group_memberships',
					group_id: group_id
				}, {
					success: function (response) {
						if (response.success) {
							membership_group_object.handle_membership_by_group_success_response(response.data);
						} else {

						}
					},
					failure: function (xhr, statusText) {

					},
					complete: function () {
						membership_group_object.remove_spinner(loader_container);

					}
				});
			});
		},
		handle_membership_by_group_success_response: function (data) {
			var membership_details = '',
				urmg_container = $('.urmg-container');
			$(data.plans).each(function (k, item) {
				membership_details += '<label><input type="radio" value="' + item.ID + '" disabled/><span>' + item.title + '</span> - <span> ' + item.period + ' </span></label>';
			});
			urmg_container.append(membership_details);
		},
		send_data: function (data, callbacks) {
			var success_callback =
					'function' === typeof callbacks.success ? callbacks.success : function () {
					},
				failure_callback =
					'function' === typeof callbacks.failure ? callbacks.failure : function () {
					},
				beforeSend_callback =
					'function' === typeof callbacks.beforeSend ? callbacks.beforeSend : function () {
					},
				complete_callback =
					'function' === typeof callbacks.complete ? callbacks.complete : function () {
					};

			// Inject default data.
			if (!data._wpnonce && urmg_data) {
				data._wpnonce = urmg_data._nonce;
			}
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: urmg_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback
			});
		}
	};

	$(document).ready(function () {
		membership_group_object.init();
	});
})
(jQuery, window.urmg_localized_data);
