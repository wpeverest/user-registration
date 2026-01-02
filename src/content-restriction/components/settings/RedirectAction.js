/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";

const RedirectAction = ({ redirectUrl, onRedirectUrlChange }) => {
	return (
		<div className="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-input-container ur-form-group">
			<label className="urcr-label-container ur-col-4">
				<span className="urcr-target-content-label">
					{__("Redirection URL", "user-registration")}
				</span>
			</label>
			<div className="urcr-body">
				<input
					type="url"
					className="urcr-input"
					value={redirectUrl || ""}
					onChange={(e) => onRedirectUrlChange(e.target.value)}
					placeholder={__("Enter a URL to redirect to...", "user-registration")}
				/>
			</div>
		</div>
	);
};

export default RedirectAction;

