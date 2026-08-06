import { Box, Button, Flex, useToast } from "@chakra-ui/react";
import { __, sprintf } from "@wordpress/i18n";
import { ReactNode, useCallback, useEffect, useRef, useState } from "react";
import { FiAlertTriangle, FiCheck, FiExternalLink, FiSlash, FiTool } from "react-icons/fi";
import {
	activateSmtpPlugin,
	CheckAction,
	CheckStatus,
	HealthCheck,
	installSmartSmtp,
	runScan,
	SmartSmtpStatus,
	SmtpPluginInfo,
} from "../../api/healthCheckupApi";
import RichText from "../RichText";
import Text from "../Text";

interface ScanStepProps {
	onNext: (checks: HealthCheck[], smartSmtpStatus: SmartSmtpStatus, smtpPlugin: SmtpPluginInfo | null) => void;
	onOpenReport: (checks: HealthCheck[]) => void;
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

// "blocked" is deliberately neutral, not red — the setting is fine, so it must
// not read as one more thing to go and fix.
const rowStyles: Record<CheckStatus, {
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
}> = {
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
	issue: {
		icon: <FiAlertTriangle size={18} />,
		badgeLabel: __("Issue", "user-registration"),
		borderColor: "red.200",
		bg: "red.50",
		iconColor: "red.600",
		titleColor: "red.600",
		messageColor: "red.600",
		badgeColor: "red.600",
		badgeBg: "white",
		badgeBorderColor: "red.200",
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
	const isIssue = check.status === "issue";
	const style = rowStyles[check.status] ?? rowStyles.pass;

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
			animation={`rowIn 360ms ease ${index * 70}ms forwards`}
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
				{isIssue && check.fix && (
					<Text fontSize="12.5px" color="red.600" mt="6px" lineHeight="1.55" fontWeight="600">
						<RichText text={check.fix} />
					</Text>
				)}
				{isIssue && check.action && (
					<Box>
						<CheckActionLink action={check.action} onResolved={onResolved} />
					</Box>
				)}
			</Box>
		</Flex>
	);
};

const ScanStep = ({ onNext, onOpenReport }: ScanStepProps) => {
	const [isLoading, setIsLoading] = useState(true);
	const [barWidth, setBarWidth] = useState("3%");
	const [checks, setChecks] = useState<HealthCheck[]>([]);
	const [smartSmtpStatus, setSmartSmtpStatus] = useState<SmartSmtpStatus>("not_installed");
	const [smtpPlugin, setSmtpPlugin] = useState<SmtpPluginInfo | null>(null);
	const [error, setError] = useState("");
	const hasStarted = useRef(false);

	// Re-read the checks after an inline fix so the row reflects the new state.
	const rescan = useCallback(() => {
		setError("");

		return runScan()
			.then((result) => {
				setChecks(result.checks);
				setSmartSmtpStatus(result.smartsmtp_status);
				setSmtpPlugin(result.smtp_plugin);
			})
			.catch((err: Error) => setError(err.message));
	}, []);

	useEffect(() => {
		if (hasStarted.current) {
			return;
		}
		hasStarted.current = true;

		requestAnimationFrame(() => requestAnimationFrame(() => setBarWidth("100%")));

		runScan()
			.then((result) => {
				setChecks(result.checks);
				setSmartSmtpStatus(result.smartsmtp_status);
				setSmtpPlugin(result.smtp_plugin);
				setIsLoading(false);
			})
			.catch((err: Error) => {
				setError(err.message);
				setIsLoading(false);
			});
	}, []);

	const issueCount = checks.filter((check) => check.status === "issue").length;

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
					? __("Checking your settings…", "user-registration")
					: __("Here's what we found", "user-registration")}
			</Text>
			<Text fontSize="14px" lineHeight="1.62" color="gray.600" mb="22px">
				{isLoading
					? __("Reading your current configuration — this only takes a moment.", "user-registration")
					: checks.length > 0
						? sprintf(
							/* translators: %d: number of checks run */
							__("%d checks run against your email settings.", "user-registration"),
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
					<Flex direction="column" gap="8px" mb="4px">
						{checks.map((check, index) => (
							<CheckRow key={check.key} check={check} index={index} onResolved={rescan} />
						))}
					</Flex>

					<Flex
						align="center"
						gap="13px"
						border="1px solid"
						borderColor={issueCount === 0 ? "green.200" : "orange.200"}
						bg={issueCount === 0 ? "green.50" : "orange.50"}
						borderRadius="8px"
						p="13px 15px"
						mt="14px"
					>
						<Flex
							flexShrink={0}
							w="30px"
							h="30px"
							borderRadius="8px"
							bg="white"
							align="center"
							justify="center"
							color={issueCount === 0 ? "green.600" : "orange.700"}
						>
							{issueCount === 0 ? <FiCheck size={16} /> : <FiTool size={16} />}
						</Flex>
						<Box fontSize="12.5px" color="gray.600" lineHeight="1.5">
							<Text as="span" display="block" fontSize="13.5px" fontWeight="700" color="inherit" mb="1px">
								{issueCount === 0
									? __("All clear", "user-registration")
									: `${issueCount} ${issueCount === 1 ? __("issue", "user-registration") : __("issues", "user-registration")} ${__("found", "user-registration")}`}
							</Text>
							{issueCount === 0
								? __("Nothing to fix here — continue to the live delivery test.", "user-registration")
								: __("Worth fixing first, then we'll test real delivery.", "user-registration")}
						</Box>
					</Flex>

					<Flex gap="10px" mt="20px" wrap="wrap" justify="flex-end">
						<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={() => onOpenReport(checks)}>
							{__("Send report to support", "user-registration")}
						</Button>
						<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={() => onNext(checks, smartSmtpStatus, smtpPlugin)}>
							{__("Next: test delivery", "user-registration")}
						</Button>
					</Flex>
				</>
			)}
		</Box>
	);
};

export default ScanStep;
