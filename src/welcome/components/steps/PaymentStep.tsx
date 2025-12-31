import {
	Box,
	Collapse,
	Flex,
	Heading,
	HStack,
	Icon,
	Input,
	Select,
	Skeleton,
	Switch,
	Text,
	Textarea,
	Tooltip,
	useColorModeValue,
	VStack
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useEffect, useState } from "react";
import {
	apiGet,
	CurrencyData,
	PaymentSettingsResponse
} from "../../api/gettingStartedApi";
import { PaymentSettings } from "../../context/Gettingstartedcontext";
import { useStateValue } from "../../context/StateProvider";

interface PaymentGatewayData {
	id: string;
	label: string;
	description: string;
	enabled: boolean;
	configured: boolean;
	settings_url: string;
	paypal_email?: string;
	paypal_client_id?: string;
	paypal_client_secret?: string;
	bank_details?: string;
	stripe_test_mode?: boolean;
	stripe_test_publishable_key?: string;
	stripe_test_secret_key?: string;
	stripe_live_publishable_key?: string;
	stripe_live_secret_key?: string;
}

const InfoIcon: React.FC = () => (
	<Icon viewBox="0 0 20 20" boxSize={4} color="gray.400">
		<path
			fill="currentColor"
			d="M10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0zm1 15H9v-2h2v2zm0-4H9V5h2v6z"
		/>
	</Icon>
);

interface PaymentOptionProps {
	label: string;
	isChecked: boolean;
	onChange: (checked: boolean) => void;
	children?: React.ReactNode;
}

const PaymentOption: React.FC<PaymentOptionProps> = ({
	label,
	isChecked,
	onChange,
	children
}) => {
	const textColor = useColorModeValue("gray.800", "white");
	const subtextColor = useColorModeValue("gray.600", "gray.300");

	return (
		<Box>
			<Flex align="center" py={4}>
				<Text
					fontWeight="500"
					color={textColor}
					fontSize="sm"
					minW="40%"
				>
					{label}
				</Text>
				<Switch
					isChecked={isChecked}
					onChange={(e) => onChange(e.target.checked)}
					colorScheme="blue"
					size="sm"
					sx={{
						"& .chakra-switch__track[data-checked]": {
							bg: "#475BB2"
						}
					}}
				/>
			</Flex>
			<Collapse in={isChecked && !!children} animateOpacity>
				<Box
					pl={4}
					pb={5}
					borderLeftWidth="2px"
					borderLeftColor="gray.300"
					ml={1}
					mb={2}
				>
					{children}
				</Box>
			</Collapse>
		</Box>
	);
};

