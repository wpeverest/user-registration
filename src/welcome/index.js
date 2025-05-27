import React from "react";
import { createRoot } from "react-dom/client";

import App from "./components/App";
import { StateProvider } from "../context/StateProvider";
import reducer, { initialState } from "../context/gettingStartedContext";

document.addEventListener("DOMContentLoaded", () => {
	const appRoot = document.getElementById("user-registration-setup-wizard");

	if (appRoot) {
		const root = createRoot(appRoot);

		root.render(
			<StateProvider initialState={initialState} reducer={reducer}>
				<App />
			</StateProvider>
		);
	}
});
