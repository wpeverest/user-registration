import { select } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";

/* global _UR_DASHBOARD_ */
const { urRestApiNonce, restURL } =
	typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

const base = restURL + "user-registration/v1/";
const urls = {
	modules: base + "modules",
	activateModule: base + "modules/activate",
	deactivateModule: base + "modules/deactivate",
	bulkActivateModules: base + "modules/bulk-activate",
	bulkDeactivateModules: base + "modules/bulk-deactivate",
};

export const getAllModules = () => {
	return apiFetch({
		path: `${urls.modules}`,
		method: "get",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
	}).then((res) => res);
};

export const activateModule = (slug, name, type) => {
	return apiFetch({
		path: urls.activateModule,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slug: slug,
			name: name,
			type: type,
		},
	}).then((res) => res);
};

export const deactivateModule = (slug, type) => {
	return apiFetch({
		path: `${urls.deactivateModule}`,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			slug: slug,
			type: type,
		},
	}).then((res) => res);
};

export const bulkActivateModules = (moduleData) => {
	return apiFetch({
		path: urls.bulkActivateModules,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			moduleData: moduleData,
		},
	}).then((res) => res);
};

export const bulkDeactivateModules = (moduleData) => {
	return apiFetch({
		path: urls.bulkDeactivateModules,
		method: "POST",
		headers: {
			"X-WP-Nonce": urRestApiNonce,
		},
		data: {
			moduleData: moduleData,
		},
	}).then((res) => res);
};
