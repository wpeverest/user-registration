/**
 * UserRegistration Black List Logic Admin JS
 */
jQuery(function ($) {
	var urblUtils = {
		init: function () {
			$(document.body).on(
				"ur_rendered_field_options ur_new_field_created",
				function () {
					urblUtils.populateBlacklistOptions();
				}
			);
		},

		// Custom options field starts
		populateBlacklistOptions: function () {
			var excludeIds = [];
			var excludeFieldTypes = [
				'section_title',
				'html',
				'wysiwyg',
				'billing_address_title',
				'shipping_address_title',
				'stripe_gateway',
				'authorize_net_gateway',
				'profile_picture',
				'file',
			];

			var optionValues = $("#user_registration_form_setting_blacklisted_words_field_settings");
			if (optionValues.length === 0) {
				return;
			}

			let selectedOptionValues = optionValues.find("option:selected").map(function () {
				return $(this).val();
			}).get();

			console.log('selected: ' + selectedOptionValues);

			// Clear existing options
			optionValues.empty();

			$(".ur-selected-item").each(function () {
				var fieldType = $(this).find(".ur-field").data("field-key");
				var fieldLabel = $(this)
					.find(".ur-general-setting-label .ur-general-setting-field")
					.val();
				var fieldId = $(this)
					.find(
						".ur-general-setting-field-name .ur-general-setting-field"
					)
					.val();
				if (fieldType && fieldLabel && fieldId) {
					if (
						!excludeIds.includes(fieldId) &&
						!excludeFieldTypes.includes(fieldType)
					) {
						var newOption = $('<option>', {
							value: fieldType,
							text: fieldLabel,
							selected: selectedOptionValues.includes(fieldType)
						});

						optionValues.append(newOption);
					}
				}
			});

			optionValues.val(selectedOptionValues);
		}
		// Custom options fields ends
	};
	urblUtils.init();

	$(document).on(
		"change",
		".select .ur-enhanced-select .select2-hidden-accessible .enhanced, .select2-hidden-accessible",
		function () {
			urblUtils.populateBlacklistOptions();
		}
	);

	urblUtils.populateBlacklistOptions();
});
