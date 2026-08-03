import { Box, ChakraProvider, extendTheme } from "@chakra-ui/react";
import { useEffect, useRef, useState } from "react";
import {
	confirmDelivery,
	DeliveryOutcome,
	HealthCheck,
	SmartSmtpStatus,
	SmtpPluginInfo,
} from "../api/healthCheckupApi";
import { buildReport } from "../utils/buildReport";
import ReportModal from "./ReportModal";
import Stepper, { WizardStep } from "./Stepper";
import IntroStep from "./steps/IntroStep";
import ResultStep, { ResultVariant } from "./steps/ResultStep";
import ScanStep from "./steps/ScanStep";
import TestDeliveryStep from "./steps/TestDeliveryStep";

// Match wp-admin's own font stack instead of loading a separate webfont.
const SANS_STACK = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif";
const MONO_STACK = "ui-monospace, SFMono-Regular, Menlo, Consolas, monospace";

const theme = extendTheme({
	fonts: {
		heading: SANS_STACK,
		body: SANS_STACK,
		mono: MONO_STACK,
	},
	colors: {
		// Matches the setup wizard's brand palette (src/welcome/components/App.tsx).
		primary: {
			50: "#eef1ff",
			100: "#d4daff",
			200: "#b8c1ff",
			300: "#9ba8ff",
			400: "#7e8fff",
			500: "#475BB2",
			600: "#3A4B9C",
			700: "#2f3da6",
			800: "#252f89",
			900: "#1c246d",
		},
		green: {
			50: "#e7f7ef",
			200: "#bfe8d3",
			600: "#0f7a55",
			700: "#0f7a55",
		},
		red: {
			50: "#fdedec",
			200: "#f4c8c4",
			600: "#b3261e",
		},
		yellow: {
			50: "#fdf3df",
			300: "#eeddb0",
			700: "#8a5a11",
		},
	},
	components: {
		Button: {
			baseStyle: {
				borderRadius: "4px",
			},
		},
	},
});

const RESULT_VARIANTS: Record<DeliveryOutcome, ResultVariant> = {
	arrived: "good",
	spam: "spam",
	none: "none",
};

const App = () => {
	const [step, setStep] = useState<WizardStep>("intro");
	const [checks, setChecks] = useState<HealthCheck[]>([]);
	const [deliveryOutcome, setDeliveryOutcome] = useState<DeliveryOutcome | null>(null);
	const [isReportOpen, setIsReportOpen] = useState(false);
	const [reportText, setReportText] = useState("");
	const [testEmailSent, setTestEmailSent] = useState(false);
	const [smartSmtpStatus, setSmartSmtpStatus] = useState<SmartSmtpStatus>("not_installed");
	const [smtpPlugin, setSmtpPlugin] = useState<SmtpPluginInfo | null>(null);
	const rootRef = useRef<HTMLDivElement>(null);

	// Prevents Chrome's scroll-anchoring from re-adjusting scroll after a step change.
	useEffect(() => {
		document.documentElement.style.overflowAnchor = "none";
	}, []);

	// Scroll each step to its own top, except on first mount where it's already correct.
	const isFirstRender = useRef(true);
	useEffect(() => {
		if (isFirstRender.current) {
			isFirstRender.current = false;
			return;
		}
		rootRef.current?.scrollIntoView({ block: "start" });
	}, [step]);

	const startNewRun = () => {
		setTestEmailSent(false);
		setStep("scan");
	};

	const openReport = () => {
		setReportText(buildReport(checks, deliveryOutcome));
		setIsReportOpen(true);
	};

	const handleChoice = (outcome: DeliveryOutcome) => {
		setDeliveryOutcome(outcome);
		confirmDelivery(outcome).catch(() => undefined);
		setStep(
			outcome === "arrived" ? "result-good" : outcome === "spam" ? "result-spam" : "result-none"
		);
	};

	const renderStep = () => {
		switch (step) {
			case "intro":
				return <IntroStep onStart={startNewRun} />;
			case "scan":
				return (
					<ScanStep
						onNext={(scannedChecks, scannedSmartSmtpStatus, scannedSmtpPlugin) => {
							setChecks(scannedChecks);
							setSmartSmtpStatus(scannedSmartSmtpStatus);
							setSmtpPlugin(scannedSmtpPlugin);
							setStep("test");
						}}
						onOpenReport={(scannedChecks) => {
							setChecks(scannedChecks);
							openReport();
						}}
					/>
				);
			case "test":
				return (
					<TestDeliveryStep
						onChoice={handleChoice}
						alreadySent={testEmailSent}
						onSent={() => setTestEmailSent(true)}
					/>
				);
			case "result-good":
			case "result-none":
			case "result-spam": {
				const variant: ResultVariant = deliveryOutcome
					? RESULT_VARIANTS[deliveryOutcome]
					: "good";
				return (
					<ResultStep
						variant={variant}
						checks={checks}
						onRunAgain={startNewRun}
						onDone={() => setStep("intro")}
						onBack={() => setStep("test")}
						onOpenReport={openReport}
						smartSmtpStatus={smartSmtpStatus}
						smtpPlugin={smtpPlugin}
					/>
				);
			}
			default:
				return null;
		}
	};

	return (
		<ChakraProvider theme={theme}>
			{/* Left-aligned, not centred: the wizard has to sit on the same axis as
			    the section heading and every other Emails settings card. */}
			<Box ref={rootRef} maxW="780px" sx={{ overflowAnchor: "none" }}>
				<Box fontSize="21px" fontWeight="600" letterSpacing="-0.01em" color="gray.800" px="2px" mb="26px">
					Email Delivery Checkup
				</Box>

				<Stepper step={step} />

				<Box
					bg="white"
					border="1px solid"
					borderColor="#F4F4F4"
					borderRadius="8px"
					boxShadow="0 10px 15px -3px rgba(0, 0, 0, 0.06)"
					p="24px 32px 32px"
				>
					{renderStep()}
				</Box>
			</Box>

			<ReportModal isOpen={isReportOpen} onClose={() => setIsReportOpen(false)} report={reportText} />
		</ChakraProvider>
	);
};

export default App;
