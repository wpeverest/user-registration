import React, { useState } from "react";
import Header from "./common/Header";
import InstallPage from "./screens/InstallPage";

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
            component: ""
        },
        {
            key: "thirdStep",
            label: "Registration",
            isDone: false,
            component: ""
        },
        {
            key: "fourthStep",
            label: "Login",
            isDone: false,
            component: ""
        },
        {
            key: "fifthStep",
            label: "My Account",
            isDone: false,
            component: ""
        },
        {
            key: "finalStep",
            label: "Final Step",
            isDone: false,
            component: ""
        }
    ]);
    const [activeStep, setActiveStep] = useState(steps[0]);

    const handleNext = () => {
        if (steps[steps.length - 1].key === activeStep.key) {
            alert("You have completed all steps.");
            return;
        }

        const index = steps.findIndex((x) => x.key === activeStep.key);
        setSteps((prevStep) =>
            prevStep.map((x) => {
                if (x.key === activeStep.key) x.isDone = true;
                return x;
            })
        );
        setActiveStep(steps[index + 1]);
    };

    const handleBack = () => {
        const index = steps.findIndex((x) => x.key === activeStep.key);
        if (index === 0) return;

        setSteps((prevStep) =>
            prevStep.map((x) => {
                if (x.key === activeStep.key) x.isDone = false;
                return x;
            })
        );
        setActiveStep(steps[index - 1]);
    };
    return (
        <React.Fragment>
            <Header steps={steps} activeStep={activeStep} />
            <div className="user-registration-setup-wizard__body">
                {activeStep.component}
            </div>
            <div className="user-registration-setup-wizard__footer">
                <input
                    type="button"
                    className="button button-secondary"
                    value="Back"
                    onClick={handleBack}
                    disabled={steps[0].key === activeStep.key}
                />
                <input
                    type="button"
                    className="button button-primary"
                    value={
                        steps[steps.length - 1].key !== activeStep.key ?
                            "Next" :
                            "Submit"
                    }
                    onClick={handleNext}
                />
            </div>
        </React.Fragment>
    );
}

export default App;
