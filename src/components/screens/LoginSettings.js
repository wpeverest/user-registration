import React from "react";
import { Stack, Flex, FormLabel, Checkbox } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const LoginSettings = () => {
    return (
        <Stack direction="column" spacing="8">
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>Form Template</FormLabel>
                <Select
                    isMulti
                    focusBorderColor="blue.500"
                    className="user-registration-setup-wizard__body--select"
                    options={[
                        {
                            label: "Round",
                            value: "round"
                        },
                        {
                            label: "Flat",
                            value: "flat"
                        },
                        {
                            label: "Bordered",
                            value: "bordered"
                        }
                    ]}
                />
            </Flex>
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Enable Lost Password
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Enable Remember Me
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Enable hide or show password
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Enable Ajax login
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
        </Stack>
    );
};

export default LoginSettings;
