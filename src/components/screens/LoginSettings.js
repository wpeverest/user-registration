import React from "react";
import { Flex, Heading, Text, OrderedList, Checkbox } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const LoginSettings = () => {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
            width={"80%"}
        >
            <Heading as="h2" size="lg" mb={4}>
				Login Form Settings
            </Heading>
            <OrderedList fontSize="xl" width={"100%"}>
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
						Enable Lost Password
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>

                <li>
                    <Flex justifyContent={"space-between"}>
						Enable Remember Me
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Enable hide or show password
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Enable Ajax login
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
            </OrderedList>
        </Flex>
    );
};

export default LoginSettings;
