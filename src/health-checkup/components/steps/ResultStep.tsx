import { Box, Button, Flex, useToast } from "@chakra-ui/react";
import { __, sprintf } from "@wordpress/i18n";
import { useState } from "react";
import { FiCheck, FiDownload, FiExternalLink, FiSettings, FiTool } from "react-icons/fi";
import { HealthCheck, installSmartSmtp, SmartSmtpStatus, SmtpPluginInfo } from "../../api/healthCheckupApi";
import RichText from "../RichText";
import Text from "../Text";

export type ResultVariant = "good" | "none" | "spam";

interface ResultStepProps {
	variant: ResultVariant;
	checks: HealthCheck[];
	onRunAgain: () => void;
	onDone: () => void;
	onBack: () => void;
	onOpenReport: () => void;
	smartSmtpStatus: SmartSmtpStatus;
	smtpPlugin: SmtpPluginInfo | null;
}

// Ordered so an earlier, more fundamental issue always wins over a later one.
const diagnoseNonDelivery = (checks: HealthCheck[], smtpPlugin: SmtpPluginInfo | null) => {
	const sendingCheck = checks.find((check) => check.key === "sending_enabled");
	const smtpCheck = checks.find((check) => check.key === "smtp_configured");
	const otherIssues = checks.filter(
		(check) => check.status === "issue" && check.key !== "sending_enabled" && check.key !== "smtp_configured"
	);

	if (sendingCheck?.status === "issue") {
		return {
			message: __("Email sending itself is switched off in your settings — nothing goes out until that's re-enabled.", "user-registration"),
			showSmartSmtpAction: false,
			showOtherPluginNotice: false,
			otherIssues,
		};
	}

	if (smtpCheck?.status === "issue") {
		return {
			message: smtpCheck.message,
			showSmartSmtpAction: true,
			showOtherPluginNotice: false,
			otherIssues,
		};
	}

	if (smtpPlugin?.is_smartsmtp) {
		return {
			message: __("SmartSMTP is active and configured, but the test still didn't arrive — its primary connection may need to be checked or reconnected.", "user-registration"),
			showSmartSmtpAction: true,
			showOtherPluginNotice: false,
			otherIssues,
		};
	}

	if (smtpPlugin) {
		return {
			message: sprintf(
				/* translators: %s: SMTP plugin name, e.g. "FluentSMTP" */
				__("Your site sends through `%s`, but the test still didn't arrive — the issue is most likely that plugin's connection settings (host, port, or API key) or the receiving mail server rejecting the message.", "user-registration"),
				smtpPlugin.name
			),
			showSmartSmtpAction: false,
			showOtherPluginNotice: true,
			otherIssues,
		};
	}

	return {
		message: __("Your site has an SMTP connection configured, but the test still didn't arrive — check whichever service handles your outgoing mail for delivery errors.", "user-registration"),
		showSmartSmtpAction: false,
		showOtherPluginNotice: false,
		otherIssues,
	};
};

