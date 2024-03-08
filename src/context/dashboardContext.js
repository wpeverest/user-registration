export const initialState = {
	pluginsStatus: "",
	themesStatus: "",
	allAddons: [],
	upgradeModal: {
		enable: false,
		type: "",
		moduleType: "",
		moduleName: "",
	},
	allFeatures: [],
};

export const actionTypes = {
	GET_PLUGINS_STATUS: "GET_PLUGINS_STATUS",
	GET_THEMES_STATUS: "GET_THEMES_STATUS",
	GET_ALL_ADDONS: "GET_ALL_ADDONS",
	GET_UPGRADE_MODAL: "GET_UPGRADE_MODAL",
	GET_ALL_FEATURES: "GET_ALL_FEATURES",
};

const reducer = (state, action) => {
	switch (action.type) {
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
		case actionTypes.GET_ALL_ADDONS:
			return {
				...state,
				allAddons: action.allAddons,
			};
		case actionTypes.GET_UPGRADE_MODAL:
			return {
				...state,
				upgradeModal: action.upgradeModal,
			};
		case actionTypes.GET_ALL_Features:
			return {
				...state,
				allFeatures: action.allFeatures,
			};
		default:
			return state;
	}
};

export default reducer;
