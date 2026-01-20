/**
 * External Dependencies
 */
import React, { useState, useEffect, useLayoutEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRLocalizedData, getURCRData } from "../../utils/localized-data";
import CheckboxRadioInput from "./CheckboxRadioInput";
import PeriodInput from "./PeriodInput";
import DateRangeInput from "./DateRangeInput";

const ConditionValueInput = ({ type, field, value, operator, onChange, uniqueId, disabled = false, placeholder = "" }) => {
	const urcrData = getURCRLocalizedData();
	
	// Normalize initial value: for checkbox, ensure it's a scalar; for multiselect, ensure it's an array
	const normalizeValue = (val, inputType) => {
		if (inputType === "multiselect") {
			return Array.isArray(val) ? val : (val ? [val] : []);
		} else if (inputType === "checkbox") {
			// For checkbox, always return a scalar value
			let normalizedVal;
			if (Array.isArray(val)) {
				normalizedVal = val.length > 0 ? val[0] : "";
			} else {
				normalizedVal = val || "";
			}
			// Convert old format (logged_in/logged_out) to new format (logged-in/logged-out) for user_state
			if (field === "user_state") {
				if (normalizedVal === "logged_in") {
					normalizedVal = "logged-in";
				} else if (normalizedVal === "logged_out") {
					normalizedVal = "logged-out";
				}
			}
			return normalizedVal;
		}
		return val || "";
	};
	
	const [inputValue, setInputValue] = useState(normalizeValue(value, type));
	const selectRef = useRef(null);

	useEffect(() => {
		setInputValue(normalizeValue(value, type));
	}, [value, type]);

	const handleChange = (newValue) => {
		setInputValue(newValue);
		onChange(newValue);
	};

	// Initialize select2 for multiselect type only
	// Use useLayoutEffect for synchronous DOM updates to prevent visible delay
	useLayoutEffect(() => {
		if (type === "multiselect" && selectRef.current) {
			const $select = window.jQuery(selectRef.current);

			// Destroy existing select2 instance if any
			if ($select.hasClass("select2-hidden-accessible")) {
				$select.select2("destroy");
			}

			// Initialize select2 immediately - DOM is ready in useLayoutEffect
			var select2_changed_flag_up = false;

			$select
				.select2({
					containerCssClass: $select.data("select2_class"),
					disabled: disabled,
				})
				.on("select2:selecting", function () {
					select2_changed_flag_up = true;
				})
				.on("select2:unselecting", function () {
					select2_changed_flag_up = true;
				})
				.on("select2:closing", function () {
					if (select2_changed_flag_up && this.multiple) {
						select2_changed_flag_up = false;
						return false;
					}
				})
				.on("change", function (e) {
					// Sync select2 changes with React state
					const selected = Array.from(e.target.selectedOptions, option => option.value);
					handleChange(selected);
				});

			// Set initial value
			if (Array.isArray(inputValue) && inputValue.length > 0) {
				$select.val(inputValue).trigger("change");
			}

			// Update disabled state
			if (disabled) {
				$select.prop("disabled", true);
			} else {
				$select.prop("disabled", false);
			}

			// Cleanup on unmount
			return () => {
				if ($select && $select.hasClass("select2-hidden-accessible")) {
					$select.off("change select2:selecting select2:unselecting select2:closing");
					$select.select2("destroy");
				}
			};
		}
	}, [type, field, disabled]); // Initialize when type, field, or disabled state changes

	// Sync select2 value when inputValue changes externally (from props)
	useEffect(() => {
		if (type === "multiselect" && selectRef.current) {
			const $select = window.jQuery(selectRef.current);
			if ($select.hasClass("select2-hidden-accessible")) {
				// Only update if value actually changed to avoid loops
				const currentVal = $select.val() || [];
				const currentArray = Array.isArray(currentVal) ? currentVal : (currentVal ? [currentVal] : []);
				const newArray = Array.isArray(inputValue) ? inputValue : (inputValue ? [inputValue] : []);
				if (JSON.stringify(currentArray.sort()) !== JSON.stringify(newArray.sort())) {
					$select.val(inputValue).trigger("change");
				}
			}
		}
	}, [inputValue, type]);

	// Sync select2 disabled state when disabled prop changes
	useEffect(() => {
		if (type === "multiselect" && selectRef.current) {
			const $select = window.jQuery(selectRef.current);
			if ($select.hasClass("select2-hidden-accessible")) {
				if (disabled) {
					$select.prop("disabled", true);
				} else {
					$select.prop("disabled", false);
				}
			}
		}
	}, [disabled, type]);

	// Hide input if operator is "empty" or "not empty"
	if (operator === "empty" || operator === "not empty") {
		return null;
	}

	// Get options for multiselect fields from urcr_localized_data
	const getOptions = () => {
		const labels = getURCRData("labels", {});

		switch (field) {
			case "membership":
				const memberships = getURCRData("memberships", {});
				return Object.entries(memberships).map(([id, label]) => ({
					value: id,
					label: label || id,
				}));

			case "roles":
				const wpRoles = getURCRData("wp_roles", {});
				return Object.entries(wpRoles).map(([id, label]) => ({
					value: id,
					label: label || id,
				}));

			case "capabilities":
				const capabilities = getURCRData("wp_capabilities", {});
				return Object.entries(capabilities).map(([id, label]) => ({
					value: id,
					label: label || id,
				}));

			case "registration_source":
				const sources = getURCRData("registration_sources", {});
				return Object.entries(sources).map(([id, label]) => ({
					value: id,
					label: label || id,
				}));

			case "ur_form_field":
				// UR Form Field - get from ur_forms or ur_form_data
				const urForms = getURCRData("ur_forms", {});
				const urFormData = getURCRData("ur_form_data", {});
				const formOptions = [];

				// Add form fields from ur_form_data
				Object.entries(urFormData).forEach(([formId, formFields]) => {
					if (formFields && typeof formFields === 'object') {
						Object.entries(formFields).forEach(([fieldName, fieldLabel]) => {
							formOptions.push({
								value: `${formId}:${fieldName}`,
								label: `${urForms[formId] || formId} - ${fieldLabel || fieldName}`,
							});
						});
					}
				});

				return formOptions.length > 0 ? formOptions : [];

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

	switch (type) {
		case "checkbox":
			// Render radio buttons for checkbox type
			return (
				<CheckboxRadioInput
					field={field}
					value={inputValue}
					onChange={handleChange}
					uniqueId={uniqueId}
					disabled={disabled}
				/>
			);

		case "multiselect":
			const options = getOptions();
			// Handle both array and single value for multiselect
			const selectedValues = Array.isArray(inputValue) ? inputValue : (inputValue ? [inputValue] : []);

			const handleMultiSelectChange = (e) => {
				// This handler is mainly for non-select2 scenarios
				// select2 changes are handled in the useEffect
				const selected = Array.from(e.target.selectedOptions, option => option.value);
				handleChange(selected);
			};

			return (
				<select
					ref={selectRef}
					className="components-select-control__input urcr-enhanced-select2 urcr-condition-value-select urcr-condition-value-select--multiselect"
					value={selectedValues}
					onChange={handleMultiSelectChange}
					multiple={true}
					disabled={disabled}
				>
					{options.length === 0 ? (
						<option value="" disabled>
							{__("No options available", "user-registration")}
						</option>
					) : (
						options.map((option) => (
							<option
								key={option.value}
								value={option.value}
							>
								{option.label}
							</option>
						))
					)}
				</select>
			);

		case "date":
			if (field === "user_registered_date") {
				return (
					<DateRangeInput
						value={inputValue}
						onChange={handleChange}
						disabled={disabled}
					/>
				);
			}
			return (
				<input
					type="date"
					className="components-text-control__input urcr-condition-value-input urcr-condition-value-date"
					value={inputValue}
					onChange={(e) => handleChange(e.target.value)}
				/>
			);

		case "period":
			return (
				<PeriodInput
					value={inputValue}
					onChange={handleChange}
					disabled={disabled}
				/>
			);

		case "number":
			return (
				<input
					type="number"
					className="components-text-control__input urcr-condition-value-input urcr-condition-value-number"
					value={inputValue}
					onChange={(e) => handleChange(e.target.value)}
					placeholder={placeholder || __("Enter value", "user-registration")}
					disabled={disabled}
					min={field === "post_count" ? 0 : undefined}
				/>
			);

		case "text":
		default:
			return (
				<input
					type="text"
					className="components-text-control__input urcr-condition-value-input urcr-condition-value-text"
					value={inputValue}
					onChange={(e) => handleChange(e.target.value)}
					placeholder={placeholder || __("Enter value", "user-registration")}
					disabled={disabled}
				/>
			);
	}
};

export default ConditionValueInput;

