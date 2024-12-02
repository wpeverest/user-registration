jQuery(document).ready(function () {
	jQuery(".multiple-select").select2();
	jQuery(".multiple-select").select2({
		dropdownAutoWidth: true,
		containerCss: { display: "block" },
		width: "20%"
	});

	jQuery("#urcr_meta_override_global_settings").on("change", function (e) {
		if (jQuery("#urcr_meta_override_global_settings").is(":checked")) {
			jQuery(".urcr_allow_to_field").show();
			if (jQuery("#urcr_allow_to").val() == "1") {
				jQuery(".urcr-multiple-select").show();
			} else {
				jQuery(".urcr-multiple-select").hide();
			}
		} else {
			jQuery(".urcr_allow_to_field, .urcr_meta_roles_field").hide();
		}
	});

	jQuery("#urcr_allow_to").on("change", function () {
		if (this.value == "1") {
			jQuery(".urcr_meta_roles_field").show();
		} else {
			jQuery(".urcr_meta_roles_field").hide();
		}
	});

	jQuery(window).load(function () {
		if (jQuery("#urcr_meta_override_global_settings").is(":checked")) {
			if (jQuery("#urcr_allow_to").val() == "1") {
			} else {
				jQuery(".urcr_meta_roles_field").hide();
			}
		} else {
			jQuery(".urcr_allow_to_field, .urcr_meta_roles_field ").hide();
		}
	});

	jQuery(function () {
		var mySelect = jQuery(
			"#user_registration_content_restriction_allow_access_to option:selected"
		).val();

		if (mySelect == "1") {
			jQuery("#user_registration_content_restriction_allow_to_roles")
				.parent()
				.parent()
				.show();
		} else {
			jQuery("#user_registration_content_restriction_allow_to_roles")
				.parent()
				.parent()
				.hide();
		}

		jQuery("body").on(
			"select2:select",
			"#user_registration_content_restriction_allow_access_to",
			function () {
				if (jQuery(this).find("option:selected").val() == "1") {
					jQuery(
						"#user_registration_content_restriction_allow_to_roles"
					)
						.parent()
						.parent()
						.show();
				} else {
					jQuery(
						"#user_registration_content_restriction_allow_to_roles"
					)
						.parent()
						.parent()
						.hide();
				}
			}
		);
	});
});
