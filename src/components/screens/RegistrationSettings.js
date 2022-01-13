import React from "react";
import { Flex, Heading, Text, OrderedList, Checkbox } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const RegistrationSettings = () => {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
            width={"80%"}
        >
            <Heading as="h2" size="lg" mb={4}>
				Registration Forms Settings
            </Heading>
            <OrderedList fontSize="xl" width={"100%"}>
                <li>
                    <Flex justifyContent={"space-between"}>
						User Login Option
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
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Enable Strong Password
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Form Template
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
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Default User Role
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
                </li>
            </OrderedList>
        </Flex>
    );
};

export default RegistrationSettings;
