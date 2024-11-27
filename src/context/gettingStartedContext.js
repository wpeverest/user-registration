/* global _UR_WIZARD_ */
const { adminEmail } = typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;

export const initialState = {
	settings: {},
	isPro: false,
	allowUsageData: {
		user_registration_allow_usage_tracking: "yes",
		user_registration_allow_email_updates: "yes",
		user_registration_updates_admin_email: adminEmail
	}
};

export const actionTypes = {
	GET_SETTINGS: "GET_SETTINGS",
	GET_SETTINGS: "GET_ALLOW_USAGE",
	GET_DEFAULT_REGISTRATION_PAGE: "GET_DEFAULT_REGISTRATION_PAGE",
	GET_IS_PRO: "GET_IS_PRO"
};

const reducer = (state, action) => {
	switch (action.type) {
		case actionTypes.GET_SETTINGS:
			return {
				...state,
				settings: action.settings
			};
		case actionTypes.GET_ALLOW_USAGE:
			return {
				...state,
				allowUsageData: action.allowUsageData
			};
		case actionTypes.GET_DEFAULT_REGISTRATION_PAGE:
			return {
				...state,
				registrationPageLink: action.registrationPageLink
			};
		case actionTypes.GET_IS_PRO:
			return {
				...state,
				isPro: action.isPro
			};
		default:
			return state;
	}
};

export default reducer;
