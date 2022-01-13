import React from "react";
import { Stack, Flex, Checkbox, FormLabel } from "@chakra-ui/react";
import { Select } from "chakra-react-select";

const GeneralSettings = () => {
    return (
        <Stack direction="column" spacing="8">
            <Flex justify={"space-between"} align="center">
                <FormLabel sx={{ fontWeight: "bold" }}>
					Anyone can register
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
            <Flex justifyContent={"space-between"}>
                <FormLabel sx={{ fontWeight: "bold" }}>
					Prevent dashboard access
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
            <Flex justifyContent={"space-between"}>
                <FormLabel sx={{ fontWeight: "bold" }}>
					Prevent Core Login
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>

            <Flex justifyContent={"space-between"}>
                <FormLabel sx={{ fontWeight: "bold" }}>
					Unistall Option
                </FormLabel>
                <Checkbox
                    flex={"0 0 40%"}
                    className="user-registration-setup-wizard__body--checkbox"
                />
            </Flex>
        </Stack>
    );
};

export default GeneralSettings;
