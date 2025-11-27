/**
 * External Dependencies
 */
import React, { useState, useEffect, useLayoutEffect, useRef } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRLocalizedData, getURCRData } from "../utils/localized-data";

const ConditionValueInput = ({ type, field, value, operator, onChange }) => {
	const urcrData = getURCRLocalizedData();
	const [inputValue, setInputValue] = useState(value || "");
	const selectRef = useRef(null);

	useEffect(() => {
		setInputValue(value || "");
	}, [value]);

	const handleChange = (newValue) => {
		setInputValue(newValue);
		onChange(newValue);
	};

	// Initialize select2 for multiselect/checkbox types
	// Use useLayoutEffect for synchronous DOM updates to prevent visible delay
	useLayoutEffect(() => {
		if ((type === "multiselect" || type === "checkbox") && selectRef.current) {
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
					if (type === "multiselect") {
						const selected = Array.from(e.target.selectedOptions, option => option.value);
						handleChange(selected);
					} else {
						handleChange(e.target.value);
					}
				});

			// Set initial value
			if (type === "multiselect" && Array.isArray(inputValue) && inputValue.length > 0) {
				$select.val(inputValue).trigger("change");
			} else if (inputValue) {
				$select.val(inputValue).trigger("change");
			}

			// Cleanup on unmount
			return () => {
				if ($select && $select.hasClass("select2-hidden-accessible")) {
					$select.off("change select2:selecting select2:unselecting select2:closing");
					$select.select2("destroy");
				}
			};
		}
	}, [type, field]); // Initialize when type or field changes

	// Sync select2 value when inputValue changes externally (from props)
	useEffect(() => {
		if ((type === "multiselect" || type === "checkbox") && selectRef.current) {
			const $select = window.jQuery(selectRef.current);
			if ($select.hasClass("select2-hidden-accessible")) {
				// Only update if value actually changed to avoid loops
				const currentVal = type === "multiselect" 
					? $select.val() || []
					: $select.val();
				
				if (type === "multiselect") {
					const currentArray = Array.isArray(currentVal) ? currentVal : (currentVal ? [currentVal] : []);
					const newArray = Array.isArray(inputValue) ? inputValue : (inputValue ? [inputValue] : []);
					if (JSON.stringify(currentArray.sort()) !== JSON.stringify(newArray.sort())) {
						$select.val(inputValue).trigger("change");
					}
				} else if (currentVal !== inputValue) {
					$select.val(inputValue).trigger("change");
				}
			}
		}
	}, [inputValue, type]);

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

			case "payment_status":
				const paymentStatus = getURCRData("payment_status", {});
				return Object.entries(paymentStatus).map(([id, label]) => ({
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
						value: "logged_in",
						label: labels.logged_in || __("Logged In", "user-registration")
					},
					{
						value: "logged_out",
						label: labels.logged_out || __("Logged Out", "user-registration")
					},
				];

			default:
				return [];
		}
	};

	switch (type) {
		case "multiselect":
		case "checkbox":
			const options = getOptions();
			// Handle both array and single value for multiselect
			const selectedValues = type === "multiselect"
				? (Array.isArray(inputValue) ? inputValue : (inputValue ? [inputValue] : []))
				: (inputValue ? [inputValue] : []);

			const handleMultiSelectChange = (e) => {
				// This handler is mainly for non-select2 scenarios
				// select2 changes are handled in the useEffect
				if (type === "multiselect") {
					const selected = Array.from(e.target.selectedOptions, option => option.value);
					handleChange(selected);
				} else {
					handleChange(e.target.value);
				}
			};

			return (
				<select
					ref={selectRef}
					className={`components-select-control__input urcr-enhanced-select2 urcr-condition-value-select ${type === "multiselect" ? "urcr-condition-value-select--multiselect" : ""}`}
					value={type === "multiselect" ? selectedValues : (inputValue || "")}
					onChange={handleMultiSelectChange}
					multiple={type === "multiselect"}
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
			return (
				<input
					type="date"
					className="components-text-control__input urcr-condition-value-input urcr-condition-value-date"
					value={inputValue}
					onChange={(e) => handleChange(e.target.value)}
				/>
			);

		case "number":
		case "period":
			return (
				<input
					type="number"
					className="components-text-control__input urcr-condition-value-input urcr-condition-value-number"
					value={inputValue}
					onChange={(e) => handleChange(e.target.value)}
					min={type === "period" ? 0 : undefined}
					placeholder={type === "period" ? __("Days", "user-registration") : ""}
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
					placeholder={__("Enter value", "user-registration")}
				/>
			);
	}
};

export default ConditionValueInput;

