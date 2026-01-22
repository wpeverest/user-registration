import {
	Box,
	Collapse,
	Flex,
	HStack,
	Heading,
	Icon,
	IconButton,
	Link,
	Stack,
	Text,
	useToast
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useState } from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";

const SpamProtection = ({ isOpen, onToggle, onSkipped, numbering }) => {
	const [isSkipping, setIsSkipping] = useState(false);
	const toast = useToast();

	const handleConfigureRecaptcha = () => {
		const settingsURL =
			window._UR_DASHBOARD_?.settingsURL ||
			`${window.location.origin}/wp-admin/admin.php?page=user-registration-settings`;
		window.open(
			`${settingsURL}&tab=registration_login&section=captcha&method=v2`,
			"_blank"
		);
	};

	const handleOtherSpamFeatures = () => {
		const settingsURL =
			window._UR_DASHBOARD_?.settingsURL ||
			`${window.location.origin}/wp-admin/admin.php?page=user-registration-settings`;
		window.open(
			`${settingsURL}&tab=registration_login&section=captcha`,
			"_blank"
		);
	};

	const handleSkip = async () => {
		setIsSkipping(true);

		try {
			const adminURL =
				window._UR_DASHBOARD_?.adminURL ||
				window.location.origin + "/wp-admin";
			const response = await fetch(`${adminURL}admin-ajax.php`, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams({
					action: "user_registration_skip_site_assistant_section",
					section: "spam_protection",
					security: window._UR_DASHBOARD_?.urRestApiNonce || ""
				})
			});

			const result = await response.json();

			if (result.success) {
				toast({
					title: __("Skipped", "user-registration"),
					description:
						result.data?.message ||
						__(
							"Spam protection setting has been acknowledged and skipped.",
							"user-registration"
						),
					status: "success",
					duration: 3000,
					isClosable: true
				});
				// Notify parent component to hide this section
				if (onSkipped) {
					onSkipped();
				}
			} else {
				throw new Error(
					result.data?.message || "Failed to skip settings"
				);
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to skip settings. Please try again.",
						"user-registration"
					),
				status: "error",
				duration: 3000,
				isClosable: true
			});
		} finally {
			setIsSkipping(false);
		}
	};

	return (
		<Stack
			p="6"
			gap="5"
			bgColor="white"
			borderRadius="base"
			border="1px"
			borderColor="gray.100"
		>
			<HStack
				justify={"space-between"}
				onClick={onToggle}
				borderBottom={isOpen && "1px solid #dcdcde"}
				paddingBottom={isOpen && 5}
				_hover={{
					cursor: "pointer"
				}}
			>
				<Heading
					as="h3"
					fontSize="18px"
					fontWeight="semibold"
					lineHeight={"1.2"}
				>
					{numbering +
						") " +
						__("Spam Protection", "user-registration")}
				</Heading>
				<IconButton
					aria-label={"spamProtection"}
					icon={
						<Icon
							as={isOpen ? BiChevronUp : BiChevronDown}
							fontSize="2xl"
							fill={isOpen ? "primary.500" : "black"}
						/>
					}
					cursor={"pointer"}
					fontSize={"xl"}
					size="sm"
					boxShadow="none"
					borderRadius="base"
					variant={isOpen ? "solid" : "link"}
					border="none"
				/>
			</HStack>
			<Collapse in={isOpen}>
				<Stack gap={5}>
					<Text fontWeight={"light"} fontSize={"15px !important"}>
						{__(
							"Set up protection against spam registrations. We recommend enabling reCaptcha v2.",
							"user-registration"
						)}
					</Text>

					<Flex
						bg="#f9fafc"
						p="4"
						borderRadius="md"
						justify="space-between"
						align="center"
					>
						<Box>
							<Text
								fontSize={"15px !important"}
								fontWeight="bold"
								mb={1}
								className="ur-recaptcha-bold"
								sx={{
									fontWeight: "bold",
									"&.ur-recaptcha-bold": {
										fontWeight: "bold"
									}
								}}
							>
								{__("reCaptcha v2", "user-registration")}
							</Text>
							<Text fontSize="14px" color="gray.600">
								{__(
									"Enable Google reCaptcha protection",
									"user-registration"
								)}
							</Text>
						</Box>
						<Link
							color="primary.500"
							textDecoration="underline"
							onClick={handleConfigureRecaptcha}
							cursor="pointer"
						>
							{__("Configure Settings", "user-registration")}
						</Link>
					</Flex>
					<HStack
						justifyContent="space-between"
						alignItems={"flex-end"}
					>
						<Text color="gray.600" fontSize="14px">
							{__(
								"You can also set up other spam protection features from ",
								"user-registration"
							)}
							<Link
								color="primary.500"
								textDecoration="underline"
								onClick={handleOtherSpamFeatures}
								cursor="pointer"
							>
								{__("here", "user-registration")}
							</Link>
							.
						</Text>

						<Link
							fontSize="14px"
							color="gray.500"
							textDecoration="underline"
							onClick={handleSkip}
							cursor="pointer"
							width="fit-content"
							opacity={isSkipping ? 0.6 : 1}
							pointerEvents={isSkipping ? "none" : "auto"}
						>
							{isSkipping
								? __("Skipping...", "user-registration")
								: __("Skip Setup", "user-registration")}
						</Link>
					</HStack>
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default SpamProtection;
