/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";

const CheckboxRadioInput = ({ field, value, onChange, uniqueId, disabled = false }) => {
	const labels = getURCRData("labels", {});
	
	// Get options based on field
	const getOptions = () => {
		switch (field) {
			case "user_state":
				return [
					{
						value: "logged-in",
						label: labels.logged_in || __("Logged In", "user-registration")
					},
					{
						value: "logged-out",
						label: labels.logged_out || __("Logged Out", "user-registration")
					},
				];
			default:
				return [];
		}
	};

	const options = getOptions();
	let currentValue = Array.isArray(value) ? (value[0] || "") : (value || "");
	
	// Convert old format (logged_in/logged_out) to new format (logged-in/logged-out) for user_state
	if (field === "user_state") {
		if (currentValue === "logged_in") {
			currentValue = "logged-in";
		} else if (currentValue === "logged_out") {
			currentValue = "logged-out";
		}
	}

	const handleChange = (optionValue) => {
		onChange(optionValue);
	};

	// Generate unique name for radio group using uniqueId or fallback to timestamp
	const radioName = uniqueId ? `urcr-radio-${field}-${uniqueId}` : `urcr-radio-${field}-${Date.now()}`;

	return (
		<div className="urcr-checkbox-radio-group">
			{options.map((option) => {
				const isChecked = currentValue === option.value;
				return (
					<label
						key={option.value}
						className={`urcr-checkbox-radio-option ${isChecked ? "is-checked" : ""}`}
					>
						<input
							type="radio"
							name={radioName}
							value={option.value}
							checked={isChecked}
							onChange={(e) => handleChange(e.target.value)}
							className="urcr-checkbox-radio-input"
							disabled={disabled}
						/>
						<span className="urcr-checkbox-radio-label">{option.label}</span>
					</label>
				);
			})}
		</div>
	);
};

export default CheckboxRadioInput;

