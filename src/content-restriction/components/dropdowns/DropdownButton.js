/**
 * External Dependencies
 */
import React, { useState, useRef, useEffect } from "react";
import DropdownMenu from "./DropdownMenu";

/**
 * Reusable Dropdown Button Component
 * Combines a button trigger with a floating dropdown menu
 * 
 * @param {Object} props
 * @param {React.ReactNode|string} props.buttonContent - Content to display in button
 * @param {Array} props.options - Array of option objects for the dropdown
 * @param {string|number} props.value - Currently selected value
 * @param {Function} props.onSelect - Callback when option is selected (option) => {}
 * @param {string} props.buttonClassName - CSS class for the button
 * @param {string} props.wrapperClassName - CSS class for the wrapper
 * @param {string} props.menuClassName - CSS class for the menu
 * @param {boolean} props.grouped - Whether options are grouped
 * @param {Function} props.renderOption - Custom render function for options
 * @param {Function} props.renderDropdown - Custom render function for entire dropdown (overrides options)
 * @param {boolean} props.isOpen - Controlled open state (optional)
 * @param {Function} props.onToggle - Callback when dropdown should toggle (optional, for controlled)
 */
const DropdownButton = ({
	buttonContent,
	options = [],
	value,
	onSelect,
	buttonClassName = "",
	wrapperClassName = "",
	menuClassName = "",
	grouped = false,
	renderOption,
	renderDropdown,
	isOpen: controlledIsOpen,
	onToggle,
}) => {
	const [internalIsOpen, setInternalIsOpen] = useState(false);
	const wrapperRef = useRef(null);

	// Use controlled state if provided, otherwise use internal state
	const isOpen = controlledIsOpen !== undefined ? controlledIsOpen : internalIsOpen;
	const setIsOpen = (newState) => {
		if (controlledIsOpen === undefined) {
			setInternalIsOpen(newState);
		}
		if (onToggle) {
			onToggle(newState);
		}
	};

	// Close dropdown when clicking outside
	useEffect(() => {
		const handleClickOutside = (event) => {
			if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
				setIsOpen(false);
			}
		};

		if (isOpen) {
			document.addEventListener("mousedown", handleClickOutside);
		}

		return () => {
			document.removeEventListener("mousedown", handleClickOutside);
		};
	}, [isOpen]);

	const handleButtonClick = (e) => {
		e.stopPropagation();
		setIsOpen(!isOpen);
	};

	const handleOptionSelect = (option) => {
		if (onSelect) {
			onSelect(option);
		}
		setIsOpen(false);
	};

	return (
		<div className={`urcr-dropdown-wrapper ${wrapperClassName}`} ref={wrapperRef}>
			<button
				type="button"
				className={`urcr-dropdown-button ${buttonClassName}`}
				onClick={handleButtonClick}
			>
				{buttonContent}
			</button>
			{isOpen && (
				renderDropdown ? (
					renderDropdown()
				) : (
					<DropdownMenu
						options={options}
						selectedValue={value}
						onSelect={handleOptionSelect}
						className={menuClassName}
						grouped={grouped}
						renderOption={renderOption}
					/>
				)
			)}
		</div>
	);
};

export default DropdownButton;
