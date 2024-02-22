import React from "react";
import { ChakraProvider, Container } from "@chakra-ui/react";
import { HashRouter } from "react-router-dom";
import Header from "./Header/Header";
import Router from "../Router/Router";
import Theme from "./Theme/Theme";

const App = () => {
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
