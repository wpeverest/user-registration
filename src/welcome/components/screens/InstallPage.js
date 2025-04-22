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
	Tooltip,
	Icon
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

	const ExternalLinkIcon = (props) => (
		<Icon
			viewBox="0 0 16 17"
			{...props}
			fill="none"
			height="17px"
			width="16px"
		>
			<path
				d="M14 9.16667V13.1667C14 13.5203 13.8595 13.8594 13.6095 14.1095C13.3594 14.3595 13.0203 14.5 12.6667 14.5H3.33333C2.97971 14.5 2.64057 14.3595 2.39052 14.1095C2.14048 13.8594 2 13.5203 2 13.1667V3.83333C2 3.47971 2.14048 3.14057 2.39052 2.89052C2.64057 2.64048 2.97971 2.5 3.33333 2.5H7.33333"
				stroke="#8C8C8C"
				strokeWidth="1.33333"
				strokeLinecap="round"
				strokeLinejoin="round"
			/>
			<path
				d="M14 2.5L8 8.5"
				stroke="#8C8C8C"
				strokeWidth="1.33333"
				strokeLinecap="round"
				strokeLinejoin="round"
			/>
			<path
				d="M10 2.5H14V6.5"
				stroke="#8C8C8C"
				strokeWidth="1.33333"
				strokeLinecap="round"
				strokeLinejoin="round"
			/>
		</Icon>
	);

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
				p="14px 16px"
				color="#383838"
				border="1px solid #EDEFF7"
				borderRadius="md"
				display="flex"
				flex="0 0 48%"
			>
				<Flex justify="space-between" align="center" width="100%">
					<Checkbox
						isChecked={true}
						isReadOnly
						className="user-registration-setup-wizard__body--checkbox"
						color="#475BB2"
					>
						<Text fontSize="15px" fontWeight={600} color="#383838">
							{pageDetails.title}
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
							<ExternalLinkIcon />
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
										marginInlineEnd: "0.5rem",
										padding: "8px 12px",
										display: "flex",
										gap: "10px",
										fontWeight: "500"
									}}
									color={
										registrationBehaviourDetails[key]
											.status === "enabled"
											? "#008000"
											: "#F25656"
									}
									bgColor={
										registrationBehaviourDetails[key]
											.status === "enabled"
											? "#F0F8F0"
											: "#FFF5F5"
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
				<Flex
					align="center"
					flexDirection="column"
					justifyContent="flex-start"
					alignItems="flex-start"
					gap="20px"
				>
					<FormLabel
						sx={{
							fontWeight: "500",
							fontSize: "15px",
							marginInlineEnd: "0.5rem"
						}}
					>
						{__("Installed Pages", "user-registration")}
					</FormLabel>
					<Flex
						align="center"
						flexDirection="row"
						gap="20px"
						flexWrap="wrap"
					>
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
		</Flex>
	);
};

export default InstallPage;
