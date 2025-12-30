/**
 * External Dependencies
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { duplicateRule } from "../../api/content-access-rules-api";
import { showSuccess, showError } from "../../utils/notifications";
import Modal from "./Modal";

const DuplicateRuleModal = ({ isOpen, onClose, rule, onDuplicateSuccess }) => {
	const [isDuplicating, setIsDuplicating] = useState(false);

	const handleDuplicate = async () => {
		if (!rule || !rule.id) {
			return;
		}

		setIsDuplicating(true);

		try {
			const response = await duplicateRule(rule.id);
			if (response.success) {
				showSuccess(response.message || __("Rule duplicated successfully", "user-registration"));
				onClose();
				if (onDuplicateSuccess) {
					onDuplicateSuccess();
				}
			} else {
				showError(response.message || __("Failed to duplicate rule", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsDuplicating(false);
		}
	};

	const handleKeyDown = (e) => {
		if (e.key === "Enter" && e.ctrlKey) {
			e.preventDefault();
			handleDuplicate();
		}
	};

	const footer = (
		<>
			<button type="button" className="button urcr-modal-cancel" onClick={onClose} disabled={isDuplicating}>
				{__("Cancel", "user-registration")}
			</button>
			<button type="button" className="button button-primary urcr-modal-duplicate" onClick={handleDuplicate} disabled={isDuplicating}>
				{isDuplicating ? __("Duplicating...", "user-registration") : __("Duplicate", "user-registration")}
			</button>
		</>
	);

	return (
		<Modal
			isOpen={isOpen}
			onClose={onClose}
			title={__("Duplicate Content Rule", "user-registration")}
			icon="dashicons-admin-page"
			footer={footer}
			onKeyDown={handleKeyDown}
			className="urcr-modal--duplicate"
		>
			<p className="urcr-modal-message">
				{__("Are you sure you want to duplicate this rule? A copy will be created with the same settings.", "user-registration")}
			</p>
		</Modal>
	);
};

export default DuplicateRuleModal;

