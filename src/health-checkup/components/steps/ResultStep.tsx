import { Box, Button, Flex, Link, useToast } from "@chakra-ui/react";
import { __, sprintf } from "@wordpress/i18n";
import { ReactNode, useState } from "react";
import { FiAlertTriangle, FiArrowRight, FiCheck, FiX } from "react-icons/fi";
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
// SPF and DMARC are absent on purpose: they are report-only now, never counted
// as issues, so they can't be the cause this screen names.
const CAUSE_PRIORITY = [
	"sending_enabled",
	"sending_route",
	"recent_failures",
	"smtp_connection",
	"from_address_valid",
	"from_effective",
	"from_alignment",
	"from_domain",
];

/**
 * What to say when the send reported no error and nothing in the scan failed
 * either — every check passed and the mail still went missing. With no remedy
 * list to lead with, this names the most likely place to look instead.
 *
 * When something *did* fail, the remedy list speaks for itself and this isn't
 * consulted; the priority order above is only used to pick the one finding whose
 * wording best explains the failure.
 */
const nonDeliveryExplanation = (
	checks: HealthCheck[],
	smtpPlugin: SmtpPluginInfo | null
): string => {
	const failing = new Map(
		checks
			.filter((check) => check.status === "error" || check.status === "warning")
			.map((check) => [check.key, check])
	);

	const causeKey = CAUSE_PRIORITY.find((key) => failing.has(key));
	const cause = causeKey ? failing.get(causeKey) : undefined;

	if (cause) {
		return cause.message;
	}

	if (smtpPlugin?.is_smartsmtp) {
		return __(
			"SmartSMTP is active and configured, but the test still didn't arrive — its primary connection may need reconnecting.",
			"user-registration"
		);
	}

	if (smtpPlugin) {
		return sprintf(
			/* translators: %s: SMTP plugin name, e.g. "FluentSMTP" */
			__(
				"Your site sends through `%s`, but the test didn't arrive — most likely that plugin's connection settings (host, port, or API key), or the receiving server rejecting the message.",
				"user-registration"
			),
			smtpPlugin.name
		);
	}

	return __(
		"An SMTP connection is configured, but the test didn't arrive — check whichever service handles your outgoing mail for delivery errors.",
		"user-registration"
	);
};

/**
 * The recommended route out, offered as a sentence and one link.
 *
 * Deliberately not a filled button: the failure screen is prose the admin has to
 * read, and a solid button planted in the middle of it reads as *the* action of
 * the page — which it isn't, since the alternative below is equally valid. It
 * stays a real <button> underneath, because it performs work rather than
 * navigating, and it carries its own loading state while the install runs.
 *
 * Install/activate and configure remain two separate clicks: the first does the
 * work and relabels, the second opens the Primary Connection screen.
 */
const SmartSmtpRecommendation = ({ status }: { status: SmartSmtpStatus }) => {
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

	// One sentence naming what will happen, then the link that does it. The
	// wording tracks what is actually needed here — installing, activating, or
	// just finishing the connection — so it never offers to install something
	// that is already sitting on the site.
	const copy = {
		not_installed: {
			lead: __(
				"Our best recommendation is to install and activate SmartSMTP — our own free plugin — and let it deliver your email reliably.",
				"user-registration"
			),
			action: __("Install & activate SmartSMTP", "user-registration"),
			loading: __("Installing…", "user-registration"),
		},
		inactive: {
			lead: __(
				"Our best recommendation is to activate SmartSMTP — it is already installed here — and let it deliver your email reliably.",
				"user-registration"
			),
			action: __("Activate SmartSMTP", "user-registration"),
			loading: __("Activating…", "user-registration"),
		},
		active: {
			lead: justActivated
				? __(
						"SmartSMTP is ready. One step left: connect it to a mail service and it will deliver your email from here on.",
						"user-registration"
					)
				: __(
						"Our best recommendation is to finish setting up SmartSMTP — it is active but has no working connection yet, which is why nothing is being delivered.",
						"user-registration"
					),
			action: __("Set up SmartSMTP", "user-registration"),
			loading: __("Opening…", "user-registration"),
		},
	}[localStatus];

	return (
		<Box>
			<Text fontSize="13px" color="gray.700" lineHeight="1.6">
				{copy.lead}
			</Text>
			<Button
				variant="link"
				colorScheme="primary"
				fontSize="13px"
				fontWeight="700"
				mt="9px"
				onClick={handleClick}
				isLoading={isWorking}
				loadingText={copy.loading}
				rightIcon={<FiArrowRight size={13} />}
			>
				{copy.action}
			</Button>
		</Box>
	);
};

/** Failing checks that actually have a remedy to offer. */
const remediableChecks = (checks: HealthCheck[]) =>
	checks.filter((check) => (check.status === "error" || check.status === "warning") && check.fix);

