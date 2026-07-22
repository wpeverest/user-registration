import React from "react";
import { __ } from "@wordpress/i18n";

/**
 * Value widget for a single "URM Form Field" condition row.
 *
 * When the selected form field carries a fixed set of choices (select / radio /
 * checkbox / country, etc.), render a dropdown of those options. Otherwise fall
 * back to a free-text input. The value is always a single scalar string so it
 * matches the server-side `===` comparison in functions-urcr-core.php.
 *
 * @param {Object}   props.fieldMeta Field meta from ur_form_data, e.g. { label, type, options }.
 * @param {string}   props.value     Current stored value.
 * @param {Function} props.onChange  Called with the new scalar value.
 * @param {boolean}  props.disabled  Whether the control is disabled.
 * @param {Object}   props.style     Inline style forwarded to the control (width, etc.).
 */
const FormFieldValueInput = ({ fieldMeta, value, onChange, disabled = false, style = {} }) => {
	const options =
		fieldMeta && fieldMeta.options && typeof fieldMeta.options === "object"
			? fieldMeta.options
			: {};
	const optionEntries = Object.entries(options);

	if (optionEntries.length > 0) {
		return (
			<select
				className="components-select-control__input urcr-condition-value-input urcr-condition-value-select"
				value={value || ""}
				onChange={(e) => onChange(e.target.value)}
				disabled={disabled}
				style={style}
			>
				<option value="">{__("Select value", "user-registration")}</option>
				{optionEntries.map(([optValue, optLabel]) => (
					<option key={optValue} value={optValue}>
						{optLabel || optValue}
					</option>
				))}
			</select>
		);
	}

	return (
		<input
			type="text"
			className="components-text-control__input urcr-condition-value-input urcr-condition-value-text"
			value={value || ""}
			onChange={(e) => onChange(e.target.value)}
			placeholder={__("Enter value", "user-registration")}
			disabled={disabled}
			style={style}
		/>
	);
};

export default FormFieldValueInput;
