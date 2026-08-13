import { Box, ChakraProvider, extendTheme } from "@chakra-ui/react";
import { useCallback, useEffect, useRef, useState } from "react";
import {
	confirmDelivery,
	DeliveryOutcome,
	runScan,
	ScanResult,
	sectionOf,
} from "../api/healthCheckupApi";
import { buildReport } from "../utils/buildReport";
import { loadState, saveState } from "../utils/persistedState";
import ReportModal from "./ReportModal";
import Stepper, { SCAN_STEPS, WizardStep } from "./Stepper";
import DeliveryStep from "./steps/DeliveryStep";
import IntroStep from "./steps/IntroStep";
import ResultStep, { ResultVariant } from "./steps/ResultStep";
import SettingsStep from "./steps/SettingsStep";
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

// A refresh part-way through the scan restores the step but has no result to
// restore with it, so the scan has to be kicked again — otherwise that step
// renders with no rows and no way to ask for them.
const needsInitialScan = !!restored && SCAN_STEPS.includes(restored.step) && !restored.scan;

const App = () => {
	const [step, setStep] = useState<WizardStep>(restored?.step ?? "intro");
	const [scan, setScan] = useState<ScanResult | null>(restored?.scan ?? null);
	// Incremented to request a scan. Starts at 0 — "nothing asked for yet" — so a
	// restored run that already has its result doesn't pay for a second one.
	const [scanToken, setScanToken] = useState(needsInitialScan ? 1 : 0);
	const [isScanning, setIsScanning] = useState(needsInitialScan);
	const [scanError, setScanError] = useState("");
	const [deliveryOutcome, setDeliveryOutcome] = useState<DeliveryOutcome | null>(
		restored?.deliveryOutcome ?? null
	);
	const [isReportOpen, setIsReportOpen] = useState(false);
	const [reportText, setReportText] = useState("");
	const [testEmailSent, setTestEmailSent] = useState(restored?.testEmailSent ?? false);
	const [sendError, setSendError] = useState<string | null>(restored?.sendError ?? null);
	const rootRef = useRef<HTMLDivElement>(null);

	// One scan per run, owned here rather than by a step: the two scan steps are
	// two views of the same result, so fetching it in the first would leave the
	// second either re-running every DNS lookup or reading a value it can't reach.
	useEffect(() => {
		if (0 === scanToken) {
			return;
		}

		let cancelled = false;
		setIsScanning(true);
		setScanError("");

		runScan()
			.then((result) => {
				if (!cancelled) {
					setScan(result);
				}
			})
			.catch((error: Error) => {
				if (!cancelled) {
					setScanError(error.message);
				}
			})
			.finally(() => {
				if (!cancelled) {
					setIsScanning(false);
				}
			});

		return () => {
			cancelled = true;
		};
	}, [scanToken]);

	// Survive a page refresh: without this the admin lands back on the intro and
	// loses a completed run.
	useEffect(() => {
		saveState({ step, scan, deliveryOutcome, testEmailSent, sendError });
	}, [step, scan, deliveryOutcome, testEmailSent, sendError]);

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
		setScan(null);
		setScanError("");
		setDeliveryOutcome(null);
		setSendError(null);
		setStep("settings");
		setScanToken((token) => token + 1);
	};

	// Re-read the checks after an inline fix so the row reflects the new state.
	// The rows already on screen stay put while it runs — `isLoading` below is
	// false whenever a result is in hand — so a fixed row flips to Pass instead
	// of the whole screen dropping back to a progress bar.
	const rescan = useCallback(() => setScanToken((token) => token + 1), []);

	const openReport = () => {
		setReportText(
			buildReport(
				scan?.checks ?? [],
				// Untested until the delivery step has been through, so never carry a
				// previous run's outcome into a report opened from a scan step.
				SCAN_STEPS.includes(step) ? null : deliveryOutcome,
				scan?.summary ?? null,
				scan?.sections ?? [],
				scan?.dns_checks ?? []
			)
		);
		setIsReportOpen(true);
	};

	// Nothing for the admin to confirm, so record the outcome here and carry the
	// server's own error through to the result.
	const handleSendFailure = (error: string) => {
		// The message is built for an admin notice and can carry markup, which
		// RichText would render as visible tags — it parses no HTML by design.
		setSendError(error.replace(/<[^>]*>/g, "").replace(/\s+/g, " ").trim());
		setDeliveryOutcome("none");
		confirmDelivery("none").catch(() => undefined);
		setStep("result-none");
	};

	const handleChoice = (outcome: DeliveryOutcome) => {
		setSendError(null);
		setDeliveryOutcome(outcome);
		confirmDelivery(outcome).catch(() => undefined);
		setStep(
			outcome === "arrived" ? "result-good" : outcome === "spam" ? "result-spam" : "result-none"
		);
	};

	// A rescan after an inline fix keeps the existing rows visible; only a run
	// with nothing to show yet gets the progress bar.
	const isLoading = isScanning && !scan;

	const renderStep = () => {
		switch (step) {
			case "intro":
				return <IntroStep onStart={startNewRun} />;
			case "settings":
				return (
					<SettingsStep
						section={scan ? sectionOf(scan.sections, "settings") : null}
						isLoading={isLoading}
						error={scanError}
						onBack={() => setStep("intro")}
						onNext={() => setStep("delivery")}
						onResolved={rescan}
					/>
				);
			case "delivery":
				return (
					<DeliveryStep
						section={scan ? sectionOf(scan.sections, "delivery") : null}
						isLoading={isLoading}
						error={scanError}
						onBack={() => setStep("settings")}
						onNext={() => setStep("test")}
						onOpenReport={openReport}
						onResolved={rescan}
					/>
				);
			case "test":
				return (
					<TestDeliveryStep
						onChoice={handleChoice}
						alreadySent={testEmailSent}
						onSent={() => setTestEmailSent(true)}
						onSendFailed={handleSendFailure}
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
						checks={scan?.checks ?? []}
						onRunAgain={startNewRun}
						onOpenReport={openReport}
						smartSmtpStatus={scan?.smartsmtp_status ?? "not_installed"}
						smtpPlugin={scan?.smtp_plugin ?? null}
						sendError={sendError}
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
