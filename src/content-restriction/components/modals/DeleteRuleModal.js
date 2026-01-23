/**
 * External Dependencies
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { deleteRule } from "../../api/content-access-rules-api";
import { showSuccess, showError } from "../../utils/notifications";
import Modal from "./Modal";

const DeleteRuleModal = ({ isOpen, onClose, rule, onDeleteSuccess }) => {
	const [isDeleting, setIsDeleting] = useState(false);

	const handleDelete = async () => {
		if (!rule || !rule.id) {
			return;
		}

		setIsDeleting(true);

		try {
			const response = await deleteRule(rule.id, true);
			if (response.success) {
				showSuccess(response.message || __("Rule deleted successfully", "user-registration"));
				onClose();
				if (onDeleteSuccess) {
					onDeleteSuccess();
				}
			} else {
				showError(response.message || __("Failed to delete rule", "user-registration"));
			}
		} catch (error) {
			showError(error.message || __("An error occurred", "user-registration"));
		} finally {
			setIsDeleting(false);
		}
	};

	const handleKeyDown = (e) => {
		if (e.key === "Enter" && e.ctrlKey) {
			e.preventDefault();
			handleDelete();
		}
	};

	const footer = (
		<>
			<button type="button" className="button urcr-modal-cancel" onClick={onClose} disabled={isDeleting}>
				{__("Cancel", "user-registration")}
			</button>
			<button type="button" className="button button-primary urcr-modal-delete" onClick={handleDelete} disabled={isDeleting}>
				{isDeleting ? __("Deleting...", "user-registration") : __("Delete", "user-registration")}
			</button>
		</>
	);

	return (
		<Modal
			isOpen={isOpen}
			onClose={onClose}
			title={__("Delete Content Rule", "user-registration")}
			icon="dashicons-trash"
			footer={footer}
			onKeyDown={handleKeyDown}
			className="urcr-modal--delete"
		>
			<p className="urcr-modal-message">
				{__("Are you sure you want to delete this rule permanently?", "user-registration")}
			</p>
		</Modal>
	);
};

export default DeleteRuleModal;

