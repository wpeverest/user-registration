/**
 * External Dependencies
 */
import React from "react";

/**
 * Reusable Dropdown Menu Component
 *
 * @param {Object} props
 * @param {Array} props.options - Array of option objects with {value, label, disabled?}
 * @param {string|number} props.selectedValue - Currently selected value
 * @param {Function} props.onSelect - Callback when option is selected (option) => {}
 * @param {string} props.className - Additional CSS class name
 * @param {boolean} props.grouped - Whether options are grouped
 * @param {Function} props.renderOption - Custom render function for options
 */
const DropdownMenu = ({
	options = [],
	selectedValue,
	onSelect,
	className = "",
	grouped = false,
	renderOption
}) => {
	const handleOptionClick = (option, e) => {
		e.stopPropagation();
		// if (option.disabled) {
		// 	return;
		// }
		if (onSelect) {
			onSelect(option);
		}
	};

	const handleKeyDown = (option, e) => {
		if (option.disabled) {
			return;
		}
		if (e.key === "Enter" || e.key === " ") {
			e.preventDefault();
			e.stopPropagation();
			if (onSelect) {
				onSelect(option);
			}
		}
	};

	const renderOptionItem = (option) => {
		if (renderOption) {
			return renderOption(
				option,
				handleOptionClick,
				handleKeyDown,
				selectedValue
			);
		}

		const isSelected = selectedValue === option.value;
		const isDisabled = option.disabled || false;

		return (
			<span
				key={option.value}
				role="button"
				// tabIndex={isDisabled ? -1 : 0}
				className={`urcr-dropdown-option ${
					isSelected ? "is-selected" : ""
				} `}
				onClick={(e) => handleOptionClick(option, e)}
				onKeyDown={(e) => handleKeyDown(option, e)}
			>
				{option.label}
			</span>
		);
	};

	if (grouped) {
		// Options are grouped: [{group: "Group Name", options: [...]}, ...]
		return (
			<div
				className={`urcr-dropdown-menu urcr-condition-field-dropdown-menu ${className}`}
			>
				{options.map((group, groupIndex) => (
					<div
						key={groupIndex}
						className="urcr-dropdown-group urcr-condition-field-dropdown-group"
					>
						{group.group && (
							<div className="urcr-dropdown-group-label urcr-condition-field-dropdown-group-label">
								{group.group}
							</div>
						)}
						{group.options.map((option) =>
							renderOptionItem(option, true)
						)}
					</div>
				))}
			</div>
		);
	}
	// Flat options array
	return (
		<div className={`urcr-dropdown-menu ${className}`}>
			{options.map((option) => renderOptionItem(option, false))}
		</div>
	);
};

export default DropdownMenu;
