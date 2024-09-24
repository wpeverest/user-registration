export const initialState = {
	settings: {},
	isPro: false
};

export const actionTypes = {
	GET_SETTINGS: "GET_SETTINGS",
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
