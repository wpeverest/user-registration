import { DateRangePicker } from "./components/ui/date-range-picker";
import { Layout, LayoutHeader, LayoutBody } from "./components/layout";
import {
	Select,
	SelectTrigger,
	SelectContent,
	SelectItem,
	SelectValue
} from "./components/ui/select";
import {
	AnalyticsFilters,
	useAnalyticsFilters
} from "./hooks/use-analytics-filters";
import { format } from "date-fns";
import { useQueries } from "@tanstack/react-query";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";
import { OverviewApiResponse } from "./types/analytics";
import { useCallback } from "react";
import { DATE_UNITS } from "./constants/configs";
import { AnalyticsContent } from "./components/analytics-content";

const API_BASE_PATH = "/user-registration/v1/analytics";

const buildQueryString = (filters: Record<string, any>): string => {
	return Object.entries(filters)
		.map(([key, value]) => {
			if (!value) return null;
			const snakeKey = key
				.replace(/([a-z])([A-Z])/g, "$1_$2")
				.toLowerCase();
			return `${snakeKey}=${encodeURIComponent(value)}`;
		})
		.filter(Boolean)
		.join("&");
};

const App = () => {
	const { setFilters, dateFrom, dateTo, filters, validUnits } =
		useAnalyticsFilters();

	const [overviewQuery] = useQueries({
		queries: [
			{
				queryKey: ["overview", filters],
				queryFn: async () => {
					const queryString = buildQueryString(filters);
					return apiFetch<OverviewApiResponse>({
						path: `${API_BASE_PATH}?${queryString}`
					});
				}
			}
		]
	});

	const handleDateRangeUpdate = useCallback(
		(values: { range: { from?: Date; to?: Date } }) => {
			if (!values.range.from || !values.range.to) return;

			setFilters({
				dateFrom: format(values.range.from, "yyyy-MM-dd"),
				dateTo: format(values.range.to, "yyyy-MM-dd")
			});
		},
		[setFilters]
	);

	const handleUnitChange = useCallback(
		(value: string) => {
			setFilters({
				unit: value as
					| "hour"
					| "day"
					| "week"
					| "month"
					| "year"
					| undefined
			});
		},
		[setFilters]
	);

	const isLoading = overviewQuery.isLoading;
	const hasError = overviewQuery.error;
	const hasData = overviewQuery.data && !isLoading && !hasError;

	const getErrorMessage = () => {
		const errors: string[] = [];

		if (overviewQuery.error) {
			const errorMessage =
				overviewQuery.error instanceof Error
					? overviewQuery.error.message
					: typeof overviewQuery.error === "object" &&
					  overviewQuery.error !== null &&
					  "message" in overviewQuery.error
					? String(overviewQuery.error)
					: __(
							"Failed to load analytics overview data.",
							"user-registration"
					  );
			errors.push(errorMessage);
		}

		return errors.length > 0
			? errors.join(" ")
			: __("Failed to load analytics data.", "user-registration");
	};

	return (
		<Layout>
			<LayoutHeader>
				<div className="UR-Analytics-Header-Content">
					<div className="UR-Analytics-Filters">
						<div>
							<DateRangePicker
								align="start"
								initialDateFrom={dateFrom}
								initialDateTo={dateTo}
								onUpdate={handleDateRangeUpdate}
							/>
						</div>
						<div>
							<Select
								value={filters.unit}
								onValueChange={handleUnitChange}
							>
								<SelectTrigger>
									<SelectValue
										placeholder={__(
											"Day",
											"user-registration"
										)}
									/>
								</SelectTrigger>
								<SelectContent className="UR-UI-Select-Content">
									{DATE_UNITS.filter((unit) =>
										validUnits.includes(unit.value)
									).map((unit) => (
										<SelectItem
											key={unit.value}
											value={unit.value}
										>
											{unit.label}
										</SelectItem>
									))}
								</SelectContent>
							</Select>
						</div>
						{!!window.__UR_ANALYTICS__.memberships.length && (
							<div>
								<Select
									defaultValue={
										filters?.membership
											? filters.membership.toString()
											: filters.scope
									}
									onValueChange={(v) => {
										let next: Partial<AnalyticsFilters> =
											{};
										if (v === "others" || v === "all") {
											next = {
												scope: v,
												membership: undefined
											};
										} else {
											next = {
												membership: parseInt(v),
												scope: "membership"
											};
										}
										setFilters(next);
									}}
								>
									<SelectTrigger>
										<SelectValue
											placeholder={__(
												"All Forms",
												"user-registration"
											)}
										/>
									</SelectTrigger>
									<SelectContent className="UR-UI-Select-Content">
										<SelectItem value="all">
											{__("All", "user-registration")}
										</SelectItem>
										{window.__UR_ANALYTICS__.memberships.map(
											(membership) => (
												<SelectItem
													key={membership.id}
													value={membership.id.toString()}
												>
													{membership.name}
												</SelectItem>
											)
										)}
										<SelectItem value="others">
											{__("Others", "user-registration")}
										</SelectItem>
									</SelectContent>
								</Select>
							</div>
						)}
					</div>
				</div>
			</LayoutHeader>
			<LayoutBody>
				{isLoading ? (
					<div className="UR-Analytics-Loading">
						<div className="UR-Analytics-Loading-Spinner" />
						<p>
							{__(
								"Loading analytics data...",
								"user-registration"
							)}
						</p>
					</div>
				) : hasError ? (
					<div className="UR-Analytics-Error">
						<div className="UR-Analytics-Error-Icon">⚠️</div>
						<p className="UR-Analytics-Error-Message">
							{getErrorMessage()}
						</p>
						<button
							className="UR-Analytics-Error-Retry"
							onClick={() => {
								overviewQuery.refetch();
							}}
						>
							{__("Retry", "user-registration")}
						</button>
					</div>
				) : hasData ? (
					<div>
						<AnalyticsContent overviewData={overviewQuery.data} />
					</div>
				) : (
					<div className="UR-Analytics-Error">
						<p>
							{__(
								"Failed to load analytics data.",
								"user-registration"
							)}
						</p>
					</div>
				)}
			</LayoutBody>
		</Layout>
	);
};

export { App };
