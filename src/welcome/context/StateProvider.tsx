import React, {
	createContext,
	useContext,
	useReducer,
	ReactNode,
	Reducer,
	Dispatch
} from "react";

interface StateProviderProps {
	initialState: any;
	reducer: Reducer<any, any>;
	children: ReactNode;
}

interface StateContextType {
	state: any;
	dispatch: Dispatch<any>;
}

const StateContext = createContext<StateContextType | undefined>(undefined);

export const StateProvider: React.FC<StateProviderProps> = ({
	initialState,
	reducer,
	children
}) => {
	const [state, dispatch] = useReducer(reducer, initialState);

	return (
		<StateContext.Provider value={{ state, dispatch }}>
			{children}
		</StateContext.Provider>
	);
};

export const useStateValue = (): StateContextType => {
	const context = useContext(StateContext);
	if (context === undefined) {
		throw new Error("useStateValue must be used within a StateProvider");
	}
	return context;
};
