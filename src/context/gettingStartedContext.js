export const initialState = {
	settings: {},
	installPage: {
		registration_page: {
			status: "not_installed",
			slug: "",
		},
		my_account_page: {
			status: "not_installed",
			slug: "",
		},
	},
	defaultFormId: "",
	isPro: false,
};

export const actionTypes = {
	GET_SETTINGS: "GET_SETTINGS",
	GET_INSTALL_PAGE: "GET_INSTALL_PAGE",
	GET_DEFAULT_FORM: "GET_DEFAULT_FORM",
	GET_IS_PRO: "GET_IS_PRO",
};

const reducer = (state, action) => {
	switch (action.type) {
		case actionTypes.GET_SETTINGS:
			return {
				...state,
				settings: action.settings,
			};
		case actionTypes.GET_INSTALL_PAGE:
			return {
				...state,
				installPage: action.installPage,
			};
		case actionTypes.GET_DEFAULT_FORM:
			return {
				...state,
				defaultFormId: action.defaultFormId,
			};
		case actionTypes.GET_IS_PRO:
			return {
				...state,
				isPro: action.isPro,
			};
		default:
			return state;
	}
};

export default reducer;
