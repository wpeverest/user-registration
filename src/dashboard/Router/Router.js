import React from "react";
import { Route, Routes, useLocation } from "react-router-dom";
import {
	Dashboard,
	Help,
	Products,
	Settings,
	Modules,
	FreeVsPro,
} from "../screens";

const Router = () => {
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
			<Route path="/free-vs-pro" element={<FreeVsPro />} />
			<Route path="/help" element={<Help />} />
			<Route path="*" element={<Dashboard />} />
		</Routes>
	);
};

export default Router;
