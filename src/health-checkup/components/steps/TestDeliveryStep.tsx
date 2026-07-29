import { Box, Flex, Spinner } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useEffect, useRef, useState } from "react";
import { FiAlertTriangle, FiCheck, FiChevronRight, FiX } from "react-icons/fi";
import { DeliveryOutcome, sendTestEmail } from "../../api/healthCheckupApi";
import Text from "../Text";

interface Choice {
	outcome: DeliveryOutcome;
	title: string;
	desc: string;
	icon: React.ReactNode;
	iconBg: string;
	iconColor: string;
}

interface TestDeliveryStepProps {
	onChoice: (outcome: DeliveryOutcome) => void;
	alreadySent: boolean;
	onSent: () => void;
}

const TestDeliveryStep = ({ onChoice, alreadySent, onSent }: TestDeliveryStepProps) => {
	const [isSending, setIsSending] = useState(!alreadySent);
	const [barWidth, setBarWidth] = useState("3%");
	const [pendingChoice, setPendingChoice] = useState<DeliveryOutcome | null>(null);
	const hasStarted = useRef(false);
	const adminEmail = window._UR_EMAIL_HEALTH_.adminEmail;

	useEffect(() => {
		if (hasStarted.current || alreadySent) {
			return;
		}
		hasStarted.current = true;

		requestAnimationFrame(() => requestAnimationFrame(() => setBarWidth("100%")));

		sendTestEmail(adminEmail)
			.catch(() => undefined)
			.finally(() => {
				setIsSending(false);
				onSent();
			});
	}, [adminEmail, alreadySent, onSent]);

	const choices: Choice[] = [
		{
			outcome: "arrived",
			title: __("Yes — it arrived in my inbox", "user-registration"),
			desc: __("Even if it was in spam, choose this", "user-registration"),
			icon: <FiCheck size={15} />,
			iconBg: "green.50",
			iconColor: "green.600",
		},
		{
			outcome: "none",
			title: __("No — nothing at all", "user-registration"),
			desc: __("Not in inbox, not in spam", "user-registration"),
			icon: <FiX size={15} />,
			iconBg: "red.50",
			iconColor: "red.600",
		},
		{
			outcome: "spam",
			title: __("Found it in spam", "user-registration"),
			desc: __("It arrived, but in the wrong folder", "user-registration"),
			icon: <FiAlertTriangle size={15} />,
			iconBg: "primary.50",
			iconColor: "primary.600",
		},
	];

	const handleChoice = (outcome: DeliveryOutcome) => {
		if (pendingChoice) {
			return;
		}
		setPendingChoice(outcome);
		setTimeout(() => onChoice(outcome), 550);
	};

	return (
		<Box>
			<Text
				fontSize="11.5px"
				fontWeight="700"
				letterSpacing="0.06em"
				textTransform="uppercase"
				color="primary.500"
				mb="9px"
			>
				{__("Step 2 · The real test", "user-registration")}
			</Text>
			<Text as="h2" fontSize="21px" fontWeight="600" mb="10px" letterSpacing="-0.01em" color="gray.800">
				{isSending
					? __("Sending your test email…", "user-registration")
					: __("Did the test email actually arrive?", "user-registration")}
			</Text>
			<Text fontSize="14px" lineHeight="1.62" color="gray.600" mb="22px" maxW="60ch">
				{isSending ? (
					__("Hang tight — this only takes a second.", "user-registration")
				) : (
					<>
						{__("We just sent a test email to", "user-registration")}{" "}
						<Text as="b" color="inherit">
							{adminEmail}
						</Text>
						. {__("Check your inbox now — and check spam too.", "user-registration")}
					</>
				)}
			</Text>

			{isSending && (
				<Box pb="26px">
					<Box height="6px" borderRadius="6px" bg="gray.200" overflow="hidden">
						<Box
							height="100%"
							width={barWidth}
							bg="primary.500"
							borderRadius="6px"
							transition="width 950ms cubic-bezier(.4,0,.2,1)"
						/>
					</Box>
				</Box>
			)}

			{!isSending && (
				<>
					<Text fontSize="13.5px" lineHeight="1.6" color="gray.500" mt="-10px" mb="22px" maxW="58ch">
						{__(
							'The "sent successfully" message only means the site tried. What matters is whether it actually',
							"user-registration"
						)}{" "}
						<Text as="b" color="gray.600">
							{__("landed", "user-registration")}
						</Text>
						.
					</Text>

					<Flex direction="column" gap="9px">
						{choices.map((choice) => (
							<Flex
								as="button"
								type="button"
								key={choice.outcome}
								align="center"
								gap="13px"
								border="1px solid"
								borderColor="gray.200"
								bg="white"
								borderRadius="8px"
								p="13px 15px"
								textAlign="left"
								cursor={pendingChoice ? "default" : "pointer"}
								width="100%"
								_hover={pendingChoice ? {} : { borderColor: "primary.500", bg: "primary.50" }}
								onClick={(e) => {
									e.preventDefault();
									handleChoice(choice.outcome);
								}}
							>
								<Flex
									flexShrink={0}
									w="30px"
									h="30px"
									borderRadius="7px"
									align="center"
									justify="center"
									bg={choice.iconBg}
									color={choice.iconColor}
								>
									{pendingChoice === choice.outcome ? <Spinner size="sm" /> : choice.icon}
								</Flex>
								<Box flex="1" minW="0">
									<Text fontSize="13.5px" fontWeight="600" display="block">
										{choice.title}
									</Text>
									<Text fontSize="12px" color="gray.500" mt="1px">
										{choice.desc}
									</Text>
								</Box>
								<Box flexShrink={0} color="gray.400">
									<FiChevronRight size={16} />
								</Box>
							</Flex>
						))}
					</Flex>
				</>
			)}
		</Box>
	);
};

export default TestDeliveryStep;
