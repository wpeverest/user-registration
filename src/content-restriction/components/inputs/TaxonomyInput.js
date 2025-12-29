/**
 * External Dependencies
 */
import React, { useState, useEffect, useRef, useCallback, useMemo } from "react";
import { __ } from "@wordpress/i18n";
import { getURCRData } from "../../utils/localized-data";
import TaxonomyTermsSelect from "./TaxonomyTermsSelect";

const TaxonomyInput = ({ value, onChange }) => {
	const [inputValue, setInputValue] = useState(
		value && typeof value === "object" ? value : { taxonomy: "", value: [] }
	);
	const [selectedTaxonomy, setSelectedTaxonomy] = useState(
		value && typeof value === "object" ? (value.taxonomy || "") : ""
	);
	const termsSelectRef = useRef(null);
	const onChangeRef = useRef(onChange);

	// Keep onChange ref up to date
	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

	useEffect(() => {
		if (value && typeof value === "object") {
			setInputValue(value);
			setSelectedTaxonomy(value.taxonomy || "");
		} else {
			setInputValue({ taxonomy: "", value: [] });
			setSelectedTaxonomy("");
		}
	}, [value]);

	const handleChange = useCallback((newValue) => {
		setInputValue(newValue);
		if (onChangeRef.current) {
			onChangeRef.current(newValue);
		}
	}, []);

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
				<TaxonomyTermsSelect
					terms={termOptions}
					selectedTerms={selectedTerms}
					onChange={handleTermsChange}
					selectRef={termsSelectRef}
				/>
			)}
		</div>
	);
};

export default TaxonomyInput;

