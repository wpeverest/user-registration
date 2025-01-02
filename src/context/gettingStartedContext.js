/* global _UR_WIZARD_ */
const { adminEmail } = typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;

export const initialState = {
	settings: {},
	isPro: false,
	allowUsageData: {
		user_registration_allow_usage_tracking: "yes",
		user_registration_allow_email_updates: "yes",
		user_registration_updates_admin_email: adminEmail
	},
	registrationType: "user_registration_normal_registration",
	defaultFormId: "",
	installedPages: {},
	registrationPageLink: "",
	defaultFormURL: ""
};

export const actionTypes = {
	GET_SETTINGS: "GET_SETTINGS",
	GET_SETTINGS: "GET_ALLOW_USAGE",
	GET_DEFAULT_REGISTRATION_PAGE: "GET_DEFAULT_REGISTRATION_PAGE",
	GET_MEMBERSHIP_DETAILS: "GET_MEMBERSHIP_DETAILS",
	GET_IS_PRO: "GET_IS_PRO",
	GET_REGISTRATION_TYPE: "GET_REGISTRATION_TYPE",
	GET_DEFAULT_FORM: "GET_DEFAULT_FORM",
	GET_INSTALLED_PAGES: "GET_INSTALLED_PAGES",
	GET_DEFAULT_FORM_URL: "GET_DEFAULT_FORM_URL"
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
		case actionTypes.GET_MEMBERSHIP_DETAILS:
			return {
				...state,
				membershipDetails: action.membershipDetails
			};
		case actionTypes.GET_IS_PRO:
			return {
				...state,
				isPro: action.isPro
			};
		case actionTypes.GET_REGISTRATION_TYPE:
			return {
				...state,
				registrationType: action.registrationType
			};
		case actionTypes.GET_DEFAULT_FORM:
			return {
				...state,
				defaultFormId: action.defaultFormId
			};
		case actionTypes.GET_INSTALLED_PAGES:
			return {
				...state,
				installedPages: action.installedPages
			};
		case actionTypes.GET_DEFAULT_FORM_URL:
			return {
				...state,
				defaultFormURL: action.defaultFormURL
			};
		default:
			return state;
	}
};

export default reducer;
