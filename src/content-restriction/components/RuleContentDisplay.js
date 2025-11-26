/**
 * External Dependencies
 */
import React, {useState} from "react";
import {__} from "@wordpress/i18n";
import {updateRule} from "../api/content-access-rules-api";
import {showSuccess, showError} from "../utils/notifications";

/* global _UR_DASHBOARD_ */
const {adminURL} = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const RuleContentDisplay = ({rule, onRuleUpdate}) => {
	const [accessControl, setAccessControl] = useState(rule.access_control || "access");
	const [redirectUrl, setRedirectUrl] = useState(rule.redirect_url || "");
	const [isSaving, setIsSaving] = useState(false);

	const handleSave = async () => {
		setIsSaving(true);
		try {
			const data = {
				access_control: accessControl,
			};
			if (redirectUrl) {
				data.redirect_url = redirectUrl;
			}

			const response = await updateRule(rule.id, data);
			if (response.success) {
				showSuccess(response.message || __("Rule saved successfully", "user-registration"));
				onRuleUpdate();
			} else {
				showError(response.message || __("Failed to save rule", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsSaving(false);
		}
	};

	// Check if this is a new/empty rule (no conditions and no targets)
	const isEmptyRule = true;

	// Get type label
	const getTypeLabel = (type) => {
		const labels = {
			wp_pages: __("Pages", "user-registration"),
			wp_posts: __("Posts", "user-registration"),
			post_types: __("Post Types", "user-registration"),
			taxonomy: __("Taxonomy", "user-registration"),
			whole_site: __("Whole Site", "user-registration"),
		};
		return labels[type] || type;
	};

	// Handle tag removal (for future implementation)
	const handleRemoveTag = (type, tagId) => {
		// TODO: Implement tag removal via API
		console.log("Remove tag", type, tagId);
	};

	// Handle add condition button click
	const handleAddCondition = () => {
		return ;
	};

	// Handle add content button click
	const handleAddContent = () => {
		const editUrl = adminURL
			? `${adminURL}admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule&post-id=${rule.id}`
			: "#";
		window.location.href = editUrl;
	};

	return (
		<div className="urcr-rule-content-panel">
			<div className="urcr-rule-body">
				{isEmptyRule && (
					<div className="urcr-empty-rule-state">
						<button type="button" className="button urcr-add-condition-button" onClick={handleAddCondition}>
							<span className="dashicons dashicons-plus-alt2"></span>
							{__("Condition", "user-registration")}
						</button>
					</div>
				)}

			</div>
			{/* Save Button */}
			<div className="urcr-rule-actions">
				<button
					className="urcr-save-rule-btn button button-primary"
					type="button"
					onClick={handleSave}
					disabled={isSaving}
					data-rule-id={rule.id}
				>
					{isSaving ? __("Saving...", "user-registration") : __("Save", "user-registration")}
				</button>
			</div>
		</div>
	);
};

export default RuleContentDisplay;
