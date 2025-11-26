/**
 * External Dependencies
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { createRule } from "../api/content-access-rules-api";
import { showSuccess, showError } from "../utils/notifications";

const AddNewRuleModal = ({ isOpen, onClose, onCreateSuccess }) => {
	const [ruleName, setRuleName] = useState("");
	const [isCreating, setIsCreating] = useState(false);

	if (!isOpen) {
		return null;
	}

	const handleContinue = async () => {
		const name = ruleName.trim() || __("Untitled Rule", "user-registration");
		setIsCreating(true);

		try {
			const response = await createRule(name);
			if (response.success) {
				showSuccess(response.message || __("Rule created successfully", "user-registration"));
				setRuleName("");
				onClose();
				if (onCreateSuccess && response.rule) {
					onCreateSuccess(response.rule);
				}
			} else {
				showError(response.message || __("Failed to create rule", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsCreating(false);
		}
	};

	const handleKeyDown = (e) => {
		if (e.key === "Escape") {
			onClose();
		} else if (e.key === "Enter" && e.ctrlKey) {
			handleContinue();
		}
	};

	return (
		<>
			<div className="urcr-modal-backdrop" onClick={onClose}></div>
			<div className="urcr-modal" role="dialog" aria-modal="true" aria-labelledby="urcr-modal-title">
				<div className="urcr-modal-content">
					<div className="urcr-modal-header">
						<span className="dashicons dashicons-plus-alt"></span>
						<h2 id="urcr-modal-title">{__("Add New Content Rule", "user-registration")}</h2>
					</div>
					<div className="urcr-modal-body">
						<label htmlFor="urcr-rule-name" className="urcr-modal-label">
							{__("Content Rule Name", "user-registration")}
						</label>
						<input
							id="urcr-rule-name"
							type="text"
							className="urcr-modal-input"
							placeholder={__("Give it a name", "user-registration")}
							value={ruleName}
							onChange={(e) => setRuleName(e.target.value)}
							onKeyDown={handleKeyDown}
							autoFocus
						/>
					</div>
					<div className="urcr-modal-footer">
						<button type="button" className="button urcr-modal-cancel" onClick={onClose} disabled={isCreating}>
							{__("Cancel", "user-registration")}
						</button>
						<button type="button" className="button button-primary urcr-modal-continue" onClick={handleContinue} disabled={isCreating}>
							{isCreating ? __("Creating...", "user-registration") : __("Continue", "user-registration")}
						</button>
					</div>
				</div>
			</div>
		</>
	);
};

export default AddNewRuleModal;

