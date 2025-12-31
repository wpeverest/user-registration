import { useEffect, useRef, useState } from "react";
import { Button } from "./button";
import { Popover, PopoverContent, PopoverTrigger } from "./popover";
import { Calendar } from "./calendar";
import { type DateRange } from "react-day-picker";
import { ChevronDown } from "../icons";

export interface DateRangePickerProps {
	onUpdate?: (values: { range: DateRange }) => void;
	initialDateFrom?: Date | string;
	initialDateTo?: Date | string;
	align?: "start" | "center" | "end";
	locale?: string;
}

interface Preset {
	name: string;
	label: string;
}

const PRESETS: Preset[] = [
	{ name: "today", label: "Today" },
	{ name: "last7", label: "Last 7 days" },
	{ name: "last14", label: "Last 14 days" },
	{ name: "last30", label: "Last 30 days" },
	{ name: "last3Months", label: "Last 3 Months" },
	{ name: "last12Months", label: "Last 12 Months" },
	{ name: "monthToDate", label: "Month to Date" },
	{ name: "quarterToDate", label: "Quarter to Date" },
	{ name: "yearToDate", label: "Year to Date" },
	{ name: "allTime", label: "All Time" }
];

const formatDate = (date: Date, locale: string = "en-us"): string => {
	return date.toLocaleDateString(locale, {
		month: "short",
		day: "numeric",
		year: "numeric"
	});
};

const getDateAdjustedForTimezone = (dateInput: Date | string): Date => {
	if (typeof dateInput === "string") {
		const parts = dateInput.split("-").map((part) => parseInt(part, 10));
		return new Date(parts[0], parts[1] - 1, parts[2]);
	}
	return dateInput;
};

const getLast30DaysRange = (): DateRange => {
	const today = new Date();
	today.setHours(23, 59, 59, 999);
	const thirtyDaysAgo = new Date(today);
	thirtyDaysAgo.setDate(today.getDate() - 30);
	thirtyDaysAgo.setHours(0, 0, 0, 0);
	return {
		from: thirtyDaysAgo,
		to: today
	};
};

export const DateRangePicker = ({
	initialDateFrom,
	initialDateTo,
	onUpdate,
	align = "end",
	locale = "en-US"
}: DateRangePickerProps) => {
	const getInitialRange = (): DateRange => {
		if (initialDateFrom) {
			return {
				from: getDateAdjustedForTimezone(initialDateFrom),
				to: initialDateTo
					? getDateAdjustedForTimezone(initialDateTo)
					: getDateAdjustedForTimezone(initialDateFrom)
			};
		}
		return getLast30DaysRange();
	};

	const [isOpen, setIsOpen] = useState(false);
	const [range, setRange] = useState<DateRange>(getInitialRange());
	const [temp, setTemp] = useState<Date | undefined>();

	const openedRangeRef = useRef<DateRange | undefined>();

	const [isSmallScreen, setIsSmallScreen] = useState(
		typeof window !== "undefined" ? window.innerWidth < 960 : false
	);

	useEffect(() => {
		const handleResize = (): void => {
			setIsSmallScreen(window.innerWidth < 960);
		};
		window.addEventListener("resize", handleResize);
		return () => {
			window.removeEventListener("resize", handleResize);
		};
	}, []);

	const getPresetRange = (presetName: string): DateRange => {
		const today = new Date();
		today.setHours(23, 59, 59, 999);

		switch (presetName) {
			case "today": {
				const start = new Date();
				start.setHours(0, 0, 0, 0);
				return { from: start, to: today };
			}
			case "last7": {
				const from = new Date(today);
				from.setDate(today.getDate() - 6);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "last14": {
				const from = new Date(today);
				from.setDate(today.getDate() - 13);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "last30": {
				const from = new Date(today);
				from.setDate(today.getDate() - 29);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "last3Months": {
				const from = new Date(today);
				from.setMonth(today.getMonth() - 3);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "last12Months": {
				const from = new Date(today);
				from.setFullYear(today.getFullYear() - 1);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "monthToDate": {
				const from = new Date(today.getFullYear(), today.getMonth(), 1);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "quarterToDate": {
				const quarter = Math.floor(today.getMonth() / 3);
				const from = new Date(today.getFullYear(), quarter * 3, 1);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "yearToDate": {
				const from = new Date(today.getFullYear(), 0, 1);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			case "allTime": {
				const from = new Date(window.__UR_ANALYTICS__.install_date);
				from.setHours(0, 0, 0, 0);
				return { from, to: today };
			}
			default:
				return { from: today, to: today };
		}
	};

	const setPreset = (preset: string): void => {
		const newRange = getPresetRange(preset);
		setTemp(undefined);
		setRange(newRange);
		onUpdate?.({ range: newRange });
		setIsOpen(false);
	};

	const handleCalendarSelect = (value: DateRange | undefined): void => {
		if (!value || !value?.from || !value?.to || !range?.from || !range?.to)
			return;
		if (!temp) {
			if (range.from.getTime() !== value.from.getTime()) {
				setTemp(value.from);
			} else if (range.to.getTime() !== value.to.getTime()) {
				setTemp(value.to);
			} else {
				setTemp(value.from);
			}
			return;
		}
		setRange(value);
		onUpdate?.({ range: value });
		setTemp(undefined);
		setIsOpen(false);
	};

	const PresetButton = ({
		preset,
		label
	}: {
		preset: string;
		label: string;
	}): JSX.Element => (
		<Button
			variant="ghost"
			onClick={() => {
				setPreset(preset);
			}}
			className="justify-start"
		>
			{label}
		</Button>
	);

	useEffect(() => {
		if (isOpen) {
			openedRangeRef.current = range;
			setTemp(undefined);
		}
	}, [isOpen, range]);

	return (
		<Popover
			modal={false}
			open={isOpen}
			onOpenChange={(open) => {
				if (!open) {
					setTemp(undefined);
				}
				setIsOpen(open);
			}}
		>
			<Button asChild variant="outline">
				<PopoverTrigger>
					<div className="UR-DateRangePicker-TriggerContent">
						<div className="UR-DateRangePicker-DateText">
							<div>{`${formatDate(range?.from!, locale)}${
								range?.to != null
									? " - " + formatDate(range.to, locale)
									: ""
							}`}</div>
						</div>
					</div>
					<div className="UR-DateRangePicker-Icon">
						<ChevronDown size={24} />
					</div>
				</PopoverTrigger>
			</Button>
			<PopoverContent
				className="UR-DateRangePicker-Content"
				align={align}
				sideOffset={4}
			>
				<div className="UR-DateRangePicker-Layout">
					<div className="UR-DateRangePicker-Presets">
						{PRESETS.map((preset) => (
							<PresetButton
								key={preset.name}
								preset={preset.name}
								label={preset.label}
							/>
						))}
					</div>
					<div className="UR-DateRangePicker-Calendar">
						<Calendar
							mode="range"
							onSelect={handleCalendarSelect}
							selected={temp ? { from: temp, to: temp } : range}
							numberOfMonths={isSmallScreen ? 1 : 2}
							defaultMonth={
								new Date(
									new Date().setMonth(
										new Date().getMonth() -
											(isSmallScreen ? 0 : 1)
									)
								)
							}
						/>
					</div>
				</div>
			</PopoverContent>
		</Popover>
	);
};
