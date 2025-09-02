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
	Link,
	Stack,
	Text
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React from "react";

/**
 *  Internal Dependencies
 */
import * as Icon from "../../components/Icon/Icon";
import UsefulPlugins from "./components/UsefulPlugins";

const Dashboard = () => {
	/* global _UR_DASHBOARD_ */
	const { newFormURL, allFormsURL, utmCampaign } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	const helpURL =
			"https://docs.wpuserregistration.com/?utm_source=dashboard-dashboard&utm_medium=sidebar-link&utm_campaign=" +
			utmCampaign,
		featureRequestURL =
			"https://wpuserregistration.com/feature-requests/?utm_source=dashboard-dashboard&utm_medium=sidebar-link&utm_campaign=" +
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
				<Box
					p="6"
					borderRadius="base"
					border="1px"
					borderColor="gray.100"
					bgColor="white"
				>
					<Heading
						as="h3"
						mb="5"
						fontSize="2xl"
						fontWeight="semibold"
					>
						{__(
							"Welcome to User Registration & Membership!",
							"user-registration"
						)}
					</Heading>
					<AspectRatio ratio={16 / 9} mt={5}>
						<iframe
							src="https://www.youtube.com/embed/ZOXVbfBTNPQ?si=82Q2iOOE2iUF7M02&autoplay=1&mute=1&rel=0"
							title="YouTube video player"
							style={{
								borderRadius: "11px",
								border: "none",
								overflow: "hidden"
							}}
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
							allowFullScreen
						></iframe>
					</AspectRatio>
					<ButtonGroup mt="5" spacing="6" gap="12px">
						<Button
							as={Link}
							backgroundColor="#475bb2"
							fontSize="14px"
							fontWeight="normal"
							borderRadius="base"
							color="white !important"
							textDecor="none !important"
							py="3"
							px="6"
							href={newFormURL}
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
							textDecor="none !important"
							isExternal
						>
							{__("View all forms", "user-registration")}
						</Button>
					</ButtonGroup>
				</Box>
				{/* <Box
					bgColor="white"
					border="1px"
					borderColor="gray.100"
					borderRadius="base"
					p="4"
				>
					<Heading as="h3" mb="4" fontSize="lg" fontWeight="semibold">
						{__("Try More Plugins", "user-registration")}
					</Heading>
					<UsefulPlugins />
				</Box> */}
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
						<Icon.Team w="5" h="5" fill="#475bb2" />
						<Heading
							as="h3"
							size="sm"
							fontWeight="semibold"
							flex="1"
						>
							{__(
								"User Registration & Membership Community",
								"user-registration"
							)}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Join our exclusive group and connect with fellow members. Ask questions, contribute to discussions, and share feedback!",
							"user-registration"
						)}
					</Text>
					<Link
						color="#475bb2 !important"
						textDecor="underline"
						href="https://www.facebook.com/groups/userregistration"
						isExternal
					>
						{__("Join our Facebook Group", "user-registration")}
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
						<Icon.DocsLines w="5" h="5" fill="#475bb2" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Getting Started", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Check our documentation for detailed information on User Registration features and how to use them.",
							"user-registration"
						)}
					</Text>
					<Link
						color="#475bb2 !important"
						textDecor="underline"
						href={helpURL}
						isExternal
					>
						{__("View Documentation", "user-registration")}
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
						<Icon.Headphones w="5" h="5" fill="#475bb2" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Support", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Submit a ticket for encountered issues and get help from our support team instantly.",
							"user-registration"
						)}
					</Text>
					<Link
						color="#475bb2 !important"
						textDecor="underline"
						href="https://wordpress.org/support/plugin/user-registration/#new-topic-0"
						isExternal
					>
						{__("Create a Ticket", "user-registration")}
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
						color="#475bb2 !important"
						textDecor="underline"
						href={featureRequestURL}
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
						color="#475bb2 !important"
						textDecor="underline"
						href="https://wordpress.org/support/plugin/user-registration/reviews/?rate=5#new-post"
						isExternal
					>
						{__("Submit a Review", "user-registration")}
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
						<Icon.Video w="5" h="5" fill="#475bb2" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Video Tutorials", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Watch our step-by-step video tutorials that’ll help you get the best out of User Registration’s features.",
							"user-registration"
						)}
					</Text>
					<Link
						color="#475bb2 !important"
						textDecor="underline"
						isExternal
						href="https://www.youtube.com/@UserRegistration"
					>
						{__("Watch Videos", "user-registration")}
					</Link>
				</Stack>
			</Stack>
		</Grid>
	);
};

export default Dashboard;
