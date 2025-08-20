/**
 *  External Dependencies
 */
import React, { useEffect, useRef } from "react";
import {
	ChakraProvider,
	Container,
	Drawer,
	DrawerBody,
	DrawerCloseButton,
	DrawerContent,
	DrawerHeader,
	DrawerOverlay,
	useDisclosure
} from "@chakra-ui/react";
import { HashRouter } from "react-router-dom";
import { sprintf, __ } from "@wordpress/i18n";

/**
 *  Internal Dependencies
 */
import Header from "./Header/Header";
import Router from "../Router/Router";
import Theme from "./Theme/Theme";
import { useStateValue } from "../../context/StateProvider";
import { actionTypes } from "../../context/dashboardContext";
import Changelog from "./Changelog/Changelog";

const App = () => {
	/* global _UR_DASHBOARD_ */
	const { plugins, themes } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
	const [{ pluginsStatus, themesStatus }, dispatch] = useStateValue();
	const { isOpen, onOpen, onClose } = useDisclosure();
	const ref = useRef();

	/**
	 * Set our Plugins and Theme Status on component load.
	 */
	useEffect(() => {
		dispatch(
			{
				type: actionTypes.GET_PLUGINS_STATUS,
				pluginsStatus: plugins
			},
			{
				type: actionTypes.GET_THEMES_STATUS,
				themeStatus: themes
			}
		);
	}, [pluginsStatus, themesStatus]);

	useEffect(() => {
		if (isOpen) {
			document.body.classList.add("ur-modal-open");
		} else {
			document.body.classList.remove("ur-modal-open");
		}
		return () => {
			document.body.classList.remove("ur-modal-open");
		};
	}, [isOpen]);

	useEffect(() => {
		const announcementButton = document.querySelector(
			".ur-announcement-button"
		);

		if (announcementButton) {
			const handleClick = () => {
				onOpen();
			};
			announcementButton.addEventListener("click", handleClick);

			return () => {
				announcementButton.removeEventListener("click", handleClick);
			};
		}
	}, []);
	return (
		<HashRouter>
			<ChakraProvider theme={Theme}>
				<Drawer
					isOpen={isOpen}
					placement="right"
					onClose={onClose}
					finalFocusRef={ref}
					size="md"
				>
					<DrawerOverlay
						bgColor="rgb(0,0,0,0.05)"
						sx={{ backdropFilter: "blur(1px)" }}
					/>
					<DrawerContent
						className="user-registration-announcement"
						top="var(--wp-admin--admin-bar--height, 0) !important"
					>
						<DrawerCloseButton />
						<DrawerHeader>
							{__("Latest Updates", "user-registration")}
						</DrawerHeader>
						<DrawerBody>
							<Changelog />
						</DrawerBody>
					</DrawerContent>
				</Drawer>
				<Container maxW="100%">
					<Router />
				</Container>
			</ChakraProvider>
		</HashRouter>
	);
};

export default App;
