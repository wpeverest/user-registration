import React from "react";
import { Flex, Heading, Text, OrderedList, Checkbox } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const GeneralSettings = () => {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
            width={"80%"}
        >
            <Heading as="h2" size="lg" mb={4}>
				Configure General Settings
            </Heading>
            <OrderedList fontSize="xl" width={"100%"}>
                <li>
                    <Flex justifyContent={"space-between"}>
						Anyone can register
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Prevent dashboard access
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
                <li>
                    <Flex justifyContent={"space-between"}>
						Prevent Core Login
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Unistall Option
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

export default GeneralSettings;
