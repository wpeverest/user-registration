export const initialState = {
    settings: {},
    installPage: {
        registration_page: {
            status: "not_installed",
            slug: ""
        },
        my_account_page: {
            status: "not_installed",
            slug: ""
        }
    },
    defaultFormId: ""
};

export const actionTypes = {
    GET_SETTINGS: "GET_SETTINGS",
    GET_INSTALL_PAGE: "GET_INSTALL_PAGE",
    GET_DEFAULT_FORM: "GET_DEFAULT_FORM"
};

const reducer = (state, action) => {
    switch (action.type) {
        case actionTypes.GET_SETTINGS:
            return {
                ...state,
                settings: action.settings
            };
        case actionTypes.GET_INSTALL_PAGE:
            return {
                ...state,
                installPage: action.installPage
            };
        case actionTypes.GET_DEFAULT_FORM:
            console.log(action.defaultFormId);
            return {
                ...state,
                defaultFormId: action.defaultFormId
            };
        default:
            return state;
    }
};

export default reducer;
