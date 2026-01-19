import React, { useState, useEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";

const URMCondition = ({
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
	const registrationSources = getURCRData("registration_sources", {});

	const allFormOptions = { ...urForms, ...registrationSources };

	useEffect(() => {
		const currentValue = condition.conditionValue;
		
		let normalizedValue = { form_id: "", form_fields: [] };
		
		if (currentValue) {
			if (typeof currentValue === "object" && currentValue.form_id) {
				normalizedValue = {
					form_id: currentValue.form_id || "",
					form_fields: Array.isArray(currentValue.form_fields) ? currentValue.form_fields : []
				};
			} else if (Array.isArray(currentValue) && currentValue.length > 0) {
				normalizedValue = {
					form_id: currentValue[0] || "",
					form_fields: []
				};
			} else if (typeof currentValue === "string" && currentValue) {
				normalizedValue = {
					form_id: currentValue,
					form_fields: []
				};
			}
		}
		
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

		let value;
		if (formFields.length > 0 && formFields.some(f => f.field_name && f.operator)) {
			value = {
				form_id: formId || "",
				form_fields: formFields.filter(f => f.field_name && f.operator)
			};
		} else if (formId) {
			value = formId;
		} else {
			value = "";
		}

		currentStateRef.current = {
			formId: formId || "",
			formFields: formFields
		};

		const lastSentValue = lastSentValueRef.current;
		if (lastSentValue) {
			if (typeof value === "object" && typeof lastSentValue === "object") {
				const lastSentFormId = lastSentValue.form_id || "";
				const lastSentFormFields = Array.isArray(lastSentValue.form_fields) ? lastSentValue.form_fields : [];
				
				if (value.form_id === lastSentFormId &&
					JSON.stringify(value.form_fields) === JSON.stringify(lastSentFormFields)) {
					return;
				}
			} else if (value === lastSentValue) {
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
			setFormFields([]);
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

	const formOptions = Object.entries(allFormOptions).map(([id, label]) => ({
		value: id,
		label: label || id
	}));

	const formFieldOptions = getFormFieldOptions();

	return (
		<div className="urcr-urm-condition">
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
				{formId && (
					<button
						type="button"
						className="button urcr-add-form-fields-button"
						onClick={handleAddField}
						disabled={disabled}
					>
						<span className="dashicons dashicons-plus-alt2"></span>
						{__("Form Fields", "user-registration")}
					</button>
				)}
			</div>

			{formId && (
				<div className="urcr-form-fields-list">
					{formFields.map((field, index) => (
						<div key={index} className="urcr-form-field-row" style={{ display: "flex", flexDirection: "column", marginBottom: "10px" }}>
							<div className="urcr-form-field-name" style={{ width: "100%", marginBottom: "8px" }}>
								<select
									className="components-select-control__input urcr-condition-value-input"
									value={field.field_name || ""}
									onChange={(e) => handleFieldUpdate(index, { field_name: e.target.value })}
									disabled={disabled}
									style={{ width: "100%" }}
								>
									<option value="">{__("Select field", "user-registration")}</option>
									{formFieldOptions.map((option) => (
										<option key={option.value} value={option.value}>
											{option.label}
										</option>
									))}
								</select>
							</div>

							<div className="urcr-form-field-operator" style={{ width: "100%", marginBottom: "8px" }}>
								<select
									className="components-select-control__input urcr-condition-value-input"
									value={field.operator || "is"}
									onChange={(e) => handleFieldUpdate(index, { operator: e.target.value, value: (e.target.value === "empty" || e.target.value === "not empty") ? "" : field.value })}
									disabled={disabled}
									style={{ width: "100%" }}
								>
									{operatorOptions.map((option) => (
										<option key={option.value} value={option.value}>
											{option.label}
										</option>
									))}
								</select>
							</div>

							{(field.operator !== "empty" && field.operator !== "not empty") && (
								<div className="urcr-form-field-value" style={{ width: "100%", marginBottom: "8px" }}>
									<input
										type="text"
										className="components-text-control__input urcr-condition-value-input urcr-condition-value-text"
										value={field.value || ""}
										onChange={(e) => handleFieldUpdate(index, { value: e.target.value })}
										placeholder={__("Enter value", "user-registration")}
										disabled={disabled}
										style={{ width: "100%" }}
									/>
								</div>
							)}

							<div style={{ display: "flex", gap: "5px" }}>
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
						</div>
					))}
				</div>
			)}
		</div>
	);
};

export default URMCondition;

