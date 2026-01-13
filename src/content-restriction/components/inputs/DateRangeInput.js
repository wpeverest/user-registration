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
			{ value: "range", label: __("Select date range", "user-registration") },
			{ value: "single", label: __("Pick a date", "user-registration") },
		];

	const getInitialDateType = (val) => {
		if (!val || typeof val !== "string") {
			return "range";
		}
		return val.includes(" to ") ? "range" : "single";
	};

	const [dateType, setDateType] = useState(() => getInitialDateType(value));

	useEffect(() => {
		dateTypeRef.current = dateType;
	}, [dateType]);

	const formatDate = (date) => {
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, "0");
		const day = String(date.getDate()).padStart(2, "0");
		return `${year}-${month}-${day}`;
	};

	const parseValue = (val, type) => {
		if (!val || typeof val !== "string") {
			return [];
		}
		if (type === "single") {
			return val.trim() ? [val.trim()] : [];
		}
		const parts = val.split(" to ");
		return parts.filter((p) => p.trim());
	};

	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

	useEffect(() => {
		const newDateType = getInitialDateType(value);
		if (newDateType !== dateType) {
			setDateType(newDateType);
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

			const valueToUse = pendingValueRef.current !== null ? pendingValueRef.current : value;
			const hadPendingValue = pendingValueRef.current !== null;

			const initialDates = parseValue(valueToUse, dateType);

			flatpickrInstanceRef.current = window.flatpickr(inputRef.current, {
				mode: dateType === "single" ? "single" : "range",
				dateFormat: "Y-m-d",
				disableMobile: true,
				defaultDate: initialDates.length > 0 ? initialDates : null,
				onChange: (selectedDates) => {
					const currentDateType = dateTypeRef.current;
					if (currentDateType === "single") {
						if (selectedDates.length === 1) {
							const date = formatDate(selectedDates[0]);
							onChangeRef.current(date);
						}
					} else {
						if (selectedDates.length === 2) {
							const fromDate = formatDate(selectedDates[0]);
							const toDate = formatDate(selectedDates[1]);
							onChangeRef.current(`${fromDate} to ${toDate}`);
							savedRangeValueRef.current = null;
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
		const currentValue = value || "";
		
		let convertedValue = "";
		if (newDateType === "single" && currentValue && currentValue.includes(" to ")) {
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
		
		pendingValueRef.current = convertedValue;
		
		setDateType(newDateType);
		
		if (convertedValue !== currentValue) {
			onChangeRef.current(convertedValue);
		}
	};

	useEffect(() => {
		if (!flatpickrInstanceRef.current || !isReady) {
			return;
		}

		
		if (pendingValueRef.current !== null) {
			return;
		}

		const dates = parseValue(value, dateType);
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
					dateType === "single"
						? __("Pick a date", "user-registration")
						: __("Select date range", "user-registration")
				}
			/>
		</div>
	);
};

export default DateRangeInput;
