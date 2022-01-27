import React, { useState, useEffect, cloneElement } from "react";
import { ChakraProvider } from "@chakra-ui/react";
import { Button, ButtonGroup } from "@chakra-ui/react";
import apiFetch from "@wordpress/api-fetch";
import { StateProvider } from "../context/StateProvider";
import reducer, { initialState } from "../context/gettingStartedContext";

import Header from "./common/Header";
import InstallPage from "./screens/InstallPage";
import LoginSettings from "./screens/LoginSettings";
import RegistrationSettings from "./screens/RegistrationSettings";
import GeneralSettings from "./screens/GeneralSettings";
import MyAccountSettings from "./screens/MyAccountSettings";
import LastPage from "./screens/LastPage";

function App () {
    const [steps, setSteps] = useState([
        {
            key: "install_pages",
            label: "Install Pages",
            isDone: true,
            component: <InstallPage />
        },
        {
            key: "general_settings",
            label: "General",
            isDone: false,
            component: <GeneralSettings />
        },
        {
            key: "registration_settings",
            label: "Registration",
            isDone: false,
            component: <RegistrationSettings />
        },
        {
            key: "login_settings",
            label: "Login",
            isDone: false,
            component: <LoginSettings />
        },
        {
            key: "my_account_settings",
            label: "My Account",
            isDone: false,
            component: <MyAccountSettings />
        },
        {
            key: "final_step",
            label: "Final Step",
            isDone: false,
            component: <LastPage />
        }
    ]);
    const [activeStep, setActiveStep] = useState(steps[0]);

    useEffect(() => {
        apiFetch({
            path: "/wp-json/user-registration/v1/getting-started"
        }).then((settings) => {
            const newStepsRef = steps.map((step) => {
                step.sectionSettings = settings[step.key] ?
                    settings[step.key] :
                    {};

                return { ...step };
            });

            setSteps(newStepsRef);
        });
    }, []);

    /**
	 * Progress to next item on menu when next button is clicked.
	 */
    const handleNext = () => {
        if (steps[steps.length - 1].key === activeStep.key) {
            alert("You have completed all steps.");
            return;
        }

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

    return (
        <StateProvider initialState={initialState} reducer={reducer}>
            <ChakraProvider>
                <Header steps={steps} activeStep={activeStep} />
                <div className="user-registration-setup-wizard__body">
                    {cloneElement(activeStep.component, {
                        sectionSettings: activeStep.sectionSettings
                    })}
                </div>
                <div className="user-registration-setup-wizard__footer">
                    <Button
                        colorScheme="gray"
                        onClick={handleBack}
                        disabled={steps[0].key === activeStep.key}
                    >
						Back
                    </Button>
                    <Button
                        colorScheme="blue"
                        disabled={
                            steps[steps.length - 1].key === activeStep.key
                        }
                        onClick={handleNext}
                    >
						Next
                    </Button>
                </div>
            </ChakraProvider>
        </StateProvider>
    );
}

export default App;
