/**
 * External Dependencies
 */
import React, { useRef, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import ContentTypeDropdown from "../dropdowns/ContentTypeDropdown";
import ContentValueInput from "../inputs/ContentValueInput";
import DropdownButton from "../dropdowns/DropdownButton";
import { isProAccess } from "../../utils/localized-data";

const AccessControlSection = ({
	accessControl = "access",
	onAccessControlChange,
	contentTargets = [],
	onContentTargetsChange,
	conditions
}) => {
	const conditionValueInputWrapperRef = useRef(null);

	const handleAfterContentTypeSelection = (option) => {
		const newContentTarget = {
			id: `x${Date.now()}`,
			type: option.value,
			label: option.label,
			value: option.value === "whole_site" ? "whole_site" : [],
			taxonomy: option.value === "taxonomy" ? "" : undefined
		};
		onContentTargetsChange([...contentTargets, newContentTarget]);
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

	useEffect(() => {
		if (!isProAccess() && accessControl === "access") {
			onAccessControlChange("access");
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [accessControl]);

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

	const handleAccessControlChange = (option) => {
		const newValue = option.value;
		if (!isProAccess() && newValue === "restrict") {
			return;
		}

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

	const getAccessControlLabel = () => {
		if (accessControl === "restrict") {
			return __("Restrict", "user-registration");
		}
		return __("Access", "user-registration");
	};

	const getAccessControlOptions = () => {
		return [
			...(isProAccess()
				? [
						{
							value: "restrict",
							label: __("Restrict", "user-registration")
						}
				  ]
				: []),
			{ value: "access", label: __("Access", "user-registration") }
		].map((option) => ({
			...option,
			disabled: !isProAccess() && option.value === "restrict"
		}));
	};

	return (
		<div className="urcr-target-selection-section ur-d-flex ur-align-items-start">
			{/* Access/Restrict Section */}
			<div
				className="urcr-condition-value-input-wrapper"
				ref={conditionValueInputWrapperRef}
			>
				<DropdownButton
					buttonContent={
						<>
							<span className="urcr-dropdown-button-text">
								{getAccessControlLabel()}
							</span>
							<span className="urcr-dropdown-button-arrow dashicons dashicons-arrow-down-alt2"></span>
						</>
					}
					options={getAccessControlOptions()}
					value={accessControl}
					onSelect={handleAccessControlChange}
					buttonClassName="urcr-access-control-button urcr-condition-value-input"
					wrapperClassName="urcr-access-control-dropdown-wrapper"
				/>
			</div>

			<span className="urcr-arrow-icon" aria-hidden="true"></span>
			<div className="ur-d-flex ur-flex-column">
				{contentTargets.length > 0 && (
					<div className="urcr-target-type-group">
						{contentTargets.map((target) => (
							<div key={target.id} className="urcr-target-item">
								<span className="urcr-target-type-label">
									{target.label.replace(/_/g, " ")}:
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
				<DropdownButton
					buttonContent={
						<>
							<span className="dashicons dashicons-plus-alt2"></span>
							{__("Content", "user-registration")}
						</>
					}
					options={[]}
					onSelect={handleAfterContentTypeSelection}
					buttonClassName="button urcr-add-content-button"
					wrapperClassName="urcr-content-dropdown-wrapper"
					renderDropdown={() => (
						<ContentTypeDropdown
							onSelect={handleAfterContentTypeSelection}
							existingContentTypes={contentTargets}
							conditions={conditions}
							accessControl={accessControl}
						/>
					)}
				/>
			</div>
		</div>
	);
};

export default AccessControlSection;
