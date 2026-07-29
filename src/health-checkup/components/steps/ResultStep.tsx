import { Box, Button, Flex, useToast } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useState } from "react";
import { FiCheck, FiDownload, FiSettings, FiTool } from "react-icons/fi";
import { installSmartSmtp, SmartSmtpStatus } from "../../api/healthCheckupApi";
import Text from "../Text";

export type ResultVariant = "good" | "none" | "spam";

interface ResultStepProps {
	variant: ResultVariant;
	onRunAgain: () => void;
	onDone: () => void;
	onBack: () => void;
	onOpenReport: () => void;
	smartSmtpStatus: SmartSmtpStatus;
}

// Prefer SmartSMTP as the concrete, one-click fix over generic "go install
// an SMTP plugin" advice — installs/activates it directly, then hands the
// admin off to its Primary Connection screen to pick Gmail.
const SmartSmtpAction = ({ status }: { status: SmartSmtpStatus }) => {
	const [isWorking, setIsWorking] = useState(false);
	const toast = useToast();

	const handleClick = async () => {
		if (status === "active") {
			window.location.href = window._UR_EMAIL_HEALTH_.smartSmtpUrl;
			return;
		}

		setIsWorking(true);
		try {
			const result = await installSmartSmtp();
			window.location.href = result.redirect;
		} catch (error) {
			setIsWorking(false);
			toast({
				title: __("Couldn't set up SmartSMTP", "user-registration"),
				description: error instanceof Error ? error.message : undefined,
				status: "error",
				duration: 6000,
				isClosable: true,
			});
		}
	};

	const copy = {
		not_installed: {
			title: __("Recommended: install SmartSMTP", "user-registration"),
			desc: __("Our own SMTP plugin — free, and connects to Gmail in a couple of clicks.", "user-registration"),
			button: __("Install & activate SmartSMTP", "user-registration"),
			loading: __("Installing…", "user-registration"),
			icon: <FiDownload size={16} />,
		},
		inactive: {
			title: __("SmartSMTP is installed but not active", "user-registration"),
			desc: __("Activate it, then connect it to Gmail to start delivering reliably.", "user-registration"),
			button: __("Activate SmartSMTP", "user-registration"),
			loading: __("Activating…", "user-registration"),
			icon: <FiSettings size={16} />,
		},
		active: {
			title: __("SmartSMTP is active but not connected", "user-registration"),
			desc: __("Open its Primary Connection screen and connect a Gmail account.", "user-registration"),
			button: __("Configure SmartSMTP", "user-registration"),
			loading: __("Opening…", "user-registration"),
			icon: <FiSettings size={16} />,
		},
	}[status];

	return (
		<Box border="1px solid" borderColor="primary.200" bg="primary.50" borderRadius="8px" p="14px 15px" mt="18px">
			<Flex align="center" gap="11px" mb="11px">
				<Flex flexShrink={0} w="30px" h="30px" borderRadius="7px" bg="white" align="center" justify="center" color="primary.600">
					{copy.icon}
				</Flex>
				<Box>
					<Text fontSize="13.5px" fontWeight="700">
						{copy.title}
					</Text>
					<Text fontSize="12px" color="gray.600" mt="1px">
						{copy.desc}
					</Text>
				</Box>
			</Flex>
			<Button
				colorScheme="primary"
				fontSize="13.5px"
				fontWeight="600"
				onClick={handleClick}
				isLoading={isWorking}
				loadingText={copy.loading}
			>
				{copy.button}
			</Button>
		</Box>
	);
};

