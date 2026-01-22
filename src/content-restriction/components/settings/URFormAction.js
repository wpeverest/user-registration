/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { RawHTML } from "@wordpress/element";
import { getURCRData } from "../../utils/localized-data";

const URFormAction = ({ urForm, onUrFormChange }) => {
	const urForms = getURCRData("ur_forms", {});
	const formOptions = Object.entries(urForms).map(([id, title]) => ({
		value: id,
		label: title
	}));

	return (
		<>
			<div className="urcr-title-body-pair urcr-rule-action-input-container urcrra-ur-form-input-container ur-form-group">
				<label className="urcr-label-container ur-col-4">
					<span className="urcr-target-content-label">
						{__(
							"Display User Registration & Membership Form",
							"user-registration"
						)}
					</span>
				</label>
				<div className="urcr-body">
					<select
						className="urcr-input"
						value={urForm || ""}
						onChange={(e) => onUrFormChange(e.target.value)}
					>
						<option value="">
							{__("Select a form", "user-registration")}
						</option>
						{formOptions.map((form) => (
							<option key={form.value} value={form.value}>
								{form.label}
							</option>
						))}
					</select>
				</div>
			</div>
		</>
	);
};

export default URFormAction;
