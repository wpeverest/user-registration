/**
 *  Internal Dependencies
 */
import React, { useState, useEffect, Fragment } from "react";
import {
    Flex,
    Heading,
    Text,
    Box,
    Checkbox,
    CircularProgress
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import { useStateValue } from "../../context/StateProvider";

const InstallPage = () => {
    const [{ installPage, defaultFormId }] = useStateValue();
    const [counter, setCounter] = useState(0);

    /**
	 * Change counter every time installPage state is changed to show spinner while installing.
	 */
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

    /**
	 * Create the HTML block for the pages to be installed.
	 *
	 * @param {object} page The detals of page to be installed.
	 * @returns
	 */
    const createInstallPageBox = (page, slug) => {
        return (
            <Box
                bg={page.status === "installed" ? "#ECEFFF" : "#FAFAFC"}
                w="100%"
                p={4}
                color={page.status !== "not_installed" ? "#2D3559" : "#C4C4C4"}
                mt={3}
                borderRadius="md"
            >
                <Flex justify="space-between" align="center">
                    <Checkbox
                        isChecked={page.status === "installed"}
                        isReadOnly
                    >
                        <Text fontSize="18px" fontWeight={600}>
                            {slug === "registration_page" ?
                                __("Registration Page", "user-registration") :
                                __("My Account Page", "user-registration")}
                        </Text>
                        {page.status !== "not_installed" && (
                            <Text fontSize="13px" color="#212121">
                                {page.slug}
                            </Text>
                        )}
                    </Checkbox>
                    {page.status === "installing" ? (
                        <Flex align="center">
                            <Text fontSize="12px" color="#212121">
                                {__("Installing...", "user-registration")}
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
                        page.status === "installed" && (
                            <Text fontSize="12px" color="#212121">
                                {__("Installed", "user-registration")}
                            </Text>
                        )
                    )}
                </Flex>
            </Box>
        );
    };
    return (
        <Fragment>
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
                        {__("Default Registration Form", "user-registration")}
                    </Text>
                    <Text fontSize="13px" color="#212121">
						Form id : {defaultFormId}
                    </Text>
                </Checkbox>
            </Box>
            {createInstallPageBox(
                installPage.registration_page,
                "registration_page"
            )}
            {createInstallPageBox(
                installPage.my_account_page,
                "my_account_page"
            )}
        </Fragment>
    );
};

export default InstallPage;
