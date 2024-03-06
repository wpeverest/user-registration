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
} from "../screens";

const Router = () => {
	/* global _UR_DASHBOARD_ */
	const { isPro } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	return (
		<Routes>
			<Route path="/" element={<Dashboard />} />
			<Route
				path="/settings"
				element={
					<Settings to="http://wpeverest.local/wp-admin/admin.php?page=user-registration-settings" />
				}
			/>
			<Route path="/features" element={<Modules />} />
			<Route path="/products" element={<Products />} />
			{!isPro && <Route path="/free-vs-pro" element={<FreeVsPro />} />}
			<Route path="/help" element={<Help />} />
			<Route path="*" element={<Dashboard />} />
		</Routes>
	);
};

export default Router;
