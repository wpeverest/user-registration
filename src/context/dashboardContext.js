export const initialState = {
	pluginsStatus: "",
	themesStatus: "",
	upgradeModal: {
		enable: false,
		type: "",
		moduleType: "",
		moduleName: "",
	},
	allModules: [],
};

export const actionTypes = {
	GET_PLUGINS_STATUS: "GET_PLUGINS_STATUS",
	GET_THEMES_STATUS: "GET_THEMES_STATUS",
	GET_UPGRADE_MODAL: "GET_UPGRADE_MODAL",
	GET_ALL_MODULES: "GET_ALL_MODULES",
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
		case actionTypes.GET_UPGRADE_MODAL:
			return {
				...state,
				upgradeModal: action.upgradeModal,
			};
		case actionTypes.GET_ALL_MODULES:
			return {
				...state,
				allModules: action.allModules,
			};
		default:
			return state;
	}
};

export default reducer;
