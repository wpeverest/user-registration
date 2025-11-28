/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";

const SettingsPanel = ({ rule }) => {
	return (
		<div className="urcr-rule-settings-panel">
			<h4>
				{__("Settings", "user-registration")}
			</h4>
			<div>
				<p>
					{__("Action settings and other configuration options will appear here.", "user-registration")}
				</p>
				<hr />
				<button
					className="button button-primary"
					type="button"
					onClick={() => {
						const editUrl = window._UR_DASHBOARD_?.adminURL
							? `${window._UR_DASHBOARD_.adminURL}admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule&post-id=${rule.id}`
							: "#";
						window.location.href = editUrl;
					}}
				>
					{__("Edit Full Settings", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default SettingsPanel;
