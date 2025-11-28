/**
 * External Dependencies
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";

const ContentTypeDropdown = ({ onSelect, existingContentTypes = [] }) => {
	const [selectedValue, setSelectedValue] = useState("");

	const options = [
		{ value: "pages", label: __("Pages", "user-registration") },
		{ value: "posts", label: __("Posts", "user-registration") },
		{ value: "post_types", label: __("Post Type", "user-registration") },
		{ value: "taxonomy", label: __("Taxonomy", "user-registration") },
		{ value: "whole_site", label: __("Whole Site", "user-registration") },
	];

	// Check if a content type already exists
	const isContentTypeExists = (contentType) => {
		return existingContentTypes.some((target) => target.type === contentType);
	};

	const handleOptionClick = (option) => {
		// Don't allow selection if already exists
		if (isContentTypeExists(option.value)) {
			return;
		}
		setSelectedValue(option.value);
		if (onSelect) {
			onSelect(option);
		}
	};

	return (
		<div className="urcr-content-type-dropdown-menu">
			{options.map((option) => {
				const isDisabled = isContentTypeExists(option.value);
				return (
					<span
						key={option.value}
						role="button"
						tabIndex={isDisabled ? -1 : 0}
						className={`urcr-content-type-dropdown-option ${
							selectedValue === option.value ? "is-selected" : ""
						} ${isDisabled ? "is-disabled" : ""}`}
						onClick={(e) => {
							e.stopPropagation();
							if (!isDisabled) {
								handleOptionClick(option);
							}
						}}
						onKeyDown={(e) => {
							if (!isDisabled && (e.key === "Enter" || e.key === " ")) {
								e.preventDefault();
								e.stopPropagation();
								handleOptionClick(option);
							}
						}}
					>
						{option.label}
					</span>
				);
			})}
		</div>
	);
};

export default ContentTypeDropdown;

