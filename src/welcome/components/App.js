/**
 * External Dependencies
 */
import React, { useState, useEffect, cloneElement } from "react";
import {
	ChakraProvider,
	Button,
	Link,
	Heading,
	Text,
	Flex,
	Center,
	Spinner,
	Box
} from "@chakra-ui/react";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";
import * as Promise from "promise";

/**
 * Internal Dependencies
 */
import Header from "./common/Header";
import InstallPage from "./screens/InstallPage";
import RegistrationSettings from "./screens/RegistrationSettings";
import GeneralSettings from "./screens/GeneralSettings";
import MyAccountSettings from "./screens/MyAccountSettings";
import LastPage from "./screens/LastPage";
import { useStateValue } from "../../context/StateProvider";
import { actionTypes } from "../../context/gettingStartedContext";

function App() {
	const [{ settings, registrationPageLink }, dispatch] = useStateValue();
	const [disabledLink, setDisabledLink] = useState(false);
	const [nextStepProgess, setNextStepProgess] = useState(false);

	/* global _UR_WIZARD_ */
	const {
		adminURL,
		siteURL,
		defaultFormURL,
		urRestApiNonce,
		onBoardIconsURL,
		restURL,
		registrationPageURL
	} = typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;

	const [steps, setSteps] = useState([
		{
			key: "install_pages",
			label: __("Install Pages", "user-registration"),
			title: __("Install Pages", "user-registration"),
			description: __(
				"The following pages have been created automatically.",
				"user-registration"
			),
			isDone: true,
			component: <InstallPage />
		},
		{
			key: "general_settings",
			label: __("General", "user-registration"),
			title: __("General Settings", "user-registration"),
			description: __(
				"Customize your general settings as per your preference.",
				"user-registration"
			),
			isDone: false,
			component: <GeneralSettings />
		},
		{
			key: "registration_settings",
			label: __("Registration", "user-registration"),
			title: __("Registration Settings", "user-registration"),
			description: __(
				"Customize your registration settings as per your preference.",
				"user-registration"
			),
			isDone: false,
			component: <RegistrationSettings />
		},
		{
			key: "my_account_settings",
			label: __("My Account", "user-registration"),
			title: __("My Account Settings", "user-registration"),
			description: __(
				"Customize my account page settings as per your preference.",
				"user-registration"
			),
			isDone: false,
			component: <MyAccountSettings />
		},
		{
			key: "final_step",
			label: __("Final Step", "user-registration"),
			isDone: false,
			component: <LastPage />
		}
	]);
	const [activeStep, setActiveStep] = useState(steps[0]);

	/**
	 * Fetch settings from api on first load.
	 */
	useEffect(() => {
		if (Object.keys(settings).length < 1) {
			apiFetch({
				path: restURL + "user-registration/v1/getting-started",
				headers: {
					"X-WP-Nonce": urRestApiNonce
				}
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
					settings: newSettingsRef
				});
			});
		} else {
			const params = new URLSearchParams(window.location.href);
			if (params.get("step") && !nextStepProgess) {
				const index = steps.findIndex(
					(step) => step.key === params.get("step")
				);

				if ("final_step" === params.get("step")) {
					return;
				}

				setSteps((prevStep) =>
					prevStep.map((step) => {
						if (step.key === params.get("step")) {
							if ("install_pages" === params.get("step")) {
								if (null !== params.get("installed")) {
									step.isDone = true;
								} else {
									step.isDone = false;
								}
							} else {
								step.isDone = true;
							}
						}
						return step;
					})
				);

				if ("install_pages" === params.get("step")) {
					if (null !== params.get("installed")) {
						setActiveStep(steps[index + 1]);
					} else {
						setActiveStep(steps[index]);
					}
				} else {
					setActiveStep(steps[index + 1]);
				}
			}
		}
	}, [settings]);

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
		setNextStepProgess(true);
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

	/**
	 * Skip the section on button press.
	 */
	const handleSkip = () => {
		var newSettingsRef = { ...settings };
		activeStep.sectionSettings.settings.map((individualSettings) => {
			newSettingsRef = Object.keys(newSettingsRef)
				.filter((key) => key !== individualSettings.id)
				.reduce((obj, key) => {
					obj[key] = newSettingsRef[key];
					return obj;
				}, {});
		});

		dispatch({
			type: actionTypes.GET_SETTINGS,
			settings: newSettingsRef
		});
		handleNext();
	};

	/**
	 * Save settings on button press.
	 */
	const handleSaveSettings = (redirectLink) => {
		var newSettingsRef = { ...settings };
		if (
			settings.user_registration_form_setting_enable_strong_password ===
			"no"
		) {
			newSettingsRef = Object.keys(settings)
				.filter(
					(key) =>
						key !==
						"user_registration_form_setting_minimum_password_strength"
				)
				.reduce((obj, key) => {
					obj[key] = settings[key];
					return obj;
				}, {});
		}

		if (activeStep.key === "final_step") {
			newSettingsRef = { ...newSettingsRef };
			newSettingsRef.user_registration_end_setup_wizard = true;
		}

		// POST
		apiFetch({
			path: restURL + "user-registration/v1/getting-started/save",
			method: "POST",
			headers: {
				"X-WP-Nonce": urRestApiNonce
			},
			data: { settings: newSettingsRef }
		}).then((res) => {
			if (res.success) {
				if ("" !== redirectLink) {
					window.location.href = redirectLink;
				} else {
					handleNext();
				}
			}
		});
	};

	return (
		<ChakraProvider>
			{Object.keys(settings).length > 0 ? (
				<>
					<Header
						steps={steps}
						activeStep={activeStep}
						siteURL={siteURL}
					/>
					<div className="user-registration-setup-wizard__body">
						{steps[steps.length - 1].key === activeStep.key ? (
							cloneElement(activeStep.component, {
								sectionSettings:
									activeStep.sectionSettings && {},
								siteURL: siteURL,
								onBoardIconsURL: onBoardIconsURL
							})
						) : (
							<Flex
								direction="column"
								justifyContent="space-between"
								alignItems="left"
							>
								{activeStep.title && (
									<Heading
										as="h2"
										size="lg"
										fontSize="22px"
										mb={4}
										color="#383838"
										fontWeight="600"
									>
										{activeStep.title}
									</Heading>
								)}
								{activeStep.description && (
									<Text
										fontSize="16px"
										as="i"
										color="#6B6B6B"
									>
										{activeStep.description}
									</Text>
								)}
								{cloneElement(activeStep.component, {
									sectionSettings: activeStep.sectionSettings,
									siteURL: siteURL,
									onBoardIconsURL: onBoardIconsURL
								})}
							</Flex>
						)}
					</div>
					<div className="user-registration-setup-wizard__footer">
						<div className="user-registration-setup-wizard__footer--left">
							{steps[steps.length - 1].key === activeStep.key ? (
								<Button
									variant="outline"
									colorScheme="gray"
									onClick={() => {
										setDisabledLink(true);
										handleSaveSettings(
											"undefined" ===
												typeof registrationPageLink
												? registrationPageURL
												: registrationPageLink
										);
									}}
									disabled={disabledLink}
									style={{
										backgroundColor: "#FAFAFA",
										color: "#6B6B6B",
										border: "1px solid #999999"
									}}
								>
									{__(
										"View Registration Page",
										"user-registration"
									)}
								</Button>
							) : steps[0].key !== activeStep.key ? (
								<Button
									variant="outline"
									onClick={handleBack}
									style={{
										backgroundColor: "#FAFAFA",
										color: "#6B6B6B",
										border: "1px solid #999999"
									}}
								>
									{__("Back", "user-registration")}
								</Button>
							) : (
								""
							)}
						</div>
						<div className="user-registration-setup-wizard__footer--right">
							{steps[steps.length - 1].key === activeStep.key ? (
								<Button
									colorScheme="blue"
									backgroundColor="#475BB2 !important"
									color="#FAFAFA !important"
									onClick={() => {
										setDisabledLink(true);
										handleSaveSettings(
											defaultFormURL +
												"&end-setup-wizard=1"
										);
									}}
									disabled={disabledLink}
								>
									{__(
										"Edit Default Form",
										"user-registration"
									)}
								</Button>
							) : (
								<React.Fragment>
									{steps[0].key !== activeStep.key && (
										<Button
											variant="link"
											colorScheme="gray"
											onClick={handleSkip}
											mr={10}
											ml={10}
											fontStyle="italic"
											textDecoration="underline"
											fontSize="14px"
											fontWeight="400"
											background="none !important"
											color="#6B6B6B !important"
											border="none !important"
										>
											{__(
												"Skip this step",
												"user-registration"
											)}
										</Button>
									)}
									<Button
										colorScheme="blue"
										backgroundColor="#475BB2 !important"
										color="#FAFAFA !important"
										disabled={
											steps[steps.length - 1].key ===
											activeStep.key
										}
										onClick={() => {
											handleSaveSettings("");
										}}
									>
										{__("Next", "user-registration")}
									</Button>
								</React.Fragment>
							)}
						</div>
					</div>
					<center>
						<Link>
							<Button
								variant="link"
								disabled={disabledLink}
								onClick={() => {
									setDisabledLink(true);
									var extraParams =
										"my_account_settings" ===
											activeStep.key ||
										"final_step" === activeStep.key
											? ""
											: `&activeStep=${activeStep.key}`;
									handleSaveSettings(
										`${adminURL}admin.php?page=user-registration-dashboard&end-setup-wizard=1${extraParams}`
									);
								}}
								mr={10}
								ml={10}
								pt="6"
								fontStyle="italic"
								textDecoration="underline"
								fontSize="16px"
								fontWeight="400"
								gap="10px"
								className="button-tertiary"
								background="none !important"
								color="#6B6B6B !important"
								border="none !important"
							>
								{__("Go to dashboard", "user-registration")}
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="12"
									height="12"
									fill="currentColor"
									className="bi bi-arrow-right"
									viewBox="0 0 16 16"
									marginleft="100px"
								>
									<path
										fillRule="evenodd"
										d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"
									/>
								</svg>
							</Button>
						</Link>
					</center>
				</>
			) : (
				<Box display="flex" justifyContent="center" padding="250px">
					<Spinner size={"lg"} />
				</Box>
			)}
		</ChakraProvider>
	);
}

export default App;
