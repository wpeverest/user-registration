/**
 * External Dependencies
 */
import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { isProAccess, getURCRData } from "../../utils/localized-data";

const ContentTypeDropdown = ({
	onSelect,
	existingContentTypes = [],
	conditions,
	accessControl
}) => {
	const [selectedValue, setSelectedValue] = useState("");
	console.log("conditions", conditions);

	// Get content type options from localized data
	const allOptions = getURCRData("content_type_options", [
		{ value: "pages", label: __("Pages", "user-registration") },
		{ value: "posts", label: __("Posts", "user-registration") },
		{ value: "post_types", label: __("Post Type", "user-registration") },
		{ value: "taxonomy", label: __("Taxonomy", "user-registration") },
		{ value: "whole_site", label: __("Whole Site", "user-registration") },
		{
			value: "masteriyo_courses",
			label: __("Courses", "user-registration")
		}
	]);
	// Filter options based on pro access
	// For free users, only show posts and pages
	let options = isProAccess()
		? allOptions
		: allOptions.filter(
				(option) => option.value === "posts" || option.value === "pages"
		  );

	options =
		"membership" === conditions[0]?.value && "access" === accessControl
			? options
			: options.filter((option) => option.value !== "masteriyo_courses");

	// Check if a content type already exists
	const isContentTypeExists = (contentType) => {
		return existingContentTypes.some(
			(target) => target.type === contentType
		);
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
							if (
								!isDisabled &&
								(e.key === "Enter" || e.key === " ")
							) {
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
