/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { getFilteredConditionOptions } from "../../utils/condition-options";
import DropdownMenu from "./DropdownMenu";

// Group options by category
const groupOptions = (options) => {
	const groups = {
		"Membership": [],
		"User Based": [],
		"User Assets Based": [],
		"Others": []
	};

	options.forEach(option => {
		if (option.value === "membership") {
			groups["Membership"].push(option);
		} else if (["roles", "user_registered_date", "access_period", "user_state"].includes(option.value)) {
			groups["User Based"].push(option);
		} else if (["email_domain", "post_count"].includes(option.value)) {
			groups["User Assets Based"].push(option);
		} else {
			groups["Others"].push(option);
		}
	});

	// Convert to array format and filter out empty groups
	return Object.entries(groups)
		.filter(([group, opts]) => opts.length > 0)
		.map(([group, opts]) => ({
			group: __(group, "user-registration"),
			options: opts
		}));
};

const ConditionFieldDropdown = ({ onSelect, isMigrated = false, ruleType = null, isFirstCondition = false }) => {
	// Use shared utility function to get filtered condition options
	// Pass forSelectField=false to exclude membership for migrated rules in dropdown
	const filteredOptions = getFilteredConditionOptions(isMigrated, ruleType, isFirstCondition, null, false);
	const options = groupOptions(filteredOptions);
	return (
		<DropdownMenu
			options={options}
			onSelect={onSelect}
			grouped={true}
		/>
	);
};

export default ConditionFieldDropdown;
