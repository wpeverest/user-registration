/**
 * External Dependencies
 */
import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";

const PeriodInput = ({ value, onChange, disabled = false }) => {
	// Value structure: { select: "During" | "After", input: "number" }
	const [periodValue, setPeriodValue] = useState(
		value && typeof value === "object"
			? { select: value.select || "During", input: value.input || "" }
			: { select: "During", input: "" }
	);

	useEffect(() => {
		if (value && typeof value === "object") {
			setPeriodValue({
				select: value.select || "During",
				input: value.input || ""
			});
		} else {
			setPeriodValue({ select: "During", input: "" });
		}
	}, [value]);

	const handleSelectChange = (e) => {
		const newValue = {
			...periodValue,
			select: e.target.value
		};
		setPeriodValue(newValue);
		onChange(newValue);
	};

	const handleInputChange = (e) => {
		const newValue = {
			...periodValue,
			input: e.target.value
		};
		setPeriodValue(newValue);
		onChange(newValue);
	};

	return (
		<div className="urcr-period-input-group">
			<select
				className="urcr-period-select urcr-condition-value-select"
				value={periodValue.select}
				onChange={handleSelectChange}
				disabled={disabled}
			>
				<option value="During">{__("During", "user-registration")}</option>
				<option value="After">{__("After", "user-registration")}</option>
			</select>
			<input
				type="number"
				className="urcr-period-number urcr-condition-value-input urcr-condition-value-number"
				value={periodValue.input}
				onChange={handleInputChange}
				min="0"
				placeholder={__("Days", "user-registration")}
				disabled={disabled}
			/>
		</div>
	);
};

export default PeriodInput;

