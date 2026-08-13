import { DeliveryOutcome, ScanResult } from "../api/healthCheckupApi";
import { WizardStep } from "../components/Stepper";

export interface PersistedState {
	step: WizardStep;
	/** The whole scan, kept as one value: both scan steps read from this. */
	scan: ScanResult | null;
	deliveryOutcome: DeliveryOutcome | null;
	testEmailSent: boolean;
	/** Optional, so a payload saved before this existed still restores. */
	sendError?: string | null;
}

// Bumping the suffix retires state saved by an older shape rather than letting
// it deserialise into something the current code doesn't expect. v3 split the
// single scan step in two and folded the loose scan fields into `scan`; v4 moved
// SPF and DMARC out of the delivery section, so a v3 payload would still hold
// those rows and render them.
const STORAGE_KEY = "urEmailHealthCheckup:v4";

const VALID_STEPS: WizardStep[] = [
	"intro",
	"settings",
	"delivery",
	"test",
	"result-good",
	"result-none",
	"result-spam",
];

/**
 * sessionStorage, not localStorage: a reload should land the admin back where
 * they were, but a scan is a point-in-time snapshot — and this flow actively
 * sends people off to change email settings — so it shouldn't outlive the tab
 * and start presenting stale results as current.
 */
export function loadState(): PersistedState | null {
	try {
		const raw = window.sessionStorage.getItem(STORAGE_KEY);

		if (!raw) {
			return null;
		}

		const parsed = JSON.parse(raw) as PersistedState;

		if (!VALID_STEPS.includes(parsed?.step)) {
			return null;
		}

		// A scan is either absent or complete. Half a payload would render a step
		// with no rows and no way to ask for them again.
		if (parsed.scan && !Array.isArray(parsed.scan.sections)) {
			return null;
		}

		return parsed;
	} catch (error) {
		// Storage can be unavailable (private mode, blocked cookies) or hold
		// unparseable text — either way, just start the wizard fresh.
		return null;
	}
}

export function saveState(state: PersistedState): void {
	try {
		window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
	} catch (error) {
		// Persisting is a convenience; losing it must never break the wizard.
	}
}
