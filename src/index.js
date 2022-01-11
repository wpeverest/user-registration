import React from "react";
import ReactDOM from "react-dom";

import App from "./components/App";

const Welcome = () => {
    return <App />;
};

ReactDOM.render(
    <Welcome />,
    document.getElementById("user-registration-setup-wizard")
);
