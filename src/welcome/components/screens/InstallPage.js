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
	Link,
	Heading,
	FormLabel,
	Tooltip
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

import { useStateValue } from "../../../context/StateProvider";

const InstallPage = () => {
	/* global _UR_WIZARD_ */
	const { defaultFormId, registrationPageSlug, myAccountPageSlug } =
		typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;
	const [{ installedPages }, dispatch] = useStateValue();
	const [installedPageDetails, setInstalledPageDetails] = useState({});
	const [registrationBehaviourDetails, setRegistrationBehaviourDetails] =
		useState({});

	useEffect(() => {
		let newInstalledPagesRef = { ...installedPages };
		let newRegistrationBehaviourDetailsRef = {
			...registrationBehaviourDetails
		};

		if (newInstalledPagesRef.default_form_id) {
			newRegistrationBehaviourDetailsRef["default_form_id"] =
				newInstalledPagesRef.default_form_id;
			delete newInstalledPagesRef.default_form_id;
		}

		if (newInstalledPagesRef.anyone_can_register) {
			newRegistrationBehaviourDetailsRef["anyone_can_register"] =
				newInstalledPagesRef.anyone_can_register;
			delete newInstalledPagesRef.anyone_can_register;
		}
		if (newInstalledPagesRef.default_wordpress_login) {
			newRegistrationBehaviourDetailsRef["default_wordpress_login"] =
				newInstalledPagesRef.default_wordpress_login;
			delete newInstalledPagesRef.default_wordpress_login;
		}

		if (newInstalledPagesRef.membership_details) {
			delete newInstalledPagesRef.membership_details;
		}
		setInstalledPageDetails(newInstalledPagesRef);
		setRegistrationBehaviourDetails(newRegistrationBehaviourDetailsRef);
	}, []);

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
		<Flex direction="column" justifyContent="space-between" gap={"40px"}>
			<Flex
				direction="column"
				justifyContent="space-between"
				alignItems="left"
				gap="20px"
			>
				<Heading
					as="h2"
					size="lg"
					fontSize="22px"
					mb={4}
					color="#383838"
					fontWeight="600"
				>
					{__("Registration Behaviour", "user-registration")}
				</Heading>
				<Flex gap="20px" flexDirection="column">
					{Object.keys(registrationBehaviourDetails).map((key) => {
						return (
							<Flex flexDirection="row">
								<Flex align="center" flex="0 0 50%">
									<FormLabel
										sx={{
											fontWeight: "500",
											fontSize: "15px",
											marginInlineEnd: "0.5rem"
										}}
									>
										{
											registrationBehaviourDetails[key]
												.title
										}
									</FormLabel>
									{registrationBehaviourDetails[key].desc && (
										<Tooltip
											label={
												registrationBehaviourDetails[
													key
												].desc
											}
											hasArrow
											fontSize="14px"
											fontWeight="400px"
											backgroundColor="#383838"
										>
											<span
												className="ur-setup-wizard-tool-tip"
												style={{
													color: "#BABABA",
													marginBottom: "5px"
												}}
											/>
										</Tooltip>
									)}
								</Flex>
								<FormLabel
									sx={{
										fontWeight: "500",
										fontSize: "15px",
										marginInlineEnd: "0.5rem"
									}}
									color={
										registrationBehaviourDetails[key]
											.status === "enabled"
											? "green"
											: "red"
									}
								>
									{
										registrationBehaviourDetails[key]
											.status_label
									}
								</FormLabel>
							</Flex>
						);
					})}
				</Flex>
			</Flex>
			<hr />
			<Flex
				direction="column"
				justifyContent="space-between"
				alignItems="left"
			>
				<Heading
					as="h2"
					size="lg"
					fontSize="22px"
					mb={4}
					color="#383838"
					fontWeight="600"
				>
					{__("Pages Installed", "user-registration")}
				</Heading>
				<Flex gap="20px" flexDirection="column">
					{Object.keys(installedPageDetails).map((key) => {
						return (
							<CreateInstallPageBox
								key={key}
								pageDetails={installedPageDetails[key]}
							/>
						);
					})}
				</Flex>
			</Flex>
		</Flex>
	);
};

export default InstallPage;
