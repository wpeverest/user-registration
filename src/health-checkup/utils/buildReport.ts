import { CheckSection, CheckStatus, DeliveryOutcome, HealthCheck, ScanSummary } from "../api/healthCheckupApi";

/** Strip the `**bold**` / `` `code` `` markers the UI renders, for a plain-text report. */
const plain = (text: string) => text.replace(/\*\*/g, "").replace(/`/g, "");

const statusLabels: Record<CheckStatus, string> = {
	pass: "PASS",
	error: "WILL FAIL",
	warning: "MAY FAIL",
	blocked: "BLOCKED",
	unknown: "CAN'T TELL",
};

const outcomeText: Record<DeliveryOutcome, string> = {
	arrived: "Arrived in inbox",
	spam: "Arrived, but filed as spam",
	none: "Never arrived (not in inbox or spam)",
};

const rule = "-".repeat(58);

/**
 * A support agent reads this cold, with no access to the site. So it leads with
 * the answer, keeps the checks grouped exactly as the admin saw them on screen,
 * and spells out every failing one in full — a bare list of titles would make
 * "please send the details" their first reply.
 *
 * Nothing here is sensitive: the scan never reads an SMTP password, so no part
 * of this should make someone hesitate to paste it into a ticket.
 */
export function buildReport(
	checks: HealthCheck[],
	outcome: DeliveryOutcome | null,
	summary?: ScanSummary | null,
	sections?: CheckSection[]
): string {
	const failing = checks.filter(
		(check) => check.status === "error" || check.status === "warning"
	);

	// One unlabelled group keeps the report readable if it is ever built before
	// a scan has grouped anything.
	const groups: CheckSection[] =
		sections && sections.length > 0
			? sections
			: [{ key: "delivery", title: "All checks", description: "", checks }];

	const lines: string[] = [
		"USER REGISTRATION & MEMBERSHIP — EMAIL HEALTH REPORT",
		"Generated from Settings → Emails → Health Checkup",
		rule,
		"",
	];

	if (summary) {
		lines.push(`RESULT: ${summary.title}`, plain(summary.message), "");
	}

	lines.push(`Checks run: ${checks.length}`, `Needing attention: ${failing.length}`, "");

	groups.forEach((group) => {
		lines.push(rule, group.title.toUpperCase(), rule);
		group.checks.forEach((check) => {
			lines.push(`[${statusLabels[check.status] ?? "ERROR"}] ${check.title}`);
		});
		lines.push("");
	});

	if (failing.length > 0) {
		lines.push(rule, "WHAT IS WRONG, IN DETAIL", rule);
		failing.forEach((check, index) => {
			lines.push(
				`${index + 1}. ${check.title}  [${statusLabels[check.status]}]`,
				`   ${plain(check.message).replace(/\n/g, "\n   ")}`
			);

			if (check.fix) {
				lines.push(`   Suggested fix: ${plain(check.fix)}`);
			}

			lines.push("");
		});
	}

	lines.push(
		rule,
		"LIVE DELIVERY TEST",
		rule,
		`Test email sent to: ${window._UR_EMAIL_HEALTH_.adminEmail}`,
		`Reported by admin:  ${outcome ? outcomeText[outcome] : "Not tested yet"}`,
		"",
		rule,
		"SITE INFO",
		rule,
		`Site URL:       ${window._UR_EMAIL_HEALTH_.siteUrl}`,
		`WordPress:      ${window._UR_EMAIL_HEALTH_.wpVersion}`,
		`PHP:            ${window._UR_EMAIL_HEALTH_.phpVersion}`,
		`Plugin version: ${window._UR_EMAIL_HEALTH_.pluginVersion}`
	);

	return lines.join("\n");
}
