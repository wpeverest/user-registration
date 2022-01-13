import React, { useState } from "react";
import { ChakraProvider } from "@chakra-ui/react";
import { Button, ButtonGroup } from "@chakra-ui/react";

import Header from "./common/Header";
import InstallPage from "./screens/InstallPage";
import GeneralSettings from "./screens/GeneralSettings";
import RegistrationSettings from "./screens/RegistrationSettings";
import LoginSettings from "./screens/LoginSettings";
import MyAccountSettings from "./screens/MyAccountSettings";
import LastPage from "./screens/LastPage";

function App () {
    const [steps, setSteps] = useState([
        {
            key: "firstStep",
            label: "Install Pages",
            isDone: true,
            component: <InstallPage />
        },
        {
            key: "secondStep",
            label: "General",
            isDone: false,
            component: <GeneralSettings />
        },
        {
            key: "thirdStep",
            label: "Registration",
            isDone: false,
            component: <RegistrationSettings />
        },
        {
            key: "fourthStep",
            label: "Login",
            isDone: false,
            component: <LoginSettings />
        },
        {
            key: "fifthStep",
            label: "My Account",
            isDone: false,
            component: <MyAccountSettings />
        },
        {
            key: "finalStep",
            label: "Final Step",
            isDone: false,
            component: <LastPage />
        }
    ]);
    const [activeStep, setActiveStep] = useState(steps[0]);

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
        <ChakraProvider>
            <Header steps={steps} activeStep={activeStep} />
            <div className="user-registration-setup-wizard__body">
                {activeStep.component}
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
                    disabled={steps[steps.length - 1].key === activeStep.key}
                    onClick={handleNext}
                >
					Next
                </Button>
            </div>
        </ChakraProvider>
    );
}

export default App;
