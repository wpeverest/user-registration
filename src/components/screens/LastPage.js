import React from "react";
import { Flex, Heading } from "@chakra-ui/react";

function LastPage () {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
            width={"80%"}
        >
            <Heading as="h2" size="lg" mb={4}>
				You're all setup
            </Heading>
        </Flex>
    );
}

export default LastPage;
