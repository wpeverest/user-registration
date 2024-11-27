/**
 *  External Dependencies
 */
import React, { useState, useEffect, Fragment } from "react";
import {
	Flex,
	Text,
	Box,
	Checkbox,
	CircularProgress,
	CircularProgressLabel
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

const InstallPage = () => {
	/* global _UR_WIZARD_ */
	const { defaultFormId, registrationPageSlug, myAccountPageSlug } =
		typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;

	/**
	 * Create the HTML block for the pages to be installed.
	 *
	 * @param {object} page The detals of page to be installed.
	 * @returns
	 */
	const createInstallPageBox = (page, slug) => {
		return (
			<Box
				bg="#F8F9FC"
				w="100%"
				p={4}
				color="#383838"
				mt={3}
				border="1px solid #DEE0E9"
				borderRadius="md"
			>
				<Flex justify="space-between" align="center">
					<Checkbox isChecked={true} isReadOnly>
						<Text fontSize="15px" fontWeight={600} color="#383838">
							{slug === "registration_page"
								? __("Registration Page", "user-registration")
								: __("My Account Page", "user-registration")}
						</Text>
						<Text fontSize="13px" color="#6B6B6B">
							{"/" + page}
						</Text>
					</Checkbox>
					<Text fontSize="12px" color="#475BB2">
						{__("Installed", "user-registration")}
					</Text>
				</Flex>
			</Box>
		);
	};
	return (
		<Fragment>
			<Box
				bg="#F8F9FC"
				w="100%"
				p={4}
				color="#383838"
				mt={3}
				borderRadius="md"
			>
				<Flex justify="space-between" align="center">
					<Checkbox isChecked isReadOnly>
						<Text fontSize="15px" fontWeight={600} color="#383838">
							{__(
								"Default Registration Form",
								"user-registration"
							)}
						</Text>
						{defaultFormId && (
							<Text fontSize="13px" color="#6B6B6B">
								Form id : {defaultFormId}
							</Text>
						)}
					</Checkbox>
					<Flex align="center">
						<Text fontSize="12px" color="#475BB2">
							{__("Installed", "user-registration")}
						</Text>
					</Flex>
				</Flex>
			</Box>
			{createInstallPageBox(registrationPageSlug, "registration_page")}
			{createInstallPageBox(myAccountPageSlug, "my_account_page")}
		</Fragment>
	);
};

export default InstallPage;
