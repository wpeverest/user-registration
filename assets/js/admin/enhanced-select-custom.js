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
			var includeFieldTypes = [
				'text',
				'textarea',
				'description',
				'nickname',
				'user_login',
				'last_name',
				'first_name'
			];

			var optionValues = $("#user_registration_form_setting_blacklisted_words_field_settings");
			if (optionValues.length === 0) {
				return;
			}

			var selectedOptionValues = optionValues.find("option:selected").map(function () {
				return $(this).val();
			}).get();

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
						includeFieldTypes.includes(fieldType)
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
