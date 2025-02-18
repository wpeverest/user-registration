/**
 *  External Dependencies
 */
import React, { useState, useEffect } from "react";
import {
	Box,
	Accordion,
	AccordionItem,
	AccordionButton,
	AccordionIcon,
	AccordionPanel,
	Stack,
	Text,
	Button,
	Table,
	Tbody,
	Td,
	Tr,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

/**
 *  Internal Dependencies
 */
import { ArrowLeftFill, Add, Minus } from "../../../../components/Icon/Icon";

const SmartTagsLists = ({ setIsListViewerOpen }) => {
	const SmartTagsList = [
		{
			id: __("Smart Tags to parse some data before registration"),
			description: __(
				"The Smart Tags listed below can be used to parse user data that doesn’t require user to be registered in the email content.",
				"user-registration"
			),
			smartTag: [
				{
					id: "{{blog_info}}",
					description: __(
						"Retrieves information about the current site.",
						"user-registration"
					),
				},
				{
					id: "{{home_url}}",
					description: __(
						"Retrieves the URL for a given site where the front end is accessible.",
						"user-registration"
					),
				},
				{
					id: "{{admin_email}}",
					description: __("Site admin email.", "user-registration"),
				},
				{
					id: "{{site_name}}",
					description: __(
						"Name of the website.",
						"user-registration"
					),
				},
				{
					id: "{{site_url}}",
					description: __("URL of the website.", "user-registration"),
				},
				{
					id: "{{page_title}}",
					description: __("Current Page title.", "user-registration"),
				},
				{
					id: "{{page_url}}",
					description: __("Current Page URL.", "user-registration"),
				},
				{
					id: "{{page_id}}",
					description: __("Current Page ID.", "user-registration"),
				},
				{
					id: "{{post_title}}",
					description: __("Current Post title.", "user-registration"),
				},
				{
					id: "{{current_date}}",
					description: __("Current date.", "user-registration"),
				},
				{
					id: "{{current_time}}",
					description: __("Current time.", "user-registration"),
				},
				{
					id: "{{current_language}}",
					description: __(
						"Current language of the site.",
						"user-registration"
					),
				},
				{
					id: "{{email_token}}",
					description: __(
						"Token generated for an email confirmation.",
						"user-registration"
					),
				},
				{
					id: "{{user_ip_address}}",
					description: __(
						"Current user's ip address.",
						"user-registration"
					),
				},
				{
					id: "{{referrer_url}}",
					description: __(
						"URL of the referrer page from where users landed on the form.",
						"user-registration"
					),
				},
				{
					id: "{{form_id}}",
					description: __("Current form ID.", "user-registration"),
				},
				{
					id: "{{form_name}}",
					description: __("Current form name.", "user-registration"),
				},
				{
					id: "{{author_name}}",
					description: __(
						"Current page or post's author name.",
						"user-registration"
					),
				},
				{
					id: "{{author_email}}",
					description: __(
						"Current page or post's author email.",
						"user-registration"
					),
				},
				{
					id: "{{unique_id}}",
					description: __(
						"Generates a unique string that can be used as an ID.",
						"user-registration"
					),
				},
			],
		},
		{
			id: __("Smart Tags to parse registered users data"),
			description: __(
				"The Smart Tags listed below can be used to parse a registered user’s submitted field value and other data in the email content.",
				"user-registration"
			),
			smartTag: [
				{
					id: "{{user_id}}",
					description: __(
						"ID of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{user_login}}",
					description: __(
						"Username of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{user_pass}}",
					description: __(
						"Password of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{first_name}}",
					description: __(
						"First name of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{description}}",
					description: __(
						"Bio of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{nickname}}",
					description: __(
						"Nickname of the registered user",
						"user-registration"
					),
				},
				{
					id: "{{user_url}}",
					description: __(
						"Website/URL of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{display_name}}",
					description: __(
						"Display name of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{email}}",
					description: __(
						"Email of the registered user.",
						"user-registration"
					),
				},
				{
					id: "{{all_fields}}",
					description: __(
						"All form field values filled and submitted by the user.",
						"user-registration"
					),
				},
				{
					id: "{{user_roles}}",
					description: __(
						"The user’s assigned role.",
						"user-registration"
					),
				},
				{
					id: "{{auto_pass}}",
					description: __(
						"Auto generated password for the user. It works only if auto-generated password feature is enabled in User Registration & Membership Pro",
						"user-registration"
					),
				},
			],
		},
	];

	const [isAccordionOpen, setIsAccordionOpen] = useState({});

	useEffect(() => {
		const accordionOpener = { ...isAccordionOpen };
		SmartTagsList.map((smartTag) => {
			accordionOpener[smartTag.id] = false;
		});
		setIsAccordionOpen(accordionOpener);
	}, []);

	const handleAccordionToggle = (smarttag_id) => {
		setIsAccordionOpen({
			...isAccordionOpen,
			[smarttag_id]: !isAccordionOpen[smarttag_id],
		});
	};

	return (
		<Stack
			px="6"
			py="8"
			direction="column"
			bgColor="white"
			borderRadius="base"
			border="1px"
			borderColor="gray.100"
		>
			<Stack direction="row">
				<Button
					leftIcon={
						<ArrowLeftFill
							w="30"
							h="30"
							position="relative"
							top="2px"
						/>
					}
					variant="outline"
					border="none"
					size="md"
					fontSize="16px"
					fontWeight="600"
					onClick={() => setIsListViewerOpen(false)}
				>
					{__("All Smart Tags", "user-registration")}
				</Button>
			</Stack>
			<Accordion allowMultiple>
				{SmartTagsList.map((smartTags) => (
					<AccordionItem key={smartTags.id} p="16px">
						<AccordionButton
							justifyContent="space-between"
							_expanded={{ bg: "#F8F8FE" }}
							onClick={() => {
								handleAccordionToggle(smartTags.id);
							}}
							boxShadow="none !important"
						>
							<Box
								flex="1"
								textAlign="left"
								bgColor="#F8F2FF"
								color="#A975E8"
								maxWidth="fit-content"
								p="4px 8px"
								fontWeight="600"
								fontSize="14px"
							>
								{smartTags.id}
							</Box>
							{isAccordionOpen[smartTags.id] ? (
								<Minus h="5" w="5" />
							) : (
								<Add h="5" w="5" />
							)}
						</AccordionButton>
						<AccordionPanel
							pb={4}
							bgColor="#FBF8FE"
							sx={{
								display: "flex",
								flexDirection: "column",
								gap: "20px",
							}}
						>
							<Text fontSize="14px">{smartTags.description}</Text>
							{smartTags.smartTag && (
								<Table
									variant="simple"
									fontSize="14px"
									size="sm"
									sx={{
										display: "flex",
										flexDirection: "column",
										gap: "16px",
									}}
								>
									<Tbody
										sx={{
											display: "flex",
											flexDirection: "column",
											gap: "12px",
										}}
									>
										{smartTags.smartTag.map(
											({ id, description }, key) => (
												<Tr key={key}>
													<Td
														px="0px"
														borderBottom="0px"
														width="200px"
													>
														<Box
															flex="1"
															textAlign="left"
															bgColor="#F8F2FF"
															color="#A975E8"
															maxWidth="fit-content"
															p="4px 8px"
															fontWeight="600"
														>
															{id}
														</Box>
													</Td>
													<Td borderBottom="0px">
														{description}
													</Td>
												</Tr>
											)
										)}
									</Tbody>
								</Table>
							)}
						</AccordionPanel>
					</AccordionItem>
				))}
			</Accordion>
		</Stack>
	);
};

export default SmartTagsLists;
