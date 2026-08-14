import { Box, Button, Flex, useToast } from "@chakra-ui/react";
import { __, sprintf } from "@wordpress/i18n";
import { ReactNode, useState } from "react";
import { FiAlertTriangle, FiArrowRight, FiCheck, FiX } from "react-icons/fi";
import { HealthCheck, installSmartSmtp, SmartSmtpStatus, SmtpPluginInfo } from "../../api/healthCheckupApi";
import IssueList from "../IssueList";
import { COLOR, TYPE } from "../../tokens";
import RichText from "../RichText";
import StepHeader from "../StepHeader";
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
	"from_effective_unknown",
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
			"SmartSMTP is active and configured, but the test still didn't arrive. Its primary connection may need reconnecting.",
			"user-registration"
		);
	}

	if (smtpPlugin) {
		return sprintf(
			/* translators: %s: SMTP plugin name, e.g. "FluentSMTP" */
			__(
				"Your site sends through `%s`, but the test didn't arrive. The likeliest causes are that plugin's connection settings (host, port, or API key), or the receiving server rejecting the message.",
				"user-registration"
			),
			smtpPlugin.name
		);
	}

	return __(
		"An SMTP connection is configured, but the test didn't arrive. Check whichever service handles your outgoing mail for delivery errors.",
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

	// One claim, then the state-specific half. The claim is identical across all
	// three states and carries the weight — this screen exists to recommend
	// SmartSMTP, and a recommendation that opens by describing plugin status
	// reads as a status report. The clause after it never offers to install
	// something already sitting on the site.
	const copy = {
		not_installed: {
			detail: __(
				"Our own free plugin takes over sending, so your email actually arrives.",
				"user-registration"
			),
			action: __("Install & activate SmartSMTP", "user-registration"),
			loading: __("Installing…", "user-registration"),
		},
		inactive: {
			detail: __(
				"It's already installed here, so activate it and it takes over sending.",
				"user-registration"
			),
			action: __("Activate SmartSMTP", "user-registration"),
			loading: __("Activating…", "user-registration"),
		},
		active: {
			detail: justActivated
				? __(
						"One step left: connect it to a mail service and it handles delivery from here on.",
						"user-registration"
					)
				: __(
						"It's active but never finished connecting, which is why nothing is going out.",
						"user-registration"
					),
			action: __("Set up SmartSMTP", "user-registration"),
			loading: __("Opening…", "user-registration"),
		},
	}[localStatus];

	return (
		<Box>
			<Text fontSize={TYPE.subheading} fontWeight="600" color={COLOR.title} lineHeight="1.5">
				{__("SmartSMTP is the most reliable fix", "user-registration")}
			</Text>
			<Text fontSize={TYPE.body} color={COLOR.body} lineHeight="1.62" mt="5px">
				{copy.detail}
			</Text>
			<Button
				variant="link"
				colorScheme="primary"
				fontSize={TYPE.body}
				fontWeight="500"
				mt="10px"
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

/**
 * Findings the live test has already answered.
 *
 * "The sender can't be confirmed from here" is a statement about the limits of
 * the scan, not about the site — it means the mailer addresses messages
 * somewhere we can't read. Once a test email has actually arrived, that
 * uncertainty is settled, and listing it under a delivered result sends the
 * admin to go and check a sender that demonstrably worked.
 */
const SUPERSEDED_BY_DELIVERY = ["from_effective_unknown"];

/** Open findings worth showing once the message did arrive, inbox or spam. */
const issuesWorthShowingAfterDelivery = (checks: HealthCheck[]) =>
	checks.filter(
		(check) =>
			(check.status === "error" || check.status === "warning") &&
			!SUPERSEDED_BY_DELIVERY.includes(check.key)
	);

/**
 * Failing checks that actually have a remedy to offer — either a sentence
 * describing one, or a link straight to the screen that owns it.
 *
 * The settings checks carry only the link: their message already says what is
 * wrong, so a remedy sentence would restate it. Filtering on `fix` alone would
 * drop them from this list entirely.
 */
const remediableChecks = (checks: HealthCheck[]) =>
	checks.filter(
		(check) => (check.status === "error" || check.status === "warning") && (check.fix || check.action)
	);

/** A labelled paragraph, for a result that has more than one thing to say. */
const Advice = ({ label, children }: { label: string; children: ReactNode }) => (
	<Box mt="15px">
		<Text fontSize={TYPE.body} fontWeight="600" color={COLOR.title}>
			{label}
		</Text>
		<Text fontSize={TYPE.body} color={COLOR.body} mt="4px" lineHeight="1.62">
			{children}
		</Text>
	</Box>
);

/** The last resort, set apart from the advice above it. */
const SupportFootnote = ({ children }: { children: ReactNode }) => (
	<Text
		fontSize={TYPE.small}
		color={COLOR.muted}
		mt="18px"
		pt="15px"
		borderTop="1px solid"
		borderColor="gray.200"
		lineHeight="1.55"
	>
		{children}
	</Text>
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
		{/* No panel of its own. This already sits inside the wizard's card, so a
		    second border around the same content just drew a box inside a box —
		    and a tinted one turned a whole screen of remedy into an alarm. The
		    outcome is carried by the icon and the headline instead. */}
		<StepHeader
			title={title}
			description={subtitle}
			icon={
				/* Tinted disc rather than filled — white on any of these three hues
				   is too low-contrast to be safe, and the soft circle reads as a
				   status marker instead of a button. */
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
			}
		/>
		{bannerFooter}
		{body}

		{/* The run ends here, so it needs a way to say so. Until now the only exit
		    was the × in the corner, which reads as "abandon" rather than "done" —
		    and left the two secondary actions carrying the whole footer with
		    sentence-length labels. Finish takes the primary slot; the other two are
		    trimmed to fit beside it. */}
		<Flex gap="10px" mt="24px" wrap="wrap" justify="flex-end">
			<Button variant="outline" fontSize="sm" fontWeight="500" onClick={onOpenReport}>
				{__("Send report", "user-registration")}
			</Button>
			<Button variant="outline" fontSize="sm" fontWeight="500" onClick={onRunAgain}>
				{__("Run again", "user-registration")}
			</Button>
			<Button
				as="a"
				href={window._UR_EMAIL_HEALTH_.exitUrl}
				bg={COLOR.link}
				color="white"
				_hover={{ bg: "#38488e" }}
				_active={{ bg: COLOR.link }}
				fontSize={{ base: "sm", md: "md" }}
				fontWeight="500"
				px={{ base: 2, md: 4 }}
				py={2}
			>
				{__("Finish", "user-registration")}
			</Button>
		</Flex>
	</Box>
);

const ResultStep = ({ variant, checks, onRunAgain, onOpenReport, smartSmtpStatus, smtpPlugin, sendError }: ResultStepProps) => {
	const openIssues = issuesWorthShowingAfterDelivery(checks);

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
								"Delivery works from this site and the message reached the inbox. Nothing below stopped it arriving, but a few settings are still worth a look.",
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
				title={__("It arrived, but landed in spam", "user-registration")}
				subtitle={__(
					"Your site sent it fine. The receiving mailbox is what put it in spam.",
					"user-registration"
				)}
				onRunAgain={onRunAgain}
				onOpenReport={onOpenReport}
				body={
					<>
						{/* Kept as a labelled block rather than folded into the subtitle:
						    the label is the reassurance — it separates "this one landed
						    in spam" from "this happens every time", which are different
						    problems and only the second is worth acting on. */}
						<Advice label={__("If it keeps happening", "user-registration")}>
							{__(
								"One email in spam is normal. Domains that have only recently started sending get filtered more until they build up a history.",
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
							"Your site couldn't send it at all. The attempt failed before the message left the server.",
							"user-registration"
						)
					: suggestions.length > 0
						? __(
								"Your site reported no error, so the message was dropped or rejected after it left.",
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

					{/* The "Or" is a word in this toggle rather than a ruled divider and
					    a bordered panel. Those out-weighed the recommendation they were
					    meant to defer to, and stacked three sets of lines into a screen
					    that is otherwise borderless. */}
					{suggestions.length > 0 && (
						<IssueList
							issues={suggestions}
							label={__("Or fix what the scan found", "user-registration")}
							defaultOpen={false}
						/>
					)}

					{/* The generic "check the log" text the server returns says only what
					    this link already says, so it is dropped — but a mailer that
					    reports a real error is worth quoting verbatim. */}
					{sendError && !sendError.includes("ur_mail_logs") && (
						<Text fontSize={TYPE.body} color={COLOR.body} mt="10px" lineHeight="1.6">
							{__("Your mail server reported:", "user-registration")}{" "}
							<Text as="code" fontFamily="mono" fontSize="0.92em">
								{sendError}
							</Text>
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
