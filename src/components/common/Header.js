import React from "react";

const Header = ({ steps, activeStep }) => {
    return (
        <div className="user-registration-setup-wizard__header">
            <div className="user-registration-setup-wizard__header--logo">
                <img
                    src="https://user.local/wp-content/plugins/user-registration/assets/images/UR-Logo.png"
                    alt=""
                />
                <h1>User Registration</h1>
            </div>
            <ul className="user-registration-setup-wizard__header--nav">
                {steps.map((step, i) => {
                    return (
                        <li
                            key={i}
                            className={`user-registration-setup-wizard__header--nav-item step ${
                                activeStep.key === step.key ? "active" : ""
                            } ${step.isDone ? "done" : ""}`}
                            id={`step-${i + 1}`}
                        >
                            <span className="step-label">{step.label}</span>
                            <p className="step-identifier" />
                        </li>
                    );
                })}
            </ul>
        </div>
    );
};

export default Header;
