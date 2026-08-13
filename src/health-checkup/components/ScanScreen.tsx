import { Box, Button, Collapse, Flex, useDisclosure, useToast } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { ReactNode, useState } from "react";
import {
	FiAlertCircle,
	FiAlertTriangle,
	FiCheck,
	FiChevronDown,
	FiChevronLeft,
	FiExternalLink,
	FiHelpCircle,
	FiSlash,
} from "react-icons/fi";
import {
	activateSmtpPlugin,
	CheckAction,
	CheckSection,
	CheckStatus,
	HealthCheck,
	installSmartSmtp,
} from "../api/healthCheckupApi";
import RichText from "./RichText";
import Text from "./Text";

/**
 * Split a finding's message after its first sentence, so a warning can lead with
 * its gist and keep the reasoning behind a disclosure.
 *
 * The naive split on ". " is wrong here: these messages are full of dotted
 * values — `themegrill.com`, `mail()`, `v=spf1 -all` — so only a full stop
 * outside a `code` span counts, and only when what follows starts a new
 * sentence rather than continuing an abbreviation.
 */
export const splitFirstSentence = (text: string): { lead: string; rest: string } => {
	let inCode = false;

	for (let index = 0; index < text.length; index++) {
		const character = text[index];

		if ("`" === character) {
			inCode = !inCode;
			continue;
		}

		if (inCode || !".!?".includes(character)) {
			continue;
		}

		const after = text.slice(index + 1);
		const following = after.match(/^\s+(\S)/);

		if (!following) {
			continue;
		}

		// A lowercase letter next means the stop was part of a value, not the end
		// of a thought. Backticks, `**` and digits have no case, so they pass.
		const next = following[1];

		if (next !== next.toUpperCase()) {
			continue;
		}

		return { lead: text.slice(0, index + 1), rest: after.trim() };
	}

	return { lead: text, rest: "" };
};

// Resolves a failing check in place — a single inline link, deliberately not a
// card, so the fix sits with the finding it belongs to. On success the scan is
// re-run so the row itself flips to Pass.
const CheckActionLink = ({ action, onResolved }: { action: CheckAction; onResolved: () => void }) => {
	const [isWorking, setIsWorking] = useState(false);
	const toast = useToast();

	if (action.type === "link") {
		return (
			<Button
				as="a"
				href={action.url}
				target="_blank"
				rel="noreferrer noopener"
				variant="link"
				colorScheme="primary"
				fontSize="12.5px"
				fontWeight="700"
				mt="6px"
				rightIcon={<FiExternalLink size={12} />}
			>
				{action.label}
			</Button>
		);
	}

	const handleClick = async () => {
		setIsWorking(true);
		try {
			if (action.type === "activate") {
				await activateSmtpPlugin(action.plugin ?? "");
			} else {
				await installSmartSmtp();
			}
			onResolved();
		} catch (error) {
			toast({
				title:
					action.type === "activate"
						? __("Couldn't activate the plugin", "user-registration")
						: __("Couldn't set up SmartSMTP", "user-registration"),
				description: error instanceof Error ? error.message : undefined,
				status: "error",
				duration: 6000,
				isClosable: true,
			});
			setIsWorking(false);
		}
	};

	return (
		<Button
			variant="link"
			colorScheme="primary"
			fontSize="12.5px"
			fontWeight="700"
			mt="6px"
			onClick={handleClick}
			isLoading={isWorking}
			loadingText={
				action.type === "activate"
					? __("Activating…", "user-registration")
					: __("Installing…", "user-registration")
			}
		>
			{action.label}
		</Button>
	);
};

interface RowStyle {
	icon: ReactNode;
	badgeLabel: string;
	borderColor: string;
	bg: string;
	iconColor: string;
	titleColor: string;
	messageColor: string;
	badgeColor: string;
	badgeBg: string;
	badgeBorderColor: string;
}

