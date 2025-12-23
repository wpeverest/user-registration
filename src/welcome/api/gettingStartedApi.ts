import { MembershipSetupType } from "../context/Gettingstartedcontext";

export const API_BASE =
	"/wp-json/user-registration/v1/getting-started";

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

export const apiPost = async <T = any>(
	path: string,
	body?: any
): Promise<T> => {
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

export const mapSetupToApiType = (
	type: MembershipSetupType
): string => {
	switch (type) {
		case "paid":
			return "paid_membership";
		case "free":
			return "free_membership";
		default:
			return "normal";
	}
};

export const mapApiToSetupType = (
	apiType: string
): MembershipSetupType => {
	switch (apiType) {
		case "paid_membership":
			return "paid";
		case "free_membership":
			return "free";
		default:
			return "other";
	}
};
