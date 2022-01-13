import React from "react";
import { Flex, Button, Heading, Text, OrderedList } from "@chakra-ui/react";

const InstallPage = () => {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="center"
        >
            <Heading as="h2" size="lg" mb={4}>
				Install User Registration Plugin's Pages
            </Heading>
            <Text fontSize="xl">
				The following pages will be installed automatically :
                <OrderedList>
                    <li>Default Registration Form</li>
                    <li>Registration Page</li>
                    <li>My Account Page</li>
                </OrderedList>
            </Text>
            <Flex justifyContent={"center"}>
                <Button
                    size="lg"
                    backgroundColor={"blue"}
                    color="white"
                    mt="24px"
                >
					Install Pages
                </Button>
            </Flex>
        </Flex>
    );
};

export default InstallPage;