// Severity is carried by the icon, the badge and the background tint — never by
// the text colour. Coloured body copy on a coloured panel is harder to read, and
// it made a warning's orange title look louder than an error's red one. Titles
// stay dark, matching the summary banner above the list.
const rowStyles: Record<CheckStatus, RowStyle> = {
	pass: {
		icon: <FiCheck size={18} />,
		badgeLabel: __("Pass", "user-registration"),
		borderColor: "gray.200",
		bg: "white",
		iconColor: "green.600",
		titleColor: "inherit",
		messageColor: "gray.600",
		badgeColor: "green.700",
		badgeBg: "green.50",
		badgeBorderColor: "green.200",
	},
	error: {
		icon: <FiAlertTriangle size={18} />,
		badgeLabel: __("Will fail", "user-registration"),
		borderColor: "red.200",
		bg: "red.50",
		iconColor: "red.600",
		titleColor: "gray.800",
		messageColor: "gray.600",
		badgeColor: "red.600",
		badgeBg: "white",
		badgeBorderColor: "red.200",
	},
	warning: {
		icon: <FiAlertCircle size={18} />,
		badgeLabel: __("May fail", "user-registration"),
		borderColor: "orange.200",
		bg: "orange.50",
		iconColor: "orange.700",
		titleColor: "gray.800",
		messageColor: "gray.600",
		badgeColor: "orange.700",
		badgeBg: "white",
		badgeBorderColor: "orange.200",
	},
	unknown: {
		icon: <FiHelpCircle size={18} />,
		badgeLabel: __("Can't tell", "user-registration"),
		borderColor: "gray.200",
		bg: "gray.50",
		iconColor: "gray.500",
		titleColor: "gray.700",
		messageColor: "gray.600",
		badgeColor: "gray.600",
		badgeBg: "white",
		badgeBorderColor: "gray.300",
	},
	blocked: {
		icon: <FiSlash size={18} />,
		badgeLabel: __("Won't send", "user-registration"),
		borderColor: "gray.200",
		bg: "gray.50",
		iconColor: "gray.500",
		titleColor: "gray.700",
		messageColor: "gray.600",
		badgeColor: "gray.600",
		badgeBg: "white",
		badgeBorderColor: "gray.300",
	},
};

const CheckRow = ({
	check,
	index,
	showFix,
	onResolved,
}: {
	check: HealthCheck;
	index: number;
	showFix: boolean;
	onResolved: () => void;
}) => {
	const style = rowStyles[check.status] ?? rowStyles.pass;
	const isActionable = check.status === "error" || check.status === "warning";
	const { lead, rest } = splitFirstSentence(check.message);
	const { isOpen, onToggle } = useDisclosure();

	// Only "may fail" folds, and only when there is something to fold. An error is
	// something the admin has to act on, so its reasoning stays on screen; a pass
	// has nothing to justify; and a one-sentence warning is already short enough
	// that hiding its fix behind an arrow would only add a click.
	const isCollapsible = "warning" === check.status && "" !== rest;

	const badge = (
		<Text
			fontSize="10.5px"
			fontWeight="700"
			letterSpacing="0.03em"
			textTransform="uppercase"
			px="7px"
			py="2px"
			borderRadius="100px"
			flexShrink={0}
			color={style.badgeColor}
			bg={style.badgeBg}
			border="1px solid"
			borderColor={style.badgeBorderColor}
		>
			{style.badgeLabel}
		</Text>
	);

	const fixAndAction = (
		<>
			{showFix && check.fix && (
				<Text fontSize="12.5px" color={style.titleColor} mt="6px" lineHeight="1.55">
					<RichText text={check.fix} />
				</Text>
			)}
			{check.action && (
				<Box>
					<CheckActionLink action={check.action} onResolved={onResolved} />
				</Box>
			)}
		</>
	);

	return (
		<Flex
			border="1px solid"
			borderColor={style.borderColor}
			bg={style.bg}
			borderRadius="8px"
			p="13px 14px"
			gap="11px"
			align="flex-start"
			opacity={0}
			animation={`rowIn 360ms ease ${index * 55}ms forwards`}
			sx={{
				"@keyframes rowIn": {
					to: { opacity: 1, transform: "none" },
				},
			}}
		>
			<Box flexShrink={0} color={style.iconColor} mt="1px">
				{style.icon}
			</Box>
			<Box flex="1" minW="0">
				<Flex
					align="center"
					justify="space-between"
					gap="10px"
					{...(isCollapsible
						? {
								as: "button",
								type: "button",
								onClick: onToggle,
								"aria-expanded": isOpen,
								width: "100%",
								textAlign: "left",
								cursor: "pointer",
							}
						: {})}
				>
					<Text fontSize="13.5px" fontWeight="600" color={style.titleColor}>
						{check.title}
					</Text>
					<Flex align="center" gap="7px" flexShrink={0}>
						{badge}
						{isCollapsible && (
							<Box
								display="flex"
								color={style.badgeColor}
								transform={isOpen ? "rotate(180deg)" : undefined}
								transition="transform 180ms ease"
							>
								<FiChevronDown size={15} />
							</Box>
						)}
					</Flex>
				</Flex>

				<Text fontSize="12.5px" color={style.messageColor} mt="3px" lineHeight="1.55">
					<RichText text={isCollapsible ? lead : check.message} />
				</Text>

				{isCollapsible ? (
					<Collapse in={isOpen} animateOpacity>
						<Text fontSize="12.5px" color={style.messageColor} mt="6px" lineHeight="1.55">
							<RichText text={rest} />
						</Text>
						{fixAndAction}
					</Collapse>
				) : (
					isActionable && fixAndAction
				)}
			</Box>
		</Flex>
	);
};

