import React, { useState } from "react";

const Header = () => {
    const [steps, setSteps] = useState([
        {
            key: "firstStep",
            label: "Install Pages",
            isDone: true,
            component: ""
        },
        {
            key: "secondStep",
            label: "General",
            isDone: false,
            component: ""
        },
        {
            key: "thirdStep",
            label: "Registration",
            isDone: false,
            component: ""
        },
        {
            key: "fourthStep",
            label: "Login",
            isDone: false,
            component: ""
        },
        {
            key: "fifthStep",
            label: "My Account",
            isDone: false,
            component: ""
        },
        {
            key: "finalStep",
            label: "Final Step",
            isDone: false,
            component: ""
        }
    ]);

    return (
        <div className="user-registration-setup-wizard__header">
            <ul className="user-registration-setup-wizard__header--nav">
                {steps.map((step, i) => {
                    return (
                        <li
                            key={i}
                            className={`user-registration-setup-wizard__header--nav-item ${
                                i === 0 ? "active" : ""
                            } ${step.isDone ? "done" : ""}`}
                        >
                            <div id={`step-${i + 1}`} className="step">
                                <p className="step-identifier">{i + 1}</p>
                                <span className="step-label">{step.label}</span>
                            </div>
                        </li>
                    );
                })}
            </ul>
        </div>
    );
};

export default Header;
