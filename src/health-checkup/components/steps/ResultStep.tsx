import { Box, Button, Flex, useToast } from "@chakra-ui/react";
import { __, sprintf } from "@wordpress/i18n";
import { ReactNode, useState } from "react";
import { FiAlertTriangle, FiCheck, FiDownload, FiExternalLink, FiSettings, FiX } from "react-icons/fi";
import { HealthCheck, installSmartSmtp, SmartSmtpStatus, SmtpPluginInfo } from "../../api/healthCheckupApi";
import IssueList from "../IssueList";
import RichText from "../RichText";
import Text from "../Text";

export type ResultVariant = "good" | "none" | "spam";

interface ResultStepProps {
	variant: ResultVariant;
	checks: HealthCheck[];
	onRunAgain: () => void;
	onOpenReport: () => void;
	smartSmtpStatus: SmartSmtpStatus;
	smtpPlugin: SmtpPluginInfo | null;
	/** The server's own error, when the test send failed outright. */
	sendError?: string | null;
}

// The scan already worked out the cause and worded it; re-deriving it here would
// only let the two drift apart. So take the most fundamental failing check and
// reuse its message — this order is "what stops mail earliest".
const CAUSE_PRIORITY = [
	"sending_enabled",
	"sending_route",
	"recent_failures",
	"smtp_setup",
	"smtp_connection",
	"from_address_valid",
	"from_effective",
	"from_alignment",
	"from_domain",
	"spf_record",
	"dmarc_policy",
];

// Causes whose fix is "connect a proper mail service".
const SMTP_FIXABLE = ["sending_route", "smtp_setup", "smtp_connection"];

const diagnoseNonDelivery = (
	checks: HealthCheck[],
	smtpPlugin: SmtpPluginInfo | null,
	sendError?: string | null
) => {
	// An error from the mail server itself outranks every inference below it:
	// the scan predicts what should happen, this is what did.
	if (sendError) {
		return {
			causeKey: "send_failed",
			message: sendError,
			showSmartSmtpAction: true,
			showOtherPluginNotice: false,
		};
	}

	const failing = new Map(
		checks
			.filter((check) => check.status === "error" || check.status === "warning")
			.map((check) => [check.key, check])
	);

	const causeKey = CAUSE_PRIORITY.find((key) => failing.has(key));
	const cause = causeKey ? failing.get(causeKey) : undefined;

	if (cause && causeKey) {
		return {
			causeKey,
			message: cause.message,
			showSmartSmtpAction: SMTP_FIXABLE.includes(causeKey),
			showOtherPluginNotice: false,
		};
	}

	if (smtpPlugin?.is_smartsmtp) {
		return {
			causeKey: null,
			message: __("SmartSMTP is active and configured, but the test still didn't arrive — its primary connection may need reconnecting.", "user-registration"),
			showSmartSmtpAction: true,
			showOtherPluginNotice: false,
		};
	}

	if (smtpPlugin) {
		return {
			causeKey: null,
			message: sprintf(
				/* translators: %s: SMTP plugin name, e.g. "FluentSMTP" */
				__("Your site sends through `%s`, but the test didn't arrive — most likely that plugin's connection settings (host, port, or API key), or the receiving server rejecting the message.", "user-registration"),
				smtpPlugin.name
			),
			showSmartSmtpAction: false,
			showOtherPluginNotice: true,
		};
	}

	return {
		causeKey: null,
		message: __("An SMTP connection is configured, but the test didn't arrive — check whichever service handles your outgoing mail for delivery errors.", "user-registration"),
		showSmartSmtpAction: false,
		showOtherPluginNotice: false,
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
			desc: __("Our own SMTP plugin — free, reliable, and built for WordPress email delivery.", "user-registration"),
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
					{__("Its send log usually shows the exact rejection reason.", "user-registration")}
				</Text>
			</Box>
		</Flex>
		<Button as="a" href="plugins.php" variant="outline" fontSize="13.5px" fontWeight="600" rightIcon={<FiExternalLink size={14} />}>
			{__("Go to Installed Plugins", "user-registration")}
		</Button>
	</Box>
);

type Tone = "green" | "orange" | "red";

const TONE_ICON: Record<Tone, ReactNode> = {
	green: <FiCheck size={16} />,
	orange: <FiAlertTriangle size={16} />,
	red: <FiX size={16} />,
};

const TONE_ICON_COLOR: Record<Tone, string> = {
	green: "green.600",
	orange: "orange.700",
	red: "red.600",
};

// Every outcome renders through this same frame — banner, body, then the one
// pair of actions — so the three results read as the same screen with a
// different summary rather than three unrelated endings.
const ResultFrame = ({
	tone,
	title,
	subtitle,
	children,
	onRunAgain,
	onOpenReport,
}: {
	tone: Tone;
	title: string;
	subtitle: ReactNode;
	children?: ReactNode;
	onRunAgain: () => void;
	onOpenReport: () => void;
}) => (
	<Box>
		<Text
			fontSize="11.5px"
			fontWeight="700"
			letterSpacing="0.06em"
			textTransform="uppercase"
			color="primary.500"
			mb="9px"
		>
			{__("Step 3 · Result", "user-registration")}
		</Text>

		<Flex
			bg={`${tone}.50`}
			border="1px solid"
			borderColor={`${tone}.200`}
			borderRadius="9px"
			p="16px 17px"
			gap="13px"
		>
			<Flex
				flexShrink={0}
				w="32px"
				h="32px"
				borderRadius="8px"
				bg="white"
				align="center"
				justify="center"
				color={TONE_ICON_COLOR[tone]}
			>
				{TONE_ICON[tone]}
			</Flex>
			<Box>
				<Text as="h2" fontSize="16px" fontWeight="700" letterSpacing="-0.01em" color="gray.800">
					{title}
				</Text>
				<Text fontSize="12.5px" color="gray.600" mt="3px" lineHeight="1.55">
					{subtitle}
				</Text>
			</Box>
		</Flex>

		{children}

		<Flex gap="10px" mt="20px" wrap="wrap" justify="flex-end">
			<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onOpenReport}>
				{__("Send report to support", "user-registration")}
			</Button>
			<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={onRunAgain}>
				{__("Run the checkup again", "user-registration")}
			</Button>
		</Flex>
	</Box>
);