export interface ScanScreenProps {
	/** e.g. "Step 2 · Plugin settings". */
	stepLabel: string;
	loadingHeading: string;
	heading: string;
	loadingBlurb: string;
	/** The one section this step presents, or null while the scan is in flight. */
	section: CheckSection | null;
	/**
	 * Whether a failing row carries its remedy inline. Plugin settings do — they
	 * are the admin's own switches, a click away. Mail delivery doesn't: those
	 * remedies are recommendations ("connect an SMTP service", "send from another
	 * domain"), and they are only worth making once the live test has shown the
	 * mail really didn't arrive, so the result screen makes them instead.
	 */
	showFixes?: boolean;
	isLoading: boolean;
	error: string;
	nextLabel: string;
	onNext: () => void;
	onBack?: () => void;
	/**
	 * Omitted where a report wouldn't help yet: the settings findings alone are
	 * not what support needs, and offering the button before the delivery checks
	 * have even been seen invites a half-empty ticket.
	 */
	onOpenReport?: () => void;
	onResolved: () => void;
}

/**
 * The frame both scan steps render in: one section's findings, the same header,
 * progress bar and footer. Splitting the scan across two steps was a change of
 * pagination, not of presentation, so the two screens share this rather than
 * each growing their own copy of it.
 *
 * No verdict banner. A headline asserting whether mail will arrive sat above
 * findings that often couldn't support it — and on an opaque transport it said
 * so in as many words, which is not a headline. The verdict still leads the
 * support report, where a hedge is useful rather than alarming.
 */
const ScanScreen = ({
	stepLabel,
	loadingHeading,
	heading,
	loadingBlurb,
	section,
	showFixes = true,
	isLoading,
	error,
	nextLabel,
	onNext,
	onBack,
	onOpenReport,
	onResolved,
}: ScanScreenProps) => (
	<Box>
		<Text
			fontSize="11.5px"
			fontWeight="700"
			letterSpacing="0.06em"
			textTransform="uppercase"
			color="primary.500"
			mb="9px"
		>
			{stepLabel}
		</Text>
		<Text as="h2" fontSize="21px" fontWeight="600" mb="10px" letterSpacing="-0.01em" color="gray.800">
			{isLoading ? loadingHeading : heading}
		</Text>
		<Text fontSize="14px" lineHeight="1.62" color="gray.600" mb="22px">
			{/* The section's own description is written server-side and already says
			    what this group covers, so the step doesn't restate it. */}
			{isLoading ? loadingBlurb : (section?.description ?? "")}
		</Text>

		{isLoading && (
			<Box pb="26px">
				<Box height="6px" borderRadius="6px" bg="gray.200" overflow="hidden">
					<Box
						height="100%"
						width="100%"
						bg="primary.500"
						borderRadius="6px"
						sx={{
							"@keyframes barIn": { from: { width: "3%" }, to: { width: "100%" } },
						}}
						animation="barIn 950ms cubic-bezier(.4,0,.2,1)"
					/>
				</Box>
			</Box>
		)}

		{error && (
			<Text color="red.600" fontSize="13.5px" mb="16px">
				{error}
			</Text>
		)}

		{!isLoading && !error && (
			<>
				<Flex direction="column" gap="8px" mb="26px">
					{(section?.checks ?? []).map((check, index) => (
						<CheckRow
							key={check.key}
							check={check}
							index={index}
							showFix={showFixes}
							onResolved={onResolved}
						/>
					))}
				</Flex>

				<Flex
					gap="10px"
					mt="4px"
					wrap="wrap"
					align="center"
					justify={onBack ? "space-between" : "flex-end"}
				>
					{onBack && (
						<Button
							variant="link"
							colorScheme="primary"
							fontSize="13.5px"
							fontWeight="600"
							onClick={onBack}
							// Chakra's leftIcon wrapper carries no aria-hidden, which leaves
							// assistive tech computing the name from the icon as well as the
							// label. Naming the button outright sidesteps that.
							aria-label={__("Back", "user-registration")}
							leftIcon={<FiChevronLeft size={14} />}
						>
							{__("Back", "user-registration")}
						</Button>
					)}
					<Flex gap="10px" wrap="wrap" justify="flex-end">
						{onOpenReport && (
							<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={onOpenReport}>
								{__("Send report to support", "user-registration")}
							</Button>
						)}
						<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={onNext}>
							{nextLabel}
						</Button>
					</Flex>
				</Flex>
			</>
		)}
	</Box>
);

export default ScanScreen;
