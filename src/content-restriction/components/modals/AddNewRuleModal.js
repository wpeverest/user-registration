/**
 * External Dependencies
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { createRule } from "../../api/content-access-rules-api";
import { showSuccess, showError } from "../../utils/notifications";
import Modal from "./Modal";

const AddNewRuleModal = ({ isOpen, onClose, onCreateSuccess }) => {
	const [ruleName, setRuleName] = useState("");
	const [isCreating, setIsCreating] = useState(false);

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
		if (e.key === "Enter" && e.ctrlKey) {
			e.preventDefault();
			handleContinue();
		}
	};

	const footer = (
		<>
			<button type="button" className="button urcr-modal-cancel" onClick={onClose} disabled={isCreating}>
				{__("Cancel", "user-registration")}
			</button>
			<button type="button" className="button button-primary urcr-modal-continue" onClick={handleContinue} disabled={isCreating}>
				{isCreating ? __("Creating...", "user-registration") : __("Continue", "user-registration")}
			</button>
		</>
	);

	return (
		<Modal
			isOpen={isOpen}
			onClose={onClose}
			title={__("Add New Content Rule", "user-registration")}
			icon="dashicons-plus-alt"
			footer={footer}
			onKeyDown={handleKeyDown}
		>
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
		</Modal>
	);
};

export default AddNewRuleModal;

