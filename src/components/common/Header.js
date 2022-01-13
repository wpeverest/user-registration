import React from "react";

const Header = ({ steps, activeStep }) => {
    return (
        <div className="user-registration-setup-wizard__header">
            <ul className="user-registration-setup-wizard__header--nav">
                {steps.map((step, i) => {
                    return (
                        <li
                            key={i}
                            className={`user-registration-setup-wizard__header--nav-item ${
                                activeStep.key === step.key ? "active" : ""
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
