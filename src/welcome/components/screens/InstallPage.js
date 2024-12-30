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
	CircularProgressLabel,
	Link
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

import { useStateValue } from "../../../context/StateProvider";

const InstallPage = () => {
	/* global _UR_WIZARD_ */
	const { defaultFormId, registrationPageSlug, myAccountPageSlug } =
		typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;
	const [{ installedPages }, dispatch] = useStateValue();
	/**
	 * Create the HTML block for the pages to be installed.
	 *
	 * @param {object} page The detals of page to be installed.
	 * @returns
	 */
	const CreateInstallPageBox = ({ pageDetails }) => {
		return (
			<Box
				bg="#F8F9FC"
				w="100%"
				p="10px 16px"
				color="#383838"
				border="1px solid #EDEFF7"
				borderRadius="md"
				height="75px"
				display="flex"
			>
				<Flex justify="space-between" align="center" width="100%">
					<Checkbox
						isChecked={true}
						isReadOnly
						className="user-registration-setup-wizard__body--checkbox"
					>
						<Text fontSize="15px" fontWeight={600} color="#383838">
							{pageDetails.title}
						</Text>
						<Text fontSize="14px" color="#6B6B6B">
							{pageDetails.page_slug}
						</Text>
					</Checkbox>
					{pageDetails.page_url !== "" && (
						<Link
							href={pageDetails.page_url}
							isExternal
							textDecoration="underline"
							fontSize="12px"
							color="#475BB2"
						>
							{pageDetails.page_url_text}
						</Link>
					)}
				</Flex>
			</Box>
		);
	};

	return (
		<Fragment>
			<Flex gap="20px" flexDirection="column">
				{Object.keys(installedPages).map((key) => {
					return (
						<CreateInstallPageBox
							key={key}
							pageDetails={installedPages[key]}
						/>
					);
				})}
			</Flex>
		</Fragment>
	);
};

export default InstallPage;
