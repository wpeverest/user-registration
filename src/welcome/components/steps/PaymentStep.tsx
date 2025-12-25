import React, { useEffect, useState } from "react";
import {
	Heading,
	VStack,
	HStack,
	Switch,
	Text,
	Input,
	Textarea,
	Box,
	FormControl,
	FormLabel,
	useColorModeValue,
	Collapse,
	Icon,
	Tooltip,
	Alert,
	AlertIcon,
	Select,
	Skeleton
} from "@chakra-ui/react";
import { useStateValue } from "../../context/StateProvider";
import { PaymentSettings } from "../../context/Gettingstartedcontext";
import { apiGet, CurrencyData, PaymentSettingsResponse } from "../../api/gettingStartedApi";

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
	const expandedBg = useColorModeValue("gray.50", "gray.750");

	return (
		<Box>
			<HStack justify="space-between" py={4}>
				<Text fontWeight="500" color={textColor}>
					{label}
				</Text>
				<Switch
					isChecked={isChecked}
					onChange={(e) => onChange(e.target.checked)}
					colorScheme="blue"
					size="md"
					sx={{
						"& .chakra-switch__track[data-checked]": {
							bg: "#475BD8"
						}
					}}
				/>
			</HStack>
			<Collapse in={isChecked && !!children} animateOpacity>
				<Box
					pl={4}
					pb={4}
					borderLeftWidth="2px"
					borderLeftColor="#475BD8"
					ml={1}
					bg={expandedBg}
					borderRadius="md"
					p={4}
					mb={4}
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
	const mutedColor = useColorModeValue("gray.600", "gray.400");
	const inputBg = useColorModeValue("white", "gray.700");
	const inputBorder = useColorModeValue("gray.300", "gray.600");
	const infoBg = useColorModeValue("blue.50", "blue.900");
	const infoBorder = useColorModeValue("blue.200", "blue.700");

	useEffect(() => {
		const loadPaymentSettings = async () => {
			try {
				setIsLoadingSettings(true);
				const response = await apiGet<PaymentSettingsResponse>("/payments");

				// Load currencies from API
				if (response.currencies) {
					setCurrencies(response.currencies);
				}

				// Load selected currency if available
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
					const gateways = response.payment_gateways as PaymentGatewayData[];

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
			<Heading size="lg" color={textColor} mb={2}>
				Payments
			</Heading>
			<Text color={mutedColor} mb={6} fontSize="sm">
				Configure your payment gateways to accept payments from members.
			</Text>

			{/* Currency Selection */}
			<FormControl mb={6}>
				<FormLabel
					fontSize="sm"
					fontWeight="500"
					color={textColor}
					display="flex"
					alignItems="center"
					gap={2}
				>
					Currency
					<Tooltip
						label="Select the currency for all transactions"
						hasArrow
					>
						<span>
							<InfoIcon />
						</span>
					</Tooltip>
				</FormLabel>
				{isLoadingSettings ? (
					<Skeleton height="40px" borderRadius="md" />
				) : (
					<Select
						value={paymentSettings.currency || ""}
						onChange={(e) =>
							handlePaymentSettingChange("currency", e.target.value)
						}
						bg={inputBg}
						borderColor={inputBorder}
						fontSize="sm"
						placeholder="Select currency"
						_focus={{
							borderColor: "#475BD8",
							boxShadow: "0 0 0 1px #475BD8"
						}}
					>
						{currencies.map((currency) => (
							<option key={currency.code} value={currency.code}>
								{currency.code} - {currency.name} ({currency.symbol})
							</option>
						))}
					</Select>
				)}
			</FormControl>

			<VStack spacing={0} align="stretch">
				{/* Offline Payment */}
				<PaymentOption
					label="Offline Payment"
					isChecked={paymentSettings.offlinePayment}
					onChange={(checked) =>
						handlePaymentSettingChange("offlinePayment", checked)
					}
				>
					<FormControl>
						<FormLabel
							fontSize="sm"
							color={mutedColor}
							display="flex"
							alignItems="center"
							gap={2}
						>
							Bank Details
							<Tooltip
								label="Enter your bank account details for offline payments"
								hasArrow
							>
								<span>
									<InfoIcon />
								</span>
							</Tooltip>
						</FormLabel>
						<Textarea
							placeholder="Enter your bank account details here..."
							value={paymentSettings.bankDetails || ""}
							onChange={(e) =>
								handlePaymentSettingChange(
									"bankDetails",
									e.target.value
								)
							}
							bg={inputBg}
							borderColor={inputBorder}
							rows={4}
							fontSize="sm"
							_focus={{
								borderColor: "#475BD8",
								boxShadow: "0 0 0 1px #475BD8"
							}}
						/>
					</FormControl>
				</PaymentOption>

				{/* PayPal */}

				<PaymentOption
					label="Paypal"
					isChecked={paymentSettings.paypal}
					onChange={(checked) =>
						handlePaymentSettingChange("paypal", checked)
					}
				>
					<VStack spacing={4} align="stretch">
						<FormControl>
							<FormLabel
								fontSize="sm"
								color={mutedColor}
								display="flex"
								alignItems="center"
								gap={2}
							>
								PayPal Email
								<Tooltip
									label="Enter the email address associated with your PayPal account"
									hasArrow
								>
									<span>
										<InfoIcon />
									</span>
								</Tooltip>
							</FormLabel>
							<Input
								type="email"
								placeholder="your-email@example.com"
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
								_focus={{
									borderColor: "#475BD8",
									boxShadow: "0 0 0 1px #475BD8"
								}}
							/>
						</FormControl>

						<FormControl>
							<FormLabel
								fontSize="sm"
								color={mutedColor}
								display="flex"
								alignItems="center"
								gap={2}
							>
								Client ID
								<Tooltip
									label="Your client_id, Required for subscription related operations."
									hasArrow
								>
									<span>
										<InfoIcon />
									</span>
								</Tooltip>
							</FormLabel>
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
								_focus={{
									borderColor: "#475BD8",
									boxShadow: "0 0 0 1px #475BD8"
								}}
							/>
						</FormControl>

						<FormControl>
							<FormLabel
								fontSize="sm"
								color={mutedColor}
								display="flex"
								alignItems="center"
								gap={2}
							>
								Client Secret
								<Tooltip
									label="Your client_secret, Required for subscription related operations"
									hasArrow
								>
									<span>
										<InfoIcon />
									</span>
								</Tooltip>
							</FormLabel>
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
								_focus={{
									borderColor: "#475BD8",
									boxShadow: "0 0 0 1px #475BD8"
								}}
							/>
						</FormControl>
					</VStack>
				</PaymentOption>

				{/* Stripe */}
				<PaymentOption
					label="Stripe"
					isChecked={paymentSettings.stripe}
					onChange={(checked) =>
						handlePaymentSettingChange("stripe", checked)
					}
				>
					<VStack spacing={4} align="stretch">
						{/* Test Mode Toggle */}
						<HStack justify="space-between">
							<HStack spacing={2}>
								<Text fontSize="sm" color={mutedColor}>
									Enable Test Mode
								</Text>
								<Tooltip
									label="Enable test mode to use Stripe's test environment"
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
										bg: "#475BD8"
									}
								}}
							/>
						</HStack>

						{/* Conditional Key Fields based on Test Mode */}
						{paymentSettings.stripeTestMode ? (
							<>
								<FormControl>
									<FormLabel
										fontSize="sm"
										color={mutedColor}
										display="flex"
										alignItems="center"
										gap={2}
									>
										Test Publishable Key
										<Tooltip
											label="Your Stripe test publishable key (starts with pk_test_)"
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</FormLabel>
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
										_focus={{
											borderColor: "#475BD8",
											boxShadow: "0 0 0 1px #475BD8"
										}}
									/>
								</FormControl>

								<FormControl>
									<FormLabel
										fontSize="sm"
										color={mutedColor}
										display="flex"
										alignItems="center"
										gap={2}
									>
										Test Secret Key
										<Tooltip
											label="Your Stripe test secret key (starts with sk_test_)"
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</FormLabel>
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
										_focus={{
											borderColor: "#475BD8",
											boxShadow: "0 0 0 1px #475BD8"
										}}
									/>
								</FormControl>
							</>
						) : (
							<>
								<FormControl>
									<FormLabel
										fontSize="sm"
										color={mutedColor}
										display="flex"
										alignItems="center"
										gap={2}
									>
										Live Publishable Key
										<Tooltip
											label="Your Stripe live publishable key (starts with pk_live_)"
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</FormLabel>
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
										_focus={{
											borderColor: "#475BD8",
											boxShadow: "0 0 0 1px #475BD8"
										}}
									/>
								</FormControl>

								<FormControl>
									<FormLabel
										fontSize="sm"
										color={mutedColor}
										display="flex"
										alignItems="center"
										gap={2}
									>
										Live Secret Key
										<Tooltip
											label="Your Stripe live secret key (starts with sk_live_)"
											hasArrow
										>
											<span>
												<InfoIcon />
											</span>
										</Tooltip>
									</FormLabel>
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
										_focus={{
											borderColor: "#475BD8",
											boxShadow: "0 0 0 1px #475BD8"
										}}
									/>
								</FormControl>
							</>
						)}
					</VStack>
				</PaymentOption>
			</VStack>

			{/* Info Alert */}
			<Alert
				status="info"
				variant="subtle"
				mt={6}
				borderRadius="md"
				bg={infoBg}
				borderWidth="1px"
				borderColor={infoBorder}
			>
				<AlertIcon color="#475BD8" />
				<Text fontSize="sm" color={mutedColor}>
					Additional payment gateways can be setup later as well.
				</Text>
			</Alert>
		</>
	);
};

export default PaymentStep;
