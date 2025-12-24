import { MembershipSetupType, PaymentSettings } from "../context/Gettingstartedcontext";

export const API_BASE = "/wp-json/user-registration/v1/getting-started";

const getHeaders = () => {
	const nonce =
		(window as any).urmSetupWizard?.nonce ||
		(window as any).wpApiSettings?.nonce;

	return {
		"Content-Type": "application/json",
		...(nonce ? { "X-WP-Nonce": nonce } : {}),
	};
};

export const apiGet = async <T = any>(path: string): Promise<T> => {
	const res = await fetch(`${API_BASE}${path}`, {
		method: "GET",
		headers: getHeaders(),
		credentials: "same-origin",
	});
	const json = await res.json();
	if (!json.success) {
		throw new Error(json.message || "Request failed");
	}
	return json.data ?? json;
};

export const apiPost = async <T = any>(path: string, body?: any): Promise<T> => {
	const res = await fetch(`${API_BASE}${path}`, {
		method: "POST",
		headers: getHeaders(),
		credentials: "same-origin",
		body: body ? JSON.stringify(body) : undefined,
	});
	const json = await res.json();
	if (!json.success) {
		throw new Error(json.message || "Request failed");
	}
	return json.data ?? json;
};

export const mapSetupToApiType = (type: MembershipSetupType): string => {
	switch (type) {
		case "paid":
			return "paid_membership";
		case "free":
			return "free_membership";
		default:
			return "normal";
	}
};

export const mapApiToSetupType = (apiType: string): MembershipSetupType => {
	switch (apiType) {
		case "paid_membership":
			return "paid";
		case "free_membership":
			return "free";
		default:
			return "other";
	}
};

/**
 * Maps the frontend PaymentSettings to the API format
 */
export const mapPaymentSettingsToApi = (settings: PaymentSettings) => {
	return {
		offline_payment: settings.offlinePayment,
		bank_details: settings.bankDetails,
		paypal: settings.paypal,
		paypal_email: settings.paypalEmail,
		paypal_client_id: settings.paypalClientId,
		paypal_client_secret: settings.paypalClientSecret,
		stripe: settings.stripe,
		stripe_test_mode: settings.stripeTestMode,
		stripe_test_publishable_key: settings.stripeTestPublishableKey,
		stripe_test_secret_key: settings.stripeTestSecretKey,
		stripe_live_publishable_key: settings.stripeLivePublishableKey,
		stripe_live_secret_key: settings.stripeLiveSecretKey
	};
};

/**
 * Maps API payment settings response to frontend format
 */
export const mapApiToPaymentSettings = (
	apiData: any
): Partial<PaymentSettings> => {
	return {
		offlinePayment: apiData.offline_payment ?? false,
		bankDetails: apiData.bank_details ?? "",
		paypal: apiData.paypal ?? false,
		paypalEmail: apiData.paypal_email ?? "",
		paypalClientId: apiData.paypalClientId ?? "",
		paypalClientSecret: apiData.paypalClientSecret ?? "",
		stripe: apiData.stripe ?? false,
		stripeTestMode: apiData.stripe_test_mode ?? false,
		stripeTestPublishableKey: apiData.stripe_test_publishable_key ?? "",
		stripeTestSecretKey: apiData.stripe_test_secret_key ?? "",
		stripeLivePublishableKey: apiData.stripe_live_publishable_key ?? "",
		stripeLiveSecretKey: apiData.stripe_live_secret_key ?? ""
	};
};
