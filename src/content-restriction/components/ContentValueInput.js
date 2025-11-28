/**
 * External Dependencies
 */
import React, { useState, useEffect, useLayoutEffect, useRef, useCallback, useMemo } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRLocalizedData, getURCRData } from "../utils/localized-data";

const ContentValueInput = ({ contentType, value, onChange }) => {
	// For taxonomy, value structure is { taxonomy: "category", value: ["1", "2"] }
	const [inputValue, setInputValue] = useState(
		contentType === "taxonomy" 
			? (value && typeof value === "object" ? value : { taxonomy: "", value: [] })
			: (value || (contentType === "multiselect" ? [] : ""))
	);
	const [selectedTaxonomy, setSelectedTaxonomy] = useState(
		contentType === "taxonomy" && value && typeof value === "object" ? (value.taxonomy || "") : ""
	);
	const selectRef = useRef(null);
	const termsSelectRef = useRef(null);
	const isUpdatingRef = useRef(false);
	const onChangeRef = useRef(onChange);

	// Keep onChange ref up to date
	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

	useEffect(() => {
		if (contentType === "taxonomy") {
			if (value && typeof value === "object") {
				setInputValue(value);
				setSelectedTaxonomy(value.taxonomy || "");
			} else {
				setInputValue({ taxonomy: "", value: [] });
				setSelectedTaxonomy("");
			}
		} else if (contentType === "multiselect") {
			setInputValue(Array.isArray(value) ? value : (value ? [value] : []));
		} else {
			setInputValue(value || "");
		}
	}, [value, contentType]);

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

	// Get options based on content type
	const getOptions = () => {
		switch (contentType) {
			case "pages":
				const pages = getURCRData("pages", {});
				return Object.entries(pages).map(([id, label]) => ({
					value: id,
					label: label || id,
				}));

			case "posts":
				const posts = getURCRData("posts", {});
				return Object.entries(posts).map(([id, label]) => ({
					value: id,
					label: label || id,
				}));

			case "post_types":
				const postTypes = getURCRData("post_types", {});
				return Object.entries(postTypes).map(([id, label]) => ({
					value: id,
					label: label || id,
				}));

			default:
				return [];
		}
	};

	// Initialize select2 for multiselect content types
	useLayoutEffect(() => {
		if ((contentType === "pages" || contentType === "posts" || contentType === "post_types") && selectRef.current) {
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
					const selected = Array.from(e.target.selectedOptions, option => option.value);
					handleChange(selected);
				}
			};
			
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
					$select.off("select2:selecting select2:unselecting select2:closing");
					$select.select2("destroy");
				}
			};
		}
	}, [contentType, handleChange]);

	// Sync select2 value when inputValue changes externally (but not from user interaction)
	useEffect(() => {
		if ((contentType === "pages" || contentType === "posts" || contentType === "post_types") && selectRef.current) {
			const $select = window.jQuery(selectRef.current);
			if ($select.hasClass("select2-hidden-accessible")) {
				const currentVal = $select.val() || [];
				const currentArray = Array.isArray(currentVal) ? currentVal : (currentVal ? [currentVal] : []);
				const newArray = Array.isArray(inputValue) ? inputValue : (inputValue ? [inputValue] : []);
				
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

	// Handle different content types
	if (contentType === "whole_site") {
		return (
			<span className="urcr-whole-site-text">
				{__("Whole Site", "user-registration")}
			</span>
		);
	}

	// Handle taxonomy type with two-step selection
	if (contentType === "taxonomy") {
		const taxonomies = getURCRData("taxonomies", {});
		const termsList = getURCRData("terms_list", {});
		
		const taxonomyOptions = Object.entries(taxonomies).map(([name, label]) => ({
			value: name,
			label: label || name,
		}));

		const getTermOptions = () => {
			if (!selectedTaxonomy) {
				return [];
			}
			
			// Check if termsList exists and has the selected taxonomy
			if (!termsList || typeof termsList !== "object") {
				if (process.env.NODE_ENV === "development") {
					console.warn("URCR: terms_list is not available or invalid", termsList);
				}
				return [];
			}
			
			const taxonomyTerms = termsList[selectedTaxonomy];
			if (!taxonomyTerms) {
				if (process.env.NODE_ENV === "development") {
					console.warn(`URCR: No terms found for taxonomy: ${selectedTaxonomy}`, {
						selectedTaxonomy,
						availableTaxonomies: Object.keys(termsList),
						termsList
					});
				}
				return [];
			}
			
			// Handle both array and object formats
			if (Array.isArray(taxonomyTerms)) {
				return taxonomyTerms.map((term, index) => ({
					value: term.term_id || term.id || String(index),
					label: term.name || term.label || String(term),
				}));
			}
			
			// Object format: { term_id: "name" }
			const termEntries = Object.entries(taxonomyTerms);
			if (termEntries.length === 0) {
				if (process.env.NODE_ENV === "development") {
					console.warn(`URCR: Taxonomy ${selectedTaxonomy} has no terms`);
				}
				return [];
			}
			
			return termEntries.map(([termId, termName]) => ({
				value: String(termId),
				label: String(termName || termId),
			}));
		};

		const handleTaxonomyChange = (e) => {
			const newTaxonomy = e.target.value;
			setSelectedTaxonomy(newTaxonomy);
			const newValue = {
				taxonomy: newTaxonomy,
				value: [], // Reset terms when taxonomy changes
			};
			setInputValue(newValue);
			handleChange(newValue);
		};

		const handleTermsChange = useCallback((selectedTermIds) => {
			const newValue = {
				...inputValue,
				value: selectedTermIds,
			};
			setInputValue(newValue);
			handleChange(newValue);
		}, [inputValue, handleChange]);

		// Memoize term options to prevent unnecessary re-renders
		const termOptions = useMemo(() => getTermOptions(), [selectedTaxonomy, termsList]);
		const selectedTerms = Array.isArray(inputValue.value) ? inputValue.value : [];

		return (
			<div className="urcr-taxonomy-select-group">
				<select
					className="urcr-condition-value-input urcr-condition-value-select urcr-taxonomy-select"
					value={selectedTaxonomy}
					onChange={handleTaxonomyChange}
				>
					<option value="">{__("Select Taxonomy", "user-registration")}</option>
					{taxonomyOptions.map((option) => (
						<option key={option.value} value={option.value}>
							{option.label}
						</option>
					))}
				</select>
				{selectedTaxonomy && (
					termOptions.length > 0 ? (
						<TaxonomyTermsSelect
							terms={termOptions}
							selectedTerms={selectedTerms}
							onChange={handleTermsChange}
							selectRef={termsSelectRef}
						/>
					) : (
						<span className="urcr-no-terms-message">
							{__("No terms available for this taxonomy", "user-registration")}
						</span>
					)
				)}
			</div>
		);
	}

	// Multiselect for pages, posts, post_types
	const options = getOptions();
	const selectedValues = Array.isArray(inputValue) ? inputValue : (inputValue ? [inputValue] : []);

	return (
		<select
			ref={selectRef}
			className="components-select-control__input urcr-enhanced-select2 urcr-condition-value-select urcr-condition-value-select--multiselect"
			value={selectedValues}
			onChange={(e) => {
				const selected = Array.from(e.target.selectedOptions, option => option.value);
				handleChange(selected);
			}}
			multiple
		>
			{options.length === 0 ? (
				<option value="" disabled>
					{__("No options available", "user-registration")}
				</option>
			) : (
				options.map((option) => (
					<option
						key={option.value}
						value={option.value}
					>
						{option.label}
					</option>
				))
			)}
		</select>
	);
};

// Separate component for taxonomy terms multiselect
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
		if (!selectRef.current || !terms || terms.length === 0) {
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
	}, [termsKey, selectedTermsKey, handleSelect2Change]); // Use stable keys for comparison

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
			{terms.map((term) => (
				<option key={term.value} value={term.value}>
					{term.label}
				</option>
			))}
		</select>
	);
};

export default ContentValueInput;

