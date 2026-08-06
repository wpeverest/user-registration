import { CheckStatus, DeliveryOutcome, HealthCheck } from "../api/healthCheckupApi";

const statusLabels: Record<CheckStatus, string> = {
	pass: "PASS",
	issue: "ISSUE",
	blocked: "BLOCKED",
};

export function buildReport(
	checks: HealthCheck[],
	outcome: DeliveryOutcome | null
): string {
	const issues = checks.filter((check) => check.status === "issue");
	const outcomeText: Record<DeliveryOutcome, string> = {
		arrived: "Arrived in inbox",
		spam: "Arrived, filed as spam",
		none: "Not received (inbox or spam)",
	};

	const lines: string[] = [
		"User Registration & Membership — Email Health Report",
		"Generated from Settings → Emails → Health Checkup",
		"",
		`SCAN RESULTS (${issues.length} issue${issues.length === 1 ? "" : "s"} found)`,
		...checks.map((check) => `[${statusLabels[check.status] ?? "ISSUE"}] ${check.title}`),
		"",
		"DELIVERY TEST",
		`Test email sent to: ${window._UR_EMAIL_HEALTH_.adminEmail}`,
		`Outcome reported by admin: ${outcome ? outcomeText[outcome] : "Not yet tested"}`,
		"",
		"SITE INFO",
		`Site URL: ${window._UR_EMAIL_HEALTH_.siteUrl}`,
		`WordPress: ${window._UR_EMAIL_HEALTH_.wpVersion}`,
		`PHP: ${window._UR_EMAIL_HEALTH_.phpVersion}`,
		`Plugin version: ${window._UR_EMAIL_HEALTH_.pluginVersion}`,
	];

	return lines.join("\n");
}