// Install/activate and configure are two separate clicks: the first only
// installs/activates and toasts; the second (relabeled) opens the Primary
// Connection screen in a new tab.
const SmartSmtpAction = ({ status }: { status: SmartSmtpStatus }) => {
	const [isWorking, setIsWorking] = useState(false);
	const [localStatus, setLocalStatus] = useState<SmartSmtpStatus>(status);
	const [justActivated, setJustActivated] = useState(false);
	const [redirectUrl, setRedirectUrl] = useState<string | null>(null);
	const toast = useToast();

	const handleClick = async () => {
		if (localStatus === "active") {
			window.open(redirectUrl ?? window._UR_EMAIL_HEALTH_.smartSmtpUrl, "_blank", "noopener,noreferrer");
			return;
		}

		setIsWorking(true);
		try {
			const result = await installSmartSmtp();
			setRedirectUrl(result.redirect);
			setJustActivated(true);
			setLocalStatus("active");
			toast({
				title:
					status === "not_installed"
						? __("SmartSMTP installed and activated", "user-registration")
						: __("SmartSMTP activated", "user-registration"),
				status: "success",
				duration: 5000,
				isClosable: true,
			});
		} catch (error) {
			toast({
				title: __("Couldn't set up SmartSMTP", "user-registration"),
				description: error instanceof Error ? error.message : undefined,
				status: "error",
				duration: 6000,
				isClosable: true,
			});
		} finally {
			setIsWorking(false);
		}
	};

	const copy = {
		not_installed: {
			title: __("Recommended: install SmartSMTP", "user-registration"),
			desc: __("Our own SMTP plugin — free, reliable, and built specifically for WordPress email delivery.", "user-registration"),
			button: __("Install & activate SmartSMTP", "user-registration"),
			loading: __("Installing…", "user-registration"),
			icon: <FiDownload size={16} />,
		},
		inactive: {
			title: __("SmartSMTP is installed but not active", "user-registration"),
			desc: __("Activate it, then set up its connection to start delivering reliably.", "user-registration"),
			button: __("Activate SmartSMTP", "user-registration"),
			loading: __("Activating…", "user-registration"),
			icon: <FiSettings size={16} />,
		},
		active: {
			title: justActivated
				? __("SmartSMTP is ready to connect", "user-registration")
				: __("SmartSMTP is active but not connected", "user-registration"),
			desc: __("Open its Primary Connection screen and finish the SMTP setup.", "user-registration"),
			button: justActivated
				? __("Set Up Configuration", "user-registration")
				: __("Configure SmartSMTP", "user-registration"),
			loading: __("Opening…", "user-registration"),
			icon: <FiSettings size={16} />,
		},
	}[localStatus];

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

// Points to a different, already-active SMTP plugin instead of pushing SmartSMTP.
const OtherSmtpPluginNotice = ({ name }: { name: string }) => (
	<Box border="1px solid" borderColor="gray.200" bg="gray.50" borderRadius="8px" p="14px 15px" mt="18px">
		<Flex align="center" gap="11px" mb="9px">
			<Flex flexShrink={0} w="30px" h="30px" borderRadius="7px" bg="white" align="center" justify="center" color="gray.600">
				<FiSettings size={16} />
			</Flex>
			<Box>
				<Text fontSize="13.5px" fontWeight="700">
					{sprintf(
						/* translators: %s: SMTP plugin name */
						__("Check %s's connection status", "user-registration"),
						name
					)}
				</Text>
				<Text fontSize="12px" color="gray.600" mt="1px">
					{__("Its send log will usually show the exact rejection reason.", "user-registration")}
				</Text>
			</Box>
		</Flex>
		<Button as="a" href="plugins.php" variant="outline" fontSize="13.5px" fontWeight="600" rightIcon={<FiExternalLink size={14} />}>
			{__("Go to Installed Plugins", "user-registration")}
		</Button>
	</Box>
);

const ResultStep = ({ variant, checks, onRunAgain, onDone, onBack, onOpenReport, smartSmtpStatus, smtpPlugin }: ResultStepProps) => {
	if (variant === "good") {
		const unresolvedIssues = checks.filter((check) => check.status === "issue");
		const hasUnresolvedIssues = unresolvedIssues.length > 0;

		return (
			<Box>
				<Flex bg="green.50" border="1px solid" borderColor="green.200" borderRadius="9px" p="16px 17px" mb="18px" gap="13px">
					<Flex flexShrink={0} w="32px" h="32px" borderRadius="8px" bg="white" align="center" justify="center" color="green.600">
						<FiCheck size={16} />
					</Flex>
					<Box>
						<Text fontSize="15.5px" fontWeight="700">
							{hasUnresolvedIssues
								? __("Good news — your email arrived", "user-registration")
								: __("You're all set", "user-registration")}
						</Text>
						<Text fontSize="12.5px" color="gray.600" mt="3px" lineHeight="1.55">
							{hasUnresolvedIssues
								? __(
										"But we noticed something worth fixing first, so future emails keep landing reliably:",
										"user-registration"
								  )
								: __("Settings check passed and delivery is confirmed.", "user-registration")}
						</Text>
					</Box>
				</Flex>

				{hasUnresolvedIssues && (
					<Box border="1px solid" borderColor="yellow.300" bg="yellow.50" borderRadius="8px" p="13px 15px" mb="18px">
						<Flex direction="column" gap="8px">
							{unresolvedIssues.map((issue) => (
								<Box key={issue.key}>
									<Text fontSize="12.5px" fontWeight="600" color="yellow.800">
										{issue.title}
									</Text>
									<Text fontSize="12px" color="yellow.800" mt="1px">
										<RichText text={issue.fix || issue.message} />
									</Text>
								</Box>
							))}
						</Flex>
					</Box>
				)}

				<Text fontSize="13.5px" lineHeight="1.65" color="gray.600">
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
		const diagnosis = diagnoseNonDelivery(checks, smtpPlugin);

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
							<RichText text={diagnosis.message} />
						</Text>
					</Box>
				</Flex>

				{diagnosis.otherIssues.length > 0 && (
					<Box border="1px solid" borderColor="gray.200" borderRadius="8px" p="13px 15px" mb="18px">
						<Text fontSize="12.5px" fontWeight="700" color="gray.700" mb="8px">
							{__("Also worth fixing:", "user-registration")}
						</Text>
						<Flex direction="column" gap="8px">
							{diagnosis.otherIssues.map((issue) => (
								<Box key={issue.key}>
									<Text fontSize="12.5px" fontWeight="600">
										{issue.title}
									</Text>
									<Text fontSize="12px" color="gray.600" mt="1px">
										<RichText text={issue.fix || issue.message} />
									</Text>
								</Box>
							))}
						</Flex>
					</Box>
				)}

				{diagnosis.showSmartSmtpAction && <SmartSmtpAction status={smartSmtpStatus} />}
				{diagnosis.showOtherPluginNotice && smtpPlugin && (
					<>
						<OtherSmtpPluginNotice name={smtpPlugin.name} />
						<Flex align="center" gap="10px" my="14px">
							<Box flex="1" h="1px" bg="gray.200" />
							<Text fontSize="11px" fontWeight="700" letterSpacing="0.04em" color="gray.400" textTransform="uppercase">
								{__("Or", "user-registration")}
							</Text>
							<Box flex="1" h="1px" bg="gray.200" />
						</Flex>
						<SmartSmtpAction status={smartSmtpStatus} />
					</>
				)}

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
			<Text fontSize="13.5px" lineHeight="1.65" color="gray.600">
				<Text as="b" color="inherit">
					{__("Right now:", "user-registration")}
				</Text>{" "}
				{__(
					'open the email and mark it "Not spam" — that\'s the single strongest signal you can give your inbox provider to trust this sender going forward.',
					"user-registration"
				)}
			</Text>
			<Text fontSize="13.5px" lineHeight="1.65" color="gray.600" mt="10px">
				<Text as="b" color="inherit">
					{__("If it keeps happening:", "user-registration")}
				</Text>{" "}
				{__(
					"one test email landing in spam isn't proof of a real problem — but if actual users report the same thing, it usually comes down to sender authentication (SPF, DKIM, DMARC — DNS records added at your domain's DNS provider, not something we can set on your behalf). Generate a report below and our support team can assist with that.",
					"user-registration"
				)}
			</Text>
			<Flex gap="10px" mt="20px" wrap="wrap">
				<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={onDone}>
					{__("Done", "user-registration")}
				</Button>
				<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onOpenReport}>
					{__("Send report to support", "user-registration")}
				</Button>
			</Flex>
		</Box>
	);
};

export default ResultStep;
