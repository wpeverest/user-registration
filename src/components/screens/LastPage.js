/**
 * External Dependencies
 */
import React from "react";
import { Flex, Image, Heading } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

function LastPage ({ onBoardIconsURL }) {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
        >
            <Image
                htmlWidth="80%"
                htmlHeight="auto"
                src={`${onBoardIconsURL}./onboard-end.png`}
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
