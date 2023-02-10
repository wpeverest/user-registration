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
				<Flex justify="space-between" wrap="wrap" gap="18px">
					<Flex
						flex="0 0 48%"
						padding="10px 14px 10px 10px"
						justifyContent="space-between"
						gap="20px"
						width="358px"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
							cursor: "pointer",
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
								fontSize="13px"
								fontWeight="400"
								noOfLines={3}
							>
								{__(
									"Advanced Fields provides you with additional advanced fields like Section Title, Custom HTML, Phone, Time Picker and WYSIWYG Fields.",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
					<Flex
						flex="0 0 48%"
						padding="10px 14px 10px 10px"
						justifyContent="space-between"
						gap="20px"
						width="358px"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
							borderRadius: "7px",
							cursor: "pointer",
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
								fontSize="13px"
								fontWeight="400"
								noOfLines={3}
							>
								{__(
									"Integrates WooCommerce plugin with your user-registration and allows you to manage user billing, shipping, orders via user registration account page",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
					<Flex
						flex="0 0 48%"
						padding="10px 14px 10px 10px"
						justifyContent="space-between"
						width="358px"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
							cursor: "pointer",
						}}
						bg="#FAFAFA"
						gap="20px"
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
								fontSize="13px"
								fontWeight="400"
								noOfLines={3}
							>
								{__(
									"Content Restriction addon allows you to restrict full or partial content from page, post to only logged in users or logged in users with specific roles.",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
					<Flex
						flex="0 0 48%"
						padding="10px 14px 10px 10px"
						justifyContent="space-between"
						height="125px"
						borderRadius="7px"
						_hover={{
							boxShadow: "0px 6px 20px rgba(71, 91, 178, 0.08)",
							cursor: "pointer",
						}}
						bg="#FAFAFA"
						width="358px"
						gap="20px"
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
								fontSize="13px"
								fontWeight="400"
								noOfLines={3}
							>
								{__(
									"User Registration Customize My Account add-on allows you to create, edit and delete additional	tabs on the account page along with custom content for that tab. Also supports drag and	drop sorting of account tabs",
									"user-registration"
								)}
							</Text>
						</Flex>
					</Flex>
				</Flex>
				<center>
					<Link
						href="https://wpeverest.com/wordpress-plugins/user-registration/addons/"
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
