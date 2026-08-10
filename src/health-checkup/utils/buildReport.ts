import { CheckStatus, DeliveryOutcome, HealthCheck, Verdict } from "../api/healthCheckupApi";

/** Strip the `**bold**` / `` `code` `` markers the UI renders, for a plain-text report. */
const plain = (text: string) => text.replace(/\*\*/g, "").replace(/`/g, "");

const statusLabels: Record<CheckStatus, string> = {
	pass: "PASS",
	error: "WILL FAIL",
	warning: "MAY FAIL",
	blocked: "BLOCKED",
	unknown: "CAN'T TELL",
};

// Support reads this, not the admin — so the findings carry their explanation
// rather than just a title, and the verdict leads.
export function buildReport(
	checks: HealthCheck[],
	outcome: DeliveryOutcome | null,
	verdict?: Verdict | null
): string {
	const failing = checks.filter(
		(check) => check.status === "error" || check.status === "warning"
	);
	const outcomeText: Record<DeliveryOutcome, string> = {
		arrived: "Arrived in inbox",
		spam: "Arrived, filed as spam",
		none: "Not received (inbox or spam)",
	};

	const lines: string[] = [
		"User Registration & Membership — Email Health Report",
		"Generated from Settings → Emails → Health Checkup",
		"",
		...(verdict
			? [`VERDICT: ${verdict.title}`, plain(verdict.message), ""]
			: []),
		`SCAN RESULTS (${failing.length} finding${failing.length === 1 ? "" : "s"})`,
		...checks.map((check) => `[${statusLabels[check.status] ?? "ERROR"}] ${check.title}`),
		"",
		...(failing.length > 0
			? [
				"DETAIL",
				...failing.flatMap((check) => [
					`* ${check.title}`,
					`  ${plain(check.message)}`,
					...(check.fix ? [`  Fix: ${plain(check.fix)}`] : []),
				]),
				"",
			]
			: []),
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
