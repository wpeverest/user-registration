export interface HealthCheck {
	key: string;
	title: string;
	status: "pass" | "issue";
	message: string;
	fix: string;
}

export type SmartSmtpStatus = "active" | "inactive" | "not_installed";

export interface SmtpPluginInfo {
	slug: string;
	name: string;
	is_smartsmtp: boolean;
}

export interface ScanResult {
	checks: HealthCheck[];
	issue_count: number;
	smartsmtp_status: SmartSmtpStatus;
	smtp_plugin: SmtpPluginInfo | null;
}

export type DeliveryOutcome = "arrived" | "spam" | "none";

const config = () => window._UR_EMAIL_HEALTH_;

async function postForm<T>(body: URLSearchParams): Promise<T> {
	const response = await fetch(config().ajaxURL, {
		method: "POST",
		headers: { "Content-Type": "application/x-www-form-urlencoded" },
		body,
	});

	const result = await response.json();

	if (!result.success) {
		throw new Error(result.data?.message || "Request failed.");
	}

	return result.data as T;
}

export function runScan(): Promise<ScanResult> {
	return postForm<ScanResult>(
		new URLSearchParams({
			action: "user_registration_email_health_scan",
			nonce: config().scanNonce,
		})
	);
}

export function sendTestEmail(email: string): Promise<{ message: string }> {
	return postForm<{ message: string }>(
		new URLSearchParams({
			action: "user_registration_send_test_email",
			email,
			nonce: config().testEmailNonce,
		})
	);
}

export function confirmDelivery(result: DeliveryOutcome): Promise<void> {
	return postForm<void>(
		new URLSearchParams({
			action: "user_registration_email_health_confirm_delivery",
			result,
			nonce: config().confirmNonce,
		})
	);
}

export function installSmartSmtp(): Promise<{ redirect: string }> {
	return postForm<{ redirect: string }>(
		new URLSearchParams({
			action: "user_registration_email_health_install_smartsmtp",
			nonce: config().scanNonce,
		})
	);
}
