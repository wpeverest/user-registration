import {
	DeliveryOutcome,
	HealthCheck,
	SmartSmtpStatus,
	SmtpPluginInfo,
	Verdict,
} from "../api/healthCheckupApi";
import { WizardStep } from "../components/Stepper";

export interface PersistedState {
	step: WizardStep;
	checks: HealthCheck[];
	deliveryOutcome: DeliveryOutcome | null;
	smartSmtpStatus: SmartSmtpStatus;
	smtpPlugin: SmtpPluginInfo | null;
	testEmailSent: boolean;
	verdict: Verdict | null;
}

// Bumping the suffix retires state saved by an older shape rather than letting
// it deserialise into something the current code doesn't expect.
const STORAGE_KEY = "urEmailHealthCheckup:v1";

const VALID_STEPS: WizardStep[] = ["intro", "scan", "test", "result-good", "result-none", "result-spam"];

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

		if (!VALID_STEPS.includes(parsed?.step) || !Array.isArray(parsed?.checks)) {
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
