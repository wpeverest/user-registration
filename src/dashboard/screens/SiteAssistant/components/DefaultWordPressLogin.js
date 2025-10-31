import {
	Button,
	Collapse,
	HStack,
	Heading,
	Icon,
	IconButton,
	Stack,
	Text,
	Box,
	Flex,
	Switch,
	FormControl,
	FormLabel,
	Link,
	useToast,
	VStack
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState } from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";
import { FaBookmark } from "react-icons/fa";

const DefaultWordPressLogin = ({ isOpen, onToggle, onHandled, numbering }) => {
	const [isDisabled, setIsDisabled] = useState(false);
	const [isSaving, setIsSaving] = useState(false);
	const [isSkipping, setIsSkipping] = useState(false);
	const toast = useToast();

	const handleSave = async () => {
		// Only allow saving if the switch is ON (disabled = true)
		if (!isDisabled) {
			toast({
				title: __("Action Required", "user-registration"),
				description: __(
					"Please turn on the switch to disable WordPress login before saving.",
					"user-registration"
				),
				status: "warning",
				duration: 3000,
				isClosable: true
			});
			return;
		}

		setIsSaving(true);

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
					action: "user_registration_handle_default_wordpress_login",
					action_type: "disable",
					security: window._UR_DASHBOARD_?.urRestApiNonce || ""
				})
			});

			const result = await response.json();

			if (result.success) {
				toast({
					title: __("Settings Saved", "user-registration"),
					description:
						result.data?.message ||
						__(
							"WordPress login has been disabled successfully.",
							"user-registration"
						),
					status: "success",
					duration: 3000,
					isClosable: true
				});
				// Notify parent component to hide this section
				if (onHandled) {
					onHandled();
				}
			} else {
				throw new Error(
					result.data?.message || "Failed to save settings"
				);
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to save settings. Please try again.",
						"user-registration"
					),
				status: "error",
				duration: 3000,
				isClosable: true
			});
		} finally {
			setIsSaving(false);
		}
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
					section: "default_wordpress_login",
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
							"WordPress login settings have been acknowledged and skipped.",
							"user-registration"
						),
					status: "success",
					duration: 3000,
					isClosable: true
				});
				// Notify parent component to hide this section
				if (onHandled) {
					onHandled();
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
						__(
							"Default WordPress Login Enabled",
							"user-registration"
						)}
				</Heading>
				<IconButton
					aria-label={"defaultWordPressLogin"}
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
					<Box
						bg="orange.50"
						p="4"
						borderRadius="md"
						border="1px"
						borderColor="orange.200"
					>
						<Flex align="center" gap={2}>
							<Icon
								as={FaBookmark}
								w="4"
								h="4"
								color="orange.500"
							/>
							<Text
								fontWeight="bold"
								fontSize="15px !important"
								color="orange.800"
							>
								{__("Recommended", "user-registration")}
							</Text>
						</Flex>
						<Text
							fontSize="15px !important"
							color="orange.700"
							mt={2}
						>
							{__(
								"WordPress default login is still active. This can lead to spam registrations on your site. Disable it to keep your site safe.",
								"user-registration"
							)}
						</Text>
					</Box>

					<FormControl bg="#f9fafc" p="4" borderRadius="md">
						<Flex justify="space-between" align="center">
							<Box>
								<FormLabel
									fontSize={"15px !important"}
									fontWeight="medium"
									mb={1}
								>
									{__(
										"Disable Default WordPress Login Screen",
										"user-registration"
									)}
								</FormLabel>
								<Text color="gray.600">
									{__(
										"Prevents access to wp-login.php",
										"user-registration"
									)}
								</Text>
							</Box>
							<Switch
								isChecked={isDisabled}
								onChange={(e) =>
									setIsDisabled(e.target.checked)
								}
								colorScheme="primary"
							/>
						</Flex>
					</FormControl>

					<HStack
						justifyContent="space-between"
						alignItems={"flex-end"}
					>
						<Button
							colorScheme={"primary"}
							rounded="base"
							fontSize="14px"
							onClick={handleSave}
							isLoading={isSaving}
							loadingText={__("Saving...", "user-registration")}
							isDisabled={!isDisabled || isSaving || isSkipping}
							opacity={!isDisabled ? 0.6 : 1}
						>
							{__("Save", "user-registration")}
						</Button>
						<Link
							color="gray.500"
							fontSize="14px"
							textDecoration="underline"
							onClick={handleSkip}
							cursor="pointer"
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

export default DefaultWordPressLogin;
