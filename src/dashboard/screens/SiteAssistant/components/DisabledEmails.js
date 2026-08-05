import {
	Box,
	Collapse,
	Flex,
	FormControl,
	FormLabel,
	HStack,
	Heading,
	Icon,
	IconButton,
	Link,
	Stack,
	Switch,
	Text,
	useToast
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useState } from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";

const DisabledEmails = ({ isOpen, onToggle, onHandled, numbering }) => {
	const [isEnabling, setIsEnabling] = useState(false);
	const toast = useToast();

	const handleEmailSettings = () => {
		const settingsURL =
			window._UR_DASHBOARD_?.settingsURL ||
			`${window.location.origin}/wp-admin/admin.php?page=user-registration-settings`;
		window.open(`${settingsURL}&tab=email`, "_blank");
	};

	const handleEnableEmails = async (checked) => {
		if (!checked) {
			return;
		}

		setIsEnabling(true);

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
					action: "user_registration_enable_emails",
					security: window._UR_DASHBOARD_?.urRestApiNonce || ""
				})
			});

			const result = await response.json();

			if (!result.success) {
				throw new Error(
					result.data?.message ||
						__("Failed to enable emails.", "user-registration")
				);
			}

			toast({
				title: __("Emails Enabled", "user-registration"),
				description:
					result.data?.message ||
					__(
						"Emails have been enabled successfully.",
						"user-registration"
					),
				status: "success",
				duration: 3000,
				isClosable: true
			});

			if (onHandled) {
				onHandled();
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to enable emails. Please try again.",
						"user-registration"
					),
				status: "error",
				duration: 3000,
				isClosable: true
			});
		} finally {
			setIsEnabling(false);
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
					{numbering + ") " + __("Emails Disabled", "user-registration")}
				</Heading>
				<IconButton
					aria-label={"disabledEmails"}
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
							"The Disable emails setting is turned on, so no emails are sent to your users or admins. Registration confirmation, admin approval and membership emails are all affected.",
							"user-registration"
						)}
					</Text>
					<FormControl bg="#f9fafc" p="4" borderRadius="md">
						<Flex justify="space-between" align="center">
							<Box>
								<FormLabel
									fontSize={"15px !important"}
									fontWeight="medium"
									mb={1}
								>
									{__("Enable Emails", "user-registration")}
								</FormLabel>
								<Text color="gray.600">
									{isEnabling
										? __("Enabling...", "user-registration")
										: __(
												"Turns off the Disable emails setting",
												"user-registration"
											)}
								</Text>
							</Box>
							<Switch
								isChecked={false}
								isDisabled={isEnabling}
								onChange={(e) =>
									handleEnableEmails(e.target.checked)
								}
								colorScheme="primary"
							/>
						</Flex>
					</FormControl>
					<Text color="gray.600" fontSize="14px">
						{__(
							"You can also manage this from ",
							"user-registration"
						)}
						<Link
							color="primary.500"
							textDecoration="underline"
							onClick={handleEmailSettings}
							cursor="pointer"
						>
							{__("Email Settings", "user-registration")}
						</Link>
						.
					</Text>
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default DisabledEmails;
