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
 * @returns {Array} Filtered array of condition options
 */
export const getFilteredConditionOptions = (
	isMigrated = false,
	ruleType = null,
	isFirstCondition = false
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
		{value: "registration_source", label: __("User Registration Source", "user-registration"), type: "multiselect"},
		{value: "ur_form_field", label: __("UR Form Field", "user-registration"), type: "multiselect"},
		{value: "payment_status", label: __("Payment Status", "user-registration"), type: "multiselect"},
	]);

	// Filter options based on pro access and migration status
	const isPro = isProAccess();
	const isMembershipRule = ruleType === "membership";
	
	// Show membership option only for the first condition of membership rules, hide it for all other cases
	const shouldShowMembership = isMembershipRule && isFirstCondition;
	
	let filteredOptions;
	
	// Pro users: show all conditions
	if (isPro) {
		filteredOptions = allOptions;
	}
	// Free users: show user_state and roles for both membership and custom rules
	// For membership rules, also show membership option if it's the first condition
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
	
	return filteredOptions;
};

