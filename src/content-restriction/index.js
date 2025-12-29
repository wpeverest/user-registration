/**
 * Content Access Rules Standalone React App Entry Point
 * This is the webpack entry point for the standalone content access rules viewer
 */
import React from "react";
import { createRoot } from "react-dom/client";
import ContentAccessRules from "./ContentAccessRules";
// SCSS is enqueued separately via class-urcr-admin-assets.php

document.addEventListener("DOMContentLoaded", () => {
	const appRoot = document.getElementById("user-registration-content-access-rules");

	if (appRoot) {
		const root = createRoot(appRoot);

		root.render(<ContentAccessRules />);

	}
});

// Export for use in other modules (e.g., dashboard screens)
export default ContentAccessRules;

