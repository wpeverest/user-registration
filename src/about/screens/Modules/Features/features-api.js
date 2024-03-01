import { select } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";

/* global _UR_ */
const { urRestApiNonce, restURL } = typeof _UR_ !== "undefined" && _UR_;

const base = restURL + "user-registration/v1/";
const urls = {
	features: base + "features",
	enableFeature: base + "features/enable",
	disableFeature: base + "features/disable",
	bulkEnableFeatures: base + "features/bulk-enable",
	bulkDisableFeatures: base + "features/bulk-disable",
};

export const getAllFeatures = () => {
	return apiFetch({
		path: `${urls.features}`,
		method: "get",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
	}).then((res) => res);
};

export const getFeature = (slug) => {
	return apiFetch(
		{
			path: `${urls.features}/${slug}`,
			method: "get",
		}.then((res) => res)
	);
};

export const enableFeature = (slug) => {
	return apiFetch({
		path: `${urls.enableFeature}`,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slug: slug,
		},
	}).then((res) => res);
};

export const disableFeature = (slug) => {
	return apiFetch({
		path: `${urls.disableFeature}`,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slug: slug,
		},
	}).then((res) => res);
};

export const bulkEnableFeatures = (slugs) => {
	return apiFetch({
		path: urls.bulkEnableFeatures,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slugs: slugs,
		},
	}).then((res) => res);
};

export const bulkDisableFeatures = (slugs) => {
	return apiFetch({
		path: urls.bulkDisableFeatures,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slugs: slugs,
		},
	}).then((res) => res);
};
