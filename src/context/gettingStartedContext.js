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
	pluginsStatus: "",
	themesStatus: "",
};

export const actionTypes = {
	GET_SETTINGS: "GET_SETTINGS",
	GET_INSTALL_PAGE: "GET_INSTALL_PAGE",
	GET_DEFAULT_FORM: "GET_DEFAULT_FORM",
	GET_IS_PRO: "GET_IS_PRO",
	GET_PLUGINS_STATUS: "GET_PLUGINS_STATUS",
	GET_THEMES_STATUS: "GET_THEMES_STATUS",
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
		case actionTypes.GET_PLUGINS_STATUS:
			return {
				...state,
				pluginsStatus: action.pluginsStatus,
			};
		case actionTypes.GET_THEMES_STATUS:
			return {
				...state,
				themesStatus: action.themesStatus,
			};
		default:
			return state;
	}
};

export default reducer;
