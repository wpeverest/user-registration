/**
 * External Dependencies
 */
import React, { useState, useRef, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import ContentTypeDropdown from "../dropdowns/ContentTypeDropdown";
import ContentValueInput from "../inputs/ContentValueInput";

const AccessControlSection = ({
	accessControl = "access",
	onAccessControlChange,
	contentTargets = [],
	onContentTargetsChange,
}) => {
	const [contentDropdownOpen, setContentDropdownOpen] = useState(false);
	const contentDropdownWrapperRef = useRef(null);

	// Close content dropdown when clicking outside
	useEffect(() => {
		const handleClickOutside = (event) => {
			if (contentDropdownWrapperRef.current && !contentDropdownWrapperRef.current.contains(event.target)) {
				setContentDropdownOpen(false);
			}
		};

		if (contentDropdownOpen) {
			document.addEventListener("mousedown", handleClickOutside);
		}

		return () => {
			document.removeEventListener("mousedown", handleClickOutside);
		};
	}, [contentDropdownOpen]);

	const handleContentButtonClick = (e) => {
		e.stopPropagation();
		setContentDropdownOpen(!contentDropdownOpen);
	};

	const handleAfterContentTypeSelection = (option) => {
		// Add new content target
		const newContentTarget = {
			id: `x${Date.now()}`,
			type: option.value,
			label: option.label,
			value: option.value === "whole_site" ? "whole_site" : [], // Will be populated based on type
			taxonomy: option.value === "taxonomy" ? "" : undefined, // For taxonomy type
		};
		onContentTargetsChange([...contentTargets, newContentTarget]);
		setContentDropdownOpen(false);
	};

	const handleContentTargetUpdate = (targetId, newValue) => {
		const updatedTargets = contentTargets.map((target) =>
			target.id === targetId ? { ...target, value: newValue } : target
		);
		onContentTargetsChange(updatedTargets);
	};

	const handleContentTargetRemove = (targetId) => {
		const updatedTargets = contentTargets.filter((target) => target.id !== targetId);
		onContentTargetsChange(updatedTargets);
	};

	return (
		<div className="urcr-target-selection-section ur-d-flex ur-align-items-start">
			{/* Access/Restrict Section */}
			<select
				className="urcr-access-select urcr-condition-value-input"
				value={accessControl}
				onChange={(e) => onAccessControlChange(e.target.value)}
			>
				<option value="access">{__("Access", "user-registration")}</option>
				<option value="restrict">{__("Restrict", "user-registration")}</option>
			</select>

			<span className="urcr-arrow-icon" aria-hidden="true"></span>
			<div className="ur-d-flex ur-flex-column">
				{contentTargets.length > 0 && (
					<div className="urcr-target-type-group">
						{contentTargets.map((target) => (
							<div key={target.id} className="urcr-target-item">
								<span className="urcr-target-type-label">{target.label}:</span>
								<ContentValueInput
									contentType={target.type}
									value={target.value}
									onChange={(newValue) => handleContentTargetUpdate(target.id, newValue)}
								/>
								<button
									type="button"
									className="button-link urcr-target-remove"
									onClick={() => handleContentTargetRemove(target.id)}
									aria-label={__("Remove", "user-registration")}
								>
									<span className="dashicons dashicons-no-alt"></span>
								</button>
							</div>
						))}
					</div>
				)}

				{/* Always show + Content button */}
				<div className="urcr-content-dropdown-wrapper" ref={contentDropdownWrapperRef}>
					<button
						type="button"
						className="button urcr-add-content-button"
						onClick={handleContentButtonClick}
					>
						<span className="dashicons dashicons-plus-alt2"></span>
						{__("Content", "user-registration")}
					</button>
					{contentDropdownOpen && (
						<ContentTypeDropdown
							onSelect={handleAfterContentTypeSelection}
							existingContentTypes={contentTargets}
						/>
					)}
				</div>
			</div>
		</div>
	);
};

export default AccessControlSection;

