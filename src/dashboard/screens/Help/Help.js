/**
 *  External Dependencies
 */
import {
	AspectRatio,
	Box,
	Button,
	ButtonGroup,
	Grid,
	Heading,
	HStack,
	Image,
	Link,
	Stack,
	Text
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState, useEffect } from "react";

/**
 *  Internal Dependencies
 */
import * as Icon from "../../components/Icon/Icon";
import facebook from "../../images/facebook.webp";
import x from "../../images/x.webp";
import youtube from "../../images/youtube.webp";
import ShortcodesLists from "./Lists/ShortcodesLists/ShortcodesLists";
import SmartTagsLists from "./Lists/SmartTagsLists/SmartTagsLists";

const Help = () => {
	/* global _UR_DASHBOARD_ */
	const { newFormURL, allFormsURL, utmCampaign } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	const [isListViewerOpen, setIsListViewerOpen] = useState(false);
	const [listViewerType, setListViewerType] = useState("");

	useEffect(() => {}, [isListViewerOpen]);

	const supportURL =
			"https://wpuserregistration.com/support/?utm_source=dashboard-help&utm_medium=help-button&utm_campaign=" +
			utmCampaign,
		helpURL =
			"https://docs.wpuserregistration.com/?utm_source=dashboard-help&utm_medium=help-button&utm_campaign=" +
			utmCampaign,
		featureRequestURL =
			"https://wpuserregistration.com/feature-requests/?utm_source=dashboard-help&utm_medium=sidebar-link&utm_campaign=" +
			utmCampaign;
	return (
		<Grid
			my="8"
			mx="6"
			gridGap="5"
			gridTemplateColumns={{
				sm: "1fr",
				md: "3fr 1fr"
			}}
		>
			<Stack gap="5">
				{isListViewerOpen ? (
					listViewerType === "shortcodes" ? (
						<ShortcodesLists
							setIsListViewerOpen={setIsListViewerOpen}
						/>
					) : (
						<SmartTagsLists
							setIsListViewerOpen={setIsListViewerOpen}
						/>
					)
				) : (
					<>
						{/* Welcome Section and Video Section - Side by Side */}
						<Grid
							gridTemplateColumns={{
								sm: "1fr",
								md: "1fr 1fr"
							}}
							gridGap="5"
							mb="5"
						>
							{/* Welcome Section - Left */}
							<Box
								p="6"
								borderRadius="base"
								border="1px"
								borderColor="gray.100"
								bgColor="white"
								display="flex"
								flexDirection="column"
								justifyContent="space-between"
								minH="300px"
							>
								<Box display="flex" flexDirection="column" gap="6">
									<Heading
										as="h3"
										fontSize="2xl"
										fontWeight="semibold"
										color="gray.800"
									>
										{__(
											"Welcome to User Registration & Membership!",
											"user-registration"
										)}
									</Heading>
									<Text fontSize="md" color="gray.600" lineHeight="1.5">
										{__(
											"Create powerful registration forms and manage your members with our comprehensive toolkit.",
											"user-registration"
										)}
									</Text>
								</Box>
								<HStack spacing="4" gap="12px" mt="auto" pt="6">
									<Button
										as={Link}
										backgroundColor="#475bb2"
										fontSize="14px"
										fontWeight="normal"
										borderRadius="base"
										color="white"
										textDecor="none"
										py="3"
										px="6"
										href={newFormURL}
										_hover={{
											backgroundColor: "#3a4a9a",
											color: "white",
											textDecor: "none"
										}}
									>
										{__(
											"Create a Registration Form",
											"user-registration"
										)}
									</Button>
									<Button
										as={Link}
										variant="outline"
										borderColor="#475bb2"
										color="#475bb2"
										borderRadius="base"
										fontSize="14px"
										fontWeight="normal"
										href={allFormsURL}
										textDecor="none"
										isExternal
										_hover={{
											backgroundColor: "#475bb2",
											color: "white",
											borderColor: "#475bb2",
											textDecor: "none"
										}}
									>
										{__("View all forms", "user-registration")}
									</Button>
								</HStack>
							</Box>

							{/* Video Section - Right */}
							<Box
								p="6"
								borderRadius="base"
								border="1px"
								borderColor="gray.100"
								bgColor="white"
							>
								<AspectRatio ratio={16 / 9}>
									<iframe
										src="https://www.youtube.com/embed/ZOXVbfBTNPQ?si=82Q2iOOE2iUF7M02&autoplay=1&mute=1&rel=0"
										title="Best WordPress User Registration & Membership"
										style={{
											borderRadius: "11px",
											border: "none",
											overflow: "hidden"
										}}
										allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
										allowFullScreen
									></iframe>
								</AspectRatio>
							</Box>
						</Grid>

						{/* Shortcodes and Smart Tags Section */}
						<Grid
							gridTemplateColumns={{
								sm: "1fr",
								md: "1fr 1fr"
							}}
							gridGap="5"
						>
						<Stack
							px="6"
							py="8"
							align="center"
							gap="3"
							bgColor="white"
							borderRadius="base"
							border="1px"
							borderColor="gray.100"
							textAlign="center"
						>
							<Icon.Shortcode w="8" h="8" fill="#475bb2" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Shortcodes", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"Find the complete list of shortcodes with their usage information and parameter details.",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								variant="outline"
								color="#475bb2"
								borderColor="#475bb2"
								borderRadius="base"
								fontSize="14px"
								fontWeight="normal"
								onClick={() => {
									setIsListViewerOpen(true);
									setListViewerType("shortcodes");
								}}
							>
								{__("View all Shortcodes", "user-registration")}
							</Button>
						</Stack>
						<Stack
							px="6"
							py="8"
							align="center"
							gap="3"
							bgColor="white"
							borderRadius="base"
							border="1px"
							borderColor="gray.100"
							textAlign="center"
						>
							<Icon.SmartTag w="8" h="8" fill="#475bb2" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Smart Tags", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"Find the complete list of smart tags with their usage information and parameter details.",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								variant="outline"
								color="#475bb2"
								borderColor="#475bb2"
								borderRadius="base"
								fontSize="14px"
								fontWeight="normal"
								onClick={() => {
									setIsListViewerOpen(true);
									setListViewerType("smartTags");
								}}
							>
								{__("View Tags", "user-registration")}
							</Button>
						</Stack>
						<Stack
							px="6"
							py="8"
							align="center"
							gap="3"
							bgColor="white"
							borderRadius="base"
							border="1px"
							borderColor="gray.100"
							textAlign="center"
						>
							<Icon.Support w="8" h="8" fill="#475bb2" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Support", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"If you have any issues or questions, our team is on standby to help you instantly.",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								as={Link}
								variant="outline"
								color="#475bb2"
								borderColor="#475bb2"
								borderRadius="base"
								fontSize="14px"
								fontWeight="normal"
								href={supportURL}
								isExternal
								textDecor="none !important"
							>
								{__("Contact Support", "user-registration")}
							</Button>
						</Stack>
						<Stack
							px="6"
							py="8"
							align="center"
							gap="3"
							bgColor="white"
							borderRadius="base"
							border="1px"
							borderColor="gray.100"
							textAlign="center"
						>
							<Icon.DocsLines w="8" h="8" fill="#475bb2" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Need Some Help?", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"Check our documentation for detailed information on User Registration & Membership features and how to use them.",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								as={Link}
								color="#475bb2"
								borderColor="#475bb2"
								borderRadius="base"
								fontSize="14px"
								fontWeight="normal"
								textDecor="none !important"
								href={helpURL}
								isExternal
								variant="outline"
							>
								{__("View Now", "user-registration")}
							</Button>
						</Stack>
						</Grid>
					</>
				)}
				<Stack marginTop="25px">
					<Heading as="h3" fontSize="lg" fontWeight="semibold">
						{__("Join Our Community", "user-registration")}
					</Heading>
				</Stack>
				<Grid
					gridTemplateColumns="1fr 1fr"
					p="4"
					bgColor="white"
					border="1px"
					borderColor="gray.100"
					borderRadius="base"
					gridGap="7"
				>
					<Image src={facebook} w="full" />
					<Stack gap="2" justify="center">
						<Heading
							as="h3"
							fontSize="xl"
							fontWeight="normal"
							color="gray.700"
						>
							{__("Facebook Community", "user-registration")}
						</Heading>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Join our exclusive group and connect with fellow members. Ask questions, contribute to discussions, and share feedback!",
								"user-registration"
							)}
						</Text>
						<Button
							as={Link}
							colorScheme="primary"
							borderRadius="base"
							fontSize="14px"
							fontWeight="normal"
							alignSelf="start"
							mt="5"
							color="white !important"
							isExternal
							href="https://www.facebook.com/groups/userregistration"
							textDecor="none !important"
						>
							{__("Join Group", "user-registration")}
						</Button>
					</Stack>
				</Grid>
				<Grid
					gridTemplateColumns="1fr 1fr"
					p="4"
					bgColor="white"
					border="1px"
					borderColor="gray.100"
					borderRadius="base"
					gridGap="7"
				>
					<Image src={x} />
					<Stack gap="2" justify="center">
						<Heading
							as="h3"
							fontSize="xl"
							fontWeight="normal"
							color="gray.700"
						>
							{__("X ( Twitter )", "user-registration")}
						</Heading>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Follow us on X to get the latest news and updates about User Registration & Membership.",
								"user-registration"
							)}
						</Text>
						<Button
							as={Link}
							borderRadius="base"
							fontSize="14px"
							fontWeight="normal"
							alignSelf="start"
							mt="5"
							color="white !important"
							bgColor="black !important"
							isExternal
							href="https://twitter.com/user_register"
							textDecor="none !important"
						>
							{__("Follow", "user-registration")}
						</Button>
					</Stack>
				</Grid>
				<Grid
					gridTemplateColumns="1fr 1fr"
					p="4"
					bgColor="white"
					border="1px"
					borderColor="gray.100"
					borderRadius="base"
					gridGap="7"
				>
					<Image src={youtube} />
					<Stack gap="2" justify="center">
						<Heading
							as="h3"
							fontSize="xl"
							fontWeight="normal"
							color="gray.700"
						>
							{__("YouTube", "user-registration")}
						</Heading>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Subscribe to our YouTube channel, where we guide you on using User Registration & Membership features and add-ons.",
								"user-registration"
							)}
						</Text>
						<Button
							as={Link}
							colorScheme="red"
							borderRadius="base"
							fontSize="14px"
							fontWeight="normal"
							alignSelf="start"
							mt="5"
							color="white !important"
							isExternal
							href="https://www.youtube.com/@UserRegistration"
							textDecor="none !important"
						>
							{__("Subscribe", "user-registration")}
						</Button>
					</Stack>
				</Grid>
			</Stack>
			<Stack gap="5">
				<Stack
					p="4"
					gap="3"
					bgColor="white"
					borderRadius="base"
					border="1px"
					borderColor="gray.100"
				>
					<HStack gap="2">
						<Icon.Video w="5" h="5" fill="#475bb2" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Video Tutorials", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Watch our step-by-step video tutorials that’ll help you get the best out of User Registration & Membership features.",
							"user-registration"
						)}
					</Text>
					<Link
						isExternal
						color="#475bb2 !important"
						textDecor="underline"
						href="https://www.youtube.com/@UserRegistration"
					>
						{__("Watch Videos", "user-registration")}
					</Link>
				</Stack>
				<Stack
					p="4"
					gap="3"
					bgColor="white"
					borderRadius="base"
					border="1px"
					borderColor="gray.100"
				>
					<HStack gap="2">
						<Icon.Bulb w="5" h="5" fill="#475bb2" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Feature Request", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Don’t find a feature you’re looking for? Suggest any features you think would enhance our product.",
							"user-registration"
						)}
					</Text>
					<Link
						href={featureRequestURL}
						color="#475bb2 !important"
						textDecor="underline"
						isExternal
					>
						{__("Request a Feature", "user-registration")}
					</Link>
				</Stack>
				<Stack
					p="4"
					gap="3"
					bgColor="white"
					borderRadius="base"
					border="1px"
					borderColor="gray.100"
				>
					<HStack gap="2">
						<Icon.Star w="5" h="5" fill="#475bb2" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Submit a Review", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Please take a moment to give us a review. We appreciate honest feedback that’ll help us improve our plugin.",
							"user-registration"
						)}
					</Text>
					<Link
						href="https://wordpress.org/support/plugin/user-registration/reviews/?rate=5#new-post"
						color="#475bb2 !important"
						textDecor="underline"
						isExternal
					>
						{__("Submit a Review", "user-registration")}
					</Link>
				</Stack>
			</Stack>
		</Grid>
	);
};

export default Help;
