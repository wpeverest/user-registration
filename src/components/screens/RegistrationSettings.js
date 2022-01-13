import React from "react";
import { Stack, Flex, FormLabel, Checkbox } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const RegistrationSettings = () => {
    return (
        <Stack direction="column" spacing="8">
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					User Login Option
                </FormLabel>
                <Select
                    focusBorderColor="blue.500"
                    className="user-registration-setup-wizard__body--select"
                    options={[
                        {
                            label: "Auto login",
                            value: "auto"
                        },
                        {
                            label: "Manual Login",
                            value: "manual"
                        },
                        {
                            label: "Email Confirmation",
                            value: "email"
                        },
                        {
                            label: "Admin Approval",
                            value: "admin-approval"
                        }
                    ]}
                />
            </Flex>
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Enable Strong Password
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
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
					Default User Role
                </FormLabel>
                <Select
                    isMulti
                    focusBorderColor="blue.500"
                    className="user-registration-setup-wizard__body--select"
                    options={[
                        {
                            label: "Administrator",
                            value: "administrator"
                        },
                        {
                            label: "Editor",
                            value: "editor"
                        },
                        {
                            label: "Subscriber",
                            value: "subscriber"
                        }
                    ]}
                />
            </Flex>
        </Stack>
    );
};

export default RegistrationSettings;
