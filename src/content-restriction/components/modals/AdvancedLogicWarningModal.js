/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import Modal from "./Modal";

const AdvancedLogicWarningModal = ({ isOpen, onClose }) => {
	const footer = (
		<>
			<button type="button" className="button button-primary" onClick={onClose}>
				{__("OK", "user-registration")}
			</button>
		</>
	);

	return (
		<Modal
			isOpen={isOpen}
			onClose={onClose}
			title={__("Remove Advanced Logic First", "user-registration")}
			icon="dashicons-warning"
			footer={footer}
			className="urcr-modal--warning"
		>
			<p className="urcr-modal-message">
				{__(
					"You're currently using OR, NOT, or subgroups for this rule. Please remove these first, then you can disable Advanced Logic. When disabled, only AND will be used for multiple conditions",
					"user-registration"
				)}
			</p>
		</Modal>
	);
};

export default AdvancedLogicWarningModal;

