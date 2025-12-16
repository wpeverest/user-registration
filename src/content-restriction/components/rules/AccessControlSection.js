/**
 * External Dependencies
 */
import React, { useState, useRef, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import ContentTypeDropdown from "../dropdowns/ContentTypeDropdown";
import ContentValueInput from "../inputs/ContentValueInput";
import { isProAccess } from "../../utils/localized-data";

const AccessControlSection = ({
	accessControl = "access",
	onAccessControlChange,
	contentTargets = [],
	onContentTargetsChange,
	conditions
}) => {
	const [contentDropdownOpen, setContentDropdownOpen] = useState(false);
	const contentDropdownWrapperRef = useRef(null);
	const conditionValueInputWrapperRef = useRef(null);

	// Close content dropdown when clicking outside
	useEffect(() => {
		const handleClickOutside = (event) => {
			if (
				contentDropdownWrapperRef.current &&
				!contentDropdownWrapperRef.current.contains(event.target)
			) {
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
			taxonomy: option.value === "taxonomy" ? "" : undefined // For taxonomy type
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
		const updatedTargets = contentTargets.filter(
			(target) => target.id !== targetId
		);
		onContentTargetsChange(updatedTargets);
	};

	// Ensure free users can only use "restrict"
	useEffect(() => {
		if (!isProAccess() && accessControl === "access") {
			onAccessControlChange("access");
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [accessControl]);

	// Set initial class based on accessControl value
	useEffect(() => {
		if (conditionValueInputWrapperRef.current) {
			if (accessControl === "access") {
				conditionValueInputWrapperRef.current.classList.add(
					"urcr-access-content"
				);
				conditionValueInputWrapperRef.current.classList.remove(
					"urcr-restrict-content"
				);
			} else {
				conditionValueInputWrapperRef.current.classList.add(
					"urcr-restrict-content"
				);
				conditionValueInputWrapperRef.current.classList.remove(
					"urcr-access-content"
				);
			}
		}
	}, [accessControl]);

	const handleAccessControlChange = (e) => {
		const newValue = e.target.value;
		// Prevent free users from selecting "access"
		if (!isProAccess() && newValue === "access") {
			return;
		}

		// Add or remove classes based on value
		if (conditionValueInputWrapperRef.current) {
			if (newValue === "access") {
				conditionValueInputWrapperRef.current.classList.add(
					"urcr-access-content"
				);
				conditionValueInputWrapperRef.current.classList.remove(
					"urcr-restrict-content"
				);
			} else {
				conditionValueInputWrapperRef.current.classList.add(
					"urcr-restrict-content"
				);
				conditionValueInputWrapperRef.current.classList.remove(
					"urcr-access-content"
				);
			}
		}

		onAccessControlChange(newValue);
	};

	return (
		<div className="urcr-target-selection-section ur-d-flex ur-align-items-start">
			{/* Access/Restrict Section */}
			<div
				className="urcr-condition-value-input-wrapper"
				ref={conditionValueInputWrapperRef}
			>
				<select
					className="urcr-access-select urcr-condition-value-input"
					value={accessControl}
					onChange={handleAccessControlChange}
				>
					{isProAccess() && (
						<option value="restrict">
							{__("Restrict", "user-registration")}
						</option>
					)}
					<option value="access">
						{__("Access", "user-registration")}
					</option>
				</select>
			</div>

			<span className="urcr-arrow-icon" aria-hidden="true"></span>
			<div className="ur-d-flex ur-flex-column">
				{contentTargets.length > 0 && (
					<div className="urcr-target-type-group">
						{contentTargets.map((target) => (
							<div key={target.id} className="urcr-target-item">
								<span className="urcr-target-type-label">
									{target.label}:
								</span>
								<ContentValueInput
									contentType={target.type}
									value={target.value}
									onChange={(newValue) =>
										handleContentTargetUpdate(
											target.id,
											newValue
										)
									}
								/>
								<button
									type="button"
									className="button-link urcr-target-remove"
									onClick={() =>
										handleContentTargetRemove(target.id)
									}
									aria-label={__(
										"Remove",
										"user-registration"
									)}
								>
									<span className="dashicons dashicons-no-alt"></span>
								</button>
							</div>
						))}
					</div>
				)}

				{/* Always show + Content button */}
				<div
					className="urcr-content-dropdown-wrapper"
					ref={contentDropdownWrapperRef}
				>
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
							conditions={conditions}
							accessControl={accessControl}
						/>
					)}
				</div>
			</div>
		</div>
	);
};

export default AccessControlSection;
