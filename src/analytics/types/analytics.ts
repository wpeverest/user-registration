export type NonEmptyArray<T> = [T, ...T[]];

type TimeSeriesData = Array<{
	time: string;
	value: number;
}>;

type CategoryData = Array<{
	label: string;
	value: number;
}>;

export type DateUnit = "hour" | "day" | "week" | "month" | "year";
export type DateRange =
	| "today"
	| "last7"
	| "last14"
	| "last30"
	| "last3Months"
	| "last12Months"
	| "monthToDate"
	| "quarterToDate"
	| "yearToDate"
	| "allTime";
export type DateRangeToUnits = Record<DateRange, Array<DateUnit>>;

interface Metric {
	count: number;
	previous: number;
	percentage_change: number;
	currency: boolean;
}

export interface OverviewApiResponse {
	new_members: Metric;
	approved_members: Metric;
	pending_members: Metric;
	denied_members: Metric;
	total_revenue: Metric;
	average_order_value: Metric;
	refunded_revenue: Metric;
	refunded_revenue_count: Metric;
}
