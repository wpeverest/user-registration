/**
 *  External Dependencies
 */
import React from "react";
import { Route, Routes, useLocation } from "react-router-dom";

/**
 *  Internal Dependencies
 */
import {
	Dashboard,
	Help,
	Products,
	Settings,
	Modules,
	FreeVsPro,
	SiteAssistant,
	ContentAccessRules
} from "../screens";

const Router = () => {
	/* global _UR_DASHBOARD_ */
	const { isPro, settingsURL } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	return (
		<Routes>
			<Route path="/" element={<SiteAssistant />} />
			<Route path="/settings" element={<Settings to={settingsURL} />} />
			<Route path="/features" element={<Modules />} />
			<Route path="/products" element={<Products />} />
			<Route path="/content-access-rules" element={<ContentAccessRules />} />
			{/* {!isPro && <Route path="/free-vs-pro" element={<FreeVsPro />} />} */}
			<Route path="/help" element={<Help />} />
			<Route path="*" element={<SiteAssistant />} />
		</Routes>
	);
};

export default Router;
