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

const ShortcodesLists = ({ setIsListViewerOpen }) => {
	const ShortcodeList = [
		{
			id: "[user_registration_form]",
			description: __(
				"Displays registration form in the frontend",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"Form ID to render",
						"user-registration"
					),
				},
			],
		},
		{
			id: "[user_registration_login]",
			description: __(
				"Displays login form in the frontend",
				"user-registration"
			),
			params: [
				{
					param_name: "redirect_url",
					param_description: __(
						"Slug or Link to the page to redirect to after login.",
						"user-registration"
					),
				},
			],
		},
		{
			id: "[user_registration_my_account]",
			description: __(
				"Displays my account page in the frontend when user is logged in and login page when user is logged out",
				"user-registration"
			),
			params: [
				{
					param_name: "form_id",
					param_description: __(
						"Displays registration form of provided id along with login form",
						"user-registration"
					),
				},
				{
					param_name: "redirect_url",
					param_description: __(
						"Slug or Link to the page to redirect to after login.",
						"user-registration"
					),
				},
				{
					param_name: "logout_redirect",
					param_description: __(
						"Slug or Link to the page to redirect to after logout.",
						"user-registration"
					),
				},
			],
		},
		{
			id: "[user_registration_edit_profile]",
			description: __(
				"Displays edit profile form in the frontend",
				"user-registration"
			),
		},
		{
			id: "[user_registration_edit_password]",
			description: __(
				"Displays password change form in the frontend",
				"user-registration"
			),
		},
		{
			id: "[user_registration_view_profile_details]",
			description: __(
				"Displays profile details of the logged in user in the frontend",
				"user-registration"
			),
			requires: __(
				"Requires User Registration Pro to be activated.",
				"user-registration"
			),
		},
		{
			id: "[user_registration_popup]",
			description: __(
				"Displays profile details of the logged in user in the frontend",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"Popup id to render on frontend.",
						"user-registration"
					),
				},
				{
					param_name: "type",
					param_description: __(
						"Type of the popup to be displayed. It will be rendered by default on page load but if you set it to button then a button will be displayed and popup will open if the button is clicked.",
						"user-registration"
					),
				},
			],
			requires: __(
				"Requires User Registration Pro to be activated.",
				"user-registration"
			),
		},
		{
			id: "[urcr_restrict]...[/urcr_restrict]",
			description: __(
				"Restrict the texts under the shortcode",
				"user-registration"
			),
			params: [
				{
					param_name: "access_role",
					param_description: __(
						"Allow access to the only mentioned user role",
						"user-registration"
					),
				},
			],
			requires: __(
				"Requires User Registration Pro to be activated.",
				"user-registration"
			),
		},
		{
			id: "[user_registration_frontend_listing]",
			description: __(
				"Display member directories in frontend",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"Frontend Listing id to render.",
						"user-registration"
					),
				},
			],
			requires: __(
				"Requires User Registration Pro to be activated.",
				"user-registration"
			),
		},
		{
			id: "[user_registration_download_pdf_button]",
			description: __(
				"Show a Download PDF button on your site. This PDF will have the user details for that specific user. This button will only be visible once the users are logged in.",
				"user-registration"
			),
			requires: __(
				"Requires User Registration Pro and User Registration PDF Form Submission addon to be activated.",
				"user-registration"
			),
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
					{__("All Shortcodes", "user-registration")}
				</Button>
			</Stack>
			<Accordion defaultIndex={[0]} allowToggle>
				{ShortcodeList.map((shortcode) => (
					<AccordionItem key={shortcode.id} p="16px">
						<AccordionButton
							justifyContent="space-between"
							_expanded={{ bg: "#F8F8FE" }}
						>
							<Box
								flex="1"
								textAlign="left"
								bgColor="#EDEFF7"
								color="#2563EB"
								maxWidth="fit-content"
								p="4px 8px"
								fontWeight="600"
								fontSize="14px"
							>
								{shortcode.id}
							</Box>
							<AccordionIcon />
						</AccordionButton>
						<AccordionPanel pb={4} bgColor="#F8F8FE">
							<Text fontSize="14px">{shortcode.description}</Text>
							{shortcode.params && (
								<Table variant="simple" fontSize="14px">
									<Tbody>
										{shortcode.params.map(
											(
												{
													param_name,
													param_description,
												},
												key
											) => (
												<Tr key={key}>
													<Td px="0px">
														<Box
															flex="1"
															textAlign="left"
															bgColor="#EDEFF7"
															color="#2563EB"
															maxWidth="fit-content"
															p="4px 8px"
															fontWeight="600"
														>
															{param_name}
														</Box>
													</Td>
													<Td>{param_description}</Td>
												</Tr>
											)
										)}
									</Tbody>
								</Table>
							)}
							{shortcode.requires && (
								<Text
									fontSize="14px"
									color="red"
									fontWeight="500"
									marginTop="10px"
								>
									{shortcode.requires}
								</Text>
							)}
						</AccordionPanel>
					</AccordionItem>
				))}
			</Accordion>
		</Stack>
	);
};

export default ShortcodesLists;
