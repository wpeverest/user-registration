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
	Text,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React from "react";
import * as Icon from "../../components/Icon/Icon";
import UsefulPlugins from "./components/UsefulPlugins";

const Dashboard = () => {
	/* global _UR_ */
	const { newFormURL } = typeof _UR_ !== "undefined" && _UR_;
	return (
		<Grid
			my="8"
			mx="6"
			gridGap="5"
			gridTemplateColumns={{
				sm: "1fr",
				md: "3fr 1fr",
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
							"Welcome to User Registration",
							"user-registration"
						)}
					</Heading>
					<AspectRatio ratio={16 / 9}>
						<iframe
							src="https://www.youtube.com/embed/ZOXVbfBTNPQ?si=82Q2iOOE2iUF7M02&autoplay=1&mute=1"
							title="YouTube video player"
							style={{
								borderRadius: "11px",
								border: "none",
								overflow: "hidden",
							}}
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
							allowFullScreen
						></iframe>
					</AspectRatio>
					<ButtonGroup mt="5" spacing="6">
						<Button
							as={Link}
							colorScheme="primary"
							fontSize="14px"
							fontWeight="normal"
							borderRadius="base"
							color="white !important"
							textDecor="none !important"
							py="3"
							px="6"
							href={newFormURL}
						>
							{__("Create a Form", "user-registration")}
						</Button>
						<Button
							as={Link}
							colorScheme="primary"
							fontSize="14px"
							fontWeight="normal"
							borderRadius="base"
							variant="link"
							textDecor="underline"
							color="var(--chakra-colors-primary-500) !important"
							href="https://wpuserregistration.com/"
							isExternal
						>
							{__("Visit Our Website", "user-registration")}
						</Button>
					</ButtonGroup>
				</Box>
				<Box
					bgColor="white"
					border="1px"
					borderColor="gray.100"
					borderRadius="base"
					p="4"
				>
					<Heading as="h3" mb="4" fontSize="lg" fontWeight="semibold">
						{__("Useful Plugins", "user-registration")}
					</Heading>
					<UsefulPlugins />
				</Box>
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
						<Icon.DocsLines w="5" h="5" fill="primary.500" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Getting Started", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Please check out basic documentation for detailed information on how to use User Registration.",
							"user-registration"
						)}
					</Text>
					<Link
						color="var(--chakra-colors-primary-500) !important"
						textDecor="underline"
						href="https://docs.wpuserregistration.com/"
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
						<Icon.Bulb w="5" h="5" fill="primary.500" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Feature Request", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Please take a moment to suggest any features that could enhance our product.",
							"user-registration"
						)}
					</Text>
					<Link
						color="var(--chakra-colors-primary-500) !important"
						textDecor="underline"
						href="https://user-registration.feedbear.com/"
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
						<Icon.Star w="5" h="5" fill="primary.500" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Submit us a Review", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Sharing your review is a valuable way to help us enhance your experience.",
							"user-registration"
						)}
					</Text>
					<Link
						color="var(--chakra-colors-primary-500) !important"
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
						<Icon.Video w="5" h="5" fill="primary.500" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Video Tutorials", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Have a look at video tutorials to walk you through getting started.",
							"user-registration"
						)}
					</Text>
					<Link
						color="var(--chakra-colors-primary-500) !important"
						textDecor="underline"
						isExternal
						href="https://www.youtube.com/watch?v=_aQoIIbcwmM"
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
						<Icon.Headphones w="5" h="5" fill="primary.500" />
						<Heading as="h3" size="sm" fontWeight="semibold">
							{__("Support", "user-registration")}
						</Heading>
					</HStack>
					<Text fontSize="13px" color="gray.700">
						{__(
							"Get in touch with our support team. You can always submit a support ticket for help.",
							"user-registration"
						)}
					</Text>
					<Link
						color="var(--chakra-colors-primary-500) !important"
						textDecor="underline"
						href="https://wordpress.org/support/plugin/user-registration/#new-topic-0"
						isExternal
					>
						{__("Create a Ticket", "user-registration")}
					</Link>
				</Stack>
			</Stack>
		</Grid>
	);
};

export default Dashboard;
