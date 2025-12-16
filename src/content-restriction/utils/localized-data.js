/**
 * Utility to access urcr_localized_data from PHP
 *
 * This data is localized via wp_localize_script in class-urcr-admin-assets.php
 * and is available as a global JavaScript variable when the script is enqueued.
 *
 * The data includes:
 * - wp_roles: WordPress user roles
 * - wp_capabilities: WordPress capabilities
 * - ur_forms: User Registration forms
 * - post_types: Available post types
 * - taxonomies: Available taxonomies
 * - labels: Translated labels
 * - templates: HTML templates
 * - memberships: Available memberships
 * - And more...
 *
 * @see class-urcr-admin-assets.php::get_localized_data_for_react()
 */

/* global urcr_localized_data */

/**
 * Get the urcr_localized_data object
 *
 * @returns {Object|null} The localized data object or null if not available
 *
 * @example
 * const data = getURCRLocalizedData();
 * if (data) {
 *   console.log(data.wp_roles);
 *   console.log(data.labels);
 * }
 */
export const getURCRLocalizedData = () => {
	if (typeof window === "undefined") {
		return null;
	}
	return typeof window.urcr_localized_data !== "undefined"
		? window.urcr_localized_data
		: null;
};

/**
 * Get a specific property from urcr_localized_data
 *
 * @param {string} key - The key to retrieve (e.g., 'wp_roles', 'labels', 'post_types')
 * @param {*} defaultValue - Default value if key doesn't exist or data is unavailable
 * @returns {*} The value or defaultValue
 *
 * @example
 * const labels = getURCRData('labels', {});
 * const wpRoles = getURCRData('wp_roles', []);
 * const postTypes = getURCRData('post_types', {});
 */
export const getURCRData = (key, defaultValue = null) => {
	const data = getURCRLocalizedData();
	if (!data) {
		if (process.env.NODE_ENV === "development") {
			console.warn(
				`URCR: urcr_localized_data is not available. Make sure the script is properly enqueued.`
			);
		}
		return defaultValue;
	}
	return data[key] !== undefined ? data[key] : defaultValue;
};

/**
 * Check if the user has pro access
 *
 * @returns {boolean} True if user has pro access, false otherwise
 *
 * @example
 * if (isProAccess()) {
 *   // Show pro features
 * }
 */
export const isProAccess = () => {
	const isPro = getURCRData("is_pro", false);
	return isPro === true || isPro === "1" || isPro === 1;
};
