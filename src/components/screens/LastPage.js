/**
 * External Dependencies
 */
import React from "react";
import { Flex, Image, Heading, Text } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

function LastPage ({ siteURL }) {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
        >
            <Image
                htmlWidth="80%"
                htmlHeight="auto"
                src={`${siteURL}/wp-content/plugins/user-registration/assets/images/onboard-end.png`}
                alt="Onboard Finish"
            />
            <Heading as="h2" fontSize="24px" mb={4} mt={4}>
                {__(
                    "Congratulations!! You are all set up.",
                    "user-registration"
                )}
            </Heading>
        </Flex>
    );
}

export default LastPage;
