import React from "react";
import {
    Flex,
    Switch,
    FormLabel,
    Box,
    Text,
    Tooltip,
    useRadio,
    useRadioGroup,
    HStack
} from "@chakra-ui/react";
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

    const RadioCard = (props) => {
        const { radioProps, label } = props;
        const { state, getInputProps, getCheckboxProps } = useRadio(radioProps);

        const input = getInputProps();
        const checkbox = getCheckboxProps();

        return (
            <Box as="label">
                <input {...input} />
                <Box
                    {...checkbox}
                    cursor="pointer"
                    borderWidth="1px"
                    borderRadius="md"
                    boxShadow="md"
                    _checked={{
                        bg: "#F5F7FF",
                        color: "#475BB2",
                        borderColor: "#475BB2"
                    }}
                    _focus={{
                        boxShadow: "outline"
                    }}
                    px={5}
                    py={3}
                >
                    <Text fontSize="18px" color={state.isChecked && "#818181"}>
                        {label}
                    </Text>
                </Box>
            </Box>
        );
    };

    const handleInputChange = (fieldType, fieldIdentifier, event) => {
        const newChangedValueRef = { ...settings };

        if (fieldType === "checkbox") {
            newChangedValueRef[fieldIdentifier] = event.target.checked;
        } else if (fieldType === "select") {
            newChangedValueRef[fieldIdentifier] = event.value;
        } else if (fieldType === "radio") {
            newChangedValueRef[fieldIdentifier] = event;
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
    };

    const renderElement = () => {
        switch (setting.type) {
            case "checkbox":
                return (
                    <Switch
                        flex={"0 0 40%"}
                        className="user-registration-setup-wizard__body--checkbox"
                        name={setting.id}
                        id={setting.id}
                        onChange={(e) =>
                            handleInputChange(setting.type, setting.id, e)
                        }
                        isChecked={!!settings[setting.id]}
                        defaultChecked={setting.default}
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

            case "radio":
                const { getRootProps, getRadioProps } = useRadioGroup({
                    name: setting.id,
                    defaultValue: settings[setting.id] ?
                        settings[setting.id].toString() :
                        setting.default.toString(),
                    onChange: (data) => {
                        handleInputChange(setting.type, setting.id, data);
                    }
                });
                const group = getRootProps();

                return (
                    <HStack {...group}>
                        {Object.keys(setting.options).map((value, key) => {
                            return (
                                <RadioCard
                                    key={value}
                                    radioProps={getRadioProps({
                                        value: key.toString()
                                    })}
                                    label={setting.options[value]}
                                />
                            );
                        })}
                    </HStack>
                );
        }
    };
    return (
        <Flex justify={"space-between"} align="center">
            <Flex align="center">
                <FormLabel sx={{ fontWeight: "bold", fontSize: "18px" }}>
                    {setting.title}
                </FormLabel>
                <Tooltip label={setting.desc} hasArrow fontSize="xs">
                    <span className="dashicons dashicons-editor-help" />
                </Tooltip>
            </Flex>
            {renderElement()}
        </Flex>
    );
}

export default InputHandler;
