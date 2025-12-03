/**
 * External Dependencies
 */
import React, {useState, useEffect} from "react";
import {__} from "@wordpress/i18n";
import ConditionValueInput from "../inputs/ConditionValueInput";
import {getURCRLocalizedData, getURCRData, isProAccess} from "../../utils/localized-data";

// Get all condition options as a flat array for select dropdown
const getAllConditionOptions = () => {
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

	// Filter options based on pro access
	// For free users, only show membership condition
	return isProAccess()
		? allOptions
		: allOptions.filter(option => option.value === "membership");
};

const ConditionRow = ({
						  condition,
						  onUpdate,
					  }) => {
	const [operator] = useState(condition.operator || "is");
	const [value, setValue] = useState(condition.conditionValue || "");

	useEffect(() => {
		onUpdate({
			...condition,
			operator,
			conditionValue: value,
		});
	}, [operator, value]);

	// Sync value when condition type or field changes
	useEffect(() => {
		if (condition.conditionValue !== undefined) {
			setValue(condition.conditionValue);
		} else {
			const inputType = condition.inputType || condition.type;
			setValue(inputType === "multiselect" ? [] : "");
		}
	}, [condition.inputType, condition.type, condition.value]);


	const handleValueChange = (newValue) => {
		setValue(newValue);
	};

	const handleFieldChange = (e) => {
		const selectedValue = e.target.value;
		const allOptions = getAllConditionOptions();
		const selectedOption = allOptions.find(opt => opt.value === selectedValue);

		if (selectedOption) {
			// Update the condition with new field, label, and inputType
			// Reset the value since the field type changed
			const updatedCondition = {
				...condition,
				value: selectedOption.value,
				label: selectedOption.label,
				inputType: selectedOption.type,
				type: condition.type || "condition", // Preserve type to distinguish from groups
				conditionValue: "", // Reset value when field changes
			};
			setValue(""); // Reset local value
			onUpdate(updatedCondition);
		}
	};


	return (
		<div className="urcr-condition-row ur-d-flex ur-mt-2 ur-align-items-start">
			<div className="urcr-condition-only ur-d-flex ur-align-items-start">
				<div className="urcr-condition-selection-section ur-d-flex ur-align-items-center ur-g-4">
					{/* Field Name (Select Dropdown) */}
					<div className="urcr-condition-field-name">
						<select
							className="components-select-control__input urcr-condition-value-input"
							value={condition.value || ""}
							onChange={handleFieldChange}
						>
							{getAllConditionOptions().map((option) => (
								<option key={option.value} value={option.value}>
									{option.label}
								</option>
							))}
						</select>
					</div>

					{/* Operator  */}
					<div className="urcr-condition-operator">
						<span>{operator}</span>
					</div>

					{/* Value Input */}
					<div className="urcr-condition-value">
						<ConditionValueInput
							type={condition.inputType || condition.type}
							field={condition.value}
							value={value}
							operator={operator}
							onChange={handleValueChange}
							uniqueId={condition.id}
						/>
					</div>
				</div>
			</div>
		</div>
	);
};

export default ConditionRow;

