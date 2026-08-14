import { __ } from "@wordpress/i18n";
import { CheckSection } from "../../api/healthCheckupApi";
import ScanScreen from "../ScanScreen";

interface SettingsStepProps {
	section: CheckSection | null;
	isLoading: boolean;
	error: string;
	onBack: () => void;
	onNext: () => void;
	onResolved: () => void;
}

/**
 * Step 2, the plugin's own settings: cheap local answers before the delivery
 * diagnosis. Runs the scan that step 3 also reads. No support report here, with
 * the delivery findings still unread.
 */
const SettingsStep = ({
	section,
	isLoading,
	error,
	onBack,
	onNext,
	onResolved,
}: SettingsStepProps) => (
	<ScanScreen
		loadingHeading={__("Checking your settings…", "user-registration")}
		heading={__("What the plugin will send", "user-registration")}
		loadingBlurb={__(
			"Reading your mail configuration and checking what your domain publishes.",
			"user-registration"
		)}
		section={section}
		isLoading={isLoading}
		error={error}
		onNext={onNext}
		onBack={onBack}
		onResolved={onResolved}
	/>
);

export default SettingsStep;