const ResultStep = ({ variant, checks, onRunAgain, onOpenReport, smartSmtpStatus, smtpPlugin, sendError }: ResultStepProps) => {
	const openIssues = checks.filter(
		(check) => check.status === "error" || check.status === "warning"
	);

	if (variant === "good") {
		return (
			<ResultFrame
				tone="green"
				title={__("Your test email arrived", "user-registration")}
				subtitle={
					openIssues.length > 0
						? __("Delivery works from this site. A few settings are still worth a look.", "user-registration")
						: __("Delivery works and every check passed — nothing to fix.", "user-registration")
				}
				onRunAgain={onRunAgain}
				onOpenReport={onOpenReport}
			>
				<IssueList issues={openIssues} />
			</ResultFrame>
		);
	}

	if (variant === "spam") {
		return (
			<ResultFrame
				tone="orange"
				title={__("Delivered — but filed as spam", "user-registration")}
				subtitle={__("Sending works. It's the receiving provider that flagged the message.", "user-registration")}
				onRunAgain={onRunAgain}
				onOpenReport={onOpenReport}
			>
				<Text fontSize="13.5px" lineHeight="1.65" color="gray.600" mt="18px">
					<Text as="b" color="inherit">
						{__("Right now:", "user-registration")}
					</Text>{" "}
					{__(
						'open the email and mark it "Not spam" — the strongest single signal you can give your provider to trust this sender.',
						"user-registration"
					)}
				</Text>
				<Text fontSize="13.5px" lineHeight="1.65" color="gray.600" mt="10px">
					<Text as="b" color="inherit">
						{__("If it keeps happening:", "user-registration")}
					</Text>{" "}
					{__(
						"one test in spam isn't proof of a problem, but if real users report the same it usually comes down to sender authentication (SPF, DKIM, DMARC) — DNS records you add at your domain's provider, not something we can set for you. Send a report and support can help.",
						"user-registration"
					)}
				</Text>
				<IssueList issues={openIssues} />
			</ResultFrame>
		);
	}

	const diagnosis = diagnoseNonDelivery(checks, smtpPlugin, sendError);
	// The diagnosis already speaks to whichever check caused it, so listing that
	// one again below would just repeat itself.
	const otherIssues = openIssues.filter((check) => check.key !== diagnosis.causeKey);

	return (
		<ResultFrame
			tone="red"
			title={__("The test email didn't arrive", "user-registration")}
			subtitle={<RichText text={diagnosis.message} />}
			onRunAgain={onRunAgain}
			onOpenReport={onOpenReport}
		>
			{diagnosis.showSmartSmtpAction && <SmartSmtpAction status={smartSmtpStatus} />}
			{diagnosis.showOtherPluginNotice && smtpPlugin && (
				<>
					<OtherSmtpPluginNotice name={smtpPlugin.name} />
					<Flex align="center" gap="10px" my="14px">
						<Box flex="1" h="1px" bg="gray.200" />
						<Text fontSize="11px" fontWeight="700" letterSpacing="0.04em" color="gray.500" textTransform="uppercase">
							{__("Or", "user-registration")}
						</Text>
						<Box flex="1" h="1px" bg="gray.200" />
					</Flex>
					<SmartSmtpAction status={smartSmtpStatus} />
				</>
			)}
			<IssueList issues={otherIssues} />
		</ResultFrame>
	);
};

export default ResultStep;
