export const initialState = {
    settings: {}
};

export const actionTypes = {
    GET_SETTINGS: "GET_SETTINGS"
};

const reducer = (state, action) => {
    switch (action.type) {
        case actionTypes.GET_SETTINGS:
            return {
                ...state,
                settings: action.settings
            };
        default:
            return state;
    }
};

export default reducer;
