/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { isProAccess } from "../../utils/localized-data";
import DropdownMenu from "./DropdownMenu";

const AccessControlDropdown = ({ value, onSelect }) => {
	// Get access control options
	const options = [
		...(isProAccess() ? [{ value: "restrict", label: __("Restrict", "user-registration") }] : []),
		{ value: "access", label: __("Access", "user-registration") },
	].map(option => ({
		...option,
		disabled: !isProAccess() && option.value === "restrict"
	}));

	return (
		<DropdownMenu
			options={options}
			selectedValue={value}
			onSelect={onSelect}
		/>
	);
};

export default AccessControlDropdown;
