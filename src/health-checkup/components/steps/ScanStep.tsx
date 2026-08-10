import { Box, Button, Flex, useToast } from "@chakra-ui/react";
import { __, sprintf } from "@wordpress/i18n";
import { ReactNode, useCallback, useEffect, useRef, useState } from "react";
import {
	FiAlertCircle,
	FiAlertTriangle,
	FiCheck,
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
	runScan,
	SmartSmtpStatus,
	SmtpPluginInfo,
	ScanSummary,
} from "../../api/healthCheckupApi";
import RichText from "../RichText";
import Text from "../Text";

interface ScanStepProps {
	onNext: (
		checks: HealthCheck[],
		smartSmtpStatus: SmartSmtpStatus,
		smtpPlugin: SmtpPluginInfo | null,
		summary: ScanSummary | null,
		sections: CheckSection[]
	) => void;
	onOpenReport: (checks: HealthCheck[], summary: ScanSummary | null, sections: CheckSection[]) => void;
}

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

// Red is reserved for "this will fail". A setting that's merely switched off,
// or something we simply couldn't verify, must not look like a broken site.
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
		titleColor: "red.600",
		messageColor: "red.600",
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
		titleColor: "orange.700",
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
	onResolved,
}: {
	check: HealthCheck;
	index: number;
	onResolved: () => void;
}) => {
	const style = rowStyles[check.status] ?? rowStyles.pass;
	const isActionable = check.status === "error" || check.status === "warning";

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
				<Flex align="center" justify="space-between" gap="10px">
					<Text fontSize="13.5px" fontWeight="600" color={style.titleColor}>
						{check.title}
					</Text>
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
				</Flex>
				<Text fontSize="12.5px" color={style.messageColor} mt="3px" lineHeight="1.55">
					<RichText text={check.message} />
				</Text>
				{isActionable && check.fix && (
					<Text fontSize="12.5px" color={style.titleColor} mt="6px" lineHeight="1.55">
						<RichText text={check.fix} />
					</Text>
				)}
				{isActionable && check.action && (
					<Box>
						<CheckActionLink action={check.action} onResolved={onResolved} />
					</Box>
				)}
			</Box>
		</Flex>
	);
};

const SUMMARY_TONE: Record<ScanSummary["level"], { tone: string; icon: ReactNode; iconColor: string }> = {
	pass: { tone: "green", icon: <FiCheck size={17} />, iconColor: "green.600" },
	warning: { tone: "orange", icon: <FiAlertCircle size={17} />, iconColor: "orange.700" },
	error: { tone: "red", icon: <FiAlertTriangle size={17} />, iconColor: "red.600" },
};

// The headline. Every row below exists to justify this one sentence, so it goes
// above them rather than being summarised at the bottom.
const SummaryBanner = ({ summary }: { summary: ScanSummary }) => {
	const { tone, icon, iconColor } = SUMMARY_TONE[summary.level] ?? SUMMARY_TONE.warning;

	return (
		<Flex
			bg={`${tone}.50`}
			border="1px solid"
			borderColor={`${tone}.200`}
			borderRadius="9px"
			p="15px 16px"
			gap="13px"
			mb="24px"
		>
			<Flex
				flexShrink={0}
				w="32px"
				h="32px"
				borderRadius="8px"
				bg="white"
				align="center"
				justify="center"
				color={iconColor}
			>
				{icon}
			</Flex>
			<Box>
				<Text as="h3" fontSize="15px" fontWeight="700" letterSpacing="-0.01em" color="gray.800">
					{summary.title}
				</Text>
				<Text fontSize="12.5px" color="gray.600" mt="3px" lineHeight="1.6">
					<RichText text={summary.message} />
				</Text>
			</Box>
		</Flex>
	);
};

