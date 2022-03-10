import React from "react";
import ReactDOM from "react-dom";

import App from "./components/App";
import { StateProvider } from "./context/StateProvider";
import reducer, { initialState } from "./context/gettingStartedContext";

const appRoot = document.getElementById("user-registration-setup-wizard");

if (appRoot) {
    ReactDOM.render(
        <StateProvider initialState={initialState} reducer={reducer}>
            <App />
        </StateProvider>,
        document.getElementById("user-registration-setup-wizard")
    );
}
