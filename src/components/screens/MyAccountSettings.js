import React from "react";
import { Stack, Flex, FormLabel, Checkbox } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const MyAccountSettings = () => {
    return (
        <Stack direction="column" spacing="8">
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					My account layout
                </FormLabel>
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
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Disable logout confirmation
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Disable profile picture
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Enable Ajax submission on edit profile
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
        </Stack>
    );
};

export default MyAccountSettings;
