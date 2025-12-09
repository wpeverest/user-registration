/**
 * External Dependencies
 */
import React, {useState} from "react";
import {__} from "@wordpress/i18n";
import {getURCRData, isProAccess} from "../../utils/localized-data";

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

const ConditionFieldDropdown = ({onSelect, isMigrated = false}) => {
	const [selectedValue, setSelectedValue] = useState("");

	// Get condition options from localized data
	const allConditionOptions = getURCRData("condition_options", []);
	
	// Filter options based on pro access and migration status
	const isMigratedBool = Boolean(isMigrated);
	const isPro = isProAccess();
	
	let filteredOptions;
	
	// Pro users: show all conditions
	if (isPro) {
		filteredOptions = allConditionOptions;
	}
	// Free users with migrated rules: show only user_state, roles, and membership
	else if (isMigratedBool) {
		filteredOptions = allConditionOptions.filter(option => 
			option.value === "membership" || 
			option.value === "roles" || 
			option.value === "user_state"
		);
	}
	// Free users with non-migrated rules: only show membership
	else {
		filteredOptions = allConditionOptions.filter(option => option.value === "membership");
	}

	const options = groupOptions(filteredOptions);

	const handleOptionClick = (option) => {
		setSelectedValue(option.value);
		if (onSelect) {
			onSelect(option);
		}
	};

	return (
		<div className="urcr-condition-field-dropdown-menu">
			{options.map((group, groupIndex) => (
				<div key={groupIndex} className="urcr-condition-field-dropdown-group">
					<div className="urcr-condition-field-dropdown-group-label">{group.group}</div>
					{group.options.map((option) => (
						<span
							key={option.value}
							role="button"
							tabIndex={0}
							className={`urcr-condition-field-dropdown-option ${
								selectedValue === option.value ? "is-selected" : ""
							}`}
							onClick={(e) => {
								e.stopPropagation();
								handleOptionClick(option);
							}}
							onKeyDown={(e) => {
								if (e.key === 'Enter' || e.key === ' ') {
									e.preventDefault();
									e.stopPropagation();
									handleOptionClick(option);
								}
							}}
						>
							{option.label}
						</span>
					))}
				</div>
			))}
		</div>
	);
};

export default ConditionFieldDropdown;

