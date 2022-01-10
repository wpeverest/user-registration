import React from "react";
import ReactDOM from "react-dom";

const Welcome = () => {
    console.log(document.getElementById("user-registration-setup-wizard"));
    return <h1>Hello World from React boilerplate</h1>;
};

ReactDOM.render(
    <Welcome />,
    document.getElementById("user-registration-setup-wizard")
);
