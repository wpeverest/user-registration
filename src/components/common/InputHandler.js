import React from "react";
import { Flex, Checkbox, FormLabel } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

import { useStateValue } from "../../context/StateProvider";
import { actionTypes } from "../../context/gettingStartedContext";

function InputHandler ({ setting }) {
    const [{ settings }, dispatch] = useStateValue();

    const renderOptions = () => {
        let newOptionsRef = [];

        if (setting.options) {
            let newSelectedOptionRef = [setting.default];

            Object.keys(setting.options).map((key, value) => {
                if (settings[setting.id]) {
                    if (
                        typeof settings[setting.id] !== "string" &&
						Object.values(settings[setting.id]).includes(key)
                    ) {
                        newSelectedOptionRef.push(value);
                    } else {
                        newSelectedOptionRef =
							key === settings[setting.id] ?
							    [value] :
							    newSelectedOptionRef;
                    }
                }

                newOptionsRef.default =
					typeof newSelectedOptionRef === "object" &&
					newSelectedOptionRef.filter((value, index) => {
					    return newSelectedOptionRef.indexOf(value) === index;
					});

                return newOptionsRef.push({
                    label: setting.options[key],
                    value: key
                });
            });
        }

        return newOptionsRef;
    };

    const handleInputChange = (fieldType, fieldIdentifier, event) => {
        const newChangedValueRef = { ...settings };

        if (fieldType === "checkbox") {
            newChangedValueRef[fieldIdentifier] = event.target.checked;
        } else if (fieldType === "select") {
            newChangedValueRef[fieldIdentifier] = event.value;
        } else {
            const multiselectValue = [];
            event.map((eve) => {
                multiselectValue.push(eve.value);
            });

            newChangedValueRef[fieldIdentifier] = multiselectValue;
        }

        dispatch({
            type: actionTypes.GET_SETTINGS,
            settings: newChangedValueRef
        });

        console.log(settings);
    };

    const renderElement = () => {
        switch (setting.type) {
            case "checkbox":
                return (
                    <Checkbox
                        flex={"0 0 40%"}
                        className="user-registration-setup-wizard__body--checkbox"
                        name={setting.id}
                        id={setting.id}
                        onChange={(e) =>
                            handleInputChange(setting.type, setting.id, e)
                        }
                        isChecked={
                            settings[setting.id] ? true : setting.default
                        }
                    />
                );
            case "select":
                return (
                    <Select
                        focusBorderColor="blue.500"
                        className="user-registration-setup-wizard__body--select"
                        name={setting.id}
                        id={setting.id}
                        options={renderOptions()}
                        onChange={(e) =>
                            handleInputChange(setting.type, setting.id, e)
                        }
                        defaultValue={renderOptions()[renderOptions().default]}
                    />
                );
            case "multiselect":
                let defaultSelectedOption = [];
                renderOptions().default.map((key) => {
                    return defaultSelectedOption.push(renderOptions()[key]);
                });
                return (
                    <Select
                        isMulti
                        focusBorderColor="blue.500"
                        className="user-registration-setup-wizard__body--select"
                        name={setting.id}
                        id={setting.id}
                        options={renderOptions()}
                        onChange={(e) =>
                            handleInputChange(setting.type, setting.id, e)
                        }
                        defaultValue={defaultSelectedOption}
                    />
                );
        }
    };
    return (
        <Flex justify={"space-between"} align="center">
            <FormLabel sx={{ fontWeight: "bold" }}>{setting.title}</FormLabel>
            {renderElement()}
        </Flex>
    );
}

export default InputHandler;
