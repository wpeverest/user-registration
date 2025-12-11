/**
 * External Dependencies
 */
import React from "react";
import { __ } from "@wordpress/i18n";

const WholeSiteDisplay = () => {
	return (
		<span className="urcr-whole-site-text">
			{__("Whole Site", "user-registration")}
		</span>
	);
};

export default WholeSiteDisplay;

