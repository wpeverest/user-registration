/* global urcr_localized_data */

// Get urcr_localized_data object
export const getURCRLocalizedData = () => {
	if (typeof window === "undefined") {
		return null;
	}
	return typeof window.urcr_localized_data !== "undefined"
		? window.urcr_localized_data
		: null;
};

// Get specific property from urcr_localized_data
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

// Check if user has pro access
export const isProAccess = () => {
	const isPro = getURCRData("is_pro", false);
	return isPro === true || isPro === "1" || isPro === 1;
};

export const isDripContent = () => {
	const isDripContent = getURCRData("is_drip_content", false);
	return (
		isDripContent === true || isDripContent === "1" || isDripContent === 1
	);
};

export const isMasteriyo = () => {
	const isMasteriyo = getURCRData("is_masteriyo", false);
	return isMasteriyo === true || isMasteriyo === "1" || isMasteriyo === 1;
};

// Check if UR_DEV is enabled
export const isURDev = () => {
	const urDev = getURCRData("UR_DEV", false);
	return urDev === true || urDev === "1" || urDev === 1;
};
