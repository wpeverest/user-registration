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

/**
 * Internal Dependencies
 */
import Header from "./common/Header";
import InstallPage from "./screens/InstallPage";
import GeneralSettings from "./screens/GeneralSettings";
import RegistrationType from "./screens/RegistrationType";
import LastPage from "./screens/LastPage";
import { useStateValue } from "../../context/StateProvider";
import { actionTypes } from "../../context/gettingStartedContext";

function App() {
	const [
		{
			settings,
			registrationPageLink,
			registrationType,
			defaultFormURL,
			membershipDetails,
			installedPages
		},
		dispatch
	] = useStateValue();
	const [disabledLink, setDisabledLink] = useState(false);
	const [nextStepProgess, setNextStepProgess] = useState(false);

	/* global _UR_WIZARD_ */
	const { adminURL, siteURL, urRestApiNonce, onBoardIconsURL, restURL } =
		typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;

	const [steps, setSteps] = useState([
		{
			key: "registration_type",
			label: __("Registration Type", "user-registration"),
			title: __("Registration Type", "user-registration"),
			description: __(
				"Select which registration page type you want to proceed with.",
				"user-registration"
			),
			isDone: true,
			component: <RegistrationType />
		},
		{
			key: "install_pages",
			label: __("Initial Setup Details", "user-registration"),
			isDone: true,
			component: <InstallPage />
		},
		{
			key: "general_settings",
			label: __("Settings", "user-registration"),
			isDone: false,
			component: <GeneralSettings />
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
					var sectionSettings = data.options[key].settings.general;
					sectionSettings.map((individualSettings) => {
						newSettingsRef[individualSettings.id] =
							individualSettings.default;
					});

					var registrationSectionSettings =
						data.options[key].settings.registration;
					registrationSectionSettings.map((individualSettings) => {
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

		if (activeStep.key === "registration_type") {
			// POST
			apiFetch({
				path:
					restURL +
					"user-registration/v1/getting-started/registration-type-selected",
				method: "POST",
				headers: {
					"X-WP-Nonce": urRestApiNonce
				},
				data: { registrationType: registrationType }
			}).then((res) => {
				if (res.success) {
					dispatch({
						type: actionTypes.GET_INSTALLED_PAGES,
						installedPages: res.page_details
					});

					var registrationPageUrl = "";

					if (res.page_details["registration"]) {
						registrationPageUrl =
							res.page_details["registration"]["page_url"];
					} else {
						var membershipRegistrationKey = Object.keys(
							res.page_details
						).filter(function (key) {
							return key.indexOf("membership-registration") === 0;
						});

						registrationPageUrl =
							res.page_details[membershipRegistrationKey[0]][
								"page_url"
							];
					}

					dispatch({
						type: actionTypes.GET_DEFAULT_FORM_URL,
						defaultFormURL:
							res.page_details["default_form_id"].page_url
					});

					dispatch({
						type: actionTypes.GET_DEFAULT_REGISTRATION_PAGE,
						registrationPageLink: registrationPageUrl
					});

					dispatch({
						type: actionTypes.GET_MEMBERSHIP_DETAILS,
						membershipDetails:
							res.page_details["membership_details"]
					});

					setActiveStep(steps[index + 1]);
					setNextStepProgess(true);
				}
			});
		} else {
			setActiveStep(steps[index + 1]);
			setNextStepProgess(true);
		}
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

	const { title, page_url } = membershipDetails || {},
		isMembershipRegistration =
			registrationType === "user_registration_membership_registration",
		rightFooterButtonText = isMembershipRegistration
			? title
			: "Edit Default Form",
		rightFooterButtonLink = isMembershipRegistration
			? page_url
			: defaultFormURL;

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
								gap="36px"
							>
								<Flex
									direction="column"
									justifyContent="space-between"
									alignItems="left"
									gap="12px"
								>
									{activeStep.title && (
										<Heading
											as="h2"
											size="lg"
											fontSize="22px"
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
								</Flex>
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
												typeof registrationPageLink ||
												"" === registrationPageLink
												? ""
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
							) : steps[0].key !== activeStep.key &&
							  steps[1].key !== activeStep.key ? (
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
											rightFooterButtonLink +
												"&end-setup-wizard=1"
										);
									}}
									disabled={disabledLink}
								>
									{__(
										rightFooterButtonText,
										"user-registration"
									)}
								</Button>
							) : (
								<React.Fragment>
									{/* {steps[0].key !== activeStep.key && (
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
									)} */}
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
					{/* <center>
						<Link>
							<Button
								variant="link"
								disabled={disabledLink}
								onClick={() => {
									setDisabledLink(true);
									var extraParams =
										"general_settings" === activeStep.key ||
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
					</center> */}
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
