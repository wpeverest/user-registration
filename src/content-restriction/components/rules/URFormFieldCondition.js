import React, { useState, useEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";

const URFormFieldCondition = ({
	condition,
	onUpdate,
	disabled = false
}) => {
	const [formId, setFormId] = useState("");
	const [formFields, setFormFields] = useState([]);
	const lastSentValueRef = useRef(null);
	const isSyncingFromProps = useRef(false);
	const currentStateRef = useRef({ formId: "", formFields: [] });

	const urForms = getURCRData("ur_forms", {});
	const urFormData = getURCRData("ur_form_data", {});

	useEffect(() => {
		const currentValue = condition.conditionValue;
		
		const normalizedValue = currentValue && typeof currentValue === "object" 
			? {
				form_id: currentValue.form_id || "",
				form_fields: Array.isArray(currentValue.form_fields) ? currentValue.form_fields : []
			}
			: { form_id: "", form_fields: [] };
		
		const lastSentValue = lastSentValueRef.current;
		if (lastSentValue && typeof lastSentValue === "object") {
			const lastSentNormalized = {
				form_id: lastSentValue.form_id || "",
				form_fields: Array.isArray(lastSentValue.form_fields) ? lastSentValue.form_fields : []
			};
			
			if (normalizedValue.form_id === lastSentNormalized.form_id &&
				JSON.stringify(normalizedValue.form_fields) === JSON.stringify(lastSentNormalized.form_fields)) {
				return;
			}
		}
		
		const currentState = currentStateRef.current;
		if (normalizedValue.form_id !== currentState.formId ||
			JSON.stringify(normalizedValue.form_fields) !== JSON.stringify(currentState.formFields)) {
			isSyncingFromProps.current = true;
			setFormId(normalizedValue.form_id);
			setFormFields(normalizedValue.form_fields);
			currentStateRef.current = {
				formId: normalizedValue.form_id,
				formFields: normalizedValue.form_fields
			};
			
			requestAnimationFrame(() => {
				isSyncingFromProps.current = false;
			});
		}
	}, [condition.conditionValue]);

	useEffect(() => {
		if (isSyncingFromProps.current) {
			return;
		}

		const value = {
			form_id: formId || "",
			form_fields: formFields
		};

		currentStateRef.current = {
			formId: value.form_id,
			formFields: value.form_fields
		};

		const lastSentValue = lastSentValueRef.current;
		if (lastSentValue && typeof lastSentValue === "object") {
			const lastSentFormId = lastSentValue.form_id || "";
			const lastSentFormFields = Array.isArray(lastSentValue.form_fields) ? lastSentValue.form_fields : [];
			
			if (value.form_id === lastSentFormId &&
				JSON.stringify(value.form_fields) === JSON.stringify(lastSentFormFields)) {
				return;
			}
		}

		lastSentValueRef.current = value;
		onUpdate({
			...condition,
			conditionValue: value
		});
	}, [formId, formFields, condition, onUpdate]);

	const getFormFieldOptions = () => {
		if (!formId || !urFormData[formId]) {
			return [];
		}
		const fields = urFormData[formId];
		return Object.entries(fields).map(([fieldName, fieldLabel]) => ({
			value: fieldName,
			label: fieldLabel || fieldName
		}));
	};

	const handleFormChange = (e) => {
		const selectedFormId = e.target.value;
		setFormId(selectedFormId);
		if (selectedFormId) {
			setFormFields([{ field_name: "", operator: "is", value: "" }]);
		} else {
			setFormFields([]);
		}
	};

	const handleAddField = () => {
		const newField = {
			field_name: "",
			operator: "is",
			value: ""
		};
		setFormFields([...formFields, newField]);
	};

	const handleRemoveField = (index) => {
		setFormFields(formFields.filter((_, i) => i !== index));
	};

	const handleFieldUpdate = (index, fieldData) => {
		const updatedFields = formFields.map((field, i) =>
			i === index ? { ...field, ...fieldData } : field
		);
		setFormFields(updatedFields);
	};

	const operatorOptions = [
		{ value: "is", label: __("is", "user-registration") },
		{ value: "is not", label: __("is not", "user-registration") },
		{ value: "empty", label: __("empty", "user-registration") },
		{ value: "not empty", label: __("not empty", "user-registration") }
	];

	const formOptions = Object.entries(urForms).map(([id, label]) => ({
		value: id,
		label: label || id
	}));

	const formFieldOptions = getFormFieldOptions();

	return (
		<div className="urcr-ur-form-field-condition">
			<div className="urcr-form-selection ur-d-flex ur-align-items-center ur-g-4 ur-mb-2">
				<select
					className="components-select-control__input urcr-condition-value-input"
					value={formId}
					onChange={handleFormChange}
					disabled={disabled}
				>
					<option value="">{__("Select a form", "user-registration")}</option>
					{formOptions.map((option) => (
						<option key={option.value} value={option.value}>
							{option.label}
						</option>
					))}
				</select>
			</div>

			{formId && (
				<div className="urcr-form-fields-list">
					{formFields.map((field, index) => (
						<div key={index} className="urcr-form-field-row ur-d-flex ur-align-items-center ur-mb-2">
							<div className="urcr-form-field-name">
								<select
									className="components-select-control__input urcr-condition-value-input"
									value={field.field_name || ""}
									onChange={(e) => handleFieldUpdate(index, { field_name: e.target.value })}
									disabled={disabled}
								>
									<option value="">{__("Select field", "user-registration")}</option>
									{formFieldOptions.map((option) => (
										<option key={option.value} value={option.value}>
											{option.label}
										</option>
									))}
								</select>
							</div>

							<div className="urcr-form-field-operator">
								<select
									className="components-select-control__input urcr-condition-value-input"
									value={field.operator || "is"}
									onChange={(e) => handleFieldUpdate(index, { operator: e.target.value, value: (e.target.value === "empty" || e.target.value === "not empty") ? "" : field.value })}
									disabled={disabled}
								>
									{operatorOptions.map((option) => (
										<option key={option.value} value={option.value}>
											{option.label}
										</option>
									))}
								</select>
							</div>

							{(field.operator !== "empty" && field.operator !== "not empty") && (
								<div className="urcr-form-field-value ur-flex-1" style={{ minWidth: 0 }}>
									<input
										type="text"
										className="components-text-control__input urcr-condition-value-input urcr-condition-value-text"
										value={field.value || ""}
										onChange={(e) => handleFieldUpdate(index, { value: e.target.value })}
										placeholder={__("Enter value", "user-registration")}
										disabled={disabled}
										style={{ width: "150px" }}
									/>
								</div>
							)}

							<button
								type="button"
								className="button urcr-add-field-button"
								onClick={handleAddField}
								disabled={disabled}
								aria-label={__("Add field", "user-registration")}
								style={{ minWidth: "32px", width: "32px", padding: "0" }}
							>
								<span className="dashicons dashicons-plus-alt2"></span>
							</button>

							<button
								type="button"
								className="button urcr-remove-field-button"
								onClick={() => handleRemoveField(index)}
								disabled={disabled || formFields.length === 1}
								aria-label={__("Remove field", "user-registration")}
								style={{ minWidth: "32px", width: "32px", padding: "0" }}
							>
								<span className="dashicons dashicons-minus"></span>
							</button>
						</div>
					))}
				</div>
			)}
		</div>
	);
};

export default URFormFieldCondition;

