/**
 * External Dependencies
 */
import React, { useState, useEffect, useRef, useCallback } from "react";
import { __ } from "@wordpress/i18n";
import ConditionValueInput from "./ConditionValueInput";
import ContentTypeDropdown from "./ContentTypeDropdown";
import ContentValueInput from "./ContentValueInput";
import { getURCRLocalizedData, getURCRData } from "../utils/localized-data";

// Get all condition options as a flat array for select dropdown
const getAllConditionOptions = () => {
	return [
		{ value: "membership", label: __("Membership", "user-registration"), type: "multiselect" },
		{ value: "roles", label: __("Roles", "user-registration"), type: "multiselect" },
		{ value: "user_registered_date", label: __("User Registered Date", "user-registration"), type: "date" },
		{ value: "access_period", label: __("Period after Registration", "user-registration"), type: "period" },
		{ value: "user_state", label: __("User State", "user-registration"), type: "checkbox" },
		{ value: "email_domain", label: __("Email Domain", "user-registration"), type: "text" },
		{ value: "post_count", label: __("Minimum Public Posts Count", "user-registration"), type: "number" },
		{ value: "capabilities", label: __("Capabilities", "user-registration"), type: "multiselect" },
		{ value: "registration_source", label: __("User Registration Source", "user-registration"), type: "multiselect" },
		{ value: "ur_form_field", label: __("UR Form Field", "user-registration"), type: "multiselect" },
		{ value: "payment_status", label: __("Payment Status", "user-registration"), type: "multiselect" },
	];
};

const ConditionRow = ({
	condition,
	onUpdate,
	onRemove,
	accessControl = "access",
	onAccessControlChange,
	onContentTargetsChange,
}) => {
	const [operator] = useState(condition.operator || "is");
	const [value, setValue] = useState(condition.conditionValue || "");
	const [contentTargets, setContentTargets] = useState(condition.contentTargets || []);
	const [contentDropdownOpen, setContentDropdownOpen] = useState(false);
	const contentDropdownWrapperRef = useRef(null);

	useEffect(() => {
		onUpdate({
			...condition,
			operator,
			conditionValue: value,
		});
	}, [operator, value]);

	// Sync value when condition type or field changes
	useEffect(() => {
		if (condition.conditionValue !== undefined) {
			setValue(condition.conditionValue);
		} else {
			setValue(condition.type === "multiselect" ? [] : "");
		}
	}, [condition.type, condition.value]);

	const handleValueChange = (newValue) => {
		setValue(newValue);
	};

	const handleFieldChange = (e) => {
		const selectedValue = e.target.value;
		const allOptions = getAllConditionOptions();
		const selectedOption = allOptions.find(opt => opt.value === selectedValue);
		
		if (selectedOption) {
			// Update the condition with new field, label, and type
			// Reset the value since the field type changed
			const updatedCondition = {
				...condition,
				value: selectedOption.value,
				label: selectedOption.label,
				type: selectedOption.type,
				conditionValue: "", // Reset value when field changes
			};
			setValue(""); // Reset local value
			onUpdate(updatedCondition);
		}
	};

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
		setContentTargets((prevTargets) => {
			const updated = [...prevTargets, newContentTarget];
			if (onContentTargetsChange) {
				onContentTargetsChange(condition.id, updated);
			}
			return updated;
		});
		setContentDropdownOpen(false);
	};

	const handleContentTargetUpdate = useCallback((targetId, newValue) => {
		setContentTargets((prevTargets) => {
			const updated = prevTargets.map((target) =>
				target.id === targetId ? { ...target, value: newValue } : target
			);
			if (onContentTargetsChange) {
				onContentTargetsChange(condition.id, updated);
			}
			return updated;
		});
	}, [condition.id, onContentTargetsChange]);

	const handleContentTargetRemove = (targetId) => {
		setContentTargets((prevTargets) => {
			const updated = prevTargets.filter((target) => target.id !== targetId);
			if (onContentTargetsChange) {
				onContentTargetsChange(condition.id, updated);
			}
			return updated;
		});
	};

	// Notify parent when contentTargets change (but not on initial mount to avoid duplicate calls)
	const isInitialMount = useRef(true);
	useEffect(() => {
		if (isInitialMount.current) {
			isInitialMount.current = false;
			// Still notify on initial mount to sync state
			if (onContentTargetsChange) {
				onContentTargetsChange(condition.id, contentTargets);
			}
		} else {
			// Notify on subsequent changes
			if (onContentTargetsChange) {
				onContentTargetsChange(condition.id, contentTargets);
			}
		}
	}, [contentTargets, condition.id, onContentTargetsChange]);

	return (
		<>
			<div className="urcr-condition-row ur-d-flex ur-align-items-start ur-mt-2">
				<div className="urcr-condition-selection-section ur-d-flex ur-align-items-center ur-g-4">
					{/* Field Name (Select Dropdown) */}
					<div className="urcr-condition-field-name">
						<select
							className="components-select-control__input urcr-condition-value-input"
							value={condition.value || ""}
							onChange={handleFieldChange}
						>
							{getAllConditionOptions().map((option) => (
								<option key={option.value} value={option.value}>
									{option.label}
								</option>
							))}
						</select>
					</div>

					{/* Operator  */}
					<div className="urcr-condition-operator">
						<span>{operator}</span>
					</div>

					{/* Value Input */}
					<div className="urcr-condition-value">
						<ConditionValueInput
							type={condition.type}
							field={condition.value}
							value={value}
							operator={operator}
							onChange={handleValueChange}
						/>
					</div>

				</div>
				<div className="urcr-target-selection-section ur-d-flex ur-align-items-start">
					{/* Access/Restrict Section */}
					<select
						className="urcr-access-select urcr-condition-value-input"
						value={accessControl}
						onChange={(e) => onAccessControlChange && onAccessControlChange(e.target.value)}
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
										{target.type !== "taxonomy" && (
											<ContentValueInput
												contentType={target.type}
												value={target.value}
												onChange={(newValue) => handleContentTargetUpdate(target.id, newValue)}
											/>
										)}
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

				{/* Remove Button */}
				<button
					type="button"
					className="button button-link-delete"
					onClick={onRemove}
					aria-label={__("Remove condition", "user-registration")}
				>
					<span className="dashicons dashicons-trash"></span>
				</button>
			</div>
		</>
	);
};

export default ConditionRow;

