/**
 * Utility functions for filtering condition options
 */
import { __ } from "@wordpress/i18n";
import { getURCRData, isProAccess } from "./localized-data";

/**
 * Get filtered condition options based on pro access, migration status, rule type, and condition position
 *
 * @param {boolean} isMigrated - Whether the rule is migrated
 * @param {string|null} ruleType - Type of rule ('membership' or null for custom)
 * @param {boolean} isFirstCondition - Whether this is the first condition
 * @param {string|null} currentValue - Current selected value (for select field to include current option)
 * @param {boolean} forSelectField - Whether this is for the select field (true) or dropdown (false)
 * @returns {Array} Filtered array of condition options
 */
export const getFilteredConditionOptions = (
	isMigrated = false,
	ruleType = null,
	isFirstCondition = false,
	currentValue = null,
	forSelectField = false
) => {
	// Get condition options from localized data
	const allOptions = getURCRData("condition_options", [
		{value: "membership", label: __("Membership", "user-registration"), type: "multiselect"},
		{value: "roles", label: __("Roles", "user-registration"), type: "multiselect"},
		{value: "user_registered_date", label: __("User Registered Date", "user-registration"), type: "date"},
		{value: "access_period", label: __("Period after Registration", "user-registration"), type: "period"},
		{value: "user_state", label: __("User State", "user-registration"), type: "checkbox"},
		{value: "email_domain", label: __("Email Domain", "user-registration"), type: "text"},
		{value: "post_count", label: __("Minimum Public Posts Count", "user-registration"), type: "number"},
		{value: "capabilities", label: __("Capabilities", "user-registration"), type: "multiselect"},
		{value: "registration_source", label: __("Registration Source", "user-registration"), type: "multiselect"},
		{value: "ur_form_field", label: __("UR Form Field", "user-registration"), type: "multiselect"},
	]);

	// Filter options based on pro access and migration status
	const isPro = isProAccess();
	const isMembershipRule = ruleType === "membership";

	// For select field: show membership only if it's the current value of this specific condition
	// For dropdown: show membership only for first condition of membership rules (NOT for migrated)
	const shouldShowMembershipInDropdown = isMembershipRule && isFirstCondition;
	const shouldShowMembershipInSelect = (isMembershipRule && isFirstCondition) || (currentValue === "membership" && forSelectField);
	
	// Use different logic for dropdown vs select field
	const shouldShowMembership = forSelectField ? shouldShowMembershipInSelect : shouldShowMembershipInDropdown;

	let filteredOptions;

	// Pro users: show all conditions
	if (isPro) {
		filteredOptions = allOptions;
	}
	// Free users: show user_state and roles for both membership and custom rules
	// For membership rules, also show membership option if it should be shown
	else {
		filteredOptions = allOptions.filter(option =>
			option.value === "roles" ||
			option.value === "user_state" ||
			(shouldShowMembership && option.value === "membership")
		);
	}

	// Hide membership option if it shouldn't be shown
	if (!shouldShowMembership) {
		filteredOptions = filteredOptions.filter(option => option.value !== "membership");
	}

	// For select field: always include current value if it exists (to prevent losing selection)
	if (forSelectField && currentValue) {
		const currentOption = allOptions.find(opt => opt.value === currentValue);
		if (currentOption && !filteredOptions.find(opt => opt.value === currentValue)) {
			filteredOptions.push(currentOption);
		}
	}

	return filteredOptions;
};

