/**
 * External Dependencies
 */
import React, {
	useState,
	useEffect,
	useLayoutEffect,
	useRef,
	useCallback
} from "react";
import { __ } from "@wordpress/i18n";
import { getURCRLocalizedData } from "../../utils/localized-data";

const MultiselectInput = ({ contentType, value, onChange }) => {
	const [inputValue, setInputValue] = useState(
		Array.isArray(value) ? value : value ? [value] : []
	);
	const selectRef = useRef(null);
	const isUpdatingRef = useRef(false);
	const onChangeRef = useRef(onChange);

	// Keep onChange ref up to date
	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

	useEffect(() => {
		setInputValue(Array.isArray(value) ? value : value ? [value] : []);
	}, [value]);

	const handleChange = useCallback((newValue) => {
		// Prevent updates during external sync
		if (isUpdatingRef.current) {
			return;
		}
		setInputValue(newValue);
		// Use ref to ensure we always call the latest onChange
		if (onChangeRef.current) {
			onChangeRef.current(newValue);
		}
	}, []);

	// Check if options are grouped (have group and options properties)
	const isGroupedOptions = (optionsArray) => {
		return (
			Array.isArray(optionsArray) &&
			optionsArray.length > 0 &&
			typeof optionsArray[0] === "object" &&
			optionsArray[0] !== null &&
			"group" in optionsArray[0] &&
			"options" in optionsArray[0] &&
			Array.isArray(optionsArray[0].options)
		);
	};

	// Get options based on content type
	const getOptions = () => {
		const data = getURCRLocalizedData()?.[contentType];

		if (!data) {
			return [];
		}

		// Check if data is already an array (likely grouped options)
		if (Array.isArray(data)) {
			return data;
		}

		// Convert object to array of {value, label} options
		return Object.entries(data).map(([id = "", label = ""]) => ({
			value: id,
			label: label || id
		}));
	};

	const options = getOptions();
	const hasGroupedOptions = isGroupedOptions(options);
	const selectedValues = Array.isArray(inputValue)
		? inputValue
		: inputValue
		? [inputValue]
		: [];

	// Initialize select2 for multiselect content types
	useLayoutEffect(() => {
		if (selectRef.current) {
			const $select = window.jQuery(selectRef.current);

			// Destroy existing select2 instance if any
			if ($select.hasClass("select2-hidden-accessible")) {
				$select.select2("destroy");
			}

			// Initialize select2 immediately
			var select2_changed_flag_up = false;
			var isInitializing = true;

			// Create a stable change handler
			const changeHandler = function (e) {
				// Only handle change if not initializing and not updating
				if (!isInitializing && !isUpdatingRef.current) {
					const selected = Array.from(
						e.target.selectedOptions,
						(option) => option.value
					);
					handleChange(selected);
				}
			};

			$select
				.select2({
					containerCssClass: $select.data("select2_class")
				})
				.on("select2:selecting", function () {
					select2_changed_flag_up = true;
					isInitializing = false;
				})
				.on("select2:unselecting", function () {
					select2_changed_flag_up = true;
					isInitializing = false;
				})
				.on("select2:closing", function () {
					if (select2_changed_flag_up && this.multiple) {
						select2_changed_flag_up = false;
						return false;
					}
				})
				.on("change", changeHandler);

			// Set initial value without triggering our change handler
			if (Array.isArray(inputValue) && inputValue.length > 0) {
				isInitializing = true;
				$select.val(inputValue);
				// Use a small delay to mark initialization complete
				setTimeout(() => {
					isInitializing = false;
				}, 50);
			} else {
				isInitializing = false;
			}

			// Cleanup on unmount
			return () => {
				if ($select && $select.hasClass("select2-hidden-accessible")) {
					$select.off("change", changeHandler);
					$select.off(
						"select2:selecting select2:unselecting select2:closing"
					);
					$select.select2("destroy");
				}
			};
		}
	}, [contentType, handleChange]);

	// Sync select2 value when inputValue changes externally (but not from user interaction)
	useEffect(() => {
		if (selectRef.current) {
			const $select = window.jQuery(selectRef.current);
			if ($select.hasClass("select2-hidden-accessible")) {
				const currentVal = $select.val() || [];
				const currentArray = Array.isArray(currentVal)
					? currentVal
					: currentVal
					? [currentVal]
					: [];
				const newArray = Array.isArray(inputValue)
					? inputValue
					: inputValue
					? [inputValue]
					: [];

				// Only update if values actually differ
				const currentSorted = [...currentArray].sort().join(",");
				const newSorted = [...newArray].sort().join(",");

				if (currentSorted !== newSorted) {
					// Mark as updating to prevent handleChange from firing
					isUpdatingRef.current = true;

					// Update value without triggering change events
					$select.val(inputValue);

					// Reset flag after a brief delay
					setTimeout(() => {
						isUpdatingRef.current = false;
					}, 50);
				}
			}
		}
	}, [inputValue, contentType]);

	return (
		<select
			ref={selectRef}
			className="components-select-control__input urcr-enhanced-select2 urcr-condition-value-select urcr-condition-value-select--multiselect"
			value={selectedValues}
			onChange={(e) => {
				const selected = Array.from(
					e.target.selectedOptions,
					(option) => option.value
				);
				handleChange(selected);
			}}
			multiple
		>
			{options.length === 0 ? (
				<option value="" disabled>
					{__("No options available", "user-registration")}
				</option>
			) : hasGroupedOptions ? (
				<>
					{options.map((option) => (
						<optgroup label={option.group} key={option.group}>
							{option.options.map((option) => (
								<option key={option.value} value={option.value}>
									{option.label}
								</option>
							))}
						</optgroup>
					))}
				</>
			) : (
				<>
					{options.map((option) => (
						<option key={option.value} value={option.value}>
							{option.label}
						</option>
					))}
				</>
			)}
		</select>
	);
};

export default MultiselectInput;
