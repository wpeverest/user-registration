import React, { useEffect, useRef, useState } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";

const DateRangeInput = ({ value, onChange, disabled = false }) => {
	const inputRef = useRef(null);
	const flatpickrInstanceRef = useRef(null);
	const onChangeRef = useRef(onChange);
	const dateTypeRef = useRef(null);
	const pendingValueRef = useRef(null);
	const savedRangeValueRef = useRef(null);
	const [isReady, setIsReady] = useState(false);

	const conditionOptions = getURCRData("condition_options", []);
	const userRegisteredDateOption = conditionOptions.find(
		(option) => option.value === "user_registered_date"
	);
	const dateTypeOptions =
		userRegisteredDateOption?.date_type_options || [
			{ value: "before", label: __("Before", "user-registration") },
			{ value: "after", label: __("After", "user-registration") },
			{ value: "range", label: __("Range", "user-registration") },
		];

	const parseValue = (val) => {
		if (!val) {
			return { value: "", type: "range" };
		}
		
		if (typeof val === "string") {
			return { value: val, type: "range" };
		}
		
		if (typeof val === "object" && val.value !== undefined && val.type !== undefined) {
			return { value: val.value || "", type: val.type || "range" };
		}
		
		return { value: "", type: "range" };
	};

	const getInitialState = (val) => {
		const parsed = parseValue(val);
		return {
			dateType: parsed.type,
			dateValue: parsed.value
		};
	};

	const initialState = getInitialState(value);
	const [dateType, setDateType] = useState(initialState.dateType);
	const [dateValue, setDateValue] = useState(initialState.dateValue);

	useEffect(() => {
		dateTypeRef.current = dateType;
	}, [dateType]);

	const formatDate = (date) => {
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, "0");
		const day = String(date.getDate()).padStart(2, "0");
		return `${year}-${month}-${day}`;
	};

	const parseDateValue = (val, type) => {
		if (!val || typeof val !== "string") {
			return [];
		}
		if (type === "range") {
			const parts = val.split(" to ");
			return parts.filter((p) => p.trim());
		} else {
			return val.trim() ? [val.trim()] : [];
		}
	};

	const updateValue = (newDateValue, newDateType) => {
		const newValue = {
			value: newDateValue || "",
			type: newDateType || "range"
		};
		onChangeRef.current(newValue);
	};

	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

	useEffect(() => {
		const parsed = parseValue(value);
		if (parsed.type !== dateType || parsed.value !== dateValue) {
			setDateType(parsed.type);
			setDateValue(parsed.value);
		}
	}, [value]);

	useEffect(() => {
		if (!inputRef.current || typeof window === "undefined") {
			return;
		}

		const initFlatpickr = () => {
			if (!window.flatpickr) {
				setTimeout(initFlatpickr, 100);
				return;
			}

			if (flatpickrInstanceRef.current) {
				flatpickrInstanceRef.current.destroy();
				flatpickrInstanceRef.current = null;
			}

			const currentParsed = parseValue(value);
			const valueToUse = pendingValueRef.current !== null 
				? (typeof pendingValueRef.current === "string" ? pendingValueRef.current : pendingValueRef.current.value)
				: currentParsed.value;
			const typeToUse = pendingValueRef.current !== null && typeof pendingValueRef.current === "object"
				? pendingValueRef.current.type
				: dateType;
			const hadPendingValue = pendingValueRef.current !== null;

			const initialDates = parseDateValue(valueToUse, typeToUse);

			flatpickrInstanceRef.current = window.flatpickr(inputRef.current, {
				mode: typeToUse === "range" ? "range" : "single",
				dateFormat: "Y-m-d",
				disableMobile: true,
				defaultDate: initialDates.length > 0 ? initialDates : null,
				onChange: (selectedDates) => {
					const currentDateType = dateTypeRef.current;
					if (currentDateType === "range") {
					if (selectedDates.length === 2) {
						const fromDate = formatDate(selectedDates[0]);
						const toDate = formatDate(selectedDates[1]);
							updateValue(`${fromDate} to ${toDate}`, "range");
							savedRangeValueRef.current = null;
						}
					} else {
						if (selectedDates.length === 1) {
							const date = formatDate(selectedDates[0]);
							updateValue(date, currentDateType);
						}
					}
				},
				onClose: (selectedDates) => {
					const currentDateType = dateTypeRef.current;
					if (currentDateType === "range" && selectedDates && selectedDates.length === 1) {
						setTimeout(() => {
							if (flatpickrInstanceRef.current && !flatpickrInstanceRef.current.isOpen) {
								flatpickrInstanceRef.current.open();
							}
						}, 50);
					}
				},
			});

			setIsReady(true);
			
			if (hadPendingValue) {
				pendingValueRef.current = null;
			}

			if (disabled) {
				flatpickrInstanceRef.current.set("clickOpens", false);
			}
		};

		initFlatpickr();

		return () => {
			if (flatpickrInstanceRef.current) {
				flatpickrInstanceRef.current.destroy();
				flatpickrInstanceRef.current = null;
				setIsReady(false);
			}
		};
	}, [dateType]);

	const handleDateTypeChange = (e) => {
		const newDateType = e.target.value;
		const currentParsed = parseValue(value);
		const currentValue = currentParsed.value;
		
		let convertedValue = "";
		if ((newDateType === "before" || newDateType === "after") && currentValue && currentValue.includes(" to ")) {
			savedRangeValueRef.current = currentValue;
			const parts = currentValue.split(" to ");
			if (parts.length > 0 && parts[0].trim()) {
				convertedValue = parts[0].trim();
			}
		} else if (newDateType === "range" && currentValue && !currentValue.includes(" to ")) {
			if (savedRangeValueRef.current) {
				convertedValue = savedRangeValueRef.current;
				savedRangeValueRef.current = null;
			} else {
				convertedValue = currentValue;
			}
		} else if (newDateType === "range" && !currentValue) {
			savedRangeValueRef.current = null;
			convertedValue = "";
		} else {
			convertedValue = currentValue;
		}
		
		pendingValueRef.current = {
			value: convertedValue,
			type: newDateType
		};
		
		setDateType(newDateType);
		setDateValue(convertedValue);
		
		updateValue(convertedValue, newDateType);
	};

	useEffect(() => {
		if (!flatpickrInstanceRef.current || !isReady) {
			return;
		}

		
		if (pendingValueRef.current !== null) {
			return;
		}

		const parsed = parseValue(value);
		const dates = parseDateValue(parsed.value, parsed.type);
		if (dates.length > 0) {
			flatpickrInstanceRef.current.setDate(dates, false);
		} else {
			flatpickrInstanceRef.current.clear();
		}
	}, [value, dateType, isReady]);

	useEffect(() => {
		if (!flatpickrInstanceRef.current) {
			return;
		}

		if (disabled) {
			flatpickrInstanceRef.current.set("clickOpens", false);
		} else {
			flatpickrInstanceRef.current.set("clickOpens", true);
		}
	}, [disabled]);

	return (
		<div className="urcr-date-input-group ur-d-flex"
		style={{
			flexDirection: "column",
			alignItems: "flex-start",
			gap: "8px",
		}}
		>
			<select
				className="urcr-date-type-select urcr-condition-value-select"
				value={dateType}
				onChange={handleDateTypeChange}
				disabled={disabled}
			>
				{dateTypeOptions.map((option) => (
					<option key={option.value} value={option.value}>
						{option.label}
					</option>
				))}
			</select>
		<input
			ref={inputRef}
			type="text"
			className="urcr-date-range-input urcr-condition-value-input urcr-condition-value-date"
			readOnly
			disabled={disabled || !isReady}
			style={{ pointerEvents: isReady ? "auto" : "none" }}
				placeholder={
					dateType === "range"
						? __("Select date range", "user-registration")
						: dateType === "before"
						? __("Select date (before)", "user-registration")
						: __("Select date (after)", "user-registration")
				}
			/>
		</div>
	);
};

export default DateRangeInput;
