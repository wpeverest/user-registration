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
    Flex
} from "@chakra-ui/react";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";
import * as Promise from "promise";
import * as Reflect from "reflect-metadata";

/**
 * Internal Dependencies
 */
import Header from "./common/Header";
import InstallPage from "./screens/InstallPage";
import LoginSettings from "./screens/LoginSettings";
import RegistrationSettings from "./screens/RegistrationSettings";
import GeneralSettings from "./screens/GeneralSettings";
import MyAccountSettings from "./screens/MyAccountSettings";
import LastPage from "./screens/LastPage";
import { useStateValue } from "../context/StateProvider";
import { actionTypes } from "../context/gettingStartedContext";

function App () {
    const [{ settings, installPage }, dispatch] = useStateValue();
    const [initiateInstall, setInitiateInstall] = useState(false);

    /* global _UR_ */
    const {
        adminURL,
        siteURL,
        defaultFormURL,
        newFormURL,
        urRestApiNonce,
        onBoardIconsURL
    } = typeof _UR_ !== "undefined" && _UR_;

    const [steps, setSteps] = useState([
        {
            key: "install_pages",
            label: __("Install Pages", "user-registration"),
            title: __("Install Pages", "user-registration"),
            description: __(
                "The following pages will be installed automatically.",
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
            key: "login_settings",
            label: __("Login", "user-registration"),
            title: __("Login Settings", "user-registration"),
            description: __(
                "Customize your login settings as per your preference.",
                "user-registration"
            ),
            isDone: false,
            component: <LoginSettings />
        },
        {
            key: "my_account_settings",
            label: __("My Account", "user-registration"),
            title: __("My Account Settings", "user-registration"),
            description: __(
                "Customize your my account page settings as per your preference.",
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
        apiFetch({
            path: "/wp-json/user-registration/v1/getting-started",
            headers: {
                "X-WP-Nonce": urRestApiNonce
            }
        }).then((data) => {
            const newStepsRef = steps.map((step) => {
                step.sectionSettings = data.options[step.key] ?
                    data.options[step.key] :
                    {};

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

    /**
	 * Skip the section on button press.
	 */
    const handleSkip = () => {
        const newSettingsRef = { ...settings };
        activeStep.sectionSettings.settings.map((individualSettings) => {
            Reflect.deleteProperty(newSettingsRef, individualSettings.id);
        });

        dispatch({
            type: actionTypes.GET_SETTINGS,
            settings: newSettingsRef
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
                "X-WP-Nonce": urRestApiNonce
            },
            data: { install_pages: true }
        }).then((res) => {
            if (res.success) {
                if (res.default_form_id) {
                    dispatch({
                        type: actionTypes.GET_DEFAULT_FORM,
                        defaultFormId: res.default_form_id
                    });
                }

                let newInstallPageRef = { ...installPage };
                newInstallPageRef.registration_page.status = "installing";
                newInstallPageRef.registration_page.slug =
					"/" + res.page_slug[0];

                dispatch({
                    type: actionTypes.GET_INSTALL_PAGE,
                    installPage: newInstallPageRef
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
                        installPage: newInstallPageRef
                    });

                    new Promise(function (resolve, reject) {
                        setTimeout(resolve, 5000);
                    }).then(function () {
                        newInstallPageRef.my_account_page.status = "installed";

                        dispatch({
                            type: actionTypes.GET_INSTALL_PAGE,
                            installPage: newInstallPageRef
                        });
                    });
                });
            } else {
                console.log(res.message);
            }
        });
    };

    /**
	 * Save settings on button press.
	 */
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
                "X-WP-Nonce": urRestApiNonce
            },
            data: { settings: settings }
        }).then((res) => {
            console.log(res);
        });
    };

    return (
        <ChakraProvider>
            <Header steps={steps} activeStep={activeStep} siteURL={siteURL} />
            <div className="user-registration-setup-wizard__body">
                {steps[steps.length - 1].key === activeStep.key ? (
                    cloneElement(activeStep.component, {
                        sectionSettings: activeStep.sectionSettings,
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
                            <Heading as="h2" size="lg" fontSize="24px" mb={4}>
                                {activeStep.title}
                            </Heading>
                        )}
                        {activeStep.description && (
                            <Text fontSize="16px" as="i">
                                {activeStep.description}
                            </Text>
                        )}
                        {cloneElement(activeStep.component, {
                            sectionSettings: activeStep.sectionSettings,
                            siteURL: siteURL
                        })}
                    </Flex>
                )}
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
                                    {__("Go To Dashboard", "user-registration")}
                                </Button>
                            </Link>
                        ) : (
                            <Button
                                variant="outline"
                                colorScheme="blue"
                                onClick={handleBack}
                            >
                                {__("Back", "user-registration")}
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
                                    {__(
                                        "Edit Default Form",
                                        "user-registration"
                                    )}
                                </Button>
                            </Link>
                            <Link href={newFormURL}>
                                <Button
                                    colorScheme="blue"
                                    onClick={handleSaveSettings}
                                >
                                    {__("Create New Form", "user-registration")}
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
                                    {__("Skip", "user-registration")}
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
                                        {__(
                                            "Install & Proceed",
                                            "user-registration"
                                        )}
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
                                        {__("Next", "user-registration")}
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
