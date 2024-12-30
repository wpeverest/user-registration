import React from "react";

const Header = ({ steps, activeStep, siteURL }) => {
	return (
		<div className="user-registration-setup-wizard__header">
			<div className="user-registration-setup-wizard__header--logo">
				<img
					src={`${siteURL}/wp-content/plugins/user-registration/assets/images/onboard-icons/logo.png`}
					alt=""
				/>
			</div>
			{activeStep.key !== "registration_type" && (
				<ul className="user-registration-setup-wizard__header--nav">
					{steps.map((step, i) => {
						if (i > 0) {
							return (
								<>
									{i > 1 && (
										<li
											className={`center-line ${
												activeStep.key === step.key ||
												step.isDone
													? "done"
													: ""
											} `}
										></li>
									)}
									<li
										key={i}
										className={`user-registration-setup-wizard__header--nav-item step ${
											activeStep.key === step.key
												? "active"
												: ""
										} ${step.isDone ? "done" : ""}`}
										id={`step-${i + 1}`}
									>
										<span className="step-label">
											{step.label}
										</span>
									</li>
								</>
							);
						}
					})}
				</ul>
			)}
		</div>
	);
};

export default Header;
