import React from "react";
import ReactDOM from "react-dom";

import App from "./components/App";
import { StateProvider } from "../context/StateProvider";
import reducer, { initialState } from "../context/dashboardContext";

const appRoot = document.getElementById("user-registration-dashboard");

if (appRoot) {
	ReactDOM.render(
		<StateProvider initialState={initialState} reducer={reducer}>
			<App />
		</StateProvider>,
		document.getElementById("user-registration-dashboard")
	);
}
