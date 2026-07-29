import { Box, Button, Flex } from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { useEffect, useRef, useState } from "react";
import { FiAlertTriangle, FiCheck, FiTool } from "react-icons/fi";
import { HealthCheck, runScan, SmartSmtpStatus } from "../../api/healthCheckupApi";
import RichText from "../RichText";
import Text from "../Text";

interface ScanStepProps {
	onNext: (checks: HealthCheck[], smartSmtpStatus: SmartSmtpStatus) => void;
	onOpenReport: (checks: HealthCheck[]) => void;
}

const CheckRow = ({ check, index }: { check: HealthCheck; index: number }) => {
	const isIssue = check.status === "issue";

	return (
		<Flex
			border="1px solid"
			borderColor={isIssue ? "red.200" : "gray.200"}
			bg={isIssue ? "red.50" : "white"}
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
			<Box flexShrink={0} color={isIssue ? "red.600" : "green.600"} mt="1px">
				{isIssue ? <FiAlertTriangle size={18} /> : <FiCheck size={18} />}
			</Box>
			<Box flex="1" minW="0">
				<Flex align="center" justify="space-between" gap="10px">
					<Text fontSize="13.5px" fontWeight="600" color={isIssue ? "red.600" : "inherit"}>
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
						color={isIssue ? "red.600" : "green.700"}
						bg={isIssue ? "white" : "green.50"}
						border="1px solid"
						borderColor={isIssue ? "red.200" : "green.200"}
					>
						{isIssue ? __("Issue", "user-registration") : __("Pass", "user-registration")}
					</Text>
				</Flex>
				<Text fontSize="12.5px" color={isIssue ? "red.600" : "gray.600"} mt="3px" lineHeight="1.55">
					<RichText text={check.message} />
				</Text>
				{isIssue && check.fix && (
					<Text fontSize="12.5px" color="red.600" mt="6px" lineHeight="1.55" fontWeight="600">
						<RichText text={check.fix} />
					</Text>
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
	const [error, setError] = useState("");
	const hasStarted = useRef(false);

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
			<Text as="h2" fontSize="25px" fontWeight="700" mb="10px" letterSpacing="-0.015em">
				{isLoading
					? __("Checking your settings…", "user-registration")
					: __("Here's what we found", "user-registration")}
			</Text>
			<Text fontSize="14.5px" lineHeight="1.62" color="gray.600" mb="22px" maxW="60ch">
				{isLoading
					? __("Reading your current configuration. This only takes a moment.", "user-registration")
					: __("Ten checks run against your current email settings.", "user-registration")}
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
							<CheckRow key={check.key} check={check} index={index} />
						))}
					</Flex>

					<Flex
						align="center"
						gap="13px"
						border="1px solid"
						borderColor={issueCount === 0 ? "green.200" : "yellow.300"}
						bg={issueCount === 0 ? "green.50" : "yellow.50"}
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
							color={issueCount === 0 ? "green.600" : "yellow.700"}
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
								? __("No misconfigurations found — continue to the live delivery test.", "user-registration")
								: __("Fix these first, then we'll test actual delivery.", "user-registration")}
						</Box>
					</Flex>

					<Flex gap="10px" mt="20px" wrap="wrap">
						<Button colorScheme="primary" fontSize="13.5px" fontWeight="600" onClick={() => onNext(checks, smartSmtpStatus)}>
							{__("Next: test delivery", "user-registration")}
						</Button>
						<Button variant="outline" fontSize="13.5px" fontWeight="600" onClick={() => onOpenReport(checks)}>
							{__("Send report to support", "user-registration")}
						</Button>
					</Flex>
				</>
			)}
		</Box>
	);
};

export default ScanStep;