/**
 * The remedies for every failing check.
 *
 * The mail delivery step states findings and stops there — "connect an SMTP
 * service", "send from a domain you own" are recommendations, and a scan alone
 * hasn't earned the right to make them. Once the live test has actually failed
 * it has, so they surface here, in the order the checks ran: the route mail
 * takes first, then who the site may send as, then the plugin's own settings.
 *
 * Borderless on purpose — it sits inside the result card rather than beside it.
 */
const SuggestedFixes = ({ suggestions }: { suggestions: HealthCheck[] }) => (
	<Box as="ol" pl="17px" m="0" mt="10px">
		{suggestions.map((check) => (
			<Box as="li" key={check.key} mb="9px" _last={{ mb: 0 }} color="gray.600">
				<Text fontSize="12.5px" fontWeight="600" color="gray.700">
					{check.title}
				</Text>
				<Text fontSize="12.5px" color="gray.600" mt="1px" lineHeight="1.55">
					<RichText text={check.fix} />
				</Text>
			</Box>
		))}
	</Box>
);

/** A labelled paragraph, for a result that has more than one thing to say. */
const Advice = ({ label, children }: { label: string; children: ReactNode }) => (
	<Box mt="15px">
		<Text fontSize="12.5px" fontWeight="700" color="gray.700">
			{label}
		</Text>
		<Text fontSize="12.5px" color="gray.600" mt="3px" lineHeight="1.6">
			{children}
		</Text>
	</Box>
);

/** The last resort, set apart from the advice above it. */
const SupportFootnote = ({ children }: { children: ReactNode }) => (
	<Text
		fontSize="12.5px"
		color="gray.500"
		mt="18px"
		pt="15px"
		borderTop="1px solid"
		borderColor="gray.200"
		lineHeight="1.55"
	>
		{children}
	</Text>
);

