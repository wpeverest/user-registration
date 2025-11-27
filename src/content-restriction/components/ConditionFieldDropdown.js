/**
 * External Dependencies
 */
import React, {useState} from "react";
import {__} from "@wordpress/i18n";

const ConditionFieldDropdown = ({onSelect}) => {
	const [selectedValue, setSelectedValue] = useState("");

	const options = [
		{
			group: __("Membership", "user-registration"),
			options: [
				{value: "membership", label: __("Membership", "user-registration"), type: "multiselect"},
			],
		},
		{
			group: __("User Based", "user-registration"),
			options: [
				{value: "roles", label: __("Roles", "user-registration"), type: "multiselect"},
				{value: "user_registered_date", label: __("User Registered Date", "user-registration"), type: "date"},
				{value: "access_period", label: __("Period after Registration", "user-registration"), type: "period"},
				{value: "user_state", label: __("User State", "user-registration"), type: "checkbox"},
			],
		},
		{
			group: __("User Assets Based", "user-registration"),
			options: [
				{value: "email_domain", label: __("Email Domain", "user-registration"), type: "text"},
				{value: "post_count", label: __("Minimum Public Posts Count", "user-registration"), type: "number"},
			],
		},
		{
			group: __("Others", "user-registration"),
			options: [
				{value: "capabilities", label: __("Capabilities", "user-registration"), type: "multiselect"},
				{
					value: "registration_source",
					label: __("User Registration Source", "user-registration"),
					type: "multiselect"
				},
				{value: "ur_form_field", label: __("UR Form Field", "user-registration"), type: "multiselect"},
				{value: "payment_status", label: __("Payment Status", "user-registration"), type: "multiselect"},
			],
		},
	];

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

