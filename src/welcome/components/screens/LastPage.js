/**
 * External Dependencies
 */
import React, { useState, useEffect } from "react";
import {
	Flex,
	Image,
	Heading,
	Box,
	Text,
	Button,
	Link
} from "@chakra-ui/react";

import { __ } from "@wordpress/i18n";
import InputHandler from "../common/InputHandler";
import { useStateValue } from "../../../context/StateProvider";
import apiFetch from "@wordpress/api-fetch";
import {
	AdvancedFieldsLogo,
	CheckIcon,
	ContentRestrictionLogo,
	CustomizeMyAccountLogo,
	RightArrowIcon,
	StyleCustomizerLogo,
	WooCommerceLogo
} from "../Icon/Icon";

function LastPage({ onBoardIconsURL }) {
	/* global _UR_WIZARD_ */
	const { restURL, adminEmail, urRestApiNonce } =
		typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;
	const [allowTracking, setAllowTracking] = useState(false);
	const [hideElement, setHideElement] = useState({
		user_registration_updates_admin_email: true
	});
	const [{ allowUsageData }, dispatch] = useStateValue();

	const consentSettings = [
		{
			title: __(
				"Help us improve User Registration by sharing non-sensitive diagnostic and usage data. See what’s shared ",
				"user-registration"
			),
			id: "user_registration_allow_usage_tracking",
			type: "checkbox",
			default: "yes",
			link: "https://wpuserregistration.com/privacy-policy/",
			linkLabel: __("here.", "user-registration")
		},
		{
			title: __(
				"Get security updates, new features, and occasional offers by email. We’ll use your email: ",
				"user-registration"
			),
			id: "user_registration_allow_email_updates",
			type: "checkbox",
			default: "yes",
			link: "change",
			linkLabel: __("Change", "user-registration"),
			email: allowUsageData.user_registration_updates_admin_email + ". "
		},
		{
			id: "user_registration_updates_admin_email",
			type: "text",
			default: allowUsageData.user_registration_updates_admin_email
		}
	];

	const addonsDetails = [
		{
			title: __("Advanced Fields", "user-registration"),
			description: __(
				"Add custom fields like profile pictures, phone numbers, and more.",
				"user-registration"
			),
			icon: <AdvancedFieldsLogo />
		},
		{
			title: __("Customize My Account", "user-registration"),
			description: __(
				"Personalize user account pages with custom tabs and layouts.",
				"user-registration"
			),
			icon: <CustomizeMyAccountLogo />
		},
		{
			title: __("WooCommerce", "user-registration"),
			description: __(
				"Add and sync billing/shipping fields with the checkout page.",
				"user-registration"
			),
			icon: <WooCommerceLogo />
		},
		{
			title: __("Content Restriction", "user-registration"),
			description: __(
				"Control content access by user role or login status.",
				"user-registration"
			),
			icon: <ContentRestrictionLogo />
		},
		{
			title: __("Style Customizer", "user-registration"),
			description: __(
				"Style registration and login forms to match your brand.",
				"user-registration"
			),
			icon: <StyleCustomizerLogo />
		}
	];

	const handleModification = (data) => {
		setHideElement((prev) => ({
			...prev,
			user_registration_updates_admin_email: data
		}));
	};

	useEffect(() => {}, [hideElement]);

	return (
		<Flex direction="column" justifyContent="space-between" gap={"24px"}>
			<Flex
				direction="column"
				justifyContent="space-between"
				gap={"16px"}
			>
				<Heading
					as="h2"
					fontSize="26px"
					fontWeight="700"
					color="#383838"
					lineHeight="38px"
				>
					{__("Success! You're all set!", "user-registration")}
				</Heading>
				<hr color="#E1E1E1" />
			</Flex>

			<Box
				w="100%"
				color="#2D3559"
				display="flex"
				flexDirection="column"
				gap="24px"
			>
				<Text
					fontSize="18px"
					lineHeight="28px"
					fontWeight="600"
					color="#383838"
				>
					{__(
						"Help us serve you better – review these preferences before you start.",
						"user-registration"
					)}
				</Text>
				<Flex justify="space-between" wrap="wrap" gap="20px">
					{consentSettings.map((setting, key) => (
						<InputHandler
							setting={setting}
							customStyle={{
								flexDirection: "row-reverse",
								gap: "8px",
								alignItems: "top",

								"> div, label": {
									flex: "auto",
									alignItems: "baseline"
								},

								"> div": {
									label: {
										fontSize: "16px",
										lineHeight: "26px",
										fontWeight: "400"
									}
								},

								"> .chakra-input__group": {
									marginTop: "4px",

									input: {
										borderColor: "#BDBDBD"
									}
								},

								"> label": {
									marginTop: "5px",

									"> .chakra-switch__track": {
										width: "1.6rem",
										height: "0.8rem",

										"> .chakra-switch__thumb": {
											width: "0.8rem",
											height: "0.8rem"
										}
									}
								}
							}}
							onModify={(value) => handleModification(value)}
							hideElement={hideElement}
						/>
					))}
				</Flex>
			</Box>
			<hr color="#E1E1E1" />
			<Box
				w="100%"
				color="#2D3559"
				gap="30px"
				display="flex"
				flexDirection="column"
			>
				<Text
					fontSize="16px"
					fontWeight="600"
					color="#383838"
					textAlign="left"
				>
					<Link
						href="https://wpuserregistration.com/features/"
						isExternal
						display="flex"
						alignItems={"center"}
						gap="5px"
					>
						{__(
							"Unlock more features with PRO",
							"user-registration"
						)}
						<RightArrowIcon />
					</Link>
				</Text>
				<Box
					display="grid"
					gridTemplateColumns="repeat(auto-fit, minmax( 220px, 1fr ))"
					gap="10px"
				>
					{addonsDetails.map((addon, key) => {
						return (
							<Flex
								padding="16px"
								justifyContent="space-between"
								gap="10px"
								borderRadius="8px"
								_hover={{
									boxShadow:
										"0px 6px 20px rgba(71, 91, 178, 0.08)"
								}}
								border="1px solid #E1E1E1"
								background="#FFFFFF"
								flexDirection="column"
								flex="0 0 32%"
							>
								{addon.icon}
								<Flex direction="column">
									<Text
										fontSize="16px"
										fontWeight="500"
										mt="6px"
										mb="6px"
										color="#383838"
										lineHeight="26px"
									>
										{addon.title}
									</Text>
									<Text
										fontSize="14px"
										fontWeight="400"
										noOfLines={3}
										lineHeight="24px"
									>
										{addon.description}
									</Text>
								</Flex>
							</Flex>
						);
					})}
					<Link
						href="https://wpuserregistration.com/features/"
						isExternal
						flex="0 0 32%"
						display="flex"
					>
						<Flex
							padding="20px"
							gap="10px"
							borderRadius="8px"
							_hover={{
								boxShadow:
									"0px 6px 20px rgba(71, 91, 178, 0.08)"
							}}
							border="1px solid #4CA772"
							background="#FFFFFF"
							flexDirection="column"
							alignItems="center"
							textAlign="center"
						>
							<CheckIcon />
							<Flex direction="column">
								<Text
									fontSize="18px"
									fontWeight="600"
									mt="6px"
									mb="6px"
									color="#383838"
									lineHeight="26px"
								>
									{__(
										"30+ addons & tons of features",
										"user-registration"
									)}
								</Text>
							</Flex>
						</Flex>
					</Link>
				</Box>
			</Box>
		</Flex>
	);
}

export default LastPage;
