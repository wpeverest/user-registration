/**
 * External Dependencies
 */
import React from "react";
import {
	Flex,
	Image,
	Heading,
	Box,
	Text,
	Button,
	Link,
} from "@chakra-ui/react";

import { __ } from "@wordpress/i18n";

function LastPage({ onBoardIconsURL }) {
	return (
		<Flex
			direction="column"
			justifyContent="space-between"
			alignItems="center"
		>
			<Image
				htmlWidth="65px"
				htmlHeight="65px"
				src={`${onBoardIconsURL}/green-tick-icon.png`}
				alt="Onboard Finish"
			/>
			<Heading
				as="h2"
				fontSize="22px"
				fontWeight="600"
				mb={4}
				mt={4}
				color="#222222"
			>
				{__("Congratulations, You’re all set! 🎉", "user-registration")}
			</Heading>
			<Box
				w="100%"
				p={4}
				color="#2D3559"
				mt={3}
				borderTop="1px solid #DEE0E9"
			>
				<Text
					fontSize="18px"
					fontWeight="600"
					mb={4}
					mt={4}
					color="#383838"
					textAlign="center"
				>
					{__("Our Popular Addons", "user-registration")}
				</Text>
				<Flex justify="space-between" wrap="wrap" gap="10px">
					<Flex
						flex="0 0 49%"
						padding="10px"
						justifyContent="space-between"
						gap="10px"
						width="358px"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
						}}
						bg="#FAFAFA"
					>
						<Image
							htmlWidth="140px"
							htmlHeight="105px"
							src={`${onBoardIconsURL}/advanced-fields.png`}
							alt="Advanced Fields"
							borderRadius="5px"
						/>
						<Flex direction="column">
							<Text
								fontSize="15px"
								fontWeight="600"
								mt="6px"
								mb="6px"
								color="#383838"
							>
								{__("Advanced Fields", "user-registration")}
							</Text>
							<Text
								fontSize="12px"
								fontWeight="400"
								noOfLines={4}
							>
								{__(
									"Offers extra fields like Section Title, Phone, Profile Picture, HTML, Time Picker, and WYSIWYG for complex forms.",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
					<Flex
						flex="0 0 49%"
						padding="10px"
						justifyContent="space-between"
						gap="10px"
						width="358px"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
						}}
						bg="#FAFAFA"
					>
						<Image
							htmlWidth="140px"
							htmlHeight="105px"
							src={`${onBoardIconsURL}/woo-extension.png`}
							alt="WooCommerce"
							borderRadius="5px"
						/>
						<Flex direction="column">
							<Text
								fontSize="15px"
								fontWeight="600"
								mt="6px"
								mb="6px"
								color="#383838"
							>
								{__("WooCommerce", "user-registration")}
							</Text>
							<Text
								fontSize="12px"
								fontWeight="400"
								noOfLines={4}
							>
								{__(
									"Integrate WooCommerce plugin for user registration, enabling user account management of billing, shipping, and orders",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
					<Flex
						flex="0 0 49%"
						padding="10px"
						justifyContent="space-between"
						width="358px"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
						}}
						bg="#FAFAFA"
						gap="10px"
					>
						<Image
							htmlWidth="140px"
							htmlHeight="105px"
							src={`${onBoardIconsURL}/content-restriction.png`}
							alt="Content Restriction"
							borderRadius="5px"
							objectFit="cover"
						/>
						<Flex direction="column">
							<Text
								fontSize="15px"
								fontWeight="600"
								mt="6px"
								mb="6px"
								color="#383838"
							>
								{__("Content Restriction", "user-registration")}
							</Text>
							<Text
								fontSize="12px"
								fontWeight="400"
								noOfLines={4}
							>
								{__(
									"Easily restrict page or post content to logged-in users or specific user roles with this addon.",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
					<Flex
						flex="0 0 49%"
						padding="10px"
						justifyContent="space-between"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
						}}
						bg="#FAFAFA"
						width="358px"
						gap="10px"
					>
						<Image
							htmlWidth="140px"
							htmlHeight="105px"
							src={`${onBoardIconsURL}/customize-my-account.png`}
							alt="Customize My Account"
							borderRadius="5px"
						/>
						<Flex direction="column">
							<Text
								fontSize="15px"
								fontWeight="600"
								mt="6px"
								mb="6px"
								color="#383838"
							>
								{__(
									"Customize My Account",
									"user-registration"
								)}
							</Text>
							<Text
								fontSize="12px"
								fontWeight="400"
								noOfLines={4}
							>
								{__(
									"Easily create, edit, delete, and sort custom tabs on the account page with custom content",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
				</Flex>
				<center>
					<Link
						href="https://wpuserregistration.com/features/"
						isExternal
					>
						<Button
							variant="link"
							mr={10}
							ml={10}
							pt="6"
							fontStyle="italic"
							textDecoration="underline"
							fontSize="14px"
							fontWeight="600"
							gap="10px"
							className="button-tertiary"
							background="none !important"
							color="#475BB2 !important"
							border="none !important"
						>
							{__("View All Addons", "user-registration")}
							<svg
								width="10"
								height="11"
								viewBox="0 0 10 11"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
							>
								<path
									d="M3.75 0.5V1.33333H8.575L0 9.90833L0.591667 10.5L9.16667 1.925V6.75H10V0.5H3.75Z"
									fill="#475BB2"
								/>
							</svg>
						</Button>
					</Link>
				</center>
			</Box>
		</Flex>
	);
}

export default LastPage;
