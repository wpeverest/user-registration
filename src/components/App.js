import React, { useState, useEffect, cloneElement } from "react";
import { ChakraProvider, Button, Link } from "@chakra-ui/react";
import apiFetch from "@wordpress/api-fetch";

import Header from "./common/Header";
import InstallPage from "./screens/InstallPage";
import LoginSettings from "./screens/LoginSettings";
import RegistrationSettings from "./screens/RegistrationSettings";
import GeneralSettings from "./screens/GeneralSettings";
import MyAccountSettings from "./screens/MyAccountSettings";
import LastPage from "./screens/LastPage";
import { useStateValue } from "../context/StateProvider";
import { actionTypes } from "../context/gettingStartedContext";

function App() {
	const [{ settings, installPage }, dispatch] = useStateValue();
	const [initiateInstall, setInitiateInstall] = useState(false);

	const { adminURL, siteURL, defaultFormURL, newFormURL, urRestApiNonce } =
		typeof _UR_ !== "undefined" && _UR_;

	const [steps, setSteps] = useState([
		{
			key: "install_pages",
			label: "Install Pages",
			isDone: true,
			component: <InstallPage />,
		},
		{
			key: "general_settings",
			label: "General",
			isDone: false,
			component: <GeneralSettings />,
		},
		{
			key: "registration_settings",
			label: "Registration",
			isDone: false,
			component: <RegistrationSettings />,
		},
		{
			key: "login_settings",
			label: "Login",
			isDone: false,
			component: <LoginSettings />,
		},
		{
			key: "my_account_settings",
			label: "My Account",
			isDone: false,
			component: <MyAccountSettings />,
		},
		{
			key: "final_step",
			label: "Final Step",
			isDone: false,
			component: <LastPage />,
		},
	]);
	const [activeStep, setActiveStep] = useState(steps[0]);

	useEffect(() => {
		apiFetch({
			path: "/wp-json/user-registration/v1/getting-started",
			headers: {
				"X-WP-Nonce": urRestApiNonce,
			},
		}).then((data) => {
			const newStepsRef = steps.map((step) => {
				step.sectionSettings = data.options[step.key]
					? data.options[step.key]
					: {};

				return { ...step };
			});

			const newSettingsRef = {};
			Object.keys(data.options).map((key) => {
				var sectionSettings = data.options[key].settings;
				sectionSettings.map((individualSettings) => {
					newSettingsRef[individualSettings.id] =
						individualSettings.default;
				});
			});
			setSteps(newStepsRef);

			dispatch({
				type: actionTypes.GET_SETTINGS,
				settings: newSettingsRef,
			});
		});
	}, []);

	/**
	 * Progress to next item on menu when next button is clicked.
	 */
	const handleNext = () => {
		const index = steps.findIndex((step) => step.key === activeStep.key);
		setSteps((prevStep) =>
			prevStep.map((step) => {
				if (step.key === activeStep.key) step.isDone = true;
				return step;
			})
		);
		setActiveStep(steps[index + 1]);
	};

	/**
	 * Go back to previous item on menu when back button is clicked.
	 */
	const handleBack = () => {
		const index = steps.findIndex((step) => step.key === activeStep.key);
		if (index === 0) return;

		setSteps((prevStep) =>
			prevStep.map((step) => {
				if (step.key === activeStep.key) step.isDone = false;
				return step;
			})
		);
		setActiveStep(steps[index - 1]);
	};

	const handleSkip = () => {
		const newSettingsRef = { ...settings };
		activeStep.sectionSettings.settings.map((individualSettings) => {
			delete newSettingsRef[individualSettings.id];
		});

		dispatch({
			type: actionTypes.GET_SETTINGS,
			settings: newSettingsRef,
		});
		handleNext();
	};

	/**
	 * Install Pages in backend when Install Pages button is clicked.
	 */
	const handleInstallPages = () => {
		setInitiateInstall(true);
		// POST
		apiFetch({
			path: "/wp-json/user-registration/v1/getting-started/install-pages",
			method: "POST",
			headers: {
				"X-WP-Nonce": urRestApiNonce,
			},
			data: { install_pages: true },
		}).then((res) => {
			if (res.success) {
				let newInstallPageRef = { ...installPage };
				newInstallPageRef.registration_page.status = "installing";
				newInstallPageRef.registration_page.slug =
					"/" + res.page_slug[0];

				dispatch({
					type: actionTypes.GET_INSTALL_PAGE,
					installPage: newInstallPageRef,
				});

				new Promise(function (resolve, reject) {
					setTimeout(resolve, 5000);
				}).then(function () {
					newInstallPageRef.registration_page.status = "installed";
					newInstallPageRef.my_account_page.status = "installing";
					newInstallPageRef.my_account_page.slug =
						"/" + res.page_slug[1];

					dispatch({
						type: actionTypes.GET_INSTALL_PAGE,
						installPage: newInstallPageRef,
					});

					new Promise(function (resolve, reject) {
						setTimeout(resolve, 5000);
					}).then(function () {
						newInstallPageRef.my_account_page.status = "installed";

						dispatch({
							type: actionTypes.GET_INSTALL_PAGE,
							installPage: newInstallPageRef,
						});
					});
				});
			} else {
				console.log(res.message);
			}
		});
	};

	const handleSaveSettings = () => {
		if (
			settings.user_registration_form_setting_enable_strong_password ===
			"no"
		) {
			Reflect.deleteProperty(
				settings,
				"user_registration_form_setting_minimum_password_strength"
			);
		}

		// POST
		apiFetch({
			path: "/wp-json/user-registration/v1/getting-started/save",
			method: "POST",
			headers: {
				"X-WP-Nonce": urRestApiNonce,
			},
			data: { settings: settings },
		}).then((res) => {
			console.log(res);
		});
	};

	return (
		<ChakraProvider>
			<Header steps={steps} activeStep={activeStep} />
			<div className="user-registration-setup-wizard__body">
				{cloneElement(activeStep.component, {
					sectionSettings: activeStep.sectionSettings,
					siteURL: siteURL,
				})}
			</div>
			<div className="user-registration-setup-wizard__footer">
				<div className="user-registration-setup-wizard__footer--left">
					{steps[steps.length - 1].key === activeStep.key ||
					steps[0].key === activeStep.key ? (
						<Link
							href={`${adminURL}/admin.php?page=user-registration`}
						>
							<Button
								variant="outline"
								colorScheme="blue"
								onClick={handleSaveSettings}
							>
								Go To Dashboard
							</Button>
						</Link>
					) : (
						<Button
							variant="outline"
							colorScheme="blue"
							onClick={handleBack}
						>
							Back
						</Button>
					)}
				</div>
				<div className="user-registration-setup-wizard__footer--right">
					{steps[steps.length - 1].key === activeStep.key ? (
						<React.Fragment>
							<Link href={defaultFormURL}>
								<Button
									variant="link"
									colorScheme="blue"
									onClick={handleSaveSettings}
									mr={10}
									ml={10}
								>
									Edit Default Form
								</Button>
							</Link>
							<Link href={newFormURL}>
								<Button
									colorScheme="blue"
									onClick={handleSaveSettings}
								>
									Create New Form
								</Button>
							</Link>
						</React.Fragment>
					) : (
						<React.Fragment>
							{steps[0].key !== activeStep.key && (
								<Button
									variant="link"
									colorScheme="blue"
									onClick={handleSkip}
									mr={10}
									ml={10}
								>
									Skip
								</Button>
							)}
							{steps[0].key === activeStep.key &&
							installPage.my_account_page.status !==
								"installed" ? (
								<Button
									colorScheme="blue"
									disabled={initiateInstall}
									onClick={handleInstallPages}
								>
									Install & Proceed
								</Button>
							) : (
								<Button
									colorScheme="blue"
									disabled={
										steps[steps.length - 1].key ===
										activeStep.key
									}
									onClick={handleNext}
								>
									Next
								</Button>
							)}
						</React.Fragment>
					)}
				</div>
			</div>
		</ChakraProvider>
	);
}

export default App;
