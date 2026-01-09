import React, { useEffect, useRef, useState } from "react";
import { __ } from "@wordpress/i18n";

const DateRangeInput = ({ value, onChange, disabled = false }) => {
	const inputRef = useRef(null);
	const flatpickrInstanceRef = useRef(null);
	const onChangeRef = useRef(onChange);
	const [isReady, setIsReady] = useState(false);

	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

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
				return;
			}

			const parseValue = (val) => {
				if (!val || typeof val !== "string") {
					return [];
				}
				const parts = val.split(" to ");
				return parts.filter(p => p.trim());
			};

			const formatDate = (date) => {
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, "0");
				const day = String(date.getDate()).padStart(2, "0");
				return `${year}-${month}-${day}`;
			};

			const initialDates = parseValue(value);

			flatpickrInstanceRef.current = window.flatpickr(inputRef.current, {
				mode: "range",
				dateFormat: "Y-m-d",
				disableMobile: true,
				defaultDate: initialDates.length > 0 ? initialDates : null,
				onChange: (selectedDates) => {
					if (selectedDates.length === 2) {
						const fromDate = formatDate(selectedDates[0]);
						const toDate = formatDate(selectedDates[1]);
						onChangeRef.current(`${fromDate} to ${toDate}`);
					}
				},
				onClose: (selectedDates) => {
					if (selectedDates && selectedDates.length === 1) {
						setTimeout(() => {
							if (flatpickrInstanceRef.current && !flatpickrInstanceRef.current.isOpen) {
								flatpickrInstanceRef.current.open();
							}
						}, 50);
					}
				},
			});

			setIsReady(true);

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
	}, []);

	useEffect(() => {
		if (!flatpickrInstanceRef.current) {
			return;
		}

		const parseValue = (val) => {
			if (!val || typeof val !== "string") {
				return [];
			}
			const parts = val.split(" to ");
			return parts.filter(p => p.trim());
		};

		const dates = parseValue(value);
		if (dates.length > 0) {
			flatpickrInstanceRef.current.setDate(dates, false);
		} else {
			flatpickrInstanceRef.current.clear();
		}
	}, [value]);

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
		<input
			ref={inputRef}
			type="text"
			className="urcr-date-range-input urcr-condition-value-input urcr-condition-value-date"
			readOnly
			disabled={disabled || !isReady}
			style={{ pointerEvents: isReady ? "auto" : "none" }}
			placeholder={__("Select date range", "user-registration")}
		/>
	);
};

export default DateRangeInput;
