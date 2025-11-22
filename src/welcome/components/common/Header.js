import React from "react";

const Header = ({ steps, activeStep, onBoardIconsURL }) => {
	console.log(onBoardIconsURL);

	return (
		<div className="user-registration-setup-wizard__header">
			<div className="user-registration-setup-wizard__header--logo">
				<img src={`${onBoardIconsURL}/logo.png`} alt="" />
			</div>
			<ul className="user-registration-setup-wizard__header--nav">
				{steps.map((step, i) => {
					return (
						<>
							{i > 0 && (
								<li
									id={`center-line-step-${i}`}
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
									activeStep.key === step.key ? "active" : ""
								} ${step.isDone ? "done" : ""}`}
								id={`step-${i + 1}`}
							>
								<span className="step-label">{step.label}</span>
							</li>
						</>
					);
				})}
			</ul>
		</div>
	);
};

export default Header;
