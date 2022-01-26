import React, { useState } from "react";
import { Flex, Checkbox, FormLabel } from "@chakra-ui/react";
import { Select } from "chakra-react-select";
import LoginSettings from "../screens/LoginSettings";

function InputHandler ({ setting }) {
    const renderOptions = () => {
        let newOptionsRef = [];
        if (setting.options) {
            Object.keys(setting.options).map((key, value) => {
                return newOptionsRef.push({
                    label: setting.options[key],
                    value: key
                });
            });
        }
        return newOptionsRef;
    };

    const renderElement = () => {
        switch (setting.type) {
            case "checkbox":
                return (
                    <Checkbox
                        flex={"0 0 40%"}
                        className="user-registration-setup-wizard__body--checkbox"
                        name={LoginSettings.identifier}
                        id={LoginSettings.identifier}
                    />
                );
            case "select":
                return (
                    <Select
                        focusBorderColor="blue.500"
                        className="user-registration-setup-wizard__body--select"
                        name={LoginSettings.identifier}
                        id={LoginSettings.identifier}
                        options={renderOptions()}
                    />
                );
            case "multiselect":
                return (
                    <Select
                        isMulti
                        focusBorderColor="blue.500"
                        className="user-registration-setup-wizard__body--select"
                        name={LoginSettings.identifier}
                        id={LoginSettings.identifier}
                        options={renderOptions()}
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