/** Separates "fix these yourself" from "or let SmartSMTP handle it". */
const OrDivider = () => (
	<Flex align="center" gap="10px" my="16px">
		<Box flex="1" h="1px" bg="gray.200" />
		<Text fontSize="11px" fontWeight="700" letterSpacing="0.04em" color="gray.500" textTransform="uppercase">
			{__("Or", "user-registration")}
		</Text>
		<Box flex="1" h="1px" bg="gray.200" />
	</Flex>
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

// Every outcome renders through this same frame — headline, body, then the one
// pair of actions — so the three results read as the same screen with a
// different verdict rather than three unrelated endings.
const ResultFrame = ({
	tone,
	title,
	subtitle,
	bannerFooter,
	body,
	onRunAgain,
	onOpenReport,
}: {
	tone: Tone;
	title: string;
	subtitle: ReactNode;
	bannerFooter?: ReactNode;
	/** Everything the outcome has to say, below the headline. */
	body?: ReactNode;
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
			mb="14px"
		>
			{__("Step 5 · Result", "user-registration")}
		</Text>

		{/* No panel of its own. This already sits inside the wizard's card, so a
		    second border around the same content just drew a box inside a box —
		    and a tinted one turned a whole screen of remedy into an alarm. The
		    outcome is carried by the icon and the headline instead.

		    The icon pairs with the title on one row rather than owning a column:
		    as a column it left a 32px disc atop a gutter as tall as the screen. */}
		<Flex align="center" gap="12px">
			{/* Tinted disc rather than filled — white on any of these three hues is
			    too low-contrast to be safe, and the soft circle reads as a status
			    marker instead of a button. */}
			<Flex
				flexShrink={0}
				w="32px"
				h="32px"
				borderRadius="full"
				bg={`${tone}.50`}
				align="center"
				justify="center"
				color={TONE_ICON_COLOR[tone]}
			>
				{TONE_ICON[tone]}
			</Flex>
			<Text as="h2" fontSize="16.5px" fontWeight="700" letterSpacing="-0.01em" color="gray.800">
				{title}
			</Text>
		</Flex>
		<Text fontSize="13px" color="gray.600" mt="12px" lineHeight="1.6">
			{subtitle}
		</Text>
		{bannerFooter}
		{body}

		<Flex gap="10px" mt="24px" wrap="wrap" justify="flex-end">
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
				// Says plainly that the leftovers didn't stop it. Without that, a
				// green headline over "3 more things to fix" reads as a contradiction
				// and the admin can't tell which half to believe.
				subtitle={
					openIssues.length > 0
						? __(
								"Delivery works from this site — the message reached the inbox. Nothing below stopped it arriving, but a few settings are still worth a look.",
								"user-registration"
							)
						: __(
								"Delivery works from this site and every check passed. There is nothing left to fix.",
								"user-registration"
							)
				}
				onRunAgain={onRunAgain}
				onOpenReport={onOpenReport}
				body={
					openIssues.length > 0 ? (
						<IssueList issues={openIssues} />
					) : (
						<SupportFootnote>
							{__(
								"Registration emails should reach your users the same way. Run this again any time you change your email or hosting setup.",
								"user-registration"
							)}
						</SupportFootnote>
					)
				}
			/>
		);
	}

	// No SPF, DMARC or DKIM by name. Whether a message is filed as spam is the
	// receiving provider's judgement, and the levers behind those acronyms are not
	// ones an admin can pull from this screen — naming them only invites a search
	// engine detour. "What your domain says about who may send for it" carries the
	// same meaning to the one audience that can act on it: our support team.
	if (variant === "spam") {
		return (
			<ResultFrame
				tone="orange"
				title={__("It arrived — but landed in spam", "user-registration")}
				subtitle={__(
					"Sending works: the message got through. It was the receiving mailbox that decided to file it as spam.",
					"user-registration"
				)}
				onRunAgain={onRunAgain}
				onOpenReport={onOpenReport}
				body={
					<>
						<Advice label={__("Do this first", "user-registration")}>
							{__(
								'Open the email and mark it "Not spam". That teaches your own mailbox to trust you — though it won\'t change anything for the people registering on your site.',
								"user-registration"
							)}
						</Advice>

						<Advice label={__("If it keeps happening", "user-registration")}>
							{__(
								"One message in spam isn't proof of a problem. Filtering is the receiving provider's own judgement, and a domain that has only recently started sending email gets treated more harshly until it builds up a history.",
								"user-registration"
							)}
						</Advice>

						<IssueList issues={openIssues} />

						{/* Last, so the escape hatch stays the final word on the screen. */}
						<SupportFootnote>
							{__(
								"Still landing in spam? Send the issue report to our support team and get help.",
								"user-registration"
							)}
						</SupportFootnote>
					</>
				}
			/>
		);
	}

	const suggestions = remediableChecks(checks);

	// The failed outcome used to be a banner plus a stack of bordered cards, each
	// making its own separate-looking demand. It is one panel now, reading as two
	// routes out and a way to ask for help: fix these, or let SmartSMTP do it.
	return (
		<ResultFrame
			tone="red"
			title={__("The test email didn't arrive", "user-registration")}
			subtitle={
				sendError
					? __(
							"Your site couldn't send it at all — the attempt failed before the message left the server.",
							"user-registration"
						)
					: suggestions.length > 0
						? __(
								"Your site sent it without reporting an error, so it was lost or refused after it left.",
								"user-registration"
							)
						: <RichText text={nonDeliveryExplanation(checks, smtpPlugin)} />
			}
			onRunAgain={onRunAgain}
			onOpenReport={onOpenReport}
			body={
				<>
					{/* The recommendation leads. Most admins hitting this screen want the
					    problem gone, not a reading list — so the one-click route comes
					    first and the findings are the alternative for anyone who'd rather
					    fix their own setup. */}
					<Box mt="12px">
						<SmartSmtpRecommendation status={smartSmtpStatus} />
					</Box>

					{suggestions.length > 0 && (
						<>
							<OrDivider />
							{/* No "Or" here — the divider immediately above already said it. */}
							<Text fontSize="12.5px" fontWeight="700" color="gray.700">
								{__("Fix what the scan found:", "user-registration")}
							</Text>
							<SuggestedFixes suggestions={suggestions} />
						</>
					)}

					{/* The generic "check the log" text the server returns says only what
					    this link already says, so it is dropped — but a mailer that
					    reports a real error is worth quoting verbatim. */}
					{sendError && !sendError.includes("ur_mail_logs") && (
						<Text fontSize="12.5px" color="gray.600" mt="10px" lineHeight="1.55">
							{__("Your mail server reported:", "user-registration")}{" "}
							<Text as="code" fontFamily="mono" fontSize="0.92em">
								{sendError}
							</Text>
						</Text>
					)}

					{smtpPlugin && !smtpPlugin.is_smartsmtp && (
						<Text fontSize="12px" color="gray.500" mt="10px" lineHeight="1.55">
							{sprintf(
								/* translators: %s: SMTP plugin name */
								__(
									"Already using %s? Its own send log usually names the exact rejection reason.",
									"user-registration"
								),
								smtpPlugin.name
							)}{" "}
							<Link href="plugins.php" color="primary.600" fontWeight="600" textDecoration="underline">
								{__("Installed Plugins", "user-registration")}
							</Link>
						</Text>
					)}

					<SupportFootnote>
						{__(
							"Still stuck? Send the issue report to our support team and get help.",
							"user-registration"
						)}
					</SupportFootnote>
				</>
			}
		/>
	);
};

export default ResultStep;
