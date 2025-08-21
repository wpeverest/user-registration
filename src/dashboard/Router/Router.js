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
	FreeVsPro
} from "../screens";

const Router = () => {
	/* global _UR_DASHBOARD_ */
	const { isPro, settingsURL } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	return (
		<Routes>
			<Route path="/" element={<Dashboard />} />
			<Route path="/settings" element={<Settings to={settingsURL} />} />
			<Route path="/features" element={<Modules />} />
			{!isPro && <Route path="/free-vs-pro" element={<FreeVsPro />} />}
			<Route path="/help" element={<Help />} />
			<Route path="*" element={<Dashboard />} />
			<Route path="/products" element={<Products />} />
		</Routes>
	);
};

export default Router;
