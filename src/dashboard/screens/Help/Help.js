import {
	Button,
	Grid,
	Heading,
	HStack,
	Image,
	Link,
	Stack,
	Text,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState, useEffect } from "react";
import * as Icon from "../../components/Icon/Icon";
import facebook from "../../images/facebook.webp";
import x from "../../images/x.webp";
import youtube from "../../images/youtube.webp";
import ShortcodesLists from "./Lists/ShortcodesLists/ShortcodesLists";
import SmartTagsLists from "./Lists/SmartTagsLists/SmartTagsLists";

const Help = () => {
	const [isListViewerOpen, setIsListViewerOpen] = useState(false);
	const [listViewerType, setListViewerType] = useState("");

	useEffect(() => {}, [isListViewerOpen]);

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
					<Grid
						gridTemplateColumns={{
							sm: "1fr",
							md: "1fr 1fr",
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
							<Icon.Chat w="8" h="8" fill="primary.500" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Shortcodes", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"You can find all the lists of shortcodes with their usage and details",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								variant="outline"
								colorScheme="primary"
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
							<Icon.Chat w="8" h="8" fill="primary.500" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Smart Tags", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"You can find all the lists of smart tags with their usage and details",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								variant="outline"
								colorScheme="primary"
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
							<Icon.Chat w="8" h="8" fill="primary.500" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Support", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"We would be happy to guide any of your issues and queries 24/7",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								as={Link}
								variant="outline"
								colorScheme="primary"
								borderRadius="base"
								fontSize="14px"
								fontWeight="normal"
								href="https://wpuserregistration.com/support"
								isExternal
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
							<Icon.DocsLines w="8" h="8" fill="primary.500" />
							<Heading as="h3" size="sm" fontWeight="semibold">
								{__("Need Some Help?", "user-registration")}
							</Heading>
							<Text fontSize="13px" color="gray.700">
								{__(
									"Please check out basic documentation for detailed information on how to use User Registration.",
									"user-registration"
								)}
							</Text>
							<Button
								mt="10"
								as={Link}
								colorScheme="primary"
								variant="link"
								borderRadius="base"
								fontSize="14px"
								fontWeight="normal"
								textDecor="underline"
								href="https://docs.wpuserregistration.com/"
								isExternal
							>
								{__("View Now", "user-registration")}
							</Button>
						</Stack>
					</Grid>
				)}
				<Stack>
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
								"Join our Facebook community, where the latest news and updates eagerly await your arrival.",
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
							{__("Twitter Community", "user-registration")}
						</Heading>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Join our Twitter community, where the latest news and updates eagerly await your arrival.",
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
					<Image src={youtube} />
					<Stack gap="2" justify="center">
						<Heading
							as="h3"
							fontSize="xl"
							fontWeight="normal"
							color="gray.700"
						>
							{__("YouTube Community", "user-registration")}
						</Heading>
						<Text fontSize="13px" color="gray.700">
							{__(
								"Subscribe to our YouTube channel, where the latest news and updates eagerly await your arrival.",
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
						isExternal
						color="var(--chakra-colors-primary-500) !important"
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
						href="https://user-registration.feedbear.com/"
						color="var(--chakra-colors-primary-500) !important"
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
						href="https://wordpress.org/support/plugin/user-registration/reviews/?rate=5#new-post"
						color="var(--chakra-colors-primary-500) !important"
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
