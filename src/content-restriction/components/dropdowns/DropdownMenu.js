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
		if (option.disabled) {
			return;
		}
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
		const isDisabled = option.disabled || option.locked || false;
		const isLocked = option.locked || false;

		const Tag = isLocked ? "div" : "span";

		return (
			<Tag
				key={option.value}
				role="button"
				tabIndex={isDisabled ? -1 : 0}
				className={`urcr-dropdown-option ${
					isSelected ? "is-selected" : ""
				} ${isDisabled ? "is-disabled" : ""} ${isLocked ? "is-locked" : ""}`}
				onClick={
					isDisabled || isLocked
						? undefined
						: (e) => handleOptionClick(option, e)
				}
				onKeyDown={
					isDisabled || isLocked
						? undefined
						: (e) => handleKeyDown(option, e)
				}
			>
				{option.label}

				{isLocked && (
					<span>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="24"
							height="24"
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							strokeWidth="2"
							strokeLinecap="round"
							strokeLinejoin="round"
						>
							<path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"></path>
							<path d="M5 21h14"></path>
						</svg>
					</span>
				)}
			</Tag>
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