const ResultStep = ({ variant, onRunAgain, onDone, onBack, onOpenReport, smartSmtpStatus }: ResultStepProps) => {
	if (variant === "good") {
		return (
			<Box>
				<Flex bg="green.50" border="1px solid" borderColor="green.200" borderRadius="9px" p="16px 17px" mb="18px" gap="13px">
					<Flex flexShrink={0} w="32px" h="32px" borderRadius="8px" bg="white" align="center" justify="center" color="green.600">
						<FiCheck size={16} />
					</Flex>
					<Box>
						<Text fontSize="15.5px" fontWeight="700">
							{__("You're all set", "user-registration")}
						</Text>
						<Text fontSize="12.5px" color="gray.600" mt="3px" lineHeight="1.55">
							{__("Settings check passed and delivery is confirmed.", "user-registration")}
						</Text>
					</Box>
				</Flex>
				<Text fontSize="13.5px" lineHeight="1.65" color="gray.600" maxW="60ch">
					{__(
						"Your email delivery is working. If anything changes in the future, you can run this checkup again any time from the Emails settings page.",
						"user-registration"
					)}
				</Text>
				<Flex gap="10px" mt="20px" wrap="wrap">
					<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={onRunAgain}>
						{__("Run the checkup again", "user-registration")}
					</Button>
					<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onDone}>
						{__("Done", "user-registration")}
					</Button>
				</Flex>
				<Text fontSize="13px" color="gray.500" mt="16px">
					{__("Want our team to review?", "user-registration")}{" "}
					<Button variant="link" color="primary.600" fontWeight="600" fontSize="inherit" onClick={onOpenReport}>
						{__("Send a report", "user-registration")}
					</Button>
				</Text>
			</Box>
		);
	}

	if (variant === "none") {
		return (
			<Box>
				<Flex bg="yellow.50" border="1px solid" borderColor="yellow.300" borderRadius="9px" p="16px 17px" mb="18px" gap="13px">
					<Flex flexShrink={0} w="32px" h="32px" borderRadius="8px" bg="white" align="center" justify="center" color="yellow.700">
						<FiTool size={16} />
					</Flex>
					<Box>
						<Text fontSize="15.5px" fontWeight="700">
							{__("The email didn't arrive", "user-registration")}
						</Text>
						<Text fontSize="12.5px" color="gray.600" mt="3px" lineHeight="1.55">
							{__("Your site isn't using an SMTP service, which is why most hosts fail to deliver.", "user-registration")}
						</Text>
					</Box>
				</Flex>
				<Text fontSize="13.5px" lineHeight="1.65" color="gray.600" maxW="60ch">
					{__(
						"The fix is a one-time setup: install an SMTP plugin and connect it to a sending service. Once that's done, emails start arriving reliably.",
						"user-registration"
					)}
				</Text>

				<SmartSmtpAction status={smartSmtpStatus} />

				<Box border="1px solid" borderColor="gray.200" bg="gray.50" borderRadius="8px" p="13px 15px" fontSize="13px" color="gray.600" lineHeight="1.55" mt="12px">
					<Text as="b" color="inherit">
						{__("Not technical?", "user-registration")}
					</Text>{" "}
					{__("Generate a report and our team will walk you through it.", "user-registration")}
				</Box>
				<Flex gap="10px" mt="20px" wrap="wrap">
					<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onRunAgain}>
						{__("Run the checkup again", "user-registration")}
					</Button>
					<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onOpenReport}>
						{__("Create a report for support", "user-registration")}
					</Button>
					<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onBack}>
						{__("Back", "user-registration")}
					</Button>
				</Flex>
			</Box>
		);
	}

	return (
		<Box>
			<Flex bg="green.50" border="1px solid" borderColor="green.200" borderRadius="9px" p="16px 17px" mb="18px" gap="13px">
				<Flex flexShrink={0} w="32px" h="32px" borderRadius="8px" bg="white" align="center" justify="center" color="green.600">
					<FiCheck size={16} />
				</Flex>
				<Box>
					<Text fontSize="15.5px" fontWeight="700">
						{__("Found it — emails are sending", "user-registration")}
					</Text>
					<Text fontSize="12.5px" color="gray.600" mt="3px" lineHeight="1.55">
						{__("The email arrived but the provider filed it as spam.", "user-registration")}
					</Text>
				</Box>
			</Flex>
			<Text fontSize="13.5px" lineHeight="1.65" color="gray.600" maxW="60ch">
				{__("Open the email and mark it", "user-registration")}{" "}
				<Text as="b" color="inherit">
					{__('"Not spam"', "user-registration")}
				</Text>
				. {__("That trains the inbox for future emails.", "user-registration")}
			</Text>
			<Text fontSize="13.5px" lineHeight="1.65" color="gray.600" maxW="60ch" mt="10px">
				{__(
					"If many users report spam issues, improving sender authentication (SPF, DKIM, DMARC) will help long-term. Our team can assist with that.",
					"user-registration"
				)}
			</Text>
			<Flex gap="10px" mt="20px" wrap="wrap">
				<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={onDone}>
					{__("Done", "user-registration")}
				</Button>
				<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onOpenReport}>
					{__("Get help with spam placement", "user-registration")}
				</Button>
			</Flex>
		</Box>
	);
};

export default ResultStep;
