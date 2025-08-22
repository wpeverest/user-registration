jQuery(document).ready(function () {
	// Initialize Select2
	var $multipleSelect = jQuery('.multiple-select');
	$multipleSelect.select2({
		dropdownAutoWidth: true,
		containerCss: { display: 'block' },
		width: '20%'
	});

	var $metaOverride = jQuery('#urcr_meta_override_global_settings'),
		$allowTo = jQuery('#urcr_allow_to'),
		$allowToField = jQuery('.urcr_allow_to_field'),
		$rolesField = jQuery('.urcr_meta_roles_field'),
		$membershipsField = jQuery('.urcr_meta_memberships_field');
		$restrictedMessage = jQuery('.urcr_meta_content_field');

	// Function to toggle visibility based on global override checkbox
	function toggleGlobalOverride() {
		if ($metaOverride.is(':checked')) {
			$allowToField.show();
			$restrictedMessage.show();
			toggleFieldsBasedOnAllowTo();
		} else {
			$allowToField.hide();
			$rolesField.hide();
			$membershipsField.hide();
			$restrictedMessage.hide();
		}
	}

	// Function to toggle fields based on the 'Allow To' selection
	function toggleFieldsBasedOnAllowTo() {
		var allowToValue = $allowTo.val();
		$rolesField.hide();
		$membershipsField.hide();

		if (allowToValue === '1') {
			$rolesField.show();
		} else if (allowToValue === '3') {
			$membershipsField.show();
		}
	}

	// Event Listeners
	$metaOverride.on('change', toggleGlobalOverride);
	$allowTo.on('change', toggleFieldsBasedOnAllowTo);

	// Initial Setup on Page Load
	// jQuery(window).on('load', toggleGlobalOverride);
	toggleGlobalOverride();

	// Content Restriction Section
	var $allowAccessTo = jQuery('#user_registration_content_restriction_allow_access_to');
	var $rolesInput = jQuery('#user_registration_content_restriction_allow_to_roles').closest('.user-registration-global-settings');
	var $membershipsInput = jQuery('#user_registration_content_restriction_allow_to_memberships').closest('.user-registration-global-settings');

	function toggleContentRestrictionFields() {
		var selectedValue = $allowAccessTo.val();
		$rolesInput.hide();
		$membershipsInput.hide();

		if (selectedValue === '1') {
			$rolesInput.show();
		} else if (selectedValue === '3') {
			$membershipsInput.show();
		}
	}

	// Initialize Content Restriction Fields
	toggleContentRestrictionFields();

	// Event Listener for Select2 Change
	jQuery('body').on('select2:select', '#user_registration_content_restriction_allow_access_to', toggleContentRestrictionFields);
});
