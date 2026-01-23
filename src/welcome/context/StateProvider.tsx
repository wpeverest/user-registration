import React, { createContext, useContext, useReducer, ReactNode } from "react";
import {
	GettingStartedState,
	Action,
	initialState,
	reducer
} from "./Gettingstartedcontext";

interface StateContextType {
	state: GettingStartedState;
	dispatch: React.Dispatch<Action>;
}

const StateContext = createContext<StateContextType | undefined>(undefined);

interface StateProviderProps {
	children: ReactNode;
}

export const StateProvider: React.FC<StateProviderProps> = ({ children }) => {
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
