/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { isProAccess, getURCRData } from "../../utils/localized-data";
import DropdownMenu from "./DropdownMenu";

const ContentTypeDropdown = ({
	onSelect,
	existingContentTypes = [],
	conditions,
	accessControl
}) => {
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
	let filteredOptions = isProAccess()
		? allOptions
		: allOptions.filter(
				(option) =>
					option.value === "posts" ||
					option.value === "pages" ||
					option.value === "whole_site" ||
					("membership" === conditions[0]?.value &&
						"access" === accessControl &&
						option.value === "masteriyo_courses")
			);

	// Check if a content type already exists
	const isContentTypeExists = (contentType) => {
		return existingContentTypes.some(
			(target) => target.type === contentType
		);
	};

	// Map options with disabled state
	const options = filteredOptions.map((option) => ({
		...option,
		disabled: isContentTypeExists(option.value)
	}));

	return <DropdownMenu options={options} onSelect={onSelect} />;
};

export default ContentTypeDropdown;
