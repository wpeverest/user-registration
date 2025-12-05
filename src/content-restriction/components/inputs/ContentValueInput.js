/**
 * External Dependencies
 */
import React from "react";
import WholeSiteDisplay from "./WholeSiteDisplay";
import TaxonomyInput from "./TaxonomyInput";
import MultiselectInput from "./MultiselectInput";

const ContentValueInput = ({ contentType, value, onChange }) => {
	// Handle different content types
	if (contentType === "whole_site") {
		return <WholeSiteDisplay />;
	}

	if (contentType === "taxonomy") {
		return <TaxonomyInput value={value} onChange={onChange} />;
	}

	// Multiselect for pages, posts, post_types
	return <MultiselectInput contentType={contentType} value={value} onChange={onChange} />;
};

export default ContentValueInput;
