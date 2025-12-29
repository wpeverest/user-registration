/**
 * External Dependencies
 */
import React, { useEffect, useLayoutEffect, useRef, useCallback, useMemo } from "react";
import { __ } from "@wordpress/i18n";

const TaxonomyTermsSelect = ({ terms, selectedTerms, onChange, selectRef }) => {
	const isUpdatingRef = useRef(false);
	const onChangeRef = useRef(onChange);

	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

	const handleSelect2Change = useCallback(function (e) {
		if (isUpdatingRef.current) {
			return;
		}
		const selected = Array.from(e.target.selectedOptions, option => option.value);
		onChangeRef.current(selected);
	}, []);

	// Memoize terms string for stable comparison
	const termsKey = useMemo(() => JSON.stringify(terms.map(t => t.value).sort()), [terms]);
	const selectedTermsKey = useMemo(() => JSON.stringify(Array.isArray(selectedTerms) ? selectedTerms.sort() : []), [selectedTerms]);

	useLayoutEffect(() => {
		if (!selectRef.current) {
			return;
		}

		const $select = window.jQuery(selectRef.current);

		// Destroy existing select2 instance if any
		if ($select.hasClass("select2-hidden-accessible")) {
			$select.select2("destroy");
		}

		var select2_changed_flag_up = false;
		var isInitializing = true;

		$select
			.select2({
				containerCssClass: $select.data("select2_class"),
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
			.on("change", function(e) {
				if (!isInitializing) {
					handleSelect2Change(e);
				}
			});

		// Set initial value
		if (Array.isArray(selectedTerms) && selectedTerms.length > 0) {
			isInitializing = true;
			$select.val(selectedTerms).trigger("change.select2");
			setTimeout(() => {
				isInitializing = false;
			}, 100);
		} else {
			isInitializing = false;
		}

		return () => {
			if ($select && $select.hasClass("select2-hidden-accessible")) {
				$select.off("change select2:selecting select2:unselecting select2:closing");
				$select.select2("destroy");
			}
		};
	}, [termsKey, selectedTermsKey, handleSelect2Change]);

	useEffect(() => {
		if (selectRef.current) {
			const $select = window.jQuery(selectRef.current);
			if ($select.hasClass("select2-hidden-accessible")) {
				const currentVal = $select.val() || [];
				const currentArray = Array.isArray(currentVal) ? currentVal : (currentVal ? [currentVal] : []);
				const newArray = Array.isArray(selectedTerms) ? selectedTerms : [];

				const currentSorted = [...currentArray].sort().join(",");
				const newSorted = [...newArray].sort().join(",");

				if (currentSorted !== newSorted) {
					isUpdatingRef.current = true;
					$select.val(selectedTerms).trigger("change.select2");
					isUpdatingRef.current = false;
				}
			}
		}
	}, [selectedTerms]);

	return (
		<select
			ref={selectRef}
			className="components-select-control__input urcr-enhanced-select2 urcr-condition-value-select urcr-condition-value-select--multiselect urcr-taxonomy-terms-select"
			value={selectedTerms}
			onChange={(e) => {
				const selected = Array.from(e.target.selectedOptions, option => option.value);
				onChange(selected);
			}}
			multiple
		>
			{terms.length === 0 ? (
				<option value="" disabled>
					{__("No terms available", "user-registration")}
				</option>
			) : (
				terms.map((term) => (
					<option key={term.value} value={term.value}>
						{term.label}
					</option>
				))
			)}
		</select>
	);
};

export default TaxonomyTermsSelect;

