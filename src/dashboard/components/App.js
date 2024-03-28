/**
 *  External Dependencies
 */
import React, { useEffect } from "react";
import { ChakraProvider, Container } from "@chakra-ui/react";
import { HashRouter } from "react-router-dom";

/**
 *  Internal Dependencies
 */
import Header from "./Header/Header";
import Router from "../Router/Router";
import Theme from "./Theme/Theme";
import { useStateValue } from "../../context/StateProvider";
import { actionTypes } from "../../context/dashboardContext";

const App = () => {
	/* global _UR_DASHBOARD_ */
	const { plugins, themes } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
	const [{ pluginsStatus, themesStatus }, dispatch] = useStateValue();

	/**
	 * Set our Plugins and Theme Status on component load.
	 */
	useEffect(() => {
		dispatch(
			{
				type: actionTypes.GET_PLUGINS_STATUS,
				pluginsStatus: plugins,
			},
			{
				type: actionTypes.GET_THEMES_STATUS,
				themeStatus: themes,
			}
		);
	}, [pluginsStatus, themesStatus]);

	return (
		<HashRouter>
			<ChakraProvider theme={Theme}>
				<Header />
				<Container maxW="container.xl">
					<Router />
				</Container>
			</ChakraProvider>
		</HashRouter>
	);
};

export default App;