const PaymentStep: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const { paymentSettings } = state;
	const [isLoadingSettings, setIsLoadingSettings] = useState(false);
	const [currencies, setCurrencies] = useState<CurrencyData[]>([]);

	const textColor = useColorModeValue("gray.800", "white");
	const subtextColor = useColorModeValue("gray.600", "gray.300");
	const mutedColor = useColorModeValue("#383838", "gray.300");
	const inputBg = useColorModeValue("white", "gray.700");
	const inputBorder = useColorModeValue("gray.300", "gray.600");

	useEffect(() => {
		const loadPaymentSettings = async () => {
			try {
				setIsLoadingSettings(true);
				const response =
					await apiGet<PaymentSettingsResponse>("/payments");

				if (response.currencies) {
					setCurrencies(response.currencies);
				}

				if (response.currency) {
					dispatch({
						type: "SET_PAYMENT_SETTING",
						payload: {
							key: "currency",
							value: response.currency
						}
					});
				}

				if (response.payment_gateways) {
					const gateways =
						response.payment_gateways as PaymentGatewayData[];

					gateways.forEach((gateway) => {
						if (gateway.id === "offline_payment") {
							dispatch({
								type: "SET_PAYMENT_SETTING",
								payload: {
									key: "offlinePayment",
									value: gateway.enabled
								}
							});
							if (gateway.bank_details) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "bankDetails",
										value: gateway.bank_details
									}
								});
							}
						} else if (gateway.id === "paypal") {
							dispatch({
								type: "SET_PAYMENT_SETTING",
								payload: {
									key: "paypal",
									value: gateway.enabled
								}
							});
							if (gateway.paypal_email) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "paypalEmail",
										value: gateway.paypal_email
									}
								});
							}
							if (gateway.paypal_client_id) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "paypalClientId",
										value: gateway.paypal_client_id
									}
								});
							}

							if (gateway.paypal_client_secret) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "paypalClientSecret",
										value: gateway.paypal_client_secret
									}
								});
							}
						} else if (gateway.id === "stripe") {
							dispatch({
								type: "SET_PAYMENT_SETTING",
								payload: {
									key: "stripe",
									value: gateway.enabled
								}
							});
							if (typeof gateway.stripe_test_mode === "boolean") {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "stripeTestMode",
										value: gateway.stripe_test_mode
									}
								});
							}
							if (gateway.stripe_test_publishable_key) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "stripeTestPublishableKey",
										value: gateway.stripe_test_publishable_key
									}
								});
							}
							if (gateway.stripe_test_secret_key) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "stripeTestSecretKey",
										value: gateway.stripe_test_secret_key
									}
								});
							}
							if (gateway.stripe_live_publishable_key) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "stripeLivePublishableKey",
										value: gateway.stripe_live_publishable_key
									}
								});
							}
							if (gateway.stripe_live_secret_key) {
								dispatch({
									type: "SET_PAYMENT_SETTING",
									payload: {
										key: "stripeLiveSecretKey",
										value: gateway.stripe_live_secret_key
									}
								});
							}
						}
					});
				}
			} catch (e) {
				console.error("Failed to load payment settings:", e);
			} finally {
				setIsLoadingSettings(false);
			}
		};

		loadPaymentSettings();
	}, [dispatch]);

	const handlePaymentSettingChange = (
		key: keyof PaymentSettings,
		value: boolean | string
	) => {
		dispatch({
			type: "SET_PAYMENT_SETTING",
			payload: { key, value }
		});
	};

	return (
		<>
			<Heading
				size="lg"
				fontFamily="Inter"
				fontWeight={600}
				fontSize="21px"
				lineHeight="34px"
				letterSpacing="-0.01em"
				color={textColor}
				mb={2}
			>
				{__("Payments", "user-registration")}
			</Heading>

			<Text fontSize="sm" color={subtextColor} mb={8}>
				{__(
					"Set up payment options for your membership plans. Choose currency and enable payment gateways . You can edit this anytime.",
					"user-registration"
				)}
			</Text>

			<Flex justify="space-between" align="center" mb={2}>
				<Text fontWeight="500" color={textColor} fontSize="sm">
					{__("Currency", "user-registration")}
				</Text>
				{isLoadingSettings ? (
					<Skeleton height="40px" width="60%" borderRadius="md" />
				) : (
					<Select
						value={paymentSettings.currency || ""}
						onChange={(e) =>
							handlePaymentSettingChange(
								"currency",
								e.target.value
							)
						}
						bg={inputBg}
						borderColor={inputBorder}
						fontSize="sm"
						w="60%"
						placeholder={__("Select currency", "user-registration")}
						_focus={{
							borderColor: "#475BB2",
							boxShadow: "0 0 0 1px #475BB2"
						}}
					>
						{currencies.map((currency) => (
							<option key={currency.code} value={currency.code}>
								{currency.name} ({currency.symbol})
							</option>
						))}
					</Select>
				)}
			</Flex>

			<VStack spacing={0} align="stretch">
				<PaymentOption
					label={__("Offline Payment", "user-registration")}
					isChecked={paymentSettings.offlinePayment}
					onChange={(checked) =>
						handlePaymentSettingChange("offlinePayment", checked)
					}
				>
					<Flex align="flex-start" mt={3}>
						<HStack spacing={1} minW="40%" flexShrink={0} pt={2}>
							<Text fontSize="sm" color={mutedColor}>
								{__("Bank Details", "user-registration")}
							</Text>
							<Tooltip
								label={__(
									"Enter your bank account details for offline payments",
									"user-registration"
								)}
								hasArrow
							>
								<span>
									<InfoIcon />
								</span>
							</Tooltip>
						</HStack>
						<Textarea
							placeholder={__(
								"Enter your bank account details here...",
								"user-registration"
							)}
							value={paymentSettings.bankDetails || ""}
							onChange={(e) =>
								handlePaymentSettingChange(
									"bankDetails",
									e.target.value
								)
							}
							bg={inputBg}
							borderColor={inputBorder}
							rows={3}
							fontSize="sm"
							flex="1"
							_focus={{
								borderColor: "#475BB2",
								boxShadow: "0 0 0 1px #475BB2"
							}}
						/>
					</Flex>
				</PaymentOption>

				<PaymentOption
					label={__("Paypal", "user-registration")}
					isChecked={paymentSettings.paypal}
					onChange={(checked) =>
						handlePaymentSettingChange("paypal", checked)
					}
				>
					<VStack spacing={4} align="stretch" mt={3}>
						<Flex align="center">
							<HStack spacing={1} minW="40%" flexShrink={0}>
								<Text fontSize="sm" color={mutedColor}>
									{__("PayPal Email", "user-registration")}
								</Text>
								<Tooltip
									label={__(
										"Enter the email address associated with your PayPal account",
										"user-registration"
									)}
									hasArrow
								>
									<span>
										<InfoIcon />
									</span>
								</Tooltip>
							</HStack>
							<Input
								type="email"
								placeholder={__(
									"your-email@example.com",
									"user-registration"
								)}
								value={paymentSettings.paypalEmail || ""}
								onChange={(e) =>
									handlePaymentSettingChange(
										"paypalEmail",
										e.target.value
									)
								}
								bg={inputBg}
								borderColor={inputBorder}
								fontSize="sm"
								flex="1"
								_focus={{
									borderColor: "#475BB2",
									boxShadow: "0 0 0 1px #475BB2"
								}}
							/>
						</Flex>

						<Flex align="center">
							<HStack spacing={1} minW="40%" flexShrink={0}>
								<Text fontSize="sm" color={mutedColor}>
									{__("Client ID", "user-registration")}
								</Text>
								<Tooltip
									label={__(
										"Your client_id, Required for subscription related operations.",
										"user-registration"
									)}
									hasArrow
								>
									<span>
										<InfoIcon />
									</span>
								</Tooltip>
							</HStack>
							<Input
								type="text"
								placeholder=""
								value={paymentSettings.paypalClientId || ""}
								onChange={(e) =>
									handlePaymentSettingChange(
										"paypalClientId",
										e.target.value
									)
								}
								bg={inputBg}
								borderColor={inputBorder}
								fontSize="sm"
								flex="1"
								_focus={{
									borderColor: "#475BB2",
									boxShadow: "0 0 0 1px #475BB2"
								}}
							/>
						</Flex>

						<Flex align="center">
							<HStack spacing={1} minW="40%" flexShrink={0}>
								<Text fontSize="sm" color={mutedColor}>
									{__("Client Secret", "user-registration")}
								</Text>
								<Tooltip
									label={__(
										"Your client_secret, Required for subscription related operations",
										"user-registration"
									)}
									hasArrow
								>
									<span>
										<InfoIcon />
									</span>
								</Tooltip>
							</HStack>
							<Input
								type="password"
								placeholder=""
								value={paymentSettings.paypalClientSecret || ""}
								onChange={(e) =>
									handlePaymentSettingChange(
										"paypalClientSecret",
										e.target.value
									)
								}
								bg={inputBg}
								borderColor={inputBorder}
								fontSize="sm"
								flex="1"
								_focus={{
									borderColor: "#475BB2",
									boxShadow: "0 0 0 1px #475BB2"
								}}
							/>
						</Flex>
					</VStack>
				</PaymentOption>

				<PaymentOption
					label={__("Stripe", "user-registration")}
					isChecked={paymentSettings.stripe}
					onChange={(checked) =>
						handlePaymentSettingChange("stripe", checked)
					}
				>
					<VStack spacing={4} align="stretch" mt={3}>
						<Flex align="center">
							<HStack spacing={1} minW="40%" flexShrink={0}>
								<Text fontSize="sm" color={mutedColor}>
									{__(
										"Enable Test Mode",
										"user-registration"
									)}
								</Text>
								<Tooltip
									label={__(
										"Enable test mode to use Stripe's test environment",
										"user-registration"
									)}
									hasArrow
								>
									<span>
										<InfoIcon />
									</span>
								</Tooltip>
							</HStack>
							<Switch
								isChecked={
									paymentSettings.stripeTestMode || false
								}
								onChange={(e) =>
									handlePaymentSettingChange(
										"stripeTestMode",
										e.target.checked
									)
								}
								colorScheme="blue"
								size="sm"
								sx={{
									"& .chakra-switch__track[data-checked]": {
										bg: "#475BB2"
									}
								}}
							/>
						</Flex>

						{paymentSettings.stripeTestMode ? (
							<>
								<Flex align="center">
									<HStack
										spacing={1}
										minW="40%"
										flexShrink={0}
									>
										<Text fontSize="sm" color={mutedColor}>
											{__(
												"Test Publishable Key",
												"user-registration"
											)}
										</Text>
										<Tooltip
											label={__(
												"Your Stripe test publishable key (starts with pk_test_)",
												"user-registration"
											)}
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</HStack>
									<Input
										type="text"
										placeholder="pk_test_..."
										value={
											paymentSettings.stripeTestPublishableKey ||
											""
										}
										onChange={(e) =>
											handlePaymentSettingChange(
												"stripeTestPublishableKey",
												e.target.value
											)
										}
										bg={inputBg}
										borderColor={inputBorder}
										fontSize="sm"
										flex="1"
										_focus={{
											borderColor: "#475BB2",
											boxShadow: "0 0 0 1px #475BB2"
										}}
									/>
								</Flex>

								<Flex align="center">
									<HStack
										spacing={1}
										minW="40%"
										flexShrink={0}
									>
										<Text fontSize="sm" color={mutedColor}>
											{__(
												"Test Secret Key",
												"user-registration"
											)}
										</Text>
										<Tooltip
											label={__(
												"Your Stripe test secret key (starts with sk_test_)",
												"user-registration"
											)}
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</HStack>
									<Input
										type="password"
										placeholder="sk_test_..."
										value={
											paymentSettings.stripeTestSecretKey ||
											""
										}
										onChange={(e) =>
											handlePaymentSettingChange(
												"stripeTestSecretKey",
												e.target.value
											)
										}
										bg={inputBg}
										borderColor={inputBorder}
										fontSize="sm"
										flex="1"
										_focus={{
											borderColor: "#475BB2",
											boxShadow: "0 0 0 1px #475BB2"
										}}
									/>
								</Flex>
							</>
						) : (
							<>
								<Flex align="center">
									<HStack
										spacing={1}
										minW="40%"
										flexShrink={0}
									>
										<Text fontSize="sm" color={mutedColor}>
											{__(
												"Live Publishable Key",
												"user-registration"
											)}
										</Text>
										<Tooltip
											label={__(
												"Your Stripe live publishable key (starts with pk_live_)",
												"user-registration"
											)}
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</HStack>
									<Input
										type="text"
										placeholder="pk_live_..."
										value={
											paymentSettings.stripeLivePublishableKey ||
											""
										}
										onChange={(e) =>
											handlePaymentSettingChange(
												"stripeLivePublishableKey",
												e.target.value
											)
										}
										bg={inputBg}
										borderColor={inputBorder}
										fontSize="sm"
										flex="1"
										_focus={{
											borderColor: "#475BB2",
											boxShadow: "0 0 0 1px #475BB2"
										}}
									/>
								</Flex>

								<Flex align="center">
									<HStack
										spacing={1}
										minW="40%"
										flexShrink={0}
									>
										<Text fontSize="sm" color={mutedColor}>
											{__(
												"Live Secret Key",
												"user-registration"
											)}
										</Text>
										<Tooltip
											label={__(
												"Your Stripe live secret key (starts with sk_live_)",
												"user-registration"
											)}
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</HStack>
									<Input
										type="password"
										placeholder="sk_live_..."
										value={
											paymentSettings.stripeLiveSecretKey ||
											""
										}
										onChange={(e) =>
											handlePaymentSettingChange(
												"stripeLiveSecretKey",
												e.target.value
											)
										}
										bg={inputBg}
										borderColor={inputBorder}
										fontSize="sm"
										flex="1"
										_focus={{
											borderColor: "#475BB2",
											boxShadow: "0 0 0 1px #475BB2"
										}}
									/>
								</Flex>
							</>
						)}
					</VStack>
				</PaymentOption>
			</VStack>
		</>
	);
};

export default PaymentStep;
