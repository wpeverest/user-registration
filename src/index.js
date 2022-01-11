import React from "react";
import ReactDOM from "react-dom";

import App from "./components/App";

const appRoot = document.getElementById("user-registration-setup-wizard");

if (appRoot) {
    ReactDOM.render(
        <App />,
        document.getElementById("user-registration-setup-wizard")
    );
}
