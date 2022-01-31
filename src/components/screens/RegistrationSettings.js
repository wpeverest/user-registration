import React from "react";
import { Stack, Flex, Heading, Text } from "@chakra-ui/react";

import InputHandler from "../common/InputHandler";

const RegistrationSettings = ({ sectionSettings }) => {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="left"
        >
            <Heading as="h2" size="lg" fontSize="24px" mb={4}>
				Registration Settings
            </Heading>
            <Text fontSize="16px" as="i">
				Customize your registration settings as per your preference.
            </Text>
            <Stack direction="column" spacing="8" mt={5}>
                {sectionSettings.settings.map((setting, key) => (
                    <InputHandler key={key} setting={setting} />
                ))}
            </Stack>
        </Flex>
    );
};

export default RegistrationSettings;