const SectionBlock = ({
	section,
	startIndex,
	onResolved,
}: {
	section: CheckSection;
	startIndex: number;
	onResolved: () => void;
}) => (
	<Box mb="26px">
		<Text fontSize="13.5px" fontWeight="700" color="gray.800" mb="2px">
			{section.title}
		</Text>
		<Text fontSize="12.5px" color="gray.500" mb="12px" lineHeight="1.55">
			{section.description}
		</Text>
		<Flex direction="column" gap="8px">
			{section.checks.map((check, index) => (
				<CheckRow key={check.key} check={check} index={startIndex + index} onResolved={onResolved} />
			))}
		</Flex>
	</Box>
);

const ScanStep = ({ onNext, onOpenReport }: ScanStepProps) => {
	const [isLoading, setIsLoading] = useState(true);
	const [barWidth, setBarWidth] = useState("3%");
	const [sections, setSections] = useState<CheckSection[]>([]);
	const [summary, setSummary] = useState<ScanSummary | null>(null);
	const [checks, setChecks] = useState<HealthCheck[]>([]);
	const [smartSmtpStatus, setSmartSmtpStatus] = useState<SmartSmtpStatus>("not_installed");
	const [smtpPlugin, setSmtpPlugin] = useState<SmtpPluginInfo | null>(null);
	const [error, setError] = useState("");
	const hasStarted = useRef(false);

	const applyResult = useCallback((result: Awaited<ReturnType<typeof runScan>>) => {
		setSections(result.sections);
		setSummary(result.summary);
		setChecks(result.checks);
		setSmartSmtpStatus(result.smartsmtp_status);
		setSmtpPlugin(result.smtp_plugin);
	}, []);

	// Re-read the checks after an inline fix so the row reflects the new state.
	const rescan = useCallback(() => {
		setError("");

		return runScan()
			.then(applyResult)
			.catch((err: Error) => setError(err.message));
	}, [applyResult]);

	useEffect(() => {
		if (hasStarted.current) {
			return;
		}
		hasStarted.current = true;

		requestAnimationFrame(() => requestAnimationFrame(() => setBarWidth("100%")));

		runScan()
			.then((result) => {
				applyResult(result);
				setIsLoading(false);
			})
			.catch((err: Error) => {
				setError(err.message);
				setIsLoading(false);
			});
	}, [applyResult]);

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
				{__("Step 1 · Auto-scan", "user-registration")}
			</Text>
			<Text as="h2" fontSize="21px" fontWeight="600" mb="10px" letterSpacing="-0.01em" color="gray.800">
				{isLoading
					? __("Checking your setup…", "user-registration")
					: __("Here's what we found", "user-registration")}
			</Text>
			<Text fontSize="14px" lineHeight="1.62" color="gray.600" mb="22px">
				{isLoading
					? __("Reading your mail configuration and checking what your domain publishes.", "user-registration")
					: checks.length > 0
						? sprintf(
							/* translators: %d: number of checks run */
							__("%d checks run against your mail setup.", "user-registration"),
							checks.length
						)
						: ""}
			</Text>

			{isLoading && (
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

			{error && (
				<Text color="red.600" fontSize="13.5px" mb="16px">
					{error}
				</Text>
			)}

			{!isLoading && !error && (
				<>
					{summary && <SummaryBanner summary={summary} />}

					{sections.map((section, sectionIndex) => (
						<SectionBlock
							key={section.key}
							section={section}
							startIndex={sectionIndex === 0 ? 0 : sections[0].checks.length}
							onResolved={rescan}
						/>
					))}

					<Flex gap="10px" mt="4px" wrap="wrap" justify="flex-end">
						<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={() => onOpenReport(checks, summary, sections)}>
							{__("Send report to support", "user-registration")}
						</Button>
						<Button
							colorScheme="primary"
							fontSize="13.5px"
							fontWeight="600"
							onClick={() => onNext(checks, smartSmtpStatus, smtpPlugin, summary, sections)}
						>
							{__("Next: test delivery", "user-registration")}
						</Button>
					</Flex>
				</>
			)}
		</Box>
	);
};

export default ScanStep;
