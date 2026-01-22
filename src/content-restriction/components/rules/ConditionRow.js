/**
 * External Dependencies
 */
import React, { useState, useEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import ConditionValueInput from "../inputs/ConditionValueInput";
import URFormFieldCondition from "./URFormFieldCondition";
import { getFilteredConditionOptions } from "../../utils/condition-options";

const ConditionRow = ({
	condition,
	onUpdate,
	isMigrated = false,
	isLocked = false,
	ruleType = null,
	isFirstCondition = false
}) => {
	const [operator] = useState(condition.operator || "is");
	const [value, setValue] = useState(condition.conditionValue || "");
	const selectRef = useRef(null);

	// Get the selected condition option to access operator_label and placeholder
	const allOptions = getFilteredConditionOptions(
		isMigrated,
		ruleType,
		isFirstCondition,
		condition.value,
		true
	);
	const selectedOption = allOptions.find(
		(opt) => opt.value === condition.value
	);
	const operatorLabel = selectedOption?.operator_label || operator;
	const placeholder = selectedOption?.placeholder || "";

	useEffect(() => {
		onUpdate({
			...condition,
			operator,
			conditionValue: value
		});
	}, [operator, value]);

	useEffect(() => {
		if (condition.conditionValue !== undefined) {
			setValue(condition.conditionValue);
		} else {
			const inputType = condition.inputType || condition.type;
			setValue(inputType === "multiselect" ? [] : "");
		}
	}, [condition.inputType, condition.type, condition.value]);

	// Ensure select stays in sync with condition.value
	useEffect(() => {
		if (selectRef.current && selectRef.current.value !== condition.value) {
			selectRef.current.value = condition.value || "";
		}
	}, [condition.value]);

	const handleValueChange = (newValue) => {
		setValue(newValue);
	};

	const handleFieldChange = (e) => {
		const selectedValue = e.target.value;
		const allOptions = getFilteredConditionOptions(
			isMigrated,
			ruleType,
			isFirstCondition,
			condition.value,
			true
		);
		const selectedOption = allOptions.find(
			(opt) => opt.value === selectedValue
		);

		if (selectedOption) {
			let initialValue = "";
			if (selectedOption.value === "ur_form_field") {
				initialValue = { form_id: "", form_fields: [] };
			} else if (selectedOption.type === "multiselect") {
				initialValue = [];
			} else {
				initialValue = "";
			}
			
			const updatedCondition = {
				...condition,
				value: selectedOption.value,
				label: selectedOption.label,
				inputType: selectedOption.type,
				type: condition.type || "condition",
				conditionValue: initialValue
			};
			setValue(initialValue);
			onUpdate(updatedCondition);
		}
	};

	if (condition.value === "ur_form_field") {
		return (
			<div className="urcr-condition-row ur-d-flex ur-mt-2 ur-align-items-start">
				<div className="urcr-condition-only ur-d-flex ur-align-items-start">
					<div className="urcr-condition-selection-section ur-d-flex ur-align-items-center ur-g-4">
						<div className="urcr-condition-field-name">
							<select
								ref={selectRef}
								className="components-select-control__input urcr-condition-value-input"
								value={condition.value || ""}
								onChange={handleFieldChange}
								disabled={isLocked}
							>
								{getFilteredConditionOptions(
									isMigrated,
									ruleType,
									isFirstCondition,
									condition.value,
									true
								).map((option) => (
									<option
										key={option.value}
										value={option.value}
									>
										{option.label}
									</option>
								))}
							</select>
						</div>
						<div className="urcr-condition-operator ur-align-self-center">
							<span>{operatorLabel}</span>
						</div>
						<div className="urcr-condition-value ur-flex-1">
							<URFormFieldCondition
								condition={condition}
								onUpdate={onUpdate}
								disabled={isLocked}
							/>
						</div>
					</div>
				</div>
			</div>
		);
	}

	return (
		<div className="urcr-condition-row ur-d-flex ur-mt-2 ur-align-items-start">
			<div className="urcr-condition-only ur-d-flex ur-align-items-start">
				<div className="urcr-condition-selection-section ur-d-flex ur-align-items-center ur-g-4">
					<div className="urcr-condition-field-name">
						<select
							ref={selectRef}
							className="components-select-control__input urcr-condition-value-input"
							value={condition.value || ""}
							onChange={handleFieldChange}
							disabled={isLocked}
						>
							{getFilteredConditionOptions(
								isMigrated,
								ruleType,
								isFirstCondition,
								condition.value,
								true
							).map((option) => (
								<option key={option.value} value={option.value}>
									{option.label}
								</option>
							))}
						</select>
					</div>

					
					<div className="urcr-condition-operator">
						<span>{operatorLabel}</span>
					</div>
					

					<div className="urcr-condition-value">
						<ConditionValueInput
							type={condition.inputType || condition.type}
							field={condition.value}
							value={value}
							operator={operator}
							onChange={handleValueChange}
							uniqueId={condition.id}
							disabled={isLocked}
							placeholder={placeholder}
						/>
					</div>
				</div>
			</div>
		</div>
	);
};

export default ConditionRow;
