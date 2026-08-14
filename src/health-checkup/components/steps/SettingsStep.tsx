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
 * Step 2 — what the plugin will send and to whom. The cheap, local answers come
 * first: they are settings the admin owns and can change in one click, so
 * getting them out of the way before the delivery diagnosis means nobody reads a
 * page of DNS findings only to discover "Disable Emails" was on all along.
 *
 * This is the step that runs the scan; step 3 reads the same result.
 *
 * It carries a Back too, even though it is the first step with findings: the
 * start screen is restored past on every reload, so without a way back to it
 * that screen is unreachable for the rest of the session.
 *
 * No support report here — half a scan, with the delivery findings still unread,
 * isn't what an agent needs. The offer starts on step 3.
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
