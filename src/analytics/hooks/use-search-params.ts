import { useState, useCallback, useEffect, useMemo, useRef } from "react";

type SearchParamValue = string | number | boolean | null | undefined;

const valueToString = (value: SearchParamValue): string | null => {
	if (value === null || value === undefined) return null;
	if (typeof value === "boolean") return value ? "true" : "false";
	return String(value);
};

const parseValue = <T extends SearchParamValue>(
	value: string | null,
	type: "string" | "number" | "boolean"
): T | null => {
	if (value === null) return null;

	switch (type) {
		case "number":
			const num = Number(value);
			return (isNaN(num) ? null : num) as T;
		case "boolean":
			return (value === "true") as T;
		default:
			return value as T;
	}
};

type SearchParamSchema = {
	[key: string]: {
		type: "string" | "number" | "boolean";
		default?: SearchParamValue;
	};
};

type InferSchemaType<T extends SearchParamSchema> = {
	[K in keyof T]: T[K]["default"] extends NonNullable<SearchParamValue>
		? NonNullable<
				T[K]["type"] extends "number"
					? number
					: T[K]["type"] extends "boolean"
						? boolean
						: string
			>
		: T[K]["type"] extends "number"
			? number | null
			: T[K]["type"] extends "boolean"
				? boolean | null
				: string | null;
};

function useSearchParams<T extends SearchParamSchema>(schema: T) {
	type ParamsType = InferSchemaType<T>;
	const schemaRef = useRef(schema);
	const stableSchema = useMemo(() => {
		const schemasEqual =
			JSON.stringify(schemaRef.current) === JSON.stringify(schema);
		if (!schemasEqual) {
			schemaRef.current = schema;
		}
		return schemaRef.current;
	}, [schema]);

	const getParamsFromURL = useCallback((): ParamsType => {
		const urlParams = new URLSearchParams(window.location.search);
		const params = {} as ParamsType;

		for (const [key, config] of Object.entries(stableSchema)) {
			const urlValue = urlParams.get(key);
			const parsedValue = parseValue(urlValue, config.type);
			params[key as keyof ParamsType] = (parsedValue ??
				config.default ??
				null) as any;
		}

		return params;
	}, [stableSchema]);

	const [params, setParamsState] = useState<ParamsType>(getParamsFromURL);

	useEffect(() => {
		const handlePopState = () => {
			setParamsState(getParamsFromURL());
		};

		window.addEventListener("popstate", handlePopState);
		return () => window.removeEventListener("popstate", handlePopState);
	}, [getParamsFromURL]);

	useEffect(() => {
		const currentSearch = window.location.search;
		const newParams = getParamsFromURL();
		if (JSON.stringify(params) !== JSON.stringify(newParams)) {
			setParamsState(newParams);
		}
	}, [window.location.search]);

	const setParams = useCallback(
		(
			updates:
				| Partial<ParamsType>
				| ((prev: ParamsType) => Partial<ParamsType>)
		) => {
			setParamsState((prev) => {
				const newParams =
					typeof updates === "function"
						? { ...prev, ...updates(prev) }
						: { ...prev, ...updates };

				const urlParams = new URLSearchParams();
				for (const [key, value] of Object.entries(newParams)) {
					const strValue = valueToString(value as SearchParamValue);
					if (strValue !== null) {
						urlParams.set(key, strValue);
					}
				}

				const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
				window.history.pushState({}, "", newUrl);

				return newParams;
			});
		},
		[]
	);

	return [params, setParams] as const;
}

export default useSearchParams;
