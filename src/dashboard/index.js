/**
 *  External Dependencies
 */
import React from "react";
import ReactDOM from "react-dom/client";

/**
 *  Internal Dependencies
 */
import App from "./components/App";
import { StateProvider } from "../context/StateProvider";
import reducer, { initialState } from "../context/dashboardContext";

const container = document.getElementById("user-registration-dashboard");
const root = ReactDOM.createRoot(container);

if (root) {
	root.render(
		<StateProvider initialState={initialState} reducer={reducer}>
			<App />
		</StateProvider>
	);
}
