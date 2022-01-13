import React from "react";
import { Flex, Heading, Text, OrderedList, Checkbox } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const MyAccountSettings = () => {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
            width={"80%"}
        >
            <Heading as="h2" size="lg" mb={4}>
				My Account Settings
            </Heading>
            <OrderedList fontSize="xl" width={"100%"}>
                <li>
                    <Flex justifyContent={"space-between"}>
						My account layout
                        <Select
                            focusBorderColor="blue.500"
                            className="user-registration-setup-wizard__body--select"
                            options={[
                                {
                                    label: "Vertical",
                                    value: "vertical"
                                },
                                {
                                    label: "Horizontal",
                                    value: "horizontal"
                                }
                            ]}
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Disable logout confirmation
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Disable profile picture
                        <Checkbox
                            flex={"0 0 40%"}
                            className="user-registration-setup-wizard__body--checkbox"
                        />
                    </Flex>
                </li>
                <li>
                    <Flex justifyContent={"space-between"}>
						Enable Ajax submission on edit profile
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

export default MyAccountSettings;
