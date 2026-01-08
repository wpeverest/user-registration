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
			title={__("Cannot Disable Advanced Logic", "user-registration")}
			icon="dashicons-warning"
			footer={footer}
			className="urcr-modal--warning"
		>
			<p className="urcr-modal-message">
				{__(
					"Please remove all advanced logic features before disabling this option. Only AND gates are allowed when advanced logic is disabled.",
					"user-registration"
				)}
			</p>
		</Modal>
	);
};

export default AdvancedLogicWarningModal;

