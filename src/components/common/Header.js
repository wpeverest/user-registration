import React from "react";

const Header = ({ steps, activeStep, siteURL }) => {
	return (
		<div className="user-registration-setup-wizard__header">
			<div className="user-registration-setup-wizard__header--logo">
				<img
					src={`${siteURL}/wp-content/plugins/user-registration/assets/images/onboard-icons/Logo.png`}
					alt=""
				/>
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
