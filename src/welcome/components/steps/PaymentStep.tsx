import React from "react";
import {
	Heading,
	VStack,
	HStack,
	Switch,
	Text,
	useColorModeValue,
} from "@chakra-ui/react";
import { useStateValue } from "../../context/StateProvider";
import { PaymentSettings } from "../../context/Gettingstartedcontext";

interface PaymentOptionProps {
	label: string;
	isChecked: boolean;
	onChange: (checked: boolean) => void;
}

const PaymentOption: React.FC<PaymentOptionProps> = ({
	label,
	isChecked,
	onChange,
}) => {
	const textColor = useColorModeValue("gray.800", "white");
	const borderColor = useColorModeValue("gray.100", "gray.700");

	return (
		<HStack
			justify="space-between"
			py={4}
			borderBottomWidth="1px"
			borderColor={borderColor}
		>
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
						bg: "#475BD8",
					},
				}}
			/>
		</HStack>
	);
};

const PaymentStep: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const { paymentSettings } = state;

	const textColor = useColorModeValue("gray.800", "white");

	const handlePaymentSettingChange = (
		key: keyof PaymentSettings,
		value: boolean
	) => {
		dispatch({
			type: "SET_PAYMENT_SETTING",
			payload: { key, value },
		});
	};

	return (
		<>
			<Heading size="lg" color={textColor} mb={8}>
				Payment Setup
			</Heading>

			<VStack spacing={0} align="stretch">
				<PaymentOption
					label="Offline Payment"
					isChecked={paymentSettings.offlinePayment}
					onChange={(checked) =>
						handlePaymentSettingChange("offlinePayment", checked)
					}
				/>
				<PaymentOption
					label="Paypal"
					isChecked={paymentSettings.paypal}
					onChange={(checked) => handlePaymentSettingChange("paypal", checked)}
				/>
				<PaymentOption
					label="Stripe"
					isChecked={paymentSettings.stripe}
					onChange={(checked) => handlePaymentSettingChange("stripe", checked)}
				/>
			</VStack>
		</>
	);
};

export default PaymentStep;
