import apiFetch from "@wordpress/api-fetch";

/* global _URCR_DASHBOARD_ */
const { urRestApiNonce } =
	typeof _URCR_DASHBOARD_ !== "undefined" && _URCR_DASHBOARD_;

const base = "user-registration/v1/";
const urls = {
	rules: base + "content-access-rules",
	getRule: (id) => base + `content-access-rules/${id}`,
	createRule: base + "content-access-rules",
	toggleStatus: (id) => base + `content-access-rules/${id}/toggle-status`,
	updateRule: (id) => base + `content-access-rules/${id}`,
	duplicateRule: (id) => base + `content-access-rules/${id}/duplicate`,
	deleteRule: (id) => base + `content-access-rules/${id}`
};

export const getAllRules = () => {
	return apiFetch({
		path: urls.rules,
		method: "GET",
		headers: {
			"X-WP-Nonce": urRestApiNonce
		}
	}).then((res) => res);
};

export const getRule = (id) => {
	return apiFetch({
		path: urls.getRule(id),
		method: "GET",
		headers: {
			"X-WP-Nonce": urRestApiNonce
		}
	}).then((res) => res);
};

export const createRule = (title) => {
	return apiFetch({
		path: urls.createRule,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce
		},
		data: {
			title: title
		}
	}).then((res) => res);
};

export const toggleRuleStatus = (id, enabled) => {
	return apiFetch({
		path: urls.toggleStatus(id),
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce
		},
		data: {
			enabled: enabled
		}
	}).then((res) => res);
};

export const updateRule = (id, data) => {
	return apiFetch({
		path: urls.updateRule(id),
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce
		},
		data: data
	}).then((res) => res);
};

export const duplicateRule = (id) => {
	return apiFetch({
		path: urls.duplicateRule(id),
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce
		}
	}).then((res) => res);
};

export const deleteRule = (id, force = false) => {
	return apiFetch({
		path: urls.deleteRule(id) + (force ? "?force=true" : ""),
		method: "DELETE",
		headers: {
			"X-WP-Nonce": urRestApiNonce
		}
	}).then((res) => res);
};
