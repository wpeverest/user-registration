import { Box, ChakraProvider, extendTheme } from "@chakra-ui/react";
import { useEffect, useRef, useState } from "react";
import {
	confirmDelivery,
	DeliveryOutcome,
	HealthCheck,
	SmartSmtpStatus,
	SmtpPluginInfo,
	Verdict,
} from "../api/healthCheckupApi";
import { buildReport } from "../utils/buildReport";
import { loadState, saveState } from "../utils/persistedState";
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
	// Every colour below comes from the plugin's own SCSS tokens in
	// assets/css/variables/_colors.scss, shaded with the same lighten()/darken()
	// steps components/_badge.scss uses for its subtle variants — so a status
	// here reads the same as the identical status anywhere else in the plugin.
	colors: {
		// $primary_color, matching the setup wizard (src/welcome/components/App.tsx).
		primary: {
			50: "#eef1ff",
			100: "#d4daff",
			200: "#b8c1ff",
			300: "#9ba8ff",
			400: "#7e8fff",
			500: "#475bb2",
			600: "#3a4b9c",
			700: "#2f3da6",
			800: "#252f89",
			900: "#1c246d",
		},
		// $green — pass.
		green: {
			50: "#e8f8e6", // lighten($green, 42%)
			200: "#cef0cb", // lighten($green, 35%)
			500: "#4cc741", // $green
			600: "#3aa530", // darken($green, 10%)
			700: "#3aa530",
		},
		// $red — issue.
		red: {
			50: "#ffe8e9", // lighten($red, 30%)
			200: "#ffb5b8", // lighten($red, 20%)
			500: "#ff4f55", // $red
			600: "#ff4f55", // _badge.scss uses the base tone for danger text.
		},
		// $orange — warning.
		orange: {
			50: "#fff8e6", // lighten($orange, 45%)
			200: "#ffeab3", // lighten($orange, 35%)
			300: "#ffeab3",
			500: "#ffba00", // $orange
			700: "#b38200", // darken($orange, 15%)
		},
		// The plugin's grey ramp ($grey-15 → $grey-500, $border-color).
		gray: {
			50: "#fdfdfd",
			100: "#f4f4f4",
			200: "#e7e7e7",
			300: "#bdbdbd",
			400: "#bababa",
			500: "#999999",
			600: "#6b6b6b",
			700: "#383838",
			800: "#222222",
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

// Read once at module load — the wizard mounts a single time, and reading per
// render would just re-parse the same JSON.
const restored = loadState();

const App = () => {
	const [step, setStep] = useState<WizardStep>(restored?.step ?? "intro");
	const [checks, setChecks] = useState<HealthCheck[]>(restored?.checks ?? []);
	const [deliveryOutcome, setDeliveryOutcome] = useState<DeliveryOutcome | null>(
		restored?.deliveryOutcome ?? null
	);
	const [isReportOpen, setIsReportOpen] = useState(false);
	const [reportText, setReportText] = useState("");
	const [testEmailSent, setTestEmailSent] = useState(restored?.testEmailSent ?? false);
	const [smartSmtpStatus, setSmartSmtpStatus] = useState<SmartSmtpStatus>(
		restored?.smartSmtpStatus ?? "not_installed"
	);
	const [smtpPlugin, setSmtpPlugin] = useState<SmtpPluginInfo | null>(restored?.smtpPlugin ?? null);
	const [verdict, setVerdict] = useState<Verdict | null>(restored?.verdict ?? null);
	const rootRef = useRef<HTMLDivElement>(null);

	// Survive a page refresh: without this the admin lands back on the intro and
	// loses a completed run.
	useEffect(() => {
		saveState({ step, checks, deliveryOutcome, smartSmtpStatus, smtpPlugin, testEmailSent, verdict });
	}, [step, checks, deliveryOutcome, smartSmtpStatus, smtpPlugin, testEmailSent, verdict]);

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

	// Clear the previous run's data too, so a refresh part-way through the new
	// run can't resurrect the old result.
	const startNewRun = () => {
		setTestEmailSent(false);
		setChecks([]);
		setDeliveryOutcome(null);
		setVerdict(null);
		setStep("scan");
	};

	// Args, not state: a caller that just called setChecks() would still read the
	// pre-update value here and report zero issues on the first open.
	const openReport = (
		reportChecks: HealthCheck[],
		outcome: DeliveryOutcome | null,
		reportVerdict: Verdict | null
	) => {
		setReportText(buildReport(reportChecks, outcome, reportVerdict));
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
						onNext={(scannedChecks, scannedSmartSmtpStatus, scannedSmtpPlugin, scannedVerdict) => {
							setChecks(scannedChecks);
							setSmartSmtpStatus(scannedSmartSmtpStatus);
							setSmtpPlugin(scannedSmtpPlugin);
							setVerdict(scannedVerdict);
							setStep("test");
						}}
						onOpenReport={(scannedChecks, scannedVerdict) => {
							setChecks(scannedChecks);
							setVerdict(scannedVerdict);
							// Untested at this point, so never carry over a previous run's outcome.
							openReport(scannedChecks, null, scannedVerdict);
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
				// The result is where a run ends: it stays put until the admin
				// explicitly starts another one.
				return (
					<ResultStep
						variant={variant}
						checks={checks}
						onRunAgain={startNewRun}
						onOpenReport={() => openReport(checks, deliveryOutcome, verdict)}
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
					borderColor="gray.100"
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
