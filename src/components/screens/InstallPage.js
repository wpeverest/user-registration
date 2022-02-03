import React, { useState, useEffect } from "react";
import {
    Flex,
    Heading,
    Text,
    Box,
    Checkbox,
    CircularProgress
} from "@chakra-ui/react";
import { useStateValue } from "../../context/StateProvider";

const InstallPage = () => {
    const [{ installPage }] = useStateValue();
    const [counter, setCounter] = useState(0);

    useEffect(() => {
        if (
            installPage.registration_page.status === "installing" ||
			installPage.my_account_page.status === "installing"
        ) {
            const timer = setInterval(() => {
                setCounter((prevCounter) => {
                    if (prevCounter <= 100) {
                        return prevCounter + 20;
                    } else {
                        prevCounter = 0;
                        return prevCounter;
                    }
                });
            }, 700);

            return () => {
                clearInterval(timer);
            };
        } else {
            setCounter(0);
        }
    }, [installPage]);

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
                bg={
                    installPage.registration_page.status === "installed" ?
                        "#ECEFFF" :
                        "#FAFAFC"
                }
                w="100%"
                p={4}
                color={
                    installPage.registration_page.status !== "not_installed" ?
                        "#2D3559" :
                        "#C4C4C4"
                }
                mt={3}
                borderRadius="md"
            >
                <Flex justify="space-between" align="center">
                    <Checkbox
                        isChecked={
                            installPage.registration_page.status === "installed"
                        }
                        isReadOnly
                    >
                        <Text fontSize="18px" fontWeight={600}>
							Registration Page
                        </Text>
                        {installPage.registration_page.status !==
							"not_installed" && (
                            <Text fontSize="13px" color="#212121">
                                {installPage.registration_page.slug}
                            </Text>
                        )}
                    </Checkbox>
                    {installPage.registration_page.status === "installing" ? (
                        <Flex align="center">
                            <Text fontSize="12px" color="#212121">
								Installing...
                            </Text>
                            <CircularProgress
                                value={counter}
                                size="30px"
                                thickness="15px"
                                color="blue.300"
                                ml={3}
                            />
                        </Flex>
                    ) : (
                        installPage.registration_page.status ===
							"installed" && (
                            <Text fontSize="12px" color="#212121">
								Installed
                            </Text>
                        )
                    )}
                </Flex>
            </Box>
            <Box
                bg={
                    installPage.my_account_page.status === "installed" ?
                        "#ECEFFF" :
                        "#FAFAFC"
                }
                w="100%"
                p={4}
                color={
                    installPage.my_account_page.status !== "not_installed" ?
                        "#2D3559" :
                        "#C4C4C4"
                }
                mt={3}
                borderRadius="md"
            >
                <Flex justify="space-between" align="center">
                    <Checkbox
                        isChecked={
                            installPage.my_account_page.status === "installed"
                        }
                        isReadOnly
                    >
                        <Text fontSize="18px" fontWeight={600}>
							My Account Page
                        </Text>
                        {installPage.my_account_page.status !==
							"not_installed" && (
                            <Text fontSize="13px" color="#212121">
                                {installPage.my_account_page.slug}
                            </Text>
                        )}
                    </Checkbox>
                    {installPage.my_account_page.status === "installing" ? (
                        <Flex align="center">
                            <Text fontSize="12px" color="#212121">
								Installing...
                            </Text>
                            <CircularProgress
                                value={counter}
                                size="30px"
                                thickness="15px"
                                color="blue.300"
                                ml={3}
                            />
                        </Flex>
                    ) : (
                        installPage.my_account_page.status === "installed" && (
                            <Text fontSize="12px" color="#212121">
								Installed
                            </Text>
                        )
                    )}
                </Flex>
            </Box>
        </Flex>
    );
};

export default InstallPage;
