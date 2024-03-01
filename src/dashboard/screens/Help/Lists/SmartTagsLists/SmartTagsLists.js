import React from "react";
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
import { ArrowLeftFill } from "../../../../components/Icon/Icon";

const SmartTagsLists = ({ setIsListViewerOpen }) => {
	const SmartTagsList = [
		{
			id: __("Smart Tags to parse some data before registration"),
			description: __(
				"The below listed smart tags can be used to parse some user datas that donot required user to be registered in the email content.",
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
					description: __("Current Date.", "user-registration"),
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
					description: __("Referral URL.", "user-registration"),
				},
				{
					id: "{{form_id}}",
					description: __("Current form id.", "user-registration"),
				},
				{
					id: "{{form_name}}",
					description: __("Current form name.", "user-registration"),
				},
				{
					id: "{{author_name}}",
					description: __(
						"Current Page or Posts author name.",
						"user-registration"
					),
				},
				{
					id: "{{author_email}}",
					description: __(
						"Current Page or Posts author email.",
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
				"The below listed smart tags can be used to parse registered users submitted field value or some other user datas in the email content.",
				"user-registration"
			),
			smartTag: [
				{
					id: "{{user_id}}",
					description: __(
						"Id of the registered user.",
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
						"First Name of the registered user.",
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
						"Display the name of the registered user.",
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
						"All form fields filled by the user.",
						"user-registration"
					),
				},
				{
					id: "{{user_roles}}",
					description: __(
						"Gives the user’s role.",
						"user-registration"
					),
				},
				{
					id: "{{auto_pass}}",
					description: __(
						"Auto generated password for the user. Works only if Auto generated password feature is enabled in User Registration Pro",
						"user-registration"
					),
				},
			],
		},
	];

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
					leftIcon={<ArrowLeftFill w="6" h="6" />}
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
			<Accordion defaultIndex={[0]} allowToggle>
				{SmartTagsList.map((smartTags) => (
					<AccordionItem key={smartTags.id} p="16px">
						<AccordionButton
							justifyContent="space-between"
							_expanded={{ bg: "#F8F8FE" }}
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
							<AccordionIcon />
						</AccordionButton>
						<AccordionPanel pb={4} bgColor="#FBF8FE">
							<Text fontSize="14px">{smartTags.description}</Text>
							{smartTags.smartTag && (
								<Table variant="simple" fontSize="14px">
									<Tbody>
										{smartTags.smartTag.map(
											({ id, description }, key) => (
												<Tr key={key}>
													<Td px="0px">
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
													<Td>{description}</Td>
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
