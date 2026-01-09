/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";

const LocalPageAction = ({ localPage, onLocalPageChange }) => {
	const pages = getURCRData('pages_for_redirect', {});
	const pageOptions = Object.entries(pages).map(([id, title]) => ({
		value: id,
		label: title
	}));

	return (
		<div className="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-to-local-page-input-container ur-form-group">
			<label className="urcr-label-container ur-col-4">
				<span className="urcr-target-content-label">
					{__("Redirect to a local page", "user-registration")}
				</span>
			</label>
			<div className="urcr-body">
				<select
					className="urcr-input"
					value={localPage || ""}
					onChange={(e) => onLocalPageChange(e.target.value)}
				>
					<option value="">
						{__("Select a page", "user-registration")}
					</option>
					{pageOptions.map((page) => (
						<option key={page.value} value={page.value}>
							{page.label}
						</option>
					))}
				</select>
			</div>
		</div>
	);
};

export default LocalPageAction;

