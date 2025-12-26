/**
 * External Dependencies
 */
import React, {useState, useEffect} from "react";
import {__} from "@wordpress/i18n";
import ConditionValueInput from "../inputs/ConditionValueInput";
import {getFilteredConditionOptions} from "../../utils/condition-options";

const ConditionRow = ({
						  condition,
						  onUpdate,
						  isMigrated = false,
						  isLocked = false,
						  ruleType = null,
						  isFirstCondition = false,
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
		const allOptions = getFilteredConditionOptions(isMigrated, ruleType, isFirstCondition);
		const selectedOption = allOptions.find(opt => opt.value === selectedValue);

		if (selectedOption) {
			const updatedCondition = {
				...condition,
				value: selectedOption.value,
				label: selectedOption.label,
				inputType: selectedOption.type,
				type: condition.type || "condition",
				conditionValue: "",
			};
			setValue("");
			onUpdate(updatedCondition);
		}
	};


	return (
		<div className="urcr-condition-row ur-d-flex ur-mt-2 ur-align-items-start">
			<div className="urcr-condition-only ur-d-flex ur-align-items-start">
				<div className="urcr-condition-selection-section ur-d-flex ur-align-items-center ur-g-4">
					<div className="urcr-condition-field-name">
						<select
							className="components-select-control__input urcr-condition-value-input"
							value={condition.value || ""}
							onChange={handleFieldChange}
							disabled={isLocked}
						>
							{getFilteredConditionOptions(isMigrated, ruleType, isFirstCondition).map((option) => (
								<option key={option.value} value={option.value}>
									{option.label}
								</option>
							))}
						</select>
					</div>

					<div className="urcr-condition-operator">
						<span>{operator}</span>
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
						/>
					</div>
				</div>
			</div>
		</div>
	);
};

export default ConditionRow;

