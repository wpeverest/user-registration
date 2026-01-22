/**
 *  External Dependencies
 */
import { CopyIcon } from "@chakra-ui/icons";
import {
	Accordion,
	AccordionButton,
	AccordionItem,
	AccordionPanel,
	Box,
	Button,
	HStack,
	IconButton,
	Stack,
	Table,
	Tbody,
	Td,
	Text,
	Thead,
	Tr,
	useClipboard,
	useToast
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useEffect, useState } from "react";

/**
 *  Internal Dependencies
 */
import { Add, ArrowLeftFill, Minus } from "../../../../components/Icon/Icon";

const ShortcodesLists = ({ setIsListViewerOpen }) => {
	const ShortcodeList = [
		{
			id: "[user_registration_form]",
			description: __(
				"Displays registration form in the front end.",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"ID of the form to display.",
						"user-registration"
					),
					required: true
				}
			],
			example: [
				{
					example_name: '[user_registration_form id="5"]',
					example_description: __(
						"Displays Registration form with id 5",
						"user-registration"
					)
				}
			]
		},
		{
			id: "[user_registration_login]",
			description: __(
				"Displays login form in the front end.",
				"user-registration"
			),
			params: [
				{
					param_name: "redirect_url",
					param_description: __(
						"Slug or Link of the page to redirect users to after login.",
						"user-registration"
					)
				}
			],
			example: [
				{
					example_name:
						'[user_registration_login redirect_url="sample-page"]',
					example_description: __(
						"Redirects to the page with the slug sample-page, i.e., Sample Page after login.",
						"user-registration"
					)
				},
				{
					example_name:
						'[user_registration_login redirect_url="https://wpuserregistration.com"]',
					example_description: __(
						"Redirects to User Registration & Membership website after login.",
						"user-registration"
					)
				}
			]
		},
		{
			id: "[user_registration_my_account]",
			description: __(
				"Displays My Account page in the front end when the user is logged in and the login page when the user is logged out.",
				"user-registration"
			),
			params: [
				{
					param_name: "form_id",
					param_description: __(
						"Displays registration form of provided ID along with login form.",
						"user-registration"
					)
				},
				{
					param_name: "redirect_url",
					param_description: __(
						"Slug or Link of the page to redirect users to after login.",
						"user-registration"
					)
				},
				{
					param_name: "logout_redirect",
					param_description: __(
						"Slug or Link of the page to redirect users to after logout.",
						"user-registration"
					)
				}
			],
			example: [
				{
					example_name: '[user_registration_my_account form_id="5"]',
					example_description: __(
						"Renders login form and registration form with form ID 5 on the same page.",
						"user-registration"
					)
				},
				{
					example_name:
						'[user_registration_my_account redirect_url="sample-page" logout_redirect="hello-world"]',
					example_description: __(
						"Redirects to page with the slug sample-page, i.e., Sample Page after login. Redirects to page with the slug hello-world after user logout.",
						"user-registration"
					)
				},
				{
					example_name:
						'[user_registration_my_account redirect_url="https://wpuserregistration.com" logout_redirect="https://everestforms.net"]',
					example_description: __(
						"Redirects to the User Registration & Membership website on login and redirects to the Everest Forms website after user logout.",
						"user-registration"
					)
				}
			]
		},
		{
			id: "[user_registration_edit_profile]",
			description: __(
				"Displays the Edit Profile form in the front end.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_edit_password]",
			description: __(
				"Displays the Password Change form in the front end.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_view_profile_details]",
			description: __(
				"Displays profile details of the logged-in user in the front end.",
				"user-registration"
			),
			requires: __(
				"Requires User Registration & Membership Pro to be activated.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_popup]",
			description: __(
				"Displays the registration form as a popup in the front end.",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"ID of the form to display as a popup.",
						"user-registration"
					),
					required: true
				},
				{
					param_name: "type",
					param_description: __(
						"Type of the popup to be displayed. (It will be rendered by default on page load. But, if you set it to ‘button,’ a button that opens the popup when clicked will be displayed.)",
						"user-registration"
					)
				}
			],
			example: [
				{
					example_name: '[user_registration_popup id="1"]',
					example_description: __(
						"Displays User Registration & Membership popup with id 1",
						"user-registration"
					)
				},
				{
					example_name: '[user_registration_popup type="button"]',
					example_description: __(
						"Displays a button on the area where this shortcode is used which will display the popup when clicked.",
						"user-registration"
					)
				}
			],
			requires: __(
				"Requires User Registration & Membership Pro to be activated.",
				"user-registration"
			)
		},
		{
			id: "[urcr_restrict]...[/urcr_restrict]",
			description: __(
				"Restricts the text inside the shortcode.",
				"user-registration"
			),
			params: [
				{
					param_name: "access_role",
					param_description: __(
						"Allow access to the mentioned user role only.",
						"user-registration"
					)
				}
			],
			example: [
				{
					example_name:
						'[urcr_restrict access_role="Administrator"] Content to restrict [/urcr_restrict]',
					example_description: __(
						"Restricts the text inside the shortcode for all users except Administrators.",
						"user-registration"
					)
				}
			],
			requires: __(
				"Requires User Registration & Membership Pro to be activated.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_frontend_listing]",
			description: __(
				"Displays member directories in the front end.",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"Frontend Listing ID to render.",
						"user-registration"
					),
					required: true
				}
			],
			example: [
				{
					example_name: '[user_registration_frontend_listing id="1"]',
					example_description: __(
						"Displays user listing with ID 1 in the front end.",
						"user-registration"
					)
				}
			],
			requires: __(
				"Requires User Registration & Membership Pro to be activated.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_member_directory]",
			description: __(
				"Displays member directories in the front end.",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"Frontend Listing ID to render.",
						"user-registration"
					),
					required: true
				}
			],
			example: [
				{
					example_name: '[user_registration_member_directory id="1"]',
					example_description: __(
						"Displays user listing with ID 1 in the front end.",
						"user-registration"
					)
				}
			],
			requires: __(
				"Requires User Registration & Membership Pro to be activated.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_download_pdf_button]",
			description: __(
				"Show a Download PDF button on your site. The PDF will have the user details for that specific user. This button will only be visible once the users are logged in.",
				"user-registration"
			),
			requires: __(
				"Requires User Registration & Membership Pro and PDF Form Submission add-on to be activated.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_groups]",
			description: __(
				"Shows a list of available membership options for users to browse.",
				"user-registration"
			),
			params: [
				{
					param_name: "id",
					param_description: __(
						"ID of the membership group to display.",
						"user-registration"
					),
					required: false
				},
				{
					param_name: "button_text",
					param_description: __(
						"Override the signup button text.",
						"user-registration"
					),
					required: false
				}
			],
			example: [
				{
					example_name:
						'[user_registration_groups id="5" button_text="Join Now"]',
					example_description: __(
						"Displays Membership listing attached to group with id 5",
						"user-registration"
					)
				}
			]
		},
		{
			id: "[user_registration_membership_thank_you]",
			description: __(
				"Displays a thank you message to users after successfully registering for a membership.",
				"user-registration"
			)
		},
		{
			id: "[user_registration_lost_password]",
			description: __(
				"Displays a section for recovering a lost password.",
				"user-registration"
			)
		}
	];
	const [isAccordionOpen, setIsAccordionOpen] = useState({});
	const [isShortcodeCopied, setShortcodeCopied] = useState({});

	const { onCopy, hasCopied } = useClipboard();
	const [isExampleShortcodeCopied, setIsExampleShortcodeCopied] =
		useState("");

	const toast = useToast();

	useEffect(() => {
		const accordionOpener = { ...isAccordionOpen };
		ShortcodeList.map((shortcode) => {
			accordionOpener[shortcode.id] = false;
		});
		setIsAccordionOpen(accordionOpener);
	}, []);

	useEffect(() => {
		const shortcodeAccordion = isShortcodeCopied;
		ShortcodeList.map((shortcode) => {
			shortcodeAccordion[shortcode.id] = false;
		});
		setShortcodeCopied(shortcodeAccordion);
	}, [isShortcodeCopied]);

	const handleAccordionToggle = (shortcode_id) => {
		setIsAccordionOpen({
			...isAccordionOpen,
			[shortcode_id]: !isAccordionOpen[shortcode_id]
		});
	};

	const handleCopyClick = (shortcode_id, event) => {
		try {
			const textField = document.createElement("textarea");
			textField.innerText = shortcode_id;
			document.body.appendChild(textField);
			textField.select();
			document.execCommand("copy");
			textField.remove();

			setShortcodeCopied({
				...isShortcodeCopied,
				[shortcode_id]: !isShortcodeCopied[shortcode_id]
			});
			toast({
				description: `${shortcode_id} copied to clipboard`,
				status: "success",
				position: "bottom-right",
				duration: 1500
			});
			event.stopPropagation();
		} catch (error) {
			console.error("Error copying shortcode:", error);
		}
	};

	const handleExampleShortcodeCopy = (example_name) => {
		try {
			const textField = document.createElement("textarea");
			textField.innerText = example_name;
			document.body.appendChild(textField);
			textField.select();
			document.execCommand("copy");
			textField.remove();
			setIsExampleShortcodeCopied(example_name);
			event.stopPropagation();
			setTimeout(() => {
				setIsExampleShortcodeCopied("");
			}, 1000);
			toast({
				description: `${example_name} copied to clipboard`,
				position: "bottom-right",
				status: "success",
				duration: 1500
			});
		} catch (error) {
			console.error("Error copying shortcode:", error);
		}
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
					boxShadow="none !important"
				>
					{__("All Shortcodes", "user-registration")}
				</Button>
			</Stack>
			<Accordion allowMultiple>
				{ShortcodeList.map((shortcode) => (
					<AccordionItem key={shortcode.id} p="16px">
						<AccordionButton
							justifyContent="space-between"
							_expanded={{ bg: "#F8F8FE" }}
							onClick={() => {
								handleAccordionToggle(shortcode.id);
							}}
							boxShadow="none !important"
						>
							<Box
								flex="1"
								textAlign="left"
								bgColor="#EDEFF7"
								color="#475bb2"
								maxWidth="fit-content"
								p="4px 8px"
								fontWeight="600"
								fontSize="14px"
							>
								{shortcode.id}
							</Box>
							<Box textAlign="right">
								<HStack>
									<IconButton
										size="md"
										icon={<CopyIcon />}
										onClick={(event) =>
											handleCopyClick(shortcode.id, event)
										}
									/>
									{isAccordionOpen[shortcode.id] ? (
										<Minus h="5" w="5" />
									) : (
										<Add h="5" w="5" />
									)}
								</HStack>
							</Box>
						</AccordionButton>
						<AccordionPanel
							pb={4}
							bgColor="#F8F8FE"
							sx={{
								display: "flex",
								flexDirection: "column",
								gap: "20px"
							}}
						>
							<Text fontSize="14px">{shortcode.description}</Text>
							{shortcode.params && (
								<Table
									variant="simple"
									fontSize="14px"
									size="sm"
									sx={{
										display: "flex",
										flexDirection: "column",
										gap: "16px"
									}}
								>
									<Thead>
										<Tr border="none">
											<Td
												sx={{
													fontWeight: "600",
													paddingLeft: "0px",
													border: "none"
												}}
											>
												{__(
													"Parameters:",
													"user-registration"
												)}
											</Td>
										</Tr>
									</Thead>
									<Tbody
										sx={{
											display: "flex",
											flexDirection: "column",
											gap: "12px"
										}}
									>
										{shortcode.params.map(
											(
												{
													param_name,
													param_description,
													required
												},
												key
											) => (
												<Tr key={key}>
													<Td
														px="0px"
														borderBottom="0px"
														width="200px"
													>
														<Box
															flex="1"
															textAlign="left"
															bgColor="#EDEFF7"
															color="#475bb2"
															maxWidth="fit-content"
															p="4px 8px"
															fontWeight="600"
														>
															{param_name}
														</Box>
													</Td>
													<Td borderBottom="0px">
														<Text>
															{required && (
																<strong>
																	{__(
																		"REQUIRED.",
																		"user-registration"
																	)}
																</strong>
															)}{" "}
															{param_description}
														</Text>
													</Td>
												</Tr>
											)
										)}
									</Tbody>
								</Table>
							)}
							{shortcode.example && (
								<Table
									variant="simple"
									fontSize="14px"
									size="sm"
									sx={{
										display: "flex",
										flexDirection: "column",
										gap: "16px"
									}}
								>
									<Thead>
										<Tr>
											<Td
												sx={{
													fontWeight: "600",
													paddingLeft: "0px",
													border: "none"
												}}
											>
												{__(
													"Examples:",
													"user-registration"
												)}
											</Td>
										</Tr>
									</Thead>
									<Tbody
										sx={{
											display: "flex",
											flexDirection: "column",
											gap: "12px"
										}}
									>
										{shortcode.example.map(
											(
												{
													example_name,
													example_description
												},
												key
											) => (
												<>
													<Tr key={key}>
														<Td
															paddingLeft="0px"
															paddingTop="2"
															paddingBottom="2"
															borderBottom="0px"
														>
															<Box
																flex="1"
																textAlign="left"
																bgColor="#EDEFF7"
																color="#475bb2"
																maxWidth="fit-content"
																p="4px 8px"
																fontWeight="600"
															>
																{example_name}
															</Box>
														</Td>
														<Td>
															{example_name && (
																<Box>
																	<IconButton
																		size="md"
																		icon={
																			<CopyIcon />
																		}
																		onClick={(
																			event
																		) =>
																			handleExampleShortcodeCopy(
																				example_name,
																				event
																			)
																		}
																	/>
																</Box>
															)}
														</Td>
													</Tr>
													<Tr>
														<Td
															paddingLeft="0px"
															paddingTop="2"
															paddingBottom="2"
															borderBottom="0px"
														>
															{
																example_description
															}
														</Td>
													</Tr>
												</>
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
