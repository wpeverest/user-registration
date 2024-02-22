import React from "react";
import { Route, Routes, useLocation } from "react-router-dom";
import { Dashboard, Help, Products } from "../screens";

const Router = () => {
	const { pathname } = useLocation();

	React.useLayoutEffect(() => {
		const submenu = document.querySelector(
			`.wp-submenu a[href="admin.php?page=user-registration-about#${pathname}"]`
		);
		if (!submenu) return;
		submenu.parentElement?.classList.add("current");
		return () => {
			submenu.parentElement?.classList?.remove("current");
		};
	}, [pathname]);

	return (
		<Routes>
			<Route path="/" element={<Dashboard />} />
			<Route path="/help" element={<Help />} />
			<Route path="/products" element={<Products />} />
			<Route path="*" element={<Dashboard />} />
		</Routes>
	);
};

export default Router;
