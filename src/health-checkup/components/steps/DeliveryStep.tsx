import { __ } from "@wordpress/i18n";
import { CheckSection } from "../../api/healthCheckupApi";
import ScanScreen from "../ScanScreen";

interface DeliveryStepProps {
	section: CheckSection | null;
	isLoading: boolean;
	error: string;
	onBack: () => void;
	onNext: () => void;
	onOpenReport: () => void;
	onResolved: () => void;
}

/**
 * Step 3 — the route mail takes off this server and whether the site is allowed
 * to send as the configured "From" address. This is where mail actually goes
 * missing, so it sits immediately before the live test that confirms it.
 */
const DeliveryStep = ({
	section,
	isLoading,
	error,
	onBack,
	onNext,
	onOpenReport,
	onResolved,
}: DeliveryStepProps) => (
	<ScanScreen
		loadingHeading={__("Checking how your mail is sent…", "user-registration")}
		heading={__("How your mail leaves this site", "user-registration")}
		loadingBlurb={__(
			"Working out the route mail takes and what your domain publishes about it.",
			"user-registration"
		)}
		section={section}
		// Findings only. The remedies for these are recommendations, and they are
		// made on the result screen once the test has proved mail didn't arrive.
		showFixes={false}
		isLoading={isLoading}
		error={error}
		onNext={onNext}
		onBack={onBack}
		onOpenReport={onOpenReport}
		onResolved={onResolved}
	/>
);

export default DeliveryStep;
