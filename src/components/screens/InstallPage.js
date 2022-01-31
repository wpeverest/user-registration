import React from "react";
import {
    Flex,
    Button,
    Heading,
    Text,
    OrderedList,
    Box,
    Checkbox
} from "@chakra-ui/react";

const InstallPage = () => {
    return (
        <Flex
            direction="column"
            justifyContent="space-between"
            alignItems="left"
        >
            <Heading as="h2" size="lg" fontSize="24px" mb={4}>
				Install Pages
            </Heading>
            <Text fontSize="16px" as="i">
				The following pages will be installed automatically.
            </Text>

            <Box
                bg="#ECEFFF"
                w="100%"
                p={4}
                color="#2D3559"
                mt={3}
                borderRadius="md"
            >
                <Checkbox isChecked isReadOnly>
                    <Text fontSize="18px" fontWeight={600}>
						Default Registration Form
                    </Text>
                    <Text fontSize="13px" color="#212121">
						Form id : 2
                    </Text>
                </Checkbox>
            </Box>
            <Box
                bg="#ECEFFF"
                w="100%"
                p={4}
                color="#2D3559"
                mt={3}
                borderRadius="md"
            >
                <Checkbox isChecked isReadOnly>
                    <Text fontSize="18px" fontWeight={600}>
						Registration Page
                    </Text>
                    <Text fontSize="13px" color="#212121">
						/registration
                    </Text>
                </Checkbox>
            </Box>
            <Box
                bg="#ECEFFF"
                w="100%"
                p={4}
                color="#2D3559"
                mt={3}
                borderRadius="md"
            >
                <Checkbox isChecked isReadOnly>
                    <Text fontSize="18px" fontWeight={600}>
						My Account Page
                    </Text>
                    <Text fontSize="13px" color="#212121">
						/my-account
                    </Text>
                </Checkbox>
            </Box>
        </Flex>
    );
};

export default InstallPage;
