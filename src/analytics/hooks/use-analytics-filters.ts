import { useState, useCallback, useEffect } from "react";
import { getDaysDiff, getUnitsForDayRange } from "../constants/configs";
import { DateUnit } from "../types/analytics";
import useSearchParams from "./use-search-params";

export type AnalyticsFilters = {
	dateFrom?: string;
	dateTo?: string;
	unit?: DateUnit;
	scope?: "all" | "others" | "membership";
	membership?: number;
	page?: string;
};

const isValidDate = (dateString: string): boolean => {
	if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
		return false;
	}

	const date = new Date(dateString);
	return !isNaN(date.getTime()) && date.toISOString().startsWith(dateString);
};

const parseDateParam = (value: string | null): string | null => {
	if (!value) return null;
	return isValidDate(value) ? value : null;
};

const parseUnitParam = (value: string | null): DateUnit | null => {
	if (
		value === "hour" ||
		value === "day" ||
		value === "week" ||
		value === "month" ||
		value === "year"
	) {
		return value as DateUnit;
	}
	return null;
};

export function useAnalyticsFilters() {
	const [params, setParams] = useSearchParams({
		"date-from": { type: "string" as const },
		"date-to": { type: "string" as const },
		unit: { type: "string" as const },
		scope: { type: "string", default: "all" },
		membership: { type: "number" as const },
		page: { type: "string" as const }
	});

	const filters: AnalyticsFilters = {
		dateFrom: parseDateParam(params["date-from"]) || undefined,
		dateTo: parseDateParam(params["date-to"]) || undefined,
		unit: parseUnitParam(params["unit"]) || undefined,
		scope:
			(params["scope"] as "all" | "others" | "membership") || undefined,
		membership: params["membership"] || undefined
	};

	const getValidUnits = useCallback((): Array<DateUnit> => {
		if (!filters.dateFrom || !filters.dateTo) {
			return ["day", "week"];
		}

		const dateFromObj = new Date(filters.dateFrom);
		const dateToObj = new Date(filters.dateTo);
		const daysDiff = getDaysDiff(dateFromObj, dateToObj);
		const allValidUnits = getUnitsForDayRange(daysDiff);

		const supportedUnits: Array<DateUnit> = [
			"hour",
			"day",
			"week",
			"month",
			"year"
		];
		const validUnits = allValidUnits.filter((u): u is DateUnit =>
			supportedUnits.includes(u as any)
		);

		return validUnits.length > 0 ? validUnits : ["day"];
	}, [filters.dateFrom, filters.dateTo]);

	const validUnits = getValidUnits();

	useEffect(() => {
		if (!filters.unit || !validUnits.includes(filters.unit)) {
			const newUnit = validUnits[0];

			if (newUnit && newUnit !== filters.unit) {
				setParams((prev) => ({
					...prev,
					unit: newUnit
				}));
			}
		}
	}, [filters.unit, validUnits, setParams]);

	const isValidDateRange = useCallback(
		(filters: AnalyticsFilters): boolean => {
			if (!filters.dateFrom || !filters.dateTo) return true;
			return new Date(filters.dateFrom) <= new Date(filters.dateTo);
		},
		[]
	);

	const setFilters = useCallback(
		(
			updates:
				| Partial<AnalyticsFilters>
				| ((prev: AnalyticsFilters) => Partial<AnalyticsFilters>)
		) => {
			const currentFilters = {
				dateFrom: parseDateParam(params["date-from"]) || undefined,
				dateTo: parseDateParam(params["date-to"]) || undefined,
				unit: parseUnitParam(params["unit"]) || undefined,
				scope:
					(params["scope"] as "all" | "others" | "membership") ||
					undefined,
				membership: params["membership"] || undefined
			};

			const newFilters = {
				...currentFilters,
				...(typeof updates === "function"
					? updates(currentFilters)
					: updates)
			};

			if (newFilters.dateFrom && !isValidDate(newFilters.dateFrom)) {
				console.warn(`Invalid dateFrom: ${newFilters.dateFrom}`);
				return;
			}
			if (newFilters.dateTo && !isValidDate(newFilters.dateTo)) {
				console.warn(`Invalid dateTo: ${newFilters.dateTo}`);
				return;
			}

			if (!isValidDateRange(newFilters)) {
				console.warn(
					"Invalid date range: dateFrom must be before or equal to dateTo"
				);
				return;
			}

			if (
				newFilters.unit &&
				!["hour", "day", "week", "month", "year"].includes(
					newFilters.unit
				)
			) {
				console.warn(`Invalid unit: ${newFilters.unit}`);
				return;
			}

			if (
				newFilters.scope &&
				!["all", "others", "membership"].includes(newFilters.scope)
			) {
				console.warn(`Invalid scope: ${newFilters.scope}`);
				return;
			}

			let compatibleUnit = newFilters.unit;
			if (newFilters.dateFrom && newFilters.dateTo && newFilters.unit) {
				const dateFromObj = new Date(newFilters.dateFrom);
				const dateToObj = new Date(newFilters.dateTo);
				const daysDiff = getDaysDiff(dateFromObj, dateToObj);
				const allValidUnits = getUnitsForDayRange(daysDiff);
				const supportedUnits: Array<DateUnit> = [
					"hour",
					"day",
					"week",
					"month",
					"year"
				];
				const validUnits = allValidUnits.filter((u): u is DateUnit =>
					supportedUnits.includes(u as any)
				);

				if (!validUnits.includes(newFilters.unit)) {
					compatibleUnit =
						validUnits.length > 0 ? validUnits[0] : "day";
				}
			}

			setParams({
				"date-from": newFilters.dateFrom || null,
				"date-to": newFilters.dateTo || null,
				unit: compatibleUnit || null,
				scope: newFilters.scope || "all",
				membership: newFilters.membership || null
			});
		},
		[params, setParams, isValidDateRange]
	);

	const clearFilters = useCallback(
		(keys?: Array<keyof AnalyticsFilters>) => {
			if (keys) {
				const updates: Partial<Record<string, null>> = {};
				const keyMap = {
					dateFrom: "date-from",
					dateTo: "date-to",
					unit: "unit",
					scope: "scope",
					membership: "membership"
				};

				for (const key of keys) {
					updates[keyMap[key as keyof typeof keyMap]] = null;
				}
				setParams(updates as any);
			} else {
				setParams({
					"date-from": null,
					"date-to": null,
					unit: null,
					scope: "all",
					membership: null
				});
			}
		},
		[setParams]
	);

	const replaceFilters = useCallback(
		(newFilters: Partial<AnalyticsFilters>) => {
			if (newFilters.dateFrom && !isValidDate(newFilters.dateFrom)) {
				console.warn(`Invalid dateFrom: ${newFilters.dateFrom}`);
				return;
			}
			if (newFilters.dateTo && !isValidDate(newFilters.dateTo)) {
				console.warn(`Invalid dateTo: ${newFilters.dateTo}`);
				return;
			}
			if (!isValidDateRange(newFilters)) {
				console.warn(
					"Invalid date range: dateFrom must be before or equal to dateTo"
				);
				return;
			}
			if (
				newFilters.unit &&
				!["hour", "day", "week", "month", "year"].includes(
					newFilters.unit
				)
			) {
				console.warn(`Invalid unit: ${newFilters.unit}`);
				return;
			}

			let compatibleUnit = newFilters.unit;
			if (newFilters.dateFrom && newFilters.dateTo && newFilters.unit) {
				const dateFromObj = new Date(newFilters.dateFrom);
				const dateToObj = new Date(newFilters.dateTo);
				const daysDiff = getDaysDiff(dateFromObj, dateToObj);
				const allValidUnits = getUnitsForDayRange(daysDiff);
				const supportedUnits: Array<DateUnit> = [
					"hour",
					"day",
					"week",
					"month",
					"year"
				];
				const validUnitsForRange = allValidUnits.filter(
					(u): u is DateUnit => supportedUnits.includes(u as any)
				);

				if (!validUnitsForRange.includes(newFilters.unit)) {
					compatibleUnit =
						validUnitsForRange.length > 0
							? validUnitsForRange[0]
							: "day";
				}
			}

			setParams({
				"date-from": newFilters.dateFrom || null,
				"date-to": newFilters.dateTo || null,
				unit: compatibleUnit || null,
				scope: newFilters.scope || "all",
				membership: newFilters.membership || null
			});
		},
		[setParams, isValidDateRange]
	);

	const compatibleFilters: AnalyticsFilters = {
		...filters,
		unit:
			filters.unit && validUnits.includes(filters.unit)
				? filters.unit
				: validUnits.length > 0
					? validUnits[0]
					: undefined
	};

	return {
		dateFrom: compatibleFilters.dateFrom,
		dateTo: compatibleFilters.dateTo,
		unit: compatibleFilters.unit,
		scope: compatibleFilters.scope,
		membership: compatibleFilters.membership,

		filters: compatibleFilters,
		validUnits,

		isValidDateRange: isValidDateRange(compatibleFilters),

		setFilters,
		clearFilters,
		replaceFilters
	};
}
