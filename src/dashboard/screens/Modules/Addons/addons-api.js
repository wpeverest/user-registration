import { select } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";

/* global _UR_DASHBOARD_ */
const { urRestApiNonce, restURL } =
	typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const base = restURL + "user-registration/v1/";
const urls = {
	addons: base + "addons",
	activateAddon: restURL + "wp/v2/plugins",
	deactivateAddon: base + "addons/deactivate",
	bulkActivateAddons: base + "addons/bulk-activate",
	bulkDeactivateAddons: base + "addons/bulk-deactivate",
	installAddon: base + "addons/install",
	bulkInstallAddon: base + "addons/bulk-install",
};

export const getAllAddons = () => {
	return apiFetch({
		path: `${urls.addons}`,
		method: "get",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
	}).then((res) => res);
};

export const getAddon = (slug) => {
	return apiFetch(
		{
			path: `${urls.addons}/${slug}`,
			method: "get",
		}.then((res) => res)
	);
};

export const activateAddon = (slug) => {
	return apiFetch({
		path: `${urls.activateAddon}/${slug}`,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			plugin: slug + "/" + slug,
			status: "active",
		},
	}).then((res) => res);
};

export const deactivateAddon = (slug) => {
	return apiFetch({
		path: `${urls.deactivateAddon}`,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slug: slug + "/" + slug + ".php",
		},
	}).then((res) => res);
};

export const bulkActivateAddons = (slugs) => {
	return apiFetch({
		path: urls.bulkActivateAddons,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slugs: slugs,
		},
	}).then((res) => res);
};

export const bulkDeactivateAddons = (slugs) => {
	return apiFetch({
		path: urls.bulkDeactivateAddons,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slugs: slugs,
		},
	}).then((res) => res);
};

export const installAddon = (slug, name) => {
	return apiFetch({
		path: urls.installAddon,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slug: slug,
			name: name,
		},
	}).then((res) => res);
};

export const bulkInstallAddon = (addonData) => {
	return apiFetch({
		path: urls.bulkInstallAddon,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			addonData: addonData,
		},
	}).then((res) => res);
};

export const isAddonActive = (slug) => {
	try {
		let allAddons = [];
		allAddons = select("addOns").getAddons();
		const currentAddon = allAddons.find((addon) => addon.slug === slug);
		return currentAddon?.active;
	} catch (error) {
		return false;
	}
};
