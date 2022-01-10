import React from "react";
import ReactDOM from "react-dom";

const Welcome = () => {
    return <h1>Hello World from React boilerplate</h1>;
};

ReactDOM.render(
    <Welcome />,
    document.getElementById("user-registration-setup-wizard")
);
