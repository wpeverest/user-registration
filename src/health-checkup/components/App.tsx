import { Box, ChakraProvider, extendTheme } from "@chakra-ui/react";
import { useState } from "react";
import {
	confirmDelivery,
	DeliveryOutcome,
	HealthCheck,
	SmartSmtpStatus,
} from "../api/healthCheckupApi";
import { buildReport } from "../utils/buildReport";
import ReportModal from "./ReportModal";
import Stepper, { WizardStep } from "./Stepper";
import IntroStep from "./steps/IntroStep";
import ResultStep, { ResultVariant } from "./steps/ResultStep";
import ScanStep from "./steps/ScanStep";
import TestDeliveryStep from "./steps/TestDeliveryStep";

// Match wp-admin's own font stack (wp-admin/css/common.css `body`) rather
// than loading a separate webfont — this page should look like the rest of
// Settings, not like a visually distinct import.
const SANS_STACK = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif";
const MONO_STACK = "ui-monospace, SFMono-Regular, Menlo, Consolas, monospace";

// Semantic colors matched to the approved design artifact's muted tones —
// Chakra's stock green/red/yellow are far more saturated than the artifact.
const theme = extendTheme({
	fonts: {
		heading: SANS_STACK,
		body: SANS_STACK,
		mono: MONO_STACK,
	},
	colors: {
		primary: {
			50: "#eef0fd",
			100: "#d6d8f7",
			200: "#b3b6ef",
			300: "#9089ff",
			400: "#6a5fe0",
			500: "#4338ca",
			600: "#3730a3",
			700: "#2c2682",
			800: "#211d61",
			900: "#161340",
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
	// Tracks whether the test email for the *current* checkup run has
	// already gone out, so navigating "Back" from a result screen re-shows
	// the same choices instead of firing off another test email.
	const [testEmailSent, setTestEmailSent] = useState(false);
	const [smartSmtpStatus, setSmartSmtpStatus] = useState<SmartSmtpStatus>("not_installed");

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
						onNext={(scannedChecks, scannedSmartSmtpStatus) => {
							setChecks(scannedChecks);
							setSmartSmtpStatus(scannedSmartSmtpStatus);
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
						onRunAgain={startNewRun}
						onDone={() => setStep("intro")}
						onBack={() => setStep("test")}
						onOpenReport={openReport}
						smartSmtpStatus={smartSmtpStatus}
					/>
				);
			}
			default:
				return null;
		}
	};

	return (
		<ChakraProvider theme={theme}>
			<Box maxW="780px" mx="auto">
				<Box fontSize="17px" fontWeight="700" letterSpacing="-0.01em" px="2px" mb="14px">
					Email Delivery Checkup
				</Box>

				<Stepper step={step} />

				<Box bg="white" border="1px solid" borderColor="gray.200" borderRadius="14px" boxShadow="sm" p="30px 32px 32px">
					{renderStep()}
				</Box>
			</Box>

			<ReportModal isOpen={isReportOpen} onClose={() => setIsReportOpen(false)} report={reportText} />
		</ChakraProvider>
	);
};

export default App;
