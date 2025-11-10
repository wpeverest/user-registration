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
	VStack,
	Flex,
	Link,
	useToast,
	Image,
	Badge
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState } from "react";
import { BiChevronDown, BiChevronUp } from "react-icons/bi";

const PaymentSetup = ({ isOpen, onToggle, onSkipped, numbering }) => {
	const [isSkipping, setIsSkipping] = useState(false);
	const toast = useToast();

	// Get payment connections from backend
	const paymentConnections =
		window._UR_DASHBOARD_?.site_assistant_data?.payment_connections || {};

	// Check if any payment method is connected
	const hasConnectedPayment = Object.values(paymentConnections).some(
		(connection) => connection.is_connected
	);

	// Check if all payment methods are connected
	const allPaymentsConnected = Object.values(paymentConnections).every(
		(connection) => connection.is_connected
	);

	// Check if any payment gateways are available/active
	const hasPaymentGateways = Object.keys(paymentConnections).length > 0;

	// Check if payment setup is explicitly skipped
	const isPaymentSetupSkipped =
		window._UR_DASHBOARD_?.site_assistant_data?.payment_setup_handled ===
			true && !hasConnectedPayment;

	const handleSettingsClick = (settingsUrl) => {
		window.open(settingsUrl, "_blank");
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
					section: "payment_setup",
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
							"Payment setup has been acknowledged and skipped.",
							"user-registration"
						),
					status: "success",
					duration: 3000,
					isClosable: true
				});
				if (onSkipped) {
					onSkipped();
				}
			} else {
				throw new Error(
					result.data?.message || "Failed to skip payment setup"
				);
			}
		} catch (error) {
			toast({
				title: __("Error", "user-registration"),
				description:
					error.message ||
					__(
						"Failed to skip payment setup. Please try again.",
						"user-registration"
					),
				status: "error",
				duration: 5000,
				isClosable: true
			});
		} finally {
			setIsSkipping(false);
		}
	};

	const handleOtherPaymentFeatures = () => {
		const settingsURL =
			window._UR_DASHBOARD_?.settingsURL ||
			`${window.location.origin}/wp-admin/admin.php?page=user-registration-settings`;
		window.open(`${settingsURL}&tab=payment`, "_blank");
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
						__("Payment Setup", "user-registration")}
				</Heading>
				<IconButton
					aria-label={"paymentSetup"}
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
							"Configure payment methods to start selling your memberships. You can set up multiple payment options for your members.",
							"user-registration"
						)}
					</Text>
					<VStack spacing={3} align="stretch">
						{Object.entries(paymentConnections).map(
							([key, connection]) => {
								const imageMap = {
									stripe: "stripe.png",
									paypal: "paypal.png",
									bank: "bank.png"
								};
								const imageFile =
									imageMap[key] || "payment.png";
								const assetsURL =
									window._UR_DASHBOARD_?.assetsURL || "";

								return (
									<Box
										key={key}
										p="4"
										borderRadius="md"
										border="1px"
										borderColor="gray.200"
										_hover={{ borderColor: "gray.300" }}
										transition="border-color 0.2s"
									>
										<Flex
											justify="space-between"
											align="center"
										>
											<HStack spacing={3}>
												<Image
													src={`${assetsURL}images/settings-icons/${imageFile}`}
													alt={connection.name}
													w="24px"
													h="24px"
													objectFit="contain"
												/>
												<HStack
													spacing={2}
													align="center"
												>
													<Text
														fontWeight="medium"
														fontSize="16px !important"
													>
														{connection.name}
													</Text>
													{connection.is_connected && (
														<Badge
															colorScheme="green"
															variant="subtle"
															px={2}
															py={0.5}
															borderRadius="md"
															fontSize="14px"
															fontWeight="normal"
															color="green.600"
															bg="green.50"
														>
															{__(
																"Connected",
																"user-registration"
															)}
														</Badge>
													)}
												</HStack>
											</HStack>
											<Link
												color="primary.500"
												fontSize="14px"
												textDecoration="underline"
												onClick={() =>
													handleSettingsClick(
														connection.settings_url
													)
												}
												cursor="pointer"
												_hover={{
													color: "primary.600"
												}}
											>
												{__(
													"Settings",
													"user-registration"
												)}
											</Link>
										</Flex>
									</Box>
								);
							}
						)}
					</VStack>

					{/* Show skip button if payment gateways are available and some (but not all) payment methods are connected */}
					{hasPaymentGateways &&
						hasConnectedPayment &&
						!allPaymentsConnected && (
							<HStack
								justifyContent="space-between"
								alignItems={"flex-end"}
							>
								<Text fontSize={"14px"} color="gray.600">
									{__(
										"Additional payment setup can be configured from ",
										"user-registration"
									)}
									<Link
										color="primary.500"
										textDecoration="underline"
										onClick={handleOtherPaymentFeatures}
										cursor="pointer"
									>
										{__(
											"Payment > Settings",
											"user-registration"
										)}
									</Link>
									.
								</Text>
								<Link
									color="gray.500"
									fontSize="14px"
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
						)}
				</Stack>
			</Collapse>
		</Stack>
	);
};

export default PaymentSetup;
